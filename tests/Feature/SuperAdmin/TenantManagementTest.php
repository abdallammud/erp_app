<?php

use App\Livewire\SuperAdmin\Dashboard;
use App\Models\AuditLogEntry;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;
use Livewire\Volt\Volt;

/**
 * Build Plan Step 0.11's Definition of Done: a Super Admin can create a
 * brand-new tenant end to end and it's immediately usable (empty but
 * functional) — proven here by actually logging in as the new tenant's
 * freshly-created admin through the real login form, not just asserting
 * database rows exist.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->superAdmin = User::factory()->create(['tenant_id' => null]);
    $this->superAdmin->assignRole(Role::SuperAdmin->value);
});

test('a non-Super-Admin cannot reach the Super Admin portal', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $this->actingAs($hrAdmin)->get('/super-admin')->assertForbidden();
});

test('a Super Admin can reach the Super Admin portal', function () {
    $this->actingAs($this->superAdmin)->get('/super-admin')->assertOk();
});

test('openCreate resets the form and opens the modal; createTenant closes it', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(Dashboard::class)
        ->set('name', 'stale value')
        ->call('openCreate')
        ->assertSet('name', '')
        ->assertDispatched('open-modal', name: 'tenant-form')
        ->set('name', 'Modal Test NGO')
        ->set('adminName', 'Modal Admin')
        ->set('adminEmail', 'modal-admin@modal-test-ngo.test')
        ->set('adminPassword', 'temporary-password')
        ->call('createTenant')
        ->assertDispatched('close-modal', name: 'tenant-form');
});

test('creating a tenant creates its first admin too, and that admin can log in and use the app immediately', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(Dashboard::class)
        ->set('name', 'Brand New NGO')
        ->set('adminName', 'Fresh Admin')
        ->set('adminEmail', 'fresh-admin@brand-new-ngo.test')
        ->set('adminPassword', 'temporary-password')
        ->call('createTenant')
        ->assertHasNoErrors();

    $tenant = Tenant::where('name', 'Brand New NGO')->sole();
    // withoutGlobalScopes(): this user belongs to a brand-new tenant,
    // not whatever tenant is ambient in this test's TenantContext (set
    // by tests/Pest.php's global beforeEach) — a plain scoped query
    // would fail closed and find nothing, same class of thing as
    // docs/build/DECISIONS.md D-031.
    $admin = User::withoutGlobalScopes()->where('email', 'fresh-admin@brand-new-ngo.test')->sole();

    expect($admin->tenant_id)->toBe($tenant->id)
        ->and($admin->hasRole(Role::HrAdmin->value))->toBeTrue()
        ->and($admin->hasVerifiedEmail())->toBeTrue();

    // The actual proof of "immediately usable": log in through the real
    // login form as this brand-new admin, not just assert the row
    // exists — see tests/Feature/Auth/AuthenticationTest.php for the
    // same pattern this mirrors.
    $component = Volt::test('pages.auth.login')
        ->set('form.email', 'fresh-admin@brand-new-ngo.test')
        ->set('form.password', 'temporary-password');

    $component->call('login')->assertHasNoErrors()->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($admin);

    // And that they can actually use a real feature of the (empty but
    // functional) new tenant — Organization, gated by HrmOrgView, which
    // HR Admin holds.
    $this->get('/organization')->assertOk();
});

test('a brand-new tenant\'s admin gets exactly HR Admin\'s access, not Super Admin\'s', function () {
    // Sanity check that "immediately usable" doesn't mean "wide open" —
    // the new admin's access is exactly what HR Admin's role grants,
    // nothing more.
    Livewire::actingAs($this->superAdmin)
        ->test(Dashboard::class)
        ->set('name', 'Another NGO')
        ->set('adminName', 'Admin Two')
        ->set('adminEmail', 'admin-two@another-ngo.test')
        ->set('adminPassword', 'temporary-password')
        ->call('createTenant');

    $admin = User::withoutGlobalScopes()->where('email', 'admin-two@another-ngo.test')->sole();

    $this->actingAs($admin)->get('/super-admin')->assertForbidden();
});

test('suspending a tenant blocks its users from logging in, even with the correct password', function () {
    $tenant = Tenant::factory()->create(['is_active' => true]);
    $employee = User::factory()->create(['tenant_id' => $tenant->id, 'password' => 'correct-password']);
    $employee->assignRole(Role::Employee->value);

    Livewire::actingAs($this->superAdmin)
        ->test(Dashboard::class)
        ->call('toggleSuspend', $tenant->id);

    expect($tenant->fresh()->is_active)->toBeFalse();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $employee->email)
        ->set('form.password', 'correct-password');

    $component->call('login')->assertHasErrors('form.email');

    $this->assertGuest();
});

test('reactivating a suspended tenant restores its users\' ability to log in', function () {
    $tenant = Tenant::factory()->create(['is_active' => false]);
    $employee = User::factory()->create(['tenant_id' => $tenant->id, 'password' => 'correct-password']);
    $employee->assignRole(Role::Employee->value);

    Livewire::actingAs($this->superAdmin)
        ->test(Dashboard::class)
        ->call('toggleSuspend', $tenant->id);

    expect($tenant->fresh()->is_active)->toBeTrue();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $employee->email)
        ->set('form.password', 'correct-password');

    $component->call('login')->assertHasNoErrors()->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($employee);
});

test('a Super Admin (no tenant) is never subject to the suspension check', function () {
    $component = Volt::test('pages.auth.login')
        ->set('form.email', $this->superAdmin->email)
        ->set('form.password', 'password');

    $component->call('login')->assertHasNoErrors();

    $this->assertAuthenticatedAs($this->superAdmin);
});

test('system health reflects real tenant, user, and storage counts', function () {
    // tests/Pest.php's global beforeEach already creates one active
    // tenant for ambient TenantContext — assert the delta this test
    // itself creates, not an absolute count, so this doesn't become
    // flaky against that shared setup.
    $before = (new Dashboard)->systemHealth();

    Tenant::factory()->count(2)->create(['is_active' => true]);
    Tenant::factory()->create(['is_active' => false]);

    $health = Livewire::actingAs($this->superAdmin)
        ->test(Dashboard::class)
        ->instance()
        ->systemHealth;

    expect($health['tenants_active'])->toBe($before['tenants_active'] + 2)
        ->and($health['tenants_suspended'])->toBe($before['tenants_suspended'] + 1)
        ->and($health['total_users'])->toBeGreaterThanOrEqual(1);
});

test('creating a tenant\'s first user works with no ambient tenant context at all, and its audit entry gets the new tenant\'s id', function () {
    // Regression test for a real bug found live (not by the 124-test
    // suite, which — like D-031 — was masked by tests/Pest.php's global
    // beforeEach always leaving SOME tenant ambient): a real Super Admin
    // request never has an ambient TenantContext at all (IdentifyTenant
    // only sets one when $user->tenant_id is truthy — never true for a
    // Super Admin). App\Models\AuditLogEntry's auto-logging (Auditable,
    // via BelongsToTenant's ambient-context fill) had nothing to fill
    // tenant_id from, so User::create() for a brand-new tenant's first
    // admin threw a NOT NULL constraint violation on activity_log —
    // every write, not a display bug. See docs/build/DECISIONS.md D-033.
    app(TenantContext::class)->clear();

    Livewire::actingAs($this->superAdmin)
        ->test(Dashboard::class)
        ->set('name', 'No Context NGO')
        ->set('adminName', 'No Context Admin')
        ->set('adminEmail', 'no-context-admin@example.test')
        ->set('adminPassword', 'temporary-password')
        ->call('createTenant')
        ->assertHasNoErrors();

    $tenant = Tenant::where('name', 'No Context NGO')->sole();
    $admin = User::withoutGlobalScopes()->where('email', 'no-context-admin@example.test')->sole();

    $entry = AuditLogEntry::withoutGlobalScopes()
        ->where('subject_type', User::class)
        ->where('subject_id', $admin->id)
        ->sole();

    expect($entry->tenant_id)->toBe($tenant->id);
});
