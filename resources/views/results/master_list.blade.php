@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <!-- Main Content -->
            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Master List - {{ $class->name }} ({{ $section->section_name }})</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('results.exportMasterList', [
                                        'class' => $class->id,
                                        'session_id' => $selectedSession->id,
                                        'term_id' => $selectedTerm->id
                                    ]) }}" class="btn btn-success mr-2">
                                        <i class="fas fa-file-excel"></i> Export to Excel
                                    </a>
                                    <button onclick="window.print()" class="btn btn-primary">
                                        <i class="fas fa-print"></i> Print Master List
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Session and Term Selection - Screen Only -->
                                <form method="GET" action="{{ route('results.masterList', $class->id) }}" class="mb-4 no-print">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Academic Session</label>
                                            <select name="session_id" class="form-control" onchange="this.form.submit()">
                                                @foreach($sessions as $session)
                                                <option value="{{ $session->id }}" {{ $selectedSession->id == $session->id ? 'selected' : '' }}>
                                                    {{ $session->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Term</label>
                                            <select name="term_id" class="form-control" onchange="this.form.submit()">
                                                @foreach($terms as $term)
                                                <option value="{{ $term->id }}" {{ $selectedTerm->id == $term->id ? 'selected' : '' }}>
                                                    {{ $term->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </form>

                                <!-- Session and Term Display - Print Only -->
                                <div class="row mb-3 print-only" style="display: none;">
                                    <div class="col-md-12">
                                        <p><strong>Academic Session:</strong> {{ $selectedSession->name }} | <strong>Term:</strong> {{ $selectedTerm->name }}</p>
                                    </div>
                                </div>

                                <!-- Master List Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover" style="font-size: 11px;">
                                        <thead class="">
                                            <tr>
                                                <th rowspan="2" style="vertical-align: middle;">Pos</th>
                                                <th rowspan="2" style="vertical-align: middle;">Admission No</th>
                                                <th rowspan="2" style="vertical-align: middle; min-width: 150px;">Student Name</th>
                                                <th colspan="{{ $subjects->count() }}" class="text-center">SUBJECTS</th>
                                                <th rowspan="2" style="vertical-align: middle;">Total</th>
                                                <th rowspan="2" style="vertical-align: middle;">Avg</th>
                                                <th rowspan="2" style="vertical-align: middle;">Grade</th>
                                            </tr>
                                            <tr>
                                                @foreach($subjects as $subject)
                                                <th style="font-size: 9px; writing-mode: vertical-lr; text-orientation: mixed; min-width: 25px;" title="{{ $subject->course_name }}">
                                                    {{ strlen($subject->course_name) > 7 ? substr($subject->course_name, 0, 7) . '...' : $subject->course_name }}
                                                </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($sortedStudents as $studentData)
                                            @php
                                                $student = $studentData['student'];
                                                $studentResults = $results->get($student->id, collect());
                                            @endphp
                                            <tr>
                                                <td class="text-center font-weight-bold">{{ $studentData['position'] }}</td>
                                                <td>{{ $student->admission_no }}</td>
                                                <td>{{ strtoupper($student->name) }}</td>
                                                
                                                @foreach($subjects as $subject)
                                                    @php
                                                        $result = $studentResults->firstWhere('course_id', $subject->id);
                                                        $total = $result?->total ?? 0;
                                                    @endphp
                                                    <td class="text-center {{ $total == 0 ? 'bg-light' : '' }}">
                                                        {{ $total > 0 ? $total : '-' }}
                                                    </td>
                                                @endforeach
                                                
                                                <td class="text-center font-weight-bold">{{ $studentData['total_score'] }}</td>
                                                <td class="text-center font-weight-bold">{{ $studentData['average'] }}</td>
                                                <td class="text-center font-weight-bold">{{ $studentData['grade'] }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="{{ 6 + $subjects->count() }}" class="text-center">
                                                    No students found in this class.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Summary Statistics -->
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0">Class Statistics</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <strong>Total Students:</strong> {{ $students->count() }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Total Subjects:</strong> {{ $subjects->count() }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Class Average:</strong> {{ $sortedStudents->avg('average') ? round($sortedStudents->avg('average'), 2) : 0 }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Highest Score:</strong> {{ $sortedStudents->first()['total_score'] ?? 0 }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <!-- Print Styles -->
    <style>
        @media print {
            .main-sidebar, .navbar, .card-header-action, .loader, footer, .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .main-content {
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            table {
                font-size: 9px !important;
            }
            @page {
                size: landscape;
                margin: 10mm;
            }
        }
    </style>
</body>
</html>