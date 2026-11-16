<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Register every command in app/Console/Commands.
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        // Render terminates TLS in front of the container.
        $middleware->trustProxies(at: '*');

        // Apply the resolved store's currency and timezone per request.
        $middleware->web(append: [
            \App\Http\Middleware\SetStoreContext::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'plan.feature' => \App\Http\Middleware\EnsurePlanFeature::class,
            'store.active' => \App\Http\Middleware\EnsureStoreActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
