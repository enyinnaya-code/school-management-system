{{-- ============================================ --}}
{{-- FILE: resources/views/students/timetable.blade.php --}}
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
                                <h4>My Timetable: {{ $student->schoolClass->name ?? 'N/A' }} - {{ $timetable->section->section_name ?? '' }}</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" onclick="printTable()">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(!$timetable)
                                    <div class="alert alert-warning">
                                        <strong>No Timetable Available:</strong> Your class timetable has not been created yet. Please contact your class teacher or administrator.
                                    </div>
                                @else
                                    @if($timetable->has_conflicts)
                                        <div class="alert alert-info">
                                            <strong>Note:</strong> Some adjustments may be made to your timetable soon.
                                        </div>
                                    @endif

                                    <div class="p-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Session:</strong> {{ $timetable->session->name ?? 'N/A' }}
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Term:</strong> {{ $timetable->term->name ?? 'N/A' }}
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Class:</strong> {{ $student->schoolClass->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive" style="max-height: 600px; overflow-x: auto; overflow-y: auto;">
                                        <table class="table table-bordered table-sm table-hover" id="timetable-table" style="font-size: 0.9rem; min-width: 1000px;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width: 100px;">Day</th>
                                                    <th colspan="999" class="text-center">Periods</th>
                                                </tr>
                                                <tr id="period-headers">
                                                    <th></th>
                                                    @php
                                                        $maxPeriods = is_array($timetable->day_periods) ? max($timetable->day_periods) : $timetable->num_periods;
                                                        $startTime = 8 * 60;
                                                        $currentTime = $startTime;
                                                        $periodCounter = 0;
                                                    @endphp
                                                    @for($p = 1; $p <= $maxPeriods + 1; $p++)
                                                        @if($p == $timetable->break_period)
                                                            <th style="background-color: #fffbea; border-left: 3px solid #f39c12;">
                                                                <i class="fas fa-coffee"></i> Break<br>
                                                                <small>{{ date('h:i A', mktime(0, $currentTime)) }}-{{ date('h:i A', mktime(0, $currentTime + $timetable->break_duration)) }}</small>
                                                            </th>
                                                            @php $currentTime += $timetable->break_duration; @endphp
                                                        @else
                                                            @if($periodCounter < $maxPeriods)
                                                                @php $periodCounter++; @endphp
                                                                <th style="background-color: #e3f2fd;">
                                                                    Period {{ $periodCounter }}<br>
                                                                    <small>{{ date('h:i A', mktime(0, $currentTime)) }}-{{ date('h:i A', mktime(0, $currentTime + $timetable->lesson_duration)) }}</small>
                                                                </th>
                                                                @php $currentTime += $timetable->lesson_duration; @endphp
                                                            @endif
                                                        @endif
                                                    @endfor
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                                                @endphp
                                                @foreach($days as $dayIndex => $day)
                                                    <tr>
                                                        <td style="vertical-align: middle; font-weight: bold; background-color: #f8f9fa;">
                                                            {{ $day }}
                                                            @if(is_array($timetable->day_periods) && isset($timetable->day_periods[$day]) && $timetable->day_periods[$day] != $timetable->num_periods)
                                                                <br><small class="badge badge-info">{{ $timetable->day_periods[$day] }} periods</small>
                                                            @endif
                                                        </td>
                                                        @php
                                                            $periodsForDay = is_array($timetable->day_periods) && isset($timetable->day_periods[$day]) ? $timetable->day_periods[$day] : $timetable->num_periods;
                                                            $periodCounter = 0;
                                                            $classId = $student->class_id;
                                                        @endphp
                                                        @for($p = 1; $p <= $maxPeriods + 1; $p++)
                                                            @if($p == $timetable->break_period)
                                                                <td style="background-color: #fffbea; text-align: center; border-left: 3px solid #f39c12;">
                                                                    <strong><i class="fas fa-utensils"></i> Break Time</strong>
                                                                </td>
                                                            @else
                                                                @php $periodCounter++; @endphp
                                                                @if($periodCounter <= $periodsForDay)
                                                                    <td style="padding: 12px;">
                                                                        @if(isset($timetable->schedule[$day][$periodCounter][$classId]))
                                                                            @if($timetable->schedule[$day][$periodCounter][$classId] == 'free')
                                                                                <span class="badge badge-secondary"><i class="fas fa-book-open"></i> Free Period</span>
                                                                            @elseif($timetable->schedule[$day][$periodCounter][$classId])
                                                                                @php
                                                                                    $subjectId = $timetable->schedule[$day][$periodCounter][$classId];
                                                                                    $subject = \App\Models\Course::find($subjectId);
                                                                                @endphp
                                                                                <div class="subject-card">
                                                                                    <strong>{{ $subject->course_name ?? 'Unknown' }}</strong>
                                                                                    @if($subject && $subject->teacher)
                                                                                        <br><small class="text-muted"><i class="fas fa-user"></i> {{ $subject->teacher->name }}</small>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                        @endif
                                                                    </td>
                                                                @else
                                                                    <td style="background-color: #e9ecef;"></td>
                                                                @endif
                                                            @endif
                                                        @endfor
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4">
                                        <div class="alert alert-light">
                                            <h6 class="mb-3"><i class="fas fa-info-circle"></i> Legend:</h6>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <span class="badge badge-secondary">Free Period</span> - Study time
                                                </div>
                                                <div class="col-md-4">
                                                    <i class="fas fa-coffee"></i> Break - Recess time
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

    <!-- Print JavaScript -->
   <!-- Print JavaScript -->
<script>
    function printTable() {
        const tableContainer = document.querySelector('.table-responsive');
        const studentInfo = `
            <div style="text-align: center; margin-bottom: 20px;">
                <h2>{{ $student->user->name ?? 'Student' }} - Timetable</h2>
                <p><strong>Class:</strong> {{ $student->schoolClass->name ?? 'N/A' }} | 
                   <strong>Session:</strong> {{ $timetable->session->name ?? 'N/A' }} | 
                   <strong>Term:</strong> {{ $timetable->term->name ?? 'N/A' }}</p>
            </div>
        `;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print My Timetable</title>
                    <style>
                        @page {
                            size: A4 landscape;
                            margin: 10mm;
                        }
                        
                        body {
                            margin: 0;
                            padding: 10px;
                            font-family: Arial, sans-serif;
                        }
                        
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            font-size: 9pt;
                            page-break-inside: auto;
                        }
                        
                        tr {
                            page-break-inside: avoid;
                            page-break-after: auto;
                        }
                        
                        thead {
                            display: table-header-group;
                        }
                        
                        th, td {
                            border: 1px solid #333;
                            padding: 6px 4px;
                            text-align: center;
                            vertical-align: middle;
                        }
                        
                        th {
                            background-color: #e3f2fd !important;
                            font-weight: bold;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        
                        .subject-card {
                            padding: 4px;
                        }
                        
                        .subject-card strong {
                            display: block;
                            font-size: 0.85rem;
                            margin-bottom: 2px;
                        }
                        
                        .subject-card small {
                            font-size: 0.75rem;
                            color: #666;
                        }
                        
                        .badge {
                            display: inline-block;
                            padding: 3px 6px;
                            font-size: 0.75rem;
                            font-weight: 500;
                            border-radius: 3px;
                        }
                        
                        .badge-secondary {
                            background-color: #000000;
                            color: white;
                        }
                        
                        .badge-info {
                            background-color: #17a2b8;
                            color: white;
                        }
                        
                        td[style*="background-color: #fffbea"] {
                            background-color: #fffbea !important;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        
                        td[style*="background-color: #e9ecef"] {
                            background-color: #e9ecef !important;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        
                        .fas {
                            font-family: Arial, sans-serif;
                        }
                        
                        .fas.fa-coffee:before {
                            content: "☕";
                        }
                        
                        .fas.fa-utensils:before {
                            content: "🍽";
                        }
                        
                        .fas.fa-book-open:before {
                            content: "📖";
                        }
                        
                        .fas.fa-user:before {
                            content: "👤";
                        }
                        
                        @media print {
                            body {
                                -webkit-print-color-adjust: exact;
                                print-color-adjust: exact;
                            }
                        }
                    </style>
                </head>
                <body onload="window.print(); window.close();">
                    ${studentInfo}
                    ${tableContainer.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>

<!-- Custom Styles -->
<style>
    .subject-card strong {
        display: block;
        font-size: 0.95rem;
    }
    
    .subject-card small {
        color: #666;
    }
    
    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        
        .navbar-bg, 
        .navbar, 
        .main-sidebar,
        .card-header-action, 
        .btn, 
        .alert-info,
        .alert-light,
        .card-header,
        form {
            display: none !important;
        }
        
        .main-content {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .card-body {
            padding: 0 !important;
        }
        
        .subject-card {
            background: #f8f9fa !important;
            color: #000 !important;
            border: 1px solid #dee2e6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        table {
            font-size: 9pt !important;
        }
        
        th, td {
            padding: 6px 4px !important;
        }
    }
</style>

    <!-- Custom Styles -->
    <style>
      
        .subject-card strong {
            display: block;
            font-size: 0.95rem;
        }
        .subject-card small {
            color: #f0f0f0;
        }
        @media print {
            .navbar-bg, .right_top_nav, .side_nav, .card-header-action, .btn, .alert, form {
                display: none !important;
            }
            .subject-card {
                background: #f8f9fa !important;
                color: #000 !important;
                border: 1px solid #dee2e6;
            }
        }
    </style>

    @include('includes.edit_footer')
</body>
</html>