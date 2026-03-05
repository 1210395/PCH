<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('designer');

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort
        $sort = $request->get('sort', 'featured');
        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('downloads', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            default:
                $query->orderBy('featured', 'desc')->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);

        // Get unique categories for filter
        $categories = Product::distinct()->pluck('category');

        return view('products', compact('products', 'categories'));
    }

    public function show($locale, $id)
    {
        $product = Product::with('designer', 'images')->findOrFail($id);

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        }

        // Get related products from same category
        $relatedProducts = Product::where('category', $product->category)
            ->where('id', '!=', $id)
            ->take(4)
            ->get();

        return view('product-detail', compact('product', 'relatedProducts'));
    }

    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:255',
            'image_paths' => 'nullable|array|max:6',
            'image_paths.*' => 'nullable|string',
        ]);

        // Create product
        $product = Product::create([
            'designer_id' => auth('designer')->id(),
            'title' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price' => 0,
            'image' => '',  // Required field in database
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

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'product' => $product->load('images')
        ]);
    }

    public function update(Request $request, $locale, $id)
    {
        \Illuminate\Support\Facades\Log::info('Product update attempt', [
            'locale_param' => $locale,
            'id_param' => $id,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'inputs' => $request->all(),
            'route_params' => $request->route()->parameters()
        ]);

        $product = Product::findOrFail($id);

        // Verify the product belongs to the authenticated designer
        if ($product->designer_id !== auth('designer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:255',
            'image_paths' => 'nullable|array|max:6',
            'image_paths.*' => 'nullable|string',
        ]);

        // Update product details
        $product->update([
            'title' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
        ]);

        // Handle image updates if provided
        if ($request->has('image_paths') && is_array($request->image_paths)) {
            // Delete existing images
            foreach ($product->images as $image) {
                \Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Add new images
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

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product->load('images')
        ]);
    }

    public function destroy($locale, $id)
    {
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
