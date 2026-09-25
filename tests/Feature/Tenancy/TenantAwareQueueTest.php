<?php

use App\Jobs\Concerns\TenantAware;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * No real job exists yet (they arrive with Phase 1's payroll runs etc.) —
 * this tiny test-only job exercises the App\Jobs\Concerns\TenantAware
 * trait + App\Support\Tenancy\SetsTenantContext middleware end to end,
 * without adding throwaway code to app/Jobs. See
 * docs/build/00-build-plan.md Step 0.3.
 */
class CountVisibleUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAware;

    public static ?int $lastSeenCount = null;

    public function __construct()
    {
        $this->captureCurrentTenant();
    }

    public function middleware(): array
    {
        return $this->tenantMiddleware();
    }

    public function handle(): void
    {
        self::$lastSeenCount = User::count();
    }
}

test('a dispatched job runs with the tenant that was active when it was queued', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    app(TenantContext::class)->set($tenantA);
    User::factory()->count(2)->create();

    app(TenantContext::class)->set($tenantB);
    User::factory()->count(5)->create();

    // Dispatched while tenant A is active...
    app(TenantContext::class)->set($tenantA);
    $job = new CountVisibleUsersJob;

    // ...runs on a "worker" where tenant B is currently active for
    // whatever ran immediately before it (the realistic failure mode
    // this whole mechanism exists to prevent).
    app(TenantContext::class)->set($tenantB);

    CountVisibleUsersJob::$lastSeenCount = null;

    foreach ($job->middleware() as $middleware) {
        $middleware->handle($job, fn ($job) => $job->handle());
    }

    expect(CountVisibleUsersJob::$lastSeenCount)->toBe(2) // tenant A's count, not tenant B's 5
        ->and(app(TenantContext::class)->id())->toBe($tenantB->id); // context restored after the job
});
