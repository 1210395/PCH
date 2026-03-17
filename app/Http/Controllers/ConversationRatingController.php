<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationRating;
use Illuminate\Http\Request;

/**
 * Handles rating of conversations between designers.
 * Enforces a 24-hour waiting period after a conversation is accepted before a rating can be submitted,
 * and prevents duplicate ratings from the same participant.
 */
class ConversationRatingController extends Controller
{
    /**
     * Get rating status for a conversation.
     *
     * @param  string  $locale
     * @param  int     $conversationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function status($locale, $conversationId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $conversation = Conversation::findOrFail($conversationId);

        // Verify user is part of this conversation
        if ($conversation->designer_1_id != $currentDesigner->id && $conversation->designer_2_id != $currentDesigner->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $canRate = $conversation->canRate();
        $hoursRemaining = $conversation->hoursUntilRatingAllowed();
        $hasRated = $conversation->hasUserRated($currentDesigner->id);
        $userRating = $conversation->getUserRating($currentDesigner->id);

        return response()->json([
            'success' => true,
            'can_rate' => $canRate,
            'hours_remaining' => $hoursRemaining,
            'has_rated' => $hasRated,
            'user_rating' => $userRating ? [
                'rating' => $userRating->rating,
                'created_at' => $userRating->created_at->diffForHumans(),
            ] : null,
            'accepted_at' => $conversation->accepted_at?->toISOString(),
        ]);
    }

    /**
     * Submit a rating for a conversation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $conversationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $locale, $conversationId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $conversation = Conversation::findOrFail($conversationId);

        // Verify user is part of this conversation
        if ($conversation->designer_1_id != $currentDesigner->id && $conversation->designer_2_id != $currentDesigner->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if rating is allowed (24 hours passed)
        if (!$conversation->canRate()) {
            $hoursRemaining = $conversation->hoursUntilRatingAllowed();
            return response()->json([
                'success' => false,
                'message' => "You can rate this conversation in {$hoursRemaining} hours",
                'hours_remaining' => $hoursRemaining
            ], 403);
        }

        // Check if already rated
        if ($conversation->hasUserRated($currentDesigner->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this conversation'
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Determine who is being rated
        $ratedId = $conversation->designer_1_id == $currentDesigner->id
            ? $conversation->designer_2_id
            : $conversation->designer_1_id;

        $rating = ConversationRating::create([
            'conversation_id' => $conversationId,
            'rater_id' => $currentDesigner->id,
            'rated_id' => $ratedId,
            'rating' => $validated['rating'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your rating!',
            'rating' => [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'created_at' => $rating->created_at->diffForHumans(),
            ]
        ]);
    }

    /**
     * Update a rating for a conversation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $conversationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $locale, $conversationId)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $conversation = Conversation::findOrFail($conversationId);

        // Verify user is part of this conversation
        if ($conversation->designer_1_id != $currentDesigner->id && $conversation->designer_2_id != $currentDesigner->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $rating = ConversationRating::getRating($conversationId, $currentDesigner->id);

        if (!$rating) {
            return response()->json([
                'success' => false,
                'message' => 'Rating not found'
            ], 404);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $rating->update([
            'rating' => $validated['rating'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your rating has been updated!',
            'rating' => [
                'id' => $rating->id,
                'rating' => $rating->rating,
            ]
        ]);
    }
}
