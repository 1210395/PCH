<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * SETUP: Register all test accounts before running flow tests.
 * Run this ONCE, then verify emails manually.
 *
 * 1. Register designer (jadallah.baragitha@gmail.com) — full wizard with images + cert
 * 2. Create academic account via admin CMS (jad.bar1122@gmail.com)
 * 3. Register guest (moahamadbarmohamad1122@gmail.com)
 */
class Flow00_SetupAccountsTest extends DuskTestCase
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

    private function sendFile(Browser $browser, string $id, string $path): void
    {
        $browser->script("var i=document.getElementById('{$id}');if(i){i.removeAttribute('hidden');i.style.cssText='display:block!important;position:absolute!important;width:1px!important;height:1px!important';}");
        $browser->pause(300);
        try { $browser->driver->findElement(\Facebook\WebDriver\WebDriverBy::id($id))->sendKeys($path); } catch (\Exception $e) { fwrite(STDERR, "File upload {$id}: " . $e->getMessage() . "\n"); }
        $browser->pause(4000);
    }

    // ==========================================
    // 1. REGISTER DESIGNER
    // ==========================================

    public function test_01_register_designer(): void
    {
        $profileImg = $this->img();
        $coverImg = $this->img();
        $certPdf = $this->pdf();

        $this->browse(function (Browser $browser) use ($profileImg, $coverImg, $certPdf) {
            // Clear saved wizard data and go to register
            $browser->visit($this->baseUrl() . '/en/register')->pause(3000);
            $browser->script("localStorage.removeItem('signupWizardData');");
            $browser->refresh()->pause(4000);
            $this->dismissWizard($browser);
            $this->injectHelpers($browser);

            // === STEP 1: Account ===
            $browser->type('[x-model="formData.firstName"]', 'Test')
                ->type('[x-model="formData.lastName"]', 'Designer')
                ->type('[x-model="formData.email"]', TestConfig::USER_A_EMAIL)
                ->type('[x-model="formData.password"]', TestConfig::USER_A_PASSWORD)
                ->type('[x-model="formData.confirmPassword"]', TestConfig::USER_A_PASSWORD);
            $browser->pause(1000)->screenshot('setup/d-step1');

            // Next
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.textContent.includes('Next')||b.textContent.includes('التالي'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(4000)->screenshot('setup/d-step2');

            // === STEP 2: Profile Type — searchable combobox ===
            $browser->script("document.querySelectorAll('input[type=\"text\"]').forEach(function(i){var m=i.getAttribute('x-model')||'';if(m.includes('searchQuery'))i.setAttribute('id','dusk-sector');});");
            $browser->pause(300);
            $browser->click('#dusk-sector')->pause(300);
            $browser->type('#dusk-sector', 'designer');
            $browser->pause(1500);
            $browser->keys('#dusk-sector', ['{ARROW_DOWN}'], ['{ENTER}']);
            $browser->pause(500);
            try { $browser->driver->findElement(\Facebook\WebDriver\WebDriverBy::xpath("//li[normalize-space(text())='Designer']"))->click(); } catch (\Exception $e) {}
            $browser->pause(2000)->screenshot('setup/d-step2-sector');

            // Sub-sector
            $browser->script("var c=0;document.querySelectorAll('input[type=\"text\"]').forEach(function(i){var m=i.getAttribute('x-model')||'';if(m.includes('searchQuery')){c++;if(c===2)i.setAttribute('id','dusk-subsector');}});");
            $browser->pause(300);
            try {
                $browser->click('#dusk-subsector')->pause(500);
                $browser->keys('#dusk-subsector', ['{ARROW_DOWN}'], ['{ENTER}']);
            } catch (\Exception $e) {}
            $browser->pause(1000)->screenshot('setup/d-step2-filled');

            // Next
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.textContent.includes('Next')||b.textContent.includes('التالي'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(4000)->screenshot('setup/d-step3');

            // === STEP 3: Profile Details ===
            // Upload images
            $browser->script("document.querySelectorAll('input[type=\"file\"]').forEach(function(i,idx){i.setAttribute('id','dusk-file-'+idx);i.removeAttribute('hidden');i.style.cssText='display:block!important;position:absolute!important;width:1px!important;height:1px!important';});");
            $this->sendFile($browser, 'dusk-file-0', $profileImg);
            $this->sendFile($browser, 'dusk-file-1', $coverImg);
            $browser->screenshot('setup/d-step3-imgs');

            // Upload cert PDF
            $browser->script("document.querySelectorAll('input[type=\"file\"]').forEach(function(i){var a=i.getAttribute('accept')||'';if(a.includes('pdf')||!a.includes('image'))i.setAttribute('id','dusk-cert');});");
            $this->sendFile($browser, 'dusk-cert', $certPdf);
            $browser->screenshot('setup/d-step3-cert');

            // Fill fields
            $browser->type('[x-model="formData.companyName"]', 'Dusk Design Studio');
            $browser->type('[x-model="formData.position"]', 'Lead Designer');
            $browser->clear('[x-model="formData.phoneNumber"]')->type('[x-model="formData.phoneNumber"]', '599123456');
            try { $browser->type('[x-model="formData.address"]', 'Ramallah, Palestine'); } catch (\Exception $e) {}
            try { $browser->type('[x-model="formData.bio"]', 'Professional Palestinian designer specializing in cultural heritage integration with modern design. Created by automated Dusk tests.'); } catch (\Exception $e) {}

            // Select all combobox dropdowns (city, years)
            $browser->script("document.querySelectorAll('[x-data]').forEach(function(el){if(!el._x_dataStack||!el.offsetParent)return;el._x_dataStack.forEach(function(d){if(d.selectOption&&d.filteredOptions&&d.filteredOptions.length>0&&!d.selectedValue)d.selectOption(d.filteredOptions[0]);});});");
            $browser->pause(1000)->screenshot('setup/d-step3-filled');

            // Check state before next
            $state = $browser->script("var r=null;document.querySelectorAll('[x-data]').forEach(function(el){if(el._x_dataStack)el._x_dataStack.forEach(function(d){if(d.formData&&d.currentStep!==undefined)r={step:d.currentStep,city:d.formData.city,years:d.formData.yearsOfExperience,hasProfile:!!(d.uploadedPaths&&d.uploadedPaths.profile),hasCover:!!(d.uploadedPaths&&d.uploadedPaths.cover)};});});return r;")[0];
            fwrite(STDERR, "Step 3 state: " . json_encode($state) . "\n");

            // Next → Steps 4-6 (skip)
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.textContent.includes('Next')||b.textContent.includes('التالي'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(4000);

            // Check if advanced
            $step = $browser->script("var r=null;document.querySelectorAll('[x-data]').forEach(function(el){if(el._x_dataStack)el._x_dataStack.forEach(function(d){if(d.currentStep!==undefined)r={step:d.currentStep,errors:d.errors||{}};});});return r;")[0];
            fwrite(STDERR, "After step 3 next: " . json_encode($step) . "\n");

            // Skip steps 4-6
            for ($i = 0; $i < 3; $i++) {
                $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.textContent.includes('Next')||b.textContent.includes('التالي'))&&b.offsetParent;});if(b)b.click();");
                $browser->pause(3000);
            }
            $browser->screenshot('setup/d-step7');

            // === STEP 7: Review ===
            $browser->script("document.querySelectorAll('input[type=\"checkbox\"]').forEach(function(c){if(!c.checked)c.click();});");
            $browser->pause(500);

            // Click Publish/Create Account
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){var t=b.textContent.toLowerCase();return(t.includes('publish')||t.includes('create account')||t.includes('submit'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(3000);

            // Confirmation modal checkboxes
            $browser->script("document.querySelectorAll('input[type=\"checkbox\"]').forEach(function(c){if(!c.checked)c.click();});");
            $browser->pause(500);

            // Confirm
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){var t=b.textContent.toLowerCase();return(t.includes('confirm')||t.includes('proceed')||t.includes('publish')||t.includes('create'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(8000)->screenshot('setup/d-result');

            $url = $browser->driver->getCurrentURL();
            fwrite(STDERR, "\n=== DESIGNER REGISTRATION: {$url} ===\n");
        });

        @unlink($profileImg);
        @unlink($coverImg);
        @unlink($certPdf);
    }

    // ==========================================
    // 2. CREATE ACADEMIC ACCOUNT VIA ADMIN CMS
    // ==========================================

    public function test_02_create_academic_via_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::adminUrl('academic-accounts/create'));
            $browser->assertDontSee('Server Error')->screenshot('setup/academic-create-form');

            // Fill the academic account form
            $browser->script("
                function fill(name, value) {
                    var el = document.querySelector('[name=\"' + name + '\"]');
                    if (!el) return;
                    if (el.tagName === 'SELECT') {
                        for (var i = 0; i < el.options.length; i++) {
                            if (el.options[i].value && el.options[i].value !== '') { el.selectedIndex = i; break; }
                        }
                        el.dispatchEvent(new Event('change', {bubbles:true}));
                    } else {
                        el.value = value;
                        el.dispatchEvent(new Event('input', {bubbles:true}));
                    }
                }
                fill('name', 'Dusk Test University');
                fill('email', '" . TestConfig::ACADEMIC_EMAIL . "');
                fill('password', '" . addslashes(TestConfig::ACADEMIC_PASSWORD) . "');
                fill('password_confirmation', '" . addslashes(TestConfig::ACADEMIC_PASSWORD) . "');
                fill('type', '');
                fill('city', 'Ramallah');
                fill('description', 'Test academic institution created by Dusk automated tests.');
                fill('phone', '022961234');
                fill('website', 'https://test-university.example.com');

                // Select first option for all dropdowns
                document.querySelectorAll('select').forEach(function(sel) {
                    if (sel.options.length > 1 && sel.selectedIndex === 0) {
                        sel.selectedIndex = 1;
                        sel.dispatchEvent(new Event('change', {bubbles:true}));
                    }
                });
            ");
            $browser->pause(1000)->screenshot('setup/academic-filled');

            // Submit
            $browser->script("document.querySelector('button[type=\"submit\"]')?.click()");
            $browser->pause(5000)->screenshot('setup/academic-submitted');

            $result = $browser->script("
                var url = window.location.href;
                var text = document.body.textContent;
                return {
                    url: url,
                    hasSuccess: text.includes('success') || text.includes('created') || text.includes('تم'),
                    hasError: text.includes('Server Error') || text.includes('error'),
                    redirected: !url.includes('/create')
                };
            ")[0];

            fwrite(STDERR, "\n=== ACADEMIC CREATION: " . json_encode($result) . " ===\n");
            $browser->screenshot('setup/academic-result');
        });
    }

    // ==========================================
    // 3. REGISTER GUEST
    // ==========================================

    public function test_03_register_guest(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit($this->baseUrl() . '/en/register')->pause(3000);
            $browser->script("localStorage.removeItem('signupWizardData');");
            $browser->refresh()->pause(4000);
            $this->dismissWizard($browser);
            $this->injectHelpers($browser);

            // === STEP 1: Account ===
            $browser->script("
                function f(model,val){var el=document.querySelector('[x-model=\"'+model+'\"]');if(el){el.value=val;el.dispatchEvent(new Event('input',{bubbles:true}));}}
                f('formData.firstName','Test');f('formData.lastName','Guest');
                f('formData.email','" . TestConfig::USER_B_EMAIL . "');
                f('formData.password','" . addslashes(TestConfig::USER_B_PASSWORD) . "');
                f('formData.confirmPassword','" . addslashes(TestConfig::USER_B_PASSWORD) . "');
            ");
            $browser->pause(1000)->screenshot('setup/g-step1');

            // Next
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.textContent.includes('Next')||b.textContent.includes('التالي'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(4000)->screenshot('setup/g-step2');

            // === STEP 2: Select Guest ===
            $browser->script("document.querySelectorAll('input[type=\"text\"]').forEach(function(i){var m=i.getAttribute('x-model')||'';if(m.includes('searchQuery'))i.setAttribute('id','dusk-guest-sector');});");
            $browser->pause(300);
            $browser->click('#dusk-guest-sector')->pause(300);
            $browser->type('#dusk-guest-sector', 'guest');
            $browser->pause(1500);
            $browser->keys('#dusk-guest-sector', ['{ARROW_DOWN}'], ['{ENTER}']);
            $browser->pause(500);
            try { $browser->driver->findElement(\Facebook\WebDriver\WebDriverBy::xpath("//li[contains(text(),'Guest')]"))->click(); } catch (\Exception $e) {}
            $browser->pause(2000)->screenshot('setup/g-step2-selected');

            // Guest skips to Review
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.textContent.includes('Next')||b.textContent.includes('التالي'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(4000)->screenshot('setup/g-review');

            // === STEP 7: Review ===
            $browser->script("document.querySelectorAll('input[type=\"checkbox\"]').forEach(function(c){if(!c.checked)c.click();});");
            $browser->pause(500);

            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){var t=b.textContent.toLowerCase();return(t.includes('create account')||t.includes('publish')||t.includes('submit'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(3000);

            // Confirmation modal
            $browser->script("document.querySelectorAll('input[type=\"checkbox\"]').forEach(function(c){if(!c.checked)c.click();});");
            $browser->pause(500);
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){var t=b.textContent.toLowerCase();return(t.includes('confirm')||t.includes('proceed')||t.includes('create'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(8000)->screenshot('setup/g-result');

            $url = $browser->driver->getCurrentURL();
            fwrite(STDERR, "\n=== GUEST REGISTRATION: {$url} ===\n");
        });
    }
}
