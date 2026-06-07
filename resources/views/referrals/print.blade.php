<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Referral Form — {{ $patient->full_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: A5 portrait;
            margin: 0;
        }

        body {
            font-family: 'Noto Sans', sans-serif;
            font-size: 9pt;
            color: #1e293b;
            background: #fff;
            width: 148mm;
            min-height: 210mm;
            margin: 0 auto;
        }

        .page {
            width: 148mm;
            min-height: 210mm;
            padding: 6mm 8mm 6mm;
            display: flex;
            flex-direction: column;
            border: 1px solid #cbd5e1;
        }

        /* ── Header ── */
        .header {
            margin-bottom: 3mm;
        }

        .header-banner {
            width: 100%;
            background: linear-gradient(135deg, #0a1f6e 0%, #1e40af 50%, #2563eb 100%);
            display: flex;
            align-items: center;
            gap: 4mm;
            padding: 3mm 5mm;
            min-height: 18mm;
            border-bottom: 2px solid #1e40af;
        }

        .header-banner img {
            height: 14mm;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
            background: white;
            border-radius: 4px;
            padding: 1px;
        }

        .header-banner-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .clinic-name {
            font-size: 20pt;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .clinic-sub {
            font-size: 8pt;
            font-weight: 600;
            color: #bfdbfe;
            letter-spacing: 5px;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .header-contact {
            margin-left: auto;
            text-align: right;
            font-size: 6.5pt;
            color: #bfdbfe;
            line-height: 1.8;
        }

        /* ── Patient fields ── */
        .patient-fields {
            display: flex;
            flex-direction: column;
            gap: 2mm;
            margin-bottom: 3mm;
        }

        .field-row {
            display: flex;
            align-items: flex-end;
            gap: 2mm;
            font-size: 8.5pt;
        }

        .field-label {
            font-weight: 600;
            white-space: nowrap;
            color: #334155;
        }

        .field-line {
            flex: 1;
            border-bottom: 1px solid #334155;
            min-height: 4mm;
        }

        /* ── Section title ── */
        .section-title {
            text-align: center;
            font-size: 12pt;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin: 2mm 0;
            border-top: 1.5px solid #334155;
            border-bottom: 1.5px solid #334155;
            padding: 1.5mm 0;
        }

        /* ── Checkbox rows ── */
        .section-block {
            margin: 2mm 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 4mm;
            margin-bottom: 1.5mm;
        }

        .section-badge {
            width: 6mm;
            height: 6mm;
            border: 1.5px solid #334155;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            font-weight: 700;
            flex-shrink: 0;
        }

        .checkbox-list {
            padding-left: 10mm;
            display: flex;
            flex-direction: column;
            gap: 1mm;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 2mm;
            font-size: 8pt;
        }

        .cb {
            width: 10px;
            height: 10px;
            border: 1.5px solid #475569;
            display: inline-block;
            flex-shrink: 0;
        }

        .tooth-line {
            display: inline-block;
            border-bottom: 1px solid #334155;
            width: 12mm;
            margin-left: 1mm;
            vertical-align: bottom;
        }

        /* ── Two-column grid (Panoramic / Cephalometric) ── */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 1.5px solid #334155;
            margin: 2mm 0;
        }

        .col-header {
            text-align: center;
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 1.5mm 0;
            border-bottom: 1.5px solid #334155;
        }

        .col-header:first-child {
            border-right: 1.5px solid #334155;
        }

        .col-body {
            padding: 2mm 3mm;
            display: flex;
            flex-direction: column;
            gap: 1.5mm;
        }

        .col-body:first-child {
            border-right: 1.5px solid #334155;
        }

        /* ── Others field ── */
        .others-line {
            display: inline-block;
            border-bottom: 1px solid #334155;
            width: 18mm;
            vertical-align: bottom;
        }

        /* ── Referred by ── */
        .referred-section {
            margin-top: 3mm;
            display: flex;
            flex-direction: column;
            gap: 2mm;
            font-size: 8pt;
        }

        .referred-row {
            display: flex;
            align-items: flex-end;
            gap: 2mm;
        }

        /* ── Print button ── */
        .print-btn {
            position: fixed;
            top: 8px;
            right: 8px;
            padding: 6px 14px;
            background: #1e40af;
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
            .page { border: none; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨 Print</button>

<div class="page">

    {{-- Header --}}
    @php
        $address = trim(($clinic->address ?? '') . ($clinic->city ? ', ' . $clinic->city : ''));
        $address = $address ?: '1 Don Juan Estevez St. Guevara Subdivision, Legazpi City';
        $phone   = $clinic->phone ?: '(052) 7421192  |  09175488934  |  09186331795';
        $email   = $clinic->email ?: 'customersupport@gonzalesdentalclinic.com';
    @endphp
    <div class="header">
        <div class="header-banner">
            <img src="{{ \App\Models\SiteSetting::instance()->logoUrl() }}" alt="{{ $clinic->clinic_name }}">
            <div class="header-banner-text">
                <span class="clinic-name">Gonzales</span>
                <span class="clinic-sub">Dental Clinic</span>
                <span style="font-size:6.5pt; color:#93c5fd; letter-spacing:1px; margin-top:1.5mm; font-style:italic;">Since 2005</span>
            </div>
            <div class="header-contact">
                {{ $address }}<br>
                {{ $phone }}<br>
                &#9679; {{ $email }}
            </div>
        </div>
    </div>


    {{-- Patient Fields --}}
    <div class="patient-fields">
        <div class="field-row">
            <span class="field-label">Patient's Name:</span>
            <span class="field-line" style="padding-bottom:1px; font-size:8.5pt; font-weight:600;">
                {{ $patient->full_name }}
            </span>
        </div>
        <div class="field-row">
            <span class="field-label">Birthday:</span>
            <span class="field-line" style="padding-bottom:1px; font-size:8.5pt;">
                {{ $patient->date_of_birth?->format('F d, Y') ?? '' }}
            </span>
        </div>
    </div>

    {{-- Section Title --}}
    <div class="section-title">Request For</div>

    {{-- Section A --}}
    <div class="section-block">
        <div class="section-header">
            <div class="section-badge">A</div>
        </div>
        <div class="checkbox-list">
            <div class="checkbox-item">
                <span class="cb"></span>
                Periapical Radiograph &nbsp;( Tooth # <span class="tooth-line"></span> )
            </div>
            <div class="checkbox-item">
                <span class="cb"></span>
                Photograph 3R
            </div>
            <div class="checkbox-item">
                <span class="cb"></span>
                Diagnostic Cast
            </div>
        </div>
    </div>

    {{-- Section B --}}
    <div class="section-block" style="margin-top:1mm;">
        <div class="section-header">
            <div class="section-badge">B</div>
            <div class="checkbox-item">
                <span class="cb"></span>
                Complete Ortho Diagnostic Package
            </div>
        </div>
    </div>

    {{-- Two-column grid --}}
    <div class="two-col">
        <div class="col-header">Panoramic</div>
        <div class="col-header">Cephalometric</div>

        <div class="col-body">
            <div class="checkbox-item"><span class="cb"></span> Standard or Jaw</div>
            <div class="checkbox-item"><span class="cb"></span> Segment</div>
            <div class="checkbox-item"><span class="cb"></span> Sinus</div>
            <div class="checkbox-item"><span class="cb"></span> Bite Wing</div>
            <div class="checkbox-item"><span class="cb"></span> TMJ – Open &amp; Close Mouth</div>
            <div class="checkbox-item"><span class="cb"></span> Orthogonal View</div>
        </div>

        <div class="col-body">
            <div class="checkbox-item"><span class="cb"></span> Lateral View</div>
            <div class="checkbox-item"><span class="cb"></span> Full Lateral View</div>
            <div class="checkbox-item"><span class="cb"></span> SMV View</div>
            <div class="checkbox-item"><span class="cb"></span> PA Position</div>
            <div class="checkbox-item"><span class="cb"></span> Carpus</div>
            <div class="checkbox-item"><span class="cb"></span> Others <span class="others-line"></span></div>
        </div>
    </div>

    {{-- Referred by --}}
    <div class="referred-section">
        <div class="referred-row">
            <span style="font-weight:600;">Referred by</span>
            <span class="field-line"></span>
        </div>
        <div class="referred-row">
            <span style="font-weight:600;">DR</span>
            <span class="field-line" style="flex:2;"></span>
            <span style="font-weight:600; margin-left:4mm;">Date</span>
            <span class="field-line" style="flex:1;"></span>
        </div>
    </div>

    {{-- Location Map --}}
    <div style="margin-top:3mm;">
        <div style="font-size:8pt; font-weight:700; letter-spacing:2px; text-transform:uppercase; margin-bottom:1.5mm;">Our Location</div>
        <svg viewBox="0 0 520 165" xmlns="http://www.w3.org/2000/svg" style="width:100%; display:block; border:1px solid #6b7280;">

            <!-- Background -->
            <rect width="520" height="165" fill="#d1d5db"/>

            <!-- === ROADS (white) === -->

            <!-- Marquez St. — full-height vertical, far left -->
            <rect x="18" y="0" width="20" height="165" fill="white" stroke="#6b7280" stroke-width="0.7"/>

            <!-- Don Juan Estevez St. LEFT — upper horizontal, from Marquez to Guevara -->
            <rect x="18" y="38" width="222" height="20" fill="white" stroke="#6b7280" stroke-width="0.7"/>

            <!-- Guevara Subdivision — full-height vertical, middle -->
            <rect x="240" y="0" width="20" height="165" fill="white" stroke="#6b7280" stroke-width="0.7"/>

            <!-- Don Juan Estevez St. RIGHT — lower horizontal (step down), from Guevara to Rizal -->
            <rect x="240" y="95" width="160" height="20" fill="white" stroke="#6b7280" stroke-width="0.7"/>

            <!-- Rizal St. — full-height vertical, right -->
            <rect x="382" y="0" width="20" height="165" fill="white" stroke="#6b7280" stroke-width="0.7"/>

            <!-- === BUILDINGS === -->

            <!-- Gonzales Dental Clinic — blue box, below the left road, right of Marquez -->
            <rect x="42" y="60" width="120" height="90" fill="#5b9bd5" rx="2"/>
            <text x="102" y="97"  text-anchor="middle" fill="white" font-size="11" font-weight="bold" font-family="Arial,sans-serif">GONZALES</text>
            <text x="102" y="113" text-anchor="middle" fill="white" font-size="9"  font-family="Arial,sans-serif">DENTAL</text>
            <text x="102" y="127" text-anchor="middle" fill="white" font-size="9"  font-family="Arial,sans-serif">CLINIC</text>

            <!-- Jennifers Karayan Hotel — small box near Guevara, below left road -->
            <rect x="166" y="62" width="72" height="55" fill="#e5e7eb" stroke="#6b7280" stroke-width="0.6" rx="1"/>
            <text transform="translate(202,78) rotate(-20)"  text-anchor="middle" fill="#374151" font-size="5.5" font-family="Arial,sans-serif">JENNIFERS</text>
            <text transform="translate(202,88) rotate(-20)"  text-anchor="middle" fill="#374151" font-size="5.5" font-family="Arial,sans-serif">KARAYAN</text>
            <text transform="translate(202,98) rotate(-20)"  text-anchor="middle" fill="#374151" font-size="5.5" font-family="Arial,sans-serif">HOTEL</text>

            <!-- Estevez Hospital — right of Guevara, above the lower road -->
            <rect x="264" y="10" width="90" height="60" fill="#e5e7eb" stroke="#6b7280" stroke-width="0.7" rx="1"/>
            <text x="309" y="37" text-anchor="middle" fill="#111827" font-size="8" font-weight="bold" font-family="Arial,sans-serif">ESTEVEZ</text>
            <text x="309" y="50" text-anchor="middle" fill="#111827" font-size="8" font-family="Arial,sans-serif">HOSPITAL</text>

            <!-- St. Agnes Academy — 4 square blocks right of Rizal St. -->
            <rect x="406" y="10"  width="26" height="26" fill="#e5e7eb" stroke="#6b7280" stroke-width="0.7"/>
            <rect x="406" y="42"  width="26" height="26" fill="#e5e7eb" stroke="#6b7280" stroke-width="0.7"/>
            <rect x="406" y="74"  width="26" height="26" fill="#e5e7eb" stroke="#6b7280" stroke-width="0.7"/>
            <rect x="406" y="106" width="26" height="26" fill="#e5e7eb" stroke="#6b7280" stroke-width="0.7"/>

            <!-- === STREET LABELS === -->

            <!-- Marquez St. (rotated upward) -->
            <text transform="translate(28,120) rotate(-90)" text-anchor="middle" fill="#111827" font-size="7.5" font-weight="bold" font-family="Arial,sans-serif">MARQUEZ ST.</text>

            <!-- Don Juan Estevez St. LEFT label (above the road) -->
            <text x="130" y="35" text-anchor="middle" fill="#111827" font-size="6.5" font-weight="bold" font-family="Arial,sans-serif">DON JUAN ESTEVEZ ST.</text>

            <!-- Guevara Subdivision (rotated) -->
            <text transform="translate(250,110) rotate(-90)" text-anchor="middle" fill="#111827" font-size="6.5" font-weight="bold" font-family="Arial,sans-serif">GUEVARA SUBDIVISION</text>

            <!-- Don Juan Estevez St. RIGHT label (above the lower road) -->
            <text x="320" y="92" text-anchor="middle" fill="#111827" font-size="6.5" font-weight="bold" font-family="Arial,sans-serif">DON JUAN ESTEVEZ ST.</text>

            <!-- Rizal St. label + directional text (rotated) -->
            <text transform="translate(392,90) rotate(-90)" text-anchor="middle" fill="#111827" font-size="7" font-weight="bold" font-family="Arial,sans-serif">RIZAL ST.</text>
            <text transform="translate(392,30) rotate(-90)" text-anchor="middle" fill="#111827" font-size="5" font-family="Arial,sans-serif">TO DARAGA</text>
            <text transform="translate(392,148) rotate(-90)" text-anchor="middle" fill="#111827" font-size="5" font-family="Arial,sans-serif">TO LEGAZPI</text>

            <!-- Direction arrows on Rizal St. -->
            <text x="392" y="14"  text-anchor="middle" fill="#111827" font-size="13" font-family="Arial,sans-serif">↑</text>
            <text x="392" y="160" text-anchor="middle" fill="#111827" font-size="13" font-family="Arial,sans-serif">↓</text>

            <!-- St. Agnes Academy label (rotated, right edge) -->
            <text transform="translate(448,90) rotate(-90)" text-anchor="middle" fill="#111827" font-size="7" font-weight="bold" font-family="Arial,sans-serif">ST. AGNES ACADEMY</text>

        </svg>
    </div>

</div>

<script>
    window.addEventListener('load', function () {
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.print();
        }
    });
</script>

</body>
</html>
