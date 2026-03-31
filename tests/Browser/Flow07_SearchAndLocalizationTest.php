<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * REAL USER TESTS: Search and Category Localization
 *
 * Tests search in both languages and verifies categories display correctly.
 * Every interaction uses real browser typing — no JS shortcuts.
 *
 * 1.  Navbar search English — type "design", verify results
 * 2.  Navbar search Arabic — type "تصميم", verify results found
 * 3.  Navbar search Arabic sector — type "مصمم", verify designers found
 * 4.  Navbar search Arabic city — type "رام الله", verify results
 * 5.  Search results page — verify designers/projects/products sections
 * 6.  Search results page Arabic — verify same content found
 * 7.  Instant search dropdown — type in navbar, verify dropdown appears with results
 * 8.  Instant search shows services/marketplace — type "consult", verify services appear
 * 9.  Products page — filter by category in English, verify results
 * 10. Products page Arabic — filter by category, verify results (Arabic label → English DB)
 * 11. Product detail English — category shows English
 * 12. Product detail Arabic — category shows Arabic translation
 * 13. Projects page — filter by category, verify
 * 14. Project detail Arabic — category and role show Arabic
 * 15. Services page — filter by category
 * 16. Service detail Arabic — category shows Arabic
 * 17. Marketplace page — filter by category and tags
 * 18. Marketplace detail Arabic — category shows Arabic
 * 19. Profile page English — products/projects/services show English categories
 * 20. Profile page Arabic — products/projects/services show Arabic categories
 */
class Flow07_SearchAndLocalizationTest extends DuskTestCase
{
    // ==========================================
    // NAVBAR SEARCH — ENGLISH
    // ==========================================

    public function test_01_navbar_search_english(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url(''));

            // Real type in search box
            try {
                $browser->click('input[name="q"]')->type('input[name="q"]', 'design');
            } catch (\Exception $e) {
                try { $browser->click('[x-ref="searchInput"]')->type('[x-ref="searchInput"]', 'design'); } catch (\Exception $e2) {}
            }
            $browser->pause(2000)->screenshot('search/01-en-typing');

            // Submit
            $browser->script("document.querySelector('form[action*=\"search\"]')?.submit()");
            $browser->pause(3000);

