<?php

namespace App\Livewire\AuditLog;

use App\Models\AuditLogEntry;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The real screen behind Build Plan Step 0.8's audit log — not just a
 * table and tests. Reads App\Models\AuditLogEntry, which is already
 * tenant-scoped (BelongsToTenant), so this component doesn't need to
 * think about tenancy at all: the query is automatically limited to the
 * current tenant.
 *
 * Gated by Permission::HrmOrgView for now — see docs/build/QUESTIONS.md
 * Q8 (no dedicated audit-log permission exists yet).
 */
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $event = '';

    #[Computed]
    public function entries()
    {
        return AuditLogEntry::query()
            ->with('causer')
            ->when($this->event !== '', fn ($query) => $query->where('event', $this->event))
            ->latest()
            ->paginate(20);
    }

    public function updatedEvent(): void
    {
        $this->resetPage();
    }

    /**
     * Renders a logged attribute value for the diff table — booleans and
     * null print as words, not the empty string Blade's `{{ }}` would
     * otherwise turn `false`/`null` into (indistinguishable from "no
     * value" in the UI, which would make a false→true change on e.g.
     * `is_active` look like nothing happened).
     */
    public function formatValue(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => '—',
            is_array($value) => json_encode($value),
            default => (string) $value,
        };
    }

    public function render()
    {
        return view('livewire.audit-log.index');
    }
}
