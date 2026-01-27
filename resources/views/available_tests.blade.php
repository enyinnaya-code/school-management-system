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
                                <h4>Available Approved Tests</h4>

                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Tests
                                    </button>
                                </div>
                            </div>

                            <div class="collapse" id="filterCollapse">
                                <div class="card-body row px-5 pb-0">
                                    <form action="{{ route('tests.available') }}" method="GET" class="row mb-4">
                                        <div class="form-group col-md-3">
                                            <label>Test Name</label>
                                            <input type="text" class="form-control" name="filter_test_name" value="{{ request('filter_test_name') }}">
                                        </div>

                                        @if(in_array(Auth::user()->user_type, [1, 2]))
                                        <div class="form-group col-md-3">
                                            <label>Class</label>
                                            <select class="form-control" name="filter_class">
                                                <option value="">-- Select Class --</option>
                                                @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ request('filter_class') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif

                                        <div class="form-group col-md-3">
                                            <label>Schedule From</label>
                                            <input type="date" class="form-control" name="filter_schedule_from" value="{{ request('filter_schedule_from') }}">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Schedule To</label>
                                            <input type="date" class="form-control" name="filter_schedule_to" value="{{ request('filter_schedule_to') }}">
                                        </div>

                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('tests.available') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Active Filters Display -->
                                @if(request('filter_test_name') || request('filter_class') || request('filter_schedule_from') || request('filter_schedule_to'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_test_name'))
                                        <span class="badge badge-info mr-2">Test Name: {{ request('filter_test_name') }}</span>
                                        @endif
                                        @if(request('filter_class'))
                                        <span class="badge badge-info mr-2">Class: {{ $classes->where('id', request('filter_class'))->first()->name ?? 'Unknown' }}</span>
                                        @endif
                                        @if(request('filter_schedule_from'))
                                        <span class="badge badge-info mr-2">Schedule From: {{ request('filter_schedule_from') }}</span>
                                        @endif
                                        @if(request('filter_schedule_to'))
                                        <span class="badge badge-info mr-2">Schedule To: {{ request('filter_schedule_to') }}</span>
                                        @endif

                                        <a href="{{ route('tests.available') }}" class="btn btn-sm m-1 btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Test Name</th>
                                                <th>Test Type</th>
                                                <th>Duration (Minutes)</th>
                                                <th>Classes</th>
                                                <th>Approval Status</th>
                                                <th>Scheduled Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($tests as $index => $test)
                                            <tr>
                                                <td>{{ $tests->firstItem() + $index }}</td>
                                                <td>{{ $test->test_name }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $test->test_type)) }}</td>
                                                <td>{{ $test->duration ?? 'N/A' }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#classesModal{{ $test->id }}" title="View Classes">
                                                        <i class="fas fa-users"></i> 
                                                        <span class="badge badge-light">{{ $test->classes->count() }}</span>
                                                    </button>
                                                </td>
                                                <td>
                                                    @if($test->is_approved)
                                                    <span class="badge badge-success">Approved</span>
                                                    @else
                                                    <span class="badge badge-warning">Not Approved</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($test->scheduled_date)
                                                    {{ \Carbon\Carbon::parse($test->scheduled_date)->format('d M, Y  g:i A') }}
                                                    @else
                                                    <span class="badge badge-warning">Not Scheduled</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No available tests found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    
                                    <!-- Pagination -->
                                    <div class="d-flex justify-content-end mt-3">
                                        {{ $tests->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Classes Modals (Outside the table loop) -->
    @foreach($tests as $test)
    <div class="modal fade" id="classesModal{{ $test->id }}" tabindex="-1" role="dialog" aria-labelledby="classesModalLabel{{ $test->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="classesModalLabel{{ $test->id }}">
                        <i class="fas fa-users"></i> Classes Taking: {{ $test->test_name }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($test->classes->count() > 0)
                        <div class="list-group">
                            @foreach($test->classes as $class)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-graduation-cap text-info"></i> 
                                        {{ $class->name }}
                                    </span>
                                    <span class="badge badge-info badge-pill">{{ $class->section->section_name ?? 'N/A' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i> No classes assigned to this test yet.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    @include('includes.footer')
</body>
</html>