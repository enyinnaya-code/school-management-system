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
                <section class="section">
                    <div class="section-header">
                        <h1>Financial Analysis</h1>
                        <div class="section-header-breadcrumb">
                            <div class="breadcrumb-item active"><a href="{{ route('dynamic.dashboard') }}">Dashboard</a></div>
                            <div class="breadcrumb-item">Financial Analysis</div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-filter"></i> Filters</h4>
                            <div class="card-header-action">
                                <a href="{{ route('finance.analysis') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-sync"></i> Clear Filters
                                </a>
                                <a href="{{ route('finance.analysis.export', request()->query()) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('finance.analysis') }}" id="filterForm">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Section</label>
                                            <select name="section_id" id="section_id" class="form-control">
                                                <option value="">All Sections</option>
                                                @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ $sectionId == $section->id ? 'selected' : '' }}>
                                                    {{ $section->section_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Session</label>
                                            <select name="session_id" id="session_id" class="form-control">
                                                <option value="">All Sessions</option>
                                                @foreach($sessions as $session)
                                                <option value="{{ $session->id }}" {{ $sessionId == $session->id ? 'selected' : '' }}>
                                                    {{ $session->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Term</label>
                                            <select name="term_id" id="term_id" class="form-control">
                                                <option value="">All Terms</option>
                                                @foreach($terms as $term)
                                                <option value="{{ $term->id }}" {{ $termId == $term->id ? 'selected' : '' }}>
                                                    {{ $term->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Class</label>
                                            <select name="class_id" id="class_id" class="form-control">
                                                <option value="">All Classes</option>
                                                @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-primary">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4>Total Expected</h4>
                                    </div>
                                    <div class="card-body">
                                        ₦{{ number_format($totalExpected, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4>Total Paid</h4>
                                    </div>
                                    <div class="card-body">
                                        ₦{{ number_format($totalPaid, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4>Total Outstanding</h4>
                                    </div>
                                    <div class="card-body">
                                        ₦{{ number_format($totalOutstanding, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-warning">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4>Collection Rate</h4>
                                    </div>
                                    <div class="card-body">
                                        {{ number_format($collectionRate, 2) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Single Card with Tabs for Breakdowns -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Financial Breakdown</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Nav Tabs -->
                                    <ul class="nav nav-tabs" id="breakdownTabs" role="tablist">
                                        @if($sectionBreakdown->isNotEmpty())
                                        <li class="nav-item">
                                            <a class="nav-link active" id="section-tab" data-toggle="tab" href="#section" role="tab">
                                                <i class="fas fa-building"></i> By Section
                                            </a>
                                        </li>
                                        @endif
                                        
                                        @if($sessionBreakdown->isNotEmpty())
                                        <li class="nav-item">
                                            <a class="nav-link {{ $sectionBreakdown->isEmpty() ? 'active' : '' }}" id="session-tab" data-toggle="tab" href="#session" role="tab">
                                                <i class="fas fa-calendar-alt"></i> By Session
                                            </a>
                                        </li>
                                        @endif
                                        
                                        @if($termBreakdown->isNotEmpty())
                                        <li class="nav-item">
                                            <a class="nav-link {{ $sectionBreakdown->isEmpty() && $sessionBreakdown->isEmpty() ? 'active' : '' }}" id="term-tab" data-toggle="tab" href="#term" role="tab">
                                                <i class="fas fa-clock"></i> By Term
                                            </a>
                                        </li>
                                        @endif
                                        
                                        @if($classBreakdown->isNotEmpty())
                                        <li class="nav-item">
                                            <a class="nav-link {{ $sectionBreakdown->isEmpty() && $sessionBreakdown->isEmpty() && $termBreakdown->isEmpty() ? 'active' : '' }}" id="class-tab" data-toggle="tab" href="#class" role="tab">
                                                <i class="fas fa-users"></i> By Class
                                            </a>
                                        </li>
                                        @endif

                                        @if($topDebtors->isNotEmpty())
                                        <li class="nav-item">
                                            <a class="nav-link" id="debtors-tab" data-toggle="tab" href="#debtors" role="tab">
                                                <i class="fas fa-exclamation-circle"></i> Top Debtors
                                            </a>
                                        </li>
                                        @endif
                                    </ul>

                                    <!-- Tab Content -->
                                    <div class="tab-content mt-3" id="breakdownTabsContent">
                                        <!-- Section Breakdown Tab -->
                                        @if($sectionBreakdown->isNotEmpty())
                                        <div class="tab-pane fade show active" id="section" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Section</th>
                                                            <th class="text-right">Expected</th>
                                                            <th class="text-right">Paid</th>
                                                            <th class="text-right">Outstanding</th>
                                                            <th class="text-right">Collection Rate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($sectionBreakdown as $item)
                                                        <tr>
                                                            <td><strong>{{ $item['name'] }}</strong></td>
                                                            <td class="text-right">₦{{ number_format($item['expected'], 2) }}</td>
                                                            <td class="text-right text-success">₦{{ number_format($item['paid'], 2) }}</td>
                                                            <td class="text-right text-danger">₦{{ number_format($item['outstanding'], 2) }}</td>
                                                            <td class="text-right">
                                                                <span class="badge badge-{{ $item['rate'] >= 75 ? 'success' : ($item['rate'] >= 50 ? 'warning' : 'danger') }}">
                                                                    {{ number_format($item['rate'], 2) }}%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Session Breakdown Tab -->
                                        @if($sessionBreakdown->isNotEmpty())
                                        <div class="tab-pane fade {{ $sectionBreakdown->isEmpty() ? 'show active' : '' }}" id="session" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Session</th>
                                                            <th class="text-right">Expected</th>
                                                            <th class="text-right">Paid</th>
                                                            <th class="text-right">Outstanding</th>
                                                            <th class="text-right">Collection Rate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($sessionBreakdown as $item)
                                                        <tr>
                                                            <td><strong>{{ $item['name'] }}</strong></td>
                                                            <td class="text-right">₦{{ number_format($item['expected'], 2) }}</td>
                                                            <td class="text-right text-success">₦{{ number_format($item['paid'], 2) }}</td>
                                                            <td class="text-right text-danger">₦{{ number_format($item['outstanding'], 2) }}</td>
                                                            <td class="text-right">
                                                                <span class="badge badge-{{ $item['rate'] >= 75 ? 'success' : ($item['rate'] >= 50 ? 'warning' : 'danger') }}">
                                                                    {{ number_format($item['rate'], 2) }}%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Term Breakdown Tab -->
                                        @if($termBreakdown->isNotEmpty())
                                        <div class="tab-pane fade {{ $sectionBreakdown->isEmpty() && $sessionBreakdown->isEmpty() ? 'show active' : '' }}" id="term" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Term</th>
                                                            <th class="text-right">Expected</th>
                                                            <th class="text-right">Paid</th>
                                                            <th class="text-right">Outstanding</th>
                                                            <th class="text-right">Collection Rate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($termBreakdown as $item)
                                                        <tr>
                                                            <td><strong>{{ $item['name'] }}</strong></td>
                                                            <td class="text-right">₦{{ number_format($item['expected'], 2) }}</td>
                                                            <td class="text-right text-success">₦{{ number_format($item['paid'], 2) }}</td>
                                                            <td class="text-right text-danger">₦{{ number_format($item['outstanding'], 2) }}</td>
                                                            <td class="text-right">
                                                                <span class="badge badge-{{ $item['rate'] >= 75 ? 'success' : ($item['rate'] >= 50 ? 'warning' : 'danger') }}">
                                                                    {{ number_format($item['rate'], 2) }}%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Class Breakdown Tab -->
                                        @if($classBreakdown->isNotEmpty())
                                        <div class="tab-pane fade {{ $sectionBreakdown->isEmpty() && $sessionBreakdown->isEmpty() && $termBreakdown->isEmpty() ? 'show active' : '' }}" id="class" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Class</th>
                                                            <th class="text-right">Expected</th>
                                                            <th class="text-right">Paid</th>
                                                            <th class="text-right">Outstanding</th>
                                                            <th class="text-right">Collection Rate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($classBreakdown as $item)
                                                        <tr>
                                                            <td><strong>{{ $item['name'] }}</strong></td>
                                                            <td class="text-right">₦{{ number_format($item['expected'], 2) }}</td>
                                                            <td class="text-right text-success">₦{{ number_format($item['paid'], 2) }}</td>
                                                            <td class="text-right text-danger">₦{{ number_format($item['outstanding'], 2) }}</td>
                                                            <td class="text-right">
                                                                <span class="badge badge-{{ $item['rate'] >= 75 ? 'success' : ($item['rate'] >= 50 ? 'warning' : 'danger') }}">
                                                                    {{ number_format($item['rate'], 2) }}%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Top Debtors Tab -->
                                        @if($topDebtors->isNotEmpty())
                                        <div class="tab-pane fade" id="debtors" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Admission No</th>
                                                            <th>Student Name</th>
                                                            <th>Class</th>
                                                            <th>Section</th>
                                                            <th class="text-right">Expected</th>
                                                            <th class="text-right">Paid</th>
                                                            <th class="text-right">Outstanding</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($topDebtors as $index => $debtor)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $debtor['admission_no'] }}</td>
                                                            <td><strong>{{ $debtor['name'] }}</strong></td>
                                                            <td>{{ $debtor['class'] }}</td>
                                                            <td>{{ $debtor['section'] }}</td>
                                                            <td class="text-right">₦{{ number_format($debtor['expected'], 2) }}</td>
                                                            <td class="text-right text-success">₦{{ number_format($debtor['paid'], 2) }}</td>
                                                            <td class="text-right text-danger font-weight-bold">₦{{ number_format($debtor['outstanding'], 2) }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Trends Chart -->
                    @if($paymentTrends->isNotEmpty())
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Payment Trends (Last 12 Months)</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="paymentTrendsChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </section>
            </div>

            @include('includes.edit_footer')
        </div>
    </div>

    <!-- Chart.js Script -->
    @if($paymentTrends->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('paymentTrendsChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($paymentTrends->pluck('month')->map(function($month) {
                    return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y');
                })) !!},
                datasets: [{
                    label: 'Payments',
                    data: {!! json_encode($paymentTrends->pluck('total')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₦' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₦' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endif

    <!-- Dynamic Filters Script -->
    <script>
        $(document).ready(function() {
            // When section changes, update sessions and classes
            $('#section_id').change(function() {
                var sectionId = $(this).val();
                
                if (sectionId) {
                    // Update sessions
                    $.ajax({
                        url: '/api/sessions',
                        data: { section_id: sectionId },
                        success: function(data) {
                            $('#session_id').html('<option value="">All Sessions</option>');
                            $.each(data, function(key, value) {
                                $('#session_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                    });
                    
                    // Update classes
                    $.ajax({
                        url: '/api/sections/' + sectionId + '/classes',
                        success: function(data) {
                            $('#class_id').html('<option value="">All Classes</option>');
                            $.each(data.classes, function(key, value) {
                                $('#class_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                    });
                } else {
                    // Reset when "All Sections" is selected
                    $('#session_id').html('<option value="">All Sessions</option>');
                    $('#term_id').html('<option value="">All Terms</option>');
                    $('#class_id').html('<option value="">All Classes</option>');
                }
                
                // Clear terms
                $('#term_id').html('<option value="">All Terms</option>');
            });
            
            // When session changes, update terms
            $('#session_id').change(function() {
                var sessionId = $(this).val();
                
                if (sessionId) {
                    $.ajax({
                        url: '/api/terms',
                        data: { session_id: sessionId },
                        success: function(data) {
                            $('#term_id').html('<option value="">All Terms</option>');
                            $.each(data, function(key, value) {
                                $('#term_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                    });
                } else {
                    $('#term_id').html('<option value="">All Terms</option>');
                }
            });
        });
    </script>
</body>