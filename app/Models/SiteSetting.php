<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Key/value store for public-facing site configuration.
 *
 * Stores hero image paths, hero text, counter labels, footer/header/subheader
 * text, and CMS page content. Values are keyed by a dot-notation string
 * (e.g., "hero.image", "footer.en.text") and stored as text.
 */
class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * Get the value attribute - decode JSON for json type or if value looks like JSON
     */
    public function getValueAttribute($value)
    {
        // Always try to decode if it looks like JSON (starts with { or [)
        if ($value && ($this->type === 'json' || (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))))) {
            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $value;
        }
        return $value;
    }

    /**
     * Set the value attribute - encode to JSON for json type
     */
    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'text', $group = 'general', $label = null, $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'label' => $label ?? ucwords(str_replace('_', ' ', $key)),
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings in a group
     */
    public static function getGroup($group)
    {
        return static::where('group', $group)->get()->pluck('value', 'key');
    }

    /**
     * Get hero image for a specific page (legacy single image - returns first from carousel)
     */
    public static function getHeroImage($page)
    {
        $images = static::getHeroImages($page);
        return !empty($images) ? $images[0] : null;
    }

    /**
     * Get all hero images for a specific page (carousel)
     */
    public static function getHeroImages($page)
    {
        $setting = static::where('key', "hero_images_{$page}")->first();
        if ($setting && $setting->value) {
            $images = is_array($setting->value) ? $setting->value : json_decode($setting->value, true);
            if (is_array($images) && !empty($images)) {
                return array_map(function($path) {
                    return url('media/' . $path);
                }, array_filter($images));
            }
        }

        // Fallback to legacy single image
        $legacySetting = static::where('key', "hero_image_{$page}")->first();
        if ($legacySetting && $legacySetting->value) {
            return [url('media/' . $legacySetting->value)];
        }

        return [];
    }

    /**
     * Get hero images paths (without URL) for a specific page
     */
    public static function getHeroImagePaths($page)
    {
        $setting = static::where('key', "hero_images_{$page}")->first();
        if ($setting && $setting->value) {
            $images = is_array($setting->value) ? $setting->value : json_decode($setting->value, true);
            if (is_array($images) && !empty(array_filter($images))) {
                return array_values(array_filter($images));
            }
        }

        // Fallback to legacy single image key
        $legacySetting = static::where('key', "hero_image_{$page}")->first();
        if ($legacySetting && $legacySetting->value) {
            return [$legacySetting->value];
        }

        return [];
    }

    /**
     * Set hero image for a specific page (legacy single image)
     */
    public static function setHeroImage($page, $imagePath)
    {
        return static::set(
            "hero_image_{$page}",
            $imagePath,
            'image',
            'hero_images',
            ucfirst($page) . ' Hero Image',
            'Hero background image for the ' . $page . ' page'
        );
    }

    /**
     * Set multiple hero images for a specific page (carousel)
     */
    public static function setHeroImages($page, array $imagePaths)
    {
        return static::set(
            "hero_images_{$page}",
            $imagePaths,
            'json',
            'hero_images',
            ucfirst($page) . ' Hero Images',
            'Hero carousel images for the ' . $page . ' page'
        );
    }

    /**
     * Add a hero image to a page's carousel
     */
    public static function addHeroImage($page, $imagePath)
    {
        $currentImages = static::getHeroImagePaths($page);
        if (count($currentImages) < 5) {
            $currentImages[] = $imagePath;
            static::setHeroImages($page, $currentImages);
            return true;
        }
        return false; // Max 5 images
    }

    /**
     * Remove a hero image from a page's carousel
     */
    public static function removeHeroImage($page, $index)
    {
        $currentImages = static::getHeroImagePaths($page);
        if (isset($currentImages[$index])) {
            $removedPath = $currentImages[$index];
            array_splice($currentImages, $index, 1);
            static::setHeroImages($page, array_values($currentImages));
            return $removedPath;
        }
        return null;
    }

    /**
     * Get hero texts (title + subtitle) for a specific page
     */
    public static function getHeroTexts($page)
    {
        return static::get("hero_texts_{$page}");
    }

    /**
     * Set hero texts for a specific page
     */
    public static function setHeroTexts($page, array $texts)
    {
        return static::set(
            "hero_texts_{$page}",
            $texts,
            'json',
            'hero_texts',
            ucfirst(str_replace('_', ' ', $page)) . ' Hero Texts',
            'Hero title and subtitle for the ' . $page . ' page'
        );
    }

    /**
     * Get hero title for a page, with fallback to default text
     */
    public static function getHeroTitle($page, $fallback = '')
    {
        $texts = static::getHeroTexts($page);
        if ($texts) {
            $locale = app()->getLocale();
            $key = $locale === 'ar' ? 'title_ar' : 'title';
            if (!empty($texts[$key])) {
                return $texts[$key];
            }
        }
        return __($fallback);
    }

    /**
     * Get hero subtitle for a page, with fallback to default text
     */
    public static function getHeroSubtitle($page, $fallback = '')
    {
        $texts = static::getHeroTexts($page);
        if ($texts) {
            $locale = app()->getLocale();
            $key = $locale === 'ar' ? 'subtitle_ar' : 'subtitle';
            if (!empty($texts[$key])) {
                return $texts[$key];
            }
        }
        return __($fallback);
    }
}
