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
                                    <h5 class="mb-0">Welcome, {{ Auth::user()->name }}!</h5>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="m-1">
                                            <i class="fas fa-user-tie mr-1"></i>
                                            <strong>Role:</strong> Bursar
                                        </div>
                                        @if($currentSession)
                                        <div class="m-1">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            <strong>Session:</strong> {{ $currentSession->name }}
                                        </div>
                                        @endif
                                        @if($currentTerm)
                                        <div class="m-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            <strong>Term:</strong> {{ $currentTerm->name }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dashboard Cards -->
                    <div class="row">

                        <!-- Today's Revenue -->
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Today's Revenue</h5>
                                                    <h2 class="mb-3 font-18">₦{{ number_format($todayRevenue) }}</h2>
                                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Collected today</p>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-money-bill-wave fa-3x text-success" style="font-size: 1.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Term Revenue -->
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Term Revenue</h5>
                                                    <h2 class="mb-3 font-18">₦{{ number_format($termRevenue) }}</h2>
                                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">This term</p>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-wallet fa-3x text-info" style="font-size: 1.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Outstanding Fees -->
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Outstanding Fees</h5>
                                                    <h2 class="mb-3 font-18 text-danger">₦{{ number_format($outstanding) }}</h2>
                                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Total arrears</p>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-exclamation-triangle fa-3x text-warning" style="font-size: 1.5rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Quick Actions (as subject-like cards) -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Quick Actions</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <a href="{{ route('payment.create') }}" class="text-decoration-none text-dark">
                                                <div class="card border hover-shadow">
                                                    <div class="card-body text-center py-4">
                                                        <i class="fas fa-plus-circle fa-3x text-dark mb-3"></i>
                                                        <h6 class="mb-0">Record New Payment</h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>

                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <a href="{{ route('payment.manage') }}" class="text-decoration-none text-dark">
                                                <div class="card border  hover-shadow">
                                                    <div class="card-body text-center py-4">
                                                        <i class="fas fa-list-alt fa-3x text-dark mb-3"></i>
                                                        <h6 class="mb-0">Manage Payments</h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>

                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <a href="{{ route('fee.prospectus.manage') }}" class="text-decoration-none text-dark">
                                                <div class="card border hover-shadow">
                                                    <div class="card-body text-center py-4">
                                                        <i class="fas fa-file-invoice-dollar fa-3x text-dark mb-3"></i>
                                                        <h6 class="mb-0">Fee Prospectus</h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Trend Chart -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Revenue Trend (Last 6 Months)</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="revenueChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Payments Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Recent Payments</h4>
                                </div>
                                <div class="card-body p-0 px-4">
                                    @if($recentPayments->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Student</th>
                                                    <th>Admission No.</th>
                                                    <th>Class</th>
                                                    <th>Amount</th>
                                                    <th>Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentPayments as $payment)
                                                <tr>
                                                    <td>{{ $payment->created_at->format('d M Y') }}</td>
                                                    <td>{{ $payment->student->name }}</td>
                                                    <td>{{ $payment->student->admission_no }}</td>
                                                    <td>{{ $payment->schoolClass->name ?? 'N/A' }}</td>
                                                    <td>₦{{ number_format($payment->amount) }}</td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $payment->payment_type }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="py-5 text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No payments recorded yet.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
            </div>

            <!-- Chart.js -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
            <script>
                const revenueLabels = @json($revenueLabels);
                const revenueData = @json($revenueData);

                const ctx = document.getElementById('revenueChart').getContext('2d');
                const revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            label: 'Monthly Revenue (₦)',
                            data: revenueData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Revenue: ₦' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₦' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                title: { display: true, text: 'Month' }
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