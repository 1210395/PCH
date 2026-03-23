<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\NotificationController;
use App\Services\NotificationSubscriptionService;

/**
 * Manages public product listings, detail pages, CRUD for authenticated designers, and likes.
 * Logged-in owners can view their own pending/rejected products alongside the approved public feed.
 */
class ProductController extends Controller
{
    /**
     * Show the paginated product listing with filtering, search, and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Validate and sanitize input
        $validated = $request->validate([
            'category' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:featured,newest,popular,rating',
        ]);

        $query = Product::with('designer', 'images');

        // Filter by language based on current locale
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            $query->whereRaw("title REGEXP '[ء-ي]'");
        } else {
            $query->whereRaw("title NOT REGEXP '[ء-ي]'");
        }

        // Filter by approval status - show approved content + own pending/rejected content
        // Also filter out products from inactive or admin accounts (unless viewing own)
        $currentDesignerId = auth('designer')->id();
        if ($currentDesignerId) {
            $query->where(function ($q) use ($currentDesignerId) {
                $q->where(function($inner) {
                    $inner->where('approval_status', 'approved')
                          ->whereHas('designer', function($d) {
                              $d->where('is_admin', false)->where('is_active', true);
                          });
                })->orWhere('designer_id', $currentDesignerId);
            });
        } else {
            $query->where('approval_status', 'approved')
                  ->whereHas('designer', function($d) {
                      $d->where('is_admin', false)->where('is_active', true);
                  });
        }

        // Filter by category (with XSS protection)
        if (!empty($validated['category']) && $validated['category'] !== 'all') {
            $category = strip_tags($validated['category']);
            $query->where('category', $category);
        }

        // Search using FULLTEXT index for better performance
        if (!empty($validated['search'])) {
            $searchTerm = strip_tags($validated['search']);
            $query->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerm . '*']);
        }

        // Sort (whitelisted values only)
        $sort = $validated['sort'] ?? 'featured';
        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('likes_count', 'desc');
                break;
            default:
                $query->orderBy('featured', 'desc')->orderBy('created_at', 'desc');
        }

        $products = $query->simplePaginate(12)->withQueryString();

        // Get categories for filter dropdown from admin CMS lookups
        $categories = \App\Helpers\DropdownHelper::productCategories();

        return view('products', compact('products', 'categories'));
    }

    /**
     * Show a single product detail page; returns JSON for AJAX requests.
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

        $product = Product::with('designer', 'images')->findOrFail($id);

        // Check if user can view this product (approved OR owner)
        $currentDesignerId = auth('designer')->id();
        if ($product->approval_status !== 'approved' && $product->designer_id !== $currentDesignerId) {
            abort(404);
        }

        // Increment view count only if viewer is not the creator
        $currentDesignerId = auth('designer')->id();
        if (!$currentDesignerId || $currentDesignerId !== $product->designer_id) {
            $product->increment('views_count');

            // Send notification to the product owner
            NotificationController::createNotification(
                $product->designer_id,
                'product_view',
                'Someone viewed your product!',
                'Your product "' . substr($product->title, 0, 30) . '" is getting attention!'
            );
        }

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        }

        // Get related products from same category
        $relatedProducts = Product::with(['images', 'designer:id,name,avatar'])
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->where('approval_status', 'approved')
            ->limit(4)
            ->get();

        return view('product-detail', compact('product', 'relatedProducts'));
    }

    /**
     * Toggle like on a product
     */
    public function toggleLike($locale, $id)
    {
        $designer = auth('designer')->user();

        if (!$designer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $product = Product::findOrFail($id);

        $existingLike = \App\Models\Like::where('designer_id', $designer->id)
            ->where('likeable_type', 'App\Models\Product')
            ->where('likeable_id', $id)
            ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $product->decrement('likes_count');
            $liked = false;
        } else {
            // Like
            \App\Models\Like::create([
                'designer_id' => $designer->id,
                'likeable_type' => 'App\Models\Product',
                'likeable_id' => $id,
            ]);
            $product->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $product->likes_count
        ]);
    }

    /**
     * Create a new product with optional multiple images (moved from temp to permanent storage).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate request - allowing Unicode characters for multilingual support
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:255',
            'image_paths' => 'nullable|array|max:6',
            'image_paths.*' => 'nullable|string|max:500',
        ]);

        // Sanitize text fields to prevent XSS
        $validated['name'] = strip_tags($validated['name']);
        $validated['description'] = strip_tags($validated['description']);
        $validated['category'] = strip_tags($validated['category']);

        // Auto-approve if admin setting is enabled OR user is trusted
        $designer = auth('designer')->user();
        $autoAcceptEnabled = \App\Models\AdminSetting::isAutoAcceptEnabled('products');
        $approvalStatus = ($autoAcceptEnabled || ($designer && $designer->is_trusted)) ? 'approved' : 'pending';

        // Create product
        $product = Product::create([
            'designer_id' => auth('designer')->id(),
            'title' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'image' => '', // Default empty - actual images stored in product_images table
            'approval_status' => $approvalStatus,
        ]);

        // Handle image uploads if provided
        if ($request->has('image_paths') && is_array($request->image_paths)) {
            $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();
            foreach ($request->image_paths as $index => $tempPath) {
                $permanentPath = $imageUploader->moveToPermStorage(
                    $tempPath,
                    'product',
                    auth('designer')->id(),
                    $product->id,
                    $index + 1
                );

                if ($permanentPath) {
                    $product->images()->create([
                        'image_path' => $permanentPath,
                        'display_order' => $index,
                        'is_primary' => $index === 0 ? 1 : 0
                    ]);
                }
            }
        }

        // If auto-approved, send subscription notifications
        if ($approvalStatus === 'approved') {
            try {
                NotificationSubscriptionService::notifyOnContentApproved($product);
            } catch (\Exception $e) {
                \Log::error('Failed to send subscription notifications for product', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'product' => $product->load('images')
        ]);
    }

    /**
     * Update a product's details and reconcile the image set (deletes removed, moves new temp images).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $locale, $id)
    {
        // Validate ID parameter
        if (!is_numeric($id) || $id < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID'
            ], 400);
        }

        $product = Product::findOrFail($id);

        // Verify the product belongs to the authenticated designer
        if ($product->designer_id !== auth('designer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Validate request - allowing Unicode characters for multilingual support
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:255',
            'image_paths' => 'nullable|array|max:6',
            'image_paths.*' => 'nullable|string|max:500',
        ]);

        // Sanitize text fields to prevent XSS
        $validated['name'] = strip_tags($validated['name']);
        $validated['description'] = strip_tags($validated['description']);
        $validated['category'] = strip_tags($validated['category']);

        // Update product details
        $product->update([
            'title' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
        ]);

        // Handle image updates if provided
        if ($request->has('image_paths') && is_array($request->image_paths)) {
            // Get existing permanent image paths (without asset URL prefix)
            $existingPaths = $product->images->pluck('image_path')->toArray();

            // Normalize incoming paths to compare (remove asset URL if present) with path traversal protection
            $incomingPaths = array_map(function($path) {
                if (empty($path)) {
                    return null;
                }

                // Decode URL-encoded characters
                $path = urldecode($path);

                // If path starts with http/https (full URL from frontend), extract just the storage path
                if (preg_match('#storage/(.+)$#', $path, $matches)) {
                    $path = $matches[1];
                }

                // Path traversal protection - remove dangerous patterns
                $path = str_replace(['../', '..\\', './', '.\\'], '', $path);
                $path = ltrim($path, '/\\');

                // Ensure path only contains safe characters
                if (!preg_match('/^[a-zA-Z0-9\/_\-\.]+$/', $path)) {
                    \Log::warning('Product image path rejected by validation', ['path' => $path]);
                    return null;
                }

                return $path;
            }, $request->image_paths);

            // Filter out invalid paths
            $incomingPaths = array_filter($incomingPaths);

            \og::debug('Product update - processing images', [
                'product_id' => $product->id,
                'existing_paths' => $existingPaths,
                'incoming_paths' => array_values($incomingPaths)
            ]);

            // Delete images that are no longer in the new set (use flip for O(1) lookup)
            $incomingPathsFlipped = array_flip($incomingPaths);
            foreach ($product->images as $image) {
                if (!isset($incomingPathsFlipped[$image->image_path])) {
                    \Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            // Process new images and update display order
            $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();
            $displayOrder = 0;
            $existingPathsFlipped = array_flip($existingPaths);

            foreach ($incomingPaths as $index => $path) {
                // Check if this is an existing permanent path
                if (isset($existingPathsFlipped[$path])) {
                    // Update display order for existing image
                    $existingImage = $product->images()->where('image_path', $path)->first();
                    if ($existingImage) {
                        $existingImage->update([
                            'display_order' => $displayOrder,
                            'is_primary' => $displayOrder === 0 ? 1 : 0
                        ]);
                        $displayOrder++;
                    }
                } else {
                    // This is a new temporary upload - move to permanent storage
                    \og::debug('Product update - moving temp image', [
                        'product_id' => $product->id,
                        'temp_path' => $path,
                        'file_exists' => \Storage::disk('public')->exists($path)
                    ]);

                    $permanentPath = $imageUploader->moveToPermStorage(
                        $path,
                        'product',
                        auth('designer')->id(),
                        $product->id,
                        $displayOrder + 1
                    );

                    if ($permanentPath) {
                        $product->images()->create([
                            'image_path' => $permanentPath,
                            'display_order' => $displayOrder,
                            'is_primary' => $displayOrder === 0 ? 1 : 0
                        ]);
                        $displayOrder++;
                        \og::debug('Product image saved successfully', [
                            'product_id' => $product->id,
                            'permanent_path' => $permanentPath
                        ]);
                    } else {
                        \Log::warning('Product image move failed - no permanent path returned', [
                            'product_id' => $product->id,
                            'temp_path' => $path
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product->load('images')
        ]);
    }

    /**
     * Delete a product and all associated image files from storage.
     *
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($locale, $id)
    {
        // Validate ID parameter
        if (!is_numeric($id) || $id < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID'
            ], 400);
        }

        $product = Product::findOrFail($id);

        // Verify the product belongs to the authenticated designer
        if ($product->designer_id !== auth('designer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete all product images from storage
        foreach ($product->images as $image) {
            \Storage::disk('public')->delete($image->image_path);
        }

        // Delete product (cascade will delete images from DB)
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
