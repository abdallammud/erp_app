<?php

use App\Livewire\AuditLog\Index;
use App\Models\Department;
use App\Models\User;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('an Employee cannot reach the audit log; an HR Admin can', function () {
    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);
    $this->actingAs($employee)->get('/audit-log')->assertForbidden();

    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);
    $this->actingAs($hrAdmin)->get('/audit-log')->assertOk();
});

test('the audit log lists real entries with who/what/when', function () {
    $hrAdmin = User::factory()->create(['name' => 'Amina Yusuf']);
    $hrAdmin->assignRole(Role::HrAdmin->value);
    $this->actingAs($hrAdmin);

    Department::create(['name' => 'Water & Sanitation']);

    Livewire::test(Index::class)
        ->assertSee('Department')
        ->assertSee('Water & Sanitation')
        ->assertSee('created')
        ->assertSee($hrAdmin->name);
});

test('filtering by event only shows matching entries', function () {
    // The dropdown itself always renders <option value="updated">/
    // "created" regardless of the active filter, so assertions here
    // check the actual filtered entry count and content (via the
    // details table's field values), not the literal words "created"/
    // "updated" anywhere in the page.
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);
    $this->actingAs($hrAdmin);

    $department = Department::create(['name' => 'Logistics']);
    $department->update(['name' => 'Logistics & Supply']);

    $component = Livewire::test(Index::class)->set('event', 'created');
    expect($component->instance()->entries->pluck('event')->unique()->all())->toBe(['created']);
    $component->assertSee('Logistics')->assertDontSee('Logistics & Supply');

    $component->set('event', 'updated');
    expect($component->instance()->entries->pluck('event')->unique()->all())->toBe(['updated']);
    $component->assertSee('Logistics & Supply');
});
