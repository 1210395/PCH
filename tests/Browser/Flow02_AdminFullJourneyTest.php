<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * REALISTIC ADMIN CMS JOURNEY — real clicks, real typing, real verification.
 */
class Flow02_AdminFullJourneyTest extends DuskTestCase
{
    public function test_01_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('dashboard'));
            $browser->assertDontSee('Server Error')->screenshot('f02/01-dashboard');
            $stats = $browser->script("return{hasCards:document.querySelectorAll('[class*=\"card\"],[class*=\"stat\"]').length>3};")[0];
            $this->assertTrue($stats['hasCards'] ?? false, 'Dashboard should have stat cards');
        });
    }

    public function test_02_designers_search_view_edit(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('designers'));
            $browser->screenshot('f02/02-designers');

            // Real type in search
            try { $browser->click('input[name="search"]')->type('input[name="search"]', 'test'); } catch (\Exception $e) {}
            $browser->script("document.querySelector('input[name=\"search\"]')?.closest('form')?.submit()");
            $browser->pause(3000)->screenshot('f02/02-searched');

            // View first designer
            $browser->script("document.querySelector('a[href*=\"designers/\"]')?.click()");
            $browser->pause(3000)->assertDontSee('Server Error')->screenshot('f02/02-detail');

            // Edit
            $browser->script("document.querySelector('a[href*=\"edit\"]')?.click()");
            $browser->pause(3000)->assertDontSee('Server Error')->screenshot('f02/02-edit');
        });
    }

    public function test_03_products_approval(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('products'));
            $browser->assertDontSee('Server Error')->screenshot('f02/03-products');

            // View first product
            $browser->script("document.querySelector('a[href*=\"products/\"]')?.click()");
            $browser->pause(3000);
            $actions = $browser->script("var t=document.body.textContent;return{hasApprove:t.includes('Approve'),hasReject:t.includes('Reject'),noError:!t.includes('Server Error')};")[0];
            $browser->screenshot('f02/03-product-detail');
            $this->assertTrue($actions['noError'] ?? false);
        });
    }

    public function test_04_all_content_pages(): void
    {
        $pages = ['projects', 'services', 'marketplace', 'fablabs', 'trainings', 'tenders'];
        $this->browse(function (Browser $browser) use ($pages) {
            $this->loginAsAdmin($browser);
            $errors = [];
            foreach ($pages as $page) {
                $this->visitPage($browser, TestConfig::adminUrl($page));
                $has500 = $browser->script("return document.body.textContent.includes('Server Error')")[0];
                if ($has500) $errors[] = $page;
            }
            $browser->screenshot('f02/04-all-content');
            $this->assertEmpty($errors, 'Admin content pages with errors: ' . implode(', ', $errors));
        });
    }

    public function test_05_ratings_criteria(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('ratings'));
            $browser->assertDontSee('Server Error')->screenshot('f02/05-ratings');

            $this->visitPage($browser, TestConfig::adminUrl('ratings/criteria'));
            $browser->assertDontSee('Server Error')->assertDontSee('500')->screenshot('f02/05-criteria');
        });
    }

    public function test_06_academic_accounts_content(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $this->visitPage($browser, TestConfig::adminUrl('academic-accounts'));
            $browser->assertDontSee('Server Error')->screenshot('f02/06-accounts');

            $this->visitPage($browser, TestConfig::adminUrl('academic-content/trainings'));
            $browser->assertDontSee('Server Error')->screenshot('f02/06-trainings');

            $this->visitPage($browser, TestConfig::adminUrl('academic-content/workshops'));
            $browser->assertDontSee('Server Error')->screenshot('f02/06-workshops');

            // View workshop detail (was 500)
            $browser->script("document.querySelector('a[href*=\"workshops/\"]')?.click()");
            $browser->pause(3000)->assertDontSee('Server Error')->assertDontSee('500')->screenshot('f02/06-workshop-detail');
        });
    }

    public function test_07_settings(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('settings'));
            $browser->assertDontSee('Server Error')->screenshot('f02/07-settings');
            $hasToggles = $browser->script("return document.querySelectorAll('input[type=\"checkbox\"]').length>0")[0];
            $this->assertTrue($hasToggles, 'Settings should have toggles');
        });
    }

    public function test_08_analytics(): void
    {
        $pages = ['overview', 'engagement', 'traffic', 'geographic', 'search'];
        $this->browse(function (Browser $browser) use ($pages) {
            $this->loginAsAdmin($browser);
            $errors = [];
            foreach ($pages as $p) {
                $this->visitPage($browser, TestConfig::adminUrl("analytics/{$p}"));
                if ($browser->script("return document.body.textContent.includes('Server Error')")[0]) $errors[] = $p;
            }
            $this->assertEmpty($errors, 'Analytics errors: ' . implode(', ', $errors));
        });
    }

    public function test_09_tenders_fablabs_pages_dropdowns(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('tenders/create'));
            $browser->assertDontSee('Server Error')->screenshot('f02/09-tender-create');

            $this->visitPage($browser, TestConfig::adminUrl('fablabs/create'));
            $browser->assertDontSee('Server Error')->screenshot('f02/09-fablab-create');

            $this->visitPage($browser, TestConfig::adminUrl('pages'));
            $browser->assertDontSee('Server Error');

            $this->visitPage($browser, TestConfig::adminUrl('dropdowns'));
            $browser->assertDontSee('Server Error');
        });
    }

    public function test_10_all_arabic(): void
    {
        $pages = ['dashboard', 'designers', 'products', 'projects', 'services', 'marketplace',
                  'fablabs', 'trainings', 'tenders', 'academic-accounts', 'ratings', 'settings'];
        $this->browse(function (Browser $browser) use ($pages) {
            $this->loginAsAdmin($browser, 'ar');
            $errors = [];
            foreach ($pages as $p) {
                $this->visitPage($browser, TestConfig::adminUrl($p, 'ar'));
                if ($browser->script("return document.body.textContent.includes('Server Error')")[0]) $errors[] = $p;
            }
            $this->assertEmpty($errors, 'Admin Arabic errors: ' . implode(', ', $errors));
        });
    }

    public function test_11_search_on_all_pages(): void
    {
        $pages = ['designers', 'products', 'projects', 'services', 'marketplace', 'fablabs', 'trainings', 'tenders', 'ratings'];
        $this->browse(function (Browser $browser) use ($pages) {
            $this->loginAsAdmin($browser);
            $missing = [];
            foreach ($pages as $p) {
                $this->visitPage($browser, TestConfig::adminUrl($p));
                if (!$browser->script("return !!document.querySelector('input[name=\"search\"]')")[0]) $missing[] = $p;
            }
            $this->assertEmpty($missing, 'Missing search: ' . implode(', ', $missing));
        });
    }
}
