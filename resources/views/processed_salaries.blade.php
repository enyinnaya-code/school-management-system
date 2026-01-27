{{-- resources/views/processed_salaries.blade.php --}}
@include('includes.head')

<head>
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }
        .status-processed {
            background-color: #28a745;
            color: white;
        }
        .status-paid {
            background-color: #17a2b8;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: black;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .filter-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>

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
                                <h4>Processed Salary Payments</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#bulkSlipsModal">
                                        <i class="fas fa-file-pdf"></i> Generate Bulk Slips
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif

                                <!-- Filters -->
                                <div class="filter-card">
                                    <form method="GET" action="{{ route('finance.payroll.processed-salaries') }}">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Section</label>
                                                    <select name="filter_section" class="form-control">
                                                        <option value="">All Sections</option>
                                                        @foreach($sections as $section)
                                                            <option value="{{ $section->id }}" {{ request('filter_section') == $section->id ? 'selected' : '' }}>
                                                                {{ $section->section_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Term</label>
                                                    <select name="filter_term" class="form-control">
                                                        <option value="">All Terms</option>
                                                        @foreach($termNames as $termName)
                                                            <option value="{{ $termName->name }}" {{ request('filter_term') == $termName->name ? 'selected' : '' }}>
                                                                {{ $termName->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Month</label>
                                                    <select name="filter_month" class="form-control">
                                                        <option value="">All Months</option>
                                                        @for($i = 1; $i <= 12; $i++)
                                                            <option value="{{ $i }}" {{ request('filter_month') == $i ? 'selected' : '' }}>
                                                                {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                                                            </option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Year</label>
                                                    <select name="filter_year" class="form-control">
                                                        <option value="">All Years</option>
                                                        @foreach($years as $year)
                                                            <option value="{{ $year }}" {{ request('filter_year') == $year ? 'selected' : '' }}>
                                                                {{ $year }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Employee Name</label>
                                                    <input type="text" name="filter_employee" class="form-control" 
                                                           placeholder="Search by name..." value="{{ request('filter_employee') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-9 text-right">
                                                <label>&nbsp;</label><br>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Filter
                                                </button>
                                                <a href="{{ route('finance.payroll.processed-salaries') }}" class="btn btn-secondary">
                                                    <i class="fas fa-redo"></i> Reset
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Employee</th>
                                                <th>Section</th>
                                                <th>Month/Year</th>
                                                <th>Term</th>
                                                <th>Basic Salary</th>
                                                <th>Allowances</th>
                                                <th>Deductions</th>
                                                <th>Net Pay</th>
                                                <th>Processed By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($salaryPayments as $key => $payment)
                                                <tr>
                                                    <td>{{ $salaryPayments->firstItem() + $key }}</td>
                                                    <td>{{ $payment->employee->name ?? 'N/A' }}</td>
                                                    <td>{{ $payment->section->section_name ?? 'N/A' }}</td>
                                                    <td>{{ date('F', mktime(0, 0, 0, $payment->month, 10)) }} {{ $payment->year }}</td>
                                                    <td>{{ $payment->term->name ?? 'N/A' }}</td>
                                                    <td>₦{{ number_format($payment->basic_salary, 2) }}</td>
                                                    <td>₦{{ number_format($payment->allowances, 2) }}</td>
                                                    <td>₦{{ number_format($payment->deductions, 2) }}</td>
                                                    <td><strong>₦{{ number_format($payment->net_pay, 2) }}</strong></td>
                                                    <td>
                                                        {{ $payment->processedBy->name ?? 'N/A' }}<br>
                                                        <small class="text-muted">{{ $payment->processed_at ? $payment->processed_at->format('d M Y, H:i') : 'N/A' }}</small>
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <a href="{{ route('finance.payroll.payment-slip', $payment) }}" 
                                                           class="btn btn-sm btn-info" target="_blank" title="View Payment Slip">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center">No processed salary payments found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-3">
                                    {{ $salaryPayments->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Bulk Payment Slips Modal -->
    <div class="modal fade" id="bulkSlipsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('finance.payroll.bulk-payment-slips') }}" method="POST" target="_blank">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Bulk Payment Slips</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @php
                            $uniqueTerms = $allTerms->unique('name');
                        @endphp
                        <div class="form-group">
                            <label>Section <span class="text-danger">*</span></label>
                            <select name="section_id" class="form-control" required>
                                <option value="">Select Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Term <span class="text-danger">*</span></label>
                            <select name="term_id" class="form-control" required>
                                <option value="">Select Term</option>
                                @foreach($uniqueTerms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }} ({{ $term->session->name ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Month <span class="text-danger">*</span></label>
                            <select name="month" class="form-control" required>
                                <option value="">Select Month</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year <span class="text-danger">*</span></label>
                            <select name="year" class="form-control" required>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Generate Slips
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>