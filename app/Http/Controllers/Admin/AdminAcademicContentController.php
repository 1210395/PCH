<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicTraining;
use App\Models\AcademicWorkshop;
use App\Models\AcademicAnnouncement;
use Illuminate\Http\Request;

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
}
