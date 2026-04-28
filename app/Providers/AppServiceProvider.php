<?php

namespace App\Providers;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Mail\GmailApiTransport;
use App\Services\GmailOAuthService;

/**
 * The primary application service provider for Palestine Creative Hub.
 *
 * Registers the GmailOAuthService singleton, extends the Mailer with the custom
 * Gmail API transport, and registers Blade directives for versioned asset URLs.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Binds GmailOAuthService as a singleton so the same OAuth token instance
     * is reused across the request lifecycle.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(GmailOAuthService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * Forces HTTPS scheme for all generated URLs, registers the 'gmail' mail transport
     * driver, and defines the @versionedAsset / @autoVersionedAsset Blade directives.
     *
     * @return void
     */
    public function boot(): void
    {
        URL::forceScheme('https');

        // Login rate limit keyed by (email + IP).
        // Per-IP-only throttling lets a single email be probed from many IPs;
        // keying by both blocks targeted credential stuffing while still
        // letting a shared NAT submit logins for different accounts.
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        // Verification-resend limit keyed by (email + IP).
        // Per-IP-only at 10/5min lets an attacker spam verification emails
        // to many addresses by varying the form field while sharing the IP.
        // Keying on email caps anyone from blasting any single inbox.
        // (bugs.md M-3)
        RateLimiter::for('verification-send', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            return Limit::perMinutes(5, 3)->by($email . '|' . $request->ip());
        });

        // Register custom Gmail API mail transport
        Mail::extend('gmail', function () {
            return new GmailApiTransport(app(GmailOAuthService::class));
        });

        // Register versioned asset Blade directive for cache busting
        // Usage: @versionedAsset('css/app.css') or @autoVersionedAsset('css/app.css')
        Blade::directive('versionedAsset', function ($expression) {
            return "<?php echo \\App\\Helpers\\AssetHelper::versioned({$expression}); ?>";
        });

        Blade::directive('autoVersionedAsset', function ($expression) {
            return "<?php echo \\App\\Helpers\\AssetHelper::autoVersioned({$expression}); ?>";
        });
    }
}
