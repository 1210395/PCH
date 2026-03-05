<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Services\NotificationSubscriptionService;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('designer');

        // Filter by approval status - show approved content + own pending/rejected content
        // Also filter out services from inactive or admin accounts (unless viewing own)
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

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'most_requested':
                $query->orderBy('requests_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $services = $query->paginate(12);

        // Get categories for filter dropdown from admin CMS lookups
        $categories = \App\Helpers\DropdownHelper::serviceCategories();

        return view('services', compact('services', 'categories'));
    }

    public function show($locale, $id)
    {
        $service = Service::with('designer')->findOrFail($id);

        // Check if user can view this service (approved OR owner)
        $currentDesignerId = auth('designer')->id();
        if ($service->approval_status !== 'approved' && $service->designer_id !== $currentDesignerId) {
            abort(404);
        }

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'service' => $service
            ]);
        }

        // Get related services from same category (only approved with active designers)
        $relatedServices = Service::with('designer:id,name,avatar')
            ->where('category', $service->category)
            ->where('id', '!=', $service->id)
            ->where('approval_status', 'approved')
            ->whereHas('designer', function($d) {
                $d->where('is_active', true)->where('is_admin', false);
            })
            ->limit(4)
            ->get();

        return view('service-detail', compact('service', 'relatedServices'));
    }

    public function store(Request $request)
    {
        // Validate request - allowing Unicode characters for multilingual support
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:255',
        ]);

        // Sanitize text fields to prevent XSS
        $validated['name'] = strip_tags($validated['name']);
        $validated['description'] = strip_tags($validated['description']);
        $validated['category'] = strip_tags($validated['category']);

        // Auto-approve if admin setting is enabled OR user is trusted
        $designer = auth('designer')->user();
        $autoAcceptEnabled = \App\Models\AdminSetting::isAutoAcceptEnabled('services');
        $approvalStatus = ($autoAcceptEnabled || ($designer && $designer->is_trusted)) ? 'approved' : 'pending';

        // Create service
        $service = Service::create([
            'designer_id' => auth('designer')->id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'approval_status' => $approvalStatus,
        ]);

        // If auto-approved, send subscription notifications
        if ($approvalStatus === 'approved') {
            try {
                NotificationSubscriptionService::notifyOnContentApproved($service);
            } catch (\Exception $e) {
                \Log::error('Failed to send subscription notifications for service', [
                    'service_id' => $service->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'service' => $service
        ]);
    }

    public function update(Request $request, $locale, $id)
    {
        $service = Service::findOrFail($id);

        // Verify the service belongs to the authenticated designer
        if ($service->designer_id !== auth('designer')->id()) {
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
        ]);

        // Sanitize text fields to prevent XSS
        $validated['name'] = strip_tags($validated['name']);
        $validated['description'] = strip_tags($validated['description']);
        $validated['category'] = strip_tags($validated['category']);

        // Update service details
        $service->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'service' => $service
        ]);
    }

    public function destroy($locale, $id)
    {
        $service = Service::findOrFail($id);

        // Verify the service belongs to the authenticated designer
        if ($service->designer_id !== auth('designer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete service
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully'
        ]);
    }
}
