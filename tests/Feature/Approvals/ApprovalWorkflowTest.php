<?php

use App\Models\ApprovalChain;
use App\Models\ApprovalInstance;
use App\Models\Tenant;
use App\Models\TestRequest;
use App\Models\User;
use App\Support\Approvals\ApprovalStatus;
use App\Support\Approvals\ApprovalStepStatus;
use App\Support\Approvals\ApprovalWorkflow;
use App\Support\Authorization\Role;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * The flagship suite for Build Plan Step 0.6 — the approval engine every
 * future module (leave, payroll, requisitions) will build on.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->chain = ApprovalChain::factory()->create(['action_type' => 'test_request']);
    $this->chain->steps()->createMany([
        ['sequence' => 1, 'approver_role' => Role::Supervisor->value, 'label' => 'Supervisor review'],
        ['sequence' => 2, 'approver_role' => Role::HrAdmin->value, 'label' => 'HR Admin approval'],
    ]);

    $this->workflow = app(ApprovalWorkflow::class);
});

test('submitting creates an instance with one step per chain step, starting pending at step 1', function () {
    $requester = User::factory()->create();
    $requester->assignRole(Role::Employee->value);

    $request = TestRequest::factory()->create(['requester_id' => $requester->id]);

    $instance = $this->workflow->submit($this->chain, $request, $requester);

    expect($instance->status)->toBe(ApprovalStatus::Pending)
        ->and($instance->current_sequence)->toBe(1)
        ->and($instance->steps)->toHaveCount(2)
        ->and($instance->steps->every(fn ($s) => $s->status === ApprovalStepStatus::Pending))->toBeTrue();
});

test('a full approval chain moves through both steps to Approved', function () {
    $requester = User::factory()->create();
    $requester->assignRole(Role::Employee->value);
    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::Supervisor->value);
    $hrAdmin = User::factory()->create();
    $hrAdmin->assignRole(Role::HrAdmin->value);

    $request = TestRequest::factory()->create(['requester_id' => $requester->id]);
    $instance = $this->workflow->submit($this->chain, $request, $requester);

    $instance = $this->workflow->approve($instance, $supervisor, 'Looks fine to me');

    expect($instance->status)->toBe(ApprovalStatus::Pending)
        ->and($instance->current_sequence)->toBe(2)
        ->and($instance->steps->firstWhere('sequence', 1)->status)->toBe(ApprovalStepStatus::Approved)
        ->and($instance->steps->firstWhere('sequence', 1)->actor->id)->toBe($supervisor->id)
        ->and($instance->steps->firstWhere('sequence', 1)->comment)->toBe('Looks fine to me');

    $instance = $this->workflow->approve($instance, $hrAdmin);

    expect($instance->status)->toBe(ApprovalStatus::Approved)
        ->and($instance->current_sequence)->toBeNull()
        ->and($instance->steps->firstWhere('sequence', 2)->status)->toBe(ApprovalStepStatus::Approved);
});

test('rejecting at step 1 stops the chain and marks remaining steps skipped, not approved', function () {
    $requester = User::factory()->create();
    $requester->assignRole(Role::Employee->value);
    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::Supervisor->value);

    $request = TestRequest::factory()->create(['requester_id' => $requester->id]);
    $instance = $this->workflow->submit($this->chain, $request, $requester);

    $instance = $this->workflow->reject($instance, $supervisor, 'Not approved');

    expect($instance->status)->toBe(ApprovalStatus::Rejected)
        ->and($instance->current_sequence)->toBeNull()
        ->and($instance->steps->firstWhere('sequence', 1)->status)->toBe(ApprovalStepStatus::Rejected)
        ->and($instance->steps->firstWhere('sequence', 2)->status)->toBe(ApprovalStepStatus::Skipped);
});

test('segregation of duties: the requester cannot approve their own request, even holding the step role', function () {
    // The requester happens to also hold the Supervisor role — a real
    // scenario (a supervisor requesting something for themselves) that
    // must not let them approve their own request at step 1.
    $requester = User::factory()->create();
    $requester->assignRole(Role::Supervisor->value);

    $request = TestRequest::factory()->create(['requester_id' => $requester->id]);
    $instance = $this->workflow->submit($this->chain, $request, $requester);

    expect($this->workflow->canAct($instance, $requester))->toBeFalse();

    $this->workflow->approve($instance, $requester);
})->throws(RuntimeException::class);

