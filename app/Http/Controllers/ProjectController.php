<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\DesignCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\NotificationController;
use App\Services\NotificationSubscriptionService;

/**
 * Manages public project listings, detail pages, CRUD for authenticated designers, and likes.
 * Mirrors ProductController's approval-aware visibility: owners see all their projects; guests see only approved.
 */
class ProjectController extends Controller
{
    /**
     * Show the paginated project listing with filtering, search, and sorting.
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
            'sort' => 'nullable|string|in:latest,popular,most_liked',
        ]);

        $query = Project::with(['designer', 'category', 'images']);

        // Filter by language based on current locale
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            $query->whereRaw("title REGEXP '[ء-ي]'");
        } else {
            $query->whereRaw("title NOT REGEXP '[ء-ي]'");
        }

        // Filter by approval status - show approved content + own pending/rejected content
        // Also filter out projects from inactive or admin accounts (unless viewing own)
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
        $sort = $validated['sort'] ?? 'latest';
        switch ($sort) {
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'most_liked':
                $query->orderBy('likes_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $projects = $query->simplePaginate(12)->withQueryString();

        // Get categories for filter dropdown from admin CMS lookups
        $categories = \App\Helpers\DropdownHelper::projectCategories();

        return view('projects', compact('projects', 'categories'));
    }

    /**
     * Show a single project detail page; returns JSON for AJAX requests.
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

        $project = Project::with(['designer', 'category', 'images'])->findOrFail($id);

        // Check if user can view this project (approved OR owner)
        $currentDesignerId = auth('designer')->id();
        if ($project->approval_status !== 'approved' && $project->designer_id !== $currentDesignerId) {
            abort(404);
        }

        // Increment view count only if viewer is not the creator
        if (!$currentDesignerId || $currentDesignerId !== $project->designer_id) {
            $project->increment('views_count');

            // Send notification to the project owner
            NotificationController::createNotification(
                $project->designer_id,
                'project_view',
                'Someone viewed your project!',
                'Your project "' . substr($project->title, 0, 30) . '" is getting views!'
            );
        }

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'project' => $project
            ]);
        }

        $relatedProjects = Project::where('category_id', $project->category_id)
            ->where('id', '!=', $id)
            ->where('approval_status', 'approved')
            ->whereHas('designer', function($d) {
                $d->where('is_active', true)->where('is_admin', false);
            })
            ->with(['designer', 'images'])
            ->take(4)
            ->get();

        return view('project-detail', compact('project', 'relatedProjects'));
    }

    /**
     * Toggle like on a project
     */
    public function toggleLike($locale, $id)
    {
        $designer = auth('designer')->user();

        if (!$designer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $project = Project::findOrFail($id);

        $existingLike = \App\Models\Like::where('designer_id', $designer->id)
            ->where('likeable_type', 'App\Models\Project')
            ->where('likeable_id', $id)
            ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $project->decrement('likes_count');
            $liked = false;
        } else {
            // Like
            \App\Models\Like::create([
                'designer_id' => $designer->id,
                'likeable_type' => 'App\Models\Project',
                'likeable_id' => $id,
            ]);
            $project->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $project->likes_count
        ]);
    }

    /**
     * Show the project creation form with all design categories.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = DesignCategory::all();
        return view('projects.create', compact('categories'));
    }

    /**
     * Create a new project with optional multiple images.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate request - allowing Unicode characters for multilingual support
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'role' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'image_paths' => 'nullable|array|max:6',
            'image_paths.*' => 'nullable|string|max:500',
        ]);

        // Sanitize text fields to prevent XSS
        $validated['title'] = strip_tags($validated['title']);
        $validated['description'] = strip_tags($validated['description']);
        $validated['role'] = \App\Models\DropdownOption::toEnglish(strip_tags($validated['role']), 'project_role');
        if (!empty($validated['category'])) {
            $validated['category'] = \App\Models\DropdownOption::toEnglish(strip_tags($validated['category']), 'project_category');
        }

        // Auto-approve if admin setting is enabled OR user is trusted
        $designer = auth('designer')->user();
        $autoAcceptEnabled = \App\Models\AdminSetting::isAutoAcceptEnabled('projects');
        $approvalStatus = ($autoAcceptEnabled || ($designer && $designer->is_trusted)) ? 'approved' : 'pending';

        // Create project
        $project = Project::create([
            'designer_id' => auth('designer')->id(),
            'category_id' => $validated['category_id'] ?? \App\Models\Category::first()?->id ?? 1,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'role' => $validated['role'],
            'category' => $validated['category'] ?? null,
            'image' => '',  // Required field in database
            'approval_status' => $approvalStatus,
        ]);

        // Handle image uploads if provided
        if ($request->has('image_paths') && is_array($request->image_paths)) {
            $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();
            foreach ($request->image_paths as $index => $tempPath) {
                $permanentPath = $imageUploader->moveToPermStorage(
                    $tempPath,
                    'project',
                    auth('designer')->id(),
                    $project->id,
                    $index + 1
                );

                if ($permanentPath) {
                    $project->images()->create([
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
                NotificationSubscriptionService::notifyOnContentApproved($project);
            } catch (\Exception $e) {
                \Log::error('Failed to send subscription notifications for project', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'project' => $project->load('images')
        ]);
    }

    /**
     * Update a project's details and reconcile the image set.
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
                'message' => 'Invalid project ID'
            ], 400);
        }

        $project = Project::findOrFail($id);

        // Verify the project belongs to the authenticated designer
        if ($project->designer_id !== auth('designer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Validate request - allowing Unicode characters for multilingual support
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'category' => 'nullable|string|max:255',
            'role' => 'required|string|max:255',
            'image_paths' => 'nullable|array|max:6',
            'image_paths.*' => 'nullable|string|max:500',
        ]);

        // Sanitize text fields to prevent XSS
        $validated['title'] = strip_tags($validated['title']);
        $validated['description'] = strip_tags($validated['description']);
        $validated['role'] = \App\Models\DropdownOption::toEnglish(strip_tags($validated['role']), 'project_role');
        if (!empty($validated['category'])) {
            $validated['category'] = \App\Models\DropdownOption::toEnglish(strip_tags($validated['category']), 'project_category');
        }

        // Update project details
        $project->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'] ?? null,
            'role' => $validated['role'],
        ]);

        // Handle image updates if provided
        if ($request->has('image_paths') && is_array($request->image_paths)) {
            // Get existing permanent image paths (without asset URL prefix)
            $existingPaths = $project->images->pluck('image_path')->toArray();

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
                    \Log::warning('Project image path rejected by validation', ['path' => $path]);
                    return null;
                }

                return $path;
            }, $request->image_paths);

            // Filter out invalid paths
            $incomingPaths = array_filter($incomingPaths);

            \Log::debug('Project update - processing images', [
                'project_id' => $project->id,
                'existing_paths' => $existingPaths,
                'incoming_paths' => array_values($incomingPaths)
            ]);

            // Delete images that are no longer in the new set (use flip for O(1) lookup)
            $incomingPathsFlipped = array_flip($incomingPaths);
            foreach ($project->images as $image) {
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
                    $existingImage = $project->images()->where('image_path', $path)->first();
                    if ($existingImage) {
                        $existingImage->update([
                            'display_order' => $displayOrder,
                            'is_primary' => $displayOrder === 0 ? 1 : 0
                        ]);
                        $displayOrder++;
                    }
                } else {
                    // This is a new temporary upload - move to permanent storage
                    \Log::debug('Project update - moving temp image', [
                        'project_id' => $project->id,
                        'temp_path' => $path,
                        'file_exists' => \Storage::disk('public')->exists($path)
                    ]);

                    $permanentPath = $imageUploader->moveToPermStorage(
                        $path,
                        'project',
                        auth('designer')->id(),
                        $project->id,
                        $displayOrder + 1
                    );

                    if ($permanentPath) {
                        $project->images()->create([
                            'image_path' => $permanentPath,
                            'display_order' => $displayOrder,
                            'is_primary' => $displayOrder === 0 ? 1 : 0
                        ]);
                        $displayOrder++;
                        \Log::debug('Project image saved successfully', [
                            'project_id' => $project->id,
                            'permanent_path' => $permanentPath
                        ]);
                    } else {
                        \Log::warning('Project image move failed - no permanent path returned', [
                            'project_id' => $project->id,
                            'temp_path' => $path
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'project' => $project->load('images')
        ]);
    }

    /**
     * Delete a project and all associated image files from storage.
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
                'message' => 'Invalid project ID'
            ], 400);
        }

        $project = Project::findOrFail($id);

        // Verify the project belongs to the authenticated designer
        if ($project->designer_id !== auth('designer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete all project images from storage
        foreach ($project->images as $image) {
            \Storage::disk('public')->delete($image->image_path);
        }

        // Delete project (cascade will delete images from DB)
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully'
        ]);
    }
}
