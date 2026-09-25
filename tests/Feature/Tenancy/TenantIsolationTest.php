<?php

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

/**
 * The single most important test in the whole system — see
 * docs/build/00-build-plan.md Step 0.3's Definition of Done and
 * docs/02-architecture.md's multi-tenancy model.
 *
 * Uses the real, permanent User model (not a throwaway demo entity) —
 * every login belongs to exactly one tenant, so User is the correct,
 * real-world proof that BelongsToTenant + TenantScope actually isolate
 * data between organizations.
 */
test('a tenant cannot see another tenant\'s users', function () {
    $context = app(TenantContext::class);

    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $context->set($tenantA);
    $userA = User::factory()->create();

    $context->set($tenantB);
    $userB = User::factory()->create();

    // Scoped to tenant A: only tenant A's user is visible.
    $context->set($tenantA);
    expect(User::pluck('id'))->toEqual(collect([$userA->id]));
    expect(User::find($userB->id))->toBeNull();

    // Scoped to tenant B: only tenant B's user is visible.
    $context->set($tenantB);
    expect(User::pluck('id'))->toEqual(collect([$userB->id]));
    expect(User::find($userA->id))->toBeNull();
});

test('creating a record auto-fills tenant_id from the current tenant context', function () {
    $tenant = Tenant::factory()->create();

    app(TenantContext::class)->set($tenant);

    $user = User::factory()->create();

    expect($user->tenant_id)->toBe($tenant->id);
});

test('explicitly creating a record with tenant_id null is respected, not overwritten by context', function () {
    // Regression test for a real bug (see docs/build/DECISIONS.md D-020):
    // BelongsToTenant's auto-fill originally used empty($model->tenant_id),
    // which can't distinguish "never mentioned tenant_id" from "explicitly
    // set it to null" (a Super Admin account) — so an ambient tenant
    // context silently overwrote an explicit null with itself.
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);

    $superAdmin = User::factory()->create(['tenant_id' => null]);

    expect($superAdmin->tenant_id)->toBeNull();
});

test('an explicitly set tenant_id is not overridden by the current context', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    // Context says tenant A, but we explicitly create the record for
    // tenant B — e.g. Super Admin tooling provisioning a new tenant's
    // first user without "being" that tenant.
    app(TenantContext::class)->set($tenantA);

    $user = User::factory()->create(['tenant_id' => $tenantB->id]);

    expect($user->tenant_id)->toBe($tenantB->id);
});

test('tenant-scoped queries return nothing when no tenant context is set (fails closed)', function () {
    app(TenantContext::class)->clear();

    // Creating a Tenant doesn't require a tenant context — Tenant itself
    // isn't tenant-scoped. Its factory creates a user-less tenant, so
    // there's nothing in `users` regardless; the point of this test is
    // that the query returns zero rather than throwing or returning
    // "everyone" when the context is genuinely unset.
    Tenant::factory()->create();

    expect(User::count())->toBe(0);
});

test('a legitimate cross-tenant query can explicitly opt out of the scope', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    app(TenantContext::class)->set($tenantA);
    $userA = User::factory()->create();

    app(TenantContext::class)->set($tenantB);
    $userB = User::factory()->create();

    // No tenant context at all right now — the scoped query sees nothing...
    app(TenantContext::class)->clear();
    expect(User::count())->toBe(0);

    // ...but an explicit, deliberate bypass (Super Admin tooling) sees both.
    expect(
        User::withoutGlobalScope(TenantScope::class)->pluck('id')->sort()->values()
    )->toEqual(collect([$userA->id, $userB->id])->sort()->values());
});
