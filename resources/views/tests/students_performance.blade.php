@include('includes.head')

<style>
    .stat-card { border-radius: 10px; padding: 20px; color: white; text-align: center; }
    .stat-card h2 { font-size: 2rem; font-weight: 700; margin: 0; }
    .stat-card p  { margin: 0; font-size: 13px; opacity: 0.9; }
    .bg-took   { background: linear-gradient(135deg, #28a745, #20c997); }
    .bg-missed { background: linear-gradient(135deg, #dc3545, #e74c3c); }
    .bg-passed { background: linear-gradient(135deg, #007bff, #6610f2); }
    .bg-failed { background: linear-gradient(135deg, #fd7e14, #ffc107); }
    .bg-avg    { background: linear-gradient(135deg, #17a2b8, #20c997); }
    .progress  { height: 8px; border-radius: 4px; }
    .badge-position {
        background: #6c757d; color: white;
        padding: 3px 8px; border-radius: 12px; font-size: 11px;
    }
</style>

<body>
<div class="loader"></div>
<div id="app">
    <div class="main-wrapper main-wrapper-1">
        <div class="navbar-bg"></div>
        @include('includes.right_top_nav')
        @include('includes.side_nav')

        <div class="main-content pt-5 mt-5">
            <section class="section mb-5 pb-1 px-0">
                <div class="col-12">

                    {{-- Page Header --}}
                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 font-weight-bold">{{ $test->test_name }}</h5>
                                    <small class="text-muted">
                                        Subject: {{ $test->course->course_name ?? '-' }} |
                                        Duration: {{ $test->duration }} min |
                                        Total Marks: {{ $totalMarks }} |
                                        Pass Mark: {{ $test->pass_mark }}
                                    </small>
                                </div>
                                <a href="{{ route('tests.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Summary Stats --}}
                    <div class="row mb-4">
                        <div class="col-6 col-md-2 mb-3">
                            <div class="stat-card bg-took">
                                <h2>{{ $didTest->count() }}</h2>
                                <p>Took Test</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 mb-3">
                            <div class="stat-card bg-missed">
                                <h2>{{ $didNotTest->count() }}</h2>
                                <p>Did Not Take</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 mb-3">
                            <div class="stat-card bg-passed">
                                <h2>{{ $passCount }}</h2>
                                <p>Passed</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 mb-3">
                            <div class="stat-card bg-failed">
                                <h2>{{ $failCount }}</h2>
                                <p>Failed</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 mb-3">
                            <div class="stat-card bg-avg">
                                <h2>{{ $avgScore }}</h2>
                                <p>Avg Score</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 mb-3">
                            <div class="stat-card"
                                 style="background: linear-gradient(135deg,#343a40,#6c757d);">
                                <h2>{{ $highScore }}/{{ $lowScore }}</h2>
                                <p>High / Low</p>
                            </div>
                        </div>
                    </div>

                    {{-- Students Who Took the Test --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle text-success"></i>
                                Students Who Took the Test ({{ $didTest->count() }})
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if($didTest->isEmpty())
                                <p class="p-3 text-muted">No students have taken this test yet.</p>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Student</th>
                                            <th>Admission No</th>
                                            <th>Class</th>
                                            <th>Score</th>
                                            <th>Percentage</th>
                                            <th>Result</th>
                                            <th>Time Spent</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($didTest as $item)
                                        @php
                                            $timeSpent = '-';
                                            if ($item['start_time'] && $item['end_time']) {
                                                $s = \Carbon\Carbon::parse($item['start_time']);
                                                $e = \Carbon\Carbon::parse($item['end_time']);
                                                $timeSpent = $s->diff($e)->format('%H:%I:%S');
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge-position">{{ $item['position'] }}</span>
                                            </td>
                                            <td class="font-weight-bold">{{ $item['student']->name }}</td>
                                            <td>{{ $item['student']->admission_no ?? '-' }}</td>
                                            <td>{{ $item['student']->class_name ?? '-' }}</td>
                                            <td>
                                                <strong>{{ $item['score'] }}</strong>
                                                <small class="text-muted">/ {{ $item['total_score'] }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2"
                                                         style="width:80px;">
                                                        <div class="progress-bar
                                                            {{ $item['percentage'] >= 50 ? 'bg-success' : 'bg-danger' }}"
                                                             style="width: {{ $item['percentage'] }}%">
                                                        </div>
                                                    </div>
                                                    <small>{{ $item['percentage'] }}%</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($item['is_passed'])
                                                    <span class="badge badge-success">Passed</span>
                                                @else
                                                    <span class="badge badge-danger">Failed</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $timeSpent }}</small></td>
                                            <td>
                                                <a href="{{ route('tests.studentIndepthAnalysis', [$test->id, $item['student']->id]) }}"
                                                   class="btn btn-sm btn-primary"
                                                   title="In-depth Analysis">
                                                    <i class="fas fa-chart-bar"></i> Analyse
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Students Who Did NOT Take the Test --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-times-circle text-danger"></i>
                                Students Who Did NOT Take the Test ({{ $didNotTest->count() }})
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if($didNotTest->isEmpty())
                                <p class="p-3 text-muted">All students have taken this test.</p>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Admission No</th>
                                            <th>Class</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($didNotTest as $i => $student)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td class="font-weight-bold text-danger">
                                                {{ $student->name }}
                                            </td>
                                            <td>{{ $student->admission_no ?? '-' }}</td>
                                            <td>{{ $student->class_name ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>
</div>
@include('includes.footer')
</body>