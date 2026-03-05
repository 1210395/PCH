<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProfileRating;
use App\Models\AdminSetting;
use Illuminate\Http\Request;

class AdminProfileRatingController extends AdminBaseController
{
    /**
     * Display a listing of ratings with search and filters
     */
    public function index(Request $request, $locale)
    {
        $query = ProfileRating::with(['designer:id,name,email,avatar', 'rater:id,name,email,avatar']);

        // Filter by approval status
        if ($status = $request->get('status')) {
            $query->where('status', strip_tags($status));
        }

        // Filter by rating value
        if ($rating = $request->get('rating')) {
            $query->where('rating', (int) $rating);
        }

        // Search by designer name, rater name, or comment
        if ($search = $request->get('search')) {
            $search = strip_tags($search);
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('designer', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('rater', function ($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['id', 'rating', 'created_at', 'status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $ratings = $query->paginate(20)->withQueryString();

        // Get pending count for badge
        $pendingCount = ProfileRating::pending()->count();

        // Get auto-accept status
        $autoAcceptEnabled = AdminSetting::isAutoAcceptEnabled('ratings');

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'ratings' => $ratings,
                'pending_count' => $pendingCount,
                'auto_accept_enabled' => $autoAcceptEnabled,
            ]);
        }

        return view('admin.ratings.index', compact('ratings', 'pendingCount', 'autoAcceptEnabled'));
    }

    /**
     * Display a single rating
     */
    public function show(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid rating ID', 400);
        }

        $rating = ProfileRating::with(['designer', 'rater', 'approver'])->findOrFail($id);

        if ($request->expectsJson()) {
            return $this->jsonResponse(['rating' => $rating]);
        }

        return view('admin.ratings.show', compact('rating'));
    }

    /**
     * Approve a rating
     */
    public function approve(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid rating ID', 400);
        }

        $rating = ProfileRating::findOrFail($id);
        $rating->approve($this->getAdminId());

        return $this->successResponse('Rating approved successfully', $rating->fresh());
    }

    /**
     * Reject/delete a rating with a reason
     */
    public function reject(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid rating ID', 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        $rating = ProfileRating::findOrFail($id);
        $rating->reject($validated['reason'], $this->getAdminId());

        return $this->successResponse('Rating rejected and user notified', $rating->fresh());
    }

    /**
     * Bulk actions on multiple ratings
     */
    public function bulkAction(Request $request, $locale)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:profile_ratings,id',
            'action' => 'required|in:approve,reject',
            'reason' => 'required_if:action,reject|nullable|string|min:10|max:500',
        ]);

        $adminId = $this->getAdminId();
        $ratings = ProfileRating::whereIn('id', $validated['ids'])->get();
        $processed = 0;

        foreach ($ratings as $rating) {
            switch ($validated['action']) {
                case 'approve':
                    $rating->approve($adminId);
                    $processed++;
                    break;

                case 'reject':
                    $rating->reject($validated['reason'], $adminId);
                    $processed++;
                    break;
            }
        }

        return $this->successResponse("Bulk action completed: {$processed} ratings processed", [
            'processed' => $processed,
        ]);
    }

    /**
     * Toggle auto-accept setting for ratings
     */
    public function toggleAutoAccept(Request $request, $locale)
    {
        $newValue = AdminSetting::toggle('auto_accept_ratings', $this->getAdminId());

        return $this->successResponse(
            $newValue ? 'Auto-accept for ratings is now enabled' : 'Auto-accept for ratings is now disabled',
            ['auto_accept_enabled' => $newValue]
        );
    }

    /**
     * Get statistics for dashboard
     */
    public function stats(Request $request, $locale)
    {
        $stats = [
            'total' => ProfileRating::count(),
            'pending' => ProfileRating::pending()->count(),
            'approved' => ProfileRating::approved()->count(),
            'rejected' => ProfileRating::rejected()->count(),
            'average_rating' => round(ProfileRating::approved()->avg('rating') ?? 0, 1),
            'recent_ratings' => ProfileRating::with(['designer:id,name,avatar', 'rater:id,name,avatar'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return $this->jsonResponse($stats);
    }
}
