<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Referral Form — {{ $patient->full_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=EB+Garamond:wght@400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        *  { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 0; }
        html, body { margin: 0; padding: 0; background: #e5e5e5; }

        :root {
            --navy-0:  #000813;
            --navy-1:  #02122b;
            --navy-2:  #0b2545;
            --navy-3:  #15417c;
            --gold-d:  #6b4f2e;
            --gold:    #b08d55;
            --gold-l:  #e6d3a3;
            --blue:    #2f7fc4;
            --paper:   #f7f7f7;
            --ink:     #14181f;
        }

        body {
            font-family: 'EB Garamond', Georgia, serif;
            color: var(--ink);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            overflow: hidden;
            background: var(--paper);
        }

        /* absolute helpers */
        .a  { position: absolute; }
        .vc { transform: translateY(-50%); }

        /* ══════════════ HEADER ══════════════ */
        .hdr {
            position: absolute;
            top: 0; left: 0;
            width: 210mm; height: 48mm;
            overflow: hidden;
            background:
                radial-gradient(58% 120% at 26% 78%, rgba(21,65,124,.85) 0%, rgba(11,37,69,.35) 45%, rgba(0,8,19,0) 72%),
                linear-gradient(105deg, #04102a 0%, #061a3d 18%, #010b1e 46%, #000610 74%, #02132f 100%);
        }
        .hdr-slash {
            position: absolute;
            top: -12mm; left: -8mm;
            width: 34mm; height: 44mm;
            background: linear-gradient(128deg,
                rgba(47,127,196,0) 40%, rgba(86,163,228,.55) 44%, rgba(47,127,196,0) 47%,
                rgba(47,127,196,0) 55%, rgba(86,163,228,.32) 59%, rgba(47,127,196,0) 62%);
        }
        .hdr-bloom {
            position: absolute;
            left: -12mm; bottom: -20mm;
            width: 95mm; height: 44mm;
            background: radial-gradient(50% 100% at 40% 100%, rgba(38,110,190,.55), rgba(38,110,190,0) 70%);
        }

        .emblem {
            position: absolute;
            left: 12.55mm; top: 6.9mm;
            width: 36.9mm; height: 36.9mm;
            object-fit: contain;
        }

        .brand-name {
            position: absolute;
            left: 55.6mm; top: 18.0mm;
            font-family: 'Cinzel', 'Times New Roman', serif;
            font-weight: 600;
            font-size: 33pt;
            line-height: 1;
            letter-spacing: 1.55mm;
            color: #ffffff;
            white-space: nowrap;
        }
        .brand-sub {
            position: absolute;
            left: 58.2mm; top: 32.0mm;
            font-family: 'Cinzel', 'Times New Roman', serif;
            font-weight: 400;
            font-size: 15pt;
            line-height: 1;
            letter-spacing: 2.55mm;
            color: var(--blue);
            white-space: nowrap;
        }

        .since-wrap { position: absolute; left: 60.3mm; top: 39.9mm; width: 67.6mm; height: 4mm; }
        .since-wrap .rule {
            position: absolute; top: 50%; height: 0.22mm;
            background: linear-gradient(to right, rgba(176,141,85,0), var(--gold-l), rgba(176,141,85,.25));
        }
        .since-wrap .rule.l { left: 0;  width: 21mm; }
        .since-wrap .rule.r { right: 0; width: 21mm;
            background: linear-gradient(to left, rgba(176,141,85,0), var(--gold-l), rgba(176,141,85,.25)); }
        .since-wrap .txt {
            position: absolute; left: 50%; top: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Cinzel', serif;
            font-size: 8pt; font-weight: 400;
            letter-spacing: 1mm;
            color: var(--gold-l);
            white-space: nowrap;
        }

        .hdr-divider {
            position: absolute;
            left: 136.7mm; top: 8.8mm;
            width: 0.22mm; height: 35.2mm;
            background: linear-gradient(to bottom, rgba(210,210,210,0), rgba(214,214,214,.7) 16%, rgba(214,214,214,.7) 84%, rgba(210,210,210,0));
        }

        .contact {
            position: absolute;
            left: 143.6mm;
            font-family: 'Lato', 'Segoe UI', sans-serif;
            font-size: 9.5pt;
            color: #f2f6fb;
            display: flex;
            align-items: flex-start;
            gap: 2.1mm;
            line-height: 5.29mm;
            white-space: nowrap;
        }
        .contact .ico { width: 4.1mm; flex-shrink: 0; display: flex; justify-content: center; padding-top: 1.2mm; }
        /* address can be long — bound it to the page and let it wrap instead of clipping */
        .contact.addr { right: 8.5mm; white-space: normal; }
        .contact.addr .txt { flex: 1; min-width: 0; overflow-wrap: break-word; }

        .hdr-rule {
            position: absolute;
            left: 0; top: 48mm;
            width: 210mm; height: 0.55mm;
            background: linear-gradient(to right, var(--gold-d), var(--gold-l) 22%, var(--gold) 55%, var(--gold-l) 80%, var(--gold-d));
        }

        .wedge-l {
            position: absolute; left: 0; top: 48.5mm;
            width: 8mm; height: 6mm;
            background: var(--navy-1);
            -webkit-clip-path: polygon(0 0, 100% 0, 0 100%);
            clip-path: polygon(0 0, 100% 0, 0 100%);
        }
        .wedge-r {
            position: absolute; right: 0; top: 48.5mm;
            width: 24mm; height: 9.5mm;
            background: linear-gradient(120deg, #02122b, #0b2545);
            -webkit-clip-path: polygon(100% 0, 100% 100%, 0 0);
            clip-path: polygon(100% 0, 100% 100%, 0 0);
        }
        .wedge-r-line {
            position: absolute; right: 0; top: 48.5mm;
            width: 24mm; height: 9.5mm;
            background: linear-gradient(to bottom right, rgba(176,141,85,0) 49.2%, var(--gold) 49.7%, rgba(176,141,85,0) 50.5%);
            -webkit-clip-path: polygon(100% 0, 100% 100%, 0 0);
            clip-path: polygon(100% 0, 100% 100%, 0 0);
        }

        /* ══════════════ FIELDS ══════════════ */
        .field-row {
            position: absolute;
            left: 11.9mm; right: 11.7mm;
            height: 8mm;
            display: flex; align-items: flex-end; gap: 2.6mm;
        }
        .field-row .lbl { font-size: 13pt; white-space: nowrap; line-height: 1.1; }
        .field-row .line {
            flex: 1;
            border-bottom: 0.4mm solid #1c2f52;
            font-size: 12pt; font-weight: 600;
            padding: 0 0 0.9mm 2mm;
            line-height: 1.1;
            min-height: 5mm;
        }

        /* ══════════════ REQUEST FOR ══════════════ */
        .request-for {
            position: absolute;
            left: 11.9mm; right: 11.7mm;
            display: flex; align-items: center; gap: 3.3mm;
        }
        .request-for .txt {
            font-family: 'EB Garamond', Georgia, serif;
            font-size: 13.5pt; font-weight: 600;
            letter-spacing: 1mm;
            white-space: nowrap;
            padding-left: 1.1mm;
        }
        .rule-gold { flex: 1; display: flex; align-items: center; }
        .rule-gold hr { flex: 1; border: 0; height: 0.28mm; background: var(--gold); }
        .dia {
            width: 1.7mm; height: 1.7mm;
            background: var(--gold);
            transform: rotate(45deg);
            flex-shrink: 0;
        }

        /* ══════════════ BADGES / CHECKBOXES ══════════════ */
        .badge {
            width: 8.4mm; height: 8.4mm;
            border-radius: 50%;
            background: radial-gradient(75% 75% at 32% 26%, #16375f 0%, #02122b 72%);
            border: 0.35mm solid var(--gold);
            box-shadow: 0 0 0 0.3mm var(--paper);
            color: #fff;
            font-family: 'EB Garamond', Georgia, serif;
            font-size: 10.5pt; font-weight: 600;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .cb {
            width: 3.5mm; height: 3.5mm;
            border: 0.3mm solid #1c2f52;
            background: #fff;
            flex-shrink: 0;
        }
        .list { position: absolute; display: flex; flex-direction: column; }
        .list .it {
            display: flex; align-items: center; gap: 2.4mm;
            font-size: 8.7pt;
            line-height: 1;
            white-space: nowrap;
        }
        .list.big .it { font-size: 9.8pt; }
        .wline {
            display: inline-block;
            border-bottom: 0.28mm solid #1c2f52;
            height: 3mm;
            margin: 0 1.4mm;
        }

        /* ══════════════ FRAMED SECTIONS ══════════════ */
        .frame {
            position: absolute;
            border: 0.45mm solid var(--gold);
        }
        .frame::after {
            content: '';
            position: absolute;
            left: 0.7mm; top: 0.7mm; right: 0.7mm; bottom: 0.7mm;
            border: 0.18mm solid var(--gold);
            pointer-events: none;
        }
        .ribbon-wrap {
            position: absolute;
            left: 1.8mm;
            display: flex; align-items: center;
            transform: translateY(-50%);
            z-index: 3;
        }
        .ribbon {
            height: 6.9mm;
            display: flex; align-items: center;
            padding: 0 8mm 0 12.6mm;
            background: linear-gradient(96deg, #0b2545 0%, #02122b 55%, #000813 100%);
            font-family: 'EB Garamond', Georgia, serif;
            font-size: 9pt; font-weight: 500;
            letter-spacing: 0.28mm;
            color: #fff;
            white-space: nowrap;
            -webkit-clip-path: polygon(0 0, 100% 0, calc(100% - 5mm) 100%, 0 100%);
            clip-path: polygon(0 0, 100% 0, calc(100% - 5mm) 100%, 0 100%);
        }
        .ribbon-wrap .badge { position: absolute; left: 2.9mm; z-index: 4; }
        .vdiv { position: absolute; width: 0.22mm; background: var(--gold); }

        /* ══════════════ REFERRED BY ══════════════ */
        .ref-row {
            position: absolute;
            display: flex; align-items: flex-end;
            font-size: 9.5pt;
            height: 6mm;
        }
        .ref-row .line { border-bottom: 0.35mm solid #1c2f52; height: 100%; }

        /* ══════════════ OUR LOCATION ══════════════ */
        .loc-head {
            position: absolute;
            left: 11.9mm;
            display: flex; align-items: center; gap: 3mm;
        }
        .loc-head .txt {
            font-family: 'EB Garamond', Georgia, serif;
            font-size: 9pt; font-weight: 600;
            letter-spacing: 0.85mm;
            white-space: nowrap;
        }
        .loc-head .rule-gold { width: 51.5mm; flex: none; }

        .map { position: absolute; left: 11.9mm; top: 239mm; width: 186.4mm; }
        .map svg { display: block; width: 100%; }

        /* ══════════════ DISCLAIMER ══════════════ */
        .disclaimer {
            position: absolute;
            left: 11.9mm; top: 287.5mm; width: 186.4mm;
            font-family: 'EB Garamond', Georgia, serif;
            font-size: 8.5pt;
            line-height: 3.9mm;
            text-align: center;
            color: var(--ink);
        }

        /* ══════════════ PRINT BUTTON ══════════════ */
        .print-btn {
            position: fixed; top: 8px; right: 8px;
            padding: 7px 15px;
            background: #02122b; color: #e6d3a3;
            border: 1px solid #b08d55; border-radius: 5px;
            font-family: 'Lato', sans-serif; font-size: 12px;
            cursor: pointer; z-index: 999;
        }

        @media print {
            html, body { background: #fff; width: 210mm; height: 297mm; }
            .print-btn { display: none; }
            .page { margin: 0; page-break-after: avoid; break-after: avoid; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨 Print</button>

@php
    $addressLines = array_values(array_filter([$clinic->address, $clinic->city]))
        ?: ['28 - 1 Don Juan Estevez Street', 'Guevarra Subdivision,', 'Legazpi City Albay, Bicol'];
    $phone    = $clinic->phone ?: '0918 633 1795';
    $email    = $clinic->email ?: 'gonzales.dentalclinic@yahoo.com';
    $pname    = $patient->full_name;
    $pbday    = $patient->date_of_birth?->format('F d, Y') ?? '';
    $emblem   = asset('images/gonzales-emblem.png');
@endphp

<div class="page">

    {{-- ══════════ HEADER ══════════ --}}
    <div class="hdr">
        <div class="hdr-bloom"></div>
        <div class="hdr-slash"></div>

        <img class="emblem" src="{{ $emblem }}" alt="Gonzales Dental Clinic">

        <div class="brand-name vc">GONZALES</div>
        <div class="brand-sub vc">DENTAL CLINIC</div>

        <div class="since-wrap vc">
            <div class="rule l"></div>
            <div class="txt">SINCE 2005</div>
            <div class="rule r"></div>
        </div>

        <div class="hdr-divider"></div>

        <div class="contact addr" style="top:10.4mm;">
            <span class="ico">
                <svg width="13" height="16" viewBox="0 0 24 30" fill="#2f7fc4">
                    <path d="M12 0C5.9 0 1 4.9 1 11c0 8.2 11 19 11 19s11-10.8 11-19c0-6.1-4.9-11-11-11zm0 15a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/>
                </svg>
            </span>
            <span class="txt">@foreach ($addressLines as $l){{ $l }}@if (!$loop->last)<br>@endif@endforeach</span>
        </div>

        <div class="contact" style="top:29.2mm;">
            <span class="ico">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#2f7fc4">
                    <path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.2.4 2.4.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1l-2.3 2.2z"/>
                </svg>
            </span>
            <span>{{ $phone }}</span>
        </div>

        <div class="contact" style="top:37.6mm;">
            <span class="ico">
                <svg width="15" height="12" viewBox="0 0 26 20" fill="none" stroke="#2f7fc4" stroke-width="1.8">
                    <rect x="1" y="1" width="24" height="18"/>
                    <path d="M1 1l12 10L25 1"/>
                </svg>
            </span>
            <span>{{ $email }}</span>
        </div>
    </div>

    <div class="hdr-rule"></div>
    <div class="wedge-l"></div>
    <div class="wedge-r"></div>
    <div class="wedge-r-line"></div>

    {{-- ══════════ PATIENT FIELDS ══════════ --}}
    <div class="field-row vc" style="top:63mm;">
        <span class="lbl">Patient&rsquo;s Name:</span>
        <span class="line">{{ $pname }}</span>
    </div>
    <div class="field-row vc" style="top:74mm;">
        <span class="lbl">Birthday:</span>
        <span class="line">{{ $pbday }}</span>
    </div>

    {{-- ══════════ REQUEST FOR ══════════ --}}
    <div class="request-for vc" style="top:88mm;">
        <div class="dia"></div>
        <div class="rule-gold"><hr></div>
        <div class="txt">REQUEST FOR</div>
        <div class="rule-gold"><hr></div>
        <div class="dia"></div>
    </div>

    {{-- ══════════ A ══════════ --}}
    <div class="a vc badge" style="left:13.35mm; top:98.5mm;">A</div>
    <div class="list big" style="left:28.3mm; top:95.55mm;">
        <div class="it" style="height:5.9mm;"><span class="cb"></span>Periapical Radiograph ( Tooth Number <span class="wline" style="width:15.7mm;"></span> )</div>
        <div class="it" style="height:5.9mm;"><span class="cb"></span>Photograph 3R</div>
        <div class="it" style="height:5.9mm;"><span class="cb"></span>Diagnostic Cast</div>
    </div>

    {{-- ══════════ B ══════════ --}}
    <div class="a vc badge" style="left:13.35mm; top:123.5mm;">B</div>
    <div class="list big vc" style="left:28.3mm; top:123.5mm;">
        <div class="it" style="height:5.9mm;"><span class="cb"></span>Complete Ortho Diagnostic Package</div>
    </div>

    {{-- ══════════ C — PANORAMIC ══════════ --}}
    <div class="frame" style="left:11.9mm; top:132mm; width:91.5mm; height:58mm;">
        <div class="ribbon-wrap" style="top:6.9mm;">
            <div class="ribbon">PANORAMIC</div>
            <div class="badge">C</div>
        </div>
        <div class="vdiv" style="left:52.4mm; top:12mm; height:42mm;"></div>
        <div class="list" style="left:8.7mm; top:12.4mm;">
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Standard or Jaw</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Segment</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Sinus</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Bite Wing</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>TMJ &ndash; Open &amp; Close Mouth</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Orthogonal View</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>With Bite Block</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Without Bite Block</div>
        </div>
        <div class="list" style="left:57.6mm; top:12.4mm;">
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Lateral View</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Full Lateral View</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>SMV View</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>PA Position</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Carpus</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>Others <span class="wline" style="width:13.5mm;"></span></div>
        </div>
    </div>

    {{-- ══════════ D — CEPHALOMETRIC ══════════ --}}
    <div class="frame" style="left:106.8mm; top:132mm; width:91.5mm; height:58mm;">
        <div class="ribbon-wrap" style="top:6.9mm;">
            <div class="ribbon">CEPHALOMETRIC</div>
            <div class="badge">D</div>
        </div>
        <div class="vdiv" style="left:43.9mm; top:15.6mm; height:20mm;"></div>
        <div class="list" style="left:7.3mm; top:17.9mm;">
            <div class="it" style="height:5.9mm;"><span class="cb"></span>Lateral View</div>
            <div class="it" style="height:5.9mm;"><span class="cb"></span>Full Lateral View</div>
            <div class="it" style="height:5.9mm;"><span class="cb"></span>SMV View</div>
        </div>
        <div class="list" style="left:49.3mm; top:17.9mm;">
            <div class="it" style="height:5.9mm;"><span class="cb"></span>PA Position</div>
            <div class="it" style="height:5.9mm;"><span class="cb"></span>Carpus</div>
            <div class="it" style="height:5.9mm;"><span class="cb"></span>Others <span class="wline" style="width:15mm;"></span></div>
        </div>
    </div>

    {{-- ══════════ E — CBCT ══════════ --}}
    <div class="frame" style="left:11.9mm; top:193mm; width:186.4mm; height:23mm;">
        <div class="ribbon-wrap" style="top:7.2mm;">
            <div class="ribbon">CONE BEAM COMPUTED TOMOGRAPHY</div>
            <div class="badge">E</div>
        </div>
        <div class="list" style="left:10.4mm; top:10.4mm;">
            <div class="it" style="height:5.6mm;"><span class="cb"></span>12 x 9.5 FOV</div>
            <div class="it" style="height:5.6mm;"><span class="cb"></span>5 x 5 FOV</div>
        </div>
    </div>

    {{-- ══════════ REFERRED BY ══════════ --}}
    <div class="ref-row vc" style="left:11.9mm; top:221mm; width:125.2mm;">
        <span style="white-space:nowrap;">Referred by</span>
        <span class="line" style="flex:1; margin-left:3.1mm;"></span>
    </div>
    <div class="ref-row vc" style="left:11.9mm; top:230mm; width:125.2mm;">
        <span style="white-space:nowrap;">Dr.</span>
        <span class="line" style="flex:1; margin-left:1.8mm;"></span>
    </div>
    <div class="ref-row vc" style="left:143.5mm; top:230mm; width:54.8mm;">
        <span style="white-space:nowrap;">Date</span>
        <span class="line" style="flex:1; margin-left:2.9mm;"></span>
    </div>

    {{-- ══════════ OUR LOCATION ══════════ --}}
    <div class="loc-head vc" style="top:236.5mm;">
        <div class="txt">OUR LOCATION</div>
        <div class="rule-gold"><hr><span class="dia"></span></div>
    </div>

    {{-- ══════════ MAP ══════════ --}}
    <div class="map">
        <svg viewBox="0 0 1016 250" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="mapbg" x1="0" y1="0" x2="0.35" y2="1">
                    <stop offset="0%"   stop-color="#0e2544"/>
                    <stop offset="55%"  stop-color="#081a34"/>
                    <stop offset="100%" stop-color="#04101f"/>
                </linearGradient>
                <style>
                    .rd  { font-family: Arial, Helvetica, sans-serif; font-weight: 600; fill: #ffffff; font-size: 15px; letter-spacing: .6px; }
                    .sm  { font-family: Arial, Helvetica, sans-serif; font-weight: 600; fill: #ffffff; font-size: 10px; letter-spacing: .4px; }
                    .bx  { font-family: Arial, Helvetica, sans-serif; font-weight: 500; fill: #ffffff; font-size: 19px; }
                    .ln  { fill: none; stroke: #b08d55; stroke-width: 1.6; }
                    .ln2 { fill: none; stroke: #b08d55; stroke-width: 1.2; }
                </style>
            </defs>

            <rect x="0" y="0" width="1016" height="250" fill="url(#mapbg)"/>
            <rect class="ln" x="0.8" y="0.8" width="1014.4" height="248.4"/>

            {{-- Marquez St. --}}
            <line class="ln2" x1="70" y1="1" x2="70" y2="249"/>
            <text class="rd" transform="translate(40,126) rotate(-90)" text-anchor="middle">MARQUEZ ST.</text>

            {{-- Don Juan Estevez St. (left) --}}
            <line class="ln2" x1="74"  y1="29" x2="188" y2="29"/>
            <line class="ln2" x1="359" y1="29" x2="475" y2="29"/>
            <text class="rd" x="273" y="34" text-anchor="middle">DON JUAN ESTEVEZ ST.</text>

            {{-- Clinic block --}}
            <rect class="ln2" x="75" y="51" width="395" height="173"/>
            <image href="{{ $emblem }}" x="135" y="92" width="90" height="90"/>
            <text x="345" y="145" text-anchor="middle" font-family="'Times New Roman', Georgia, serif" font-size="37" font-weight="600" fill="#ffffff" letter-spacing="2.5">GONZALES</text>
            <text x="347" y="175" text-anchor="middle" font-family="'Times New Roman', Georgia, serif" font-size="16" fill="#2f7fc4" letter-spacing="7">DENTAL CLINIC</text>

            {{-- Guevarra Subdivision --}}
            <line class="ln2" x1="481" y1="1" x2="481" y2="249"/>
            <text class="rd" transform="translate(511,125) rotate(-90)" text-anchor="middle">GUEVARRA SUBDIVISION</text>
            <line class="ln2" x1="532" y1="1" x2="532" y2="249"/>

            {{-- Estevez Hospital --}}
            <rect class="ln2" x="577" y="18" width="147" height="83"/>
            <text class="bx" x="650" y="53" text-anchor="middle">ESTEVEZ</text>
            <text class="bx" x="650" y="80" text-anchor="middle">HOSPITAL</text>

            <text class="rd" x="650" y="133" text-anchor="middle">DON JUAN ESTEVEZ ST.</text>
            <line class="ln2" x1="532" y1="148" x2="774" y2="148"/>
            <rect class="ln2" x="532" y="148" width="242" height="96"/>

            {{-- Rizal St. --}}
            <line class="ln2" x1="774" y1="1" x2="774" y2="249"/>
            <text class="rd" transform="translate(820,124) rotate(-90)" text-anchor="middle">RIZAL ST.</text>
            <text class="sm" transform="translate(798,68) rotate(-90)" text-anchor="middle">TO DARAGA</text>
            <text class="sm" transform="translate(798,212) rotate(-90)" text-anchor="middle">TO LEGAZPI</text>
            <path d="M798 10 l5 10 h-10 z" fill="#ffffff"/>
            <path d="M798 241 l5 -10 h-10 z" fill="#ffffff"/>
            <line class="ln2" x1="845" y1="1" x2="845" y2="249"/>

            {{-- St. Agnes Academy --}}
            <rect class="ln2" x="859" y="18"  width="35" height="38"/>
            <rect class="ln2" x="859" y="77"  width="35" height="38"/>
            <rect class="ln2" x="859" y="139" width="35" height="38"/>
            <rect class="ln2" x="859" y="196" width="35" height="38"/>
            <text class="rd" transform="translate(945,126) rotate(-90)" text-anchor="middle">ST. AGNES ACADEMY</text>
        </svg>
    </div>

    {{-- ══════════ DISCLAIMER ══════════ --}}
    <div class="disclaimer">
        Gonzales Dental Clinic does not provide an official reading of the X-ray and CBCT requests.<br>
        Instead, the referring dentist will interpret the results.
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
