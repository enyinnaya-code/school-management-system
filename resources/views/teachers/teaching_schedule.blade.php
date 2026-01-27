{{-- ============================================ --}}
{{-- FILE: resources/views/teachers/teaching_schedule.blade.php --}}
{{-- ============================================ --}}

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
                                <h4><i class="fas fa-chalkboard-teacher"></i> My Teaching Schedule</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary mr-2" onclick="printSchedule()">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <button class="btn btn-info" onclick="toggleView()">
                                        <i class="fas fa-exchange-alt"></i> <span id="viewToggleText">List View</span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong><i class="fas fa-user"></i> Teacher:</strong> {{ $teacher->name }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong><i class="fas fa-calendar-alt"></i> Session:</strong>
                                            @php
                                                $currentSession = \App\Models\Session::where('is_current', 1)->first();
                                            @endphp
                                            <span class="badge badge-success">{{ $currentSession ? $currentSession->name : 'N/A' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong><i class="fas fa-calendar-check"></i> Term:</strong>
                                            @php
                                                $currentTerm = $currentSession ? \App\Models\Term::where('session_id', $currentSession->id)->where('is_current', 1)->first() : null;
                                            @endphp
                                            <span class="badge badge-info">{{ $currentTerm ? $currentTerm->name : 'N/A' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong><i class="fas fa-calendar-week"></i> Total Classes:</strong> 
                                            <span class="badge badge-primary">{{ count($teachingSchedule) }} periods/week</span>
                                        </div>
                                    </div>
                                </div>

                                @if(empty($teachingSchedule))
                                    <div class="alert alert-warning">
                                        <strong>No Classes Assigned:</strong> You don't have any classes assigned to you in the current timetable yet. Please contact the administrator.
                                    </div>
                                @else
                                    <!-- Grid View (Default - Shown first) -->
                                    <div id="gridView">
                                        <h6 class="mb-3"><i class="fas fa-th"></i> Weekly Grid View</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Time</th>
                                                        @php
                                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                                                        @endphp
                                                        @foreach($days as $day)
                                                            <th class="text-center">{{ $day }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $allTimes = collect($teachingSchedule)->pluck('time')->unique()->sort()->values();
                                                    @endphp
                                                    @foreach($allTimes as $time)
                                                        <tr>
                                                            <td style="font-weight: 500;">{{ $time }}</td>
                                                            @foreach($days as $day)
                                                                <td>
                                                                    @php
                                                                        $class = collect($teachingSchedule)->where('day', $day)->where('time', $time)->first();
                                                                    @endphp
                                                                    @if($class)
                                                                        <div class="teaching-card">
                                                                            <strong>{{ $class['subject'] }}</strong><br>
                                                                            <small>{{ $class['class'] }} - {{ $class['section'] }}</small>
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
                                    <div id="listView" style="display: none;" class="mb-4">
                                        <h5 class="mb-3"><i class="fas fa-list"></i> Schedule by Day</h5>
                                        @php
                                            $scheduleByDay = collect($teachingSchedule)->groupBy('day');
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
                                                                    @foreach($scheduleByDay[$day]->sortBy('time') as $schedule)
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
                                            $subjectCount = collect($teachingSchedule)->groupBy('subject')->count();
                                            $classCount = collect($teachingSchedule)->groupBy('class')->count();
                                        @endphp
                                        <div class="col-md-4">
                                            <div class="card text-center">
                                                <div class="card-body">
                                                    <h3 class="text-dark">{{ count($teachingSchedule) }}</h3>
                                                    <p class="mb-0">Total Periods</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card text-center">
                                                <div class="card-body">
                                                    <h3 class="text-success">{{ $subjectCount }}</h3>
                                                    <p class="mb-0">Different Subject(s)</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card text-center">
                                                <div class="card-body">
                                                    <h3 class="text-info">{{ $classCount }}</h3>
                                                    <p class="mb-0">Different Classes</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Print Preview Modal -->
    <div class="modal fade" id="printPreviewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-print"></i> Print Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="printPreviewContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="doPrint()">
                        <i class="fas fa-print"></i> Print Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
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
            const listView = document.getElementById('listView');
            const gridView = document.getElementById('gridView');
            const printPreviewContent = document.getElementById('printPreviewContent');
            
            // Determine which view is currently visible
            const isGridView = gridView.style.display !== 'none';
            
            // Clone the visible view
            const contentToClone = isGridView ? gridView : listView;
            const clonedContent = contentToClone.cloneNode(true);
            clonedContent.style.display = 'block';
            
            // Build the print preview content
            printPreviewContent.innerHTML = `
                <div class="print-header mb-4 text-center">
                    <h3>Teaching Schedule</h3>
                    <p><strong>Teacher:</strong> {{ $teacher->name }}</p>
                    <p>
                        <strong>Session:</strong> ${document.querySelector('.alert-info .col-md-3:nth-child(2) .badge').textContent} | 
                        <strong>Term:</strong> ${document.querySelector('.alert-info .col-md-3:nth-child(3) .badge').textContent}
                    </p>
                    <p><strong>Total Classes:</strong> {{ count($teachingSchedule) }} periods/week</p>
                    <hr>
                </div>
            `;
            printPreviewContent.appendChild(clonedContent);
            
            // Show the modal
            $('#printPreviewModal').modal('show');
        }

        function doPrint() {
            // Hide the modal
            $('#printPreviewModal').modal('hide');
            
            // Wait for modal to close then print
            setTimeout(function() {
                const printPreviewContent = document.getElementById('printPreviewContent');
                const originalContents = document.body.innerHTML;
                
                // Create print-friendly content
                const printContent = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Teaching Schedule - Print</title>
                        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
                        <style>
                            body { padding: 20px; font-family: Arial, sans-serif; }
                            .table { width: 100%; margin-bottom: 1rem; }
                            .table th, .table td { padding: 8px; border: 1px solid #dee2e6; }
                            .teaching-card { padding: 8px; background: #f8f9fa; border-radius: 4px; }
                            .teaching-card strong { display: block; margin-bottom: 3px; }
                            .badge { display: inline-block; padding: 4px 8px; background: #17a2b8; color: white; border-radius: 4px; }
                            .card { margin-bottom: 15px; border: 1px solid #dee2e6; }
                            .card-header { padding: 10px 15px; background: #007bff; color: white; font-weight: bold; }
                            .card-body { padding: 15px; }
                            .text-muted { color: #6c757d; }
                            h3, h5, h6 { margin-bottom: 15px; }
                            .print-header { text-align: center; margin-bottom: 30px; }
                            @page { margin: 1cm; }
                        </style>
                    </head>
                    <body>
                        ${printPreviewContent.innerHTML}
                    </body>
                    </html>
                `;
                
                // Open new window and print
                const printWindow = window.open('', '_blank');
                printWindow.document.write(printContent);
                printWindow.document.close();
                printWindow.focus();
                
                // Wait for content to load then print
                printWindow.onload = function() {
                    printWindow.print();
                    printWindow.onafterprint = function() {
                        printWindow.close();
                    };
                };
            }, 500);
        }
    </script>

    <!-- Custom Styles -->
    <style>
        .teaching-card {
            padding: 8px;
            border-radius: 4px;
            min-height: 50px;
        }
        
        .teaching-card strong {
            display: block;
            margin-bottom: 3px;
        }
        
        #printPreviewModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .print-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
    </style>

    @include('includes.edit_footer')
</body>
</html>