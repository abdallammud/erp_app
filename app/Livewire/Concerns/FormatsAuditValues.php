<?php

namespace App\Livewire\Concerns;

/**
 * Shared by every component rendering an AuditLogEntry's attribute
 * diff (App\Livewire\AuditLog\Index, App\Livewire\Employees\History) —
 * booleans and null print as words, not the empty string Blade's
 * `{{ }}` would otherwise turn `false`/`null` into (indistinguishable
 * from "no value," which would make a false->true change on something
 * like `is_active` look like nothing happened).
 */
trait FormatsAuditValues
{
    public function formatValue(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => '—',
            is_array($value) => json_encode($value),
            default => (string) $value,
        };
    }
}
