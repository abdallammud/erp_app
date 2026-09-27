<?php

namespace App\Support\Hrm;

/**
 * Feeds the "gender & diversity breakdown" HR analytics in
 * docs/04-module-hrm.md §B — a fixed, small enum (not free text) so
 * that later aggregation is consistent. Nullable on Employee: not
 * every record will have this filled in immediately.
 */
enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
    case PreferNotToSay = 'prefer_not_to_say';
}
