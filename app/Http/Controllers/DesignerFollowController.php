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
     * Follow a designer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id  Designer ID to follow
     * @return \Illuminate\Http\JsonResponse
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

            // Wrap follow + counter increments in a transaction so two
            // concurrent clicks don't both pass the existence check and
            // double-attach / double-increment. (bugs.md H-3, H-5)
            $created = \Illuminate\Support\Facades\DB::transaction(function () use ($currentUser, $id, $designerToFollow) {
                $exists = $currentUser->following()
                    ->where('following_id', $id)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    return false;
                }

                $currentUser->following()->attach($id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $currentUser->increment('following_count');
                $designerToFollow->increment('followers_count');
                return true;
            });

            if (!$created) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already following this designer'
                ], 400);
            }

            // Notify outside the transaction so a notification failure doesn't roll back the follow.
            NotificationController::createNotification(
                $id,
                'new_follower',
                'Someone started following you!',
                'You have a new follower. Check out your growing community!'
            );

            return response()->json([
                'success' => true,
                'message' => 'Successfully followed ' . $designerToFollow->name,
                'followers_count' => $designerToFollow->fresh()->followers_count
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
     * Unfollow a designer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id  Designer ID to unfollow
     * @return \Illuminate\Http\JsonResponse
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

            // Wrap detach + counter decrements in a transaction so two
            // concurrent clicks don't both decrement the counter when only
            // one row was actually deleted. (bugs.md H-3, H-5)
            $detached = \Illuminate\Support\Facades\DB::transaction(function () use ($currentUser, $id, $designerToUnfollow) {
                $exists = $currentUser->following()
                    ->where('following_id', $id)
                    ->lockForUpdate()
                    ->exists();

                if (!$exists) {
                    return false;
                }

                $currentUser->following()->detach($id);
                $currentUser->decrement('following_count');
                $designerToUnfollow->decrement('followers_count');
                return true;
            });

            if (!$detached) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not following this designer'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully unfollowed ' . $designerToUnfollow->name,
                'followers_count' => $designerToUnfollow->fresh()->followers_count
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
     * Check if current user is following a designer.
     *
     * @param  string  $locale
     * @param  int     $id  Designer ID to check
     * @return \Illuminate\Http\JsonResponse
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
     * Toggle like on a designer profile.
     *
     * @param  string  $locale
     * @param  int     $id  Designer ID to like/unlike
     * @return \Illuminate\Http\JsonResponse
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

        // Wrap in a transaction with lockForUpdate so two concurrent clicks
        // don't both create Like rows (and double-increment the counter).
        // (bugs.md H-3, H-5)
        $liked = \Illuminate\Support\Facades\DB::transaction(function () use ($currentDesigner, $id, $designer) {
            $existingLike = \App\Models\Like::where('designer_id', $currentDesigner->id)
                ->where('likeable_type', 'App\Models\Designer')
                ->where('likeable_id', $id)
                ->lockForUpdate()
                ->first();

            if ($existingLike) {
                $existingLike->delete();
                $designer->decrement('likes_count');
                return false;
            }

            \App\Models\Like::create([
                'designer_id' => $currentDesigner->id,
                'likeable_type' => 'App\Models\Designer',
                'likeable_id' => $id,
            ]);
            $designer->increment('likes_count');
            return true;
        });

        // Notify outside the transaction so a notification failure doesn't roll back the like.
        if ($liked) {
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
     * Search users by name, sector, or city (for sharing marketplace posts).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\JsonResponse
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
            ->where('sector', '!=', 'guest')
            ->where('id', '!=', $currentDesigner->id)
            ->where(function ($q) use ($query) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $query . '%'])
                  ->orWhere('city', 'like', '%' . $query . '%')
                  ->orWhere('sector', 'like', '%' . $query . '%')
                  ->orWhere('title', 'like', '%' . $query . '%')
                  ->orWhere('company_name', 'like', '%' . $query . '%');
            })
            ->limit(15)
            ->get(['id', 'first_name', 'last_name', 'avatar', 'city', 'sector', 'title']);

        $followingIds = $currentDesigner->following()->pluck('designers.id')->toArray();

        $results = $users->map(function ($user) use ($followingIds) {
            return [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'avatar' => $user->avatar ? url('media/' . $user->avatar) : null,
                'city' => $user->city,
                'sector' => $user->sector,
                'title' => $user->title,
                'is_following' => in_array($user->id, $followingIds),
            ];
        });

        return response()->json(['success' => true, 'users' => $results]);
    }

    /**
     * Get suggested users for sharing (followers, same sector/city, recently interacted).
     * Prioritises: followers of the current user, users the current user follows, same sector, same city.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestedUsers(Request $request, $locale)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $excludeIds = array_filter(explode(',', $request->input('exclude', '')));
        $excludeIds[] = $currentDesigner->id;

        $suggested = collect();

        // 1. Users who follow the current designer (mutual interest)
        $followers = $currentDesigner->followers()
            ->where('is_active', true)
            ->whereNotIn('designers.id', $excludeIds)
            ->limit(6)
            ->get(['designers.id', 'first_name', 'last_name', 'avatar', 'city', 'sector', 'title']);
        foreach ($followers as $user) {
            $user->_reason = 'follower';
        }
        $suggested = $suggested->merge($followers);

        // 2. Users the current designer follows
        $following = $currentDesigner->following()
            ->where('is_active', true)
            ->whereNotIn('designers.id', $excludeIds)
            ->whereNotIn('designers.id', $suggested->pluck('id'))
            ->limit(6)
            ->get(['designers.id', 'first_name', 'last_name', 'avatar', 'city', 'sector', 'title']);
        foreach ($following as $user) {
            $user->_reason = 'following';
        }
        $suggested = $suggested->merge($following);

        // 3. Same sector
        if ($currentDesigner->sector && $suggested->count() < 12) {
            $sameSector = Designer::where('is_active', true)
                ->where('sector', '!=', 'guest')
                ->where('sector', $currentDesigner->sector)
                ->whereNotIn('id', $excludeIds)
                ->whereNotIn('id', $suggested->pluck('id'))
                ->inRandomOrder()
                ->limit(6)
                ->get(['id', 'first_name', 'last_name', 'avatar', 'city', 'sector', 'title']);
            foreach ($sameSector as $user) {
                $user->_reason = 'same_sector';
            }
            $suggested = $suggested->merge($sameSector);
        }

        // 4. Same city
        if ($currentDesigner->city && $suggested->count() < 12) {
            $sameCity = Designer::where('is_active', true)
                ->where('sector', '!=', 'guest')
                ->where('city', $currentDesigner->city)
                ->whereNotIn('id', $excludeIds)
                ->whereNotIn('id', $suggested->pluck('id'))
                ->inRandomOrder()
                ->limit(4)
                ->get(['id', 'first_name', 'last_name', 'avatar', 'city', 'sector', 'title']);
            foreach ($sameCity as $user) {
                $user->_reason = 'same_city';
            }
            $suggested = $suggested->merge($sameCity);
        }

        $followingIds = $currentDesigner->following()->pluck('designers.id')->toArray();

        $results = $suggested->take(12)->map(function ($user) use ($followingIds) {
            return [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'avatar' => $user->avatar ? url('media/' . $user->avatar) : null,
                'city' => $user->city,
                'sector' => $user->sector,
                'title' => $user->title,
                'reason' => $user->_reason ?? null,
                'is_following' => in_array($user->id, $followingIds),
            ];
        })->values();

        return response()->json(['success' => true, 'users' => $results]);
    }
}
