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
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="mb-0">Welcome back, {{ $teacher->name }}!</h5>
                                    <div class="d-flex flex-wrap gap-3 mt-2">
                                        <div class="m-1">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            <strong>Session:</strong>
                                            {{ $currentSession?->name ?? '<em class="text-muted">Not set</em>' }}
                                        </div>
                                        <div class="m-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            <strong>Term:</strong>
                                            {{ $currentTerm?->name ?? '<em class="text-muted">Not set</em>' }}
                                        </div>
                                        @if($formClass)
                                            <div class="m-1">
                                                <i class="fas fa-user-tie mr-1"></i>
                                                <strong>Form Class:</strong>
                                                {{ $formClass->name }}
                                                @if($formClass->section)
                                                    ({{ $formClass->section->section_name }})
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dashboard Cards -->
                    <div class="row">
                        <!-- Classes Taught -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 mb-1">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Classes Taught</h5>
                                                    <h2 class="mb-3 font-18">{{ $classesCount }}</h2>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-chalkboard-teacher fa-3x text-success" style="font-size: 2.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subjects Taught -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 mb-1">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Subjects Taught</h5>
                                                    <h2 class="mb-3 font-18">{{ $coursesCount }}</h2>
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

                        <!-- Form Teacher Status -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 mb-1">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Form Teacher</h5>
                                                    <h2 class="mb-3 font-18">
                                                        {{ $formClass ? 'Yes' : 'No' }}
                                                    </h2>
                                                    @if($formClass)
                                                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                                                            {{ $formClass->name }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-user-tie fa-3x {{ $formClass ? 'text-primary' : 'text-secondary' }}"
                                                   style="font-size: 2.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Placeholder for future metric (e.g., Results Entered) -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 mb-1">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Results Entered</h5>
                                                    <h2 class="mb-3 font-18">—</h2>
                                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">This term</p>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-file-alt fa-3x text-warning" style="font-size: 2.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Classes Currently Teaching -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Classes I Am Currently Teaching</h4>
                                </div>
                                <div class="card-body">
                                    @if($assignedClasses->count() > 0)
                                        <div class="row">
                                            @foreach($assignedClasses as $class)
                                                <div class="col-md-4 col-sm-6 mb-3">
                                                    <div class="card border">
                                                        <div class="card-body py-3">
                                                            <h6 class="mb-1">
                                                                <i class="fas fa-school text-primary mr-2"></i>
                                                                {{ $class->name ?? $class->class_name }}
                                                            </h6>
                                                            <small class="text-muted">
                                                                {{ $class->section->section_name ?? 'No Section' }}
                                                            </small>
                                                            <br>
                                                            <small class="text-muted">
                                                                Subjects:
                                                                @php
                                                                    $classCourses = $teacher->courses()->wherePivot('class_id', $class->id)->get();
                                                                @endphp
                                                                {{ $classCourses->pluck('course_name')->implode(', ') ?: 'None assigned' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-chalkboard fa-3x mb-3"></i>
                                            <p>No classes assigned yet.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Class Performance Trend Chart (example – replace with real data later) -->
                    <div class="row mt-1">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Average Class Performance (This Term)</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="performanceChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
            <script>
                // Example static data – replace with real averages from controller if needed
                const ctx = document.getElementById('performanceChart').getContext('2d');
                const performanceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($assignedClasses->pluck('name')),
                        datasets: [{
                            label: 'Average Score (%)',
                            data: [82, 78, 85, 90, 76], // Replace with real data
                            backgroundColor: 'rgba(0, 123, 255, 0.6)',
                            borderColor: '#007bff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            </script>

            @include('includes.edit_footer')
        </div>
    </div>
</body>
</html>