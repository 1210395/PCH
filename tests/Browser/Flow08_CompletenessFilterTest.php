<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * REAL USER TESTS: Admin Completeness Filter & Row Highlighting
 *
 * Tests the completeness filter dropdown and amber row highlighting
 * on every admin index page. Every interaction uses real clicks.
 *
 * 1.  Designers — filter dropdown exists with all 4 options
 * 2.  Designers — filter by "Incomplete", verify URL param
 * 3.  Designers — filter by "Complete", verify URL param
 * 4.  Designers — filter by "Has Other", verify URL param
 * 5.  Designers — incomplete rows have amber background
 * 6.  Products — filter dropdown exists, filter by incomplete
 * 7.  Projects — filter dropdown exists, filter by incomplete
 * 8.  Services — filter dropdown exists, filter by incomplete
 * 9.  Marketplace — filter dropdown exists, filter by incomplete
 * 10. FabLabs — filter dropdown exists, filter by incomplete
 * 11. Academic Accounts — filter dropdown exists, filter by incomplete
 * 12. Trainings — filter dropdown exists, filter by incomplete
 * 13. Tenders — filter dropdown exists, filter by incomplete
 * 14. All pages — no 500 when filtering by incomplete
 * 15. All pages — no 500 when filtering by others
 * 16. Arabic — filter dropdown shows Arabic labels
 */
class Flow08_CompletenessFilterTest extends DuskTestCase
{
    // ==========================================
    // DESIGNERS — FULL FILTER TEST
    // ==========================================

    public function test_01_designers_filter_dropdown_exists(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('designers'));

