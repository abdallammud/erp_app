<?php

use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\DemoTenantSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Proves the RBAC mechanism built in Build Plan Step 0.4 actually
 * differentiates roles the way docs/03-roles-and-permissions.md's access
 * matrix says it should. Not exhaustive (10 roles x ~7 modules) — a
 * representative set of the matrix's most important distinctions,
 * including the ones a bug would most plausibly get wrong: confidentiality
 * tiers, Supervisor vs. Employee, and Super Admin's deliberate lack of
 * tenant-business access.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('the full role catalog from docs/03-roles-and-permissions.md is seeded', function () {
    expect(SpatieRole::pluck('name')->sort()->values()->all())
        ->toEqual(collect(Role::all())->map->value->sort()->values()->all());
});

test('an Employee can see their own HR record but not the org-wide view', function () {
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);

    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);

    expect($employee->can(Permission::HrmOwnView->value))->toBeTrue()
        ->and($employee->can(Permission::HrmOrgView->value))->toBeFalse()
        ->and($employee->can(Permission::HrmOrgApprove->value))->toBeFalse();
});

test('a Supervisor can approve their team\'s HR requests but not the whole org\'s', function () {
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);

    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::Supervisor->value);

    expect($supervisor->can(Permission::HrmTeamApprove->value))->toBeTrue()
        ->and($supervisor->can(Permission::HrmOrgApprove->value))->toBeFalse();
});

test('only Payroll/Finance Officer can approve payroll and finance', function () {
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);

    $payrollOfficer = User::factory()->create();
    $payrollOfficer->assignRole(Role::PayrollFinanceOfficer->value);

    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    expect($payrollOfficer->can(Permission::PayrollApprove->value))->toBeTrue()
        ->and($payrollOfficer->can(Permission::FinanceApprove->value))->toBeTrue()
        // HR Admin can see payroll/finance but never approve it — a real
        // segregation-of-duties distinction from the matrix, not just a
        // naming difference.
        ->and($hrAdmin->can(Permission::PayrollView->value))->toBeTrue()
        ->and($hrAdmin->can(Permission::PayrollApprove->value))->toBeFalse()
        ->and($hrAdmin->can(Permission::FinanceApprove->value))->toBeFalse();
});

test('Super Admin has no default tenant-business permissions', function () {
    $superAdmin = User::factory()->create(['tenant_id' => null]);
    $superAdmin->assignRole(Role::SuperAdmin->value);

    foreach (Permission::all() as $permission) {
        expect($superAdmin->can($permission->value))->toBeFalse();
    }
});

test('the demo tenant seeder produces one user per non-Super-Admin role, correctly assigned', function () {
    $this->seed(DemoTenantSeeder::class);

    // Not Tenant::firstOrFail() — Pest.php's global beforeEach already
    // created an unrelated default tenant for this test (Tenant isn't
    // tenant-scoped, so "first" isn't necessarily "the one we just
    // seeded"). Find the demo tenant specifically.
    $tenant = Tenant::where('slug', 'demo-ngo')->firstOrFail();
    app(TenantContext::class)->set($tenant);

    $rolesExpectedInTenant = collect(Role::all())->reject(fn (Role $r) => $r === Role::SuperAdmin);

    expect(User::count())->toBe($rolesExpectedInTenant->count());

    foreach ($rolesExpectedInTenant as $role) {
        $user = User::whereHas('roles', fn ($q) => $q->where('name', $role->value))->first();

        expect($user)->not->toBeNull("expected a seeded user with role [{$role->value}]")
            ->and($user->tenant_id)->toBe($tenant->id);
    }
});
