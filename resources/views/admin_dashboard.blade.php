@include('includes.head')
<style>
  .main-content {
    padding-top: 96px;
  }
</style>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      @include('includes.right_top_nav')
      @include('includes.side_nav')

      <!-- Main Content -->
      <div class="main-content">
        <section class="section mb-5 pb-5">

          <!-- Session and Term Info -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body py-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-1 text-dark">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Current Session: <strong>{{ $currentSession->name ?? 'Not Set' }}</strong>
                      </h6>
                      <p class="mb-0 text-dark">
                        <i class="fas fa-clock mr-2"></i>
                        Term: <strong>{{ $currentTerm->name ?? 'Not Set' }}</strong>
                      </p>
                    </div>
                    {{-- <div class="text-right">
                      <small class="text-dark">Last Updated</small>
                      <p class="mb-0 font-weight-bold text-dark">{{ now()->format('M d, Y') }}</p>
                    </div> --}}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- First Row - Main Stats -->
          <div class="row">
            <!-- Total Students -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 ">
              <div class="card" style="height: 150px;">
                <div class="card-statistic-3 p-4">
                  <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                      <h6 class="mb-2 text-dark">Total Students</h6>
                      <h4 class="mb-0 font-weight-bold text-dark">{{ number_format($totalStudents) }}</h4>
                      <small class="text-dark">Active Learners</small>
                    </div>
                    <div>
                      <i class="fas fa-user-graduate fa-3x text-dark"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Staff -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 ">
              <div class="card" style="height: 150px;">
                <div class="card-statistic-3 p-4">
                  <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                      <h6 class="mb-2 text-dark">Total Staff</h6>
                      <h4 class="mb-0 font-weight-bold text-dark">{{ number_format($totalStaff) }}</h4>
                      <small class="text-dark">Teachers: {{ $totalTeachers }}</small>
                    </div>
                    <div>
                      <i class="fas fa-chalkboard-teacher fa-3x text-dark"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Classes -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 ">
              <div class="card" style="height: 150px;">
                <div class="card-statistic-3 p-4">
                  <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                      <h6 class="mb-2 text-dark">Total Classes</h6>
                      <h4 class="mb-0 font-weight-bold text-dark">{{ number_format($totalClasses) }}</h4>
                      <small class="text-dark">Active Classes</small>
                    </div>
                    <div>
                      <i class="fas fa-school fa-3x text-dark"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Attendance Rate -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 ">
              <div class="card" style="height: 150px;">
                <div class="card-statistic-3 p-4">
                  <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                      <h6 class="mb-2 text-dark">Attendance (Week)</h6>
                      <h4 class="mb-0 font-weight-bold text-dark">{{ $attendanceRate }}%</h4>
                      <small class="text-dark">Teacher Attendance</small>
                    </div>
                    <div>
                      <i class="fas fa-user-check fa-3x text-dark"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Second Row - Additional Stats -->
          <div class="row">
            <!-- Total Courses -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 ">
              <div class="card" style="height: 140px;">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row px-3 py-2">
                      <div class="col-7 pr-0 pt-3">
                        <div class="card-content">
                          <h6 class="font-15 text-dark">Total Courses</h6>
                          <h4 class="mb-0 font-22 text-dark">{{ number_format($totalCourses) }}</h4>
                        </div>
                      </div>
                      <div class="col-5 pl-0 text-center pt-4">
                        <i class="fas fa-book fa-3x text-warning"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Library Books -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 ">
              <div class="card" style="height: 140px;">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row px-3 py-2">
                      <div class="col-7 pr-0 pt-3">
                        <div class="card-content">
                          <h6 class="font-15 text-dark">Library Books</h6>
                          <h4 class="mb-0 font-22 text-dark">{{ number_format($libraryBooks) }}</h4>
                        </div>
                      </div>
                      <div class="col-5 pl-0 text-center pt-4">
                        <i class="fas fa-book-open fa-3x text-info"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Upcoming Events -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 ">
              <div class="card" style="height: 140px;">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row px-3 py-2">
                      <div class="col-7 pr-0 pt-3">
                        <div class="card-content">
                          <h6 class="font-15 text-dark">Upcoming Events</h6>
                          <h4 class="mb-0 font-22 text-dark">{{ number_format($upcomingEvents) }}</h4>
                        </div>
                      </div>
                      <div class="col-5 pl-0 text-center pt-4">
                        <i class="fas fa-calendar-alt fa-3x text-success"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Suspended Accounts -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12 ">
              <div class="card" style="height: 140px;">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row px-3 py-2">
                      <div class="col-7 pr-0 pt-3">
                        <div class="card-content">
                          <h6 class="font-15 text-dark">Suspended</h6>
                          <h4 class="mb-0 font-22 text-dark">{{ number_format($suspendedAccounts) }}</h4>
                        </div>
                      </div>
                      <div class="col-5 pl-0 text-center pt-4">
                        <i class="fas fa-user-slash fa-3x text-danger"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Row - Equal Height Cards -->
          <div class="row mt-3">
            <!-- Teacher Attendance Chart -->
            <div class="col-lg-8 col-md-12 mb-4">
              <div class="card h-100">
                <div class="card-header">
                  <h4 class="text-dark">Teacher Attendance - Last 7 Days</h4>
                  <div class="card-header-action">
                    <span class="badge badge-success">Present: {{ $attendanceStats->get('Present')->total ?? 0 }}</span>
                    <span class="badge badge-danger ml-2">Absent: {{ $attendanceStats->get('Absent')->total ?? 0
                      }}</span>
                  </div>
                </div>
                <div class="card-body">
                  <canvas id="attendanceChart" height="120"></canvas>
                </div>
              </div>
            </div>

            <!-- Monthly Attendance Breakdown -->
            <div class="col-lg-4 col-md-12 mb-4">
              <div class="card h-100">
                <div class="card-header">
                  <h4 class="text-dark">Monthly Attendance</h4>
                </div>
                <div class="card-body d-flex flex-column">
                  <div style="height: 200px;">
                    <canvas id="monthlyAttendanceChart"></canvas>
                  </div>
                  <div class="mt-auto pt-3">
                    <div class="d-flex justify-content-between mb-2">
                      <span class="text-dark">Present</span>
                      <strong class="text-success">{{ $monthlyAttendance->get('Present')->total ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                      <span class="text-dark">Absent</span>
                      <strong class="text-danger">{{ $monthlyAttendance->get('Absent')->total ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                      <span class="text-dark">Late</span>
                      <strong class="text-warning">{{ $monthlyAttendance->get('Late')->total ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                      <span class="text-dark">On Leave</span>
                      <strong class="text-info">{{ $monthlyAttendance->get('On Leave')->total ?? 0 }}</strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Students Per Class Chart and Events - Equal Height -->
          <div class="row">
            <div class="col-lg-8 col-md-12 mb-4">
              <div class="card h-100">
                <div class="card-header">
                  <h4 class="text-dark">Students Distribution by Class</h4>
                </div>
                <div class="card-body">
                  <canvas id="classDistributionChart" height="100"></canvas>
                </div>
              </div>
            </div>

            <!-- Upcoming Events List -->
            <div class="col-lg-4 col-md-12 mb-4">
              <div class="card h-100">
                <div class="card-header">
                  <h4 class="text-dark">Next Events</h4>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                  <ul class="list-group list-group-flush">
                    @forelse($nextEvents as $event)
                    <li class="list-group-item">
                      <div class="d-flex align-items-center">
                        <div class="mr-3">
                          <div class="bg-light rounded text-center" style="width: 50px; padding: 4px;">
                            <div class="font-weight-bold text-dark">{{
                              \Carbon\Carbon::parse($event->start_date)->format('d') }}</div>
                            <small class="text-dark">{{ \Carbon\Carbon::parse($event->start_date)->format('M')
                              }}</small>
                          </div>
                        </div>
                        <div>
                          <h6 class="mb-1 text-dark">{{ $event->title }}</h6>
                          <small class="text-muted">
                            {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}
                            @if($event->end_date && $event->start_date != $event->end_date)
                            - {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y') }}
                            @endif
                          </small>
                        </div>
                      </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">
                      <i class="fas fa-calendar-times fa-2x mb-2"></i>
                      <p class="mb-0">No upcoming events</p>
                    </li>
                    @endforelse
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Attendance Activity -->
          {{-- <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="text-dark">Recent Attendance Records</h4>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-striped mb-0">
                      <thead>
                        <tr>
                          <th class="text-dark">Teacher Name</th>
                          <th class="text-dark">Date</th>
                          <th class="text-dark">Time</th>
                          <th class="text-dark">Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($recentAttendances as $record)
                        <tr>
                          <td class="text-dark">{{ $record->teacher->name ?? 'N/A' }}</td>
                          <td class="text-dark">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                          <td class="text-dark">{{ \Carbon\Carbon::parse($record->time)->format('h:i A') }}</td>
                          <td>
                            @if($record->attendance == 'Present')
                            <span class="badge badge-success">Present</span>
                            @elseif($record->attendance == 'Absent')
                            <span class="badge badge-danger">Absent</span>
                            @elseif($record->attendance == 'Late')
                            <span class="badge badge-warning">Late</span>
                            @else
                            <span class="badge badge-info">On Leave</span>
                            @endif
                          </td>
                        </tr>
                        @empty
                        <tr>
                          <td colspan="4" class="text-center text-muted py-4">No recent attendance records</td>
                        </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div> --}}

        </section>
      </div>
    </div>
  </div>

  <!-- Chart.js Library -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    // Teacher Attendance - Last 7 Days Chart
    const attendanceLabels = @json($last7Days);
    const attendanceData = @json($attendanceByDay);
    const absentData = @json($absentByDay);

    const attendanceChartCanvas = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceChartCanvas, {
      type: 'line',
      data: {
        labels: attendanceLabels,
        datasets: [
          {
            label: 'Present',
            data: attendanceData,
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
          },
          {
            label: 'Absent/Late/Leave',
            data: absentData,
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            borderColor: 'rgba(220, 53, 69, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'top',
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Teachers'
            }
          }
        }
      }
    });

    // Monthly Attendance Doughnut Chart
    const monthlyCtx = document.getElementById('monthlyAttendanceChart').getContext('2d');
    new Chart(monthlyCtx, {
      type: 'doughnut',
      data: {
        labels: ['Present', 'Absent', 'Late', 'On Leave'],
        datasets: [{
          data: [
            {{ $monthlyAttendance->get('Present')->total ?? 0 }},
            {{ $monthlyAttendance->get('Absent')->total ?? 0 }},
            {{ $monthlyAttendance->get('Late')->total ?? 0 }},
            {{ $monthlyAttendance->get('On Leave')->total ?? 0 }}
          ],
          backgroundColor: [
            'rgba(40, 167, 69, 0.8)',
            'rgba(220, 53, 69, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(23, 162, 184, 0.8)'
          ],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    // Class Distribution Chart
    const classLabels = @json($classNames);
    const studentCounts = @json($studentCounts);

    const classChartCanvas = document.getElementById('classDistributionChart').getContext('2d');
    new Chart(classChartCanvas, {
      type: 'bar',
      data: {
        labels: classLabels,
        datasets: [{
          label: 'Number of Students',
          data: studentCounts,
          backgroundColor: 'rgba(54, 162, 235, 0.7)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Students'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Classes'
            }
          }
        }
      }
    });
  </script>

  @include('includes.edit_footer')