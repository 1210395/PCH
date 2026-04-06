<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin management for portfolio project entries.
 *
 * Provides list, detail, edit, image delete, approve, reject, destroy,
 * and bulk-action endpoints for the projects submitted by designers.
 * Uses the HasApprovalStatus workflow via AdminBaseController helpers.
 */
class AdminProjectController extends AdminBaseController
{
    /**
     * Display a listing of projects with search and filters
     */
    public function index(Request $request, $locale)
    {
        $query = Project::with(['designer', 'images', 'category']);

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
                  ->orWhere('role', 'like', "%{$search}%")
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

        // Filter by completeness
        if ($completeness = $request->get('completeness')) {
            \App\Helpers\CompletenessHelper::applyFilter($query, 'project', $completeness);
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

        $projects = $query->paginate(20)->withQueryString();

        // Get categories for filter dropdown from database options
        $categories = \App\Helpers\DropdownHelper::projectCategories();

        // Get pending count for badge
        $pendingCount = Project::pending()->count();

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'projects' => $projects,
                'categories' => $categories,
                'pending_count' => $pendingCount,
            ]);
        }

        return view('admin.projects.index', compact('projects', 'categories', 'pendingCount'));
    }

    /**
     * Display a single project
     */
    public function show(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid project ID', 400);
        }

        $project = Project::with(['designer', 'images', 'category', 'approvedByAdmin'])->findOrFail($id);

        if ($request->expectsJson()) {
            return $this->jsonResponse(['project' => $project]);
        }

        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing a project
     */
    public function edit(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return redirect()->route('admin.projects.index', ['locale' => $locale])
                ->with('error', 'Invalid project ID');
        }

        $project = Project::with(['designer', 'images'])->findOrFail($id);

        return view('admin.projects.edit', compact('project'));
    }

    /**
     * Update project details
     */
    public function update(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid project ID', 400);
        }

        $project = Project::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'role' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'featured' => 'nullable',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        // Update project fields
        $project->update([
            'title' => strip_tags($request->input('title')),
            'description' => strip_tags($request->input('description', '')),
            'role' => strip_tags($request->input('role', '')),
            'category' => strip_tags($request->input('category', '')),
            'featured' => $request->input('featured') == '1' || $request->input('featured') === true,
        ]);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $currentCount = $project->images()->count();
            $maxImages = 6;
            $availableSlots = $maxImages - $currentCount;

            $files = $request->file('images');
            $uploaded = 0;

            foreach ($files as $file) {
                if ($uploaded >= $availableSlots) break;

                $path = ImageService::process($file, ImageService::CARD, 'projects', 'project_' . $project->id . '_' . time() . '_' . $uploaded);

                \App\Models\ProjectImage::create([
                    'project_id' => $project->id,
                    'image_path' => $path,
                    'display_order' => $currentCount + $uploaded,
                    'is_primary' => ($currentCount + $uploaded) === 0,
                ]);

                $uploaded++;
            }
        }

        return $this->successResponse('Project updated successfully', $project->fresh()->load('images'));
    }

    /**
     * Delete a project image
     */
    public function deleteImage(Request $request, $locale, $id, $imageId)
    {
        if (!$this->validateId($id) || !$this->validateId($imageId)) {
            return $this->errorResponse('Invalid ID', 400);
        }

        $project = Project::findOrFail($id);
        $image = \App\Models\ProjectImage::where('project_id', $id)->where('id', $imageId)->firstOrFail();

        // Delete file from storage
        Storage::disk('public')->delete($image->image_path);

        // Delete record
        $image->delete();

        return $this->successResponse('Image deleted successfully');
    }

    /**
     * Approve a project
     */
    public function approve(Request $request, $locale, $id)
    {
        return $this->approveContent(Project::class, $id, 'Project');
    }

    /**
     * Reject a project
     */
    public function reject(Request $request, $locale, $id)
    {
        return $this->rejectContent(Project::class, $id, 'Project', $request);
    }

    /**
     * Delete a project
     */
    public function destroy(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid project ID', 400);
        }

        $project = Project::with('images')->findOrFail($id);

        // Delete associated images from storage
        foreach ($project->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $project->delete();

        return $this->successResponse('Project deleted successfully');
    }

    /**
     * Bulk actions on multiple projects
     */
    public function bulkAction(Request $request, $locale)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:projects,id',
            'action' => 'required|in:approve,reject,delete',
            'reason' => 'nullable|string|max:500',
        ]);

        $adminId = $this->getAdminId();
        $projects = Project::with('images')->whereIn('id', $validated['ids'])->get();
        $processed = 0;

        foreach ($projects as $project) {
            switch ($validated['action']) {
                case 'approve':
                    $project->approve($adminId);
                    $processed++;
                    break;

                case 'reject':
                    $project->reject($adminId, $validated['reason'] ?? null);
                    $processed++;
                    break;

                case 'delete':
                    foreach ($project->images as $image) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    $project->delete();
                    $processed++;
                    break;
            }
        }

        return $this->successResponse("Bulk action completed: {$processed} projects processed", [
            'processed' => $processed,
        ]);
    }
}
