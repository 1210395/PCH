<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use Illuminate\Http\Request;

class AdminServiceController extends AdminBaseController
{
    /**
     * Display a listing of services with search and filters
     */
    public function index(Request $request, $locale)
    {
        $query = Service::with('designer');

        // Filter by approval status
        if ($status = $request->get('status')) {
            $query->where('approval_status', strip_tags($status));
        }

        // Search by name, description, or designer
        if ($search = $request->get('search')) {
            $search = strip_tags($search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
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
        $allowedSorts = ['id', 'name', 'created_at', 'approval_status', 'category'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $services = $query->paginate(20)->withQueryString();

        // Get categories for filter dropdown from database options
        $categories = \App\Helpers\DropdownHelper::serviceCategories();

        // Get pending count for badge
        $pendingCount = Service::pending()->count();

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'services' => $services,
                'categories' => $categories,
                'pending_count' => $pendingCount,
            ]);
        }

        return view('admin.services.index', compact('services', 'categories', 'pendingCount'));
    }

    /**
     * Display a single service
     */
    public function show(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid service ID', 400);
        }

        $service = Service::with(['designer', 'approvedByAdmin'])->findOrFail($id);

        if ($request->expectsJson()) {
            return $this->jsonResponse(['service' => $service]);
        }

        return view('admin.services.show', compact('service'));
    }

    /**
     * Show the form for editing a service
     */
    public function edit(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return redirect()->route('admin.services.index', ['locale' => $locale])
                ->with('error', 'Invalid service ID');
        }

        $service = Service::with('designer')->findOrFail($id);

        return view('admin.services.edit', compact('service'));
    }

    /**
     * Update service details
     */
    public function update(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid service ID', 400);
        }

        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
        ]);

        $service->update([
            'name' => strip_tags($request->input('name')),
            'description' => strip_tags($request->input('description', '')),
            'category' => strip_tags($request->input('category', '')),
        ]);

        return $this->successResponse('Service updated successfully', $service->fresh());
    }

    /**
     * Approve a service
     */
    public function approve(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid service ID', 400);
        }

        $service = Service::findOrFail($id);
        $service->approve($this->getAdminId());

        return $this->successResponse('Service approved successfully', $service->fresh());
    }

    /**
     * Reject a service
     */
    public function reject(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid service ID', 400);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $service = Service::findOrFail($id);
        $service->reject($this->getAdminId(), $validated['reason'] ?? null);

        return $this->successResponse('Service rejected', $service->fresh());
    }

    /**
     * Delete a service
     */
    public function destroy(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid service ID', 400);
        }

        $service = Service::findOrFail($id);
        $service->delete();

        return $this->successResponse('Service deleted successfully');
    }

    /**
     * Bulk actions on multiple services
     */
    public function bulkAction(Request $request, $locale)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:services,id',
            'action' => 'required|in:approve,reject,delete',
            'reason' => 'nullable|string|max:500',
        ]);

        $adminId = $this->getAdminId();
        $services = Service::whereIn('id', $validated['ids'])->get();
        $processed = 0;

        foreach ($services as $service) {
            switch ($validated['action']) {
                case 'approve':
                    $service->approve($adminId);
                    $processed++;
                    break;

                case 'reject':
                    $service->reject($adminId, $validated['reason'] ?? null);
                    $processed++;
                    break;

                case 'delete':
                    $service->delete();
                    $processed++;
                    break;
            }
        }

        return $this->successResponse("Bulk action completed: {$processed} services processed", [
            'processed' => $processed,
        ]);
    }
}