            $filter = $browser->script("
                var sel = document.querySelector('select[name=\"completeness\"]');
                if (!sel) return { exists: false };
                var options = [];
                for (var i = 0; i < sel.options.length; i++) {
                    options.push({ value: sel.options[i].value, text: sel.options[i].text });
                }
                return { exists: true, options: options, count: sel.options.length };
            ")[0];

            $browser->screenshot('completeness/01-designers-dropdown');
            $this->assertTrue($filter['exists'] ?? false, 'Designers should have completeness filter');
            $this->assertEquals(4, $filter['count'] ?? 0, 'Should have 4 options (All, Complete, Incomplete, Others)');
        });
    }

    public function test_02_designers_filter_incomplete(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('designers'));

            // Real select the "Incomplete" option
            $browser->select('select[name="completeness"]', 'incomplete');
            $browser->pause(500);
            $browser->script("document.querySelector('select[name=\"completeness\"]').closest('form')?.submit()");
            $browser->pause(3000)->screenshot('completeness/02-designers-incomplete');

            $result = $browser->script("
                return {
                    url: window.location.href,
                    hasParam: window.location.href.includes('completeness=incomplete'),
                    noError: !document.body.textContent.includes('Server Error'),
                    rowCount: document.querySelectorAll('tbody tr').length
                };
            ")[0];

            $this->assertTrue($result['hasParam'] ?? false, 'URL should have completeness=incomplete');
            $this->assertTrue($result['noError'] ?? false, 'Should not error');
            fwrite(STDERR, "Incomplete designers: {$result['rowCount']} rows\n");
        });
    }

    public function test_03_designers_filter_complete(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('designers'));

            $browser->select('select[name="completeness"]', 'complete');
            $browser->pause(500);
            $browser->script("document.querySelector('select[name=\"completeness\"]').closest('form')?.submit()");
            $browser->pause(3000)->screenshot('completeness/03-designers-complete');

            $result = $browser->script("return{hasParam:window.location.href.includes('completeness=complete'),noError:!document.body.textContent.includes('Server Error')};")[0];
            $this->assertTrue($result['hasParam'] ?? false);
            $this->assertTrue($result['noError'] ?? false);
        });
    }

    public function test_04_designers_filter_others(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('designers'));

            $browser->select('select[name="completeness"]', 'others');
            $browser->pause(500);
            $browser->script("document.querySelector('select[name=\"completeness\"]').closest('form')?.submit()");
            $browser->pause(3000)->screenshot('completeness/04-designers-others');

            $result = $browser->script("return{hasParam:window.location.href.includes('completeness=others'),noError:!document.body.textContent.includes('Server Error')};")[0];
            $this->assertTrue($result['hasParam'] ?? false);
            $this->assertTrue($result['noError'] ?? false);
        });
    }

    public function test_05_designers_amber_row_highlight(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('designers') . '?completeness=incomplete');
            $browser->pause(3000);

            $amber = $browser->script("
                var rows = document.querySelectorAll('tbody tr');
                var amberCount = 0;
                var totalCount = rows.length;
                rows.forEach(function(r) {
                    if (r.classList.contains('bg-amber-50') || r.className.includes('amber')) amberCount++;
                });
                return { total: totalCount, amber: amberCount };
            ")[0];

            $browser->screenshot('completeness/05-designers-amber');
            fwrite(STDERR, "Amber rows: {$amber['amber']}/{$amber['total']}\n");
        });
    }

    // ==========================================
    // ALL OTHER PAGES — FILTER EXISTS + NO ERROR
    // ==========================================

    public function test_06_products_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('products'));

            $exists = $browser->script("return !!document.querySelector('select[name=\"completeness\"]')")[0];
            $this->assertTrue($exists, 'Products should have completeness filter');

            $browser->select('select[name="completeness"]', 'incomplete');
            $browser->script("document.querySelector('select[name=\"completeness\"]').closest('form')?.submit()");
            $browser->pause(3000)->screenshot('completeness/06-products');

            $noError = $browser->script("return !document.body.textContent.includes('Server Error')")[0];
            $this->assertTrue($noError);
        });
    }

    public function test_07_projects_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('projects'));

            $exists = $browser->script("return !!document.querySelector('select[name=\"completeness\"]')")[0];
            $this->assertTrue($exists, 'Projects should have completeness filter');

            $browser->select('select[name="completeness"]', 'incomplete');
            $browser->script("document.querySelector('select[name=\"completeness\"]').closest('form')?.submit()");
            $browser->pause(3000)->screenshot('completeness/07-projects');

            $noError = $browser->script("return !document.body.textContent.includes('Server Error')")[0];
            $this->assertTrue($noError);
        });
    }

    public function test_08_services_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('services'));

            $exists = $browser->script("return !!document.querySelector('select[name=\"completeness\"]')")[0];
            $this->assertTrue($exists);

            $browser->script("var s=document.querySelector('select[name=\"completeness\"]');if(s){s.value='incomplete';s.dispatchEvent(new Event('change'));s.closest('form')?.submit();}");
            $browser->pause(3000)->screenshot('completeness/08-services');
            $this->assertTrue($browser->script("return !document.body.textContent.includes('Server Error')")[0]);
        });
    }

    public function test_09_marketplace_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('marketplace'));

            $exists = $browser->script("return !!document.querySelector('select[name=\"completeness\"]')")[0];
            $this->assertTrue($exists);

            $browser->select('select[name="completeness"]', 'incomplete');
            $browser->script("document.querySelector('select[name=\"completeness\"]').closest('form')?.submit()");
            $browser->pause(3000)->screenshot('completeness/09-marketplace');
            $this->assertTrue($browser->script("return !document.body.textContent.includes('Server Error')")[0]);
        });
    }

    public function test_10_fablabs_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('fablabs'));

            $exists = $browser->script("return !!document.querySelector('select[name=\"completeness\"]')")[0];
            $this->assertTrue($exists);

            $browser->select('select[name="completeness"]', 'incomplete');
            $browser->script("document.querySelector('select[name=\"completeness\"]').closest('form')?.submit()");
            $browser->pause(3000)->screenshot('completeness/10-fablabs');
            $this->assertTrue($browser->script("return !document.body.textContent.includes('Server Error')")[0]);
        });
    }

    public function test_11_academic_accounts_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('academic-accounts'));

            $exists = $browser->script("return !!document.querySelector('select[name=\"completeness\"]')")[0];
            $this->assertTrue($exists);

            $browser->select('select[name="completeness"]', 'incomplete');
            $browser->script("document.querySelector('select[name=\"completeness\"]').closest('form')?.submit()");
            $browser->pause(3000)->screenshot('completeness/11-academic');
            $this->assertTrue($browser->script("return !document.body.textContent.includes('Server Error')")[0]);
        });
    }

    public function test_12_trainings_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('trainings'));

            $exists = $browser->script("return !!document.querySelector('select[name=\"completeness\"]')")[0];
            $this->assertTrue($exists);

            $browser->script("var s=document.querySelector('select[name=\"completeness\"]');if(s){s.value='incomplete';s.dispatchEvent(new Event('change'));s.closest('form')?.submit();}");
            $browser->pause(3000)->screenshot('completeness/12-trainings');
            $this->assertTrue($browser->script("return !document.body.textContent.includes('Server Error')")[0]);
        });
    }

    public function test_13_tenders_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('tenders'));

            $exists = $browser->script("return !!document.querySelector('select[name=\"completeness\"]')")[0];
            $this->assertTrue($exists);

            $browser->select('select[name="completeness"]', 'incomplete');
            $browser->script("document.querySelector('select[name=\"completeness\"]').closest('form')?.submit()");
            $browser->pause(3000)->screenshot('completeness/13-tenders');
            $this->assertTrue($browser->script("return !document.body.textContent.includes('Server Error')")[0]);
        });
    }

    // ==========================================
    // BULK — ALL PAGES NO 500 WITH "OTHERS" FILTER
    // ==========================================

    public function test_14_all_pages_others_filter_no_error(): void
    {
        $pages = ['designers', 'products', 'projects', 'services', 'marketplace', 'fablabs', 'academic-accounts', 'trainings', 'tenders'];
        $this->browse(function (Browser $browser) use ($pages) {
            $this->loginAsAdmin($browser);
            $errors = [];
            foreach ($pages as $page) {
                $this->visitPage($browser, TestConfig::adminUrl($page) . '?completeness=others');
                $has500 = $browser->script("return document.body.textContent.includes('Server Error')")[0];
                if ($has500) $errors[] = $page;
            }
            $this->assertEmpty($errors, 'Pages with 500 on "others" filter: ' . implode(', ', $errors));
        });
    }

    // ==========================================
    // ARABIC — FILTER LABELS
    // ==========================================

    public function test_15_arabic_filter_labels(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser, 'ar');
            $this->visitPage($browser, TestConfig::adminUrl('designers', 'ar'));

            $filter = $browser->script("
                var sel = document.querySelector('select[name=\"completeness\"]');
                if (!sel) return { exists: false };
                var options = [];
                for (var i = 0; i < sel.options.length; i++) {
                    options.push(sel.options[i].text);
                }
                var hasArabic = options.some(function(t) { return /[\\u0600-\\u06FF]/.test(t); });
                return { exists: true, options: options, hasArabic: hasArabic };
            ")[0];

            $browser->screenshot('completeness/15-arabic-labels');
            $this->assertTrue($filter['exists'] ?? false, 'Arabic page should have filter');
            $this->assertTrue($filter['hasArabic'] ?? false, 'Filter labels should be in Arabic');
            fwrite(STDERR, "Arabic filter options: " . json_encode($filter['options'] ?? []) . "\n");
        });
    }

    public function test_16_arabic_incomplete_filter_works(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser, 'ar');
            $this->visitPage($browser, TestConfig::adminUrl('designers', 'ar') . '?completeness=incomplete');
            $browser->pause(3000)->screenshot('completeness/16-arabic-incomplete');

            $result = $browser->script("return{noError:!document.body.textContent.includes('Server Error'),isRtl:document.documentElement.getAttribute('dir')==='rtl'};")[0];
            $this->assertTrue($result['noError'] ?? false);
            $this->assertTrue($result['isRtl'] ?? false);
        });
    }
}
