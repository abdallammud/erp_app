<?php

namespace App\Support\Hrm;

/**
 * Stored explicitly rather than derived purely from dates — a contract
 * can be terminated early (status changes before end_date arrives), the
 * same reasoning already used for ApprovalInstance/ApprovalStepStatus.
 */
enum ContractStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Terminated = 'terminated';
}
