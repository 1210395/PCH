<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ImageService;

/**
 * Admin CRUD management for academic institution accounts.
 *
 * Handles listing, create, read, update, delete, toggle active status,
 * and password reset for AcademicAccount records (universities, colleges,
 * TVET centres, and training centres).
 */
class AdminAcademicAccountController extends AdminBaseController
{
    /**
     * Display a listing of academic accounts.
     */
    public function index(Request $request, $locale)
    {
        $query = AcademicAccount::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filter by institution type
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('institution_type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by completeness
        if ($completeness = $request->get('completeness')) {
            \App\Helpers\CompletenessHelper::applyFilter($query, 'academic_account', $completeness);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['id', 'name', 'email', 'institution_type', 'city', 'created_at', 'is_active'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $accounts = $query->withCount(['trainings', 'workshops', 'announcements'])
                         ->paginate(15)
                         ->withQueryString();

        // Stats - single query with conditional counts
        $stats = AcademicAccount::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN institution_type = 'university' THEN 1 ELSE 0 END) as universities,
            SUM(CASE WHEN institution_type = 'tvet' THEN 1 ELSE 0 END) as tvets,
            SUM(CASE WHEN institution_type = 'ebdc' THEN 1 ELSE 0 END) as ebdcs
        ")->first()->toArray();

        return view('admin.academic-accounts.index', compact('accounts', 'stats'));
    }

    /**
     * Show the form for creating a new academic account.
     */
    public function create(Request $request, $locale)
    {
        return view('admin.academic-accounts.create');
    }

    /**
     * Store a newly created academic account.
     */
    public function store(Request $request, $locale)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email','unique:academic_accounts,email', function ($attribute, $value, $fail) { if (\App\Models\Designer::whereRaw('LOWER(email) = ?', [strtolower($value)])->exists()) { $fail(__('This email is already registered as a designer account.')); } }],
            'password' => 'required|string|min:8',
            'institution_type' => 'required|in:university,tvet,college,ebdc,other',
            'description' => 'nullable|string|max:2000',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = ImageService::process($request->file('logo'), ImageService::SQUARE, 'academic-accounts', 'academic_' . time() . '_logo');
            $validated['logo'] = $path;
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            $path = ImageService::process($request->file('banner'), ImageService::BANNER, 'academic-accounts', 'academic_' . time() . '_banner');
            $validated['banner'] = $path;
        }

        $account = AcademicAccount::create($validated);

        if ($request->expectsJson()) {
            return $this->successResponse('Academic account created successfully', ['id' => $account->id]);
        }

        return redirect()->route('admin.academic-accounts.show', ['locale' => $locale, 'id' => $account->id])
                        ->with('success', 'Academic account created successfully');
    }

    /**
     * Display the specified academic account.
     */
    public function show(Request $request, $locale, $id)
    {
        $account = AcademicAccount::with([
            'trainings' => function ($q) {
                $q->latest()->limit(5);
            },
            'workshops' => function ($q) {
                $q->latest()->limit(5);
            },
            'announcements' => function ($q) {
                $q->latest()->limit(5);
            }
        ])->findOrFail($id);

        // Content stats
        $contentStats = [
            'trainings' => [
                'total' => $account->trainings()->count(),
                'pending' => $account->trainings()->pending()->count(),
                'approved' => $account->trainings()->approved()->count(),
                'rejected' => $account->trainings()->rejected()->count(),
            ],
            'workshops' => [
                'total' => $account->workshops()->count(),
                'pending' => $account->workshops()->pending()->count(),
                'approved' => $account->workshops()->approved()->count(),
                'rejected' => $account->workshops()->rejected()->count(),
            ],
            'announcements' => [
                'total' => $account->announcements()->count(),
                'pending' => $account->announcements()->pending()->count(),
                'approved' => $account->announcements()->approved()->count(),
                'rejected' => $account->announcements()->rejected()->count(),
            ],
        ];

        return view('admin.academic-accounts.show', compact('account', 'contentStats'));
    }

    /**
     * Show the form for editing the specified academic account.
     */
    public function edit(Request $request, $locale, $id)
    {
        $account = AcademicAccount::findOrFail($id);

        return view('admin.academic-accounts.edit', compact('account'));
    }

    /**
     * Update the specified academic account.
     */
    public function update(Request $request, $locale, $id)
    {
        $account = AcademicAccount::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email','unique:academic_accounts,email,' . $id, function ($attribute, $value, $fail) { if (\App\Models\Designer::whereRaw('LOWER(email) = ?', [strtolower($value)])->exists()) { $fail(__('This email is already registered as a designer account.')); } }],
            'institution_type' => 'required|in:university,tvet,college,ebdc,other',
            'description' => 'nullable|string|max:2000',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($account->logo) {
                Storage::disk('public')->delete($account->logo);
            }

            $path = ImageService::process($request->file('logo'), ImageService::SQUARE, 'academic-accounts', 'academic_' . time() . '_logo');
            $validated['logo'] = $path;
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            // Delete old banner
            if ($account->banner) {
                Storage::disk('public')->delete($account->banner);
            }

            $path = ImageService::process($request->file('banner'), ImageService::BANNER, 'academic-accounts', 'academic_' . time() . '_banner');
            $validated['banner'] = $path;
        }

        $account->update($validated);

        if ($request->expectsJson()) {
            return $this->successResponse('Academic account updated successfully');
        }

        return redirect()->route('admin.academic-accounts.show', ['locale' => $locale, 'id' => $id])
                        ->with('success', 'Academic account updated successfully');
    }

    /**
     * Remove the specified academic account.
     */
    public function destroy(Request $request, $locale, $id)
    {
        $account = AcademicAccount::findOrFail($id);

        // Delete logo if exists
        if ($account->logo) {
            Storage::disk('public')->delete($account->logo);
        }

        // Delete banner if exists
        if ($account->banner) {
            Storage::disk('public')->delete($account->banner);
        }

        // Note: Consider what happens to related content (trainings, workshops, announcements)
        // For now, we'll let them cascade delete via foreign keys or leave orphaned

        $account->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('Academic account deleted successfully');
        }

        return redirect()->route('admin.academic-accounts.index', ['locale' => $locale])
                        ->with('success', 'Academic account deleted successfully');
    }

    /**
     * Toggle the active status of an academic account.
     */
    public function toggleActive(Request $request, $locale, $id)
    {
        $account = AcademicAccount::findOrFail($id);
        $account->is_active = !$account->is_active;
        $account->save();

        $status = $account->is_active ? 'activated' : 'deactivated';

        if ($request->expectsJson()) {
            return $this->successResponse("Academic account {$status} successfully", [
                'is_active' => $account->is_active
            ]);
        }

        return back()->with('success', "Academic account {$status} successfully");
    }

    /**
     * Reset the password for an academic account.
     */
    public function resetPassword(Request $request, $locale, $id)
    {
        $account = AcademicAccount::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $account->password = Hash::make($request->password);
        $account->save();

        if ($request->expectsJson()) {
            return $this->successResponse('Password reset successfully');
        }

        return back()->with('success', 'Password reset successfully');
    }
}
