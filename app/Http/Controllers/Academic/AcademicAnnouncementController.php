<?php

namespace App\Http\Controllers\Academic;

use App\Models\AcademicAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ImageService;
use App\Services\NotificationSubscriptionService;

/**
 * Manages CRUD operations for academic announcements (admissions, events, scholarships, etc.)
 * belonging to the authenticated institution. Follows the same pending/approved/rejected
 * approval workflow as trainings and workshops; expired items are filtered by expiry_date.
 */
class AcademicAnnouncementController extends AcademicBaseController
{
    /**
     * Display a paginated, filtered, and sortable listing of the account's announcements.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $locale)
    {
        $accountId = $this->getAccountId();
        $query = AcademicAnnouncement::where('academic_account_id', $accountId);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('approval_status', $request->status);
        }

        // Filter by category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by expired
        if ($request->filled('expired')) {
            if ($request->expired === 'yes') {
                $query->expired();
            } elseif ($request->expired === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>=', now()->toDateString());
                });
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['id', 'title', 'publish_date', 'expiry_date', 'created_at', 'approval_status', 'priority'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $announcements = $query->paginate(15)->withQueryString();

        return view('academic.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\View\View
     */
    public function create(Request $request, $locale)
    {
        return view('academic.announcements.create');
    }

    /**
     * Store a newly created announcement; fires subscription notifications if auto-approved.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $locale)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'category' => 'nullable|in:general,admission,event,scholarship,job,other',
            'priority' => 'nullable|in:normal,important,urgent',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'external_link' => 'nullable|url|max:255',
        ]);

        $validated['academic_account_id'] = $this->getAccountId();
        $validated['category'] = $validated['category'] ?? 'general';
        $validated['priority'] = $validated['priority'] ?? 'normal';

        // Auto-approve if admin setting is enabled
        $autoAcceptEnabled = \App\Models\AdminSetting::isAutoAcceptEnabled('announcements');
        $validated['approval_status'] = $autoAcceptEnabled ? 'approved' : 'pending';

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = ImageService::process($request->file('image'), ImageService::CARD, 'academic-announcements', 'academic_announcement_' . Str::random(16));
            $validated['image'] = $path;
        }

        $announcement = AcademicAnnouncement::create($validated);

        // If auto-approved, send subscription notifications
        if ($validated['approval_status'] === 'approved') {
            try {
                NotificationSubscriptionService::notifyOnContentApproved($announcement);
            } catch (\Exception $e) {
                \Log::error('Failed to send subscription notifications for announcement', [
                    'announcement_id' => $announcement->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($request->expectsJson()) {
            return $this->successResponse('Announcement created successfully. It will be visible after admin approval.', [
                'id' => $announcement->id
            ]);
        }

        return redirect()->route('academic.announcements.index', ['locale' => $locale])
                        ->with('success', 'Announcement created successfully. It will be visible after admin approval.');
    }

    /**
     * Display the specified announcement (scoped to the authenticated account).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $locale, $id)
    {
        $announcement = AcademicAnnouncement::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        return view('academic.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified announcement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View
     */
    public function edit(Request $request, $locale, $id)
    {
        $announcement = AcademicAnnouncement::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        return view('academic.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified announcement; resets approval_status to pending if previously rejected.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $locale, $id)
    {
        $announcement = AcademicAnnouncement::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'category' => 'nullable|in:general,admission,event,scholarship,job,other',
            'priority' => 'nullable|in:normal,important,urgent',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'external_link' => 'nullable|url|max:255',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($announcement->image) {
                Storage::disk('public')->delete($announcement->image);
            }

            $path = ImageService::process($request->file('image'), ImageService::CARD, 'academic-announcements', 'academic_announcement_' . Str::random(16));
            $validated['image'] = $path;
        }

        // If content was rejected and now edited, reset to pending for re-review
        if ($announcement->isRejected()) {
            $validated['approval_status'] = 'pending';
            $validated['rejection_reason'] = null;
        }

        $announcement->update($validated);

        if ($request->expectsJson()) {
            return $this->successResponse('Announcement updated successfully');
        }

        return redirect()->route('academic.announcements.show', ['locale' => $locale, 'id' => $id])
                        ->with('success', 'Announcement updated successfully');
    }

    /**
     * Remove the specified announcement and its associated image from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $locale, $id)
    {
        $announcement = AcademicAnnouncement::where('academic_account_id', $this->getAccountId())
            ->findOrFail($id);

        // Delete image if exists
        if ($announcement->image) {
            Storage::disk('public')->delete($announcement->image);
        }

        $announcement->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('Announcement deleted successfully');
        }

        return redirect()->route('academic.announcements.index', ['locale' => $locale])
                        ->with('success', 'Announcement deleted successfully');
    }
}
