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
                                <h4>Cumulative Results - {{ $class->name }} ({{ $section->section_name }})</h4>
                                <div class="card-header-action">
                                    <button onclick="window.print()" class="btn btn-primary">
                                        <i class="fas fa-print"></i> Print Cumulative Results
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Session Selection - Screen Only -->
                                <form method="GET" action="{{ route('results.cumulative', $class->id) }}" class="mb-4 no-print">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Academic Session</label>
                                            <select name="session_id" class="form-control" onchange="this.form.submit()">
                                                @foreach($sessions as $session)
                                                <option value="{{ $session->id }}" {{ $selectedSession->id == $session->id ? 'selected' : '' }}>
                                                    {{ $session->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </form>

                                <!-- Session Display - Print Only -->
                                <div class="row mb-3 print-only" style="display: none;">
                                    <div class="col-md-12">
                                        <p><strong>Academic Session:</strong> {{ $selectedSession->name }}</p>
                                        <p><strong>Terms Included:</strong> {{ $terms->pluck('name')->implode(', ') }}</p>
                                    </div>
                                </div>

                                <!-- Cumulative Results Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover" style="font-size: 11px;">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="vertical-align: middle;">Pos</th>
                                                <th rowspan="2" style="vertical-align: middle;">Admission No</th>
                                                <th rowspan="2" style="vertical-align: middle; min-width: 150px;">Student Name</th>
                                                @foreach($terms as $term)
                                                <th class="text-center">{{ $term->name }}</th>
                                                @endforeach
                                                <th rowspan="2" style="vertical-align: middle;">Cumulative Total</th>
                                                <th rowspan="2" style="vertical-align: middle;">Cumulative Avg</th>
                                                <th rowspan="2" style="vertical-align: middle;">Grade</th>
                                            </tr>
                                            <tr>
                                                @foreach($terms as $term)
                                                <th class="text-center">Total</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($sortedStudents as $studentData)
                                            @php
                                                $student = $studentData['student'];
                                            @endphp
                                            <tr>
                                                <td class="text-center font-weight-bold">{{ $studentData['position'] }}</td>
                                                <td>{{ $student->admission_no }}</td>
                                                <td>{{ strtoupper($student->name) }}</td>
                                                
                                                @foreach($terms as $term)
                                                    <td class="text-center">
                                                        {{ $studentData['term_totals'][$term->id] ?? 0 }}
                                                    </td>
                                                @endforeach
                                                
                                                <td class="text-center font-weight-bold bg-light">{{ $studentData['cumulative_total'] }}</td>
                                                <td class="text-center font-weight-bold bg-light">{{ $studentData['cumulative_average'] }}</td>
                                                <td class="text-center font-weight-bold bg-light">{{ $studentData['grade'] }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="{{ 6 + $terms->count() }}" class="text-center">
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
                                                <h6 class="mb-0">Cumulative Statistics</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <strong>Total Students:</strong> {{ $students->count() }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Terms Covered:</strong> {{ $terms->count() }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Class Average:</strong> {{ $sortedStudents->avg('cumulative_average') ? round($sortedStudents->avg('cumulative_average'), 2) : 0 }}%
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Highest Score:</strong> {{ $sortedStudents->first()['cumulative_total'] ?? 0 }}
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