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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0"><i class="fas fa-calendar-check mr-2"></i>Teacher Attendance Report
                                </h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse show" id="filterCollapse">
                                <div class="card-body pb-0 border-bottom">
                                    <!-- Filter Form -->
                                    <form method="GET" action="{{ route('attendance.teachers.index') }}" class="row"
                                        id="filterForm">
                                        <div class="form-group col-md-3">
                                            <label>Select Section</label>
                                            <select class="form-control" name="section_id" id="section_id"
                                                onchange="fetchSessions()">
                                                <option value="">Select a section...</option>
                                                @foreach ($sections as $section)
                                                <option value="{{ $section->id }}" {{ $sectionId==$section->id ?
                                                    'selected' : '' }}>{{ $section->section_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Select Session</label>
                                            <select class="form-control" name="session_id" id="session_id"
                                                onchange="fetchTerms()">
                                                <option value="">Select a session...</option>
                                                @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}" {{ $sessionId==$session->id ?
                                                    'selected' : '' }}>{{ $session->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Select Term</label>
                                            <select class="form-control" name="term_id" id="term_id">
                                                <option value="">Select a term...</option>
                                                @if ($terms->isNotEmpty())
                                                @foreach ($terms as $term)
                                                <option value="{{ $term->id }}" {{ $termId==$term->id ? 'selected' : ''
                                                    }}>{{ $term->name }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Select Period</label>
                                            <select class="form-control" name="period" id="period"
                                                onchange="updateDateRange()">
                                                <option value="weekly" {{ $period=='weekly' ? 'selected' : '' }}>Weekly
                                                </option>
                                                <option value="monthly" {{ $period=='monthly' ? 'selected' : '' }}>
                                                    Monthly</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Start Date</label>
                                            <input type="date" name="start_date" class="form-control" id="start_date"
                                                value="{{ request('start_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>End Date</label>
                                            <input type="date" name="end_date" class="form-control" id="end_date"
                                                value="{{ request('end_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
                                        </div>

                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('attendance.teachers.index') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Display Success/Error Messages -->
                                @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                @endif
                                @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                @endif

                                <!-- Display Current Session and Term -->
                                @if ($currentSession)
                                <div class="alert alert-info">
                                    <strong>Current Session:</strong> {{ $currentSession->name }}
                                    @if ($terms->where('is_current', true)->first())
                                    <br><strong>Current Term:</strong> {{ $terms->where('is_current',
                                    true)->first()->name }}
                                    @else
                                    <br><em>No current term set for this session.</em>
                                    @endif
                                </div>
                                @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>No current session set. Please
                                    select a section, session, and term.
                                </div>
                                @endif

                                <!-- Display Active Filters -->
                                @if(request('section_id') || request('session_id') || request('term_id') ||
                                request('period') || request('start_date') || request('end_date'))
                                <div class="mb-3">
                                    <h6 class="mb-2"><i class="fas fa-tags mr-1"></i>Active Filters:</h6>
                                    <div class="active-filters d-flex flex-wrap align-items-center">
                                        @if(request('section_id'))
                                        @php
                                        $selectedSection = $sections->firstWhere('id', request('section_id'));
                                        @endphp
                                        <span class="badge badge-info mr-2 mb-1">Section: {{
                                            $selectedSection->section_name ?? request('section_id') }}</span>
                                        @endif
                                        @if(request('session_id'))
                                        @php
                                        $selectedSession = $sessions->firstWhere('id', request('session_id'));
                                        @endphp
                                        <span class="badge badge-info mr-2 mb-1">Session: {{ $selectedSession->name ??
                                            request('session_id') }}</span>
                                        @endif
                                        @if(request('term_id'))
                                        @php
                                        $selectedTerm = $terms->firstWhere('id', request('term_id'));
                                        @endphp
                                        <span class="badge badge-info mr-2 mb-1">Term: {{ $selectedTerm->name ??
                                            request('term_id') }}</span>
                                        @endif
                                        @if(request('start_date') || request('end_date'))
                                        <span class="badge badge-info mr-2 mb-1">Date Range: {{ request('start_date') }}
                                            to {{ request('end_date') }}</span>
                                        @else
                                        @if(request('period'))
                                        <span class="badge badge-info mr-2 mb-1">Period: {{ ucfirst(request('period'))
                                            }}</span>
                                        @endif
                                        @endif
                                        <a href="{{ route('attendance.teachers.index') }}"
                                            class="btn btn-sm btn-outline-danger ml-2 mb-1">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <!-- Attendance Summary Cards -->
                                @if ($sectionId && $sessionId && $termId && $attendances->isNotEmpty())
                                @php
                                $total = $attendances->count();
                                $present = $attendances->where('attendance', 'Present')->count();
                                $absent = $attendances->where('attendance', 'Absent')->count();
                                $presentPercent = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                                $absentPercent = $total > 0 ? round(($absent / $total) * 100, 1) : 0;
                                @endphp
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm bg-primary">
                                            <div class="card-body text-center">
                                                <i class="fas fa-list fa-2x text-primary mb-2"></i>
                                                <h5 class="card-title text-primary">{{ $total }}</h5>
                                                <p class="card-text text-white">Total Records</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm bg-success text-white">
                                            <div class="card-body text-center">
                                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                                <h5 class="card-title">{{ $present }}</h5>
                                                <p class="card-text text-white">{{ $presentPercent }}% Present</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm bg-danger text-white">
                                            <div class="card-body text-center">
                                                <i class="fas fa-times-circle fa-2x mb-2"></i>
                                                <h5 class="card-title">{{ $absent }}</h5>
                                                <p class="card-text text-white">{{ $absentPercent }}% Absent</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Attendance Table -->
                                @if ($sectionId && $sessionId && $termId)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Week starting {{ \Carbon\Carbon::parse($displayStart)->format('F j, Y') }}</h5>
                                    <div>
                                        @if($prevWeekStart)
                                        <a href="{{ route('attendance.teachers.index', array_merge(request()->query(), ['view_start' => $prevWeekStart])) }}"
                                            class="btn btn-sm btn-outline-primary mr-2">Previous</a>
                                        @endif
                                        @if($nextWeekStart)
                                        <a href="{{ route('attendance.teachers.index', array_merge(request()->query(), ['view_start' => $nextWeekStart])) }}"
                                            class="btn btn-sm btn-outline-primary">Next</a>
                                        @endif
                                    </div>
                                </div>

                                @php
                                $days = [];
                                $currentDay = \Carbon\Carbon::parse($displayStart);
                                $endDay = \Carbon\Carbon::parse($displayEnd);
                                while ($currentDay <= $endDay) { $days[]=$currentDay->copy();
                                    $currentDay->addDay();
                                    }
                                    @endphp

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover" id="tableExport">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th><i class="fas fa-user mr-1"></i>Teacher Name</th>
                                                    @foreach ($days as $day)
                                                    <th class="text-center">{{ $day->format('D') }}<br>{{
                                                        $day->format('M d') }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($teachers as $teacher)
                                                <tr>
                                                    <td><strong>{{ $teacher->name }}</strong></td>
                                                    @foreach ($days as $day)
                                                    @php
                                                    $attRecord = $attendances->where('teacher_id',
                                                    $teacher->id)->where('date', $day->format('Y-m-d'))->first();
                                                    $status = $attRecord ? $attRecord->attendance : 'N/A';
                                                    $badgeClass = match($status) {
                                                    'Present' => 'badge-success',
                                                    'Absent' => 'badge-danger',
                                                    'Late' => 'badge-warning',
                                                    'On Leave' => 'badge-primary',
                                                    default => ''
                                                    };
                                                    $time = $attRecord ? $attRecord->time : null;
                                                    $formattedTime = $time ? \Carbon\Carbon::parse($time)->format('g:i
                                                    A') : '-';
                                                    @endphp
                                                    <td class="text-center">
                                                        <div>
                                                            <span class="badge {{ $badgeClass }} p-2 d-block">{{ $status
                                                                }}</span>
                                                            <small class="text-dark">{{ $formattedTime }}</small>
                                                        </div>
                                                    </td>
                                                    @endforeach
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                                        Please select a section, session, and term to view the attendance report.
                                    </div>
                                    @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')
    <script src="{{ asset('bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('bundles/datatables/export-tables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('bundles/datatables/export-tables/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('bundles/datatables/export-tables/jszip.min.js') }}"></script>
    <script src="{{ asset('bundles/datatables/export-tables/pdfmake.min.js') }}"></script>
    <script src="{{ asset('bundles/datatables/export-tables/vfs_fonts.js') }}"></script>
    <script src="{{ asset('bundles/datatables/export-tables/buttons.print.min.js') }}"></script>
    <script src="{{ asset('js/page/datatables.js') }}"></script>


    <script>
        function fetchSessions() {
            const sectionId = document.getElementById('section_id').value;
            const sessionSelect = document.getElementById('session_id');
            const termSelect = document.getElementById('term_id');

            // Clear session and term dropdowns
            sessionSelect.innerHTML = '<option value="">Select a session...</option>';
            termSelect.innerHTML = '<option value="">Select a term...</option>';

            if (sectionId) {
                fetch(`/api/sessions?section_id=${sectionId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(session => {
                            const option = document.createElement('option');
                            option.value = session.id;
                            option.text = session.name;
                            sessionSelect.appendChild(option);
                        });
                        // Auto-select current session if available
                        const currentSession = data.find(session => session.is_current);
                        if (currentSession) {
                            sessionSelect.value = currentSession.id;
                            fetchTerms();
                        }
                    })
                    .catch(error => console.error('Error fetching sessions:', error));
            }
        }

        function fetchTerms() {
            const sessionId = document.getElementById('session_id').value;
            const termSelect = document.getElementById('term_id');

            // Clear term dropdown
            termSelect.innerHTML = '<option value="">Select a term...</option>';

            if (sessionId) {
                fetch(`/api/terms?session_id=${sessionId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(term => {
                            const option = document.createElement('option');
                            option.value = term.id;
                            option.text = term.name;
                            termSelect.appendChild(option);
                        });
                        // Auto-select current term if available
                        const currentTerm = data.find(term => term.is_current);
                        if (currentTerm) {
                            termSelect.value = currentTerm.id;
                            document.getElementById('filterForm').submit();
                        }
                    })
                    .catch(error => console.error('Error fetching terms:', error));
            }
        }

        function updateDateRange() {
            const periodSelect = document.getElementById('period');
            const period = periodSelect.value;
            const today = new Date().toISOString().split('T')[0];
            let start, end;
            const d = new Date(today);
            switch (period) {
                case 'weekly':
                    // Week starts on Monday
                    const day = d.getDay();
                    const diff = d.getDate() - day + (day == 0 ? -6 : 1);
                    const monday = new Date(d.setDate(diff));
                    start = monday.toISOString().split('T')[0];
                    const sunday = new Date(monday);
                    sunday.setDate(sunday.getDate() + 6);
                    end = sunday.toISOString().split('T')[0];
                    break;
                case 'monthly':
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    start = `${year}-${month}-01`;
                    const lastDay = new Date(year, d.getMonth() + 1, 0).getDate();
                    end = `${year}-${month}-${lastDay}`;
                    break;
            }
            document.getElementById('start_date').value = start;
            document.getElementById('end_date').value = end;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const periodSelect = document.getElementById('period');
            if (periodSelect) {
                // Trigger on load if period is set
                if (periodSelect.value) {
                    updateDateRange();
                }
            }
        });
    </script>


</body>