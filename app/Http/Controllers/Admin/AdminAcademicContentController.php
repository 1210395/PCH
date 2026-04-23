<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicTraining;
use App\Models\AcademicWorkshop;
use App\Models\AcademicAnnouncement;
use Illuminate\Http\Request;
use App\Models\AcademicAccount;
use App\Services\ImageService;
use Illuminate\Support\Str;


/**
 * Admin moderation of academic content submitted by academic institutions.
 *
 * Handles approve/reject/delete for academic trainings, workshops,
 * and announcements, plus a bulk-action endpoint shared across all types.
 */
class AdminAcademicContentController extends AdminBaseController
{
    // ==========================================
    // Trainings
    // ==========================================

    /**
     * Display a listing of academic trainings for approval.
     */
    public function trainings(Request $request, $locale)
    {
        $query = AcademicTraining::with('academicAccount');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('academicAccount', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
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

        // Stats
        $stats = [
            'total' => AcademicTraining::count(),
            'pending' => AcademicTraining::pending()->count(),
            'approved' => AcademicTraining::approved()->count(),
            'rejected' => AcademicTraining::rejected()->count(),
            'expired' => AcademicTraining::expired()->count(),
        ];

        return view('admin.academic-content.trainings', compact('trainings', 'stats'));
    }

    /**
     * Display the specified academic training.
     */
    public function showTraining(Request $request, $locale, $id)
    {
        $training = AcademicTraining::with(['academicAccount', 'approvedByAdmin'])->findOrFail($id);

        return view('admin.academic-content.training-show', compact('training'));
    }

    /**
     * Approve an academic training.
     */
    public function approveTraining(Request $request, $locale, $id)
    {
        $training = AcademicTraining::findOrFail($id);
        $training->approve($this->getAdminId());

        if ($request->expectsJson()) {
            return $this->successResponse('Training approved successfully');
        }

        return back()->with('success', 'Training approved successfully');
    }

    /**
     * Reject an academic training.
     */
    public function rejectTraining(Request $request, $locale, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $training = AcademicTraining::findOrFail($id);
        $training->reject($this->getAdminId(), $request->reason);

        if ($request->expectsJson()) {
            return $this->successResponse('Training rejected successfully');
        }

        return back()->with('success', 'Training rejected successfully');
    }

    /**
     * Delete an academic training.
     */
    public function deleteTraining(Request $request, $locale, $id)
    {
        $training = AcademicTraining::findOrFail($id);

        // Delete image if exists
        if ($training->image) {
            \Storage::disk('public')->delete($training->image);
        }

        $training->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('Training deleted successfully');
        }

        return redirect()->route('admin.academic-content.trainings', ['locale' => $locale])
                        ->with('success', 'Training deleted successfully');
    }

    // ==========================================
    // Workshops
    // ==========================================

    /**
     * Display a listing of academic workshops for approval.
     */
    public function workshops(Request $request, $locale)
    {
        $query = AcademicWorkshop::with('academicAccount');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('academicAccount', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
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

        // Stats
        $stats = [
            'total' => AcademicWorkshop::count(),
            'pending' => AcademicWorkshop::pending()->count(),
            'approved' => AcademicWorkshop::approved()->count(),
            'rejected' => AcademicWorkshop::rejected()->count(),
            'expired' => AcademicWorkshop::expired()->count(),
        ];

        return view('admin.academic-content.workshops', compact('workshops', 'stats'));
    }

    /**
     * Display the specified academic workshop.
     */
    public function showWorkshop(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::with(['academicAccount', 'approvedByAdmin'])->findOrFail($id);

        return view('admin.academic-content.workshop-show', compact('workshop'));
    }

    /**
     * Approve an academic workshop.
     */
    public function approveWorkshop(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::findOrFail($id);
        $workshop->approve($this->getAdminId());

        if ($request->expectsJson()) {
            return $this->successResponse('Workshop approved successfully');
        }

        return back()->with('success', 'Workshop approved successfully');
    }

    /**
     * Reject an academic workshop.
     */
    public function rejectWorkshop(Request $request, $locale, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $workshop = AcademicWorkshop::findOrFail($id);
        $workshop->reject($this->getAdminId(), $request->reason);

        if ($request->expectsJson()) {
            return $this->successResponse('Workshop rejected successfully');
        }

        return back()->with('success', 'Workshop rejected successfully');
    }

    /**
     * Delete an academic workshop.
     */
    public function deleteWorkshop(Request $request, $locale, $id)
    {
        $workshop = AcademicWorkshop::findOrFail($id);

        // Delete image if exists
        if ($workshop->image) {
            \Storage::disk('public')->delete($workshop->image);
        }

        $workshop->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('Workshop deleted successfully');
        }

        return redirect()->route('admin.academic-content.workshops', ['locale' => $locale])
                        ->with('success', 'Workshop deleted successfully');
    }

