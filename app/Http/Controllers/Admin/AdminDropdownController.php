<?php

namespace App\Http\Controllers\Admin;

use App\Models\DropdownOption;
use Illuminate\Http\Request;

class AdminDropdownController extends AdminBaseController
{
    /**
     * Get dropdown type configurations (translated)
     */
    private function getDropdownTypes()
    {
        return [
            'sector' => [
                'label' => __('Sectors'),
                'description' => __('User profile sectors (Academic, Designer, etc.)'),
                'has_children' => true,
                'child_type' => 'subsector',
                'child_label' => __('Sub-Sectors')
            ],
            'skill' => [
                'label' => __('Skills'),
                'description' => __('Skills and expertise options for user profiles'),
                'has_children' => false
            ],
            'city' => [
                'label' => __('Cities / Governorates'),
                'description' => __('Palestinian governorates for location selection'),
                'has_children' => false
            ],
            'product_category' => [
                'label' => __('Product Categories'),
                'description' => __('Categories for product listings'),
                'has_children' => false
            ],
            'project_category' => [
                'label' => __('Project Categories'),
                'description' => __('Categories for project portfolios'),
                'has_children' => false
            ],
            'project_role' => [
                'label' => __('Project Roles'),
                'description' => __('Roles users can have in projects'),
                'has_children' => false
            ],
            'service_category' => [
                'label' => __('Service Categories'),
                'description' => __('Categories for service offerings'),
                'has_children' => false
            ],
            'years_experience' => [
                'label' => __('Years of Experience'),
                'description' => __('Experience range options for profiles'),
                'has_children' => false
            ],
            'fablab_type' => [
                'label' => __('FabLab Types'),
                'description' => __('Types of fabrication laboratories'),
                'has_children' => false
            ],
            'marketplace_type' => [
                'label' => __('Marketplace Types'),
                'description' => __('Types of marketplace posts'),
                'has_children' => false
            ],
            'marketplace_tag' => [
                'label' => __('Marketplace Tags'),
                'description' => __('Tags for marketplace posts'),
                'has_children' => false
            ],
            'marketplace_category' => [
                'label' => __('Marketplace Categories'),
                'description' => __('Categories for marketplace posts'),
                'has_children' => false
            ],
            'training_category' => [
                'label' => __('Training Categories'),
                'description' => __('Categories for academic training programs'),
                'has_children' => false
            ],
            'tender_category' => [
                'label' => __('Tender Categories'),
                'description' => __('Categories for tender listings'),
                'has_children' => false
            ],
        ];
    }

    /**
     * Display list of all dropdown categories
     */
    public function index(Request $request, $locale)
    {
        $types = $this->getDropdownTypes();

        // Get counts for each type
        $counts = DropdownOption::selectRaw('type, COUNT(*) as count')
            ->where('is_active', true)
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return view('admin.settings.dropdowns.index', compact('types', 'counts'));
    }

    /**
     * Display options for a specific type
     */
    public function show(Request $request, $locale, $type)
    {
        if (!isset($this->getDropdownTypes()[$type])) {
            abort(404, 'Invalid dropdown type');
        }

        $typeConfig = $this->getDropdownTypes()[$type];

        $options = DropdownOption::ofType($type)
            ->rootLevel()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        // For subsectors, we need to show which parent they belong to
        $parentOptions = null;
        if ($type === 'subsector') {
            $parentOptions = DropdownOption::ofType('sector')
                ->active()
                ->orderBy('sort_order')
                ->get();
        }

        if ($request->expectsJson()) {
            return $this->successResponse('Options retrieved', [
                'type' => $type,
                'config' => $typeConfig,
                'options' => $options
            ]);
        }

        return view('admin.settings.dropdowns.show', compact('type', 'typeConfig', 'options', 'parentOptions'));
    }

    /**
     * Show sub-options for a parent (e.g., subsectors for a sector)
     */
    public function showChildren(Request $request, $locale, $type, $parentId)
    {
        if (!isset($this->getDropdownTypes()[$type])) {
            abort(404, 'Invalid dropdown type');
        }

        $typeConfig = $this->getDropdownTypes()[$type];
        $parent = DropdownOption::findOrFail($parentId);

        $childType = $typeConfig['child_type'] ?? null;
        if (!$childType) {
            abort(404, 'This type does not have children');
        }

        $options = DropdownOption::ofType($childType)
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->get();

        if ($request->expectsJson()) {
            return $this->successResponse('Child options retrieved', [
                'parent' => $parent,
                'options' => $options
            ]);
        }

        return view('admin.settings.dropdowns.children', compact('type', 'typeConfig', 'parent', 'options', 'childType'));
    }

