<?php

namespace App\Listeners\Approvals;

use App\Events\Approvals\ApprovalStepActedOn;
use App\Notifications\Approvals\ApprovalDecisionMade;

/**
 * Tells the requester about every step decision on their own request,
 * whether it's a mid-chain approval, the final approval, or a
 * rejection — see App\Notifications\Approvals\ApprovalDecisionMade.
 *
 * Not ShouldQueue — see App\Notifications\Approvals\ApprovalNeeded's
 * docblock.
 */
class NotifyRequesterOfDecision
{
    public function handle(ApprovalStepActedOn $event): void
    {
        $event->instance->requester?->notify(
            new ApprovalDecisionMade($event->instance, $event->step)
        );
    }
}
