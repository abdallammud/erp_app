<?php

namespace App\Support\Hrm;

/**
 * See docs/04-module-hrm.md §C's deduction configuration list.
 */
enum DeductionCategory: string
{
    case Tax = 'tax';
    case Insurance = 'insurance';
    case Pension = 'pension';
    case Loan = 'loan';
    case Other = 'other';
}
