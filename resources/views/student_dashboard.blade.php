@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <!-- Main Content -->
            <div class="main-content container-fluid mt-0" style="padding-top:100px;">
                <section class="section mb-5 pb-5">

                    <!-- Welcome Header with Current Session/Term -->
                    <div class="row mb-1">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="mb-0">Welcome, {{ $student->name }}!</h5>
                                    <div class="d-flex flex-wrap gap-3">
                                        @if($student->class)
                                        <div class="m-1">
                                            <i class="fas fa-school mr-1"></i>
                                            <strong>Class:</strong> {{ $student->class->name }}
                                            @if($student->class->section)
                                            ({{ $student->class->section->section_name }})
                                            @endif
                                        </div>
                                        @endif

                                        @if($student->hostel)
                                        <div class="m-1">
                                            <i class="fas fa-home mr-1"></i>
                                            <strong>Hostel:</strong> {{ $student->hostel->name }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dashboard Cards -->
                    <div class="row">
                        <!-- Attendance Rate -->
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    @if($currentSession)
                                                    <div class="m-1">
                                                        <h5 class="font-15">Session: {{ $currentSession->name }}</h5>

                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-calendar-alt mr-1 fa-3x"
                                                    style="font-size: 2.5rem; color: {{ ($attendanceStats && $attendanceStats['total_days'] > 0 && $attendanceStats['attendance_rate'] >= 75) ? '#28a745' : '#dc3545' }};"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Subjects -->
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Enrolled Subjects</h5>
                                                    <h2 class="mb-3 font-18">{{ $enrolledSubjects->count() }}</h2>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-book fa-3x text-info" style="font-size: 2.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Assessments -->
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    @if($currentTerm)
                                                    <h5 class="font-15">Term</h5>
                                                    <h2 class="mb-3 font-18"> {{ $currentTerm->name }}</h2>
                                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">This term</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-clock mr-1 fa-3x text-warning"
                                                    style="font-size: 2.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Average Grade -->
                        {{-- <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Average Grade</h5>
                                                    <h2 class="mb-3 font-18">{{ $letterGrade }}</h2>
                                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                                        {{ $averageScore }}%
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-graduation-cap fa-3x text-success"
                                                    style="font-size: 2.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>

                    <!-- Recent Grades Chart -->
                    @if(!empty($chartLabels) && !empty($chartData))
                    {{-- <div class="row">
                        <div class="col-12 col-sm-12 col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Recent Grades Trend</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="gradeChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    @endif

                    <!-- Recent Grades Table -->
                    {{-- <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Recent Assessment Results</h4>
                                </div>
                                <div class="card-body p-0 px-4">
                                    @if($recentGrades->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Score</th>
                                                    <th>Grade</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentGrades as $grade)
                                                <tr>
                                                    <td>{{ $grade->course->course_name ?? 'N/A' }}</td>
                                                    <td>{{ $grade->total }}/100</td>
                                                    <td>
                                                        @php
                                                        $score = $grade->total;
                                                        $letterGradeItem = 'F';
                                                        if ($score >= 90) $letterGradeItem = 'A+';
                                                        elseif ($score >= 85) $letterGradeItem = 'A';
                                                        elseif ($score >= 80) $letterGradeItem = 'A-';
                                                        elseif ($score >= 75) $letterGradeItem = 'B+';
                                                        elseif ($score >= 70) $letterGradeItem = 'B';
                                                        elseif ($score >= 65) $letterGradeItem = 'C+';
                                                        elseif ($score >= 60) $letterGradeItem = 'C';
                                                        elseif ($score >= 55) $letterGradeItem = 'D';
                                                        @endphp
                                                        <span
                                                            class="badge badge-{{ $score >= 60 ? 'success' : 'danger' }}">
                                                            {{ $letterGradeItem }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $grade->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        @if($grade->total >= 60)
                                                        <span class="text-success"><i class="fas fa-check-circle"></i>
                                                            Passed</span>
                                                        @else
                                                        <span class="text-danger"><i class="fas fa-times-circle"></i>
                                                            Failed</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="py-5 text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No assessment results available yet.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <!-- Enrolled Subjects -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>My Subjects</h4>
                                </div>
                                <div class="card-body">
                                    @if($enrolledSubjects->count() > 0)
                                    <div class="row">
                                        @foreach($enrolledSubjects as $subject)
                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-book-open mr-2 text-primary"></i>
                                                        {{ $subject->course_name }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="text-center text-muted py-3">
                                        <p>No subjects enrolled yet.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
            @if(!empty($chartLabels) && !empty($chartData))
            <script>
                const gradeLabels = @json($chartLabels);
                const grades = @json($chartData);

                const ctx = document.getElementById('gradeChart').getContext('2d');
                const gradeChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: gradeLabels,
                        datasets: [{
                            label: 'Score (%)',
                            data: grades,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        return 'Score: ' + context.parsed.y + '%';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Score (%)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Subjects'
                                }
                            }
                        }
                    }
                });
            </script>
            @endif

            @include('includes.edit_footer')
        </div>
    </div>
</body>