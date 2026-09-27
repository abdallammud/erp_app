<?php

namespace App\Support\Reporting;

use Illuminate\Support\Collection;

/**
 * A format-agnostic tabular dataset — the one shape every export format
 * in App\Support\Reporting\ReportExporter consumes. Any future module's
 * "Reports" screen builds one of these from whatever Eloquent query it
 * has and gets Excel/CSV/PDF export for free — see
 * docs/build/00-build-plan.md Step 0.10.
 *
 * Deliberately decoupled from Eloquent: $rows are already-flattened,
 * already-formatted associative arrays (dates as strings, enums as
 * labels, etc.) keyed by column key — the exporter never has to know
 * anything about the model these came from.
 */
class ReportDataset
{
    /**
     * @param  array<string, string>  $columns  Ordered [key => label] map.
     * @param  Collection  $rows  Each row an associative array keyed like $columns.
     *
     * $rows is deliberately NOT typed with a generic parameter
     * (`Collection<int, array<string, mixed>>`) here: Laravel's
     * Collection has a non-covariant TValue, so a caller's array
     * shape — which differs by module, since every future Reports
     * screen builds its own — could never satisfy an invariant generic
     * constraint. Accepting exactly that variety is this class's job.
     */
    public function __construct(
        public readonly string $title,
        public readonly array $columns,
        public readonly Collection $rows,
    ) {}

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return array_values($this->columns);
    }

    /**
     * @return array<int, list<mixed>>
     */
    public function rowsAsArrays(): array
    {
        $keys = array_keys($this->columns);

        return $this->rows
            ->map(fn (array $row) => array_map(fn (string $key) => $row[$key] ?? '', $keys))
            ->all();
    }
}
