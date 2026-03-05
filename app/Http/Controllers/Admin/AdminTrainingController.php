<?php

namespace App\Http\Controllers\Admin;

use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\DropdownHelper;

class AdminTrainingController extends AdminBaseController
{
    /**
     * Display a listing of trainings with search and filters
     */
    public function index(Request $request, $locale)
    {
        $query = Training::query();

        // Search by title, description, or instructor
        if ($search = $request->get('search')) {
            $search = strip_tags($search);
            $query->search($search);
        }

        // Filter by category
        if ($category = $request->get('category')) {
            $query->byCategory(strip_tags($category));
        }

        // Filter by level
        if ($level = $request->get('level')) {
            $query->byLevel(strip_tags($level));
        }

        // Filter by location type
        if ($type = $request->get('type')) {
            $query->byLocationType(strip_tags($type));
        }

        // Filter by featured (only if column exists)
        if ($request->has('featured') && $request->get('featured') !== '') {
            $query->where('featured', $request->boolean('featured'));
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['id', 'title', 'created_at', 'category', 'start_date', 'level'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $trainings = $query->paginate(20)->withQueryString();

        // Get categories for filter dropdown
        $categories = DropdownHelper::trainingCategories();

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'trainings' => $trainings,
                'categories' => $categories,
            ]);
        }

