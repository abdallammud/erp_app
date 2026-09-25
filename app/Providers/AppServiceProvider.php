<?php

namespace App\Providers;

use App\Models\Scopes\TenantScope;
use App\Support\Tenancy\TenantContext;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Singleton: the same "current tenant" instance for the whole
        // request/job lifecycle. See docs/02-architecture.md.
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // See config/auth.php ('providers.users.driver') and
        // docs/build/DECISIONS.md D-015: user lookups for authentication
        // (by session id on every request, or by credentials on login)
        // happen BEFORE any tenant is known — that's how tenant gets
        // known. They must bypass App\Models\Scopes\TenantScope's normal
        // fail-closed behavior, or nobody could ever log in (or stay
        // logged in past the first request).
        Auth::provider('tenant-aware-eloquent', function ($app, array $config) {
            return (new EloquentUserProvider($app['hash'], $config['model']))
                ->withQuery(fn ($query) => $query->withoutGlobalScope(TenantScope::class));
        });
    }
}
