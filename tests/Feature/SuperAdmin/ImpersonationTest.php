<?php

use App\Livewire\SuperAdmin\Dashboard;
use App\Models\AuditLogEntry;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Impersonation\ImpersonationManager;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

/**
 * Build Plan Step 0.11's logged impersonation flow — see
 * docs/02-architecture.md ("can impersonate (with logging) for support
 * — but has no default access to tenant business data").
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->superAdmin = User::factory()->create(['tenant_id' => null]);
    $this->superAdmin->assignRole(Role::SuperAdmin->value);

    $this->tenant = Tenant::factory()->create();
    $this->target = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->target->assignRole(Role::Employee->value);

    $this->manager = app(ImpersonationManager::class);
});

test('a Super Admin can start impersonating a tenant user via the real dashboard, becoming that user', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(Dashboard::class)
        ->call('impersonate', $this->target->id);

    $this->assertAuthenticatedAs($this->target);
});

test('impersonating resolves the real tenant context for the impersonated user via the real middleware pipeline', function () {
    // Deliberately a real HTTP request (not Livewire::test, which
    // bypasses middleware entirely) and deliberately clears
    // TenantContext first — same discipline as
    // tests/Feature/Tenancy/IdentifyTenantMiddlewareOrderTest.php,
    // proving this against the real pipeline, not ambient test setup.
    $this->actingAs($this->superAdmin);
    $this->manager->start($this->superAdmin, $this->target);
    app(TenantContext::class)->clear();

    $this->get('/dashboard')->assertOk();

    $this->assertAuthenticatedAs($this->target);
});

test('starting and stopping impersonation both write a logged audit entry against the target\'s own tenant', function () {
    $this->manager->start($this->superAdmin, $this->target);

    $started = AuditLogEntry::withoutGlobalScopes()->where('event', 'impersonation_started')->sole();

    expect($started->tenant_id)->toBe($this->tenant->id)
        ->and($started->causer_id)->toBe($this->superAdmin->id)
        ->and($started->subject_id)->toBe($this->target->id)
        ->and($started->description)->toContain($this->superAdmin->name)
        ->and($started->description)->toContain($this->target->name);

    $admin = $this->manager->stop();

    expect($admin?->id)->toBe($this->superAdmin->id);

    $ended = AuditLogEntry::withoutGlobalScopes()->where('event', 'impersonation_ended')->sole();

    expect($ended->tenant_id)->toBe($this->tenant->id)
        ->and($ended->causer_id)->toBe($this->superAdmin->id)
        ->and($ended->subject_id)->toBe($this->target->id);

    $this->assertAuthenticatedAs($this->superAdmin);
});

test('impersonation audit entries are visible on the target tenant\'s own Audit Log screen, not hidden from it', function () {
    $this->manager->start($this->superAdmin, $this->target);
    $this->manager->stop();

    app(TenantContext::class)->set($this->tenant);

    $hrAdmin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $this->actingAs($hrAdmin)
        ->get('/audit-log')
        ->assertOk()
        ->assertSee('impersonating');
});

test('stopping when nothing is being impersonated does nothing and returns null', function () {
    expect($this->manager->stop())->toBeNull();
});

test('a non-Super-Admin cannot start an impersonation', function () {
    $notAnAdmin = User::factory()->create();
    $notAnAdmin->assignRole(Role::HrAdmin->value);

    expect(fn () => $this->manager->start($notAnAdmin, $this->target))->toThrow(RuntimeException::class);
});

test('a Super Admin account can never be impersonated', function () {
    $anotherSuperAdmin = User::factory()->create(['tenant_id' => null]);
    $anotherSuperAdmin->assignRole(Role::SuperAdmin->value);

    expect(fn () => $this->manager->start($this->superAdmin, $anotherSuperAdmin))->toThrow(RuntimeException::class);
});

test('a tampered impersonator session id that isn\'t really a Super Admin is rejected on stop, not trusted', function () {
    $this->manager->start($this->superAdmin, $this->target);

    // Simulate a tampered/stale session: swap the stored impersonator id
    // for a user who is NOT (or no longer) a Super Admin.
    $notAnAdmin = User::factory()->create();
    session(['impersonator_id' => $notAnAdmin->id]);

    expect($this->manager->stop())->toBeNull();

    // Session key is still cleared even on rejection — no dangling state.
    expect($this->manager->isImpersonating())->toBeFalse();
});
