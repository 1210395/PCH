<?php

namespace Tests\Browser;

/**
 * Test configuration for real user flow tests.
 *
 * We use 3 real accounts:
 *   - User A (Designer): jadallah.baragitha@gmail.com — already registered & verified
 *   - User B (Manufacturer): moahamadbarmohamad1122@gmail.com — will be registered by tests
 *   - Academic: jad.bar1122@gmail.com — already registered as designer, also used for academic
 *   - Admin: admin@palestinecreativehub.com
 */
class TestConfig
{
    public const BASE_URL = 'https://technopark.ps/PalestineCreativeHub';

    // ==========================================
    // USER A — Designer (already registered & verified)
    // ==========================================
    public const USER_A_EMAIL = 'jadallah.baragitha+designer@gmail.com';
    public const USER_A_PASSWORD = 'TestDesigner@2024!';
    public const USER_A_NAME = 'Test Designer';

    // ==========================================
    // USER B — Manufacturer (will be registered in test)
    // ==========================================
    public const USER_B_EMAIL = 'moahamadbarmohamad1122@gmail.com';
    public const USER_B_PASSWORD = 'TestManufacturer@2024!';
    public const USER_B_NAME = 'Test Manufacturer';

    // ==========================================
    // ACADEMIC ACCOUNT
    // ==========================================
    public const ACADEMIC_EMAIL = 'jad.bar1122@gmail.com';
    public const ACADEMIC_PASSWORD = 'TestDesigner@2024!';
    public const ACADEMIC_NAME = 'Test University';

    // ==========================================
    // ADMIN
    // ==========================================
    public const ADMIN_EMAIL = 'admin@palestinecreativehub.com';
    public const ADMIN_PASSWORD = 'Admin@PCH2024!';

    // Backward compat aliases
    public const DESIGNER_EMAIL = self::USER_A_EMAIL;
    public const DESIGNER_PASSWORD = self::USER_A_PASSWORD;
    public const MANUFACTURER_EMAIL = self::USER_B_EMAIL;
    public const MANUFACTURER_PASSWORD = self::USER_B_PASSWORD;

    // Timing
    public const ACTION_DELAY = 500;
    public const PAGE_LOAD_WAIT = 3;

    public static function url(string $path = '', string $locale = 'en'): string
    {
        $base = rtrim(self::BASE_URL, '/');
        return $path === '' || $path === '/' ? "{$base}/{$locale}" : "{$base}/{$locale}/" . ltrim($path, '/');
    }

    public static function adminUrl(string $path = '', string $locale = 'en'): string
    {
        return self::url("admin/{$path}", $locale);
    }

    public static function academicUrl(string $path = '', string $locale = 'en'): string
    {
        return self::url("academic/{$path}", $locale);
    }
}
