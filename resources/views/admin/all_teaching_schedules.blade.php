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
                                <h4><i class="fas fa-chalkboard-teacher"></i> All Teachers' Teaching Schedules</h4>
                            </div>
                            <div class="card-body">
                                <!-- Filters -->
                                <form method="GET" action="{{ route('admin.teaching-schedules') }}" id="filterForm">
                                    <div class="row mb-4">
                                        <!-- Section Filter -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="section_id"><i class="fas fa-layer-group"></i> Filter by Section:</label>
                                                <select name="section_id" id="section_id" class="form-control" onchange="this.form.submit()">
                                                    <option value="">-- All Sections --</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}" {{ $selectedSectionId == $section->id ? 'selected' : '' }}>
                                                            {{ $section->section_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Teacher Selection -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="teacher_id"><i class="fas fa-user-tie"></i> Select Teacher:</label>
                                                <select name="teacher_id" id="teacher_id" class="form-control" onchange="this.form.submit()" {{ $teachers->isEmpty() ? 'disabled' : '' }}>
                                                    <option value="">-- Select a Teacher --</option>
                                                    @foreach($teachers as $t)
                                                        <option value="{{ $t->id }}" {{ $selectedTeacherId == $t->id ? 'selected' : '' }}>
                                                            {{ $t->name }} 
                                                            @if($t->email)
                                                                ({{ $t->email }})
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if($teachers->isEmpty() && $selectedSectionId)
                                                    <small class="text-danger">No teachers found in this section</small>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Clear Filter Button -->
                                        <div class="col-md-2">
                                            <label class="d-block">&nbsp;</label>
                                            <a href="{{ route('admin.teaching-schedules') }}" class="btn btn-secondary btn-block">
                                                <i class="fas fa-times"></i> Clear Filters
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                @if($teacher)
                                    <div class="alert alert-info">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong><i class="fas fa-user"></i> Teacher:</strong> {{ $teacher->name }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong><i class="fas fa-envelope"></i> Email:</strong> {{ $teacher->email ?? 'N/A' }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong><i class="fas fa-calendar-alt"></i> Session:</strong>
                                                @php
                                                    $currentSession = \App\Models\Session::where('is_current', 1)->first();
                                                @endphp
                                                <span class="badge badge-success">{{ $currentSession ? $currentSession->name : 'N/A' }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong><i class="fas fa-calendar-week"></i> Total Classes:</strong> 
                                                <span class="badge badge-primary">{{ count($teachingSchedule ?? []) }} periods/week</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if(empty($teachingSchedule))
                                        <div class="alert alert-warning">
                                            <strong>No Classes Assigned:</strong> This teacher doesn't have any classes assigned in the current timetable yet.
                                        </div>
                                    @else
                                        <div class="mb-3">
                                            <button class="btn btn-primary mr-2" onclick="printSchedule()">
                                                <i class="fas fa-print"></i> Print Schedule
                                            </button>
                                            <button class="btn btn-info" onclick="toggleView()">
                                                <i class="fas fa-exchange-alt"></i> <span id="viewToggleText">List View</span>
                                            </button>
                                        </div>

                                        <!-- Grid View (Default) - Days on Left, Times on Top -->
                                        <div id="gridView">
                                            <h6 class="mb-3"><i class="fas fa-th"></i> Weekly Grid View</h6>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="120">Day</th>
                                                            @php
                                                                $allTimes = collect($teachingSchedule ?? [])->pluck('time')->unique()->sortBy(function($time) use ($teachingSchedule) {
                                                                    $schedule = collect($teachingSchedule ?? [])->where('time', $time)->first();
                                                                    return $schedule['start_time'] ?? 0;
                                                                })->values();
                                                            @endphp
                                                            @foreach($allTimes as $time)
                                                                <th class="text-center" style="min-width: 150px;">{{ $time }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                                                        @endphp
                                                        @foreach($days as $day)
                                                            <tr>
                                                                <td style="font-weight: 600; background: #f8f9fa;">{{ $day }}</td>
                                                                @foreach($allTimes as $time)
                                                                    <td>
                                                                        @php
                                                                            $class = collect($teachingSchedule ?? [])->where('day', $day)->where('time', $time)->first();
                                                                        @endphp
                                                                        @if($class)
                                                                            <div class="teaching-card">
                                                                                <strong class="text-primary">{{ $class['subject'] }}</strong><br>
                                                                                <small class="text-muted">{{ $class['class'] }}</small><br>
                                                                                <small class="badge badge-info badge-sm">{{ $class['section'] }}</small>
                                                                            </div>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- List View (Hidden by default) -->
                                        <div id="listView" style="display: none;">
                                            <h6 class="mb-3"><i class="fas fa-list"></i> Schedule by Day</h6>
                                            @php
                                                $scheduleByDay = collect($teachingSchedule ?? [])->groupBy('day');
                                            @endphp
                                            @foreach($days as $day)
                                                <div class="card mb-3">
                                                    <div class="card-header bg-primary text-white">
                                                        <h6 class="mb-0"><i class="fas fa-calendar-day"></i> {{ $day }}</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        @if($scheduleByDay->has($day))
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Time</th>
                                                                            <th>Subject</th>
                                                                            <th>Class</th>
                                                                            <th>Section</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($scheduleByDay[$day]->sortBy('start_time') as $schedule)
                                                                            <tr>
                                                                                <td><i class="fas fa-clock"></i> {{ $schedule['time'] }}</td>
                                                                                <td><strong>{{ $schedule['subject'] }}</strong></td>
                                                                                <td><span class="badge badge-info">{{ $schedule['class'] }}</span></td>
                                                                                <td>{{ $schedule['section'] }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <p class="text-muted mb-0"><i class="fas fa-info-circle"></i> No classes scheduled for this day</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Summary Statistics -->
                                        <div class="row mt-4">
                                            @php
                                                $subjectCount = collect($teachingSchedule ?? [])->groupBy('subject')->count();
                                                $classCount = collect($teachingSchedule ?? [])->groupBy('class')->count();
                                                $sectionCount = collect($teachingSchedule ?? [])->groupBy('section')->count();
                                            @endphp
                                            <div class="col-md-3">
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        <h3 class="text-dark">{{ count($teachingSchedule ?? []) }}</h3>
                                                        <p class="mb-0">Total Periods</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        <h3 class="text-success">{{ $subjectCount }}</h3>
                                                        <p class="mb-0">Different Subject(s)</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        <h3 class="text-info">{{ $classCount }}</h3>
                                                        <p class="mb-0">Different Classes</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        <h3 class="text-warning">{{ $sectionCount }}</h3>
                                                        <p class="mb-0">Section(s)</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Please select a teacher from the dropdown above to view their teaching schedule.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        function toggleView() {
            const listView = document.getElementById('listView');
            const gridView = document.getElementById('gridView');
            const toggleText = document.getElementById('viewToggleText');
            
            if (gridView.style.display === 'none') {
                gridView.style.display = 'block';
                listView.style.display = 'none';
                toggleText.textContent = 'List View';
            } else {
                gridView.style.display = 'none';
                listView.style.display = 'block';
                toggleText.textContent = 'Grid View';
            }
        }

        function printSchedule() {
            window.print();
        }
    </script>

    <style>
        .teaching-card {
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid #007bff;
            border-radius: 4px;
            min-height: 60px;
        }
        
        .teaching-card strong {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .teaching-card small {
            display: block;
            font-size: 12px;
        }

        .badge-sm {
            font-size: 10px;
            padding: 3px 6px;
        }

        .table-bordered td, .table-bordered th {
            vertical-align: middle;
        }

        @media print {
            /* Set A4 page dimensions and margins */
            @page {
                size: A4 landscape;
                margin: 10mm 8mm;
            }

            /* Hide unnecessary elements */
            .card-header-action,
            .btn,
            #section_id,
            #teacher_id,
            label,
            .navbar-bg,
            .main-sidebar,
            .navbar,
            form,
            .loader,
            #listView,
            .row.mt-4 {
                display: none !important;
            }
            
            /* Reset body and main content */
            body {
                margin: 0;
                padding: 0;
                background: white !important;
            }

            .main-content {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            .section {
                padding: 0 !important;
                margin: 0 !important;
            }

            .col-12 {
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
            }

            .card-header {
                padding: 8px 0 !important;
                background: white !important;
                border: none !important;
            }

            .card-header h4 {
                font-size: 16px !important;
                margin: 0 !important;
            }

            .card-body {
                padding: 0 !important;
            }

            /* Optimize alert info box */
            .alert-info {
                padding: 8px 10px !important;
                margin-bottom: 10px !important;
                font-size: 11px !important;
                page-break-inside: avoid;
            }

            .alert-info .row {
                margin: 0 !important;
            }

            .alert-info .col-md-3 {
                padding: 2px 5px !important;
                font-size: 10px !important;
            }

            .alert-info strong {
                font-size: 10px !important;
            }

            .alert-info .badge {
                font-size: 9px !important;
                padding: 2px 4px !important;
            }

            /* Optimize table for A4 landscape */
            #gridView h6 {
                font-size: 13px !important;
                margin-bottom: 8px !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .table {
                width: 100% !important;
                font-size: 9px !important;
                margin: 0 !important;
            }

            .table thead th {
                padding: 5px 3px !important;
                font-size: 9px !important;
                font-weight: 600 !important;
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table tbody td {
                padding: 4px 3px !important;
                font-size: 8px !important;
                border: 1px solid #ddd !important;
            }

            .table tbody td:first-child {
                font-weight: 600 !important;
                width: 80px !important;
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Optimize teaching card */
            .teaching-card {
                padding: 4px 5px !important;
                border: 1px solid #dee2e6 !important;
                border-left: 2px solid #007bff !important;
                page-break-inside: avoid;
                min-height: 35px !important;
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border-radius: 2px !important;
            }

            .teaching-card strong {
                font-size: 9px !important;
                margin-bottom: 2px !important;
                line-height: 1.2 !important;
                color: #007bff !important;
            }

            .teaching-card small {
                font-size: 8px !important;
                line-height: 1.2 !important;
            }

            .teaching-card .badge {
                font-size: 7px !important;
                padding: 1px 3px !important;
            }

            /* Ensure colors print */
            .text-primary,
            .text-muted,
            .badge-info {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Remove page breaks within table */
            table, tr, td, th {
                page-break-inside: avoid !important;
            }

            /* Scale entire content if needed */
            #gridView {
                transform: scale(0.95);
                transform-origin: top left;
                width: 105%;
            }
        }

        /* Landscape print optimization */
        @media print and (orientation: landscape) {
            .table {
                max-width: 100% !important;
            }
        }
    </style>

    @include('includes.edit_footer')
</body>
</html>