<?php

namespace App\Http\Controllers;

use App\Models\MarketplacePost;
use App\Services\CacheService;
use Illuminate\Http\Request;

/**
 * Handles the public marketplace listing and post detail views.
 * Logged-in users can also see their own pending/rejected posts alongside approved content.
 */
class MarketplaceController extends Controller
{
    /**
     * Show the paginated marketplace listing with filters, search, and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Validate and sanitize input
        $validated = $request->validate([
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|in:service,collaboration,showcase,opportunity',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:recent,popular,views,comments',
            'tags' => 'nullable|string|max:500',
        ]);

        $query = MarketplacePost::with('designer');

        // No language filter — user-generated content should be visible in both locales

        // Filter by approval status - show approved content + own pending/rejected content
        // Also filter out posts from inactive or admin accounts (unless viewing own)
        $currentDesignerId = auth('designer')->id();
        if ($currentDesignerId) {
            $query->where(function ($q) use ($currentDesignerId) {
                $q->where(function($inner) {
                    $inner->where('approval_status', 'approved')
                          ->whereHas('designer', function($d) {
                              $d->where('is_active', true)->where('is_admin', false);
                          });
                })->orWhere('designer_id', $currentDesignerId);
            });
        } else {
            $query->where('approval_status', 'approved')
                  ->whereHas('designer', function($d) {
                      $d->where('is_active', true)->where('is_admin', false);
                  });
        }

        // Filter by category (with XSS protection + Arabic→English conversion)
        if (!empty($validated['category']) && $validated['category'] !== 'All') {
            $category = \App\Models\DropdownOption::toEnglish(strip_tags($validated['category']), 'marketplace_category');
            $query->byCategory($category);
        }

        // Filter by type (whitelisted values only)
        if (!empty($validated['type']) && $validated['type'] !== 'All Types') {
            $query->byType($validated['type']);
        }

        // Search (with XSS protection and SQL injection prevention via parameter binding)
        if (!empty($validated['search'])) {
            $searchTerm = strip_tags($validated['search']);
            $query->search($searchTerm);
        }

        // Filter by tags (convert Arabic→English for DB query)
        if (!empty($validated['tags'])) {
            $tags = array_map('strip_tags', explode(',', $validated['tags']));
            $tags = array_filter($tags);
            $tags = array_map(fn($t) => \App\Models\DropdownOption::toEnglish(trim($t), 'marketplace_tag'), $tags);
            $query->withTags($tags);
        }

        // Sort (whitelisted values only)
        $sort = $validated['sort'] ?? 'recent';
        switch ($sort) {
            case 'popular':
                $query->orderBy('likes_count', 'desc');
                break;
            case 'views':
                $query->orderBy('views_count', 'desc');
                break;
            case 'comments':
                $query->orderBy('comments_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $posts = $query->simplePaginate(12)->withQueryString();

        // Get unique categories and types for filters (cached)
        $categories = CacheService::getMarketplaceCategories();
        $allTags = CacheService::getMarketplaceTags();

        return view('marketplace', compact('posts', 'categories', 'allTags'));
    }

    /**
     * Show a single marketplace post detail page; returns JSON for AJAX requests.
     *
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($locale, $id)
    {
        // Validate ID parameter
        if (!is_numeric($id) || $id < 1) {
            abort(404);
        }

        $post = MarketplacePost::with('designer')->findOrFail($id);

        // Check if user can view this post (approved OR owner)
        $currentDesignerId = auth('designer')->id();
        if ($post->approval_status !== 'approved' && $post->designer_id !== $currentDesignerId) {
            abort(404);
        }

        // Increment view count
        $post->increment('views_count');

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'post' => $post
            ]);
        }

        // Get related posts from same category (only approved with active designers)
        $relatedPosts = MarketplacePost::where('category', $post->category)
            ->where('id', '!=', $id)
            ->where('approval_status', 'approved')
            ->whereHas('designer', function($d) {
                $d->where('is_active', true)->where('is_admin', false);
            })
            ->take(3)
            ->get();

        return view('marketplace-post-detail', compact('post', 'relatedPosts'));
    }

    /**
     * Toggle like on a marketplace post
     */
    public function toggleLike($locale, $id)
    {
        $designer = auth('designer')->user();

        if (!$designer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $post = MarketplacePost::findOrFail($id);

        // Check if already liked
        $existingLike = \App\Models\Like::where('designer_id', $designer->id)
            ->where('likeable_type', 'App\Models\MarketplacePost')
            ->where('likeable_id', $post->id)
            ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            // Like
            \App\Models\Like::create([
                'designer_id' => $designer->id,
                'likeable_type' => 'App\Models\MarketplacePost',
                'likeable_id' => $post->id,
            ]);
            $post->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $post->likes_count ?? 0,
        ]);
    }
}
