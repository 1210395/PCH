<?php

namespace App\Providers;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');

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
