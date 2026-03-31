<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tender;
use Illuminate\Http\Request;

/**
 * Admin full CRUD management for tender / opportunity entries.
 *
 * Tenders can be created manually by admins or ingested via the Jobs.ps webhook.
 * Supports listing, create, read, update, delete, toggle visibility, and bulk actions.
 */
class AdminTenderController extends AdminBaseController
{
    /**
     * Display a listing of tenders with search and filters
     */
    public function index(Request $request, $locale)
    {
        $query = Tender::query();

        // Search by title, description, or publisher
        if ($search = $request->get('search')) {
            $search = strip_tags($search);
            $query->search($search);
        }

        // Filter by tender status (open, closing_soon, closed)
        if ($tenderStatus = $request->get('tender_status')) {
            $query->byStatus(strip_tags($tenderStatus));
        }

        // Filter by publisher type
        if ($publisherType = $request->get('publisher_type')) {
            $query->byPublisherType(strip_tags($publisherType));
        }

        // Filter by visibility
        if ($request->has('visibility')) {
            $visibility = $request->get('visibility');
            if ($visibility === 'visible') {
                $query->where('is_visible', true);
            } elseif ($visibility === 'hidden') {
                $query->where('is_visible', false);
            }
        }

        // Filter by source (api or manual)
        if ($source = $request->get('source')) {
            if ($source === 'api') {
                $query->fromApi();
            } elseif ($source === 'manual') {
                $query->manual();
            }
        }

        // Filter by completeness
        if ($completeness = $request->get('completeness')) {
            \App\Helpers\CompletenessHelper::applyFilter($query, 'tender', $completeness);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['id', 'title', 'created_at', 'deadline', 'status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $tenders = $query->paginate(20)->withQueryString();

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'tenders' => $tenders,
            ]);
        }

