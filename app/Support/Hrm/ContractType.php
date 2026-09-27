<?php

namespace App\Support\Hrm;

/**
 * See docs/04-module-hrm.md §B's contract lifecycle list.
 */
enum ContractType: string
{
    case FixedTerm = 'fixed_term';
    case ProjectBased = 'project_based';
    case Consultant = 'consultant';
    case Intern = 'intern';
}
