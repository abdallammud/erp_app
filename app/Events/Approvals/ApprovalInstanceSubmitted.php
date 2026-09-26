<?php

namespace App\Events\Approvals;

use App\Models\ApprovalInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired once, right after ApprovalWorkflow::submit() creates the
 * instance and its steps — so step 1's eligible approvers can be
 * notified even though nobody has acted on anything yet (that's what
 * ApprovalStepActedOn is for). See docs/build/00-build-plan.md Step 0.7.
 */
class ApprovalInstanceSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ApprovalInstance $instance,
    ) {}
}
