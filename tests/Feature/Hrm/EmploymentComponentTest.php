<?php

use App\Livewire\Employees\Employment;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\SalaryGrade;
use App\Models\User;
use Livewire\Livewire;

test('a user with no linked employee record sees a friendly message', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Employment::class)
        ->assertSee('No employee record is linked to your account yet');
});

test('an employee sees their own department, position, and contract history, including their own salary grade', function () {
    $user = User::factory()->create();
    $department = Department::factory()->create(['name' => 'Programs']);
    $employee = Employee::factory()->create(['user_id' => $user->id, 'department_id' => $department->id]);
    $grade = SalaryGrade::factory()->create(['name' => 'Grade P4', 'code' => 'P4']);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'salary_grade_id' => $grade->id,
        'type' => 'fixed_term',
    ]);

    Livewire::actingAs($user)
        ->test(Employment::class)
        ->assertSee('Programs')
        ->assertSee('Grade P4')
        ->assertSee('P4')
        ->assertSee('Fixed Term');
});

test('an employee cannot see another employee\'s employment details through this component', function () {
    $user = User::factory()->create();
    Employee::factory()->create(['user_id' => $user->id]);

    Employee::factory()->create(['employee_number' => 'OTHER-EMP-001']);

    Livewire::actingAs($user)
        ->test(Employment::class)
        ->assertDontSee('OTHER-EMP-001');
});
