<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use Illuminate\Http\Request;
use App\Http\Controllers\NotificationController;

/**
 * DesignerFollowController
 *
 * Handles social interaction routes:
 * - Follow/unfollow designers
 * - Check following status
 * - Toggle like on designer profiles
 * - Search users (for sharing)
 */
class DesignerFollowController extends Controller
{
    /**
     * Follow a designer
     */
    public function follow(Request $request, $locale, $id)
    {
        try {
            $currentUser = auth('designer')->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to follow designers'
                ], 401);
            }

            // Prevent self-following
            if ($currentUser->id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot follow yourself'
                ], 400);
            }

            $designerToFollow = Designer::findOrFail($id);

            // Check if already following
            if ($currentUser->following()->where('following_id', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already following this designer'
                ], 400);
            }

            // Create follow relationship
            $currentUser->following()->attach($id, [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update counts
            $currentUser->increment('following_count');
            $designerToFollow->increment('followers_count');

            // Send notification to the followed designer
            NotificationController::createNotification(
                $id,
                'new_follower',
                'Someone started following you!',
                'You have a new follower. Check out your growing community!'
            );

            return response()->json([
                'success' => true,
                'message' => 'Successfully followed ' . $designerToFollow->name,
                'followers_count' => $designerToFollow->followers_count
            ]);

        } catch (\Exception $e) {
            \Log::error('Follow failed', [
                'user_id' => auth('designer')->id(),
                'designer_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while following this designer'
            ], 500);
        }
    }

    /**
     * Unfollow a designer
     */
    public function unfollow(Request $request, $locale, $id)
    {
        try {
            $currentUser = auth('designer')->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to unfollow designers'
                ], 401);
            }

            $designerToUnfollow = Designer::findOrFail($id);

            // Check if actually following
            if (!$currentUser->following()->where('following_id', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not following this designer'
                ], 400);
            }

            // Remove follow relationship
            $currentUser->following()->detach($id);

            // Update counts
            $currentUser->decrement('following_count');
            $designerToUnfollow->decrement('followers_count');

            return response()->json([
                'success' => true,
                'message' => 'Successfully unfollowed ' . $designerToUnfollow->name,
                'followers_count' => $designerToUnfollow->followers_count
            ]);

        } catch (\Exception $e) {
            \Log::error('Unfollow failed', [
                'user_id' => auth('designer')->id(),
                'designer_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while unfollowing this designer'
            ], 500);
        }
    }

    /**
     * Check if current user is following a designer
     */
    public function checkFollowing($locale, $id)
    {
        try {
            $currentUser = auth('designer')->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => true,
                    'is_following' => false
                ]);
            }

            $isFollowing = $currentUser->following()->where('following_id', $id)->exists();

            return response()->json([
                'success' => true,
                'is_following' => $isFollowing
            ]);

        } catch (\Exception $e) {
            \Log::error('Check following failed', [
                'user_id' => auth('designer')->id(),
                'designer_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Toggle like on a designer profile
     */
    public function toggleLike($locale, $id)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Prevent self-liking
        if ($currentDesigner->id == $id) {
            return response()->json(['success' => false, 'message' => 'You cannot like your own profile'], 400);
        }

        $designer = Designer::findOrFail($id);

        $existingLike = \App\Models\Like::where('designer_id', $currentDesigner->id)
            ->where('likeable_type', 'App\Models\Designer')
            ->where('likeable_id', $id)
            ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $designer->decrement('likes_count');
            $liked = false;
        } else {
            // Like
            \App\Models\Like::create([
                'designer_id' => $currentDesigner->id,
                'likeable_type' => 'App\Models\Designer',
                'likeable_id' => $id,
            ]);
            $designer->increment('likes_count');
            $liked = true;

            // Send notification to the liked designer
            NotificationController::createNotification(
                $id,
                'profile_like',
                'Someone liked your profile!',
                'Your work is being appreciated. Keep creating!'
            );
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $designer->fresh()->likes_count
        ]);
    }

    /**
     * Search users by name (for sharing marketplace posts)
     */
    public function searchUsers(Request $request, $locale)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = trim($request->input('q', ''));
        if (strlen($query) < 2) {
            return response()->json(['success' => true, 'users' => []]);
        }

        $users = Designer::where('is_active', true)
            ->where('id', '!=', $currentDesigner->id)
            ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $query . '%'])
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'avatar', 'city']);

        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'city' => $user->city,
            ];
        });

        return response()->json(['success' => true, 'users' => $results]);
    }
}
