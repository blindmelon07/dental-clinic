<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rx {{ $prescription->prescription_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        /* ── A5 page setup ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: A5 portrait;
            margin: 0;
        }

        body {
            font-family: 'Noto Sans', sans-serif;
            font-size: 10pt;
            color: #1e293b;
            background: #fff;
            width: 148mm;
            min-height: 210mm;
            margin: 0 auto;
        }

        /* ── Layout ── */
        .page {
            width: 148mm;
            min-height: 210mm;
            padding: 10mm 12mm 10mm;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 6mm;
            border-bottom: 2px solid #0e7490;
            margin-bottom: 5mm;
        }

        .header img {
            height: 18mm;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }

        .header-info {
            flex: 1;
            text-align: right;
        }

        .header-info .clinic-name {
            font-size: 11pt;
            font-weight: 700;
            color: #0e7490;
            line-height: 1.3;
        }

        .header-info .clinic-sub {
            font-size: 8pt;
            color: #64748b;
            margin-top: 1mm;
        }

        /* ── Rx number + date row ── */
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 4mm;
            font-size: 8.5pt;
            color: #475569;
        }

        .rx-number {
            font-weight: 600;
            color: #0e7490;
            font-size: 9pt;
        }

        /* ── Patient / Doctor section ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2mm 6mm;
            margin-bottom: 5mm;
            padding: 3mm 4mm;
            background: #f8fafc;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            font-size: 7.5pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #94a3b8;
        }

        .info-value {
            font-size: 9.5pt;
            font-weight: 500;
            color: #1e293b;
            margin-top: 0.5mm;
        }

        /* ── Diagnosis ── */
        .diagnosis-box {
            margin-bottom: 5mm;
        }

        .section-label {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 1.5mm;
        }

        .diagnosis-text {
            font-size: 9.5pt;
            color: #334155;
            font-style: italic;
        }

        /* ── Rx symbol + medications ── */
        .rx-symbol {
            font-size: 28pt;
            font-weight: 700;
            color: #0e7490;
            line-height: 1;
            margin-bottom: 2mm;
            font-style: italic;
        }

        .medications {
            list-style: none;
            margin-bottom: 5mm;
        }

        .medication-item {
            display: flex;
            gap: 3mm;
            padding: 3mm 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .medication-item:last-child {
            border-bottom: none;
        }

        .med-number {
            font-size: 10pt;
            font-weight: 700;
            color: #0e7490;
            flex-shrink: 0;
            width: 5mm;
            padding-top: 0.5mm;
        }

        .med-body {
            flex: 1;
        }

        .med-name {
            font-size: 10.5pt;
            font-weight: 700;
            color: #0f172a;
        }

        .med-strength {
            font-size: 9pt;
            color: #475569;
            margin-left: 1.5mm;
        }

        .med-form-badge {
            display: inline-block;
            font-size: 7pt;
            font-weight: 600;
            text-transform: uppercase;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 3px;
            padding: 0.5mm 1.5mm;
            margin-left: 1.5mm;
            vertical-align: middle;
        }

        .med-sig {
            margin-top: 1mm;
            font-size: 9pt;
            color: #334155;
        }

        .med-sig span {
            font-style: italic;
        }

        .med-instructions {
            margin-top: 1mm;
            font-size: 8.5pt;
            color: #64748b;
        }

        /* ── Notes ── */
        .notes-box {
            margin-bottom: 5mm;
            padding: 2.5mm 3mm;
            border-left: 2.5px solid #0e7490;
            background: #f0f9ff;
            border-radius: 0 4px 4px 0;
        }

        .notes-text {
            font-size: 8.5pt;
            color: #334155;
        }

        /* ── Signature area ── */
        .signature-area {
            margin-top: auto;
            padding-top: 4mm;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
        }

        .signature-block {
            text-align: center;
            width: 55mm;
        }

        .signature-line {
            border-top: 1.5px solid #334155;
            margin-bottom: 1.5mm;
        }

        .signature-name {
            font-size: 9.5pt;
            font-weight: 700;
            color: #0f172a;
        }

        .signature-sub {
            font-size: 7.5pt;
            color: #64748b;
        }

        /* ── Footer strip ── */
        .page-footer {
            margin-top: 4mm;
            padding-top: 2mm;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 7pt;
            color: #94a3b8;
        }

        /* ── Print-only ── */
        .print-btn {
            position: fixed;
            top: 8px;
            right: 8px;
            padding: 6px 14px;
            background: #0e7490;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-family: inherit;
            cursor: pointer;
            z-index: 999;
        }

        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨 Print</button>

<div class="page">

    {{-- Header --}}
    <div class="header">
        <img src="{{ \App\Models\SiteSetting::instance()->logoUrl() }}" alt="Gonzales Dental Clinic">
        <div class="header-info">
            <div class="clinic-sub">Dental Department</div>
            @if($clinic)
                <div class="clinic-sub" style="margin-top:1mm">{{ $clinic->address }}, {{ $clinic->city }}</div>
                <div class="clinic-sub">{{ $clinic->phone }}</div>
            @endif
        </div>
    </div>

    {{-- Rx number & date --}}
    <div class="meta-row">
        <span class="rx-number">{{ $prescription->prescription_number }}</span>
        <span>Date: <strong>{{ $prescription->prescribed_date->format('F d, Y') }}</strong></span>
    </div>

    {{-- Patient & Doctor --}}
    <div class="info-grid">
        <div>
            <div class="info-label">Patient</div>
            <div class="info-value">{{ $prescription->patient->full_name }}</div>
        </div>
        <div>
            <div class="info-label">Prescribing Dentist</div>
            <div class="info-value">Dr. {{ $prescription->dentist->user->name }}</div>
        </div>
        @if($prescription->dentist->license_number)
        <div>
            <div class="info-label">License No.</div>
            <div class="info-value">{{ $prescription->dentist->license_number }}</div>
        </div>
        @endif
        @if($prescription->appointment)
        <div>
            <div class="info-label">Appointment</div>
            <div class="info-value">{{ $prescription->appointment->appointment_number }}</div>
        </div>
        @endif
    </div>

    {{-- Diagnosis --}}
    @if($prescription->diagnosis)
    <div class="diagnosis-box">
        <div class="section-label">Diagnosis</div>
        <div class="diagnosis-text">{{ $prescription->diagnosis }}</div>
    </div>
    @endif

    {{-- Medications --}}
    <div class="rx-symbol">℞</div>

    <ul class="medications">
        @foreach($prescription->medications as $i => $med)
        <li class="medication-item">
            <div class="med-number">{{ $i + 1 }}.</div>
            <div class="med-body">
                <div>
                    <span class="med-name">{{ $med['name'] ?? '' }}</span>
                    @if(!empty($med['strength']))
                        <span class="med-strength">{{ $med['strength'] }}</span>
                    @endif
                    @if(!empty($med['form']))
                        <span class="med-form-badge">{{ ucfirst($med['form']) }}</span>
                    @endif
                </div>
                <div class="med-sig">
                    <span>Sig:</span>
                    {{ implode(', ', array_filter([
                        $med['dose'] ?? null,
                        $med['frequency'] ?? null,
                        isset($med['duration']) && $med['duration'] ? 'for ' . $med['duration'] : null,
                    ])) ?: '—' }}
                </div>
                @if(!empty($med['instructions']))
                    <div class="med-instructions">★ {{ $med['instructions'] }}</div>
                @endif
            </div>
        </li>
        @endforeach
    </ul>

    {{-- Notes --}}
    @if($prescription->notes)
    <div class="notes-box">
        <div class="section-label" style="margin-bottom:1mm">Notes</div>
        <div class="notes-text">{{ $prescription->notes }}</div>
    </div>
    @endif

    {{-- Signature --}}
    <div class="signature-area">
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-name">Dr. {{ $prescription->dentist->user->name }}</div>
            <div class="signature-sub">{{ $prescription->dentist->specialization ?? 'Dentist' }}</div>
            <div class="signature-sub">Lic. No. {{ $prescription->dentist->license_number }}</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="page-footer">
        <span>This prescription is valid for 7 days from the date issued.</span>
        <span>Printed: {{ now()->format('M d, Y H:i') }}</span>
    </div>

</div>

</body>
</html>
