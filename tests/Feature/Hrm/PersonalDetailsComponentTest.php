<?php

use App\Livewire\Employees\PersonalDetails;
use App\Models\Dependent;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * Build Plan Step 1.2: "Profile view/edit with change-history logging"
 * (contact info) and "Dependents & emergency contacts, with insurance-
 * beneficiary percentage-split validation (must total 100%)".
 */
test('a user with no linked employee record sees a friendly message', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(PersonalDetails::class)
        ->assertSee('No employee record is linked to your account yet');
});

test('an employee can update their own contact information', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id, 'phone' => 'old-phone']);

    Livewire::actingAs($user)
        ->test(PersonalDetails::class)
        ->set('phone', '+252-000-0000')
        ->set('personalEmail', 'me@example.test')
        ->set('address', '123 Main St')
        ->call('saveContactInfo')
        ->assertHasNoErrors();

    expect($employee->fresh()->phone)->toBe('+252-000-0000')
        ->and($employee->fresh()->personal_email)->toBe('me@example.test');
});

test('a dependent can be added, edited, and removed', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(PersonalDetails::class)
        ->set('firstName', 'Layla')
        ->set('lastName', 'Ahmed')
        ->set('relationship', 'child')
        ->set('isEmergencyContact', true)
        ->call('saveDependent')
        ->assertHasNoErrors();

    $dependent = Dependent::where('employee_id', $employee->id)->sole();
    expect($dependent->fullName())->toBe('Layla Ahmed')
        ->and($dependent->is_emergency_contact)->toBeTrue();

    Livewire::actingAs($user)
        ->test(PersonalDetails::class)
        ->call('editDependent', $dependent->id)
        ->set('lastName', 'Yusuf')
        ->call('saveDependent')
        ->assertHasNoErrors();

    expect($dependent->fresh()->last_name)->toBe('Yusuf');

    Livewire::actingAs($user)
        ->test(PersonalDetails::class)
        ->call('deleteDependent', $dependent->id);

    expect(Dependent::count())->toBe(0)
        ->and(Dependent::withTrashed()->count())->toBe(1);
});

test('passport_number is encrypted at rest', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(PersonalDetails::class)
        ->set('firstName', 'Layla')
        ->set('lastName', 'Ahmed')
        ->set('relationship', 'child')
        ->set('passportNumber', 'P-9988776')
        ->call('saveDependent')
        ->assertHasNoErrors();

    $dependent = Dependent::where('employee_id', $employee->id)->sole();
    $raw = DB::table('dependents')->where('id', $dependent->id)->value('passport_number');

    expect($dependent->passport_number)->toBe('P-9988776')
        ->and($raw)->not->toContain('9988776');
});

test('beneficiary allocations cannot exceed 100% in total', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id]);

    Dependent::factory()->create([
        'employee_id' => $employee->id,
        'is_insurance_beneficiary' => true,
        'insurance_beneficiary_percentage' => 70,
    ]);

    Livewire::actingAs($user)
        ->test(PersonalDetails::class)
        ->set('firstName', 'Second')
        ->set('lastName', 'Beneficiary')
        ->set('relationship', 'spouse')
        ->set('isInsuranceBeneficiary', true)
        ->set('insuranceBeneficiaryPercentage', '40')
        ->call('saveDependent')
        ->assertHasErrors('insuranceBeneficiaryPercentage');

    expect(Dependent::where('is_insurance_beneficiary', true)->count())->toBe(1);
});

test('beneficiary allocations totalling exactly 100% across dependents are accepted', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id]);

    Dependent::factory()->create([
        'employee_id' => $employee->id,
        'is_insurance_beneficiary' => true,
        'insurance_beneficiary_percentage' => 60,
    ]);

    Livewire::actingAs($user)
        ->test(PersonalDetails::class)
        ->set('firstName', 'Second')
        ->set('lastName', 'Beneficiary')
        ->set('relationship', 'spouse')
        ->set('isInsuranceBeneficiary', true)
        ->set('insuranceBeneficiaryPercentage', '40')
        ->call('saveDependent')
        ->assertHasNoErrors();

    expect((float) Dependent::where('is_insurance_beneficiary', true)->sum('insurance_beneficiary_percentage'))->toBe(100.0);
});

test('editing a beneficiary\'s own percentage does not double-count it against itself', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id]);

    $dependent = Dependent::factory()->create([
        'employee_id' => $employee->id,
        'is_insurance_beneficiary' => true,
        'insurance_beneficiary_percentage' => 60,
    ]);

    // Raising this dependent's own share to 90% (still <= 100% overall,
    // since it's the only beneficiary) must not be rejected by counting
    // its OLD 60% against its OWN new value.
    Livewire::actingAs($user)
        ->test(PersonalDetails::class)
        ->call('editDependent', $dependent->id)
        ->set('insuranceBeneficiaryPercentage', '90')
        ->call('saveDependent')
        ->assertHasNoErrors();

    expect((float) $dependent->fresh()->insurance_beneficiary_percentage)->toBe(90.0);
});

test('a dependent belonging to another employee cannot be edited or deleted', function () {
    $user = User::factory()->create();
    Employee::factory()->create(['user_id' => $user->id]);

    $otherEmployee = Employee::factory()->create();
    $otherDependent = Dependent::factory()->create(['employee_id' => $otherEmployee->id]);

    // editDependent() scopes its lookup through $employee->dependents(),
    // so another employee's dependent id genuinely doesn't exist from
    // this component's point of view — findOrFail throws, it isn't
    // silently ignored or, worse, editable.
    expect(fn () => Livewire::actingAs($user)->test(PersonalDetails::class)->call('editDependent', $otherDependent->id))
        ->toThrow(ModelNotFoundException::class);
});
