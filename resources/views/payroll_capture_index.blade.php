{{-- resources/views/payrolls/index.blade.php --}}
@include('includes.head')

<head>
    <style>
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table {
            min-width: 900px;
        }
        .active-filters .badge {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Payroll Management</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Payroll
                                    </button>
                                    <a href="{{ route('finance.payroll.create') }}" class="btn btn-success ml-2">
                                        <i class="fas fa-plus"></i> Create New Capture
                                    </a>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse @if(request()->hasAny(['filter_name', 'filter_section', 'filter_bank_name', 'filter_salary_min', 'filter_salary_max', 'filter_date_from', 'filter_date_to'])) show @endif" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('finance.payroll.index') }}" method="GET" class="row">
                                        <div class="form-group col-md-4">
                                            <label>Staff Name</label>
                                            <input type="text" class="form-control" name="filter_name"
                                                value="{{ request('filter_name') }}" placeholder="Enter staff name">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Section</label>
                                            <input type="text" class="form-control" name="filter_section"
                                                value="{{ request('filter_section') }}" placeholder="Enter section">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Bank Name</label>
                                            <input type="text" class="form-control" name="filter_bank_name"
                                                value="{{ request('filter_bank_name') }}" placeholder="Enter bank name">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Basic Salary (Min)</label>
                                            <input type="number" class="form-control" name="filter_salary_min"
                                                value="{{ request('filter_salary_min') }}" placeholder="Minimum salary">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Basic Salary (Max)</label>
                                            <input type="number" class="form-control" name="filter_salary_max"
                                                value="{{ request('filter_salary_max') }}" placeholder="Maximum salary">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Date From</label>
                                            <input type="date" class="form-control" name="filter_date_from"
                                                value="{{ request('filter_date_from') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Date To</label>
                                            <input type="date" class="form-control" name="filter_date_to"
                                                value="{{ request('filter_date_to') }}">
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('finance.payroll.index') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Display Active Filters -->
                            @if(request('filter_name') || request('filter_section') || request('filter_bank_name') || 
                               request('filter_salary_min') || request('filter_salary_max') || 
                               request('filter_date_from') || request('filter_date_to'))
                                <div class="card-body pt-2 pb-0">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters d-flex flex-wrap align-items-center">
                                        @if(request('filter_name'))
                                            <span class="badge badge-info">Name: {{ request('filter_name') }}</span>
                                        @endif
                                        @if(request('filter_section'))
                                            <span class="badge badge-info">Section: {{ request('filter_section') }}</span>
                                        @endif
                                        @if(request('filter_bank_name'))
                                            <span class="badge badge-info">Bank: {{ request('filter_bank_name') }}</span>
                                        @endif
                                        @if(request('filter_salary_min'))
                                            <span class="badge badge-info">Min Salary: {{ number_format(request('filter_salary_min'), 2) }}</span>
                                        @endif
                                        @if(request('filter_salary_max'))
                                            <span class="badge badge-info">Max Salary: {{ number_format(request('filter_salary_max'), 2) }}</span>
                                        @endif
                                        @if(request('filter_date_from'))
                                            <span class="badge badge-info">From: {{ request('filter_date_from') }}</span>
                                        @endif
                                        @if(request('filter_date_to'))
                                            <span class="badge badge-info">To: {{ request('filter_date_to') }}</span>
                                        @endif
                                        <a href="{{ route('finance.payroll.index') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div class="card-body">
                                <div class="table-wrapper">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th> <!-- Serial Number Column -->
                                                <th>Staff Name</th>
                                                <th>Basic Salary</th>
                                                <th>Allowances</th>
                                                <th>Total</th>
                                                <th>Section</th>
                                                <th>Bank Name</th>
                                                <th>Account Number</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($payrolls as $key => $payroll)
                                                <tr>
                                                    <td>{{ $payrolls->firstItem() + $key }}</td> <!-- Serial Number -->
                                                    <td>{{ $payroll->employee->name ?? 'N/A' }}</td>
                                                    <td>₦{{ number_format($payroll->basic_salary, 2) }}</td>
                                                    <td>₦{{ number_format($payroll->allowances, 2) }}</td>
                                                    <td><strong>₦{{ number_format($payroll->basic_salary + $payroll->allowances, 2) }}</strong></td>
                                                    <td>{{ $payroll->section->section_name ?? 'N/A' }}</td>
                                                    <td>{{ $payroll->bank_name }}</td>
                                                    <td>{{ $payroll->account_number }}</td>
                                                    <td class="text-nowrap">
                                                        <a href="{{ route('finance.payroll.edit', $payroll) }}" class="btn btn-sm btn-warning m-1">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger m-1" data-toggle="modal" data-target="#deleteModal"
                                                                data-payroll-id="{{ $payroll->id }}"
                                                                data-employee-name="{{ $payroll->employee->name ?? 'N/A' }}"
                                                                data-payroll-route="{{ route('finance.payroll.destroy', $payroll) }}">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4"> <!-- Updated colspan to 9 -->
                                                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                                        <p class="text-muted mb-0">No payroll records found.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination Controls with filter parameters -->
                                <div class="mt-4">
                                    {{ $payrolls->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Single Delete Confirmation Modal (Moved Outside the Table) -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the payroll for <strong id="modal-employee-name">N/A</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" action="" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-dismiss alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Handle delete modal population
            $('#deleteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var payrollId = button.data('payroll-id');
                var employeeName = button.data('employee-name');
                var payrollRoute = button.data('payroll-route');

                var modal = $(this);
                modal.find('#modal-employee-name').text(employeeName);
                modal.find('#deleteForm').attr('action', payrollRoute);
            });
        });
    </script>
</body>