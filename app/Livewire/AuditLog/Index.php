<?php

namespace App\Livewire\AuditLog;

use App\Models\AuditLogEntry;
use App\Models\User;
use App\Support\Reporting\ReportDataset;
use App\Support\Reporting\ReportExporter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Every module's Reports screen will build one of these from
     * whatever query it already has — this is the first real one. Not
     * paginated: exporting means "everything matching the filter," a
     * different concern from what fits on one screen.
     */
    private function exportDataset(): ReportDataset
    {
        $entries = AuditLogEntry::query()
            ->with('causer')
            ->when($this->event !== '', fn ($query) => $query->where('event', $this->event))
            ->latest()
            ->get();

        $rows = $entries->map(fn (AuditLogEntry $entry): array => [
            'when' => $entry->created_at?->toDateTimeString() ?? '',
            'event' => $entry->event ?? '',
            'subject' => class_basename($entry->subject_type ?? 'Unknown').' #'.$entry->subject_id,
            'causer' => $entry->causer instanceof User ? $entry->causer->name : 'System',
            'changes' => $this->summarizeChanges($entry),
        ]);

        return new ReportDataset(
            title: 'Audit Log',
            columns: ['when' => 'When', 'event' => 'Event', 'subject' => 'Record', 'causer' => 'By', 'changes' => 'Changes'],
            rows: $rows,
        );
    }

    /**
     * A flattened, one-line version of the same old->new diff the
     * expandable table in the Blade view renders — without this, the
     * export would be materially less useful than the screen it's
     * exported from (who/what/when but not *what changed*, which is
     * the actual point of an audit trail).
     */
    private function summarizeChanges(AuditLogEntry $entry): string
    {
        $new = $entry->attribute_changes?->get('attributes', []) ?? [];
        $old = $entry->attribute_changes?->get('old', []) ?? [];
        $fields = collect(array_keys($new))->merge(array_keys($old))->unique();

        return $fields
            ->map(function (string $field) use ($old, $new): string {
                if (array_key_exists($field, $old) && array_key_exists($field, $new)) {
                    return "{$field}: {$this->formatValue($old[$field])} -> {$this->formatValue($new[$field])}";
                }

                return "{$field}: ".$this->formatValue($new[$field] ?? $old[$field] ?? null);
            })
            ->implode('; ');
    }

    public function exportExcel(): BinaryFileResponse
    {
        return app(ReportExporter::class)->toExcel($this->exportDataset(), 'audit-log');
    }

    public function exportCsv(): BinaryFileResponse
    {
        return app(ReportExporter::class)->toCsv($this->exportDataset(), 'audit-log');
    }

    public function exportPdf(): StreamedResponse
    {
        return app(ReportExporter::class)->toPdf($this->exportDataset(), 'audit-log');
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
