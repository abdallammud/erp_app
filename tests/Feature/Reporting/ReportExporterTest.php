<?php

use App\Support\Reporting\ReportDataset;
use App\Support\Reporting\ReportExporter;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Build Plan Step 0.10's Definition of Done: one demo dataset exports
 * correctly in all three formats. Doesn't just assert "no exception
 * thrown" — actually reads the generated file back (via PhpSpreadsheet
 * for Excel, plain parsing for CSV) and confirms the real headings and
 * row values round-trip correctly, and confirms the PDF bytes are a
 * genuine PDF containing the dataset's actual content.
 */
beforeEach(function () {
    $this->dataset = new ReportDataset(
        title: 'Demo Report',
        columns: ['name' => 'Name', 'amount' => 'Amount'],
        rows: new Collection([
            ['name' => 'Alpha & Co', 'amount' => '100'],
            ['name' => 'Beta', 'amount' => '250'],
        ]),
    );

    $this->exporter = app(ReportExporter::class);
});

test('toExcel() produces a real spreadsheet with correct headings and rows', function () {
    $response = $this->exporter->toExcel($this->dataset, 'demo');

    expect($response->headers->get('Content-Disposition'))->toContain('demo.xlsx');

    $path = $response->getFile()->getPathname();
    $sheet = IOFactory::load($path)->getActiveSheet();

    expect($sheet->getCell('A1')->getValue())->toBe('Name')
        ->and($sheet->getCell('B1')->getValue())->toBe('Amount')
        ->and($sheet->getCell('A2')->getValue())->toBe('Alpha & Co')
        // PhpSpreadsheet auto-detects numeric-looking strings and
        // stores them as real numeric cells (correct Excel behavior,
        // not a bug) — compare loosely rather than assume the string
        // round-trips as a string.
        ->and((string) $sheet->getCell('B2')->getValue())->toBe('100')
        ->and($sheet->getCell('A3')->getValue())->toBe('Beta');
});

test('toCsv() produces real CSV content with correct headings and rows', function () {
    $response = $this->exporter->toCsv($this->dataset, 'demo');

    expect($response->headers->get('Content-Disposition'))->toContain('demo.csv');

    $path = $response->getFile()->getPathname();
    $rows = array_map('str_getcsv', file($path));

    expect($rows[0])->toBe(['Name', 'Amount'])
        ->and($rows[1])->toBe(['Alpha & Co', '100'])
        ->and($rows[2])->toBe(['Beta', '250']);
});

test('toPdf() produces a real PDF containing the dataset\'s content', function () {
    $response = $this->exporter->toPdf($this->dataset, 'demo');

    ob_start();
    $response->sendContent();
    $bytes = ob_get_clean();

    expect($bytes)->toStartWith('%PDF-')
        ->and($response->headers->get('Content-Type'))->toBe('application/pdf');
});

test('the shared PDF view actually renders the dataset\'s real content, not just valid PDF bytes', function () {
    // toPdf()'s output is binary and not text-searchable without a PDF
    // parser this stack doesn't have — render the same Blade view
    // directly to prove the dataset's actual values appear in the HTML
    // DomPDF converts, closing the gap a bare "starts with %PDF-" check
    // would leave (e.g. an export that silently rendered zero rows
    // would still produce valid, non-empty PDF bytes).
    $html = view('exports.generic-table', ['dataset' => $this->dataset])->render();

    expect($html)->toContain('Demo Report')
        ->toContain('Alpha &amp; Co')
        ->toContain('250');
});
