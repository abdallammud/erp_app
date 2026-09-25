<?php

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests get the full Laravel TestCase (HTTP, DB, auth helpers) and
| a freshly migrated in-memory SQLite database per test (RefreshDatabase) —
| needed from Phase 0 Step 0.3 onward since tenancy tests depend on real
| tables. Unit tests stay on Pest's default plain PHPUnit TestCase — no
| framework bootstrapping needed for pure logic tests.
|
| Every Feature test also gets a default tenant set as the current
| TenantContext before it runs — see App\Models\Scopes\TenantScope: with
| no tenant context, tenant-scoped queries (like `User::factory()->create()`
| or `$user->fresh()`) fail closed and return nothing, which would break
| almost every ordinary feature test for no good reason. Most tests
| shouldn't have to think about tenancy at all — that mirrors real usage,
| where IdentifyTenant middleware establishes a tenant for the whole
| request. Tests specifically ABOUT tenant isolation
| (tests/Feature/Tenancy/*) override this default explicitly per-test.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        app(TenantContext::class)->set(Tenant::factory()->create());
    })
    ->in('Feature');
