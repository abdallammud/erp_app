<?php

namespace App\Support\Approvals;

/**
 * The lifecycle status of a whole ApprovalInstance — see
 * docs/03-roles-and-permissions.md's approval workflow engine and
 * docs/build/00-build-plan.md Step 0.6.
 */
enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
