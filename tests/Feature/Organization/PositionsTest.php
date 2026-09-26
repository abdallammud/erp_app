<?php

use App\Livewire\Organization\Positions;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('an HR Admin can create a position linked to a department', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $department = Department::factory()->create(['name' => 'Logistics']);

    Livewire::actingAs($hrAdmin)
        ->test(Positions::class)
        ->set('title', 'Logistics Officer')
        ->set('grade', 'P2')
        ->set('departmentId', $department->id)
        ->call('save')
        ->assertHasNoErrors();

    $position = Position::sole();

    expect($position->title)->toBe('Logistics Officer')
        ->and($position->grade)->toBe('P2')
        ->and($position->department_id)->toBe($department->id)
        ->and($position->department->name)->toBe('Logistics');
});

test('a Procurement Officer cannot manage positions', function () {
    $procurementOfficer = User::factory()->create();
    $procurementOfficer->assignRole(Role::ProcurementOfficer->value);

    Livewire::actingAs($procurementOfficer)
        ->test(Positions::class)
        ->set('title', 'Should not be allowed')
        ->call('save')
        ->assertForbidden();

    expect(Position::count())->toBe(0);
});

test('removing a position soft-deletes it, not hard-deletes', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $position = Position::factory()->create();

    Livewire::actingAs($hrAdmin)
        ->test(Positions::class)
        ->call('delete', $position->id);

    expect(Position::count())->toBe(0)
        ->and(Position::withTrashed()->count())->toBe(1);
});
