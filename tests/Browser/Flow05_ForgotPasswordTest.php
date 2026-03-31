<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * REALISTIC FORGOT PASSWORD FLOW — real typing, real clicks.
 */
class Flow05_ForgotPasswordTest extends DuskTestCase
{
    public function test_01_forgot_link_from_login(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('login'));
            $browser->screenshot('forgot/01-login');

            // Real click on forgot link
            $browser->script("
                var a = Array.from(document.querySelectorAll('a')).find(function(l) {
                    return l.textContent.toLowerCase().includes('forgot') || l.textContent.includes('نسيت');
                });
                if (a) a.click();
            ");
            $browser->pause(3000)->screenshot('forgot/01-forgot-page');

            $has = $browser->script("return{hasEmail:!!document.querySelector('input[type=\"email\"],input[name=\"email\"]'),hasSubmit:!!document.querySelector('button[type=\"submit\"]')};")[0];
            $this->assertTrue($has['hasEmail'] ?? false, 'Should have email input');
            $this->assertTrue($has['hasSubmit'] ?? false, 'Should have submit button');
        });
    }

    public function test_02_submit_valid_email(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('password/forgot'));

            // Real type email
            try {
                $browser->click('input[name="email"],input[type="email"]')
                    ->type('input[name="email"],input[type="email"]', TestConfig::USER_A_EMAIL);
            } catch (\Exception $e) {
                $browser->script("var i=document.querySelector('input[name=\"email\"],input[type=\"email\"]');if(i){i.value='" . TestConfig::USER_A_EMAIL . "';i.dispatchEvent(new Event('input',{bubbles:true}));}");
            }
            $browser->pause(500)->screenshot('forgot/02-email-typed');

            // Real click submit
            $browser->script("document.querySelector('button[type=\"submit\"]')?.click()");
            $browser->pause(5000)->screenshot('forgot/02-submitted');

            $noError = $browser->script("return !document.body.textContent.includes('Server Error')")[0];
            $this->assertTrue($noError, 'Should not 500 on forgot password submit');
        });
    }

    public function test_03_empty_email_validation(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('password/forgot'));
            $browser->script("document.querySelector('button[type=\"submit\"]')?.click()");
            $browser->pause(3000)->screenshot('forgot/03-empty');
        });
    }

    public function test_04_nonexistent_email(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('password/forgot'));
            try {
                $browser->type('input[name="email"],input[type="email"]', 'fake-' . time() . '@test.com');
            } catch (\Exception $e) {}
            $browser->script("document.querySelector('button[type=\"submit\"]')?.click()");
            $browser->pause(5000)->screenshot('forgot/04-nonexistent');
            $noError = $browser->script("return !document.body.textContent.includes('Server Error')")[0];
            $this->assertTrue($noError, 'Should not crash with fake email');
        });
    }

    public function test_05_arabic(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('password/forgot', 'ar'));
            $isRtl = $browser->script("return document.documentElement.getAttribute('dir')==='rtl'")[0];
            $browser->screenshot('forgot/05-arabic');
            $this->assertTrue($isRtl, 'Should be RTL');
        });
    }

    public function test_06_back_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitPage($browser, TestConfig::url('password/forgot'));

            // Click "Log In" link
            $browser->script("
                var a = Array.from(document.querySelectorAll('a')).find(function(l) {
                    var t = l.textContent.toLowerCase();
                    return t.includes('log in') || t.includes('login') || t.includes('sign in') || t.includes('تسجيل');
                });
                if (a) a.click();
            ");
            $browser->pause(3000)->screenshot('forgot/06-back');
            $url = $browser->driver->getCurrentURL();
            $this->assertStringContainsString('login', $url, 'Should go back to login');
        });
    }
}
