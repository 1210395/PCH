<?php

namespace App\Http\Controllers;

use App\Models\ProfileRating;
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

        // Check if already rated
        if (ProfileRating::hasRated($designerId, $currentDesigner->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this profile'
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:500',
        ]);

        $rating = ProfileRating::create([
            'designer_id' => $designerId,
            'rater_id' => $currentDesigner->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'success' => true,
            'message' => $rating->isApproved()
                ? 'Your rating has been submitted!'
                : 'Your rating has been submitted and is pending approval.',
            'rating' => [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'status' => $rating->status,
                'rater' => [
                    'id' => $currentDesigner->id,
                    'name' => $currentDesigner->name,
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
        $ratingCount = ProfileRating::getRatingCount($designerId);

        // Check if current user has already rated
        $hasRated = false;
        $currentDesigner = auth('designer')->user();
        if ($currentDesigner) {
            $hasRated = ProfileRating::hasRated($designerId, $currentDesigner->id);
        }

        return response()->json([
            'success' => true,
            'average_rating' => round($averageRating, 1),
            'rating_count' => $ratingCount,
            'has_rated' => $hasRated,
            'ratings' => $ratings->items(),
            'pagination' => [
                'current_page' => $ratings->currentPage(),
                'last_page' => $ratings->lastPage(),
                'per_page' => $ratings->perPage(),
                'total' => $ratings->total(),
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

        $rating = ProfileRating::with('designer:id,name')
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
            'rating' => [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'status' => $rating->status,
                'rejection_reason' => $rating->rejection_reason,
                'created_at' => $rating->created_at->diffForHumans(),
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
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:500',
        ]);

        // Reset to pending if auto-accept is disabled
        $status = $rating->status;
        if (!AdminSetting::isAutoAcceptEnabled('ratings') && $rating->isRejected()) {
            $status = 'pending';
        }

        $rating->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => $status,
            'rejection_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your rating has been updated!',
            'rating' => [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'status' => $rating->status,
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
