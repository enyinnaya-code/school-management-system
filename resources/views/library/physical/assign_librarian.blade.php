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
                                <h4>Assign Librarians</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Teachers
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0 border-bottom">
                                    <form action="{{ route('physical_library.assign_librarian') }}" method="GET" class="row">
                                        <div class="form-group col-md-6">
                                            <label>Teacher Name</label>
                                            <input type="text" class="form-control" name="filter_name" id="filter_name"
                                                value="{{ request('filter_name') }}" placeholder="Search by name...">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Status</label>
                                            <select class="form-control" name="filter_status" id="filter_status">
                                                <option value="">All Teachers</option>
                                                <option value="assigned" {{ request('filter_status') == 'assigned' ? 'selected' : '' }}>
                                                    Assigned as Librarian
                                                </option>
                                                <option value="not_assigned" {{ request('filter_status') == 'not_assigned' ? 'selected' : '' }}>
                                                    Not Assigned
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('physical_library.assign_librarian') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <form action="{{ route('physical_library.store_assign_librarian') }}" method="POST">
                                @csrf

                                <div class="card-body">
                                    <!-- Display Active Filters -->
                                    @if(request('filter_name') || request('filter_status'))
                                    <div class="mb-3">
                                        <h6>Active Filters:</h6>
                                        <div class="active-filters">
                                            @if(request('filter_name'))
                                            <span class="badge badge-info mr-2">Name: {{ request('filter_name') }}</span>
                                            @endif
                                            @if(request('filter_status'))
                                            <span class="badge badge-info mr-2">
                                                Status: {{ request('filter_status') == 'assigned' ? 'Assigned' : 'Not Assigned' }}
                                            </span>
                                            @endif
                                            <a href="{{ route('physical_library.assign_librarian') }}" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i> Clear All
                                            </a>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Quick Search -->
                                    <div class="form-group">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="quickSearch" 
                                                   placeholder="Quick search by name or email...">
                                        </div>
                                    </div>

                                    @if($teachers->isEmpty())
                                        <p class="text-muted">No teachers available to assign as librarians.</p>
                                    @else
                                        <!-- Summary Stats -->
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <div class="card card-statistic-1">
                                                    <div class="card-icon bg-primary">
                                                        <i class="fas fa-users"></i>
                                                    </div>
                                                    <div class="card-wrap">
                                                        <div class="card-header">
                                                            <h4>Total Teachers</h4>
                                                        </div>
                                                        <div class="card-body" id="totalTeachers">
                                                            {{ $teachers->count() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card card-statistic-1">
                                                    <div class="card-icon bg-success">
                                                        <i class="fas fa-user-check"></i>
                                                    </div>
                                                    <div class="card-wrap">
                                                        <div class="card-header">
                                                            <h4>Assigned Librarians</h4>
                                                        </div>
                                                        <div class="card-body" id="assignedCount">
                                                            {{ $teachers->where('is_librarian', true)->count() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card card-statistic-1">
                                                    <div class="card-icon bg-warning">
                                                        <i class="fas fa-user-times"></i>
                                                    </div>
                                                    <div class="card-wrap">
                                                        <div class="card-header">
                                                            <h4>Not Assigned</h4>
                                                        </div>
                                                        <div class="card-body" id="notAssignedCount">
                                                            {{ $teachers->where('is_librarian', false)->count() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-striped" id="teachersTable">
                                                <thead>
                                                    <tr>
                                                        <th>S/N</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Current Status</th>
                                                        <th style="width: 150px;">Assign as Librarian</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($teachers as $index => $teacher)
                                                        <tr class="teacher-row" 
                                                            data-name="{{ strtolower($teacher->name) }}" 
                                                            data-email="{{ strtolower($teacher->email) }}"
                                                            data-status="{{ $teacher->is_librarian ? 'assigned' : 'not_assigned' }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $teacher->name }}</td>
                                                            <td>{{ $teacher->email }}</td>
                                                            <td>
                                                                @if($teacher->is_librarian)
                                                                    <span class="badge badge-success">
                                                                        <i class="fas fa-check-circle"></i> Currently Assigned
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-secondary">
                                                                        <i class="fas fa-times-circle"></i> Not Assigned
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" 
                                                                           class="custom-control-input librarian-checkbox" 
                                                                           id="teacher_{{ $teacher->id }}"
                                                                           name="librarians[]" 
                                                                           value="{{ $teacher->id }}" 
                                                                           {{ $teacher->is_librarian ? 'checked' : '' }}>
                                                                    <label class="custom-control-label" for="teacher_{{ $teacher->id }}">
                                                                        <span class="checkbox-label">
                                                                            {{ $teacher->is_librarian ? 'Assigned' : 'Assign' }}
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div id="noResults" class="alert alert-info" style="display: none;">
                                            <i class="fas fa-info-circle"></i> No teachers found matching your search criteria.
                                        </div>
                                    @endif
                                </div>

                                <div class="card-footer text-right pt-4">
                                    <button type="submit" class="btn btn-primary" {{ $teachers->isEmpty() ? 'disabled' : '' }}>
                                        <i class="fas fa-save"></i> Save Assignments
                                    </button>
                                    <a href="{{ route('physical_library.manage_books') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <script>
        $(document).ready(function() {
            // Ensure checkboxes are clickable
            $('.librarian-checkbox').on('click', function(e) {
                e.stopPropagation();
            });

            // Update label text on change
            $('.librarian-checkbox').on('change', function() {
                const label = $(this).siblings('label').find('.checkbox-label');
                if ($(this).is(':checked')) {
                    label.text('Assigned');
                } else {
                    label.text('Assign');
                }
                updateStats();
            });

            // Quick Search Functionality
            $('#quickSearch').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                let visibleRows = 0;

                $('.teacher-row').each(function() {
                    const name = $(this).data('name');
                    const email = $(this).data('email');
                    
                    if (name.includes(searchTerm) || email.includes(searchTerm)) {
                        $(this).show();
                        visibleRows++;
                    } else {
                        $(this).hide();
                    }
                });

                // Show/hide no results message
                if (visibleRows === 0) {
                    $('#noResults').show();
                    $('#teachersTable').hide();
                } else {
                    $('#noResults').hide();
                    $('#teachersTable').show();
                }

                // Update serial numbers
                updateSerialNumbers();
            });

            // Update serial numbers after filtering
            function updateSerialNumbers() {
                let counter = 1;
                $('.teacher-row:visible').each(function() {
                    $(this).find('td:first').text(counter++);
                });
            }

            // Update statistics
            function updateStats() {
                const total = $('.teacher-row').length;
                const assigned = $('.librarian-checkbox:checked').length;
                const notAssigned = total - assigned;

                $('#totalTeachers').text(total);
                $('#assignedCount').text(assigned);
                $('#notAssignedCount').text(notAssigned);
            }

            // Select All functionality (optional)
            $('#selectAll').on('change', function() {
                $('.librarian-checkbox').prop('checked', $(this).is(':checked'));
                $('.librarian-checkbox').trigger('change');
            });

            // Initial stats update
            updateStats();
        });
    </script>

    <style>
        /* Ensure checkboxes are fully clickable */
        .custom-control-input {
            pointer-events: auto !important;
            cursor: pointer !important;
        }
        
        .custom-control-label {
            cursor: pointer !important;
            user-select: none;
        }

        /* Fix z-index issues */
        .custom-control {
            position: relative;
            z-index: 1;
        }

        /* Highlight search results */
        .teacher-row {
            transition: background-color 0.3s ease;
        }

        /* Stats cards styling */
        .card-statistic-1 {
            box-shadow: 0 2px 6px rgba(0,0,0,.1);
            border: none;
        }

        .card-statistic-1 .card-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            font-size: 30px;
            color: #fff;
        }

        .card-statistic-1 .card-wrap {
            flex: 1;
            padding-left: 15px;
        }

        .card-statistic-1 .card-header h4 {
            font-size: 14px;
            margin-bottom: 5px;
            color: #6c757d;
        }

        .card-statistic-1 .card-body {
            font-size: 24px;
            font-weight: bold;
            color: #34395e;
        }

        /* Badge styling */
        .badge {
            font-size: 12px;
            padding: 5px 10px;
        }

        /* Active filters styling */
        .active-filters {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>
</body>