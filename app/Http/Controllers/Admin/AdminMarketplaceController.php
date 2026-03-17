<?php

namespace App\Http\Controllers\Admin;

use App\Models\MarketplacePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin management for marketplace post listings.
 *
 * Provides list, detail, edit, approve, reject, destroy, and bulk-action
 * endpoints for the community posts submitted by designers.
 */
class AdminMarketplaceController extends AdminBaseController
{
    /**
     * Display a listing of marketplace posts with search and filters
     */
    public function index(Request $request, $locale)
    {
        $query = MarketplacePost::with('designer');

        // Filter by approval status
        if ($status = $request->get('status')) {
            $query->where('approval_status', strip_tags($status));
        }

        // Search by title, description, or designer
        if ($search = $request->get('search')) {
            $search = strip_tags($search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('designer', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by category
        if ($category = $request->get('category')) {
            $query->where('category', strip_tags($category));
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', strip_tags($type));
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['id', 'title', 'created_at', 'approval_status', 'category', 'type'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $posts = $query->paginate(20)->withQueryString();

        // Get categories and types for filter dropdowns from database options
        $categories = \App\Helpers\DropdownHelper::marketplaceCategories();
        $types = \App\Helpers\DropdownHelper::marketplaceTypes();

        // Get pending count for badge
        $pendingCount = MarketplacePost::pending()->count();

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'posts' => $posts,
                'categories' => $categories,
                'types' => $types,
                'pending_count' => $pendingCount,
            ]);
        }

        return view('admin.marketplace.index', compact('posts', 'categories', 'types', 'pendingCount'));
    }

    /**
     * Display a single marketplace post
     */
    public function show(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid post ID', 400);
        }

        $post = MarketplacePost::with(['designer', 'approvedByAdmin'])->findOrFail($id);

        if ($request->expectsJson()) {
            return $this->jsonResponse(['post' => $post]);
        }

        return view('admin.marketplace.show', compact('post'));
    }

    /**
     * Show the form for editing a marketplace post
     */
    public function edit(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            abort(404, 'Invalid post ID');
        }

        $post = MarketplacePost::with(['designer', 'approvedByAdmin'])->findOrFail($id);

        return view('admin.marketplace.edit', compact('post'));
    }

    /**
     * Update marketplace post details
     */
    public function update(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid post ID', 400);
        }

        $post = MarketplacePost::findOrFail($id);

        $validated = $this->validateAndSanitize($request, [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'category' => 'required|string|max:100',
            'type' => 'required|in:service,collaboration,showcase,opportunity',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $post->update($validated);

        return $this->successResponse('Marketplace post updated successfully', $post->fresh());
    }

    /**
     * Approve a marketplace post
     */
    public function approve(Request $request, $locale, $id)
    {
        return $this->approveContent(MarketplacePost::class, $id, 'Marketplace post');
    }

    /**
     * Reject a marketplace post
     */
    public function reject(Request $request, $locale, $id)
    {
        return $this->rejectContent(MarketplacePost::class, $id, 'Marketplace post', $request);
    }

    /**
     * Delete a marketplace post
     */
    public function destroy(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid post ID', 400);
        }

        $post = MarketplacePost::findOrFail($id);

        // Delete associated image if exists
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return $this->successResponse('Marketplace post deleted successfully');
    }

    /**
     * Bulk actions on multiple marketplace posts
     */
    public function bulkAction(Request $request, $locale)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:marketplace_posts,id',
            'action' => 'required|in:approve,reject,delete',
            'reason' => 'nullable|string|max:500',
        ]);

        $adminId = $this->getAdminId();
        $posts = MarketplacePost::whereIn('id', $validated['ids'])->get();
        $processed = 0;

        foreach ($posts as $post) {
            switch ($validated['action']) {
                case 'approve':
                    $post->approve($adminId);
                    $processed++;
                    break;

                case 'reject':
                    $post->reject($adminId, $validated['reason'] ?? null);
                    $processed++;
                    break;

                case 'delete':
                    if ($post->image) {
                        Storage::disk('public')->delete($post->image);
                    }
                    $post->delete();
                    $processed++;
                    break;
            }
        }

        return $this->successResponse("Bulk action completed: {$processed} posts processed", [
            'processed' => $processed,
        ]);
    }
}
