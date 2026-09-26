<?php

namespace App\Support\Notifications;

/**
 * Platform-wide catalog of notification types, stored in each
 * notification's `toDatabase()` payload under the `notification_type`
 * key so the in-app inbox can filter/label consistently — see
 * docs/build/00-build-plan.md Step 0.7.
 *
 * Only ApprovalNeeded and ApprovalDecision have real triggers today
 * (App\Listeners\Approvals\*). DocumentExpiring, ContractExpiring, and
 * BudgetThreshold are seeded here as documented extension points for
 * modules that don't exist yet (Documents, Contracts, Budgets —
 * Phase 1+), matching the build plan's "even if only a couple have
 * real triggers yet."
 */
enum NotificationType: string
{
    case ApprovalNeeded = 'approval_needed';
    case ApprovalDecision = 'approval_decision';
    case DocumentExpiring = 'document_expiring';
    case ContractExpiring = 'contract_expiring';
    case BudgetThreshold = 'budget_threshold';
}