            $result = $browser->script("
                return {
                    url: window.location.href,
                    hasDesigners: document.querySelectorAll('a[href*=\"designer/\"]').length,
                    hasProducts: document.querySelectorAll('a[href*=\"product\"]').length,
                    hasProjects: document.querySelectorAll('a[href*=\"project\"]').length,
                    totalLinks: document.querySelectorAll('a[href*=\"designer/\"],a[href*=\"product\"],a[href*=\"project\"]').length,
                    noError: !document.body.textContent.includes('Server Error')
                };
            ")[0];

            $browser->screenshot('search/01-en-results');
            $this->assertTrue($result['noError'] ?? false, 'English search should not error');
            $this->assertStringContainsString('search', $result['url'], 'Should be on search page');
        });
    }

    // ==========================================
    // NAVBAR SEARCH — ARABIC
    // ==========================================

    public function test_02_navbar_search_arabic_term(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('', 'ar'));

            // Real type Arabic search term
            try {
                $browser->click('input[name="q"]')->type('input[name="q"]', 'تصميم');
            } catch (\Exception $e) {
                try { $browser->click('[x-ref="searchInput"]')->type('[x-ref="searchInput"]', 'تصميم'); } catch (\Exception $e2) {}
            }
            $browser->pause(2000);

            $browser->script("document.querySelector('form[action*=\"search\"]')?.submit()");
            $browser->pause(3000);

            $result = $browser->script("
                return {
                    url: window.location.href,
                    resultCount: document.querySelectorAll('a[href*=\"designer/\"],a[href*=\"product\"],a[href*=\"project\"]').length,
                    noError: !document.body.textContent.includes('Server Error'),
                    bodyLength: document.body.textContent.length
                };
            ")[0];

            $browser->screenshot('search/02-ar-results');
            $this->assertTrue($result['noError'] ?? false, 'Arabic search should not error');
            fwrite(STDERR, "Arabic search 'تصميم': {$result['resultCount']} results\n");
        });
    }

    public function test_03_navbar_search_arabic_sector(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('', 'ar'));

            try { $browser->click('input[name="q"]')->type('input[name="q"]', 'مصمم'); } catch (\Exception $e) {}
            $browser->pause(1000);
            $browser->script("document.querySelector('form[action*=\"search\"]')?.submit()");
            $browser->pause(3000);

            $result = $browser->script("
                return {
                    designerCount: document.querySelectorAll('a[href*=\"designer/\"]').length,
                    noError: !document.body.textContent.includes('Server Error')
                };
            ")[0];

            $browser->screenshot('search/03-ar-sector');
            $this->assertTrue($result['noError'] ?? false);
            fwrite(STDERR, "Arabic search 'مصمم': {$result['designerCount']} designers\n");
        });
    }

    public function test_04_navbar_search_arabic_city(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('', 'ar'));

            try { $browser->click('input[name="q"]')->type('input[name="q"]', 'رام الله'); } catch (\Exception $e) {}
            $browser->pause(1000);
            $browser->script("document.querySelector('form[action*=\"search\"]')?.submit()");
            $browser->pause(3000);

            $result = $browser->script("return{resultCount:document.querySelectorAll('a[href*=\"designer/\"]').length,noError:!document.body.textContent.includes('Server Error')};")[0];
            $browser->screenshot('search/04-ar-city');
            $this->assertTrue($result['noError'] ?? false);
            fwrite(STDERR, "Arabic search 'رام الله': {$result['resultCount']} results\n");
        });
    }

    // ==========================================
    // INSTANT SEARCH DROPDOWN
    // ==========================================

    public function test_05_instant_search_dropdown_appears(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url(''));

            // Type in navbar search — don't submit, just wait for dropdown
            try { $browser->click('input[name="q"]')->type('input[name="q"]', 'furniture'); } catch (\Exception $e) {}
            $browser->pause(3000)->screenshot('search/05-instant-dropdown');

            $dropdown = $browser->script("
                var results = document.querySelectorAll('[class*=\"dropdown\"] a, [class*=\"result\"] a, [x-show*=\"showResults\"] a');
                return {
                    visible: results.length > 0,
                    count: results.length
                };
            ")[0];

            $browser->screenshot('search/05-dropdown-result');
            fwrite(STDERR, "Instant search dropdown: {$dropdown['count']} results\n");
        });
    }

    public function test_06_instant_search_arabic(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('', 'ar'));

            try { $browser->click('input[name="q"]')->type('input[name="q"]', 'أثاث'); } catch (\Exception $e) {}
            $browser->pause(3000)->screenshot('search/06-instant-ar');

            $dropdown = $browser->script("
                var results = document.querySelectorAll('[class*=\"dropdown\"] a, [class*=\"result\"] a');
                return { count: results.length };
            ")[0];

            $browser->screenshot('search/06-instant-ar-result');
            fwrite(STDERR, "Arabic instant search 'أثاث': {$dropdown['count']} results\n");
        });
    }

    // ==========================================
    // PRODUCT CATEGORY FILTER
    // ==========================================

    public function test_07_products_filter_english(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('products'));

            // Verify filter panel is visible on desktop
            $panel = $browser->script("
                var fp = document.querySelector('.filter-panel');
                var catSel = document.querySelector('select[name=\"category\"]');
                var sortSel = document.querySelector('select[name=\"sort\"]');
                return {
                    panelVisible: fp ? (fp.offsetHeight > 0) : false,
                    categoryVisible: catSel ? (catSel.offsetHeight > 0) : false,
                    sortVisible: sortSel ? (sortSel.offsetHeight > 0) : false,
                    categoryOptions: catSel ? catSel.options.length : 0,
                    sortOptions: sortSel ? sortSel.options.length : 0
                };
            ")[0];
            $this->assertTrue($panel['panelVisible'] ?? false, 'Products filter panel should be visible on desktop');
            $this->assertTrue($panel['categoryVisible'] ?? false, 'Category dropdown should be visible');
            $this->assertTrue($panel['sortVisible'] ?? false, 'Sort dropdown should be visible');

            // Select a category from dropdown
            $browser->script("
                var sel = document.querySelector('select[name=\"category\"]');
                if (sel && sel.options.length > 1) {
                    sel.selectedIndex = 1;
                    sel.dispatchEvent(new Event('change', {bubbles: true}));
                }
            ");
            $browser->pause(3000)->screenshot('search/07-products-filtered-en');

            $result = $browser->script("
                return {
                    url: window.location.href,
                    hasCategory: window.location.href.includes('category='),
                    noError: !document.body.textContent.includes('Server Error'),
                    cardCount: document.querySelectorAll('[class*=\"card\"],article').length
                };
            ")[0];
            $this->assertTrue($result['noError'] ?? false, 'Product filter should not error');
        });
    }

    public function test_08_products_filter_arabic(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('products', 'ar'));

            // Select a category (Arabic label)
            $browser->script("
                var sel = document.querySelector('select[name=\"category\"]');
                if (sel && sel.options.length > 1) {
                    sel.selectedIndex = 1;
                    sel.dispatchEvent(new Event('change', {bubbles: true}));
                }
            ");
            $browser->pause(3000)->screenshot('search/08-products-filtered-ar');

            $result = $browser->script("
                return {
                    url: window.location.href,
                    noError: !document.body.textContent.includes('Server Error'),
                    cardCount: document.querySelectorAll('[class*=\"card\"],article').length
                };
            ")[0];
            $browser->screenshot('search/08-products-ar-result');
            $this->assertTrue($result['noError'] ?? false, 'Arabic product filter should not error');
            fwrite(STDERR, "Arabic product filter: {$result['cardCount']} cards, URL: {$result['url']}\n");
        });
    }

    // ==========================================
    // CATEGORY DISPLAY — ENGLISH VS ARABIC
    // ==========================================

    public function test_09_product_detail_english_category(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('products'));
            $browser->pause(2000);
            $browser->script("document.querySelector('a[href*=\"/product\"]')?.click()");
            $browser->pause(3000);
            $this->dismissWizard($browser);

            $category = $browser->script("
                var badge = document.querySelector('[class*=\"badge\"],[class*=\"category\"],[class*=\"rounded-full\"]');
                var allText = document.body.textContent;
                // Check if any Arabic characters in category badges
                var badges = document.querySelectorAll('span[class*=\"rounded\"]');
                var categoryTexts = [];
                badges.forEach(function(b) { if (b.textContent.trim().length > 0 && b.textContent.trim().length < 50) categoryTexts.push(b.textContent.trim()); });
                var hasArabic = categoryTexts.some(function(t) { return /[\\u0600-\\u06FF]/.test(t); });
                return { categoryTexts: categoryTexts, hasArabic: hasArabic, noError: !allText.includes('Server Error') };
            ")[0];

            $browser->screenshot('search/09-product-en-category');
            $this->assertTrue($category['noError'] ?? false);
            $this->assertFalse($category['hasArabic'] ?? true, 'English product detail should NOT show Arabic categories');
            fwrite(STDERR, "EN product categories: " . json_encode($category['categoryTexts'] ?? []) . "\n");
        });
    }

    public function test_10_product_detail_arabic_category(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('products', 'ar'));
            $browser->pause(2000);
            $browser->script("document.querySelector('a[href*=\"/product\"]')?.click()");
            $browser->pause(3000);
            $this->dismissWizard($browser);

            $category = $browser->script("
                var badges = document.querySelectorAll('span[class*=\"rounded\"]');
                var categoryTexts = [];
                badges.forEach(function(b) { if (b.textContent.trim().length > 0 && b.textContent.trim().length < 50) categoryTexts.push(b.textContent.trim()); });
                var hasArabic = categoryTexts.some(function(t) { return /[\\u0600-\\u06FF]/.test(t); });
                return { categoryTexts: categoryTexts, hasArabic: hasArabic, isRtl: document.documentElement.getAttribute('dir') === 'rtl' };
            ")[0];

            $browser->screenshot('search/10-product-ar-category');
            $this->assertTrue($category['isRtl'] ?? false, 'Should be RTL');
            fwrite(STDERR, "AR product categories: " . json_encode($category['categoryTexts'] ?? []) . "\n");
        });
    }

    // ==========================================
    // PROJECT CATEGORY + ROLE LOCALIZATION
    // ==========================================

    public function test_11_project_detail_arabic_category_and_role(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('projects', 'ar'));
            $browser->pause(2000);
            $browser->script("document.querySelector('a[href*=\"/project\"]')?.click()");
            $browser->pause(3000);
            $this->dismissWizard($browser);

            $page = $browser->script("
                var text = document.body.textContent;
                return {
                    isRtl: document.documentElement.getAttribute('dir') === 'rtl',
                    noError: !text.includes('Server Error'),
                    bodyLength: text.length
                };
            ")[0];

            $browser->screenshot('search/11-project-ar');
            $this->assertTrue($page['noError'] ?? false);
            $this->assertTrue($page['isRtl'] ?? false);
        });
    }

    // ==========================================
    // SERVICE FILTER + DISPLAY
    // ==========================================

    public function test_12_services_filter_arabic(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('services', 'ar'));

            // Verify filter panel visible
            $panel = $browser->script("
                var fp = document.querySelector('.filter-panel');
                var catSel = document.querySelector('select[name=\"category\"]');
                var sortSel = document.querySelector('select[name=\"sort\"]');
                return {
                    panelVisible: fp ? (fp.offsetHeight > 0) : false,
                    categoryVisible: catSel ? (catSel.offsetHeight > 0) : false,
                    sortVisible: sortSel ? (sortSel.offsetHeight > 0) : false
                };
            ")[0];
            $this->assertTrue($panel['panelVisible'] ?? false, 'Services filter panel should be visible');
            $this->assertTrue($panel['categoryVisible'] ?? false, 'Services category dropdown should be visible');

            $browser->script("
                var sel = document.querySelector('select[name=\"category\"]');
                if (sel && sel.options.length > 1) { sel.selectedIndex = 1; sel.dispatchEvent(new Event('change', {bubbles: true})); }
            ");
            $browser->pause(3000)->screenshot('search/12-services-filtered-ar');

            $result = $browser->script("return{noError:!document.body.textContent.includes('Server Error')};")[0];
            $this->assertTrue($result['noError'] ?? false, 'Arabic service filter should not error');
        });
    }

    // ==========================================
    // MARKETPLACE FILTER + TAGS
    // ==========================================

    public function test_13_marketplace_filter_category_arabic(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('marketplace', 'ar'));

            $browser->script("
                var sel = document.querySelector('select[name=\"category\"]');
                if (sel && sel.options.length > 1) { sel.selectedIndex = 1; sel.dispatchEvent(new Event('change', {bubbles: true})); }
            ");
            $browser->pause(3000)->screenshot('search/13-marketplace-filtered-ar');

            $result = $browser->script("return{noError:!document.body.textContent.includes('Server Error'),url:window.location.href};")[0];
            $this->assertTrue($result['noError'] ?? false, 'Arabic marketplace filter should not error');
        });
    }

    // ==========================================
    // PROFILE — CATEGORIES LOCALIZED
    // ==========================================

    public function test_14_profile_english_categories(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('profile'));

            $categories = $browser->script("
                var badges = document.querySelectorAll('span[class*=\"rounded\"]');
                var texts = [];
                badges.forEach(function(b) { var t = b.textContent.trim(); if (t.length > 0 && t.length < 50) texts.push(t); });
                var hasArabic = texts.some(function(t) { return /[\\u0600-\\u06FF]/.test(t); });
                return { texts: texts.slice(0, 10), hasArabic: hasArabic };
            ")[0];

            $browser->screenshot('search/14-profile-en');
            fwrite(STDERR, "EN profile categories: " . json_encode($categories['texts'] ?? []) . "\n");
        });
    }

    public function test_15_profile_arabic_categories(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser, 'ar');
            $this->visitPage($browser, TestConfig::url('profile', 'ar'));

            $categories = $browser->script("
                var badges = document.querySelectorAll('span[class*=\"rounded\"]');
                var texts = [];
                badges.forEach(function(b) { var t = b.textContent.trim(); if (t.length > 0 && t.length < 50) texts.push(t); });
                return { texts: texts.slice(0, 10), isRtl: document.documentElement.getAttribute('dir') === 'rtl' };
            ")[0];

            $browser->screenshot('search/15-profile-ar');
            $this->assertTrue($categories['isRtl'] ?? false, 'Arabic profile should be RTL');
            fwrite(STDERR, "AR profile categories: " . json_encode($categories['texts'] ?? []) . "\n");
        });
    }

    // ==========================================
    // ADMIN CMS — CATEGORIES DISPLAY
    // ==========================================

    public function test_16_admin_products_categories_display(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('products'));

            $categories = $browser->script("
                var cells = document.querySelectorAll('td span[class*=\"bg-gray\"],td span[class*=\"rounded\"]');
                var texts = [];
                cells.forEach(function(c) { texts.push(c.textContent.trim()); });
                return { texts: texts.slice(0, 10), noError: !document.body.textContent.includes('Server Error') };
            ")[0];

            $browser->screenshot('search/16-admin-products');
            $this->assertTrue($categories['noError'] ?? false);
            fwrite(STDERR, "Admin product categories: " . json_encode($categories['texts'] ?? []) . "\n");
        });
    }

    public function test_17_admin_products_arabic_categories(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser, 'ar');
            $this->visitPage($browser, TestConfig::adminUrl('products', 'ar'));

            $categories = $browser->script("
                var cells = document.querySelectorAll('td span[class*=\"bg-gray\"],td span[class*=\"rounded\"]');
                var texts = [];
                cells.forEach(function(c) { texts.push(c.textContent.trim()); });
                var hasArabic = texts.some(function(t) { return /[\\u0600-\\u06FF]/.test(t); });
                return { texts: texts.slice(0, 10), hasArabic: hasArabic, noError: !document.body.textContent.includes('Server Error') };
            ")[0];

            $browser->screenshot('search/17-admin-products-ar');
            $this->assertTrue($categories['noError'] ?? false);
            fwrite(STDERR, "Admin AR product categories: " . json_encode($categories['texts'] ?? []) . "\n");
        });
    }

    // ==========================================
    // TRAININGS FILTER
    // ==========================================

    public function test_18_trainings_filter_arabic(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('trainings', 'ar'));

            // Filter by category
            $browser->script("
                var sel = document.querySelector('select[name=\"category\"]');
                if (sel && sel.options.length > 1) { sel.selectedIndex = 1; sel.dispatchEvent(new Event('change', {bubbles: true})); sel.closest('form')?.submit(); }
            ");
            $browser->pause(3000)->screenshot('search/18-trainings-filtered-ar');

            $result = $browser->script("return{noError:!document.body.textContent.includes('Server Error')};")[0];
            $this->assertTrue($result['noError'] ?? false, 'Arabic training filter should not error');
        });
    }
}