    // ==========================================
    // Announcements
    // ==========================================

    /**
     * Display a listing of academic announcements for approval.
     */
    public function announcements(Request $request, $locale)
    {
        $query = AcademicAnnouncement::with('academicAccount');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('academicAccount', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
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

        // Stats
        $stats = [
            'total' => AcademicAnnouncement::count(),
            'pending' => AcademicAnnouncement::pending()->count(),
            'approved' => AcademicAnnouncement::approved()->count(),
            'rejected' => AcademicAnnouncement::rejected()->count(),
            'expired' => AcademicAnnouncement::expired()->count(),
        ];

        return view('admin.academic-content.announcements', compact('announcements', 'stats'));
    }

    /**
     * Display the specified academic announcement.
     */
    public function showAnnouncement(Request $request, $locale, $id)
    {
        $announcement = AcademicAnnouncement::with(['academicAccount', 'approvedByAdmin'])->findOrFail($id);

        return view('admin.academic-content.announcement-show', compact('announcement'));
    }

    /**
     * Approve an academic announcement.
     */
    public function approveAnnouncement(Request $request, $locale, $id)
    {
        $announcement = AcademicAnnouncement::findOrFail($id);
        $announcement->approve($this->getAdminId());

        if ($request->expectsJson()) {
            return $this->successResponse('Announcement approved successfully');
        }

        return back()->with('success', 'Announcement approved successfully');
    }

    /**
     * Reject an academic announcement.
     */
    public function rejectAnnouncement(Request $request, $locale, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $announcement = AcademicAnnouncement::findOrFail($id);
        $announcement->reject($this->getAdminId(), $request->reason);

        if ($request->expectsJson()) {
            return $this->successResponse('Announcement rejected successfully');
        }

        return back()->with('success', 'Announcement rejected successfully');
    }

    /**
     * Delete an academic announcement.
     */
    public function deleteAnnouncement(Request $request, $locale, $id)
    {
        $announcement = AcademicAnnouncement::findOrFail($id);

        // Delete image if exists
        if ($announcement->image) {
            \Storage::disk('public')->delete($announcement->image);
        }

        $announcement->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('Announcement deleted successfully');
        }

        return redirect()->route('admin.academic-content.announcements', ['locale' => $locale])
                        ->with('success', 'Announcement deleted successfully');
    }

    // ==========================================
    // Bulk Actions
    // ==========================================

