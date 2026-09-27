<?php

use App\Livewire\Hrm\Employees;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\SalaryGrade;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\Role;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * Build Plan Step 1.1's Definition of Done: an employee can be created
 * with a contract and a salary grade, entirely through UI, no seeders
 * needed.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->hrAdmin = User::factory()->create();
    $this->hrAdmin->assignRole(Role::HrAdmin->value);
});

test('the employees page requires hrm.org.view', function () {
    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);

    $this->actingAs($employee)->get('/employees')->assertForbidden();
    $this->actingAs($this->hrAdmin)->get('/employees')->assertOk();
});

test('creating an employee also creates their first contract, entirely through the UI', function () {
    $grade = SalaryGrade::factory()->create(['name' => 'Grade P3']);

    Livewire::actingAs($this->hrAdmin)
        ->test(Employees::class)
        ->set('employeeNumber', 'EMP-001')
        ->set('firstName', 'Amina')
        ->set('lastName', 'Yusuf')
        ->set('staffCategory', 'permanent')
        ->set('hireDate', '2026-01-15')
        ->set('contractType', 'fixed_term')
        ->set('salaryGradeId', $grade->id)
        ->set('contractStartDate', '2026-01-15')
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::where('employee_number', 'EMP-001')->sole();
    $contract = Contract::where('employee_id', $employee->id)->sole();

    expect($employee->fullName())->toBe('Amina Yusuf')
        ->and($employee->status->value)->toBe('active')
        ->and($contract->salary_grade_id)->toBe($grade->id)
        ->and($contract->type->value)->toBe('fixed_term')
        ->and($contract->status->value)->toBe('active')
        ->and($employee->currentContract()->id)->toBe($contract->id);
});

test('an employee cannot be created without a salary grade or contract type', function () {
    Livewire::actingAs($this->hrAdmin)
        ->test(Employees::class)
        ->set('employeeNumber', 'EMP-002')
        ->set('firstName', 'No')
        ->set('lastName', 'Contract')
        ->set('staffCategory', 'permanent')
        ->set('hireDate', '2026-01-15')
        ->call('save')
        ->assertHasErrors(['contractType', 'salaryGradeId', 'contractStartDate']);

    expect(Employee::count())->toBe(0);
});

test('national_id is encrypted at rest, not stored as plaintext', function () {
    $grade = SalaryGrade::factory()->create();

    Livewire::actingAs($this->hrAdmin)
        ->test(Employees::class)
        ->set('employeeNumber', 'EMP-003')
        ->set('firstName', 'Secure')
        ->set('lastName', 'Record')
        ->set('nationalId', 'SO-1234567890')
        ->set('staffCategory', 'permanent')
        ->set('hireDate', '2026-01-15')
        ->set('contractType', 'fixed_term')
        ->set('salaryGradeId', $grade->id)
        ->set('contractStartDate', '2026-01-15')
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::where('employee_number', 'EMP-003')->sole();

    expect($employee->national_id)->toBe('SO-1234567890');

    $rawValue = DB::table('employees')->where('id', $employee->id)->value('national_id');

    expect($rawValue)->not->toBe('SO-1234567890')
        ->and($rawValue)->not->toContain('1234567890');
});

test('editing an employee updates their record without touching their contract', function () {
    $grade = SalaryGrade::factory()->create();
    $employee = Employee::factory()->create(['first_name' => 'Original']);
    $contract = Contract::factory()->create(['employee_id' => $employee->id, 'salary_grade_id' => $grade->id]);

    Livewire::actingAs($this->hrAdmin)
        ->test(Employees::class)
        ->call('edit', $employee->id)
        ->set('firstName', 'Renamed')
        ->call('save')
        ->assertHasNoErrors();

    expect($employee->fresh()->first_name)->toBe('Renamed')
        ->and(Contract::count())->toBe(1)
        ->and($contract->fresh()->salary_grade_id)->toBe($grade->id);
});

test('employee numbers are unique per tenant, but editing the same employee does not trip that check', function () {
    $grade = SalaryGrade::factory()->create();
    $existing = Employee::factory()->create(['employee_number' => 'EMP-100']);
    Contract::factory()->create(['employee_id' => $existing->id]);

    Livewire::actingAs($this->hrAdmin)
        ->test(Employees::class)
        ->set('employeeNumber', 'EMP-100')
        ->set('firstName', 'Dup')
        ->set('lastName', 'Licate')
        ->set('staffCategory', 'permanent')
        ->set('hireDate', '2026-01-15')
        ->set('contractType', 'fixed_term')
        ->set('salaryGradeId', $grade->id)
        ->set('contractStartDate', '2026-01-15')
        ->call('save')
        ->assertHasErrors('employeeNumber');

    // Editing the existing record and keeping its own number must NOT
    // trip the uniqueness check against itself.
    Livewire::actingAs($this->hrAdmin)
        ->test(Employees::class)
        ->call('edit', $existing->id)
        ->set('employeeNumber', 'EMP-100')
        ->call('save')
        ->assertHasNoErrors();
});

test('an employee cannot be set as their own manager', function () {
    $employee = Employee::factory()->create(['first_name' => 'Selfreport', 'last_name' => 'Loop']);
    Contract::factory()->create(['employee_id' => $employee->id]);

    // The full employee list (used to populate every OTHER select on
    // this page) still contains them — only the "reports to" <option>
    // for their own id must be missing while editing themselves.
    Livewire::actingAs($this->hrAdmin)
        ->test(Employees::class)
        ->call('edit', $employee->id)
        ->assertDontSee('<option value="'.$employee->id.'">'.$employee->fullName().'</option>', false);
});

test('a Country Director can view employees but cannot create one', function () {
    $director = User::factory()->create();
    $director->assignRole(Role::CountryDirector->value);
    $grade = SalaryGrade::factory()->create();

    Livewire::actingAs($director)
        ->test(Employees::class)
        ->set('employeeNumber', 'EMP-999')
        ->set('firstName', 'Should')
        ->set('lastName', 'Fail')
        ->set('staffCategory', 'permanent')
        ->set('hireDate', '2026-01-15')
        ->set('contractType', 'fixed_term')
        ->set('salaryGradeId', $grade->id)
        ->set('contractStartDate', '2026-01-15')
        ->call('save')
        ->assertForbidden();

    expect(Employee::count())->toBe(0);
});

test('a portal account can be linked to an employee, and stops appearing as linkable to another', function () {
    $user = User::factory()->create();
    $grade = SalaryGrade::factory()->create();

    Livewire::actingAs($this->hrAdmin)
        ->test(Employees::class)
        ->set('employeeNumber', 'EMP-500')
        ->set('firstName', 'Linked')
        ->set('lastName', 'User')
        ->set('userId', $user->id)
        ->set('staffCategory', 'permanent')
        ->set('hireDate', '2026-01-15')
        ->set('contractType', 'fixed_term')
        ->set('salaryGradeId', $grade->id)
        ->set('contractStartDate', '2026-01-15')
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::where('employee_number', 'EMP-500')->sole();
    expect($employee->user_id)->toBe($user->id);

    $component = Livewire::actingAs($this->hrAdmin)->test(Employees::class);
    expect($component->instance()->linkableUsers->pluck('id'))->not->toContain($user->id);
});

test('employees from another tenant are never visible', function () {
    $otherTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($otherTenant);
    Employee::factory()->create(['first_name' => 'Other', 'last_name' => 'Tenant']);

    app(TenantContext::class)->set($this->hrAdmin->tenant);

    Livewire::actingAs($this->hrAdmin)
        ->test(Employees::class)
        ->assertDontSee('Other Tenant');
});
