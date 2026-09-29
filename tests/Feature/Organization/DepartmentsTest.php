<?php

use App\Livewire\Organization\Departments;
use App\Models\Department;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('the organization page requires hrm.org.view', function () {
    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);

    $this->actingAs($employee)->get('/organization')->assertForbidden();

    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $this->actingAs($hrAdmin)->get('/organization')->assertOk();
});

test('an HR Admin can create, edit, and remove a department', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    Livewire::actingAs($hrAdmin)
        ->test(Departments::class)
        ->set('name', 'Health & Nutrition')
        ->set('code', 'HN')
        ->call('save')
        ->assertHasNoErrors();

    $department = Department::sole();
    expect($department->name)->toBe('Health & Nutrition');

    Livewire::actingAs($hrAdmin)
        ->test(Departments::class)
        ->call('edit', $department->id)
        ->set('name', 'Health & Nutrition (renamed)')
        ->call('save')
        ->assertHasNoErrors();

    expect($department->fresh()->name)->toBe('Health & Nutrition (renamed)');

    Livewire::actingAs($hrAdmin)
        ->test(Departments::class)
        ->call('delete', $department->id);

    expect(Department::count())->toBe(0)
        // soft-deleted, not gone — docs/08-data-model.md's soft-delete rule.
        ->and(Department::withTrashed()->count())->toBe(1);
});

test('openCreate resets the form and opens the modal; save closes it', function () {
    // Regression coverage for the modal contract itself (see
    // docs/build/DESIGN.md) — a prior version of x-modal's close-modal
    // listener compared $event.detail (an object, from Livewire's
    // dispatch()) against a plain string and could never match, so
    // "close" silently never fired. This locks in that dispatch() is
    // actually called with the right event/name pair.
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    Livewire::actingAs($hrAdmin)
        ->test(Departments::class)
        ->set('name', 'stale value')
        ->call('openCreate')
        ->assertSet('name', '')
        ->assertDispatched('open-modal', name: 'department-form')
        ->set('name', 'Health & Nutrition')
        ->call('save')
        ->assertDispatched('close-modal', name: 'department-form');

    $department = Department::sole();

    Livewire::actingAs($hrAdmin)
        ->test(Departments::class)
        ->call('edit', $department->id)
        ->assertDispatched('open-modal', name: 'department-form')
        ->call('cancel')
        ->assertDispatched('close-modal', name: 'department-form');
});

test('a Country Director can view departments but cannot create one', function () {
    $director = User::factory()->create();
    $director->assignRole(Role::CountryDirector->value);

    Livewire::actingAs($director)
        ->test(Departments::class)
        ->set('name', 'Should not be allowed')
        ->call('save')
        ->assertForbidden();

    expect(Department::count())->toBe(0);
});

test('a department can be created with a parent, and the link actually persists', function () {
    // Regression test: save() originally passed the validated array
    // straight to create()/update() with the key `parentDepartmentId`
    // (the PHP property name), but the column is `parent_department_id`
    // — silently ignored by mass assignment, so the parent link never
    // actually saved even though the form appeared to accept it.
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $parent = Department::factory()->create(['name' => 'Programs']);

    Livewire::actingAs($hrAdmin)
        ->test(Departments::class)
        ->set('name', 'Health & Nutrition')
        ->set('parentDepartmentId', $parent->id)
        ->call('save')
        ->assertHasNoErrors();

    $child = Department::where('name', 'Health & Nutrition')->sole();

    expect($child->parent_department_id)->toBe($parent->id)
        ->and($child->parent->name)->toBe('Programs');
});

test('a department cannot be set as its own parent', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $department = Department::factory()->create();

    Livewire::actingAs($hrAdmin)
        ->test(Departments::class)
        ->call('edit', $department->id)
        ->set('parentDepartmentId', $department->id)
        ->call('save')
        ->assertHasErrors('parentDepartmentId');
});

test('departments from another tenant are never visible', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $otherTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($otherTenant);
    Department::factory()->create(['name' => 'Other tenant department']);

    // Back to the acting user's own tenant for the Livewire test.
    app(TenantContext::class)->set($hrAdmin->tenant);

    Livewire::actingAs($hrAdmin)
        ->test(Departments::class)
        ->assertDontSee('Other tenant department');
});
