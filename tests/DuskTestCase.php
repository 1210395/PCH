<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the application instance.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    /**
     * Helper JS code that defines findByAlpine/clickText/findByText/clickAlpine.
     * Prepended to every script() call via the scriptWithHelpers macro.
     */
    protected static string $helperJS = <<<'HELPERJS'
        if (typeof findByAlpine === 'undefined') {
            window.findByAlpine = function(action, tag) {
                var els = document.querySelectorAll(tag || '*');
                return Array.from(els).find(function(el) {
                    return (el.getAttribute('x-on:click') || '').includes(action)
                        || (el.getAttribute('@click') || '').includes(action)
                        || (el.getAttribute('x-on:click.prevent') || '').includes(action);
                }) || null;
            };
            window.findByText = function(text, tag) {
                var els = document.querySelectorAll(tag || 'button, a');
                return Array.from(els).find(function(el) { return el.textContent.trim().includes(text); }) || null;
            };
            window.clickAlpine = function(action, tag) {
                var el = findByAlpine(action, tag);
                if (el) { el.click(); return true; }
                return false;
            };
            window.clickText = function(text, tag) {
                var el = findByText(text, tag);
                if (el) { el.click(); return true; }
                return false;
            };
        }
    HELPERJS;

    /**
     * Register a Browser macro that ensures helpers are always available in script() calls.
     * This runs once and makes every $browser->script() auto-inject helpers.
     */
    protected static bool $macroRegistered = false;

    /**
     * Reset browser state before each test to ensure isolation.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!static::$macroRegistered) {
            static::$macroRegistered = true;
            $helperJS = static::$helperJS;
            Browser::macro('scriptWithHelpers', function ($js) use ($helperJS) {
                return $this->script($helperJS . "\n" . $js);
            });
        }

        // Clear browser state from previous test to prevent cross-contamination.
        // If the browser is dead (InvalidSessionId), kill it so a fresh one is created.
        if (!empty(static::$browsers)) {
            foreach (static::$browsers as $browser) {
                try {
                    $browser->driver->manage()->deleteAllCookies();
                    $browser->script('try { localStorage.clear(); sessionStorage.clear(); } catch(e) {}');
                } catch (\Exception $e) {
                    // Browser session is dead — close all so browse() creates a fresh one
                    static::closeAll();
                    break;
                }
            }
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-extensions',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge(['--headless=new']);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            ),
            60000, // connection timeout ms
            120000  // request timeout ms
        );
    }

    /**
     * Base URL for all tests - points to production
     */
    protected function baseUrl(): string
    {
        return 'https://technopark.ps/PalestineCreativeHub';
    }

    /**
     * Assert the page is RTL Arabic.
     */
    protected function assertRtlArabic(Browser $browser): void
    {
        $dir = $browser->script("return document.documentElement.getAttribute('dir');")[0];
        $lang = $browser->script("return document.documentElement.getAttribute('lang');")[0];
        $this->assertEquals('rtl', $dir, "Expected dir=rtl, got dir={$dir}");
        $this->assertEquals('ar', $lang, "Expected lang=ar, got lang={$lang}");
    }

    /**
     * Dismiss the discover wizard modal and set localStorage to prevent it reappearing.
     */
    protected function dismissWizard(Browser $browser): Browser
    {
        $browser->script("localStorage.setItem('discoverWizardDismissed', Date.now().toString());");
        $browser->script(<<<'JS'
            const wizard = document.querySelector('[x-data*="discoverWizard"]');
            if (wizard && wizard.__x) wizard.__x.$data.show = false;
            document.querySelectorAll('[x-show]').forEach(function(el) {
                if (el.textContent.includes('browsing') || el.textContent.includes('What are you looking for')) {
                    el.style.display = 'none';
                }
            });
        JS);
        $browser->pause(300);
        return $browser;
    }

    /**
     * Inject JS helper functions for finding Alpine.js elements.
     * querySelector doesn't support [x-on:click] or [@click] selectors.
     * Always re-injects to handle page navigations that clear window state.
     */
    protected function injectHelpers(Browser $browser): void
    {
        $browser->script(static::$helperJS);
    }

    /**
     * Visit a page and dismiss wizard automatically.
     */
    protected function visitPage(Browser $browser, string $url): Browser
    {
        $browser->visit($url)->pause(2000);
        $this->dismissWizard($browser);
        $this->injectHelpers($browser);
        $browser->pause(500);
        return $browser;
    }

    /**
     * Clear all browser state (cookies, localStorage, sessionStorage).
     */
    protected function clearBrowserState(Browser $browser): Browser
    {
        $browser->driver->manage()->deleteAllCookies();
        $browser->script('try { localStorage.clear(); sessionStorage.clear(); } catch(e) {}');
        return $browser;
    }

    /**
     * Execute a script with helper functions auto-injected.
     * Use for single-line JS that depends on findByAlpine/clickText/etc.
     */
    protected function runScript(Browser $browser, string $js)
    {
        return $browser->script(static::$helperJS . "\n" . $js);
    }

    /**
     * Login as a specific user. Works with the auth layout login form.
     */
    protected function loginAs(Browser $browser, string $email, string $password, string $locale = 'en'): Browser
    {
        $browser->visit($this->baseUrl() . "/{$locale}/login")
            ->pause(2000);

        $safeEmail = addslashes($email);
        $safePass = addslashes($password);

        // Use ID selectors which are more reliable
        $browser->script("
            const emailField = document.getElementById('email') || document.querySelector('input[name=\"email\"]');
            const passField = document.getElementById('password') || document.querySelector('input[name=\"password\"]');
            if (emailField) { emailField.value = '{$safeEmail}'; emailField.dispatchEvent(new Event('input', {bubbles:true})); }
            if (passField) { passField.value = '{$safePass}'; passField.dispatchEvent(new Event('input', {bubbles:true})); }
        ");
        $browser->pause(500);

        // Click submit button
        $browser->script("
            const btn = document.querySelector('button[type=\"submit\"]');
            if (btn) btn.click();
        ");
        $browser->pause(4000);

        // Dismiss wizard on landing page
        $this->dismissWizard($browser);

        return $browser;
    }

    /**
     * Login as admin
     */
    protected function loginAsAdmin(Browser $browser, string $locale = 'en'): Browser
    {
        return $this->loginAs($browser, 'admin@palestinecreativehub.com', 'Admin@PCH2024!', $locale);
    }

    /**
     * Login as designer
     */
    protected function loginAsDesigner(Browser $browser, string $locale = 'en'): Browser
    {
        return $this->loginAs($browser, \Tests\Browser\TestConfig::DESIGNER_EMAIL, \Tests\Browser\TestConfig::DESIGNER_PASSWORD, $locale);
    }

    /**
     * Login as academic
     */
    protected function loginAsAcademic(Browser $browser, string $locale = 'en'): Browser
    {
        return $this->loginAs($browser, \Tests\Browser\TestConfig::ACADEMIC_EMAIL, \Tests\Browser\TestConfig::ACADEMIC_PASSWORD, $locale);
    }
}
