<?php

namespace App\Helpers;

/**
 * Checks if a model has incomplete (null/empty) required fields.
 * Used in admin CMS to highlight incomplete records and filter them.
 */
class CompletenessHelper
{
    /**
     * Required fields per model type.
     * If any of these are null/empty, the record is "incomplete".
     */
    private static array $requiredFields = [
        'designer' => ['sector', 'city', 'phone_number', 'bio', 'avatar', 'company_name', 'position'],
        'product' => ['title', 'description', 'category'],
        'project' => ['title', 'description', 'category', 'role'],
        'service' => ['title', 'description', 'category'],
        'marketplace_post' => ['title', 'description', 'category', 'type'],
        'fablab' => ['name', 'city', 'description', 'type'],
        'academic_account' => ['name', 'email', 'institution_type', 'description', 'city'],
        'training' => ['title', 'description', 'category', 'start_date'],
        'tender' => ['title', 'description', 'deadline'],
    ];

    /**
     * Check if a model record is incomplete (has null/empty required fields).
     */
    public static function isIncomplete($model, string $type): bool
    {
        $fields = self::$requiredFields[$type] ?? [];
        foreach ($fields as $field) {
            $value = $model->$field ?? null;
            if ($value === null || $value === '') return true;
        }
        return false;
    }

    /**
     * Check if a model record has "Other" in any categorizable field.
     */
    public static function hasOther($model, string $type): bool
    {
        $categoryFields = ['category', 'sector', 'type', 'role', 'institution_type'];
        foreach ($categoryFields as $field) {
            $value = $model->$field ?? null;
            if ($value !== null && strtolower($value) === 'other') return true;
        }
        return false;
    }

    /**
     * Get the list of null/empty fields for a model.
     */
    public static function getMissingFields($model, string $type): array
    {
        $fields = self::$requiredFields[$type] ?? [];
        $missing = [];
        foreach ($fields as $field) {
            $value = $model->$field ?? null;
            if ($value === null || $value === '') $missing[] = $field;
        }
        return $missing;
    }

    /**
     * Apply completeness filter to a query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type Model type key (e.g., 'designer', 'product')
     * @param string $completeness 'all', 'complete', 'incomplete', 'others'
     */
    public static function applyFilter($query, string $type, string $completeness)
    {
        $fields = self::$requiredFields[$type] ?? [];

        if ($completeness === 'incomplete') {
            $query->where(function ($q) use ($fields) {
                foreach ($fields as $field) {
                    $q->orWhereNull($field)->orWhere($field, '');
                }
            });
        } elseif ($completeness === 'complete') {
            foreach ($fields as $field) {
                $query->whereNotNull($field)->where($field, '!=', '');
            }
        } elseif ($completeness === 'others') {
            $categoryFields = ['category', 'sector', 'type', 'role', 'institution_type'];
            $query->where(function ($q) use ($categoryFields) {
                foreach ($categoryFields as $field) {
                    $q->orWhere($field, 'other')->orWhere($field, 'Other');
                }
            });
        }
        // 'all' = no filter

        return $query;
    }
}