    /**
     * Store a new option
     */
    public function store(Request $request, $locale, $type)
    {
        if (!isset($this->getDropdownTypes()[$type])) {
            return $this->errorResponse('Invalid dropdown type', 404);
        }

        $validated = $this->validateAndSanitize($request, [
            'value' => 'required|string|max:100',
            'label' => 'required|string|max:100',
            'label_ar' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:dropdown_options,id',
            'metadata' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean'
        ]);

        // Clean value (lowercase, replace spaces with underscores)
        $validated['value'] = strtolower(str_replace([' ', '/', '&'], ['_', '_', '_'], $validated['value']));

        // Determine the actual type (for subsectors, use 'subsector' as type)
        $actualType = $type;
        if (isset($validated['parent_id']) && $this->getDropdownTypes()[$type]['has_children'] ?? false) {
            $actualType = $this->getDropdownTypes()[$type]['child_type'];
        }

        // Check for duplicate
        $query = DropdownOption::where('type', $actualType)
            ->where('value', $validated['value']);

        if (isset($validated['parent_id'])) {
            $query->where('parent_id', $validated['parent_id']);
        }

        if ($query->exists()) {
            return $this->errorResponse('An option with this value already exists');
        }

        $option = DropdownOption::create([
            'type' => $actualType,
            'value' => $validated['value'],
            'label' => $validated['label'],
            'label_ar' => $validated['label_ar'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
            'sort_order' => $validated['sort_order'] ?? DropdownOption::ofType($actualType)->max('sort_order') + 1,
            'is_active' => $validated['is_active'] ?? true,
            'is_system' => false
        ]);

        return $this->successResponse('Option created successfully', $option);
    }

    /**
     * Update an existing option
     */
    public function update(Request $request, $locale, $type, $id)
    {
        $option = DropdownOption::findOrFail($id);

        $validated = $this->validateAndSanitize($request, [
            'value' => 'required|string|max:100',
            'label' => 'required|string|max:100',
            'label_ar' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean'
        ]);

        // Clean value
        $validated['value'] = strtolower(str_replace([' ', '/', '&'], ['_', '_', '_'], $validated['value']));

        // Check for duplicate (excluding current)
        $query = DropdownOption::where('type', $option->type)
            ->where('value', $validated['value'])
            ->where('id', '!=', $id);

        if ($option->parent_id) {
            $query->where('parent_id', $option->parent_id);
        }

        if ($query->exists()) {
            return $this->errorResponse('An option with this value already exists');
        }

        $option->update([
            'value' => $validated['value'],
            'label' => $validated['label'],
            'label_ar' => $validated['label_ar'] ?? $option->label_ar,
            'metadata' => $validated['metadata'] ?? $option->metadata,
            'sort_order' => $validated['sort_order'] ?? $option->sort_order,
            'is_active' => $validated['is_active'] ?? $option->is_active
        ]);

        return $this->successResponse('Option updated successfully', $option);
    }

    /**
     * Delete an option
     */
    public function destroy(Request $request, $locale, $type, $id)
    {
        $option = DropdownOption::findOrFail($id);

        // Check if this option has children
        $childCount = DropdownOption::where('parent_id', $id)->count();
        if ($childCount > 0) {
            return $this->errorResponse("Cannot delete this option. It has {$childCount} sub-options. Delete them first.");
        }

        $option->delete();

        // Clear cache for this type
        DropdownOption::clearCache($type);

        return $this->successResponse('Option deleted successfully');
    }

    /**
     * Reorder options
     */
    public function reorder(Request $request, $locale, $type)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:dropdown_options,id'
        ]);

        foreach ($validated['order'] as $index => $id) {
            DropdownOption::where('id', $id)->update(['sort_order' => $index]);
        }

        DropdownOption::clearCache($type);

        return $this->successResponse('Order updated successfully');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Request $request, $locale, $type, $id)
    {
        $option = DropdownOption::findOrFail($id);
        $option->is_active = !$option->is_active;
        $option->save();

        $status = $option->is_active ? 'activated' : 'deactivated';
        return $this->successResponse("Option {$status} successfully", $option);
    }

    /**
     * Sort options alphabetically
     */
    public function sortAlphabetically(Request $request, $locale, $type)
    {
        if (!isset($this->getDropdownTypes()[$type])) {
            return $this->errorResponse('Invalid dropdown type', 404);
        }

        // Get optional parent_id for sorting subsectors
        $parentId = $request->get('parent_id');

        // Get options to sort
        $query = DropdownOption::ofType($type);

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->rootLevel();
        }

        $options = $query->orderBy('label', 'asc')->get();

        // Update sort_order based on alphabetical order
        foreach ($options as $index => $option) {
            $option->update(['sort_order' => $index]);
        }

        DropdownOption::clearCache($type);

        return $this->successResponse('Options sorted alphabetically');
    }

    /**
     * API endpoint for frontend dropdowns
     */
    public function api(Request $request, $locale, $type)
    {
        $parentId = $request->get('parent_id');
        $options = DropdownOption::getOptions($type, $parentId);

        return response()->json([
            'success' => true,
            'options' => $options
        ]);
    }
}
