<?php

use App\Http\Middleware\IdentifyTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Auto-discovers handle*() listeners in app/Listeners by their
    // type-hinted event parameter — Laravel's skeleton doesn't call this
    // by default. Needed for App\Listeners\Approvals\* (Step 0.7) rather
    // than hand-registering every event/listener pair. See
    // docs/build/00-build-plan.md Step 0.7 and docs/build/DECISIONS.md.
    ->withEvents()
    ->withMiddleware(function (Middleware $middleware): void {
        // Runs on every web request, after auth resolves $request->user().
        // No-ops until Step 0.4 (Auth & RBAC) adds login — see
        // App\Http\Middleware\IdentifyTenant and docs/02-architecture.md.
        $middleware->appendToGroup('web', IdentifyTenant::class);

        $middleware->alias([
            'identify.tenant' => IdentifyTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