    /**
     * Handle bulk actions for academic content.
     */
    public function bulkAction(Request $request, $locale)
    {
        $request->validate([
            'type' => 'required|in:trainings,workshops,announcements',
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action' => 'required|in:approve,reject,delete',
            'reason' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        $type = $request->type;
        $ids = $request->ids;
        $action = $request->action;
        $reason = $request->reason;

        $modelClass = match($type) {
            'trainings' => AcademicTraining::class,
            'workshops' => AcademicWorkshop::class,
            'announcements' => AcademicAnnouncement::class,
        };

        $processed = 0;

        foreach ($ids as $id) {
            $item = $modelClass::find($id);
            if (!$item) continue;

            switch ($action) {
                case 'approve':
                    $item->approve($this->getAdminId());
                    $processed++;
                    break;

                case 'reject':
                    $item->reject($this->getAdminId(), $reason);
                    $processed++;
                    break;

                case 'delete':
                    $item->delete();
                    $processed++;
                    break;
            }
        }

        $actionLabel = match($action) {
            'approve' => 'approved',
            'reject' => 'rejected',
            'delete' => 'deleted',
        };

        if ($request->expectsJson()) {
            return $this->successResponse("{$processed} items {$actionLabel} successfully");
        }

        return back()->with('success', "{$processed} items {$actionLabel} successfully");
    }


    // ==========================================
    // Admin-initiated CREATE for academic content
    // (Publishes on behalf of an institution with status=approved)
    // ==========================================

    public function createTraining(Request $request, $locale)
    {
        return view('admin.academic-content.training-create');
    }

    public function storeTraining(Request $request, $locale)
    {
        $validated = $request->validate([
            
            'title'                  => 'required|string|max:255',
            'short_description'      => 'nullable|string|max:500',
            'description'            => 'nullable|string|max:5000',
            'category'               => 'nullable|string|max:100',
            'level'                  => 'nullable|in:beginner,intermediate,advanced',
            'location_type'          => 'nullable|in:online,in-person,hybrid',
            'location'               => 'nullable|string|max:255',
            'price'                  => 'nullable|string|max:100',
            'duration'               => 'nullable|string|max:100',
            'start_date'             => 'required|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'registration_deadline'  => 'nullable|date|before_or_equal:start_date',
            'max_participants'       => 'nullable|integer|min:1',
            'registration_link'      => 'nullable|url|max:500',
            'has_certificate'        => 'boolean',
            'image'                  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Always publish as Palestine Creative Hub (hardcoded)
        $validated['academic_account_id'] = \App\Models\AcademicAccount::where('name', 'Palestine Creative Hub')->value('id') ?? 15;

        $validated['has_certificate']  = $request->boolean('has_certificate');
        $validated['approval_status']  = 'approved';
        $validated['approved_by'] = $this->getAdminId();
        $validated['approved_at']      = now();

        if ($request->hasFile('image')) {
            $validated['image'] = ImageService::process(
                $request->file('image'), ImageService::CARD, 'academic-trainings',
                'academic_training_' . Str::random(16)
            );
        }

        \App\Models\AcademicTraining::create($validated);

        return redirect()
            ->route('admin.academic-content.trainings', ['locale' => $locale])
            ->with('success', __('Training created and published.'));
    }

    public function createWorkshop(Request $request, $locale)
    {
        return view('admin.academic-content.workshop-create');
    }

    public function storeWorkshop(Request $request, $locale)
    {
        $validated = $request->validate([
            
            'title'               => 'required|string|max:255',
            'short_description'   => 'nullable|string|max:500',
            'description'         => 'nullable|string|max:5000',
            'objectives'          => 'nullable|string|max:5000',
            'category'            => 'nullable|string|max:100',
            'location_type'       => 'nullable|in:online,in-person,hybrid',
            'location'            => 'nullable|string|max:255',
            'is_online'           => 'nullable|boolean',
            'instructor'          => 'nullable|string|max:255',
            'price'               => 'nullable|string|max:100',
            'is_free'             => 'nullable|boolean',
            'duration'            => 'nullable|string|max:100',
            'workshop_date'       => 'required|date',
            'start_time'          => 'nullable|date_format:H:i',
            'end_time'            => 'nullable|date_format:H:i|after:start_time',
            'max_participants'    => 'nullable|integer|min:1',
            'has_certificate'     => 'boolean',
            'registration_link'   => 'nullable|url|max:500',
        ]);

        // Always publish as Palestine Creative Hub (hardcoded)
        $validated['academic_account_id'] = \App\Models\AcademicAccount::where('name', 'Palestine Creative Hub')->value('id') ?? 15;

        $validated['is_online']       = $request->boolean('is_online');
        $validated['is_free']         = $request->boolean('is_free');
        $validated['has_certificate'] = $request->boolean('has_certificate');
        $validated['approval_status'] = 'approved';
        $validated['approved_by'] = $this->getAdminId();
        $validated['approved_at']     = now();

        \App\Models\AcademicWorkshop::create($validated);

        return redirect()
            ->route('admin.academic-content.workshops', ['locale' => $locale])
            ->with('success', __('Workshop created and published.'));
    }

    public function createAnnouncement(Request $request, $locale)
    {
        return view('admin.academic-content.announcement-create');
    }

    public function storeAnnouncement(Request $request, $locale)
    {
        $validated = $request->validate([
            
            'title'               => 'required|string|max:255',
            'content'             => 'required|string|max:10000',
            'category'            => 'nullable|in:general,admission,event,scholarship,job,other',
            'priority'            => 'nullable|in:normal,important,urgent',
            'publish_date'        => 'required|date',
            'expiry_date'         => 'nullable|date|after_or_equal:publish_date',
            'external_link'       => 'nullable|url|max:255',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Always publish as Palestine Creative Hub (hardcoded)
        $validated['academic_account_id'] = \App\Models\AcademicAccount::where('name', 'Palestine Creative Hub')->value('id') ?? 15;

        $validated['category']        = $validated['category'] ?? 'general';
        $validated['priority']        = $validated['priority'] ?? 'normal';
        $validated['approval_status'] = 'approved';
        $validated['approved_by'] = $this->getAdminId();
        $validated['approved_at']     = now();

        if ($request->hasFile('image')) {
            $validated['image'] = ImageService::process(
                $request->file('image'), ImageService::CARD, 'academic-announcements',
                'academic_announcement_' . Str::random(16)
            );
        }

        \App\Models\AcademicAnnouncement::create($validated);

        return redirect()
            ->route('admin.academic-content.announcements', ['locale' => $locale])
            ->with('success', __('Announcement created and published.'));
    }
}
