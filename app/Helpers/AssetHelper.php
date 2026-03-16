<?php

namespace App\Helpers;

/**
 * Asset Helper for cache busting and versioned URLs
 *
 * Usage in Blade templates:
 *   <link href="{{ \App\Helpers\AssetHelper::versioned('css/app.css') }}" rel="stylesheet">
 *   <script src="{{ \App\Helpers\AssetHelper::versioned('js/app.js') }}"></script>
 *   <img src="{{ \App\Helpers\AssetHelper::versioned('images/logo.png') }}">
 *
 * Or register the helper globally in AppServiceProvider and use:
 *   <link href="{{ versioned_asset('css/app.css') }}" rel="stylesheet">
 */
class AssetHelper
{
    /**
     * Generate a versioned asset URL for cache busting
     *
     * @param string $path The asset path (e.g., 'css/app.css')
     * @return string The full URL with version query string
     */
    public static function versioned(string $path): string
    {
        $version = config('app.asset_version', '1');

        // Use asset() helper to generate the proper URL
        $url = asset($path);

        // Append version query string
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . $version;
    }

    /**
     * Generate a versioned asset URL using file modification time
     * This automatically busts cache when the file changes (no manual version update needed)
     *
     * @param string $path The asset path relative to public directory
     * @return string The full URL with modification time query string
     */
    public static function autoVersioned(string $path): string
    {
        $fullPath = public_path($path);

        // Get file modification time, fallback to config version if file doesn't exist
        if (file_exists($fullPath)) {
            $version = filemtime($fullPath);
        } else {
            $version = config('app.asset_version', '1');
        }

        $url = asset($path);
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . $version;
    }

    /**
     * Generate a versioned storage URL for uploaded assets
     *
     * @param string $path The storage path
     * @return string The full URL with version query string
     */
    public static function storage(string $path): string
    {
        $version = config('app.asset_version', '1');

        $url = url('media/' . $path);
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . $version;
    }
}
