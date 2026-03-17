<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use App\Services\CacheService;
use Illuminate\Http\Request;

/**
 * Admin settings for the homepage statistics counter widget.
 *
 * Controls which counters appear on the homepage discover section,
 * their labels (bilingual), order, and whether they are visible.
 * Values are persisted in SiteSetting and cached via CacheService.
 */
class AdminCounterSettingsController extends AdminBaseController
{
    /**
     * Available counter types for the discover page
     */
    public static function getAvailableCounterTypes()
    {
        return [
            'all_members' => ['label' => 'All Members', 'description' => 'Total active registered members', 'category' => 'simple'],
            'designers' => ['label' => 'Designers (All)', 'description' => 'Total active designers (all sectors)', 'category' => 'simple'],
            'designers_by_sector' => ['label' => 'Designers by Sector', 'description' => 'Count designers filtered by selected sectors', 'category' => 'sector_filter'],
            'products' => ['label' => 'Products', 'description' => 'Total approved products', 'category' => 'simple'],
            'projects' => ['label' => 'Projects', 'description' => 'Total approved projects', 'category' => 'simple'],
            'services' => ['label' => 'Services', 'description' => 'Total approved services', 'category' => 'simple'],
            'fablabs' => ['label' => 'Fab Labs', 'description' => 'Total fab labs', 'category' => 'simple'],
            'trainings' => ['label' => 'Trainings', 'description' => 'Total trainings', 'category' => 'simple'],
            'tenders' => ['label' => 'Tenders', 'description' => 'Total tenders', 'category' => 'simple'],
            'marketplace_posts' => ['label' => 'Marketplace Posts', 'description' => 'Total approved marketplace posts', 'category' => 'simple'],
        ];
    }

    /**
     * Available sectors for filtering designers
     */
    public static function getAvailableSectors()
    {
        $sectors = \App\Helpers\DropdownHelper::sectorOptions();
        $result = [];
        foreach ($sectors as $sector) {
            if ($sector['value'] !== 'guest') {
                $result[] = [
                    'value' => $sector['value'],
                    'label' => $sector['label']
                ];
            }
        }
        return $result;
    }

    /**
     * Get default counter settings
     */
    public static function getDefaultCounterSettings()
    {
        return [
            'badge_counter' => [
                'type' => 'designers',
                'label' => 'creative professionals',
                'label_ar' => 'مبدعين محترفين',
                'sectors' => [],
            ],
            'stats_counters' => [
                ['type' => 'products', 'label' => 'Products', 'label_ar' => 'المنتجات', 'sectors' => []],
                ['type' => 'projects', 'label' => 'Projects', 'label_ar' => 'المشاريع', 'sectors' => []],
                ['type' => 'designers_by_sector', 'label' => 'Manufacturers & Showrooms', 'label_ar' => 'المصنعين وصالات العرض', 'sectors' => ['manufacturer', 'showroom']],
            ],
        ];
    }

    /**
     * Get counter settings for the discover/home page
     */
    public function getCounterSettings(Request $request, $locale)
    {
        $counterSettings = SiteSetting::get('counter_settings');
        if (!$counterSettings) {
            $counterSettings = static::getDefaultCounterSettings();
        }

        return $this->successResponse('Counter settings retrieved', [
            'settings' => $counterSettings,
            'available_types' => static::getAvailableCounterTypes(),
        ]);
    }

    /**
     * Update counter settings
     */
    public function updateCounters(Request $request, $locale)
    {
        $badgeCounter = $request->input('badge_counter');
        $statsCounters = $request->input('stats_counters');

        if (!$badgeCounter || !is_array($badgeCounter)) {
            return $this->errorResponse('Badge counter is required', 422);
        }

        if (!isset($badgeCounter['type']) || !isset($badgeCounter['label'])) {
            return $this->errorResponse('Badge counter must have type and label', 422);
        }

        if (!$statsCounters || !is_array($statsCounters) || count($statsCounters) < 1) {
            return $this->errorResponse('At least one stats counter is required', 422);
        }

        $availableTypes = array_keys(static::getAvailableCounterTypes());
        if (!in_array($badgeCounter['type'], $availableTypes)) {
            return $this->errorResponse('Invalid badge counter type: ' . $badgeCounter['type'], 422);
        }

        foreach ($statsCounters as $index => $counter) {
            if (!isset($counter['type']) || !isset($counter['label'])) {
                return $this->errorResponse("Stats counter {$index} must have type and label", 422);
            }
            if (!in_array($counter['type'], $availableTypes)) {
                return $this->errorResponse("Invalid stats counter type: " . $counter['type'], 422);
            }
        }

        $availableSectors = array_map(function($sector) {
            return $sector['value'];
        }, static::getAvailableSectors());

        // Clean up badge counter sectors
        if ($badgeCounter['type'] !== 'designers_by_sector') {
            $badgeCounter['sectors'] = [];
        } else {
            $badgeCounter['sectors'] = isset($badgeCounter['sectors']) && is_array($badgeCounter['sectors'])
                ? array_values(array_filter($badgeCounter['sectors'], function($sector) use ($availableSectors) {
                    return in_array($sector, $availableSectors);
                }))
                : [];
            if (empty($badgeCounter['sectors'])) {
                return $this->errorResponse('Badge counter with "Designers by Sector" type requires at least one sector selected', 422);
            }
        }

        // Clean up stats counters sectors
        foreach ($statsCounters as $index => &$counter) {
            if ($counter['type'] !== 'designers_by_sector') {
                $counter['sectors'] = [];
            } else {
                $counter['sectors'] = isset($counter['sectors']) && is_array($counter['sectors'])
                    ? array_values(array_filter($counter['sectors'], function($sector) use ($availableSectors) {
                        return in_array($sector, $availableSectors);
                    }))
                    : [];
                if (empty($counter['sectors'])) {
                    return $this->errorResponse('Stats counter "' . ($counter['label'] ?? ($index + 1)) . '" with "Designers by Sector" type requires at least one sector selected', 422);
                }
            }
        }

        $counterSettings = [
            'badge_counter' => $badgeCounter,
            'stats_counters' => $statsCounters,
        ];

        SiteSetting::set('counter_settings', $counterSettings, 'json', 'layout', 'Counter Settings', 'Home page counter configuration');

        CacheService::clearDashboardCache();

        return $this->successResponse('Counter settings updated successfully');
    }

    /**
     * Reset counters to defaults
     */
    public function resetCounters(Request $request, $locale)
    {
        SiteSetting::set('counter_settings', static::getDefaultCounterSettings(), 'json', 'layout', 'Counter Settings', 'Home page counter configuration');

        CacheService::clearDashboardCache();

        return $this->successResponse('Counter settings reset to defaults');
    }
}
