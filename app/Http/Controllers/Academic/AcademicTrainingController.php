<?php

namespace App\Http\Controllers\Academic;

use App\Models\AcademicTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\NotificationSubscriptionService;

/**
 * Manages CRUD operations for academic trainings belonging to the authenticated institution.
 * New trainings are submitted as pending and must be approved by an admin before appearing publicly,
 * unless the admin auto-accept setting is enabled. Rejected items revert to pending on edit.
 */
class AcademicTrainingController extends AcademicBaseController
{
    /**
     * Display a paginated, filtered, and sortable listing of the account's trainings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $locale)
    {
        $accountId = $this->getAccountId();
        $query = AcademicTraining::where('academic_account_id', $accountId);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('approval_status', $request->status);
        }

        // Filter by expired
        if ($request->filled('expired')) {
            if ($request->expired === 'yes') {
                $query->expired();
            } elseif ($request->expired === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now()->toDateString());
                });
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['id', 'title', 'start_date', 'end_date', 'created_at', 'approval_status'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $trainings = $query->paginate(15)->withQueryString();

        return view('academic.trainings.index', compact('trainings'));
    }

    /**
     * Show the form for creating a new training.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\View\View
     */
    public function create(Request $request, $locale)
    {
        return view('academic.trainings.create');
    }

    /**
     * Store a newly created training; fires subscription notifications if auto-approved.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $locale)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'location_type' => 'nullable|in:online,in-person,hybrid',
            'location' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date|before_or_equal:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string|max:5000',
            'features' => 'nullable|array',
            'has_certificate' => 'boolean',
            'registration_link' => 'nullable|url|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $validated['academic_account_id'] = $this->getAccountId();
        $validated['has_certificate'] = $request->boolean('has_certificate');

        // Auto-approve if admin setting is enabled
        $autoAcceptEnabled = \App\Models\AdminSetting::isAutoAcceptEnabled('trainings');
        $validated['approval_status'] = $autoAcceptEnabled ? 'approved' : 'pending';

        // Handle requirements - convert text to array (split by newlines)
        if (!empty($validated['requirements'])) {
            $lines = array_filter(array_map('trim', explode("\n", $validated['requirements'])));
            $validated['requirements'] = array_values($lines);
        } else {
            $validated['requirements'] = null;
        }

        // Handle price - if is_free is checked, set price to null or 'Free'
        if ($request->boolean('is_free')) {
            $validated['price'] = 'Free';
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'academic_training_' . Str::random(16) . '.' . ($image->guessExtension() ?? $image->getClientOriginalExtension());
            $path = $image->storeAs('academic-trainings', $filename, 'public');
            $validated['image'] = $path;
        }

        $training = AcademicTraining::create($validated);

        // If auto-approved, send subscription notifications
        if ($validated['approval_status'] === 'approved') {
            try {
                NotificationSubscriptionService::notifyOnContentApproved($training);
            } catch (\Exception $e) {
                \Log::error('Failed to send subscription notifications for training', [
                    'training_id' => $training->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($request->expectsJson()) {
            return $this->successResponse('Training created successfully. It will be visible after admin approval.', [
                'id' => $training->id
            ]);
        }

        return redirect()->route('academic.trainings.index', ['locale' => $locale])
                        ->with('success', 'Training created successfully. It will be visible after admin approval.');
    }

    /**
     * Display the specified training (scoped to the authenticated account).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $locale, $id)
    {
        $training = AcademicTraining::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        return view('academic.trainings.show', compact('training'));
    }

    /**
     * Show the form for editing the specified training.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View
     */
    public function edit(Request $request, $locale, $id)
    {
        $training = AcademicTraining::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        return view('academic.trainings.edit', compact('training'));
    }

    /**
     * Update the specified training; resets approval_status to pending if previously rejected.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $locale, $id)
    {
        $training = AcademicTraining::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'location_type' => 'nullable|in:online,in-person,hybrid',
            'location' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date|before_or_equal:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string|max:5000',
            'features' => 'nullable|array',
            'has_certificate' => 'boolean',
            'registration_link' => 'nullable|url|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $validated['has_certificate'] = $request->boolean('has_certificate');

        // Handle requirements - convert text to array (split by newlines)
        if (!empty($validated['requirements'])) {
            $lines = array_filter(array_map('trim', explode("\n", $validated['requirements'])));
            $validated['requirements'] = array_values($lines);
        } else {
            $validated['requirements'] = null;
        }

        // Handle price - if is_free is checked, set price to null or 'Free'
        if ($request->boolean('is_free')) {
            $validated['price'] = 'Free';
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($training->image) {
                Storage::disk('public')->delete($training->image);
            }

            $image = $request->file('image');
            $filename = 'academic_training_' . $id . '_' . Str::random(16) . '.' . ($image->guessExtension() ?? $image->getClientOriginalExtension());
            $path = $image->storeAs('academic-trainings', $filename, 'public');
            $validated['image'] = $path;
        }

        // If content was rejected and now edited, reset to pending for re-review
        if ($training->isRejected()) {
            $validated['approval_status'] = 'pending';
            $validated['rejection_reason'] = null;
        }

        $training->update($validated);

        if ($request->expectsJson()) {
            return $this->successResponse('Training updated successfully');
        }

        return redirect()->route('academic.trainings.show', ['locale' => $locale, 'id' => $id])
                        ->with('success', 'Training updated successfully');
    }

    /**
     * Remove the specified training and its associated image from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $locale, $id)
    {
        $training = AcademicTraining::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        // Delete image if exists
        if ($training->image) {
            Storage::disk('public')->delete($training->image);
        }

        $training->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('Training deleted successfully');
        }

        return redirect()->route('academic.trainings.index', ['locale' => $locale])
                        ->with('success', 'Training deleted successfully');
    }
}
