<?php

namespace App\Helpers;

/**
 * URL safety helpers for rendering user-supplied URLs in views.
 */
class UrlHelper
{
    /**
     * Sanitise a user-supplied URL for use in an `<a href>` attribute.
     *
     * Returns the URL unchanged if it explicitly starts with http:// or https://.
     * For schemeless legacy data ("example.com") it prepends `https://`.
     * For URLs carrying any other scheme (javascript:, data:, vbscript:, file:,
     * etc.) it returns null so the caller can render no link.
     *
     * @param  string|null  $url  Raw user-supplied URL or null.
     * @return string|null        Safe http(s) URL or null if unsafe / empty.
     */
    public static function safe(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // Already an http(s) URL — pass through unchanged.
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        // Has any other URL scheme (javascript:, data:, etc.) — reject.
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return null;
        }

        // Schemeless ("example.com" / "www.example.com") — prepend https://.
        return 'https://' . $url;
    }
}
