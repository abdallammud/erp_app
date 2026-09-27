<?php

namespace App\Support\Hrm;

/**
 * HR classification of a staff member — see docs/04-module-hrm.md §B
 * ("Staff categorization"). Distinct from ContractType: a contract's
 * legal type (fixed-term, project-based, consultant, intern) and an
 * employee's staff category overlap but aren't the same axis — "casual"
 * and "permanent" are categories with no matching contract type in the
 * source docs.
 */
enum StaffCategory: string
{
    case Permanent = 'permanent';
    case FixedTerm = 'fixed_term';
    case Consultant = 'consultant';
    case Intern = 'intern';
    case Casual = 'casual';
}
