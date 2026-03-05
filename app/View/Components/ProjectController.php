<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\DesignCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['designer', 'category']);

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%');
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
            default:
                $query->orderBy('created_at', 'desc');
        }

        $projects = $query->paginate(12);
        $categories = DesignCategory::all();

        return view('projects', compact('projects', 'categories'));
    }

    public function show($locale, $id)
    {
        $project = Project::with(['designer', 'category', 'images'])->findOrFail($id);

        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'project' => $project
            ]);
        }

        $relatedProjects = Project::where('category_id', $project->category_id)
            ->where('id', '!=', $id)
            ->take(4)
            ->get();

        return view('project-detail', compact('project', 'relatedProjects'));
    }

    public function create()
    {
        $categories = DesignCategory::all();
        return view('projects.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'role' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'image_paths' => 'nullable|array|max:6',
            'image_paths.*' => 'nullable|string',
        ]);

        // Create project
        $project = Project::create([
            'designer_id' => auth('designer')->id(),
            'category_id' => 9,  // Default category ID - required field
            'title' => $validated['title'],
            'description' => $validated['description'],
            'role' => $validated['role'],
            'category' => $validated['category'] ?? null,
            'image' => '',  // Required field in database
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

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'project' => $project->load('images')
        ]);
    }

    public function update(Request $request, $locale, $id)
    {
        $project = Project::findOrFail($id);

        // Verify the project belongs to the authenticated designer
        if ($project->designer_id !== auth('designer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'role' => 'required|string|max:255',
            'image_paths' => 'nullable|array|max:6',
            'image_paths.*' => 'nullable|string',
        ]);

        // Update project details
        $project->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'role' => $validated['role'],
        ]);

        // Handle image updates if provided
        if ($request->has('image_paths') && is_array($request->image_paths)) {
            // Delete existing images
            foreach ($project->images as $image) {
                \Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Add new images
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

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'project' => $project->load('images')
        ]);
    }

    public function destroy($locale, $id)
    {
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
