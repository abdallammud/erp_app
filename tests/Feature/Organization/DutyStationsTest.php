<?php

use App\Livewire\Organization\DutyStations;
use App\Models\DutyStation;
use App\Models\User;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('an HR Admin can create, edit, and remove a duty station', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    Livewire::actingAs($hrAdmin)
        ->test(DutyStations::class)
        ->set('name', 'Mogadishu Office')
        ->set('countryCode', 'so')
        ->set('city', 'Mogadishu')
        ->call('save')
        ->assertHasNoErrors();

    $station = DutyStation::sole();
    expect($station->name)->toBe('Mogadishu Office')
        // uppercased regardless of what was typed — see save()
        ->and($station->country_code)->toBe('SO');

    Livewire::actingAs($hrAdmin)
        ->test(DutyStations::class)
        ->call('edit', $station->id)
        ->set('city', 'Mogadishu (renamed)')
        ->call('save')
        ->assertHasNoErrors();

    expect($station->fresh()->city)->toBe('Mogadishu (renamed)');

    Livewire::actingAs($hrAdmin)
        ->test(DutyStations::class)
        ->call('delete', $station->id);

    expect(DutyStation::count())->toBe(0)
        ->and(DutyStation::withTrashed()->count())->toBe(1);
});

test('an Employee cannot create a duty station', function () {
    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);

    Livewire::actingAs($employee)
        ->test(DutyStations::class)
        ->set('name', 'Should not be allowed')
        ->set('countryCode', 'so')
        ->call('save')
        ->assertForbidden();

    expect(DutyStation::count())->toBe(0);
});

test('country code is required', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    Livewire::actingAs($hrAdmin)
        ->test(DutyStations::class)
        ->set('name', 'No Country Office')
        ->set('countryCode', '')
        ->call('save')
        ->assertHasErrors('countryCode');
});
