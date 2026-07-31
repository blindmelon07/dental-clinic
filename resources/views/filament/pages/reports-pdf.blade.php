<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { margin: 12mm 14mm 16mm; }

        body {
            font-family: 'Helvetica', 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1e293b;
        }

        /* ── Header ── */
        table.header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        table.header-table td { vertical-align: middle; padding: 0; }

        .logo-cell { width: 20mm; }

        .logo-cell img {
            height: 18mm;
            width: 18mm;
            object-fit: contain;
            border-radius: 50%;
        }

        .clinic-cell { padding-left: 4mm; }

        .clinic-name {
            font-size: 15pt;
            font-weight: bold;
            color: #0e7490;
        }

        .clinic-contact {
            font-size: 8pt;
            color: #64748b;
            margin-top: 1mm;
            line-height: 1.5;
        }

        .meta-cell {
            text-align: right;
            vertical-align: middle;
        }

        .meta-cell .report-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0e7490;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-cell .report-range {
            font-size: 9pt;
            color: #475569;
            margin-top: 1mm;
        }

        .header-rule {
            height: 3px;
            background: #0e7490;
            margin: 4mm 0 7mm;
        }

        /* ── Stat cards (Summary section) ── */
        table.stat-cards {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3mm 0;
            margin-bottom: 8mm;
        }

        table.stat-cards td.stat-card {
            width: 25%;
            background: #f0fdfa;
            border: 1px solid #cce9ec;
            border-radius: 4px;
            padding: 4mm 3mm;
            text-align: center;
            vertical-align: top;
        }

        .stat-value {
            font-size: 13pt;
            font-weight: bold;
            color: #0e7490;
        }

        .stat-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #64748b;
            margin-top: 1.5mm;
        }

        /* ── Detail sections ── */
        .section { margin-bottom: 7mm; }

        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0e7490;
            border-left: 3px solid #0e7490;
            padding: 0.5mm 0 0.5mm 2.5mm;
            margin-bottom: 2.5mm;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.data-table th {
            text-align: left;
            font-size: 8.5pt;
            font-weight: bold;
            color: #475569;
            background: #e6f7f9;
            padding: 2mm 2.5mm;
            border-bottom: 1px solid #b6e0e5;
        }

        table.data-table td {
            font-size: 9pt;
            padding: 1.8mm 2.5mm;
            border-bottom: 1px solid #e2e8f0;
        }

        table.data-table tr.row-even td { background: #f8fafc; }

        .empty-row td {
            color: #94a3b8;
            font-style: italic;
            text-align: center;
            padding: 3mm;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 6mm;
            padding-top: 3mm;
            border-top: 1px solid #cbd5e1;
            font-size: 7.5pt;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            @if ($logoDataUri)
                <td class="logo-cell"><img src="{{ $logoDataUri }}" alt="{{ $clinicName }}"></td>
            @endif
            <td class="clinic-cell">
                <div class="clinic-name">{{ $clinicName }}</div>
                <div class="clinic-contact">
                    @if ($clinicAddress || $clinicCity)
                        {{ trim(($clinicAddress ?? '') . (($clinicAddress && $clinicCity) ? ', ' : '') . ($clinicCity ?? '')) }}<br>
                    @endif
                    @if ($clinicPhone)
                        {{ $clinicPhone }}
                    @endif
                </div>
            </td>
            <td class="meta-cell">
                <div class="report-title">{{ $title }}</div>
                <div class="report-range">{{ $rangeLabel }}</div>
            </td>
        </tr>
    </table>

    <div class="header-rule"></div>

    @foreach ($sections as $section)
        @php
            $sectionTitle = $section[0];
            $headers = $section[1];
            $rows = $section[2];
            $currencyCols = $section[3] ?? [];
        @endphp

        @if ($sectionTitle === 'Summary')
            <table class="stat-cards">
                <tr>
                    @foreach ($rows as $row)
                        <td class="stat-card">
                            <div class="stat-value">{{ $row[1] }}</div>
                            <div class="stat-label">{{ $row[0] }}</div>
                        </td>
                    @endforeach
                </tr>
            </table>
        @else
            <div class="section">
                <div class="section-title">{{ $sectionTitle }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            @foreach ($headers as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr @class(['row-even' => $loop->even])>
                                @foreach ($row as $i => $cell)
                                    <td>{{ in_array($i, $currencyCols) ? '₱' . number_format((float) $cell, 2) : $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="{{ count($headers) }}">No data in this range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach

    <div class="footer">
        Generated by {{ $clinicName }} on {{ $generatedAt }}
    </div>

</body>
</html>
