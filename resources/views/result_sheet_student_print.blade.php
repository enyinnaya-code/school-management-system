<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skill Sheet — {{ strtoupper($student->name) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            background: #d8d8d8;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            color: #000;
        }

        /* ── TOOLBAR ── */
        .toolbar {
            width: 210mm;
            display: flex;
            justify-content: flex-end;
            gap: 16;
            margin-bottom: 12px;
        }
        .toolbar button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
        }
        .btn-print { background: #c00; color: #fff; }
        .btn-close  { background: #555; color: #fff; }

        /* ── A4 PAGE ── */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            padding: 8mm 10mm;
            box-shadow: 0 2px 16px rgba(0,0,0,.3);
        }

        /* ── HEADER ── */
        .header {
            display: table;
            width: 100%;
            border-bottom: 1.5px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .hd-logo { display: table-cell; width: 60px; vertical-align: middle; text-align: center; }
        .hd-logo img { width: 52px; height: 52px; object-fit: contain; }
        .hd-center { display: table-cell; text-align: center; vertical-align: middle; padding: 0 6px; }
        .school-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .16;
        }
        .school-addr { font-size: 16px; color: #333; margin-top: 2px; }
        .sheet-badge {
            display: inline-block;
            border: 1.5px solid #000;
            color: #000;
            font-size: 16px;
            font-weight: bold;
            padding: 2px 16px;
            margin-top: 5px;
            letter-spacing: .5px;
        }
        .hd-right {
            display: table-cell;
            width: 90px;
            vertical-align: middle;
            text-align: right;
            font-size: 16px;
            line-height: 1.8;
            font-weight: bold;
        }

        /* ── CLASS BANNER ── */
        .class-banner {
            border: 1.5px solid #000;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            padding: 3px;
            margin: 5px 0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* ── STUDENT INFO BAR ── */
        .student-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            border: 1px solid #aaa;
            font-size: 16px;
        }
        .student-bar td { padding: 3px 7px; border-right: 1px solid #ddd; }
        .student-bar td:last-child { border-right: none; }
        .lbl { font-weight: bold; }

        /* ── TERM HEADING ── */
        .term-heading {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin-bottom: 5px;
            letter-spacing: 1px;
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
            width: 22px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            writing-mode: vertical-lr;
            white-space: nowrap;
            padding: 3px 1px;
            border: 1px solid #999;
            background: #f5f5f5;
            vertical-align: bottom;
        }

        /* ── SUBJECT BLOCK ── */
        .subject-block { margin-bottom: 5px; }
        .subject-title {
            font-size: 16px;
            font-weight: bold;
            border-left: 3px solid #000;
            padding: 2px 5px;
            margin-bottom: 1px;
            background: #f5f5f5;
        }
        .subcat-title {
            font-size: 13px;
            font-style: italic;
            padding: 1px 5px 1px 16px;
            color: #222;
        }

        /* ── ITEM ROW ── */
        .item-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #ddd;
        }
        .item-text {
            display: table-cell;
            font-size: 16px;
            padding: 2px 5px;
            vertical-align: middle;
            line-height: 1.4;
        }
        .item-cell {
            display: table-cell;
            width: 22px;
            text-align: center;
            vertical-align: middle;
            border-left: 1px solid #ddd;
        }

        /* ── CHECK BOX — uses border only, no background fill (ink saving) ── */
        .cb {
            width: 13px;
            height: 13px;
            border: 1.5px solid #333;
            display: inline-block;
            background: #fff;
            position: relative;
            vertical-align: middle;
        }
        /* Ticked: just a bold ✓ character, no background fill */
        .cb.ticked::after {
            content: '✓';
            color: #000;
            font-size: 11px;
            font-weight: bold;
            position: absolute;
            top: -3px;
            left: 0px;
            line-height: 1;
        }

        /* ── FOOTER ── */
        .sheet-footer {
            border-top: 1px solid #888;
            margin-top: 16px;
            padding-top: 7px;
        }
        .footer-row {
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }
        .footer-cell {
            display: table-cell;
            vertical-align: bottom;
            padding-right: 20px;
        }
        .footer-cell:last-child { padding-right: 0; }
        .f-label {
            font-size: 13px;
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
        }
        .f-value {
            font-size: 16px;
            display: block;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding-bottom: 2px;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            display: block;
            min-height: 20px;
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
                padding: 6mm 8mm;
                box-shadow: none;
            }
            /* Ensure checkboxes and their pseudo-elements print */
            .cb {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 1.5px solid #333 !important;
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
                background: #f5f5f5 !important;
            }
            .rh-col {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #f5f5f5 !important;
            }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>
</head>
<body>

    {{-- Toolbar --}}
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
        <button class="btn-close"  onclick="window.close()">✕ Close</button>
    </div>

    <div class="a4-page">

        {{-- Header --}}
        <div class="header">
            <div class="hd-logo">
                @php
                    $settings = school_settings();
                    $logoPath = $settings && $settings->logo
                        ? public_path('storage/logos/' . $settings->logo)
                        : public_path('images/school_management_logo__1_-removebg-preview.png');
                @endphp
                @if(file_exists($logoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Logo">
                @endif
            </div>
            <div class="hd-center">
                <div class="school-name">{{ school_name() }}</div>
                @if(!empty($settings->address))
                    <div class="school-addr">{{ $settings->address }}</div>
                @endif
                <div class="sheet-badge">PUPIL'S CONTINUOUS ASSESSMENT REPORT</div>
            </div>
            <div class="hd-right">
                {{ $currentSession->name }}<br>
                {{ $selectedTerm->name }}
            </div>
        </div>

        {{-- Class banner --}}
        <div class="class-banner">{{ $class->name }}</div>

        {{-- Student info --}}
        <table class="student-bar">
            <tr>
                <td><span class="lbl">Name:</span> {{ strtoupper($student->name) }}</td>
                <td><span class="lbl">Adm No:</span> {{ $student->admission_no ?? '—' }}</td>
                <td><span class="lbl">Class:</span> {{ $class->name }}</td>
                <td><span class="lbl">Gender:</span> {{ ucfirst($student->gender ?? '—') }}</td>
                <td><span class="lbl">Session:</span> {{ $currentSession->name }}</td>
            </tr>
        </table>

        {{-- Term heading --}}
        <div class="term-heading">{{ $selectedTerm->name }}</div>

        {{-- Rating column headers --}}
        <div class="rating-header">
            <div class="rh-spacer"></div>
            @foreach($sheetTemplate->rating_columns as $col)
                <div class="rh-col">{{ $col }}</div>
            @endforeach
        </div>

        {{-- Subjects --}}
        @foreach($subjects as $subject)
        <div class="subject-block">
            <div class="subject-title">
                ({{ $subject->subject_number }}) {{ $subject->subject_name }}
            </div>

            @foreach($subject->subcategories as $sub)
                @if($sub->name)
                    <div class="subcat-title">{{ $sub->label }} {{ $sub->name }}</div>
                @endif
                @foreach($sub->items as $item)
                <div class="item-row">
                    <div class="item-text">{{ $item->item_text }}</div>
                    @foreach($sheetTemplate->rating_columns as $col)
                        <div class="item-cell">
                            @php $isTicked = (isset($ratings[(int)$item->id]) && trim($ratings[(int)$item->id]) === trim($col)); @endphp
                            <span class="cb {{ $isTicked ? 'ticked' : '' }}"></span>
                        </div>
                    @endforeach
                </div>
                @endforeach
            @endforeach

            {{-- Direct items --}}
            @foreach($subject->items as $item)
            <div class="item-row">
                <div class="item-text">{{ $item->item_text }}</div>
                @foreach($sheetTemplate->rating_columns as $col)
                    <div class="item-cell">
                        @php $isTicked = (isset($ratings[(int)$item->id]) && trim($ratings[(int)$item->id]) === trim($col)); @endphp
                        <span class="cb {{ $isTicked ? 'ticked' : '' }}"></span>
                    </div>
                @endforeach
            </div>
            @endforeach
        </div>
        @endforeach

        {{-- Footer --}}
        @php $ff = $sheetTemplate->footer_fields ?? []; @endphp
        @if(array_filter($ff))
        <div class="sheet-footer">

            {{-- Row 1: Remark + Re-opening Date --}}
            @if(($ff['footer_remark'] ?? false) || ($ff['footer_reopening'] ?? false))
            <div class="footer-row">
                @if($ff['footer_remark'] ?? false)
                <div class="footer-cell">
                    <span class="f-label">REMARK:</span>
                    <span class="f-value">{{ $footerData->remark ?? '' }}</span>
                </div>
                @endif
                @if($ff['footer_reopening'] ?? false)
                <div class="footer-cell">
                    <span class="f-label">RE-OPENING DATE:</span>
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
                    <span class="f-label">CLASS TEACHER'S SIGNATURE:</span>
                    <span class="sig-line"></span>
                </div>
                @endif
                @if($ff['footer_headmistress'] ?? false)
                <div class="footer-cell">
                    <span class="f-label">HEADMISTRESS' SIGNATURE:</span>
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