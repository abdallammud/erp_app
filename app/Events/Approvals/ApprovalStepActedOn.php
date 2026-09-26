<?php

namespace App\Events\Approvals;

use App\Models\ApprovalInstance;
use App\Models\ApprovalInstanceStep;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired every time someone approves or rejects a step — the requester
 * cares about this even if the whole instance isn't finished yet
 * ("Level 1 approved, waiting on Level 2" — see
 * docs/03-roles-and-permissions.md's status-tracking requirement).
 *
 * No listener yet — Step 0.7 (notifications) attaches one without
 * touching the approval engine itself. See docs/build/00-build-plan.md.
 */
class ApprovalStepActedOn
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ApprovalInstance $instance,
        public readonly ApprovalInstanceStep $step,
    ) {}
}
