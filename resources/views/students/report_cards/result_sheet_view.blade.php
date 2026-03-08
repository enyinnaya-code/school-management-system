<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skill Sheet — {{ strtoupper($student->name) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            background: #c8c8c8;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px;
            color: #000;
        }

        /* ── TOOLBAR ── */
        .toolbar {
            width: 210mm;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 14px;
        }
        .toolbar button {
            padding: 10px 22px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: .3px;
        }
        .btn-print { background: #b00020; color: #fff; }
        .btn-print:hover { background: #8c0018; }
        .btn-close  { background: #444; color: #fff; }
        .btn-close:hover { background: #222; }

        /* ── A4 PAGE ── */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            padding: 9mm 11mm;
            box-shadow: 0 4px 24px rgba(0,0,0,.35);
        }

        /* ══════════════════ HEADER ══════════════════ */
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 7px;
            margin-bottom: 6px;
        }
        .hd-logo {
            display: table-cell;
            width: 68px;
            vertical-align: middle;
            text-align: center;
        }
        .hd-logo img {
            width: 58px;
            height: 58px;
            object-fit: contain;
        }
        .hd-center {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            padding: 0 8px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .8px;
            line-height: 1.2;
        }
        .school-addr {
            font-size: 12px;
            color: #444;
            margin-top: 3px;
            line-height: 1.4;
        }
        .sheet-badge {
            display: inline-block;
            border: 2px solid #000;
            color: #000;
            font-size: 12px;
            font-weight: bold;
            padding: 3px 18px;
            margin-top: 6px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .hd-right {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            line-height: 2;
            white-space: nowrap;
        }

        /* ── CLASS BANNER ── */
        .class-banner {
            border: 2px solid #000;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            padding: 4px;
            margin: 6px 0;
            letter-spacing: 3px;
            text-transform: uppercase;
            background: #f0f0f0;
        }

        /* ── STUDENT INFO BAR ── */
        .student-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 12px;
            border: 1.5px solid #888;
        }
        .student-bar td {
            padding: 4px 8px;
            border-right: 1px solid #ccc;
            vertical-align: middle;
        }
        .student-bar td:last-child { border-right: none; }
        .lbl { font-weight: bold; color: #222; margin-right: 3px; }

        /* ── TERM HEADING ── */
        .term-heading {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 4px 0;
            margin-bottom: 6px;
            letter-spacing: 2px;
            background: #fafafa;
        }

        /* ── RATING HEADER ROW ── */
        .rating-header {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }
        .rh-spacer { display: table-cell; }
        .rh-col {
            display: table-cell;
            width: 26px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            writing-mode: vertical-lr;
            white-space: nowrap;
            padding: 4px 2px;
            border: 1px solid #aaa;
            background: #eaeaea;
            vertical-align: bottom;
            letter-spacing: .3px;
        }

        /* ── SUBJECT BLOCK ── */
        .subject-block { margin-bottom: 7px; }

        .subject-title {
            font-size: 12px;
            font-weight: bold;
            border-left: 4px solid #222;
            padding: 3px 7px;
            margin-bottom: 1px;
            background: #ececec;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .subcat-title {
            font-size: 11.5px;
            font-style: italic;
            font-weight: 600;
            padding: 2px 7px 2px 18px;
            color: #111;
            background: #f7f7f7;
            border-bottom: 1px solid #ddd;
        }

        /* ── ITEM ROW ── */
        .item-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #e2e2e2;
        }

        .item-text {
            display: table-cell;
            font-size: 12px;
            padding: 3px 7px;
            vertical-align: middle;
            line-height: 1.55;
            color: #111;
        }
        .item-cell {
            display: table-cell;
            width: 26px;
            text-align: center;
            vertical-align: middle;
            border-left: 1px solid #e0e0e0;
        }

        /* ── CHECKBOX ── */
        .cb {
            width: 14px;
            height: 14px;
            border: 1.5px solid #666;
            display: inline-block;
            background: #fff;
            position: relative;
            vertical-align: middle;
            border-radius: 2px;
        }
        .cb.ticked {
            border-color: #000;
        }
        .cb.ticked::after {
            content: '✓';
            color: #000;
            font-size: 12px;
            font-weight: bold;
            position: absolute;
            top: -4px;
            left: 0px;
            line-height: 1;
        }

        /* ── FOOTER ── */
        .sheet-footer {
            border-top: 1.5px solid #777;
            margin-top: 20px;
            padding-top: 10px;
        }
        .footer-row {
            display: table;
            width: 100%;
            margin-bottom: 22px;
        }
        .footer-cell {
            display: table-cell;
            vertical-align: bottom;
            padding-right: 28px;
        }
        .footer-cell:last-child { padding-right: 0; }
        .f-label {
            font-size: 11px;
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #222;
        }
        .f-value {
            font-size: 12px;
            display: block;
            border-bottom: 1.5px solid #000;
            min-height: 22px;
            padding-bottom: 2px;
        }
        .sig-line {
            border-bottom: 1.5px solid #000;
            display: block;
            min-height: 22px;
        }

        /* ── PRINT ── */
        @media print {
            body {
                background: #fff;
                padding: 0;
                display: block;
            }
            .toolbar { display: none !important; }
            .a4-page {
                width: 100%;
                min-height: auto;
                padding: 6mm 9mm;
                box-shadow: none;
            }
            .cb {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 1.5px solid #666 !important;
            }
            .cb.ticked::after {
                content: '✓' !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .subject-title {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #ececec !important;
                border-left: 4px solid #222 !important;
            }
            .class-banner {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #f0f0f0 !important;
            }
            .rh-col {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #eaeaea !important;
            }
            .term-heading {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #fafafa !important;
            }
            .subcat-title {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #f7f7f7 !important;
            }
            .subject-block { page-break-inside: avoid; }
            .item-row       { page-break-inside: avoid; }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>
</head>
<body>

    {{-- ── Toolbar (hidden on print) ── --}}
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">🖨&nbsp; Print / Save PDF</button>
        <button class="btn-close"  onclick="history.back()">✕&nbsp; Back</button>
    </div>

    <div class="a4-page">

        {{-- ══ HEADER ══ --}}
        <div class="header">
            <div class="hd-logo">
                @php
                    $settings = school_settings();
                    $logoPath = $settings && $settings->logo
                        ? public_path('storage/logos/' . $settings->logo)
                        : public_path('images/school_management_logo__1_-removebg-preview.png');
                @endphp
                @if(file_exists($logoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="School Logo">
                @endif
            </div>

            <div class="hd-center">
                <div class="school-name">{{ school_name() }}</div>
                @if(!empty($settings->address))
                    <div class="school-addr">{{ $settings->address }}</div>
                @endif
                <div class="sheet-badge">Pupil's Continuous Assessment Report</div>
            </div>

            <div class="hd-right">
                {{ $currentSession->name }}<br>
                {{ $currentTerm->name }}
            </div>
        </div>

        {{-- ══ CLASS BANNER ══ --}}
        <div class="class-banner">{{ $class->name }}</div>

        {{-- ══ STUDENT INFO BAR ══ --}}
        <table class="student-bar">
            <tr>
                <td><span class="lbl">Name:</span> {{ strtoupper($student->name) }}</td>
                <td><span class="lbl">Adm No:</span> {{ $student->admission_no ?? '—' }}</td>
                <td><span class="lbl">Class:</span> {{ $class->name }}</td>
                <td><span class="lbl">Gender:</span> {{ ucfirst($student->gender ?? '—') }}</td>
                <td><span class="lbl">Session:</span> {{ $currentSession->name }}</td>
            </tr>
        </table>

        {{-- ══ TERM HEADING ══ --}}
        <div class="term-heading">{{ $currentTerm->name }}</div>

        {{-- ══ RATING COLUMN HEADERS ══ --}}
        <div class="rating-header">
            <div class="rh-spacer"></div>
            @foreach($sheetTemplate->rating_columns as $col)
                <div class="rh-col">{{ $col }}</div>
            @endforeach
        </div>

        {{-- ══ SUBJECTS ══ --}}
        @foreach($subjects as $subject)
        <div class="subject-block">

            <div class="subject-title">
                ({{ $subject->subject_number }})&nbsp; {{ $subject->subject_name }}
            </div>

            {{-- Subcategories and their items --}}
            @foreach($subject->subcategories as $sub)

                @if($sub->name)
                <div class="subcat-title">
                    @if(!empty($sub->label)){{ $sub->label }}&nbsp;@endif{{ $sub->name }}
                </div>
                @endif

                @foreach($sub->items as $item)
                <div class="item-row">
                    <div class="item-text">{{ $item->item_text }}</div>
                    @foreach($sheetTemplate->rating_columns as $col)
                        @php
                            $isTicked = isset($ratings[(int)$item->id])
                                && trim($ratings[(int)$item->id]) === trim($col);
                        @endphp
                        <div class="item-cell">
                            <span class="cb {{ $isTicked ? 'ticked' : '' }}"></span>
                        </div>
                    @endforeach
                </div>
                @endforeach

            @endforeach

            {{-- Direct items (no subcategory) --}}
            @foreach($subject->items as $item)
            <div class="item-row">
                <div class="item-text">{{ $item->item_text }}</div>
                @foreach($sheetTemplate->rating_columns as $col)
                    @php
                        $isTicked = isset($ratings[(int)$item->id])
                            && trim($ratings[(int)$item->id]) === trim($col);
                    @endphp
                    <div class="item-cell">
                        <span class="cb {{ $isTicked ? 'ticked' : '' }}"></span>
                    </div>
                @endforeach
            </div>
            @endforeach

        </div>
        @endforeach

        {{-- ══ FOOTER ══ --}}
        @php $ff = $sheetTemplate->footer_fields ?? []; @endphp
        @if(array_filter($ff))
        <div class="sheet-footer">

            {{-- Row 1: Remark + Re-opening Date --}}
            @if(($ff['footer_remark'] ?? false) || ($ff['footer_reopening'] ?? false))
            <div class="footer-row">
                @if($ff['footer_remark'] ?? false)
                <div class="footer-cell">
                    <span class="f-label">Remark:</span>
                    <span class="f-value">{{ $footerData->remark ?? '' }}</span>
                </div>
                @endif
                @if($ff['footer_reopening'] ?? false)
                <div class="footer-cell">
                    <span class="f-label">Re-Opening Date:</span>
                    <span class="f-value">{{ $footerData->reopening_date ?? '' }}</span>
                </div>
                @endif
            </div>
            @endif

            {{-- Row 2: Signatures --}}
            @if(($ff['footer_class_teacher'] ?? false) || ($ff['footer_headmistress'] ?? false))
            <div class="footer-row">
                @if($ff['footer_class_teacher'] ?? false)
                <div class="footer-cell">
                    <span class="f-label">Class Teacher's Signature:</span>
                    <span class="sig-line"></span>
                </div>
                @endif
                @if($ff['footer_headmistress'] ?? false)
                <div class="footer-cell">
                    <span class="f-label">Headmistress' Signature:</span>
                    <span class="sig-line"></span>
                </div>
                @endif
            </div>
            @endif

        </div>
        @endif

    </div>{{-- /.a4-page --}}

</body>
</html>