test('a user without the current step\'s role cannot act', function () {
    $requester = User::factory()->create();
    $requester->assignRole(Role::Employee->value);
    $wrongRole = User::factory()->create();
    $wrongRole->assignRole(Role::HrAdmin->value); // step 1 needs Supervisor, not HR Admin

    $request = TestRequest::factory()->create(['requester_id' => $requester->id]);
    $instance = $this->workflow->submit($this->chain, $request, $requester);

    expect($this->workflow->canAct($instance, $wrongRole))->toBeFalse();

    $this->workflow->approve($instance, $wrongRole);
})->throws(RuntimeException::class);

test('a step cannot be acted on twice — once approved, canAct is false even for a still-eligible approver', function () {
    $requester = User::factory()->create();
    $requester->assignRole(Role::Employee->value);
    $supervisorA = User::factory()->create();
    $supervisorA->assignRole(Role::Supervisor->value);
    $supervisorB = User::factory()->create();
    $supervisorB->assignRole(Role::Supervisor->value);

    $request = TestRequest::factory()->create(['requester_id' => $requester->id]);
    $instance = $this->workflow->submit($this->chain, $request, $requester);

    $instance = $this->workflow->approve($instance, $supervisorA);

    // Step 1 is done; step 2 needs HR Admin, so a second Supervisor
    // cannot act even though they hold "a" role in this chain.
    expect($this->workflow->canAct($instance, $supervisorB))->toBeFalse();
});

test('awaitingActionBy only returns instances where the given user is eligible, excluding their own requests', function () {
    $requester = User::factory()->create();
    $requester->assignRole(Role::Supervisor->value); // eligible role, but it's their own request
    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::Supervisor->value);
    $unrelated = User::factory()->create();
    $unrelated->assignRole(Role::Employee->value);

    $request = TestRequest::factory()->create(['requester_id' => $requester->id]);
    $this->workflow->submit($this->chain, $request, $requester);

    expect($this->workflow->awaitingActionBy($supervisor))->toHaveCount(1)
        ->and($this->workflow->awaitingActionBy($requester))->toHaveCount(0) // segregation of duties
        ->and($this->workflow->awaitingActionBy($unrelated))->toHaveCount(0); // wrong role
});

test('tenant_id is explicitly mass-assignable on approval models, not just auto-filled from context', function () {
    // Regression test: seeders that use WithoutModelEvents (like
    // DatabaseSeeder here) skip the `creating` hook BelongsToTenant
    // relies on for auto-fill — so an explicit `tenant_id` in the
    // create array is the only way those get set correctly. That
    // requires `tenant_id` to actually be in each model's #[Fillable]
    // list, which it originally wasn't (only User's was) — caught when
    // DemoTenantSeeder failed with a NOT NULL constraint violation
    // trying to create an ApprovalChain with an explicit tenant_id that
    // mass assignment was silently dropping.
    $otherTenant = Tenant::factory()->create();

    $chain = ApprovalChain::create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Explicit tenant chain',
        'action_type' => 'explicit_test',
    ]);

    expect($chain->tenant_id)->toBe($otherTenant->id);

    $step = $chain->steps()->create([
        'tenant_id' => $otherTenant->id,
        'sequence' => 1,
        'approver_role' => Role::Supervisor->value,
    ]);

    expect($step->tenant_id)->toBe($otherTenant->id);
});

test('an approval instance from another tenant is invisible', function () {
    $requester = User::factory()->create();
    $requester->assignRole(Role::Employee->value);
    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::Supervisor->value);

    $request = TestRequest::factory()->create(['requester_id' => $requester->id]);
    $this->workflow->submit($this->chain, $request, $requester);

    $otherTenant = Tenant::factory()->create();
    app(TenantContext::class)->set($otherTenant);

    expect(ApprovalInstance::count())->toBe(0);
});
