<?php

namespace App\Providers;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use App\Mail\GmailApiTransport;
use App\Services\GmailOAuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GmailOAuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');

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
