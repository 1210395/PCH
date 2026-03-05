<?php

namespace App\Http\Controllers\Admin;

use App\Models\Designer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminDesignerController extends AdminBaseController
{
    /**
     * Display a listing of designers with search and filters
     */
    public function index(Request $request, $locale)
    {
        $query = Designer::with('skills');

        // Search by email, name, or ID
        if ($search = $request->get('search')) {
            $search = strip_tags($search);
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        // Filter by sector
        if ($sector = $request->get('sector')) {
            $query->where('sector', strip_tags($sector));
        }

        // Filter by sub_sector
        if ($subSector = $request->get('sub_sector')) {
            $query->where('sub_sector', strip_tags($subSector));
        }

        // Filter by active status (only if a value is explicitly selected)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by trusted status (only if a value is explicitly selected)
        if ($request->filled('is_trusted')) {
            $query->where('is_trusted', $request->boolean('is_trusted'));
        }

        // Filter by admin status (only if a value is explicitly selected)
        if ($request->filled('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['id', 'name', 'email', 'created_at', 'sector'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $designers = $query->paginate(20)->withQueryString();

        // Get sectors for filter dropdown from database options
        $sectors = \App\Helpers\DropdownHelper::sectorOptions();
        $subSectors = Designer::distinct()->whereNotNull('sub_sector')->pluck('sub_sector')->filter();

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'designers' => $designers,
                'sectors' => $sectors,
                'sub_sectors' => $subSectors,
            ]);
        }

        return view('admin.designers.index', compact('designers', 'sectors', 'subSectors'));
    }

    /**
     * Display a single designer with all details
     */
    public function show(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid designer ID', 400);
        }

        $designer = Designer::with('skills')
            ->withCount([
                'products',
                'products as products_pending_count' => fn($q) => $q->where('approval_status', 'pending'),
                'products as products_approved_count' => fn($q) => $q->where('approval_status', 'approved'),
                'products as products_rejected_count' => fn($q) => $q->where('approval_status', 'rejected'),
                'projects',
                'projects as projects_pending_count' => fn($q) => $q->where('approval_status', 'pending'),
                'projects as projects_approved_count' => fn($q) => $q->where('approval_status', 'approved'),
                'projects as projects_rejected_count' => fn($q) => $q->where('approval_status', 'rejected'),
                'services',
                'services as services_pending_count' => fn($q) => $q->where('approval_status', 'pending'),
                'services as services_approved_count' => fn($q) => $q->where('approval_status', 'approved'),
                'services as services_rejected_count' => fn($q) => $q->where('approval_status', 'rejected'),
                'marketplacePosts',
                'marketplacePosts as marketplace_posts_pending_count' => fn($q) => $q->where('approval_status', 'pending'),
                'marketplacePosts as marketplace_posts_approved_count' => fn($q) => $q->where('approval_status', 'approved'),
                'marketplacePosts as marketplace_posts_rejected_count' => fn($q) => $q->where('approval_status', 'rejected'),
            ])
            ->findOrFail($id);

        // Content stats from withCount (single query instead of 16 separate ones)
        $contentStats = [
            'products' => [
                'total' => $designer->products_count,
                'pending' => $designer->products_pending_count,
                'approved' => $designer->products_approved_count,
                'rejected' => $designer->products_rejected_count,
            ],
            'projects' => [
                'total' => $designer->projects_count,
                'pending' => $designer->projects_pending_count,
                'approved' => $designer->projects_approved_count,
                'rejected' => $designer->projects_rejected_count,
            ],
            'services' => [
                'total' => $designer->services_count,
                'pending' => $designer->services_pending_count,
                'approved' => $designer->services_approved_count,
                'rejected' => $designer->services_rejected_count,
            ],
            'marketplace' => [
                'total' => $designer->marketplace_posts_count,
                'pending' => $designer->marketplace_posts_pending_count,
                'approved' => $designer->marketplace_posts_approved_count,
                'rejected' => $designer->marketplace_posts_rejected_count,
            ],
        ];

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'designer' => $designer,
                'content_stats' => $contentStats,
            ]);
        }

        return view('admin.designers.show', compact('designer', 'contentStats'));
    }

    /**
     * Show the form for editing a designer
     */
    public function edit(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return redirect()->route('admin.designers.index', ['locale' => $locale])
                ->with('error', 'Invalid designer ID');
        }

        $designer = Designer::with('skills')->findOrFail($id);

        return view('admin.designers.edit', compact('designer'));
    }

    /**
     * Update designer details
     */
    public function update(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid designer ID', 400);
        }

        $designer = Designer::findOrFail($id);

        $validated = $this->validateAndSanitize($request, [
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:100',
            'email' => 'required|email|unique:designers,email,' . $id,
            'sector' => 'nullable|string|max:100',
            'sub_sector' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'phone_number' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:100',
            'years_of_experience' => 'nullable|string|max:50',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'behance' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'show_location' => 'boolean',
            'allow_messages' => 'boolean',
            'email_marketing' => 'boolean',
            'email_notifications' => 'boolean',
            'is_active' => 'boolean',
            'is_tevet' => 'boolean',
        ]);

        // Prevent non-admin modifications to admin account status
        if ($designer->is_admin) {
            unset($validated['is_active']);
        }

        // Only allow is_tevet for manufacturers and showrooms
        if (isset($validated['is_tevet'])) {
            $sector = $validated['sector'] ?? $designer->sector;
            if (!in_array($sector, ['manufacturer', 'showroom'])) {
                unset($validated['is_tevet']);
            }
        }

        $designer->update($validated);

        return $this->successResponse('Designer updated successfully', $designer->fresh());
    }

    /**
     * Reset designer password
     */
    public function resetPassword(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid designer ID', 400);
        }

        $designer = Designer::findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $designer->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->successResponse('Password reset successfully');
    }

    /**
     * Toggle designer active status
     */
    public function toggleActive(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid designer ID', 400);
        }

        $designer = Designer::findOrFail($id);

        // Prevent deactivating own account
        if ($designer->id === $this->getAdminId()) {
            return $this->errorResponse('Cannot deactivate your own account', 403);
        }

        // Prevent deactivating other admin accounts
        if ($designer->is_admin && !$designer->is_active) {
            // Allow reactivating admins
        } elseif ($designer->is_admin) {
            return $this->errorResponse('Cannot deactivate admin accounts', 403);
        }

        $designer->is_active = !$designer->is_active;
        $designer->save();

        $status = $designer->is_active ? 'activated' : 'deactivated';
        return $this->successResponse("Designer {$status} successfully", [
            'is_active' => $designer->is_active,
        ]);
    }

    /**
     * Toggle designer trusted status
     */
    public function toggleTrusted(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid designer ID', 400);
        }

        $designer = Designer::findOrFail($id);

        $designer->is_trusted = !$designer->is_trusted;
        $designer->save();

        $status = $designer->is_trusted ? 'trusted (can bypass approval)' : 'untrusted (requires approval)';
        return $this->successResponse("Designer marked as {$status}", [
            'is_trusted' => $designer->is_trusted,
        ]);
    }

    /**
     * Delete a designer account
     */
    public function destroy(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid designer ID', 400);
        }

        $designer = Designer::findOrFail($id);

        // Prevent deleting own account
        if ($designer->id === $this->getAdminId()) {
            return $this->errorResponse('Cannot delete your own account', 403);
        }

        // Prevent deleting admin accounts
        if ($designer->is_admin) {
            return $this->errorResponse('Cannot delete admin accounts', 403);
        }

        // Delete the designer (related content will be handled by database cascades or remain orphaned)
        $designer->delete();

        return $this->successResponse('Designer deleted successfully');
    }

    /**
     * Bulk actions on multiple designers
     */
    public function bulkAction(Request $request, $locale)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:designers,id',
            'action' => 'required|in:activate,deactivate,set_trusted,unset_trusted,delete',
        ]);

        $adminId = $this->getAdminId();
        $designers = Designer::whereIn('id', $validated['ids'])->get();
        $processed = 0;
        $skipped = 0;

        foreach ($designers as $designer) {
            // Skip own account and admin accounts for dangerous actions
            if ($designer->id === $adminId) {
                $skipped++;
                continue;
            }

            if ($designer->is_admin && in_array($validated['action'], ['deactivate', 'delete'])) {
                $skipped++;
                continue;
            }

            switch ($validated['action']) {
                case 'activate':
                    $designer->is_active = true;
                    $designer->save();
                    $processed++;
                    break;

                case 'deactivate':
                    $designer->is_active = false;
                    $designer->save();
                    $processed++;
                    break;

                case 'set_trusted':
                    $designer->is_trusted = true;
                    $designer->save();
                    $processed++;
                    break;

                case 'unset_trusted':
                    $designer->is_trusted = false;
                    $designer->save();
                    $processed++;
                    break;

                case 'delete':
                    $designer->delete();
                    $processed++;
                    break;
            }
        }

        $message = "Bulk action completed: {$processed} processed";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped (protected accounts)";
        }

        return $this->successResponse($message, [
            'processed' => $processed,
            'skipped' => $skipped,
        ]);
    }
}
