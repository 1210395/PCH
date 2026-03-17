<?php

namespace App\Http\Controllers;

use App\Models\ProfileRating;
use App\Models\RatingCriteria;
use App\Models\RatingCriteriaResponse;
use App\Models\Designer;
use App\Models\AdminSetting;
use Illuminate\Http\Request;

class ProfileRatingController extends Controller
{
    /**
     * Store a new rating
     */
    public function store(Request $request, $locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to rate profiles'
            ], 401);
        }

        // Can't rate yourself
        if ($currentDesigner->id == $designerId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot rate your own profile'
            ], 403);
        }

        $designer = Designer::findOrFail($designerId);

        // Guest accounts cannot be rated via profile ratings
        if ($designer->isGuest()) {
            return response()->json([
                'success' => false,
                'message' => __('Guest accounts cannot be rated')
            ], 403);
        }

        // Check if already rated
        if (ProfileRating::hasRated($designerId, $currentDesigner->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this profile'
            ], 403);
        }

        $validated = $request->validate([
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'required|string|min:10|max:500',
            'criteria_ids' => 'nullable|array',
            'criteria_ids.*' => 'integer|exists:rating_criteria,id',
        ]);

        $rating = ProfileRating::create([
            'designer_id' => $designerId,
            'rater_id'    => $currentDesigner->id,
            'rating'      => $validated['rating'],
            'comment'     => $validated['comment'],
        ]);

        // Save criteria responses
        if (!empty($validated['criteria_ids'])) {
            $activeCriteriaIds = RatingCriteria::active()
                ->whereIn('id', $validated['criteria_ids'])
                ->pluck('id');

            foreach ($activeCriteriaIds as $criteriaId) {
                RatingCriteriaResponse::create([
                    'profile_rating_id' => $rating->id,
                    'rating_criteria_id' => $criteriaId,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $rating->isApproved()
                ? 'Your rating has been submitted!'
                : 'Your rating has been submitted and is pending approval.',
            'rating' => [
                'id'      => $rating->id,
                'rating'  => $rating->rating,
                'comment' => $rating->comment,
                'status'  => $rating->status,
                'rater'   => [
                    'id'     => $currentDesigner->id,
                    'name'   => $currentDesigner->name,
                    'avatar' => $currentDesigner->avatar,
                ],
                'created_at' => $rating->created_at->diffForHumans(),
            ]
        ]);
    }

    /**
     * Get ratings for a designer profile (public endpoint)
     */
    public function index($locale, $designerId)
    {
        $designer = Designer::findOrFail($designerId);

        $ratings = ProfileRating::with('rater:id,name,avatar')
            ->approved()
            ->forDesigner($designerId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $averageRating = ProfileRating::getAverageRating($designerId);
        $ratingCount   = ProfileRating::getRatingCount($designerId);

        // Check if current user has already rated
        $hasRated = false;
        $currentDesigner = auth('designer')->user();
        if ($currentDesigner) {
            $hasRated = ProfileRating::hasRated($designerId, $currentDesigner->id);
        }

        return response()->json([
            'success'        => true,
            'average_rating' => round($averageRating, 1),
            'rating_count'   => $ratingCount,
            'has_rated'      => $hasRated,
            'ratings'        => $ratings->items(),
            'pagination'     => [
                'current_page' => $ratings->currentPage(),
                'last_page'    => $ratings->lastPage(),
                'per_page'     => $ratings->perPage(),
                'total'        => $ratings->total(),
            ]
        ]);
    }

    /**
     * Get user's rating for a specific designer
     */
    public function show($locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $rating = ProfileRating::with(['designer:id,name', 'criteria:id'])
            ->where('designer_id', $designerId)
            ->where('rater_id', $currentDesigner->id)
            ->first();

        if (!$rating) {
            return response()->json([
                'success' => false,
                'message' => 'Rating not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'rating'  => [
                'id'               => $rating->id,
                'rating'           => $rating->rating,
                'comment'          => $rating->comment,
                'status'           => $rating->status,
                'rejection_reason' => $rating->rejection_reason,
                'criteria_ids'     => $rating->criteria->pluck('id')->toArray(),
                'created_at'       => $rating->created_at->diffForHumans(),
            ]
        ]);
    }

    /**
     * Update user's rating for a specific designer
     */
    public function update(Request $request, $locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $rating = ProfileRating::where('designer_id', $designerId)
            ->where('rater_id', $currentDesigner->id)
            ->first();

        if (!$rating) {
            return response()->json([
                'success' => false,
                'message' => 'Rating not found'
            ], 404);
        }

        $validated = $request->validate([
            'rating'         => 'required|integer|min:1|max:5',
            'comment'        => 'required|string|min:10|max:500',
            'criteria_ids'   => 'nullable|array',
            'criteria_ids.*' => 'integer|exists:rating_criteria,id',
        ]);

        // Reset to pending if auto-accept is disabled
        $status = $rating->status;
        if (!AdminSetting::isAutoAcceptEnabled('ratings') && $rating->isRejected()) {
            $status = 'pending';
        }

        $rating->update([
            'rating'           => $validated['rating'],
            'comment'          => $validated['comment'],
            'status'           => $status,
            'rejection_reason' => null,
        ]);

        // Replace criteria responses
        $rating->criteriaResponses()->delete();
        if (!empty($validated['criteria_ids'])) {
            $activeCriteriaIds = RatingCriteria::active()
                ->whereIn('id', $validated['criteria_ids'])
                ->pluck('id');

            foreach ($activeCriteriaIds as $criteriaId) {
                RatingCriteriaResponse::create([
                    'profile_rating_id'  => $rating->id,
                    'rating_criteria_id' => $criteriaId,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Your rating has been updated!',
            'rating'  => [
                'id'      => $rating->id,
                'rating'  => $rating->rating,
                'comment' => $rating->comment,
                'status'  => $rating->status,
            ]
        ]);
    }

    /**
     * Delete user's rating for a specific designer
     */
    public function destroy($locale, $designerId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $rating = ProfileRating::where('designer_id', $designerId)
            ->where('rater_id', $currentDesigner->id)
            ->first();

        if (!$rating) {
            return response()->json([
                'success' => false,
                'message' => 'Rating not found'
            ], 404);
        }

        $rating->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your rating has been deleted.'
        ]);
    }
}
