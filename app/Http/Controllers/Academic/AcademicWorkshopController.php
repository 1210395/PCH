<?php

namespace App\Http\Controllers\Academic;

use App\Models\AcademicWorkshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\NotificationSubscriptionService;

class AcademicWorkshopController extends AcademicBaseController
{
    /**
     * Display a listing of workshops.
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
     */
    public function create(Request $request, $locale)
    {
        return view('academic.workshops.create');
    }

    /**
     * Store a newly created workshop.
     */
    public function store(Request $request, $locale)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'location_type' => 'nullable|in:online,in-person,hybrid',
            'location' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'workshop_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'max_participants' => 'nullable|integer|min:1',
            'requirements' => 'nullable|array',
            'tools_provided' => 'nullable|array',
            'has_certificate' => 'boolean',
        ]);

        $validated['academic_account_id'] = $this->getAccountId();
        $validated['has_certificate'] = $request->boolean('has_certificate');

        // Auto-approve if admin setting is enabled
        $autoAcceptEnabled = \App\Models\AdminSetting::isAutoAcceptEnabled('workshops');
        $validated['approval_status'] = $autoAcceptEnabled ? 'approved' : 'pending';

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'academic_workshop_' . Str::random(16) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('academic-workshops', $filename, 'public');
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
     * Display the specified workshop.
     */
    public function show(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        return view('academic.workshops.show', compact('workshop'));
    }

    /**
     * Show the form for editing the specified workshop.
     */
    public function edit(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        return view('academic.workshops.edit', compact('workshop'));
    }

    /**
     * Update the specified workshop.
     */
    public function update(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'location_type' => 'nullable|in:online,in-person,hybrid',
            'location' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'workshop_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'max_participants' => 'nullable|integer|min:1',
            'requirements' => 'nullable|array',
            'tools_provided' => 'nullable|array',
            'has_certificate' => 'boolean',
        ]);

        $validated['has_certificate'] = $request->boolean('has_certificate');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($workshop->image) {
                Storage::disk('public')->delete($workshop->image);
            }

            $image = $request->file('image');
            $filename = 'academic_workshop_' . $id . '_' . Str::random(16) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('academic-workshops', $filename, 'public');
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
     * Remove the specified workshop.
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
