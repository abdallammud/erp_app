<?php

namespace App\Support\Hrm;

enum DependentRelationship: string
{
    case Spouse = 'spouse';
    case Child = 'child';
    case Parent = 'parent';
    case Sibling = 'sibling';
    case Other = 'other';
}