        return view('admin.tenders.index', compact('tenders'));
    }

    /**
     * Display a single tender
     */
    public function show(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid tender ID', 400);
        }

        $tender = Tender::findOrFail($id);

        if ($request->expectsJson()) {
            return $this->jsonResponse(['tender' => $tender]);
        }

        return view('admin.tenders.show', compact('tender'));
    }

    /**
     * Show the form for creating a new tender
     */
    public function create(Request $request, $locale)
    {
        return view('admin.tenders.edit', [
            'tender' => null,
            'isCreate' => true,
        ]);
    }

    /**
     * Store a newly created tender
     */
    public function store(Request $request, $locale)
    {
        $validated = $this->validateTenderRequest($request);

        // Create tender
        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><pre><code><table><thead><tbody><tr><th><td><div><span>';
        $description = strip_tags($validated['description'] ?? '', $allowedTags);
        $shortDesc = strip_tags($description);
        $shortDescription = mb_strlen($shortDesc) > 200 ? mb_substr($shortDesc, 0, 200) . '...' : $shortDesc;

        $createData = [
            'title' => strip_tags($validated['title']),
            'short_description' => $shortDescription,
            'description' => $description,
            'publisher' => strip_tags($validated['publisher'] ?? ''),
            'publisher_type' => $validated['publisher_type'] ?? 'other',
            'company_name' => strip_tags($validated['company_name'] ?? ''),
            'company_url' => strip_tags($validated['company_url'] ?? ''),
            'location' => strip_tags($validated['location'] ?? ''),
            'status' => $validated['status'] ?? 'open',
            'published_date' => $validated['published_date'] ?? now()->toDateString(),
            'deadline' => $validated['deadline'] ?? null,
            'source_url' => strip_tags($validated['source_url'] ?? ''),
            'is_visible' => $validated['is_visible'] ?? true,
        ];

        $tender = Tender::create($createData);

        if ($request->expectsJson()) {
            return $this->successResponse('Tender created successfully', $tender);
        }

        return redirect()->route('admin.tenders.index', ['locale' => $locale])
            ->with('success', 'Tender created successfully');
    }

    /**
     * Show the form for editing a tender
     */
    public function edit(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return redirect()->route('admin.tenders.index', ['locale' => $locale])
                ->with('error', 'Invalid tender ID');
        }

        $tender = Tender::findOrFail($id);

        return view('admin.tenders.edit', [
            'tender' => $tender,
            'isCreate' => false,
        ]);
    }

    /**
     * Update tender details
     */
    public function update(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid tender ID', 400);
        }

        $tender = Tender::findOrFail($id);
        $validated = $this->validateTenderRequest($request, false);

        // Update tender fields
        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><pre><code><table><thead><tbody><tr><th><td><div><span>';
        $description = strip_tags($validated['description'] ?? '', $allowedTags);
        $shortDesc = strip_tags($description);
        $shortDescription = mb_strlen($shortDesc) > 200 ? mb_substr($shortDesc, 0, 200) . '...' : $shortDesc;

        $updateData = [
            'title' => strip_tags($validated['title']),
            'short_description' => $shortDescription,
            'description' => $description,
            'publisher' => strip_tags($validated['publisher'] ?? ''),
            'publisher_type' => $validated['publisher_type'] ?? 'other',
            'company_name' => strip_tags($validated['company_name'] ?? ''),
            'company_url' => strip_tags($validated['company_url'] ?? ''),
            'location' => strip_tags($validated['location'] ?? ''),
            'status' => $validated['status'] ?? 'open',
            'published_date' => $validated['published_date'] ?? $tender->published_date,
            'deadline' => $validated['deadline'] ?? null,
            'source_url' => strip_tags($validated['source_url'] ?? ''),
            'is_visible' => $validated['is_visible'] ?? $tender->is_visible,
        ];

        $tender->update($updateData);

        if ($request->expectsJson()) {
            return $this->successResponse('Tender updated successfully', $tender->fresh());
        }

        return redirect()->route('admin.tenders.index', ['locale' => $locale])
            ->with('success', 'Tender updated successfully');
    }

    /**
     * Delete a tender
     */
    public function destroy(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid tender ID', 400);
        }

        $tender = Tender::findOrFail($id);
        $tender->delete();

        if ($request->expectsJson()) {
            return $this->successResponse('Tender deleted successfully');
        }

        return redirect()->route('admin.tenders.index', ['locale' => $locale])
            ->with('success', 'Tender deleted successfully');
    }

    /**
     * Toggle visibility of a tender
     */
    public function toggleVisibility(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid tender ID', 400);
        }

        $tender = Tender::findOrFail($id);
        $tender->is_visible = !$tender->is_visible;
        $tender->save();

        $status = $tender->is_visible ? 'visible' : 'hidden';

        if ($request->expectsJson()) {
            return $this->successResponse("Tender is now {$status}", [
                'is_visible' => $tender->is_visible
            ]);
        }

        return back()->with('success', "Tender is now {$status}");
    }

    /**
     * Bulk actions on multiple tenders
     */
    public function bulkAction(Request $request, $locale)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:tenders,id',
            'action' => 'required|in:show,hide,delete',
        ]);

        $tenders = Tender::whereIn('id', $validated['ids'])->get();
        $processed = 0;

        foreach ($tenders as $tender) {
            switch ($validated['action']) {
                case 'show':
                    $tender->update(['is_visible' => true]);
                    $processed++;
                    break;

                case 'hide':
                    $tender->update(['is_visible' => false]);
                    $processed++;
                    break;

                case 'delete':
                    $tender->delete();
                    $processed++;
                    break;
            }
        }

        return $this->successResponse("Bulk action completed: {$processed} tenders processed", [
            'processed' => $processed,
        ]);
    }

    /**
     * Validate tender request data
     */
    private function validateTenderRequest(Request $request, bool $isCreate = true): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'publisher' => 'nullable|string|max:255',
            'publisher_type' => 'nullable|in:government,ngo,private,academic,media,other',
            'company_name' => 'nullable|string|max:255',
            'company_url' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:open,closing_soon,closed',
            'published_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'source_url' => 'nullable|string|max:500',
            'is_visible' => 'nullable|boolean',
        ]);
    }
}
