{{-- resources/views/other_expense_manage.blade.php --}}
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
                                <h4>Manage Other Expenses</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Expenses
                                    </button>
                                    <a href="{{ route('other.expense.create') }}" class="btn btn-success ml-2">
                                        <i class="fas fa-plus"></i> Create New
                                    </a>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('other.expense.manage') }}" method="GET" class="row">
                                        <div class="form-group col-md-3">
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
                                        <div class="form-group col-md-3">
                                            <label>Search Description</label>
                                            <input type="text" name="filter_description" class="form-control" 
                                                   value="{{ request('filter_description') }}">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Date From</label>
                                            <input type="date" name="filter_date_from" class="form-control" 
                                                   value="{{ request('filter_date_from') }}">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Date To</label>
                                            <input type="date" name="filter_date_to" class="form-control" 
                                                   value="{{ request('filter_date_to') }}">
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('other.expense.manage') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
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

                                <!-- Display Active Filters -->
                                @if(request('filter_section') || request('filter_description') || request('filter_date_from') || request('filter_date_to'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_section'))
                                        @php
                                        $selectedSection = $sections->firstWhere('id', request('filter_section'));
                                        @endphp
                                        <span class="badge badge-info mr-2">Section: {{ $selectedSection->section_name ?? request('filter_section') }}</span>
                                        @endif
                                        @if(request('filter_description'))
                                        <span class="badge badge-info mr-2">Description: {{ request('filter_description') }}</span>
                                        @endif
                                        @if(request('filter_date_from'))
                                        <span class="badge badge-info mr-2">From: {{ request('filter_date_from') }}</span>
                                        @endif
                                        @if(request('filter_date_to'))
                                        <span class="badge badge-info mr-2">To: {{ request('filter_date_to') }}</span>
                                        @endif
                                        <a href="{{ route('other.expense.manage') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Section</th>
                                                <th>Amount (₦)</th>
                                                <th>Description</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($expenses as $key => $expense)
                                                <tr>
                                                    <td>{{ $expenses->firstItem() + $key }}</td>
                                                    <td>{{ $expense->section ? $expense->section->section_name : 'All Sections' }}</td>
                                                    <td>{{ number_format($expense->amount, 2) }}</td>
                                                    <td>{{ Str::limit($expense->description, 50) }}</td>
                                                    <td>{{ $expense->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('other.expense.edit', $expense->id) }}" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="openDeleteModal('{{ $expense->id }}', '{{ $expense->description }}')">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No other expenses found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    {{ $expenses->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Delete Confirmation Modal -->
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
                    Are you sure you want to delete the expense with description "<span id="deleteItemName"></span>"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" action="" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </form>
                </div>
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

        function openDeleteModal(id, description) {
            document.getElementById('deleteItemName').textContent = description;
            document.getElementById('deleteForm').action = `/other-expense/${id}`;
            $('#deleteModal').modal('show');
        }
    </script>
</body>