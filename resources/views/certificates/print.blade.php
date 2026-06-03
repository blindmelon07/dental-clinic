<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $certificate->typeLabel() }} — {{ $certificate->patient->full_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Noto+Serif:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { size: A4 portrait; margin: 0; }

        body {
            font-family: 'Noto Sans', sans-serif;
            font-size: 11pt;
            color: #1e293b;
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 18mm 15mm;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 8mm;
            border-bottom: 3px solid #0e7490;
            margin-bottom: 8mm;
        }

        .header img {
            height: 20mm;
            width: auto;
            object-fit: contain;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .header-info { flex: 1; }

        .clinic-name {
            font-family: 'Noto Serif', serif;
            font-size: 14pt;
            font-weight: 700;
            color: #0e7490;
            letter-spacing: 0.3px;
        }

        .clinic-sub {
            font-size: 9pt;
            color: #475569;
            margin-top: 2px;
            font-style: italic;
        }

        .clinic-contact {
            font-size: 9pt;
            color: #64748b;
            margin-top: 3px;
        }

        /* ── Document Title ── */
        .doc-title {
            text-align: center;
            margin: 6mm 0 8mm;
        }

        .doc-title h1 {
            font-family: 'Noto Serif', serif;
            font-size: 17pt;
            font-weight: 700;
            color: #0e7490;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .doc-title .cert-number {
            font-size: 9pt;
            color: #94a3b8;
            margin-top: 3px;
            letter-spacing: 1px;
        }

        /* ── Body ── */
        .salutation {
            font-size: 11pt;
            margin-bottom: 5mm;
            color: #334155;
        }

        .body-text {
            font-size: 11pt;
            line-height: 1.9;
            text-align: justify;
            text-indent: 12mm;
            color: #1e293b;
            margin-bottom: 4mm;
        }

        /* ── Details Box ── */
        .details-box {
            border-left: 4px solid #0e7490;
            background: #f0fdfa;
            padding: 5mm 7mm;
            margin: 5mm 0;
            border-radius: 0 6px 6px 0;
        }

        .details-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-box td {
            padding: 2px 0;
            font-size: 10.5pt;
            vertical-align: top;
        }

        .details-box td:first-child {
            font-weight: 600;
            color: #0e7490;
            width: 45mm;
            padding-right: 4mm;
        }

        /* ── Checklist (Medical Clearance) ── */
        .checklist {
            margin: 5mm 0;
        }

        .checklist-title {
            font-weight: 600;
            font-size: 10.5pt;
            margin-bottom: 3mm;
            color: #334155;
        }

        .checklist-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2mm 8mm;
        }

        .checklist-item {
            font-size: 10pt;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .check-box {
            width: 11px;
            height: 11px;
            border: 1.5px solid #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 8pt;
            color: #0e7490;
            font-weight: 700;
        }

        .physician-fields {
            margin-top: 6mm;
        }

        .field-line {
            display: flex;
            align-items: flex-end;
            gap: 4mm;
            margin-bottom: 4mm;
            font-size: 10pt;
        }

        .field-line .field-label {
            white-space: nowrap;
            color: #475569;
            font-weight: 500;
        }

        .field-line .field-blank {
            flex: 1;
            border-bottom: 1px solid #94a3b8;
            min-width: 40mm;
        }

        /* ── Closing ── */
        .closing-text {
            font-size: 11pt;
            line-height: 1.9;
            text-align: justify;
            text-indent: 12mm;
            color: #1e293b;
            margin-top: 4mm;
        }

        /* ── Signature Block ── */
        .signature-block {
            margin-top: auto;
            padding-top: 12mm;
            text-align: right;
        }

        .signature-line {
            display: inline-block;
            border-top: 1.5px solid #1e293b;
            min-width: 55mm;
            padding-top: 2px;
            font-weight: 600;
            font-size: 10.5pt;
            color: #1e293b;
            letter-spacing: 0.5px;
        }

        .signature-sub {
            font-size: 9pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 10mm;
            padding-top: 4mm;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8.5pt;
            color: #94a3b8;
        }

        /* ── Print Button (screen only) ── */
        .print-btn {
            position: fixed;
            top: 12px;
            right: 12px;
            background: #0e7490;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Noto Sans', sans-serif;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .print-btn:hover { background: #0891b2; }

        @media print {
            .print-btn { display: none; }
            body { width: 210mm; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨 Print</button>

<div class="page">

    {{-- ── Header ── --}}
    <div class="header">
        <img src="{{ $clinic->logoUrl() }}" alt="{{ $clinic->clinic_name }}">
        <div class="header-info">
            <div class="clinic-name">{{ $clinic->clinic_name }}</div>
            <div class="clinic-sub">{{ $dentist->dentist->specialization ?? 'General Dentistry' }}</div>
            <div class="clinic-contact">
                {{ $clinic->address }}, {{ $clinic->city }}<br>
                {{ $clinic->phone }}
            </div>
        </div>
    </div>

    {{-- ── Document Title ── --}}
    <div class="doc-title">
        <h1>{{ strtoupper($certificate->typeLabel()) }}</h1>
        <div class="cert-number">{{ $certificate->certificate_number }}</div>
    </div>

    {{-- ── Salutation ── --}}
    <p class="salutation">To Whom It May Concern:</p>

    @php
        $patient = $certificate->patient;
        $gender  = strtolower($patient->gender?->value ?? 'other');
        $pronoun = $gender === 'female' ? 'She' : ($gender === 'male' ? 'He' : 'They');
        $pronoun_poss = $gender === 'female' ? 'her' : ($gender === 'male' ? 'his' : 'their');
        $age = $patient->date_of_birth ? $patient->date_of_birth->age : '';
    @endphp

    {{-- ── CERTIFICATION content ── --}}
    @if($certificate->type === 'certification')

        <p class="body-text">
            This is to certify that <strong>{{ $patient->full_name }}</strong>,
            {{ $age }} years of age, was examined and treated by the undersigned on
            <strong>{{ $certificate->date_treated->format('F d, Y') }}</strong>.
        </p>

        @if($certificate->findings || $certificate->treatment_done)
        <div class="details-box">
            <table>
                @if($certificate->findings)
                <tr>
                    <td>Findings:</td>
                    <td>{{ $certificate->findings }}</td>
                </tr>
                @endif
                @if($certificate->treatment_done)
                <tr>
                    <td>Service Rendered:</td>
                    <td>{{ $certificate->treatment_done }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        @if($certificate->notes)
        <p class="body-text">{{ $certificate->notes }}</p>
        @endif

        <p class="closing-text">
            Issued upon request of the party concerned, this
            @php $d = $certificate->issue_date->day; $sfx = ($d % 100 >= 11 && $d % 100 <= 13) ? 'th' : (['st','nd','rd'][$d % 10 - 1] ?? 'th'); @endphp
            <strong>{{ $d }}{{ $sfx }} day of {{ $certificate->issue_date->format('F Y') }}</strong>,
            for whatever purpose it may serve.
        </p>

    {{-- ── DENTAL CLEARANCE content ── --}}
    @elseif($certificate->type === 'dental_clearance')

        <p class="body-text">
            This is to certify that <strong>{{ $patient->full_name }}</strong>,
            {{ $age }} years of age, was examined and treated by the undersigned on
            <strong>{{ $certificate->date_treated->format('F d, Y') }}</strong>.
            {{ $pronoun }} has undergone <strong>{{ $certificate->treatment_done }}</strong>.
        </p>

        @if($certificate->notes)
        <p class="body-text">{{ $certificate->notes }}</p>
        @else
        <p class="body-text">
            The patient was advised to take a rest and refrain from doing strenuous activities
            for at least 2 weeks and is now cleared to undergo any medical treatment after 2 weeks to 1 month.
        </p>
        @endif

        <p class="closing-text">
            Issued upon request of the party concerned, this
            @php $d = $certificate->issue_date->day; $sfx = ($d % 100 >= 11 && $d % 100 <= 13) ? 'th' : (['st','nd','rd'][$d % 10 - 1] ?? 'th'); @endphp
            <strong>{{ $d }}{{ $sfx }} day of {{ $certificate->issue_date->format('F Y') }}</strong>,
            for whatever purpose it may serve.
        </p>

    {{-- ── MEDICAL CLEARANCE content ── --}}
    @elseif($certificate->type === 'medical_clearance')

        <div class="details-box">
            <table>
                <tr>
                    <td>Date:</td>
                    <td>{{ $certificate->issue_date->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Patient:</td>
                    <td><strong>{{ $patient->full_name }}</strong></td>
                </tr>
                @if($certificate->birthdate)
                <tr>
                    <td>Birthdate:</td>
                    <td>{{ $certificate->birthdate->format('F d, Y') }}</td>
                </tr>
                @endif
            </table>
        </div>

        <p class="body-text" style="text-indent:0; margin-top:4mm;">
            Dear Dr.,
        </p>
        <p class="body-text">
            Our mutual patient, <strong>{{ $patient->full_name }}</strong>, is scheduled for dental treatment.
        </p>

        <div class="checklist">
            <p class="checklist-title">Treatment may include:</p>
            <div class="checklist-grid">
                <div class="checklist-item">
                    <span class="check-box">{{ $certificate->treatment_cleaning ? '✓' : ' ' }}</span>
                    Cleaning (simple or deep)
                </div>
                <div class="checklist-item">
                    <span class="check-box">{{ $certificate->treatment_root_canal ? '✓' : ' ' }}</span>
                    Root Canal Therapy
                </div>
                <div class="checklist-item">
                    <span class="check-box">{{ $certificate->treatment_xray ? '✓' : ' ' }}</span>
                    Radiographs / X-Ray
                </div>
                <div class="checklist-item">
                    <span class="check-box">{{ $certificate->treatment_fillings ? '✓' : ' ' }}</span>
                    Fillings, Crowns, Bridges
                </div>
                <div class="checklist-item">
                    <span class="check-box">{{ $certificate->treatment_anesthetic ? '✓' : ' ' }}</span>
                    Local Anesthetic (with epinephrine)
                </div>
                <div class="checklist-item">
                    <span class="check-box">{{ $certificate->treatment_other ? '✓' : ' ' }}</span>
                    Other: {{ $certificate->treatment_other ?? '___________' }}
                </div>
                <div class="checklist-item">
                    <span class="check-box">{{ $certificate->treatment_extraction ? '✓' : ' ' }}</span>
                    Extraction (multiple)
                </div>
            </div>
        </div>

        @if($certificate->medical_conditions)
        <p class="body-text" style="text-indent:0; margin-top:3mm;">
            The patient has indicated the following medical conditions:<br>
            <strong>{{ $certificate->medical_conditions }}</strong>
        </p>
        @endif

        <p class="body-text" style="text-indent:0; margin-top:3mm;">
            Please evaluate this patient's medical history and advise us of any special considerations that should be made.
        </p>

        <div class="physician-fields">
            <div class="field-line">
                <span class="field-label">Antibiotic prophylaxis:</span>
                <span class="field-blank"></span>
                <span class="field-label">Yes ___</span>
                <span class="field-blank"></span>
                <span class="field-label">No ___</span>
            </div>
            <div class="field-line">
                <span class="field-label">Interruption of anticoagulants:</span>
                <span class="field-blank"></span>
                <span class="field-label">Yes ___</span>
                <span class="field-blank"></span>
                <span class="field-label">No ___</span>
            </div>
            <div class="field-line">
                <span class="field-label">How long before/after treatment:</span>
                <span class="field-blank"></span>
            </div>
            <div class="field-line">
                <span class="field-label">Anesthetic restrictions:</span>
                <span class="field-blank"></span>
                <span class="field-label">Yes ___</span>
                <span class="field-blank"></span>
                <span class="field-label">No ___</span>
            </div>
            <div class="field-line">
                <span class="field-label">Is Epinephrine OK?</span>
                <span class="field-blank"></span>
                <span class="field-label">Yes ___</span>
                <span class="field-blank"></span>
                <span class="field-label">No ___</span>
            </div>
            <div class="field-line">
                <span class="field-label">Type of antibiotic allowed:</span>
                <span class="field-blank"></span>
            </div>
            <div class="field-line">
                <span class="field-label">Pain medication allowed:</span>
                <span class="field-blank"></span>
            </div>
            <div class="field-line">
                <span class="field-label">Additional comments:</span>
                <span class="field-blank"></span>
            </div>
        </div>

        <div style="margin-top: 8mm;">
            <div class="field-line">
                <span class="field-label">Physician's Name:</span>
                <span class="field-blank"></span>
            </div>
            <div class="field-line">
                <span class="field-label">Physician's Signature:</span>
                <span class="field-blank"></span>
            </div>
            <div class="field-line">
                <span class="field-label">Date:</span>
                <span class="field-blank"></span>
            </div>
        </div>

        <p style="margin-top: 5mm; font-size: 9.5pt; color: #475569; font-style: italic;">
            We appreciate your assistance in providing optimum care for this patient. Please have physician sign.
        </p>
    @endif

    {{-- ── Signature Block ── --}}
    <div class="signature-block">
        <div class="signature-line">{{ $dentist->name }}, D.M.D.</div>
        @if($certificate->issuedBy->dentist?->license_number)
        <div class="signature-sub">Licensed #: {{ $certificate->issuedBy->dentist->license_number }}</div>
        @endif
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        {{ $clinic->clinic_name }} · {{ $clinic->address }}, {{ $clinic->city }} · {{ $clinic->phone }}
    </div>

</div>

<script>
    window.addEventListener('load', function () {
        // Auto-print if ?print=1 in URL
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.print();
        }
    });
</script>

</body>
</html>
