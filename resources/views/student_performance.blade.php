{{-- resources/views/student_performance.blade.php --}}
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
                                <h4>Performance Report for {{ $student->name }} ({{ $student->admission_no }})</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('students.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Manage Students
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filters for Term and Subject -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <form method="GET" action="{{ route('students.performance', $student->id) }}" class="row">
                                            <div class="form-group col-md-3">
                                                <label>Term</label>
                                                <select class="form-control" name="term">
                                                    <option value="">All Terms</option>
                                                    <option value="Term 1" {{ ($term ?? '') == 'Term 1' ? 'selected' : '' }}>Term 1</option>
                                                    <option value="Term 2" {{ ($term ?? '') == 'Term 2' ? 'selected' : '' }}>Term 2</option>
                                                    <option value="Term 3" {{ ($term ?? '') == 'Term 3' ? 'selected' : '' }}>Term 3</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Subject</label>
                                                <select class="form-control" name="subject_id">
                                                    <option value="">All Subjects</option>
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject->id }}" {{ ($subjectId ?? '') == $subject->id ? 'selected' : '' }}>{{ $subject->course_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Filter
                                                </button>
                                            </div>
                                            <div class="form-group col-md-3 d-flex align-items-end">
                                                <a href="{{ route('students.performance', $student->id) }}" class="btn btn-light">
                                                    <i class="fas fa-sync"></i> Reset
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Student Summary -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <strong>Class:</strong> {{ $student->class->name ?? 'Not Assigned' }}<br>
                                        <strong>Overall Average:</strong> {{ number_format($average, 2) }}%
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Term:</strong> {{ $term ?? 'All' }}<br>
                                        <strong>Total Subjects:</strong> {{ $results->count() }}
                                    </div>
                                </div>

                                <!-- Performance Trend Graph -->
                                @if($results->isNotEmpty() && !empty($chartLabels) && count($chartLabels) > 0)
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="mb-3">Performance Trend Over Time</h5>
                                                <div style="position: relative; height: 400px;">
                                                    <canvas id="performanceChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Performance Table -->
                                @if($results->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>CA</th>
                                                <th>Test</th>
                                                <th>Exam</th>
                                                <th>Total</th>
                                                <th>Grade</th>
                                                <th>Comment</th>
                                                <th>Uploaded By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($results as $result)
                                            <tr>
                                                <td>{{ $result->course->course_name ?? 'N/A' }}</td>
                                                <td>{{ $result->ca ?? 'N/A' }}</td>
                                                <td>{{ $result->test ?? 'N/A' }}</td>
                                                <td>{{ $result->exam ?? 'N/A' }}</td>
                                                <td>{{ $result->total ?? 'N/A' }}</td>
                                                <td>{{ $result->grade ?? 'N/A' }}</td>
                                                <td>{{ $result->comment ?? '-' }}</td>
                                                <td>{{ $result->uploader->name ?? 'N/A' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No performance records found for this student in the selected term and subject.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            @include('includes.edit_footer')
        </div>
    </div>

    <!-- Chart.js Script -->
    @if($results->isNotEmpty() && !empty($chartLabels) && count($chartLabels) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('performanceChart');
            
            if (ctx) {
                const chartLabels = @json($chartLabels);
                const chartData = @json($chartData);
                
                // Verify data exists
                if (!chartLabels || !chartData || chartLabels.length === 0 || chartData.length === 0) {
                    console.error('Chart data is empty or undefined');
                    return;
                }
                
                const performanceChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Average Total Score',
                            data: chartData,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointBackgroundColor: 'rgb(75, 192, 192)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Average Score (%)',
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month',
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Student Performance Trend',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Average Score: ' + context.parsed.y.toFixed(2) + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif
</body>
</html>