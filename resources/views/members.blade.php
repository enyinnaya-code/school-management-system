
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
                                <h4>Library Members</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Members
                                    </button>
                                    <a href="{{ route('physical_library.borrowing_returns') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-arrow-left"></i> Back to Borrowing & Returns
                                    </a>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('physical_library.members') }}" method="GET" class="row">
                                        <div class="form-group col-md-4">
                                            <label>Name</label>
                                            <input type="text" class="form-control" name="filter_name" value="{{ request('filter_name') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Membership ID</label>
                                            <input type="text" class="form-control" name="filter_membership_id" value="{{ request('filter_membership_id') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>User Type</label>
                                            <select class="form-control select2" name="filter_user_type">
                                                <option value="">All User Types</option>
                                                <option value="1" {{ request('filter_user_type') == '1' ? 'selected' : '' }}>Superadmin</option>
                                                <option value="2" {{ request('filter_user_type') == '2' ? 'selected' : '' }}>Admin</option>
                                                <option value="3" {{ request('filter_user_type') == '3' ? 'selected' : '' }}>Teacher</option>
                                                <option value="4" {{ request('filter_user_type') == '4' ? 'selected' : '' }}>Student</option>
                                                <option value="5" {{ request('filter_user_type') == '5' ? 'selected' : '' }}>Parent</option>
                                                <option value="6" {{ request('filter_user_type') == '6' ? 'selected' : '' }}>Librarian</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Status</label>
                                            <select class="form-control select2" name="filter_status">
                                                <option value="">All Statuses</option>
                                                <option value="active" {{ request('filter_status') == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ request('filter_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                <option value="suspended" {{ request('filter_status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('physical_library.members') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Display Active Filters -->
                                @if(request('filter_name') || request('filter_membership_id') || request('filter_user_type') || request('filter_status'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_name'))
                                        <span class="badge badge-info mr-2">Name: {{ request('filter_name') }}</span>
                                        @endif
                                        @if(request('filter_membership_id'))
                                        <span class="badge badge-info mr-2">Membership ID: {{ request('filter_membership_id') }}</span>
                                        @endif
                                        @if(request('filter_user_type'))
                                        <span class="badge badge-info mr-2">
                                            User Type: {{ ['1' => 'Superadmin', '2' => 'Admin', '3' => 'Teacher', '4' => 'Student', '5' => 'Parent', '6' => 'Librarian'][request('filter_user_type')] }}
                                        </span>
                                        @endif
                                        @if(request('filter_status'))
                                        <span class="badge badge-info mr-2">Status: {{ ucfirst(request('filter_status')) }}</span>
                                        @endif
                                        <a href="{{ route('physical_library.members') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <!-- Display Notifications -->
                                @if (session('success'))
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    {{ session('success') }}
                                </div>
                                @endif
                                @if (session('error'))
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    {{ session('error') }}
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="members-table" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Name</th>
                                                <th>Membership ID</th>
                                                <th>User Type</th>
                                                <th>Status</th>
                                                <th>Active Borrowings</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($members as $index => $member)
                                            <tr>
                                                <td>{{ ($members->currentPage() - 1) * $members->perPage() + $index + 1 }}</td>
                                                <td>{{ $member->user->name }}</td>
                                                <td>{{ $member->membership_id }}</td>
                                                <td>
                                                    {{ ['1' => 'Superadmin', '2' => 'Admin', '3' => 'Teacher', '4' => 'Student', '5' => 'Parent', '6' => 'Librarian'][$member->user->user_type] }}
                                                </td>
                                                <td>
                                                    @if($member->status == 'active')
                                                    <span class="badge badge-success">Active</span>
                                                    @elseif($member->status == 'inactive')
                                                    <span class="badge badge-secondary">Inactive</span>
                                                    @elseif($member->status == 'suspended')
                                                    <span class="badge badge-danger">Suspended</span>
                                                    @endif
                                                </td>
                                                <td>{{ $member->user->borrowings()->whereNull('returned_at')->count() }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Controls -->
                                <div class="mt-4">
                                    {{ $members->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <!-- Select2 Assets and Initialization -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select...",
                allowClear: true,
                width: '100%'
            });

            $('#members-table').DataTable({
                responsive: true,
                pageLength: 10,
                ordering: true,
                searching: false // Server-side filtering is handled by the form
            });
        });
    </script>
</body>
</html>
