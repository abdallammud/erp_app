<?php

use App\Livewire\Approvals\Demo;
use App\Models\ApprovalChain;
use App\Models\TestRequest;
use App\Models\User;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

/**
 * Exercises the actual Livewire entry point (App\Livewire\Approvals\Demo),
 * not just App\Support\Approvals\ApprovalWorkflow directly.
 *
 * This gap is exactly how a real bug got through: submit() never set
 * requester_id at all (only title/reason come from validate()), and
 * every ApprovalWorkflowTest.php test used TestRequest::factory(), which
 * sets requester_id itself — so the missing wiring in the component's own
 * submit() method was invisible to the service-layer tests. Caught only
 * by manually driving the real page via tinker. See
 * docs/build/DECISIONS.md.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $chain = ApprovalChain::factory()->create(['action_type' => 'test_request']);
    $chain->steps()->createMany([
        ['sequence' => 1, 'approver_role' => Role::Supervisor->value, 'label' => 'Supervisor review'],
        ['sequence' => 2, 'approver_role' => Role::HrAdmin->value, 'label' => 'HR Admin approval'],
    ]);
});

test('submitting through the real component sets requester_id and creates a pending instance', function () {
    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);

    Livewire::actingAs($employee)
        ->test(Demo::class)
        ->set('title', 'Laptop replacement')
        ->set('reason', 'Screen is cracked')
        ->call('submit')
        ->assertHasNoErrors();

    $request = TestRequest::sole();

    expect($request->requester_id)->toBe($employee->id)
        ->and($request->approvalInstance)->not->toBeNull()
        ->and($request->approvalInstance->status->value)->toBe('pending');
});

test('the full demo flow works end to end through the real component: submit, approve, approve', function () {
    $employee = User::factory()->create();
    $employee->assignRole(Role::Employee->value);
    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::Supervisor->value);
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    Livewire::actingAs($employee)
        ->test(Demo::class)
        ->set('title', 'Laptop replacement')
        ->call('submit');

    $instance = TestRequest::sole()->approvalInstance;

    Livewire::actingAs($supervisor)
        ->test(Demo::class)
        ->assertSee('Laptop replacement')
        ->call('approve', $instance->id)
        ->assertHasNoErrors();

    expect($instance->fresh()->status->value)->toBe('pending')
        ->and($instance->fresh()->current_sequence)->toBe(2);

    Livewire::actingAs($hrAdmin)
        ->test(Demo::class)
        ->assertSee('Laptop replacement')
        ->call('approve', $instance->id)
        ->assertHasNoErrors();

    expect($instance->fresh()->status->value)->toBe('approved');
});

test('the component will not let the requester approve their own request', function () {
    $employee = User::factory()->create();
    $employee->assignRole([Role::Employee->value, Role::Supervisor->value]);

    Livewire::actingAs($employee)
        ->test(Demo::class)
        ->set('title', 'Self-approval attempt')
        ->call('submit');

    $instance = TestRequest::sole()->approvalInstance;

    Livewire::actingAs($employee)
        ->test(Demo::class)
        ->call('approve', $instance->id)
        ->assertHasErrors('approval');

    expect($instance->fresh()->status->value)->toBe('pending');
});
