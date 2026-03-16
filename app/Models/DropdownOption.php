<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DropdownOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
        'label',
        'label_ar',
        'parent_id',
        'metadata',
        'sort_order',
        'is_active',
        'is_system'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    // Cache duration in seconds (1 hour)
    const CACHE_TTL = 3600;

    // =====================
    // Relationships
    // =====================

    /**
     * Get the parent option (for subsectors)
     */
    public function parent()
    {
        return $this->belongsTo(DropdownOption::class, 'parent_id');
    }

    /**
     * Get child options (subsectors for sectors)
     */
    public function children()
    {
        return $this->hasMany(DropdownOption::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    // =====================
    // Scopes
    // =====================

    /**
     * Filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Only active options
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Only root level (no parent)
     */
    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    // =====================
    // Static Helper Methods with Caching
    // =====================

    /**
     * Get options for a type (with optional parent filter)
     */
    public static function getOptions(string $type, ?int $parentId = null): array
    {
        $cacheKey = "dropdown_options_{$type}" . ($parentId ? "_{$parentId}" : '');

        // Check cache first
        $cached = Cache::get($cacheKey);
        if ($cached !== null && !empty($cached)) {
            return $cached;
        }

        // Query the database
        $query = static::ofType($type)->active()->orderBy('sort_order');

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->rootLevel();
        }

        $result = $query->get()->toArray();

        // Only cache non-empty results to allow fallbacks to work
        if (!empty($result)) {
            Cache::put($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    /**
     * Get options formatted for select dropdown (value => label) - locale-aware
     */
    public static function getForSelect(string $type, ?int $parentId = null): array
    {
        $options = static::getOptions($type, $parentId);
        $locale = app()->getLocale();
        return collect($options)->mapWithKeys(function ($item) use ($locale) {
            $label = ($locale === 'ar' && !empty($item['label_ar'])) ? $item['label_ar'] : $item['label'];
            return [$item['value'] => $label];
        })->toArray();
    }

    /**
     * Get options with their children (e.g., sectors with subsectors)
     */
    public static function getWithChildren(string $type): array
    {
        $cacheKey = "dropdown_options_{$type}_with_children";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($type) {
            return static::ofType($type)
                ->active()
                ->rootLevel()
                ->with('children')
                ->orderBy('sort_order')
                ->get()
                ->toArray();
        });
    }

    /**
     * Get all options for a type (including inactive) - for admin
     */
    public static function getAllOptions(string $type, ?int $parentId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::ofType($type)->orderBy('sort_order');

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->rootLevel();
        }

        return $query->with('children')->get();
    }

    /**
     * Get sectors formatted for JavaScript (with subsectors array) - locale-aware
     */
    public static function getSectorsForJs(): array
    {
        $locale = app()->getLocale();
        $cacheKey = "dropdown_options_sectors_js_{$locale}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($locale) {
            $sectors = static::ofType('sector')
                ->active()
                ->rootLevel()
                ->orderBy('sort_order')
                ->get();

            return $sectors->map(function ($sector) use ($locale) {
                $subsectors = static::ofType('subsector')
                    ->active()
                    ->where('parent_id', $sector->id)
                    ->orderBy('sort_order')
                    ->get(['label', 'label_ar'])
                    ->map(function ($sub) use ($locale) {
                        return ($locale === 'ar' && !empty($sub->label_ar)) ? $sub->label_ar : $sub->label;
                    })
                    ->toArray();

                return [
                    'value' => $sector->value,
                    'label' => ($locale === 'ar' && !empty($sector->label_ar)) ? $sector->label_ar : $sector->label,
                    'subSectors' => $subsectors
                ];
            })->toArray();
        });
    }

    /**
     * Get subsectors grouped by sector value - locale-aware
     */
    public static function getSubsectorsByType(): array
    {
        $locale = app()->getLocale();
        $cacheKey = "dropdown_options_subsectors_by_type_{$locale}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($locale) {
            $sectors = static::ofType('sector')
                ->active()
                ->rootLevel()
                ->orderBy('sort_order')
                ->get();

            $result = [];
            foreach ($sectors as $sector) {
                $subsectors = static::ofType('subsector')
                    ->active()
                    ->where('parent_id', $sector->id)
                    ->orderBy('sort_order')
                    ->get(['label', 'label_ar'])
                    ->map(function ($sub) use ($locale) {
                        return ($locale === 'ar' && !empty($sub->label_ar)) ? $sub->label_ar : $sub->label;
                    })
                    ->toArray();

                $result[$sector->value] = $subsectors;
            }

            return $result;
        });
    }

    /**
     * Get the localized label based on current app locale
     */
    public function getLocalizedLabelAttribute(): string
    {
        if (app()->getLocale() === 'ar' && !empty($this->label_ar)) {
            return $this->label_ar;
        }
        return $this->label;
    }

    /**
     * Get simple list of labels for a type (locale-aware)
     */
    public static function getLabels(string $type): array
    {
        $locale = app()->getLocale();
        $cacheKey = "dropdown_options_{$type}_labels_{$locale}";

        // Check cache first
        $cached = Cache::get($cacheKey);
        if ($cached !== null && !empty($cached)) {
            return $cached;
        }

        // Query the database
        $column = ($locale === 'ar') ? 'label_ar' : 'label';
        $items = static::ofType($type)
            ->active()
            ->rootLevel()
            ->orderBy('sort_order')
            ->get(['label', 'label_ar']);

        $result = $items->map(function ($item) use ($locale) {
            if ($locale === 'ar' && !empty($item->label_ar)) {
                return $item->label_ar;
            }
            return $item->label;
        })->toArray();

        // Only cache non-empty results to allow fallbacks to work
        if (!empty($result)) {
            Cache::put($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    /**
     * Get key=>label pairs for a type (English key stored in DB, localized label for display)
     * Returns: ['English Value' => 'Localized Display Label']
     */
    public static function getKeyLabelPairs(string $type): array
    {
        $locale = app()->getLocale();
        $cacheKey = "dropdown_options_{$type}_pairs_{$locale}";

        $cached = Cache::get($cacheKey);
        if ($cached !== null && !empty($cached)) {
            return $cached;
        }

        $items = static::ofType($type)
            ->active()
            ->rootLevel()
            ->orderBy('sort_order')
            ->get(['label', 'label_ar']);

        $result = [];
        foreach ($items as $item) {
            $key = $item->label;
            $display = ($locale === 'ar' && !empty($item->label_ar)) ? $item->label_ar : $item->label;
            $result[$key] = $display;
        }

        if (!empty($result)) {
            Cache::put($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    // =====================
    // Cache Management
    // =====================

    /**
     * Clear cache for a specific type or all
     */
    public static function clearCache(?string $type = null): void
    {
        if ($type) {
            Cache::forget("dropdown_options_{$type}");
            Cache::forget("dropdown_options_{$type}_with_children");
            Cache::forget("dropdown_options_{$type}_labels");
            Cache::forget("dropdown_options_{$type}_labels_en");
            Cache::forget("dropdown_options_{$type}_labels_ar");

            // Clear parent-specific caches
            $options = static::ofType($type)->rootLevel()->get();
            foreach ($options as $option) {
                Cache::forget("dropdown_options_{$type}_{$option->id}");
            }

            // Clear special caches
            if ($type === 'sector' || $type === 'subsector') {
                Cache::forget("dropdown_options_sectors_js");
                Cache::forget("dropdown_options_sectors_js_en");
                Cache::forget("dropdown_options_sectors_js_ar");
                Cache::forget("dropdown_options_subsectors_by_type");
                Cache::forget("dropdown_options_subsectors_by_type_en");
                Cache::forget("dropdown_options_subsectors_by_type_ar");
            }
        } else {
            // Clear all dropdown caches
            $types = ['sector', 'subsector', 'skill', 'city', 'product_category',
                     'project_category', 'project_role', 'service_category',
                     'years_experience', 'fablab_type', 'marketplace_type',
                     'marketplace_tag', 'marketplace_category', 'training_category',
                     'tender_category'];

            foreach ($types as $t) {
                static::clearCache($t);
            }
        }
    }

    // =====================
    // Boot Method
    // =====================

    protected static function boot()
    {
        parent::boot();

        // Clear cache when options are modified
        static::saved(function ($option) {
            static::clearCache($option->type);
        });

        static::deleted(function ($option) {
            static::clearCache($option->type);
        });
    }
}
