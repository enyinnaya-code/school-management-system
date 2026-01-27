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

                            <!-- Header Info (Visible on screen and print) -->
                            <div class="section-header d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="mb-1">Class Results: {{ $class->name }}</h5>
                                    <p class="mb-0 text-muted">
                                        <strong>{{ $selectedTerm->name }}</strong> —
                                        <strong>{{ $selectedSession->name }}</strong>
                                    </p>
                                </div>

                                <!-- Print & Export Buttons - Hidden when printing -->
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

                            <!-- Filter Form - Hidden when printing -->
                            <div class="card mb-4 no-print">
                                <div class="card-body">
                                    <form method="GET" action="{{ route('results.class.view', $class->id) }}">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <label for="session_id" class="form-label">Session</label>
                                                <select name="session_id" id="session_id" class="form-control"
                                                    onchange="this.form.submit()">
                                                    @foreach($sessions as $session)
                                                    <option value="{{ $session->id }}" {{ $selectedSession->id ==
                                                        $session->id ? 'selected' : '' }}>
                                                        {{ $session->name }} {{ $session->is_current ? '(Current)' : ''
                                                        }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label for="term_id" class="form-label">Term</label>
                                                <select name="term_id" id="term_id" class="form-control"
                                                    onchange="this.form.submit()">
                                                    @foreach($terms as $term)
                                                    <option value="{{ $term->id }}" {{ $selectedTerm->id == $term->id ?
                                                        'selected' : '' }}>
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

                            <!-- Printable Area: Only this will show nicely when printing -->
                            <div class="printable-area">
                                <!-- Repeat header for print -->
                                <div class="text-center mb-4 print-only">
                                    <h4>{{ $class->name }} Results</h4>
                                    <h5>{{ $selectedSession->name }} — {{ $selectedTerm->name }}</h5>
                                    <hr>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-sm mb-4 pb-4">
                                        <thead class="text-center bg-light">
                                            <tr>
                                                <th rowspan="2">S/N</th>
                                                <th rowspan="2">Student Name</th>
                                                <th rowspan="2">Adm No</th>

                                                @foreach($subjects as $subject)
                                                <th colspan="6">{{ $subject->course_name }}</th>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @foreach($subjects as $subject)
                                                <th>CA1</th>
                                                <th>CA2</th>
                                                <th>Mid</th>
                                                <th>Exam</th>
                                                <th>Total</th>
                                                <th>Grade</th>
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
                                                $result = $resultsMatrix[$student->id][$subject->id][0] ?? null;

                                                $ca1 = $result?->first_ca ?? 0;
                                                $ca2 = $result?->second_ca ?? 0;
                                                $mid = $result?->mid_term_test ?? 0;
                                                $exam = $result?->examination ?? 0;
                                                $total = $result?->total ?? 0;
                                                $grade = $result?->grade ?? 'F';
                                                @endphp

                                                <td class="text-center">{{ $ca1 ?: '-' }}</td>
                                                <td class="text-center">{{ $ca2 ?: '-' }}</td>
                                                <td class="text-center">{{ $mid ?: '-' }}</td>
                                                <td class="text-center">{{ $exam ?: '-' }}</td>
                                                <td class="text-center font-weight-bold">{{ $total ?: '-' }}</td>
                                                <td class="text-center">
                                                    <span class="badge 
                                                        @if($grade == 'A') badge-success
                                                        @elseif($grade == 'B') badge-info
                                                        @elseif($grade == 'C') badge-primary
                                                        @elseif($grade == 'D') badge-warning
                                                        @elseif($grade == 'E') badge-secondary
                                                        @else badge-danger @endif
                                                    ">
                                                        {{ $grade }}
                                                    </span>
                                                </td>
                                                @endforeach
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="{{ 3 + ($subjects->count() * 6) }}"
                                                    class="text-center">
                                                    No students found in this class.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <!-- Print-specific CSS -->
    <style>
        @media print {

            /* Hide everything except the printable area */
            body * {
                visibility: hidden;
            }

            .printable-area,
            .printable-area * {
                visibility: visible;
            }

            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Hide elements we don't want in print */
            .no-print {
                display: none !important;
            }

            /* Show print-only header */
            .print-only {
                display: block !important;
            }

            /* Better table styling for print */
            .table {
                font-size: 10px;
            }

            .table th,
            .table td {
                border: 1px solid #000 !important;
                padding: 4px !important;
            }

            .bg-light {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
            }

            .badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Hide print-only header on screen */
        @media screen {
            .print-only {
                display: none;
            }
        }

        .badge {
            font-size: 0.85em;
            padding: 4px 8px;
        }
    </style>
</body>