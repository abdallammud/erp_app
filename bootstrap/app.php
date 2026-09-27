<?php

use App\Http\Middleware\IdentifyTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

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

        // Without this, appendToGroup's plain array position isn't
        // enough: Laravel's default 'web' group already lists
        // SubstituteBindings (which resolves route-model-bound
        // parameters, e.g. `Document $document`) BEFORE anything
        // appended here, and SubstituteBindings has no defined priority
        // of its own to reorder around. Any route implicitly
        // route-model-binding a BelongsToTenant model would have its
        // binding query run with NO tenant context yet — TenantScope
        // fails closed, so it 404s even for its rightful owner. Found
        // live (Step 0.9's /documents/{document}/download was the first
        // route ever to bind a tenant-scoped model directly in its URI)
        // — Pest's HTTP tests never caught it because they set
        // TenantContext directly in setup, bypassing the real middleware
        // pipeline entirely. See docs/build/DECISIONS.md D-031.
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: IdentifyTenant::class,
        );

        $middleware->alias([
            'identify.tenant' => IdentifyTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
