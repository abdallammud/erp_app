<?php

use App\Livewire\Organization\SalaryGrades;
use App\Models\SalaryGrade;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('an HR Admin can create, edit, and remove a salary grade', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    Livewire::actingAs($hrAdmin)
        ->test(SalaryGrades::class)
        ->set('name', 'Grade P3')
        ->set('code', 'P3')
        ->set('minSalary', '1000')
        ->set('maxSalary', '2000')
        ->set('currency', 'usd')
        ->call('save')
        ->assertHasNoErrors();

    $grade = SalaryGrade::sole();
    expect($grade->name)->toBe('Grade P3')
        ->and((float) $grade->min_salary)->toBe(1000.0)
        ->and($grade->currency)->toBe('USD');

    Livewire::actingAs($hrAdmin)
        ->test(SalaryGrades::class)
        ->call('edit', $grade->id)
        ->set('maxSalary', '2500')
        ->call('save')
        ->assertHasNoErrors();

    expect((float) $grade->fresh()->max_salary)->toBe(2500.0);

    Livewire::actingAs($hrAdmin)
        ->test(SalaryGrades::class)
        ->call('delete', $grade->id);

    expect(SalaryGrade::count())->toBe(0)
        ->and(SalaryGrade::withTrashed()->count())->toBe(1);
});

test('openCreate resets the form and opens the modal; save closes it', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    Livewire::actingAs($hrAdmin)
        ->test(SalaryGrades::class)
        ->set('name', 'stale value')
        ->call('openCreate')
        ->assertSet('name', '')
        ->assertDispatched('open-modal', name: 'salary-grade-form')
        ->set('name', 'Grade P3')
        ->set('code', 'P3')
        ->set('minSalary', '1000')
        ->set('maxSalary', '2000')
        ->call('save')
        ->assertDispatched('close-modal', name: 'salary-grade-form');
});

test('max salary must be at least min salary', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    Livewire::actingAs($hrAdmin)
        ->test(SalaryGrades::class)
        ->set('name', 'Broken Grade')
        ->set('code', 'BG')
        ->set('minSalary', '2000')
        ->set('maxSalary', '1000')
        ->call('save')
        ->assertHasErrors('maxSalary');

    expect(SalaryGrade::count())->toBe(0);
});

test('a Country Director can view salary grades but cannot create one', function () {
    $director = User::factory()->create();
    $director->assignRole(Role::CountryDirector->value);

    Livewire::actingAs($director)
        ->test(SalaryGrades::class)
        ->set('name', 'Should not be allowed')
        ->set('code', 'NA')
        ->set('minSalary', '100')
        ->set('maxSalary', '200')
        ->call('save')
        ->assertForbidden();

    expect(SalaryGrade::count())->toBe(0);
});

test('salary grades from another tenant are never visible', function () {
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $otherTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($otherTenant);
    SalaryGrade::factory()->create(['name' => 'Other Tenant Grade']);

    app(TenantContext::class)->set($hrAdmin->tenant);

    Livewire::actingAs($hrAdmin)
        ->test(SalaryGrades::class)
        ->assertDontSee('Other Tenant Grade');
});
