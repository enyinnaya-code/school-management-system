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
                                <h4>Timetable: {{ $timetable->section->section_name }} - {{ $timetable->term->name }}</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary mr-2" onclick="printTable()">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <a href="{{ route('timetables.export', $timetable->id) }}" class="btn btn-success">
                                        <i class="fas fa-file-excel"></i> Export to Excel
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filter Section -->
                                <form method="GET" action="{{ route('timetables.show', $timetable->id) }}" class="mb-3">
                                    <div class="row align-items-end">
                                        <div class="col-md-6">
                                            <label class="font-weight-bold">Filter by Class:</label>
                                            <select name="class_filter" id="classFilter" class="form-control">
                                                <option value="">All Classes</option>
                                                @foreach($allClasses as $class)
                                                    <option value="{{ $class->id }}" {{ request('class_filter') == $class->id ? 'selected' : '' }}>
                                                        {{ $class->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-info">
                                                <i class="fas fa-filter"></i> Apply Filter
                                            </button>
                                            <a href="{{ route('timetables.show', $timetable->id) }}" class="btn btn-secondary">
                                                <i class="fas fa-redo"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                @if(request('class_filter'))
                                    <div class="alert alert-info">
                                        <strong>Filtered by:</strong> {{ $allClasses->firstWhere('id', request('class_filter'))->name ?? 'Unknown Class' }}
                                    </div>
                                @endif

                                @if($timetable->has_conflicts)
                                    <div class="alert alert-warning">
                                        <strong>Warning:</strong> This timetable has conflicts. Please review and resolve them.
                                        <ul>
                                            @foreach($timetable->conflicts ?? [] as $conflict)
                                                <li>{{ $conflict }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="table-responsive" style="max-height: 600px; overflow-x: auto; overflow-y: auto;">
                                    <table class="table table-bordered table-sm" id="timetable-table" style="font-size: 0.85rem; min-width: 1200px;">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;">Day</th>
                                                <th style="width: 100px;">Class</th>
                                                <th colspan="999" class="text-center">Periods</th>
                                            </tr>
                                            <tr id="period-headers">
                                                <th></th>
                                                <th></th>
                                                @php
                                                    $maxPeriods = is_array($timetable->day_periods) ? max($timetable->day_periods) : $timetable->num_periods;
                                                    $startTime = 8 * 60;
                                                    $currentTime = $startTime;
                                                    $periodCounter = 0;
                                                @endphp
                                                @for($p = 1; $p <= $maxPeriods + 1; $p++)
                                                    @if($p == $timetable->break_period)
                                                        <th style="background-color: #f8f9fa;">
                                                            Break<br>
                                                            <small>{{ date('h:i A', mktime(0, $currentTime)) }}-{{ date('h:i A', mktime(0, $currentTime + $timetable->break_duration)) }}</small>
                                                        </th>
                                                        @php $currentTime += $timetable->break_duration; @endphp
                                                    @else
                                                        @if($periodCounter < $maxPeriods)
                                                            @php $periodCounter++; @endphp
                                                            <th>
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
                                                @foreach($classes as $classIndex => $class)
                                                    <tr>
                                                        @if($classIndex == 0)
                                                            <td rowspan="{{ $classes->count() }}" style="vertical-align: middle; font-weight: bold;">
                                                                {{ $day }}
                                                                @if(is_array($timetable->day_periods) && $timetable->day_periods[$day] != $timetable->num_periods)
                                                                    <br><small class="badge badge-info">{{ $timetable->day_periods[$day] }} periods</small>
                                                                @endif
                                                            </td>
                                                        @endif
                                                        <td style="font-weight: 500;">
                                                            {{ $class->name }}
                                                        </td>
                                                        @php
                                                            $periodsForDay = is_array($timetable->day_periods) && isset($timetable->day_periods[$day]) ? $timetable->day_periods[$day] : $timetable->num_periods;
                                                            $periodCounter = 0;
                                                        @endphp
                                                        @for($p = 1; $p <= $maxPeriods + 1; $p++)
                                                            @if($p == $timetable->break_period)
                                                                <td style="background-color: #f8f9fa;">
                                                                    @if($periodCounter < $periodsForDay && isset($timetable->schedule[$day]['break'][$class->id]) && $timetable->schedule[$day]['break'][$class->id])
                                                                        <strong>Break</strong>
                                                                    @endif
                                                                </td>
                                                            @else
                                                                @php $periodCounter++; @endphp
                                                                @if($periodCounter <= $periodsForDay)
                                                                    <td>
                                                                        @if(isset($timetable->schedule[$day][$periodCounter][$class->id]))
                                                                            @if($timetable->schedule[$day][$periodCounter][$class->id] == 'free')
                                                                                Free Period
                                                                            @elseif($timetable->schedule[$day][$periodCounter][$class->id])
                                                                                {{ $subjects[$timetable->schedule[$day][$periodCounter][$class->id]]->course_name ?? 'Unknown' }}
                                                                                @if($timetable->has_conflicts && $timetable->conflicts)
                                                                                    @foreach($timetable->conflicts as $conflict)
                                                                                        @if(str_contains($conflict, $day) && str_contains($conflict, 'Period ' . $periodCounter))
                                                                                            <span class="badge badge-danger">Conflict</span>
                                                                                        @endif
                                                                                    @endforeach
                                                                                @endif
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
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('timetables.index') }}" class="btn btn-secondary">Back to Timetables</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Print JavaScript -->
    <script>
        function printTable() {
            const tableContainer = document.querySelector('.table-responsive');
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print Timetable</title>
                        <style>
                            @media print {
                                @page {
                                    size: landscape;
                                    margin: 10mm;
                                }
                                body {
                                    margin: 0;
                                    font-family: Arial, sans-serif;
                                }
                                .table-responsive {
                                    width: 100%;
                                    overflow: visible !important;
                                }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    font-size: 9pt;
                                }
                                th, td {
                                    border: 1px solid #000;
                                    padding: 6px;
                                    text-align: center;
                                }
                                th {
                                    background-color: #f8f9fa;
                                    font-weight: bold;
                                }
                                td {
                                    background-color: #fff;
                                }
                                thead {
                                    display: table-header-group;
                                }
                                tbody {
                                    display: table-row-group;
                                }
                                body > *:not(.table-responsive) {
                                    display: none !important;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        ${tableContainer.outerHTML}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    </script>

    <!-- CSS Styles -->
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }
            .navbar-bg, .right_top_nav, .side_nav, .card-header-action, .btn, .card-header, .alert, .mt-3, form {
                display: none !important;
            }
            .main-content {
                padding-top: 0 !important;
                margin-top: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .table-responsive {
                overflow: visible !important;
            }
            .table {
                font-size: 9pt !important;
                width: 100% !important;
            }
            .table th, .table td {
                border: 1px solid #000 !important;
                padding: 6px !important;
            }
            thead {
                display: table-header-group;
            }
            tbody {
                display: table-row-group;
            }
        }
    </style>

    @include('includes.edit_footer')
</body>
</html>