<?php

namespace App\Http\Controllers\Admin;

use App\Models\RatingCriteria;
use Illuminate\Http\Request;

/**
 * Admin CRUD for rating criteria dimensions.
 *
 * Manages the configurable criteria used in designer profile ratings
 * (e.g., professionalism, communication quality, delivery timeliness).
 * Supports create, update, delete, toggle active, and drag-to-reorder.
 */
class AdminRatingCriteriaController extends AdminBaseController
{
    /**
     * List all criteria
     */
    public function index(Request $request, $locale)
    {
        $criteria = RatingCriteria::ordered()->get();

        return view('admin.ratings.criteria.index', compact('criteria'));
    }

    /**
     * Create a new criterion
     */
    public function store(Request $request, $locale)
    {
        $validated = $request->validate([
            'en_label'   => 'required|string|max:255',
            'ar_label'   => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['en_label'] = strip_tags($validated['en_label']);
        $validated['ar_label'] = strip_tags($validated['ar_label']);

        $maxOrder = RatingCriteria::max('sort_order') ?? 0;
        $criterion = RatingCriteria::create([
            'en_label'   => $validated['en_label'],
            'ar_label'   => $validated['ar_label'],
            'is_active'  => true,
            'sort_order' => $validated['sort_order'] ?? ($maxOrder + 1),
        ]);

        return $this->successResponse(__('Criterion added successfully'), $criterion);
    }

    /**
     * Update a criterion
     */
    public function update(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid ID', 400);
        }

        $validated = $request->validate([
            'en_label'   => 'required|string|max:255',
            'ar_label'   => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['en_label'] = strip_tags($validated['en_label']);
        $validated['ar_label'] = strip_tags($validated['ar_label']);

        $criterion = RatingCriteria::findOrFail($id);
        $criterion->update([
            'en_label'   => $validated['en_label'],
            'ar_label'   => $validated['ar_label'],
            'sort_order' => $validated['sort_order'] ?? $criterion->sort_order,
        ]);

        return $this->successResponse(__('Criterion updated successfully'), $criterion->fresh());
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleActive(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid ID', 400);
        }

        $criterion = RatingCriteria::findOrFail($id);
        $criterion->update(['is_active' => !$criterion->is_active]);

        $state = $criterion->is_active ? __('enabled') : __('disabled');

        return $this->successResponse(__("Criterion {$state} successfully"), $criterion->fresh());
    }

    /**
     * Update sort order for multiple criteria
     */
    public function reorder(Request $request, $locale)
    {
        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:rating_criteria,id',
        ]);

        foreach ($validated['order'] as $position => $criteriaId) {
            RatingCriteria::where('id', $criteriaId)->update(['sort_order' => $position]);
        }

        return $this->successResponse(__('Order saved successfully'));
    }

    /**
     * Delete a criterion (hard delete — historical responses preserved via cascade)
     */
    public function destroy(Request $request, $locale, $id)
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse('Invalid ID', 400);
        }

        $criterion = RatingCriteria::findOrFail($id);
        $criterion->delete();

        return $this->successResponse(__('Criterion deleted successfully'));
    }
}
