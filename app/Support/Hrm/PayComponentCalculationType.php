<?php

namespace App\Support\Hrm;

/**
 * Shared by AllowanceType and DeductionType — see
 * docs/04-module-hrm.md §C. `Fixed` = a flat amount per pay period;
 * `PercentageOfBase` = a percentage of the employee's contract salary
 * grade base.
 */
enum PayComponentCalculationType: string
{
    case Fixed = 'fixed';
    case PercentageOfBase = 'percentage_of_base';
}
