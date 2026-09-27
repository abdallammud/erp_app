<?php

namespace App\Support\Reporting;

use App\Exports\GenericExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The shared export framework every module's "Reports" screen is built
 * on — see docs/build/00-build-plan.md Step 0.10. A screen builds one
 * ReportDataset from whatever query it already has and gets all three
 * formats for free; nothing here is specific to any one module.
 *
 * Loads the full dataset into memory (via GenericExport's FromArray and
 * this class's own array-building for PDF) — fine for the row counts a
 * single tenant's reports realistically have in Phase 0-2, but revisit
 * with chunked/streamed export (Maatwebsite\Excel's FromQuery +
 * WithChunkReading) if a future module's dataset grows large enough
 * that this becomes a real memory concern.
 */
class ReportExporter
{
    public function toExcel(ReportDataset $dataset, string $filename): BinaryFileResponse
    {
        return Excel::download(new GenericExport($dataset), $this->withExtension($filename, 'xlsx'), ExcelWriter::XLSX);
    }

    public function toCsv(ReportDataset $dataset, string $filename): BinaryFileResponse
    {
        return Excel::download(new GenericExport($dataset), $this->withExtension($filename, 'csv'), ExcelWriter::CSV);
    }

    public function toPdf(ReportDataset $dataset, string $filename): StreamedResponse
    {
        // Deliberately not Pdf::...->download(): that returns a plain
        // Illuminate\Http\Response with the content already embedded,
        // which Livewire's file-download support doesn't recognize
        // (it only auto-triggers a browser download for a
        // StreamedResponse or BinaryFileResponse — see
        // Livewire\Features\SupportFileDownloads). Building the
        // response manually keeps all three formats equally
        // downloadable from a Livewire action method.
        $bytes = Pdf::loadView('exports.generic-table', ['dataset' => $dataset])
            ->setPaper('a4', 'landscape')
            ->output();

        return response()->streamDownload(
            fn () => print ($bytes),
            $this->withExtension($filename, 'pdf'),
            ['Content-Type' => 'application/pdf'],
        );
    }

    private function withExtension(string $filename, string $extension): string
    {
        return Str::finish(Str::beforeLast($filename, '.'.$extension), '.'.$extension);
    }
}
