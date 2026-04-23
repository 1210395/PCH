<?php

namespace App\Http\Controllers\Admin;

use App\Models\FabLab;
use App\Models\Designer;
use App\Http\Controllers\NotificationController;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin full CRUD management for fabrication laboratory (FabLab) entries.
 *
 * FabLabs are admin-managed and do not go through an approval workflow.
 * Supports listing with filters, create, read, update, delete, and bulk actions.
 */
class AdminFabLabController extends AdminBaseController
{
    /**
     * Display a listing of FabLabs with search and filters
     */
    public function index(Request $request, $locale)
    {
        $query = FabLab::query();

        // Search by name, location, or city
        if ($search = $request->get('search')) {
            $search = strip_tags($search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by city
        if ($city = $request->get('city')) {
            $query->where('city', strip_tags($city));
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', strip_tags($type));
        }

        // Filter by verified status
        if ($request->has('verified')) {
            $query->where('verified', $request->boolean('verified'));
        }

        // Filter by completeness
        if ($completeness = $request->get('completeness')) {
            \App\Helpers\CompletenessHelper::applyFilter($query, 'fablab', $completeness);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['id', 'name', 'city', 'created_at', 'rating', 'members'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $fablabs = $query->paginate(20)->withQueryString();

        // Get cities and types from DISTINCT values in fab_labs so the
        // filters always match the actual data (the central dropdown
        // options do not reflect the free-text values seeded into fab_labs).
        $cities = \Illuminate\Support\Facades\DB::table('fab_labs')
            ->whereNotNull('city')->where('city', '!=', '')
            ->distinct()->orderBy('city')->pluck('city')->all();
        $typeValues = \Illuminate\Support\Facades\DB::table('fab_labs')
            ->whereNotNull('type')->where('type', '!=', '')
            ->distinct()->orderBy('type')->pluck('type')->all();
        $types = array_map(fn($v) => ['value' => $v, 'label' => ucwords(str_replace(['_','___'], ' ', $v))], $typeValues);

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'fablabs' => $fablabs,
                'cities' => $cities,
                'types' => $types,
            ]);
        }

        return view('admin.fablabs.index', compact('fablabs', 'cities', 'types'));
    }

    /**
     * Show the form for creating a new FabLab
     */
    public function create(Request $request, $locale)
    {
        return view('admin.fablabs.create');
    }

    /**
     * Store a newly created FabLab
     */
    public function store(Request $request, $locale)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'nullable|string',
            'city' => 'required|string',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'image' => 'nullable',
            'cover_image' => 'nullable',
            'rating' => 'nullable',
            'reviews_count' => 'nullable',
            'members' => 'nullable',
            'equipment' => 'nullable',
            'services' => 'nullable',
            'features' => 'nullable',
            'opening_hours' => 'nullable|string',
            'type' => 'nullable|string',
            'verified' => 'nullable',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
            'website' => 'nullable|string',
        ]);

        // Handle image uploads
        if ($request->hasFile('image')) {
            $validated['image'] = ImageService::process($request->file('image'), ImageService::CARD, 'fablabs', 'fablab_' . time());
        } else {
            unset($validated['image']);
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = ImageService::process($request->file('cover_image'), ImageService::BANNER, 'fablabs/covers', 'fablab_cover_' . time());
        } else {
            unset($validated['cover_image']);
        }

        // Handle JSON arrays
        if (isset($validated['equipment']) && is_string($validated['equipment'])) {
            $validated['equipment'] = json_decode($validated['equipment'], true);
        }
        if (isset($validated['services']) && is_string($validated['services'])) {
            $validated['services'] = json_decode($validated['services'], true);
        }
        if (isset($validated['features']) && is_string($validated['features'])) {
            $validated['features'] = json_decode($validated['features'], true);
        }

        // Set defaults
        $validated['verified'] = filter_var($validated['verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['rating'] = $validated['rating'] ?? 0;
        $validated['reviews_count'] = $validated['reviews_count'] ?? 0;
        $validated['members'] = $validated['members'] ?? 0;

        $fablab = FabLab::create($validated);

        // Send notification to all active users
        $this->notifyAllUsers($fablab);

        if ($request->expectsJson()) {
            return $this->successResponse('FabLab created successfully', $fablab);
        }

        return redirect()->route('admin.fablabs.index', ['locale' => $locale])
            ->with('success', 'FabLab created successfully');
    }

    /**
     * Send notification to all active users about new FabLab
     */
    private function notifyAllUsers(FabLab $fablab)
    {
        $designers = Designer::where('is_active', true)->get();

        foreach ($designers as $designer) {
            NotificationController::createNotification(
                $designer->id,
                'new_fablab',
                'New FabLab Added',
                "A new FabLab \"{$fablab->name}\" has been added in {$fablab->city}.",
                ['fablab_id' => $fablab->id]
            );
        }
    }

    /**
     * Display a single FabLab
     */
    public function show(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid FabLab ID', 400);
        }

        $fablab = FabLab::findOrFail($id);

        if ($request->expectsJson()) {
            return $this->jsonResponse(['fablab' => $fablab]);
        }

        return view('admin.fablabs.show', compact('fablab'));
    }

    /**
     * Show the form for editing a FabLab
     */
    public function edit(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            abort(404);
        }

        $fablab = FabLab::findOrFail($id);

        return view('admin.fablabs.edit', compact('fablab'));
    }

    /**
     * Update a FabLab
     */
    public function update(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid FabLab ID', 400);
        }

        $fablab = FabLab::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'nullable|string',
            'city' => 'required|string',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'image' => 'nullable',
            'cover_image' => 'nullable',
            'rating' => 'nullable',
            'reviews_count' => 'nullable',
            'members' => 'nullable',
            'equipment' => 'nullable',
            'services' => 'nullable',
            'features' => 'nullable',
            'opening_hours' => 'nullable|string',
            'type' => 'nullable|string',
            'verified' => 'nullable',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
            'website' => 'nullable|string',
        ]);

        // Handle image uploads
        if ($request->hasFile('image')) {
            // Delete old image
            if ($fablab->image) {
                Storage::disk('public')->delete($fablab->image);
            }
            $validated['image'] = ImageService::process($request->file('image'), ImageService::CARD, 'fablabs', 'fablab_' . time());
        } else {
            unset($validated['image']);
        }

        if ($request->hasFile('cover_image')) {
            // Delete old cover image
            if ($fablab->cover_image) {
                Storage::disk('public')->delete($fablab->cover_image);
            }
            $validated['cover_image'] = ImageService::process($request->file('cover_image'), ImageService::BANNER, 'fablabs/covers', 'fablab_cover_' . time());
        } else {
            unset($validated['cover_image']);
        }

        // Handle JSON arrays
        if (isset($validated['equipment']) && is_string($validated['equipment'])) {
            $validated['equipment'] = json_decode($validated['equipment'], true);
        }
        if (isset($validated['services']) && is_string($validated['services'])) {
            $validated['services'] = json_decode($validated['services'], true);
        }
        if (isset($validated['features']) && is_string($validated['features'])) {
            $validated['features'] = json_decode($validated['features'], true);
        }

        // Handle verified boolean
        if (isset($validated['verified'])) {
            $validated['verified'] = filter_var($validated['verified'], FILTER_VALIDATE_BOOLEAN);
        }

        $fablab->update($validated);

        if ($request->expectsJson()) {
            return $this->successResponse('FabLab updated successfully', $fablab->fresh());
        }

        return redirect()->route('admin.fablabs.index', ['locale' => $locale])
            ->with('success', 'FabLab updated successfully');
    }

    /**
     * Delete a FabLab
     */
    public function destroy(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid FabLab ID', 400);
        }

        $fablab = FabLab::findOrFail($id);

        // Delete associated images
        if ($fablab->image) {
            Storage::disk('public')->delete($fablab->image);
        }
        if ($fablab->cover_image) {
            Storage::disk('public')->delete($fablab->cover_image);
        }

        $fablab->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('FabLab deleted successfully');
        }

        return redirect()->route('admin.fablabs.index', ['locale' => $locale])
            ->with('success', 'FabLab deleted successfully');
    }

    /**
     * Bulk actions on multiple FabLabs
     */
    public function bulkAction(Request $request, $locale)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:fab_labs,id',
            'action' => 'required|in:verify,unverify,delete',
        ]);

        $fablabs = FabLab::whereIn('id', $validated['ids'])->get();
        $processed = 0;

        foreach ($fablabs as $fablab) {
            switch ($validated['action']) {
                case 'verify':
                    $fablab->update(['verified' => true]);
                    $processed++;
                    break;

                case 'unverify':
                    $fablab->update(['verified' => false]);
                    $processed++;
                    break;

                case 'delete':
                    if ($fablab->image) {
                        Storage::disk('public')->delete($fablab->image);
                    }
                    if ($fablab->cover_image) {
                        Storage::disk('public')->delete($fablab->cover_image);
                    }
                    $fablab->delete();
                    $processed++;
                    break;
            }
        }

        return $this->successResponse("Bulk action completed: {$processed} FabLabs processed", [
            'processed' => $processed,
        ]);
    }
}
