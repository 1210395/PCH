<?php

namespace App\Http\Controllers;

use App\Models\MarketplacePost;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplacePost::with('designer');

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'most_liked':
                $query->orderBy('likes_count', 'desc');
                break;
            case 'most_commented':
                $query->orderBy('comments_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $posts = $query->paginate(12);

        // Get unique categories for filter
        $categories = MarketplacePost::distinct()->pluck('category');
        $types = ['service', 'collaboration', 'showcase', 'opportunity'];

        return view('marketplace', compact('posts', 'categories', 'types'));
    }

    public function show($id)
    {
        $post = MarketplacePost::with('designer')->findOrFail($id);

        // Increment view count
        $post->increment('views_count');

        // Get related posts from same category or type
        $relatedPosts = MarketplacePost::where(function ($query) use ($post) {
            $query->where('category', $post->category)
                  ->orWhere('type', $post->type);
        })
        ->where('id', '!=', $id)
        ->take(4)
        ->get();

        return view('post-detail', compact('post', 'relatedPosts'));
    }
}
