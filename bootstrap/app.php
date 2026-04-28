<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Load academic routes
            Route::middleware('web')
                ->group(base_path('routes/academic.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust ONLY Cloudflare proxies for forwarded headers. Trusting `*`
        // let any client spoof X-Forwarded-For to bypass per-IP rate limits
        // and falsify $request->ip() in logs. List from
        // https://www.cloudflare.com/ips-v4/ and …/ips-v6/
        // (snapshot 2026-04). Refresh as Cloudflare publishes new ranges.
        // (bugs.md H-28)
        $middleware->trustProxies(
            at: [
                '173.245.48.0/20',
                '103.21.244.0/22',
                '103.22.200.0/22',
                '103.31.4.0/22',
                '141.101.64.0/18',
                '108.162.192.0/18',
                '190.93.240.0/20',
                '188.114.96.0/20',
                '197.234.240.0/22',
                '198.41.128.0/17',
                '162.158.0.0/15',
                '104.16.0.0/13',
                '104.24.0.0/14',
                '172.64.0.0/13',
                '131.0.72.0/22',
                '2400:cb00::/32',
                '2606:4700::/32',
                '2803:f800::/32',
                '2405:b500::/32',
                '2405:8100::/32',
                '2a06:98c0::/29',
                '2c0f:f248::/32',
            ],
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );

        // Register middleware aliases
        $middleware->alias([
            'locale' => \App\Http\Middleware\SetLocale::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'academic' => \App\Http\Middleware\AcademicMiddleware::class,
            'verified'    => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'active'      => \App\Http\Middleware\EnsureDesignerActive::class,
            'signed'      => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'track.page'  => \App\Http\Middleware\TrackPageVisit::class,
        ]);

        // Apply locale and security headers middleware to web routes
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
