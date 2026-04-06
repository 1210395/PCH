<?php

namespace App\Http\Controllers\Academic;

use App\Models\AcademicWorkshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ImageService;
use App\Services\NotificationSubscriptionService;

/**
 * Manages CRUD operations for academic workshops belonging to the authenticated institution.
 * Follows the same pending/approved/rejected approval workflow as trainings and announcements.
 */
class AcademicWorkshopController extends AcademicBaseController
{
    /**
     * Display a paginated, filtered, and sortable listing of the account's workshops.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $locale)
    {
        $accountId = $this->getAccountId();
        $query = AcademicWorkshop::where('academic_account_id', $accountId);

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
                $query->where('workshop_date', '>=', now()->toDateString());
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['id', 'title', 'workshop_date', 'created_at', 'approval_status'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $workshops = $query->paginate(15)->withQueryString();

        return view('academic.workshops.index', compact('workshops'));
    }

    /**
     * Show the form for creating a new workshop.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\View\View
     */
    public function create(Request $request, $locale)
    {
        return view('academic.workshops.create');
    }

    /**
     * Store a newly created workshop; fires subscription notifications if auto-approved.
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
            'objectives' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'location_type' => 'nullable|in:online,in-person,hybrid',
            'location' => 'nullable|string|max:255',
            'is_online' => 'nullable|boolean',
            'instructor' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:100',
            'is_free' => 'nullable|boolean',
            'duration' => 'nullable|string|max:100',
            'workshop_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'max_participants' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string|max:5000',
            'tools_provided' => 'nullable|array',
            'has_certificate' => 'boolean',
            'registration_link' => 'nullable|url|max:500',
        ]);

        $validated['academic_account_id'] = $this->getAccountId();
        $validated['has_certificate'] = $request->boolean('has_certificate');
        $validated['is_online'] = $request->boolean('is_online');
        $validated['is_free'] = $request->boolean('is_free');

        // Convert requirements text to array (split by newlines, filter empty)
        if (isset($validated['requirements']) && is_string($validated['requirements'])) {
            $validated['requirements'] = array_values(array_filter(
                array_map('trim', preg_split('/\r?\n/', $validated['requirements'])),
                fn($line) => $line !== ''
            ));
        }

        // Auto-approve if admin setting is enabled
        $autoAcceptEnabled = \App\Models\AdminSetting::isAutoAcceptEnabled('workshops');
        $validated['approval_status'] = $autoAcceptEnabled ? 'approved' : 'pending';

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = ImageService::process($request->file('image'), ImageService::CARD, 'academic-workshops', 'academic_workshop_' . Str::random(16));
            $validated['image'] = $path;
        }

        $workshop = AcademicWorkshop::create($validated);

        // If auto-approved, send subscription notifications
        if ($validated['approval_status'] === 'approved') {
            try {
                NotificationSubscriptionService::notifyOnContentApproved($workshop);
            } catch (\Exception $e) {
                \Log::error('Failed to send subscription notifications for workshop', [
                    'workshop_id' => $workshop->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($request->expectsJson()) {
            return $this->successResponse('Workshop created successfully. It will be visible after admin approval.', [
                'id' => $workshop->id
            ]);
        }

        return redirect()->route('academic.workshops.index', ['locale' => $locale])
                        ->with('success', 'Workshop created successfully. It will be visible after admin approval.');
    }

    /**
     * Display the specified workshop (scoped to the authenticated account).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        return view('academic.workshops.show', compact('workshop'));
    }

    /**
     * Show the form for editing the specified workshop.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View
     */
    public function edit(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        return view('academic.workshops.edit', compact('workshop'));
    }

    /**
     * Update the specified workshop; resets approval_status to pending if previously rejected.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:5000',
            'objectives' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'location_type' => 'nullable|in:online,in-person,hybrid',
            'location' => 'nullable|string|max:255',
            'is_online' => 'nullable|boolean',
            'instructor' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:100',
            'is_free' => 'nullable|boolean',
            'duration' => 'nullable|string|max:100',
            'workshop_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'max_participants' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string|max:5000',
            'tools_provided' => 'nullable|array',
            'has_certificate' => 'boolean',
            'registration_link' => 'nullable|url|max:500',
        ]);

        $validated['has_certificate'] = $request->boolean('has_certificate');
        $validated['is_online'] = $request->boolean('is_online');
        $validated['is_free'] = $request->boolean('is_free');

        // Convert requirements text to array (split by newlines, filter empty)
        if (isset($validated['requirements']) && is_string($validated['requirements'])) {
            $validated['requirements'] = array_values(array_filter(
                array_map('trim', preg_split('/\r?\n/', $validated['requirements'])),
                fn($line) => $line !== ''
            ));
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($workshop->image) {
                Storage::disk('public')->delete($workshop->image);
            }

            $path = ImageService::process($request->file('image'), ImageService::CARD, 'academic-workshops', 'academic_workshop_' . Str::random(16));
            $validated['image'] = $path;
        }

        // If content was rejected and now edited, reset to pending for re-review
        if ($workshop->isRejected()) {
            $validated['approval_status'] = 'pending';
            $validated['rejection_reason'] = null;
        }

        $workshop->update($validated);

        if ($request->expectsJson()) {
            return $this->successResponse('Workshop updated successfully');
        }

        return redirect()->route('academic.workshops.show', ['locale' => $locale, 'id' => $id])
                        ->with('success', 'Workshop updated successfully');
    }

    /**
     * Remove the specified workshop and its associated image from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        // Delete image if exists
        if ($workshop->image) {
            Storage::disk('public')->delete($workshop->image);
        }

        $workshop->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('Workshop deleted successfully');
        }

        return redirect()->route('academic.workshops.index', ['locale' => $locale])
                        ->with('success', 'Workshop deleted successfully');
    }
}
