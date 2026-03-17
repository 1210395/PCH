<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin management for product catalogue entries.
 *
 * Provides list, detail, edit, image delete, approve, reject, destroy,
 * and bulk-action endpoints for the products submitted by designers.
 * Uses the HasApprovalStatus workflow via AdminBaseController helpers.
 */
class AdminProductController extends AdminBaseController
{
    /**
     * Display a listing of products with search and filters
     */
    public function index(Request $request, $locale)
    {
        $query = Product::with(['designer', 'images']);

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

        // Filter by categories (supports multiple)
        if ($categories = $request->get('categories')) {
            if (is_array($categories) && count($categories) > 0) {
                $sanitized = array_map('strip_tags', $categories);
                $query->whereIn('category', $sanitized);
            }
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['id', 'title', 'created_at', 'approval_status', 'category'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(20)->withQueryString();

        // Get categories for filter dropdown from database options
        $categories = \App\Helpers\DropdownHelper::productCategories();

        // Get pending count for badge
        $pendingCount = Product::pending()->count();

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'products' => $products,
                'categories' => $categories,
                'pending_count' => $pendingCount,
            ]);
        }

        return view('admin.products.index', compact('products', 'categories', 'pendingCount'));
    }

    /**
     * Display a single product
     */
    public function show(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid product ID', 400);
        }

        $product = Product::with(['designer', 'images', 'approvedByAdmin'])->findOrFail($id);

        if ($request->expectsJson()) {
            return $this->jsonResponse(['product' => $product]);
        }

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing a product
     */
    public function edit(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return redirect()->route('admin.products.index', ['locale' => $locale])
                ->with('error', 'Invalid product ID');
        }

        $product = Product::with(['designer', 'images'])->findOrFail($id);

        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update product details
     */
    public function update(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid product ID', 400);
        }

        $product = Product::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'featured' => 'nullable',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        // Update product fields
        $product->update([
            'title' => strip_tags($request->input('title')),
            'description' => strip_tags($request->input('description', '')),
            'category' => strip_tags($request->input('category', '')),
            'featured' => $request->input('featured') == '1' || $request->input('featured') === true,
        ]);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $currentCount = $product->images()->count();
            $maxImages = 6;
            $availableSlots = $maxImages - $currentCount;

            $files = $request->file('images');
            $uploaded = 0;

            foreach ($files as $file) {
                if ($uploaded >= $availableSlots) break;

                $filename = 'product_' . $product->id . '_' . time() . '_' . $uploaded . '.' . ($file->guessExtension() ?? $file->getClientOriginalExtension());
                $path = $file->storeAs('products', $filename, 'public');

                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'display_order' => $currentCount + $uploaded,
                    'is_primary' => ($currentCount + $uploaded) === 0,
                ]);

                $uploaded++;
            }
        }

        return $this->successResponse('Product updated successfully', $product->fresh()->load('images'));
    }

    /**
     * Delete a product image
     */
    public function deleteImage(Request $request, $locale, $id, $imageId)
    {
        if (!$this->validateId($id) || !$this->validateId($imageId)) {
            return $this->errorResponse('Invalid ID', 400);
        }

        $product = Product::findOrFail($id);
        $image = \App\Models\ProductImage::where('product_id', $id)->where('id', $imageId)->firstOrFail();

        // Delete file from storage
        Storage::disk('public')->delete($image->image_path);

        // Delete record
        $image->delete();

        return $this->successResponse('Image deleted successfully');
    }

    /**
     * Approve a product
     */
    public function approve(Request $request, $locale, $id)
    {
        return $this->approveContent(Product::class, $id, 'Product');
    }

    /**
     * Reject a product
     */
    public function reject(Request $request, $locale, $id)
    {
        return $this->rejectContent(Product::class, $id, 'Product', $request);
    }

    /**
     * Delete a product
     */
    public function destroy(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid product ID', 400);
        }

        $product = Product::with('images')->findOrFail($id);

        // Delete associated images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $product->delete();

        return $this->successResponse('Product deleted successfully');
    }

    /**
     * Bulk actions on multiple products
     */
    public function bulkAction(Request $request, $locale)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:products,id',
            'action' => 'required|in:approve,reject,delete',
            'reason' => 'nullable|string|max:500',
        ]);

        $adminId = $this->getAdminId();
        $products = Product::with('images')->whereIn('id', $validated['ids'])->get();
        $processed = 0;

        foreach ($products as $product) {
            switch ($validated['action']) {
                case 'approve':
                    $product->approve($adminId);
                    $processed++;
                    break;

                case 'reject':
                    $product->reject($adminId, $validated['reason'] ?? null);
                    $processed++;
                    break;

                case 'delete':
                    foreach ($product->images as $image) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    $product->delete();
                    $processed++;
                    break;
            }
        }

        return $this->successResponse("Bulk action completed: {$processed} products processed", [
            'processed' => $processed,
        ]);
    }
}
