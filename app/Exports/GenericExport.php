<?php

namespace App\Exports;

use App\Support\Reporting\ReportDataset;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * The one Export class every module's Reports screen shares — see
 * App\Support\Reporting\ReportExporter, the actual entry point. Never
 * subclassed per module; a ReportDataset is all that varies.
 */
class GenericExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private readonly ReportDataset $dataset,
    ) {}

    public function array(): array
    {
        return $this->dataset->rowsAsArrays();
    }

    public function headings(): array
    {
        return $this->dataset->headings();
    }

    public function title(): string
    {
        // Excel sheet titles are capped at 31 characters and can't
        // contain: \ / ? * [ ]
        return substr(preg_replace('/[\\\\\/?*\[\]]/', ' ', $this->dataset->title), 0, 31);
    }
}