        return view('admin.trainings.index', compact('trainings', 'categories'));
    }

    /**
     * Display a single training
     */
    public function show(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid training ID', 400);
        }

        $training = Training::findOrFail($id);

        if ($request->expectsJson()) {
            return $this->jsonResponse(['training' => $training]);
        }

        return view('admin.trainings.show', compact('training'));
    }

    /**
     * Show the form for creating a new training
     */
    public function create(Request $request, $locale)
    {
        return view('admin.trainings.edit', [
            'training' => null,
            'isCreate' => true,
        ]);
    }

    /**
     * Store a newly created training
     */
    public function store(Request $request, $locale)
    {
        $validated = $this->validateTrainingRequest($request);

        // Handle image uploads
        $imagePaths = $this->handleImageUploads($request);

        // Create training
        $training = Training::create([
            'title' => strip_tags($validated['title']),
            'short_description' => strip_tags($validated['short_description'] ?? ''),
            'description' => $validated['description'] ?? '',
            'image' => $imagePaths['image'] ?? null,
            'cover_image' => $imagePaths['cover_image'] ?? null,
            'instructor_name' => strip_tags($validated['instructor_name'] ?? ''),
            'instructor_title' => strip_tags($validated['instructor_title'] ?? ''),
            'instructor_bio' => strip_tags($validated['instructor_bio'] ?? ''),
            'instructor_image' => $imagePaths['instructor_image'] ?? null,
            'category' => strip_tags($validated['category'] ?? ''),
            'level' => $validated['level'] ?? 'beginner',
            'location_type' => $validated['location_type'] ?? 'hybrid',
            'location' => strip_tags($validated['location'] ?? ''),
            'price' => strip_tags($validated['price'] ?? 'Free for members'),
            'duration' => strip_tags($validated['duration'] ?? ''),
            'schedule' => strip_tags($validated['schedule'] ?? ''),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'languages' => $validated['languages'] ?? ['Arabic', 'English'],
            'has_certificate' => $validated['has_certificate'] ?? true,
            'features' => $validated['features'] ?? [],
            'learning_outcomes' => $validated['learning_outcomes'] ?? [],
            'syllabus' => $validated['syllabus'] ?? [],
            'requirements' => $validated['requirements'] ?? [],
            'tools' => $validated['tools'] ?? [],
            'featured' => $validated['featured'] ?? false,
        ]);

        if ($request->expectsJson()) {
            return $this->successResponse('Training created successfully', $training);
        }

        return redirect()->route('admin.trainings.index', ['locale' => $locale])
            ->with('success', 'Training created successfully');
    }

    /**
     * Show the form for editing a training
     */
    public function edit(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return redirect()->route('admin.trainings.index', ['locale' => $locale])
                ->with('error', 'Invalid training ID');
        }

        $training = Training::findOrFail($id);

        return view('admin.trainings.edit', [
            'training' => $training,
            'isCreate' => false,
        ]);
    }

    /**
     * Update training details
     */
    public function update(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid training ID', 400);
        }

        $training = Training::findOrFail($id);
        $validated = $this->validateTrainingRequest($request, false);

        // Handle image uploads
        $imagePaths = $this->handleImageUploads($request, $training);

        // Update training fields
        $training->update([
            'title' => strip_tags($validated['title']),
            'short_description' => strip_tags($validated['short_description'] ?? ''),
            'description' => $validated['description'] ?? '',
            'image' => $imagePaths['image'] ?? $training->image,
            'cover_image' => $imagePaths['cover_image'] ?? $training->cover_image,
            'instructor_name' => strip_tags($validated['instructor_name'] ?? ''),
            'instructor_title' => strip_tags($validated['instructor_title'] ?? ''),
            'instructor_bio' => strip_tags($validated['instructor_bio'] ?? ''),
            'instructor_image' => $imagePaths['instructor_image'] ?? $training->instructor_image,
            'category' => strip_tags($validated['category'] ?? ''),
            'level' => $validated['level'] ?? 'beginner',
            'location_type' => $validated['location_type'] ?? 'hybrid',
            'location' => strip_tags($validated['location'] ?? ''),
            'price' => strip_tags($validated['price'] ?? 'Free for members'),
            'duration' => strip_tags($validated['duration'] ?? ''),
            'schedule' => strip_tags($validated['schedule'] ?? ''),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'languages' => $validated['languages'] ?? $training->languages,
            'has_certificate' => $validated['has_certificate'] ?? true,
            'features' => $validated['features'] ?? $training->features,
            'learning_outcomes' => $validated['learning_outcomes'] ?? $training->learning_outcomes,
            'syllabus' => $validated['syllabus'] ?? $training->syllabus,
            'requirements' => $validated['requirements'] ?? $training->requirements,
            'tools' => $validated['tools'] ?? $training->tools,
            'featured' => $validated['featured'] ?? false,
        ]);

        if ($request->expectsJson()) {
            return $this->successResponse('Training updated successfully', $training->fresh());
        }

        return redirect()->route('admin.trainings.index', ['locale' => $locale])
            ->with('success', 'Training updated successfully');
    }

    /**
     * Delete a training
     */
    public function destroy(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid training ID', 400);
        }

        $training = Training::findOrFail($id);

        // Delete associated images from storage
        $this->deleteTrainingImages($training);

        $training->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('Training deleted successfully');
        }

        return redirect()->route('admin.trainings.index', ['locale' => $locale])
            ->with('success', 'Training deleted successfully');
    }

    /**
     * Bulk actions on multiple trainings
     */
    public function bulkAction(Request $request, $locale)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:trainings,id',
            'action' => 'required|in:feature,unfeature,delete',
        ]);

        $trainings = Training::whereIn('id', $validated['ids'])->get();
        $processed = 0;

        foreach ($trainings as $training) {
            switch ($validated['action']) {
                case 'feature':
                    $training->update(['featured' => true]);
                    $processed++;
                    break;

                case 'unfeature':
                    $training->update(['featured' => false]);
                    $processed++;
                    break;

                case 'delete':
                    $this->deleteTrainingImages($training);
                    $training->delete();
                    $processed++;
                    break;
            }
        }

        return $this->successResponse("Bulk action completed: {$processed} trainings processed", [
            'processed' => $processed,
        ]);
    }

    /**
     * Validate training request data
     */
    private function validateTrainingRequest(Request $request, bool $isCreate = true): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'instructor_name' => 'nullable|string|max:255',
            'instructor_title' => 'nullable|string|max:255',
            'instructor_bio' => 'nullable|string|max:2000',
            'instructor_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'category' => 'nullable|string|max:100',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'location_type' => 'nullable|in:online,in-person,hybrid',
            'location' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'schedule' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
            'has_certificate' => 'nullable|boolean',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'learning_outcomes' => 'nullable|array',
            'learning_outcomes.*' => 'string|max:500',
            'syllabus' => 'nullable|array',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:500',
            'tools' => 'nullable|array',
            'tools.*' => 'string|max:100',
            'featured' => 'nullable',
        ]);
    }

    /**
     * Handle image uploads for training
     */
    private function handleImageUploads(Request $request, ?Training $training = null): array
    {
        $paths = [];

        if ($request->hasFile('image')) {
            if ($training && $training->image) {
                Storage::disk('public')->delete($training->image);
            }
            $file = $request->file('image');
            $filename = 'training_' . time() . '_image.' . $file->getClientOriginalExtension();
            $paths['image'] = $file->storeAs('trainings', $filename, 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($training && $training->cover_image) {
                Storage::disk('public')->delete($training->cover_image);
            }
            $file = $request->file('cover_image');
            $filename = 'training_' . time() . '_cover.' . $file->getClientOriginalExtension();
            $paths['cover_image'] = $file->storeAs('trainings', $filename, 'public');
        }

        if ($request->hasFile('instructor_image')) {
            if ($training && $training->instructor_image) {
                Storage::disk('public')->delete($training->instructor_image);
            }
            $file = $request->file('instructor_image');
            $filename = 'training_' . time() . '_instructor.' . $file->getClientOriginalExtension();
            $paths['instructor_image'] = $file->storeAs('trainings/instructors', $filename, 'public');
        }

        return $paths;
    }

    /**
     * Delete training images from storage
     */
    private function deleteTrainingImages(Training $training): void
    {
        if ($training->image) {
            Storage::disk('public')->delete($training->image);
        }
        if ($training->cover_image) {
            Storage::disk('public')->delete($training->cover_image);
        }
        if ($training->instructor_image) {
            Storage::disk('public')->delete($training->instructor_image);
        }
    }
}
