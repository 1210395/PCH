<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Models\MarketplacePost;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationSubscriptionService;

/**
 * Handles CRUD operations for marketplace posts created by authenticated designers.
 * Supports trusted-user auto-approval, temp-to-permanent image migration, and share-to-user notifications.
 */
class MarketplacePostController extends Controller
{
    /**
     * Store a newly created marketplace post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
                'category' => 'required|string|max:100',
                'type' => 'required|string|in:service,collaboration,showcase,opportunity',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:100',
                'image_path' => 'nullable|string|max:500',
                'source_type' => 'nullable|string|in:product,project',
                'source_id' => 'nullable|integer',
            ]);

            // Determine approval status - trusted users get auto-approved
            $approvalStatus = $designer->is_trusted ? 'approved' : 'pending';

            // Tags are already an array from validation
            $tags = !empty($validated['tags'])
                ? array_filter(array_map(fn($t) => \App\Models\DropdownOption::toEnglish(trim($t), 'marketplace_tag'), $validated['tags']))
                : [];

            // Handle image path
            $imagePath = null;
            if (!empty($validated['image_path'])) {
                $path = str_replace(['../', '..\\', './'], '', $validated['image_path']);
                $path = ltrim($path, '/\\');

                // Move from temp to permanent storage if needed
                if (strpos($path, 'uploads/temp/') === 0) {
                    $imageController = new Auth\ImageUploadController();
                    $permanentPath = $imageController->moveToPermStorage($path, 'marketplace', $designer->id);
                    if (!empty($permanentPath)) {
                        $imagePath = $permanentPath;
                    }
                } else if (Storage::disk('public')->exists($path)) {
                    $imagePath = $path;
                }
            }

            $post = MarketplacePost::create([
                'designer_id' => $designer->id,
                'title' => strip_tags($validated['title']),
                'description' => strip_tags($validated['description']),
                'category' => \App\Models\DropdownOption::toEnglish(strip_tags($validated['category']), 'marketplace_category'),
                'type' => $validated['type'],
                'tags' => $tags,
                'image' => $imagePath,
                'approval_status' => $approvalStatus,
                'approved_at' => $approvalStatus === 'approved' ? now() : null,
            ]);

            // If auto-approved, send subscription notifications
            if ($approvalStatus === 'approved') {
                try {
                    NotificationSubscriptionService::notifyOnContentApproved($post);
                } catch (\Exception $e) {
                    \Log::error('Failed to send subscription notifications for marketplace post', [
                        'post_id' => $post->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $approvalStatus === 'approved'
                    ? 'Marketplace post created successfully!'
                    : 'Marketplace post created and pending approval.',
                'post' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'category' => $post->category,
                    'type' => $post->type,
                    'tags' => $post->tags,
                    'image' => $post->image ? url('media/' . $post->image) : null,
                    'approval_status' => $post->approval_status,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Marketplace post creation failed', [
                'designer_id' => auth('designer')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the post.'
            ], 500);
        }
    }

    /**
     * Update the specified marketplace post (resets approval status to pending unless trusted).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $locale, $id)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $post = MarketplacePost::where('id', $id)
                ->where('designer_id', $designer->id)
                ->firstOrFail();

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
                'category' => 'required|string|max:100',
                'type' => 'required|string|in:service,collaboration,showcase,opportunity',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:100',
                'image_path' => 'nullable|string|max:500',
            ]);

            // Tags are already an array from validation
            $tags = !empty($validated['tags'])
                ? array_filter(array_map(fn($t) => \App\Models\DropdownOption::toEnglish(trim($t), 'marketplace_tag'), $validated['tags']))
                : [];

            // Handle image path
            $imagePath = $post->image; // Keep existing if not changed
            if ($request->has('image_path')) {
                if (empty($validated['image_path'])) {
                    $imagePath = null;
                } else {
                    $path = str_replace(['../', '..\\', './'], '', $validated['image_path']);
                    $path = ltrim($path, '/\\');

                    if (strpos($path, 'uploads/temp/') === 0) {
                        $imageController = new Auth\ImageUploadController();
                        $permanentPath = $imageController->moveToPermStorage($path, 'marketplace', $designer->id);
                        if (!empty($permanentPath)) {
                            $imagePath = $permanentPath;
                        }
                    } else if (Storage::disk('public')->exists($path)) {
                        $imagePath = $path;
                    }
                }
            }

            // Reset to pending if content changed (unless trusted user)
            $newApprovalStatus = $designer->is_trusted ? 'approved' : 'pending';

            $post->update([
                'title' => strip_tags($validated['title']),
                'description' => strip_tags($validated['description']),
                'category' => \App\Models\DropdownOption::toEnglish(strip_tags($validated['category']), 'marketplace_category'),
                'type' => $validated['type'],
                'tags' => $tags,
                'image' => $imagePath,
                'approval_status' => $newApprovalStatus,
                'rejection_reason' => null,
                'approved_at' => $newApprovalStatus === 'approved' ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => $newApprovalStatus === 'approved'
                    ? 'Marketplace post updated successfully!'
                    : 'Marketplace post updated and pending approval.',
                'post' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'category' => $post->category,
                    'type' => $post->type,
                    'tags' => $post->tags,
                    'image' => $post->image ? url('media/' . $post->image) : null,
                    'approval_status' => $post->approval_status,
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found or you do not have permission to edit it.'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Marketplace post update failed', [
                'designer_id' => auth('designer')->id(),
                'post_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the post.'
            ], 500);
        }
    }

    /**
     * Remove the specified marketplace post and its associated image file.
     *
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($locale, $id)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $post = MarketplacePost::where('id', $id)
                ->where('designer_id', $designer->id)
                ->firstOrFail();

            // Delete associated image if exists
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }

            $post->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Marketplace post deleted successfully.'
                ]);
            }

            return redirect()->route('profile', ['locale' => $locale])
                ->with('success', __('Marketplace post deleted successfully.'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found or you do not have permission to delete it.'
                ], 404);
            }
            return redirect()->back()->with('error', __('Post not found or you do not have permission to delete it.'));
        } catch (\Exception $e) {
            \Log::error('Marketplace post deletion failed', [
                'designer_id' => auth('designer')->id(),
                'post_id' => $id,
                'error' => $e->getMessage()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the post.'
                ], 500);
            }
            return redirect()->back()->with('error', __('An error occurred while deleting the post.'));
        }
    }

    /**
     * Get data from a product or project to pre-fill the marketplace post creation form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSourceData(Request $request, $locale)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $sourceType = $request->get('source_type');
            $sourceId = $request->get('source_id');

            if (!$sourceType || !$sourceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source type and ID are required'
                ], 400);
            }

            $data = null;

            if ($sourceType === 'product') {
                $product = Product::with('images')
                    ->where('id', $sourceId)
                    ->where('designer_id', $designer->id)
                    ->first();

                if ($product) {
                    $primaryImage = $product->images->first();
                    $data = [
                        'title' => $product->title,
                        'description' => $product->description,
                        'category' => $product->category,
                        'image' => $primaryImage ? url('media/' . $primaryImage->image_path) : null,
                        'image_path' => $primaryImage ? $primaryImage->image_path : null,
                    ];
                }
            } elseif ($sourceType === 'project') {
                $project = Project::with('images')
                    ->where('id', $sourceId)
                    ->where('designer_id', $designer->id)
                    ->first();

                if ($project) {
                    $primaryImage = $project->images->first();
                    $data = [
                        'title' => $project->title,
                        'description' => $project->description,
                        'category' => $project->category,
                        'image' => $primaryImage ? url('media/' . $primaryImage->image_path) : null,
                        'image_path' => $primaryImage ? $primaryImage->image_path : null,
                    ];
                }
            }

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source not found or you do not have access to it.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Get source data failed', [
                'designer_id' => auth('designer')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the data.'
            ], 500);
        }
    }

    /**
     * Send in-platform share notifications to a list of user IDs (max 10).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function shareToUsers(Request $request, $locale, $id)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $post = MarketplacePost::find($id);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array|max:10',
            'user_ids.*' => 'integer|exists:designers,id',
        ]);

        $sharerName = $currentDesigner->first_name . ' ' . $currentDesigner->last_name;
        $postTitle = \Illuminate\Support\Str::limit($post->title, 100);
        $sharedCount = 0;

        foreach ($validated['user_ids'] as $userId) {
            if ($userId == $currentDesigner->id) continue;

            NotificationController::createNotification(
                $userId,
                'shared_content',
                $sharerName . ' shared a post with you',
                $postTitle,
                [
                    'url' => '/' . $locale . '/marketplace/' . $post->id,
                    'post_id' => $post->id,
                    'sharer_id' => $currentDesigner->id,
                ]
            );
            $sharedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Shared with ' . $sharedCount . ' user' . ($sharedCount !== 1 ? 's' : ''),
            'shared_count' => $sharedCount,
        ]);
    }
}
