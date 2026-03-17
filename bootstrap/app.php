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
        // Trust Cloudflare proxies for correct client IP and HTTPS detection
        $middleware->trustProxies(
            at: '*',
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
