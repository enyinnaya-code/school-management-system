{{-- resources/views/finance_overview.blade.php --}}
@include('includes.head')

<style>
    @media print {
        /* Hide everything except the printable area */
        body * {
            visibility: hidden;
        }
        
        #printableArea, #printableArea * {
            visibility: visible;
        }
        
        #printableArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        
        /* Set portrait orientation */
        @page {
            size: portrait;
            margin: 1cm;
        }
        
        /* Print-specific styling */
        .print-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .print-header h2 {
            margin: 5px 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .print-header h4 {
            margin: 5px 0;
            font-size: 22px;
        }
        
        .print-filters {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        
        .print-filters h6 {
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .print-filters div {
            font-size: 13px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        table th, table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        
        /* Hide action buttons in print */
        .no-print {
            display: none !important;
        }
    }
    
    /* Screen-only styles */
    @media screen {
        #printableArea {
            display: none;
        }
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
            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Income & Expenses Overview</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filters
                                    </button>
                                    <button class="btn btn-success ml-2" onclick="printTable()" type="button">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('finance.overview') }}" method="GET" class="row">
                                        <div class="form-group col-md-3">
                                            <label>Search</label>
                                            <input type="text" class="form-control" name="filter_search"
                                                value="{{ request('filter_search') }}">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Section</label>
                                            <select class="form-control" name="filter_section">
                                                <option value="">All Sections</option>
                                                @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{
                                                    request('filter_section')==$section->id ? 'selected' : '' }}>
                                                    {{ $section->section_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Type</label>
                                            <select class="form-control" name="filter_type">
                                                <option value="all" {{ request('filter_type', 'all' )=='all'
                                                    ? 'selected' : '' }}>All</option>
                                                <option value="income" {{ request('filter_type')=='income' ? 'selected'
                                                    : '' }}>Income</option>
                                                <option value="expense" {{ request('filter_type')=='expense'
                                                    ? 'selected' : '' }}>Expense</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Date From</label>
                                            <input type="date" class="form-control" name="filter_date_from"
                                                value="{{ request('filter_date_from') }}">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Date To</label>
                                            <input type="date" class="form-control" name="filter_date_to"
                                                value="{{ request('filter_date_to') }}">
                                        </div>
                                        <div class="form-group col-md-1 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Summary Cards -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Total Income</h6>
                                                        <h4 class="mb-0">₦{{ number_format($totalIncome, 2) }}</h4>
                                                    </div>
                                                    <i class="fas fa-arrow-up fa-2x opacity-75"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-danger text-white">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Total Expenses</h6>
                                                        <h4 class="mb-0">₦{{ number_format($totalExpense, 2) }}</h4>
                                                    </div>
                                                    <i class="fas fa-arrow-down fa-2x opacity-75"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Net Balance</h6>
                                                        <h4 class="mb-0">₦{{ number_format($net, 2) }}</h4>
                                                    </div>
                                                    <i class="fas fa-balance-scale fa-2x opacity-75"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Total Transactions</h6>
                                                        <h4 class="mb-0">{{ $paginated->total() }}</h4>
                                                    </div>
                                                    <i class="fas fa-exchange-alt fa-2x opacity-75"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                              
                                <!-- Display Active Filters -->
                                @if(request('filter_search') || request('filter_section') || (request('filter_type') &&
                                request('filter_type') !== 'all') || request('filter_date_from') ||
                                request('filter_date_to'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_search'))
                                        <span class="badge badge-info mr-2">Search: {{ request('filter_search')
                                            }}</span>
                                        @endif
                                        @if(request('filter_section'))
                                        @php
                                        $selectedSection = $sections->firstWhere('id', request('filter_section'));
                                        @endphp
                                        <span class="badge badge-info mr-2">Section: {{ $selectedSection->section_name
                                            ?? request('filter_section') }}</span>
                                        @endif
                                        @if(request('filter_type') && request('filter_type') !== 'all')
                                        <span class="badge badge-info mr-2">Type: {{ ucfirst(request('filter_type'))
                                            }}</span>
                                        @endif
                                        @if(request('filter_date_from'))
                                        <span class="badge badge-info mr-2">From: {{ request('filter_date_from')
                                            }}</span>
                                        @endif
                                        @if(request('filter_date_to'))
                                        <span class="badge badge-info mr-2">To: {{ request('filter_date_to') }}</span>
                                        @endif
                                        <a href="{{ route('finance.overview') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Subtype</th>
                                                <th>Description</th>
                                                <th>Section</th>
                                                <th>Amount (₦)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($paginated as $index => $transaction)
                                            <tr>
                                                <td>{{ ($paginated->currentPage() - 1) * $paginated->perPage() + $index
                                                    + 1 }}</td>
                                                <td>{{ $transaction['date'] }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $transaction['type'] === 'income' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($transaction['type']) }}
                                                    </span>
                                                </td>
                                                <td>{{ $transaction['subtype'] }}</td>
                                                <td>{{ $transaction['description'] }}</td>
                                                <td>{{ $transaction['section_name'] }}</td>
                                                <td>{{ number_format($transaction['amount'], 2) }}</td>
                                                <td>
                                                    @if($transaction['model'] === 'Payment')
                                                    <a href="{{ route('payment.receipt', $transaction['id']) }}"
                                                        class="btn btn-sm btn-info" title="View Receipt">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @elseif($transaction['model'] === 'MiscFeePayment')
                                                    <a href="{{ route('misc.fee.payments.receipt', $transaction['id']) }}"
                                                        class="btn btn-sm btn-info" title="View Receipt">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @elseif($transaction['model'] === 'SalaryPayment')
                                                    <a href="{{ route('finance.payroll.slip', $transaction['id']) }}"
                                                        class="btn btn-sm btn-info" title="View Slip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @elseif($transaction['model'] === 'OtherExpense')
                                                    <a href="{{ route('other.expense.show', $transaction['id']) }}"
                                                        class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No transactions found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-3">
                                    {{ $paginated->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Hidden Printable Area -->
    <div id="printableArea">
        <div class="print-header">
            <h2>{{ config('app.name', 'School Name') }}</h2>
            <h4>Income & Expenses Overview</h4>
            <p style="margin: 5px 0;">Printed on: {{ date('F d, Y') }}</p>
        </div>
        
        @if(request('filter_search') || request('filter_section') || (request('filter_type') && request('filter_type') !== 'all') || request('filter_date_from') || request('filter_date_to'))
        <div class="print-filters">
            <h6>Active Filters:</h6>
            <div>
                @if(request('filter_search'))
                    <strong>Search:</strong> {{ request('filter_search') }} |
                @endif
                @if(request('filter_section'))
                    @php
                    $selectedSection = $sections->firstWhere('id', request('filter_section'));
                    @endphp
                    <strong>Section:</strong> {{ $selectedSection->section_name ?? request('filter_section') }} |
                @endif
                @if(request('filter_type') && request('filter_type') !== 'all')
                    <strong>Type:</strong> {{ ucfirst(request('filter_type')) }} |
                @endif
                @if(request('filter_date_from'))
                    <strong>From:</strong> {{ request('filter_date_from') }} |
                @endif
                @if(request('filter_date_to'))
                    <strong>To:</strong> {{ request('filter_date_to') }}
                @endif
            </div>
        </div>
        @endif
        
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Subtype</th>
                    <th>Description</th>
                    <th>Section</th>
                    <th>Amount (₦)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginated as $index => $transaction)
                <tr>
                    <td>{{ ($paginated->currentPage() - 1) * $paginated->perPage() + $index + 1 }}</td>
                    <td>{{ $transaction['date'] }}</td>
                    <td>
                        <span class="badge badge-{{ $transaction['type'] === 'income' ? 'success' : 'danger' }}">
                            {{ ucfirst($transaction['type']) }}
                        </span>
                    </td>
                    <td>{{ $transaction['subtype'] }}</td>
                    <td>{{ $transaction['description'] }}</td>
                    <td>{{ $transaction['section_name'] }}</td>
                    <td>{{ number_format($transaction['amount'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No transactions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="margin-top: 20px; text-align: right; font-size: 14px;">
            <p><strong>Total Income:</strong> ₦{{ number_format($totalIncome, 2) }}</p>
            <p><strong>Total Expenses:</strong> ₦{{ number_format($totalExpense, 2) }}</p>
            <p><strong>Net Balance:</strong> ₦{{ number_format($net, 2) }}</p>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        function printTable() {
            window.print();
        }
    </script>
</body>