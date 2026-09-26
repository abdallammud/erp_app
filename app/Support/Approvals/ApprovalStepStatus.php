<?php

namespace App\Support\Approvals;

/**
 * The status of one step within an ApprovalInstance. `Skipped` is used
 * for steps after the one where a rejection happened — they never got a
 * chance to act, which is a different, visible thing from "still
 * pending."
 */
enum ApprovalStepStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Skipped = 'skipped';
}
