<?php

use App\Models\ApprovalChain;
use App\Models\TestRequest;
use App\Models\User;
use App\Notifications\Approvals\ApprovalDecisionMade;
use App\Notifications\Approvals\ApprovalNeeded;
use App\Support\Approvals\ApprovalWorkflow;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;

/**
 * Build Plan Step 0.7's Definition of Done: an in-app + email
 * notification fires when the demo approval workflow changes status.
 *
 * Deliberately exercises real events (submit/approve/reject through
 * App\Support\Approvals\ApprovalWorkflow, not by calling notify()
 * directly) — the same lesson as
 * tests/Feature/Approvals/DemoComponentTest.php: a notification class
 * being correct in isolation doesn't prove the listener wiring that's
 * supposed to trigger it actually fires. The last test in this file
 * skips Notification::fake() specifically to prove a real database row
 * lands, not just that Laravel *would* have sent something.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->chain = ApprovalChain::factory()->create(['action_type' => 'test_request']);
    $this->chain->steps()->createMany([
        ['sequence' => 1, 'approver_role' => Role::Supervisor->value, 'label' => 'Supervisor review'],
        ['sequence' => 2, 'approver_role' => Role::HrAdmin->value, 'label' => 'HR Admin approval'],
    ]);

    $this->workflow = app(ApprovalWorkflow::class);
    $this->requester = User::factory()->create();
    $this->requester->assignRole(Role::Employee->value);
    $this->supervisor = User::factory()->create();
    $this->supervisor->assignRole(Role::Supervisor->value);
    $this->hrAdmin = User::factory()->create();
    $this->hrAdmin->assignRole(Role::HrAdmin->value);
});

test('submitting notifies the step-1 eligible approver, not the requester or unrelated roles', function () {
    Notification::fake();

    $request = TestRequest::factory()->create(['requester_id' => $this->requester->id]);
    $this->workflow->submit($this->chain, $request, $this->requester);

    Notification::assertSentTo($this->supervisor, ApprovalNeeded::class);
    Notification::assertNotSentTo($this->requester, ApprovalNeeded::class);
    Notification::assertNotSentTo($this->hrAdmin, ApprovalNeeded::class);
});

test('approving a non-final step notifies the requester and the next step\'s approver', function () {
    $request = TestRequest::factory()->create(['requester_id' => $this->requester->id]);
    $instance = $this->workflow->submit($this->chain, $request, $this->requester);

    // Fake only from here — submit() above already (correctly) notified
    // the supervisor once; this test is about what approve() itself
    // triggers, not re-asserting submit()'s own behaviour.
    Notification::fake();

    $this->workflow->approve($instance, $this->supervisor);

    Notification::assertSentTo($this->requester, ApprovalDecisionMade::class);
    Notification::assertSentTo($this->hrAdmin, ApprovalNeeded::class);
    Notification::assertNotSentTo($this->supervisor, ApprovalNeeded::class);
});

test('the final approval notifies only the requester, with no further approval-needed notice', function () {
    Notification::fake();

    $request = TestRequest::factory()->create(['requester_id' => $this->requester->id]);
    $instance = $this->workflow->submit($this->chain, $request, $this->requester);
    $this->workflow->approve($instance, $this->supervisor);

    Notification::fake();

    $this->workflow->approve($instance->fresh(), $this->hrAdmin);

    Notification::assertSentTo($this->requester, ApprovalDecisionMade::class);
    Notification::assertNothingSentTo($this->supervisor);
    Notification::assertNothingSentTo($this->hrAdmin);
});

test('rejecting notifies only the requester', function () {
    Notification::fake();

    $request = TestRequest::factory()->create(['requester_id' => $this->requester->id]);
    $instance = $this->workflow->submit($this->chain, $request, $this->requester);

    Notification::fake();

    $this->workflow->reject($instance, $this->supervisor, 'Not approved');

    Notification::assertSentTo($this->requester, ApprovalDecisionMade::class);
    Notification::assertNothingSentTo($this->hrAdmin);
});

test('a real approval-needed database notification is actually persisted for the approver', function () {
    $request = TestRequest::factory()->create(['requester_id' => $this->requester->id]);
    $this->workflow->submit($this->chain, $request, $this->requester);

    $notification = $this->supervisor->fresh()->notifications()->sole();

    expect($notification->type)->toBe(ApprovalNeeded::class)
        ->and($notification->data['notification_type'])->toBe('approval_needed')
        ->and($notification->read_at)->toBeNull();
});
