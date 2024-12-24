<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Pennant\Feature;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        AppServiceProvider::class,
        \Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->web([
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\EnableFeatureFlags::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->throttleApi();
        $middleware->statefulApi();

        $middleware->replace(\Illuminate\Http\Middleware\TrustProxies::class, \App\Http\Middleware\TrustProxies::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
