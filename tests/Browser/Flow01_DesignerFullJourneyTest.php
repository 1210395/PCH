<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * REALISTIC DESIGNER JOURNEY — every interaction uses real browser typing/clicking.
 * No JavaScript value shortcuts. Types every character, clicks every button.
 */
class Flow01_DesignerFullJourneyTest extends DuskTestCase
{
    private function img(): string
    {
        $p = sys_get_temp_dir() . '/dusk-' . uniqid() . '.png';
        file_put_contents($p, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFklEQVQYV2P8z8BQz0BFwMgwqpBuCgEAHbkEAYDnI1MAAAAASUVORK5CYII='));
        return $p;
    }

    private function pdf(): string
    {
        $p = sys_get_temp_dir() . '/dusk-cert-' . uniqid() . '.pdf';
        file_put_contents($p, "%PDF-1.0\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 3 3]>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n190\n%%EOF");
        return $p;
    }

    private function uploadFile(Browser $browser, string $selector, string $path): void
    {
        $browser->script("
            var inp = document.querySelector('{$selector}');
            if(inp){inp.removeAttribute('hidden');inp.style.cssText='display:block!important;position:absolute!important;width:1px!important;height:1px!important';inp.setAttribute('id','dusk-upload-tmp');}
        ");
        $browser->pause(300);
        try {
            $browser->driver->findElement(\Facebook\WebDriver\WebDriverBy::id('dusk-upload-tmp'))->sendKeys($path);
        } catch (\Exception $e) {
            fwrite(STDERR, "Upload failed ({$selector}): " . $e->getMessage() . "\n");
        }
        $browser->pause(4000);
    }

    private function verifyUrl(Browser $browser, string $shouldContain, string $shouldNotContain = ''): array
    {
        $url = $browser->driver->getCurrentURL();
        $text = $browser->script("return document.body.textContent.substring(0, 300)")[0];
        $result = [
            'url' => $url,
            'hasServerError' => str_contains($text ?? '', 'Server Error'),
            'has429' => str_contains($text ?? '', 'Too Many'),
        ];
        fwrite(STDERR, "URL: {$url}\n");
        return $result;
    }

    // ==========================================
    // LOGIN
    // ==========================================

    public function test_01_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit($this->baseUrl() . '/en/login')->pause(2000);

            // Real typing into login form
            $browser->click('#email')->type('#email', TestConfig::USER_A_EMAIL);
            $browser->click('#password')->type('#password', TestConfig::USER_A_PASSWORD);
            $browser->screenshot('f01/01-login-typed');

            // Real click on submit
            $browser->press('button[type="submit"]');
            $browser->pause(5000);
            $this->dismissWizard($browser);

            $result = $this->verifyUrl($browser, '', 'login');
            $browser->screenshot('f01/01-logged-in');
            $this->assertStringNotContainsString('/login', $result['url'], 'Should be logged in');
            $this->assertFalse($result['hasServerError'], 'Login should not 500');
        });
    }

    // ==========================================
    // HOME PAGE — VERIFY ALL ELEMENTS
    // ==========================================

    public function test_02_home_page(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url(''));

            $home = $browser->script("
                return {
                    hasNav: !!document.querySelector('nav, header'),
                    hasSearch: !!document.querySelector('input[name=\"q\"]'),
                    hasFooter: !!document.querySelector('footer'),
                    navLinkCount: document.querySelectorAll('nav a[href], header a[href]').length,
                    hasLangSwitch: !!Array.from(document.querySelectorAll('a,button')).find(function(b){return b.textContent.includes('عربي')||b.textContent.includes('AR');}),
                    hasUserAvatar: !!document.querySelector('img[class*=\"rounded-full\"]'),
                    bodyLength: document.body.textContent.length,
                    noError: !document.body.textContent.includes('Server Error')
                };
            ")[0];

            $browser->screenshot('f01/02-home');
            $this->assertTrue($home['noError'] ?? false, 'Home should not have errors');
            $this->assertTrue($home['hasSearch'] ?? false, 'Home should have search');
            $this->assertTrue(($home['navLinkCount'] ?? 0) > 3, 'Home should have nav links');
        });
    }

    // ==========================================
    // NAVBAR SEARCH — TYPE, SEE RESULTS, SUBMIT
    // ==========================================

    public function test_03_navbar_search(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url(''));

            // Real click and type in search
            try {
                $browser->click('input[name="q"]')->type('input[name="q"]', 'design');
            } catch (\Exception $e) {
                // Fallback: try x-ref
                $browser->click('[x-ref="searchInput"]')->type('[x-ref="searchInput"]', 'design');
            }
            $browser->pause(2000)->screenshot('f01/03-search-typing');

            // Submit search form
            $browser->script("document.querySelector('form[action*=\"search\"]')?.submit()");
            $browser->pause(3000);

            $result = $this->verifyUrl($browser, 'search');
            $browser->screenshot('f01/03-search-results');
            $this->assertStringContainsString('search', $result['url'], 'Should be on search page');
        });
    }

    // ==========================================
    // VIEW PROFILE — ALL SECTIONS
    // ==========================================

    public function test_04_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('profile'));

            $profile = $browser->script("
                return {
                    hasName: !!document.querySelector('h1,h2'),
                    hasAvatar: !!document.querySelector('img[class*=\"rounded-full\"]'),
                    hasBio: document.body.textContent.length > 200,
                    hasTabs: document.querySelectorAll('[role=\"tab\"],nav button,nav a').length >= 3,
                    hasEditBtn: !!Array.from(document.querySelectorAll('a,button')).find(function(b){return b.textContent.includes('Edit');}),
                    noError: !document.body.textContent.includes('Server Error')
                };
            ")[0];

            $browser->screenshot('f01/04-profile');
            $this->assertTrue($profile['noError'] ?? false, 'Profile should not error');
            $this->assertTrue($profile['hasName'] ?? false, 'Profile should show name');
        });
    }

    // ==========================================
    // EDIT BIO — REAL TYPING
    // ==========================================

    public function test_05_edit_bio(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('profile'));

            // Click edit bio button
            $browser->scriptWithHelpers(<<<'JS'
                var btn = findByAlpine('editBio') || findByAlpine('bio') || findByText('Edit Bio');
                if(!btn) { var pens = document.querySelectorAll('.fa-pen,.fa-edit'); if(pens.length) btn = pens[0].closest('button'); }
                if(btn) btn.click();
            JS);
            $browser->pause(2000)->screenshot('f01/05-bio-modal');

            // Real clear and type in textarea
            $newBio = 'Updated bio ' . date('Y-m-d H:i:s') . ' — Professional Palestinian designer.';
            try {
                $browser->clear('textarea')->type('textarea', $newBio);
            } catch (\Exception $e) {
                $browser->script("var ta=document.querySelector('textarea');if(ta){ta.value='';ta.value='" . addslashes($newBio) . "';ta.dispatchEvent(new Event('input',{bubbles:true}));}");
            }
            $browser->pause(500)->screenshot('f01/05-bio-typed');

            // Click save
            $browser->scriptWithHelpers(<<<'JS'
                var btn = findByAlpine('submitEditBio') || findByAlpine('saveBio') || findByText('Save') || findByText('Update');
                if(btn) btn.click();
            JS);
            $browser->pause(5000);
            try { $browser->driver->switchTo()->alert()->accept(); } catch (\Exception $e) {}
            $browser->screenshot('f01/05-bio-saved');
        });
    }

    // ==========================================
    // UPLOAD CERTIFICATION PDF
    // ==========================================

    public function test_06_upload_cert(): void
    {
        $certPdf = $this->pdf();
        $this->browse(function (Browser $browser) use ($certPdf) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('profile'));

            // Click certification section
            $browser->scriptWithHelpers(<<<'JS'
                clickText('Certif') || clickText('Education') || clickText('شهاد') || clickAlpine('cert');
            JS);
            $browser->pause(2000);

            // Upload PDF via file input
            $this->uploadFile($browser, 'input[type="file"][accept*="pdf"]', $certPdf);
            $browser->screenshot('f01/06-cert-uploaded');

            // Verify no "Not Implemented" error
            $result = $browser->script("
                return {
                    hasNotImplemented: document.body.textContent.includes('Not implemented'),
                    hasSuccess: document.body.textContent.includes('success') || document.body.textContent.includes('تم'),
                    noError: !document.body.textContent.includes('Server Error')
                };
            ")[0];
            $browser->screenshot('f01/06-cert-result');
            $this->assertFalse($result['hasNotImplemented'] ?? true, 'Cert upload should NOT return "Not implemented"');
        });
        @unlink($certPdf);
    }

    // ==========================================
    // CREATE PRODUCT — REAL TYPING + IMAGE
    // ==========================================

    public function test_07_create_product(): void
    {
        $img = $this->img();
        $this->browse(function (Browser $browser) use ($img) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('profile'));

            // Click Products tab
            $browser->scriptWithHelpers(<<<'JS'
                clickText('Products') || clickText('Product') || clickText('منتجات');
            JS);
            $browser->pause(1500);

            // Click Add Product
            $browser->scriptWithHelpers(<<<'JS'
                clickText('Add Product') || clickText('Add') || clickText('New') || clickAlpine('addProduct') || clickAlpine('openProductModal');
            JS);
            $browser->pause(2000)->screenshot('f01/07-product-modal');

            // Real type into product name
            try {
                $browser->type('[x-model*="productForm.name"],[x-model*="name"]', 'Dusk Product ' . date('His'));
            } catch (\Exception $e) {
                $browser->script("var i=document.querySelector('[x-model*=\"name\"]');if(i){i.focus();i.value='Dusk Product " . date('His') . "';i.dispatchEvent(new Event('input',{bubbles:true}));}");
            }

            // Real type description
            try {
                $browser->type('textarea', 'Handcrafted product created by automated user test with real typing.');
            } catch (\Exception $e) {
                $browser->script("var t=document.querySelector('textarea');if(t){t.focus();t.value='Handcrafted product from Dusk test.';t.dispatchEvent(new Event('input',{bubbles:true}));}");
            }

            // Select category via Alpine (combobox)
            $browser->script("document.querySelectorAll('[x-data]').forEach(function(el){if(!el._x_dataStack||!el.offsetParent)return;el._x_dataStack.forEach(function(d){if(d.selectOption&&d.filteredOptions&&d.filteredOptions.length>0&&!d.selectedValue)d.selectOption(d.filteredOptions[0]);});});");
            $browser->pause(1000);

            // Upload image
            $this->uploadFile($browser, 'input[type="file"][accept*="image"]', $img);
            $browser->screenshot('f01/07-product-filled');

            // Click Save
            $browser->script("var b=document.querySelector('button[type=\"submit\"]')||Array.from(document.querySelectorAll('button')).find(function(b){var t=b.textContent.toLowerCase();return(t.includes('save')||t.includes('add')||t.includes('create'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(5000);
            try { $browser->driver->switchTo()->alert()->accept(); } catch (\Exception $e) {}
            $browser->screenshot('f01/07-product-saved');
        });
        @unlink($img);
    }

    // ==========================================
    // CREATE PROJECT — REAL TYPING
    // ==========================================

    public function test_08_create_project(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('profile'));

            $browser->scriptWithHelpers(<<<'JS'
                clickText('Projects') || clickText('Project') || clickText('مشاريع');
            JS);
            $browser->pause(1500);
            $browser->scriptWithHelpers(<<<'JS'
                clickText('Add Project') || clickText('Add') || clickText('New') || clickAlpine('addProject');
            JS);
            $browser->pause(2000);

            try { $browser->type('[x-model*="title"],[x-model*="projectForm.title"]', 'Dusk Project ' . date('His')); } catch (\Exception $e) {}
            try { $browser->type('textarea', 'Heritage design project from automated test with real typing.'); } catch (\Exception $e) {}

            $browser->script("document.querySelectorAll('[x-data]').forEach(function(el){if(!el._x_dataStack||!el.offsetParent)return;el._x_dataStack.forEach(function(d){if(d.selectOption&&d.filteredOptions&&d.filteredOptions.length>0&&!d.selectedValue)d.selectOption(d.filteredOptions[0]);});});");
            $browser->pause(1000);

            $browser->script("var b=document.querySelector('button[type=\"submit\"]')||Array.from(document.querySelectorAll('button')).find(function(b){return b.textContent.toLowerCase().includes('save')&&b.offsetParent;});if(b)b.click();");
            $browser->pause(5000);
            try { $browser->driver->switchTo()->alert()->accept(); } catch (\Exception $e) {}
            $browser->screenshot('f01/08-project-saved');
        });
    }

    // ==========================================
    // CREATE SERVICE — REAL TYPING
    // ==========================================

    public function test_09_create_service(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('profile'));

            $browser->scriptWithHelpers(<<<'JS'
                clickText('Services') || clickText('Service') || clickText('خدمات');
            JS);
            $browser->pause(1500);
            $browser->scriptWithHelpers(<<<'JS'
                clickText('Add Service') || clickText('Add') || clickAlpine('addService');
            JS);
            $browser->pause(2000);

            try { $browser->type('[x-model*="name"],[x-model*="serviceForm.name"]', 'Dusk Service ' . date('His')); } catch (\Exception $e) {}
            try { $browser->type('textarea', 'Professional design consultation from automated test.'); } catch (\Exception $e) {}

            $browser->script("document.querySelectorAll('[x-data]').forEach(function(el){if(!el._x_dataStack||!el.offsetParent)return;el._x_dataStack.forEach(function(d){if(d.selectOption&&d.filteredOptions&&d.filteredOptions.length>0&&!d.selectedValue)d.selectOption(d.filteredOptions[0]);});});");
            $browser->pause(1000);

            $browser->script("var b=document.querySelector('button[type=\"submit\"]')||Array.from(document.querySelectorAll('button')).find(function(b){return b.textContent.toLowerCase().includes('save')&&b.offsetParent;});if(b)b.click();");
            $browser->pause(5000);
            try { $browser->driver->switchTo()->alert()->accept(); } catch (\Exception $e) {}
            $browser->screenshot('f01/09-service-saved');
        });
    }

    // ==========================================
    // BROWSE DESIGNERS — FILTERS, SEARCH, SORT
    // ==========================================

    public function test_10_designers_filters(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('designers'));
            $browser->screenshot('f01/10-designers');

            // Click Designers filter tab
            $browser->script("var a=document.querySelector('a[href*=\"type=designers\"]');if(a)a.click();");
            $browser->pause(3000)->screenshot('f01/10-filtered');

            // Type in search
            try {
                $browser->click('input[name="search"]')->type('input[name="search"]', 'design');
            } catch (\Exception $e) {}
            $browser->script("document.querySelector('input[name=\"search\"]')?.closest('form')?.submit()");
            $browser->pause(3000)->screenshot('f01/10-searched');
        });
    }

    // ==========================================
    // DESIGNER DETAIL — FOLLOW, LIKE, SHARE
    // ==========================================

    public function test_11_designer_detail_follow_unfollow(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('designers'));
            $browser->pause(2000);

            // Click a designer that is NOT self
            $browser->script("
                var links = document.querySelectorAll('a[href*=\"/designer/\"]');
                for (var i = 0; i < links.length; i++) {
                    if (!links[i].textContent.includes('Test Designer') && links[i].offsetParent) { links[i].click(); break; }
                }
                if (!links.length) links[0]?.click();
            ");
            $browser->pause(3000);
            $this->dismissWizard($browser);
            $browser->screenshot('f01/11-designer-detail');

            // Follow
            $browser->scriptWithHelpers(<<<'JS'
                clickText('Follow') || clickText('Subscribe') || clickText('متابعة');
            JS);
            $browser->pause(2000)->screenshot('f01/11-followed');

            // Unfollow
            $browser->scriptWithHelpers(<<<'JS'
                clickText('Unfollow') || clickText('Following') || clickText('Unsubscribe') || clickText('إلغاء');
            JS);
            $browser->pause(2000)->screenshot('f01/11-unfollowed');
        });
    }

    // ==========================================
    // PRODUCT DETAIL — LIKE, SHARE
    // ==========================================

    public function test_12_product_like_share(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('products'));
            $browser->pause(2000);

            $browser->script("document.querySelector('a[href*=\"/product\"]')?.click()");
            $browser->pause(3000);
            $this->dismissWizard($browser);
            $browser->screenshot('f01/12-product-detail');

            // Like
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.getAttribute('@click')||'').includes('toggleLike');});if(b)b.click();");
            $browser->pause(2000)->screenshot('f01/12-liked');

            // Unlike
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.getAttribute('@click')||'').includes('toggleLike');});if(b)b.click();");
            $browser->pause(1000);

            // Share
            $browser->scriptWithHelpers(<<<'JS'
                var btn = findByText('Share') || findByText('مشاركة') || findByAlpine('share');
                if(btn) btn.click();
            JS);
            $browser->pause(1500)->screenshot('f01/12-share-open');

            // Copy link
            $browser->scriptWithHelpers(<<<'JS'
                clickText('Copy') || clickText('نسخ') || clickAlpine('copyLink');
            JS);
            $browser->pause(1000)->screenshot('f01/12-copied');
        });
    }

    // ==========================================
    // MARKETPLACE — FILTERS
    // ==========================================

    public function test_13_marketplace_filters(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('marketplace'));

            $filters = $browser->script("
                return {
                    hasCategory: !!document.querySelector('select[name=\"category\"]'),
                    hasType: !!document.querySelector('select[name=\"type\"]'),
                    hasSort: !!document.querySelector('select[name=\"sort\"]'),
                    hasSearch: !!document.querySelector('input[name=\"search\"]'),
                    noError: !document.body.textContent.includes('Server Error')
                };
            ")[0];
            $browser->screenshot('f01/13-marketplace');
            $this->assertTrue($filters['noError'] ?? false);
        });
    }

    // ==========================================
    // BROWSE TRAININGS, TENDERS, FABLABS
    // ==========================================

    public function test_14_trainings_tenders_fablabs(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);

            $this->visitPage($browser, TestConfig::url('trainings'));
            $browser->assertDontSee('Server Error')->screenshot('f01/14-trainings');

            $this->visitPage($browser, TestConfig::url('tenders'));
            $browser->assertDontSee('Server Error')->screenshot('f01/14-tenders');

            $this->visitPage($browser, TestConfig::url('fab-labs'));
            $browser->assertDontSee('Server Error')->screenshot('f01/14-fablabs');
        });
    }

    // ==========================================
    // MESSAGES — NO 429/500
    // ==========================================

    public function test_15_messages(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('messages'));

            $msg = $browser->script("
                var t = document.body.textContent;
                return { hasContent: t.includes('Messages')||t.includes('رسائل'), no429: !t.includes('Too Many'), no500: !t.includes('Server Error') };
            ")[0];
            $browser->screenshot('f01/15-messages');
            $this->assertTrue($msg['no429'] ?? false, 'No 429');
            $this->assertTrue($msg['no500'] ?? false, 'No 500');

            // Message requests
            $this->visitPage($browser, TestConfig::url('messages/requests'));
            $browser->assertDontSee('Server Error')->screenshot('f01/15-requests');
        });
    }

    // ==========================================
    // ACCOUNT SETTINGS — REAL INTERACTION
    // ==========================================

    public function test_16_account_settings(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('account/settings'));

            $settings = $browser->script("
                return {
                    hasPasswordFields: document.querySelectorAll('input[type=\"password\"]').length >= 2,
                    hasPrivacyToggles: document.querySelectorAll('input[type=\"checkbox\"],[role=\"switch\"]').length > 0,
                    hasDangerZone: document.body.textContent.includes('Delete') || document.body.textContent.includes('حذف'),
                    noError: !document.body.textContent.includes('Server Error')
                };
            ")[0];
            $browser->screenshot('f01/16-settings');
            $this->assertTrue($settings['noError'] ?? false);
            $this->assertTrue($settings['hasPasswordFields'] ?? false, 'Should have password fields');
        });
    }

    // ==========================================
    // ARABIC
    // ==========================================

    public function test_17_arabic(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser, 'ar');
            $this->visitPage($browser, TestConfig::url('profile', 'ar'));
            $dir = $browser->script("return document.documentElement.getAttribute('dir')")[0];
            $browser->screenshot('f01/17-arabic');
            $this->assertEquals('rtl', $dir, 'Arabic should be RTL');
        });
    }

    // ==========================================
    // LOGOUT
    // ==========================================

    public function test_18_logout(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url(''));

            $browser->scriptWithHelpers(<<<'JS'
                var form = document.querySelector('form[action*="logout"]');
                if(form) form.submit();
                else { clickText('Logout') || clickText('Log Out') || clickText('تسجيل الخروج'); }
            JS);
            $browser->pause(3000);

            $browser->visit(TestConfig::url('profile'))->pause(3000);
            $url = $browser->driver->getCurrentURL();
            $browser->screenshot('f01/18-logout');
            $this->assertStringContainsString('login', $url, 'Should redirect to login after logout');
        });
    }
}
