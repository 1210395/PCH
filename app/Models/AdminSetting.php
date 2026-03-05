<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'updated_by',
    ];

    /**
     * Cache key prefix
     */
    private const CACHE_PREFIX = 'admin_setting_';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, ?int $updatedBy = null, ?string $type = null): bool
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = is_array($value) ? json_encode($value) : (string) $value;

        // Set type - use provided type, keep existing type, or default to 'string'
        if ($type) {
            $setting->type = $type;
        } elseif (!$setting->exists || !$setting->type) {
            // For new records or records without type, determine type from value
            if (is_array($value)) {
                $setting->type = 'json';
            } elseif (in_array($value, ['0', '1', 0, 1, true, false], true)) {
                $setting->type = 'boolean';
            } else {
                $setting->type = 'string';
            }
        }

        if ($updatedBy) {
            $setting->updated_by = $updatedBy;
        }

        $result = $setting->save();

        // Clear cache
        Cache::forget(self::CACHE_PREFIX . $key);

        return $result;
    }

    /**
     * Toggle a boolean setting
     */
    public static function toggle(string $key, ?int $updatedBy = null): bool
    {
        $currentValue = self::get($key, false);
        $newValue = !$currentValue;

        self::set($key, $newValue ? '1' : '0', $updatedBy, 'boolean');

        return $newValue;
    }

    /**
     * Check if auto-accept is enabled for a content type
     */
    public static function isAutoAcceptEnabled(string $type): bool
    {
        return (bool) self::get("auto_accept_{$type}", false);
    }

    /**
     * Cast value based on type
     */
    private static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::pluck('key');
        foreach ($settings as $key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        }
    }
}
