<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceComment;
use App\Models\MarketplacePost;
use Illuminate\Http\Request;

class MarketplaceCommentController extends Controller
{
    /**
     * Get comments for a marketplace post
     */
    public function index(Request $request, $locale, $postId)
    {
        try {
            $post = MarketplacePost::where('id', $postId)
                ->where('approval_status', 'approved')
                ->firstOrFail();

            $comments = MarketplaceComment::with(['designer:id,name,avatar', 'replies.designer:id,name,avatar'])
                ->where('marketplace_post_id', $postId)
                ->topLevel()
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $formattedComments = $comments->getCollection()->map(function ($comment) {
                return $this->formatComment($comment);
            });

            return response()->json([
                'success' => true,
                'comments' => $formattedComments,
                'pagination' => [
                    'current_page' => $comments->currentPage(),
                    'last_page' => $comments->lastPage(),
                    'total' => $comments->total(),
                    'has_more' => $comments->hasMorePages(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching comments', [
                'post_id' => $postId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching comments.'
            ], 500);
        }
    }

    /**
     * Store a new comment
     */
    public function store(Request $request, $locale, $postId)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to comment.'
                ], 401);
            }

            // Check if designer account is active
            if (!$designer->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not active.'
                ], 403);
            }

            $post = MarketplacePost::where('id', $postId)
                ->where('approval_status', 'approved')
                ->firstOrFail();

            $validated = $request->validate([
                'content' => 'required|string|min:1|max:1000',
                'parent_id' => 'nullable|integer|exists:marketplace_comments,id',
            ]);

            // If replying, verify parent comment belongs to this post
            if (!empty($validated['parent_id'])) {
                $parentComment = MarketplaceComment::where('id', $validated['parent_id'])
                    ->where('marketplace_post_id', $postId)
                    ->first();

                if (!$parentComment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parent comment not found.'
                    ], 404);
                }
            }

            $comment = MarketplaceComment::create([
                'marketplace_post_id' => $postId,
                'designer_id' => $designer->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'content' => strip_tags($validated['content']),
            ]);

            // Update comments count on the post
            $post->increment('comments_count');

            // Load the designer relationship for the response
            $comment->load('designer:id,name,avatar');

            return response()->json([
                'success' => true,
                'message' => 'Comment posted successfully.',
                'comment' => $this->formatComment($comment)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating comment', [
                'designer_id' => auth('designer')->id(),
                'post_id' => $postId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while posting the comment.'
            ], 500);
        }
    }

    /**
     * Update a comment
     */
    public function update(Request $request, $locale, $postId, $commentId)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to edit comments.'
                ], 401);
            }

            $comment = MarketplaceComment::where('id', $commentId)
                ->where('marketplace_post_id', $postId)
                ->where('designer_id', $designer->id)
                ->firstOrFail();

            $validated = $request->validate([
                'content' => 'required|string|min:1|max:1000',
            ]);

            $comment->update([
                'content' => strip_tags($validated['content']),
                'is_edited' => true,
            ]);

            $comment->load('designer:id,name,avatar');

            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully.',
                'comment' => $this->formatComment($comment)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found or you do not have permission to edit it.'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating comment', [
                'designer_id' => auth('designer')->id(),
                'comment_id' => $commentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the comment.'
            ], 500);
        }
    }

    /**
     * Delete a comment
     */
    public function destroy($locale, $postId, $commentId)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to delete comments.'
                ], 401);
            }

            $comment = MarketplaceComment::where('id', $commentId)
                ->where('marketplace_post_id', $postId)
                ->firstOrFail();

            // Check if user owns the comment or owns the post
            $post = MarketplacePost::find($postId);
            $isCommentOwner = $comment->designer_id === $designer->id;
            $isPostOwner = $post && $post->designer_id === $designer->id;

            if (!$isCommentOwner && !$isPostOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this comment.'
                ], 403);
            }

            // Count replies that will be deleted (for decrementing post comment count)
            $repliesCount = $comment->replies()->count();

            // Delete comment (replies will be deleted via cascade)
            $comment->delete();

            // Update comments count on the post (parent + replies)
            if ($post) {
                $post->decrement('comments_count', 1 + $repliesCount);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting comment', [
                'designer_id' => auth('designer')->id(),
                'comment_id' => $commentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the comment.'
            ], 500);
        }
    }

    /**
     * Format a comment for JSON response
     */
    private function formatComment($comment)
    {
        $formatted = [
            'id' => $comment->id,
            'content' => $comment->content,
            'is_edited' => $comment->is_edited,
            'created_at' => $comment->created_at->toISOString(),
            'created_at_human' => $comment->created_at->diffForHumans(),
            'designer' => [
                'id' => $comment->designer->id,
                'name' => $comment->designer->name,
                'avatar' => $comment->designer->avatar
                    ? url('media/' . $comment->designer->avatar)
                    : null,
            ],
            'is_owner' => auth('designer')->check() && auth('designer')->id() === $comment->designer_id,
            'replies' => [],
        ];

        // Format replies if loaded
        if ($comment->relationLoaded('replies') && $comment->replies->count() > 0) {
            $formatted['replies'] = $comment->replies->map(function ($reply) {
                return [
                    'id' => $reply->id,
                    'content' => $reply->content,
                    'is_edited' => $reply->is_edited,
                    'created_at' => $reply->created_at->toISOString(),
                    'created_at_human' => $reply->created_at->diffForHumans(),
                    'designer' => [
                        'id' => $reply->designer->id,
                        'name' => $reply->designer->name,
                        'avatar' => $reply->designer->avatar
                            ? url('media/' . $reply->designer->avatar)
                            : null,
                    ],
                    'is_owner' => auth('designer')->check() && auth('designer')->id() === $reply->designer_id,
                ];
            });
        }

        return $formatted;
    }
}
