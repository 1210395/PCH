<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function show($id)
    {
        $service = Service::with('designer')->findOrFail($id);

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'service' => $service
            ]);
        }

        return response()->json([
            'success' => true,
            'service' => $service
        ]);
    }

    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'category' => 'required|string|max:255',
        ]);

        // Create service
        $service = Service::create([
            'designer_id' => auth('designer')->id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'service' => $service
        ]);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        // Verify the service belongs to the authenticated designer
        if ($service->designer_id !== auth('designer')->id()) {
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
        ]);

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

    public function destroy($id)
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
