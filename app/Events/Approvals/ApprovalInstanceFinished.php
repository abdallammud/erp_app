<?php

namespace App\Events\Approvals;

use App\Models\ApprovalInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired once when an ApprovalInstance reaches a terminal state (approved
 * after its last step, or rejected at any step). Check
 * `$instance->status` for which. No listener yet — see
 * ApprovalStepActedOn's docblock and docs/build/00-build-plan.md Step 0.7.
 */
class ApprovalInstanceFinished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ApprovalInstance $instance,
    ) {}
}
