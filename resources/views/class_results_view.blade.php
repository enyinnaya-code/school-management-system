@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-2">
                    <div class="card">
                        <div class="card-body">

                            {{-- Header --}}
                            <div class="section-header d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="mb-1">Class Results: {{ $class->name }}</h5>
                                    <p class="mb-0 text-muted">
                                        <strong>{{ $selectedTerm->name }}</strong> —
                                        <strong>{{ $selectedSession->name }}</strong>
                                    </p>
                                </div>
                                <div class="no-print d-flex gap-2">
                                    <button type="button" class="btn m-1 btn-success" onclick="window.print()">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <a href="{{ route('results.class.export', $class->id) }}?session_id={{ $selectedSession->id }}&term_id={{ $selectedTerm->id }}"
                                        class="btn btn-info text-white m-1">
                                        <i class="fas fa-file-excel"></i> Export to Excel
                                    </a>
                                </div>
                            </div>

                            {{-- Filter Form --}}
                            <div class="card mb-4 no-print">
                                <div class="card-body">
                                    <form method="GET" action="{{ route('results.class.view', $class->id) }}">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <label for="session_id" class="form-label">Session</label>
                                                <select name="session_id" id="session_id" class="form-control"
                                                    onchange="this.form.submit()">
                                                    @foreach($sessions as $session)
                                                        <option value="{{ $session->id }}"
                                                            {{ $selectedSession->id == $session->id ? 'selected' : '' }}>
                                                            {{ $session->name }} {{ $session->is_current ? '(Current)' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label for="term_id" class="form-label">Term</label>
                                                <select name="term_id" id="term_id" class="form-control"
                                                    onchange="this.form.submit()">
                                                    @foreach($terms as $term)
                                                        <option value="{{ $term->id }}"
                                                            {{ $selectedTerm->id == $term->id ? 'selected' : '' }}>
                                                            {{ $term->name }} {{ $term->is_current ? '(Current)' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="fas fa-filter"></i> Apply
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Printable Area --}}
                            <div class="printable-area">

                                {{-- Print-only header --}}
                                <div class="text-center mb-4 print-only">
                                    <h4>{{ $class->name }} Results</h4>
                                    <h5>{{ $selectedSession->name }} — {{ $selectedTerm->name }}</h5>
                                    <hr>
                                </div>

                                <div class="table-responsive">

                                    {{-- ══════════════════════════════════════════════════════
                                         NURSERY: checkbox columns per subject item
                                         ══════════════════════════════════════════════════════ --}}
                                    @if($isNursery)

                                        @foreach($subjects as $subject)

                                            <h6 class="mt-4 mb-2 subject-heading">
                                                ({{ $subject->subject_number }}) {{ $subject->subject_name }}
                                            </h6>

                                            @foreach($subject->subcategories as $sub)

                                                @if($sub->name)
                                                    <p class="mb-1 subcat-heading">
                                                        <em>{{ $sub->name }}</em>
                                                    </p>
                                                @endif

                                                @if(count($sub->items) > 0)
                                                    <table class="table table-bordered table-sm mb-3 nursery-table">
                                                        <thead class="bg-light text-center">
                                                            <tr>
                                                                <th style="width:40px;">S/N</th>
                                                                <th style="text-align:left; min-width:180px;">Item</th>
                                                                @foreach($sheetTemplate->rating_columns as $col)
                                                                    <th style="min-width:55px; font-size:10px;">{{ $col }}</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($students as $index => $student)
                                                                <tr class="table-secondary student-banner">
                                                                    <td colspan="{{ 2 + count($sheetTemplate->rating_columns) }}"
                                                                        style="font-weight:bold; font-size:11px; padding:3px 8px;">
                                                                        {{ $index + 1 }}. {{ $student->name }}
                                                                        <span class="text-muted"
                                                                            style="font-weight:normal; font-size:10px;">
                                                                            — {{ $student->admission_no }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                @foreach($sub->items as $itemIdx => $item)
                                                                    @php
                                                                        $storedVal = $ratingsMatrix[$student->id][$item->id] ?? null;
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="text-center text-muted"
                                                                            style="font-size:10px;">{{ $itemIdx + 1 }}</td>
                                                                        <td style="font-size:11px;">{{ $item->item_text }}</td>
                                                                        @foreach($sheetTemplate->rating_columns as $col)
                                                                            <td class="text-center">
                                                                                @if(trim((string)$storedVal) === trim($col))
                                                                                    <span class="cb-checked">&#10003;</span>
                                                                                @else
                                                                                    <span class="cb-empty"></span>
                                                                                @endif
                                                                            </td>
                                                                        @endforeach
                                                                    </tr>
                                                                @endforeach
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif

                                            @endforeach

                                            {{-- Direct items on the subject (no subcategory) --}}
                                            @if(count($subject->items) > 0)
                                                <table class="table table-bordered table-sm mb-3 nursery-table">
                                                    <thead class="bg-light text-center">
                                                        <tr>
                                                            <th style="width:40px;">S/N</th>
                                                            <th style="text-align:left; min-width:180px;">Item</th>
                                                            @foreach($sheetTemplate->rating_columns as $col)
                                                                <th style="min-width:55px; font-size:10px;">{{ $col }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($students as $index => $student)
                                                            <tr class="table-secondary student-banner">
                                                                <td colspan="{{ 2 + count($sheetTemplate->rating_columns) }}"
                                                                    style="font-weight:bold; font-size:11px; padding:3px 8px;">
                                                                    {{ $index + 1 }}. {{ $student->name }}
                                                                    <span class="text-muted"
                                                                        style="font-weight:normal; font-size:10px;">
                                                                        — {{ $student->admission_no }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            @foreach($subject->items as $itemIdx => $item)
                                                                @php
                                                                    $storedVal = $ratingsMatrix[$student->id][$item->id] ?? null;
                                                                @endphp
                                                                <tr>
                                                                    <td class="text-center text-muted"
                                                                        style="font-size:10px;">{{ $itemIdx + 1 }}</td>
                                                                    <td style="font-size:11px;">{{ $item->item_text }}</td>
                                                                    @foreach($sheetTemplate->rating_columns as $col)
                                                                        <td class="text-center">
                                                                            @if(trim((string)$storedVal) === trim($col))
                                                                                <span class="cb-checked">&#10003;</span>
                                                                            @else
                                                                                <span class="cb-empty"></span>
                                                                            @endif
                                                                        </td>
                                                                    @endforeach
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endif

                                        @endforeach

                                    {{-- ══════════════════════════════════════════════════════
                                         PRIMARY & SECONDARY: Total / Grade only
                                         (1st Half and 2nd Half commented out — uncomment
                                          and change colspan to 7/8 to restore them)
                                         ══════════════════════════════════════════════════════ --}}
                                    @else

                                        <table class="table table-bordered table-striped table-sm mb-4 results-table">
                                            <thead class="text-center bg-light">
                                                {{-- Row 1: fixed columns + subject name colspan --}}
                                                <tr>
                                                    <th rowspan="2" style="vertical-align:middle; white-space:nowrap;">S/N</th>
                                                    <th rowspan="2" style="vertical-align:middle; min-width:130px;">Student Name</th>
                                                    <th rowspan="2" style="vertical-align:middle; white-space:nowrap;">Adm No</th>
                                                    @foreach($subjects as $subject)
                                                        {{-- colspan=3 for secondary (Total Obtainable + Total Obtained + Grade)  --}}
                                                        {{-- colspan=3 for primary  (Total Obtainable + Total Obtained + Remark)  --}}
                                                        {{-- Change to 7 / 8 when restoring 1st & 2nd Half columns               --}}
                                                        <th colspan="3" style="font-size:10px; white-space:nowrap;">
                                                            {{ $subject->course_name }}
                                                        </th>
                                                    @endforeach
                                                </tr>
                                                {{-- Row 2: sub-headers per subject --}}
                                                <tr>
                                                    @foreach($subjects as $subject)
                                                        {{-- 1st Half — uncomment to restore
                                                        <th style="font-size:9px; min-width:28px;">1st<br>Obtainable</th>
                                                        <th style="font-size:9px; min-width:28px;">1st<br>Obtained</th>
                                                        --}}
                                                        {{-- 2nd Half — uncomment to restore
                                                        <th style="font-size:9px; min-width:28px;">2nd<br>Obtainable</th>
                                                        <th style="font-size:9px; min-width:28px;">2nd<br>Obtained</th>
                                                        --}}
                                                        <th style="font-size:9px; min-width:28px;">Total<br>Obtainable</th>
                                                        <th style="font-size:9px; min-width:28px;">Total<br>Obtained</th>
                                                        @if(!$isPrimary)
                                                            <th style="font-size:9px; min-width:28px;">Grade</th>
                                                        @else
                                                            <th style="font-size:9px; min-width:40px;">Remark</th>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($students as $index => $student)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>{{ $student->name }}</td>
                                                        <td class="text-center">{{ $student->admission_no }}</td>

                                                        @foreach($subjects as $subject)
                                                            @php
                                                                $r               = $resultsMatrix[$student->id][$subject->id] ?? null;
                                                                // $firstObtainable  = $r?->first_half_obtainable  ?? 30;  // uncomment to restore
                                                                // $firstObtained    = $r?->first_half_obtained    ?? 0;   // uncomment to restore
                                                                // $secondObtainable = $r?->second_half_obtainable ?? 70;  // uncomment to restore
                                                                // $secondObtained   = $r?->second_half_obtained   ?? 0;   // uncomment to restore
                                                                $finalObtainable = $r?->final_obtainable ?? 100;
                                                                $finalObtained   = $r?->final_obtained   ?? 0;
                                                                $grade           = $r?->grade            ?? '-';
                                                                $teacherRemark   = $r?->teacher_remark   ?? '-';
                                                            @endphp

                                                            {{-- 1st Half cells — uncomment to restore
                                                            <td class="text-center">{{ $firstObtainable }}</td>
                                                            <td class="text-center">{{ $firstObtained ?: '-' }}</td>
                                                            --}}
                                                            {{-- 2nd Half cells — uncomment to restore
                                                            <td class="text-center">{{ $secondObtainable }}</td>
                                                            <td class="text-center">{{ $secondObtained ?: '-' }}</td>
                                                            --}}
                                                            <td class="text-center">{{ $finalObtainable }}</td>
                                                            <td class="text-center font-weight-bold">{{ $finalObtained ?: '-' }}</td>

                                                            @if($isPrimary)
                                                                <td class="text-center" style="font-size:10px;">{{ $teacherRemark }}</td>
                                                            @else
                                                                <td class="text-center">
                                                                    <span class="badge
                                                                        @if($grade == 'A') badge-success
                                                                        @elseif($grade == 'B') badge-info
                                                                        @elseif($grade == 'C') badge-primary
                                                                        @elseif($grade == 'D') badge-warning
                                                                        @elseif($grade == 'E') badge-secondary
                                                                        @else badge-danger @endif">
                                                                        {{ $grade }}
                                                                    </span>
                                                                </td>
                                                            @endif

                                                        @endforeach
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="{{ 3 + ($subjects->count() * 3) }}"
                                                            class="text-center">
                                                            No students found in this class.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                    @endif
                                    {{-- end nursery / primary / secondary --}}

                                </div>
                            </div>
                            {{-- /printable-area --}}

                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <style>

        /* ── Horizontal scroll on screen — nothing gets clipped ── */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ── Results table: never collapse columns on screen ── */
        .results-table {
            min-width: max-content;
        }

        /* ── Nursery subject / subcat headings ── */
        .subject-heading {
            border-left: 4px solid #333;
            background: #f5f5f5;
            padding: 5px 8px;
            font-size: 13px;
        }
        .subcat-heading {
            font-size: 12px;
            padding-left: 14px;
            font-weight: 600;
            color: #444;
        }

        /* ── Nursery checkbox cells ── */
        .cb-checked,
        .cb-empty {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1.5px solid #555;
            border-radius: 2px;
            text-align: center;
            line-height: 14px;
            font-size: 13px;
            font-weight: bold;
            vertical-align: middle;
            background: #fff;
        }
        .cb-checked {
            border-color: #000;
            color: #000;
        }

        /* ── Badge ── */
        .badge { font-size: 0.85em; padding: 4px 8px; }

        /* ══════════════════════════════════════════════════
           PRINT
           ══════════════════════════════════════════════════ */
        @media print {

            @page {
                size: A3 landscape;
                margin: 6mm;
            }

            body * { visibility: hidden; }

            .printable-area,
            .printable-area * { visibility: visible; }

            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print   { display: none !important; }
            .print-only { display: block !important; }

            /* ── Results table on print ── */
            .results-table {
                font-size: 7px !important;
                width: 100% !important;
                min-width: unset !important;
                table-layout: fixed !important;
            }
            .results-table th,
            .results-table td {
                border: 1px solid #000 !important;
                padding: 2px 1px !important;
                word-wrap: break-word;
                overflow-wrap: break-word;
                white-space: normal !important;
            }
            /* Student name column */
            .results-table td:nth-child(2),
            .results-table th:nth-child(2) {
                min-width: 70px;
                max-width: 90px;
            }
            /* All score/grade columns */
            .results-table td:not(:nth-child(1)):not(:nth-child(2)):not(:nth-child(3)),
            .results-table th:not(:nth-child(1)):not(:nth-child(2)):not(:nth-child(3)) {
                min-width: 16px;
                max-width: 30px;
                text-align: center !important;
            }
            .results-table tbody tr { page-break-inside: avoid; }

            .bg-light {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
            }
            .badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-size: 7px !important;
                padding: 1px 2px !important;
            }

            /* ── Nursery headings ── */
            .subject-heading {
                font-size: 9px !important;
                margin: 4px 0 2px 0 !important;
                padding: 3px 6px !important;
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }
            .subcat-heading {
                font-size: 8px !important;
                margin: 2px 0 1px 0 !important;
            }

            /* ── Nursery table ── */
            .nursery-table {
                font-size: 8px !important;
                width: 100% !important;
                table-layout: auto !important;
            }
            .nursery-table th,
            .nursery-table td {
                border: 1px solid #000 !important;
                padding: 2px 3px !important;
                white-space: normal !important;
                word-wrap: break-word;
            }
            .nursery-table tbody tr { page-break-inside: avoid; }

            /* Student banner row in nursery */
            .student-banner td {
                font-size: 8px !important;
                background-color: #e9e9e9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* ── Nursery checkboxes ── */
            .cb-checked,
            .cb-empty {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 1.5px solid #555 !important;
                display: inline-block !important;
                width: 12px !important;
                height: 12px !important;
                line-height: 11px !important;
                font-size: 11px !important;
                background: #fff !important;
            }
            .cb-checked {
                border-color: #000 !important;
                color: #000 !important;
            }

            /* ── Hide chrome ── */
            .navbar-bg,
            .main-sidebar,
            .navbar,
            .main-footer,
            .loader {
                display: none !important;
            }
        }

        @media screen {
            .print-only { display: none; }
        }

    </style>
</body>