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
                                         NURSERY: rating columns per subject item
                                         ══════════════════════════════════════════════════════ --}}
                                    @if($isNursery)

                                        @foreach($subjects as $subject)
                                            <h6 class="mt-4 mb-1 font-weight-bold">
                                                ({{ $subject->subject_number }}) {{ $subject->subject_name }}
                                            </h6>

                                            @foreach($subject->subcategories as $sub)
                                                @if($sub->name)
                                                    <p class="mb-1 text-muted" style="font-size:12px; padding-left:10px;">
                                                        <em>{{ $sub->name }}</em>
                                                    </p>
                                                @endif

                                                <table class="table table-bordered table-sm mb-2" style="font-size:12px;">
                                                    <thead class="text-center bg-light">
                                                        <tr>
                                                            <th>S/N</th>
                                                            <th>Student Name</th>
                                                            <th>Adm No</th>
                                                            @foreach($sub->items as $item)
                                                                <th title="{{ $item->item_text }}"
                                                                    style="max-width:80px; white-space:normal; font-size:10px;">
                                                                    {{ Str::limit($item->item_text, 30) }}
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($students as $index => $student)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $student->name }}</td>
                                                                <td>{{ $student->admission_no }}</td>
                                                                @foreach($sub->items as $item)
                                                                    @php
                                                                        $val = $ratingsMatrix[$student->id][$item->id] ?? null;
                                                                    @endphp
                                                                    <td class="text-center">
                                                                        @if($val)
                                                                            <span class="badge badge-primary">{{ $val }}</span>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="{{ 3 + $sub->items->count() }}" class="text-center">
                                                                    No students found.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            @endforeach

                                            {{-- Direct items on the subject (no subcategory) --}}
                                            @if(count($subject->items) > 0)
                                                <table class="table table-bordered table-sm mb-2" style="font-size:12px;">
                                                    <thead class="text-center bg-light">
                                                        <tr>
                                                            <th>S/N</th>
                                                            <th>Student Name</th>
                                                            <th>Adm No</th>
                                                            @foreach($subject->items as $item)
                                                                <th title="{{ $item->item_text }}"
                                                                    style="max-width:80px; white-space:normal; font-size:10px;">
                                                                    {{ Str::limit($item->item_text, 30) }}
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($students as $index => $student)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $student->name }}</td>
                                                                <td>{{ $student->admission_no }}</td>
                                                                @foreach($subject->items as $item)
                                                                    @php
                                                                        $val = $ratingsMatrix[$student->id][$item->id] ?? null;
                                                                    @endphp
                                                                    <td class="text-center">
                                                                        @if($val)
                                                                            <span class="badge badge-primary">{{ $val }}</span>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="{{ 3 + count($subject->items) }}" class="text-center">
                                                                    No students found.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            @endif

                                        @endforeach

                                    {{-- ══════════════════════════════════════════════════════
                                         PRIMARY & SECONDARY: 1st Half / 2nd Half / Total / Grade
                                         ══════════════════════════════════════════════════════ --}}
                                    @else

                                        <table class="table table-bordered table-striped table-sm mb-4 pb-4">
                                            <thead class="text-center bg-light">
                                                <tr>
                                                    <th rowspan="3">S/N</th>
                                                    <th rowspan="3">Student Name</th>
                                                    <th rowspan="3">Adm No</th>
                                                    @foreach($subjects as $subject)
                                                        <th colspan="{{ $isPrimary ? 7 : 8 }}">
                                                            {{ $subject->course_name }}
                                                        </th>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    @foreach($subjects as $subject)
                                                        <th colspan="2" style="font-size:10px;">1st Half (Max 30)</th>
                                                        <th colspan="2" style="font-size:10px;">2nd Half (Max 70)</th>
                                                        <th colspan="2" style="font-size:10px;">Total (Max 100)</th>
                                                        @if(!$isPrimary)
                                                            <th rowspan="2" style="font-size:10px; vertical-align:middle;">Grade</th>
                                                        @else
                                                            <th rowspan="2" style="font-size:10px; vertical-align:middle;">Remark</th>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    @foreach($subjects as $subject)
                                                        <th style="font-size:9px;">Obtainable</th>
                                                        <th style="font-size:9px;">Obtained</th>
                                                        <th style="font-size:9px;">Obtainable</th>
                                                        <th style="font-size:9px;">Obtained</th>
                                                        <th style="font-size:9px;">Obtainable</th>
                                                        <th style="font-size:9px;">Obtained</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($students as $index => $student)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $student->name }}</td>
                                                        <td>{{ $student->admission_no }}</td>

                                                        @foreach($subjects as $subject)
                                                            @php
                                                                $r = $resultsMatrix[$student->id][$subject->id] ?? null;
                                                                $firstObtainable  = $r?->first_half_obtainable  ?? 30;
                                                                $firstObtained    = $r?->first_half_obtained    ?? 0;
                                                                $secondObtainable = $r?->second_half_obtainable ?? 70;
                                                                $secondObtained   = $r?->second_half_obtained   ?? 0;
                                                                $finalObtainable  = $r?->final_obtainable       ?? 100;
                                                                $finalObtained    = $r?->final_obtained         ?? 0;
                                                                $grade            = $r?->grade                  ?? '-';
                                                                $teacherRemark    = $r?->teacher_remark         ?? '-';
                                                            @endphp

                                                            <td class="text-center">{{ $firstObtainable }}</td>
                                                            <td class="text-center">{{ $firstObtained ?: '-' }}</td>
                                                            <td class="text-center">{{ $secondObtainable }}</td>
                                                            <td class="text-center">{{ $secondObtained ?: '-' }}</td>
                                                            <td class="text-center">{{ $finalObtainable }}</td>
                                                            <td class="text-center font-weight-bold">{{ $finalObtained ?: '-' }}</td>

                                                            @if($isPrimary)
                                                                <td class="text-center">{{ $teacherRemark }}</td>
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
                                                        <td colspan="{{ 3 + ($subjects->count() * ($isPrimary ? 7 : 8)) }}"
                                                            class="text-center">
                                                            No students found in this class.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                    @endif
                                    {{-- end primary/secondary table --}}

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
        @media print {
            body * { visibility: hidden; }
            .printable-area, .printable-area * { visibility: visible; }
            .printable-area { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .table { font-size: 9px; }
            .table th, .table td { border: 1px solid #000 !important; padding: 3px !important; }
            .bg-light { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
            .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media screen { .print-only { display: none; } }
        .badge { font-size: 0.85em; padding: 4px 8px; }
    </style>
</body>