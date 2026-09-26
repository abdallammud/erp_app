<?php

namespace App\Listeners\Approvals;

use App\Events\Approvals\ApprovalInstanceSubmitted;
use App\Events\Approvals\ApprovalStepActedOn;
use App\Notifications\Approvals\ApprovalNeeded;
use App\Support\Approvals\ApprovalStatus;
use App\Support\Approvals\ApprovalWorkflow;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Notifies whoever is eligible to act on an instance's current step:
 * on first submission (step 1 has nobody yet), and again whenever a
 * step is approved and the instance moves on to a new step. Does
 * nothing on rejection or final approval — there's no new "current
 * step" to notify anyone about.
 *
 * Not ShouldQueue — see App\Notifications\Approvals\ApprovalNeeded's
 * docblock.
 */
class NotifyEligibleApprovers
{
    public function __construct(
        private readonly ApprovalWorkflow $workflow,
    ) {}

    public function handleSubmitted(ApprovalInstanceSubmitted $event): void
    {
        $this->notify($event->instance);
    }

    public function handleStepActedOn(ApprovalStepActedOn $event): void
    {
        if ($event->instance->status !== ApprovalStatus::Pending) {
            return;
        }

        $this->notify($event->instance);
    }

    private function notify($instance): void
    {
        NotificationFacade::send($this->workflow->eligibleApprovers($instance), new ApprovalNeeded($instance));
    }
}
