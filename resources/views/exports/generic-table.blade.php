{{--
    The one PDF layout every module's Reports screen shares — see
    App\Support\Reporting\ReportExporter. Takes a single $dataset
    (App\Support\Reporting\ReportDataset); never customized per module.
    DomPDF only, deliberately plain HTML/CSS — no Tailwind/Vite build
    step runs inside a PDF renderer.
--}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1a1a1a; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        p.meta { color: #666; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px 7px; text-align: left; }
        th { background-color: #f3f3f3; }
        tr:nth-child(even) { background-color: #fafafa; }
    </style>
</head>
<body>
    <h1>{{ $dataset->title }}</h1>
    <p class="meta">Generated {{ now()->toDayDateTimeString() }} &middot; {{ $dataset->rows->count() }} row{{ $dataset->rows->count() === 1 ? '' : 's' }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($dataset->headings() as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($dataset->rowsAsArrays() as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($dataset->columns) }}">No rows.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
