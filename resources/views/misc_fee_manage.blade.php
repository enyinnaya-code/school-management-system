{{-- resources/views/misc_fee_manage.blade.php --}}
@include('includes.head')

<head>
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }
        .status-active {
            background-color: #28a745;
            color: white;
        }
        .status-inactive {
            background-color: #000000;
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
                                <h4>Manage Miscellaneous Fee Types</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Fees
                                    </button>
                                    <a href="{{ route('misc.fee.create') }}" class="btn btn-success ml-2">
                                        <i class="fas fa-plus"></i> Create New Fee Type
                                    </a>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form method="GET" action="{{ route('misc.fee.manage') }}" class="row">
                                        <div class="form-group col-md-4">
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
                                        <div class="form-group col-md-4">
                                            <label>Name</label>
                                            <input type="text" name="filter_name" class="form-control" 
                                                   placeholder="Search by name..." value="{{ request('filter_name') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Amount Range</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <input type="number" name="filter_amount_min" class="form-control" 
                                                           placeholder="Min" value="{{ request('filter_amount_min') }}">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" name="filter_amount_max" class="form-control" 
                                                           placeholder="Max" value="{{ request('filter_amount_max') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('misc.fee.manage') }}" class="btn btn-light">
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
                                @if(request('filter_section') || request('filter_name') || request('filter_amount_min') || request('filter_amount_max'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_section'))
                                        @php
                                        $section_name = $sections->firstWhere('id', request('filter_section'))->section_name ?? 'Unknown';
                                        @endphp
                                        <span class="badge badge-info mr-2">Section: {{ $section_name }}</span>
                                        @endif
                                        @if(request('filter_name'))
                                        <span class="badge badge-info mr-2">Name: {{ request('filter_name') }}</span>
                                        @endif
                                        @if(request('filter_amount_min') || request('filter_amount_max'))
                                        <span class="badge badge-info mr-2">Amount: {{ request('filter_amount_min', 'Min') }} - {{ request('filter_amount_max', 'Max') }}</span>
                                        @endif
                                        <a href="{{ route('misc.fee.manage') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Amount (₦)</th>
                                                <th>Section</th>
                                                <th>Created By</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($feeTypes as $key => $feeType)
                                                <tr>
                                                    <td>{{ $feeTypes->firstItem() + $key }}</td>
                                                    <td>{{ $feeType->name }}</td>
                                                    <td>{{ Str::limit($feeType->description ?? 'N/A', 50) }}</td>
                                                    <td>₦{{ number_format($feeType->amount, 2) }}</td>
                                                    <td>{{ $feeType->section->section_name ?? 'All Sections' }}</td>
                                                    <td>{{ $feeType->createdBy->name ?? 'N/A' }}</td>
                                                    <td>{{ $feeType->created_at->format('d M Y') }}</td>
                                                    <td class="text-nowrap">
                                                        <a href="{{ route('misc.fee.edit', $feeType) }}" 
                                                           class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form id="deleteForm{{ $feeType->id }}" action="{{ route('misc.fee.destroy', $feeType) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-form="deleteForm{{ $feeType->id }}" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No miscellaneous fee types found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-3">
                                    {{ $feeTypes->appends(request()->query())->links() }}
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
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this fee type? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Handle delete button clicks
            $('.delete-btn').on('click', function() {
                var formId = $(this).data('form');
                $('#confirmDeleteBtn').data('form', formId);
                $('#confirmDeleteModal').modal('show');
            });

            // Handle confirm delete
            $('#confirmDeleteBtn').on('click', function() {
                var formId = $(this).data('form');
                $('#' + formId).submit();
                $('#confirmDeleteModal').modal('hide');
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>