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
                                <h4>Manage Timetables</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Timetables
                                    </button>
                                    <a href="{{ route('timetables.create') }}" class="btn btn-success ml-2">Create New Timetable</a>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('timetables.index') }}" method="GET" class="row">
                                        <div class="form-group col-md-4">
                                            <label>Section</label>
                                            <select class="form-control" name="filter_section">
                                                <option value="">All Sections</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{ request('filter_section') == $section->id ? 'selected' : '' }}>{{ $section->section_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Session</label>
                                            <select class="form-control" name="filter_session">
                                                <option value="">All Sessions</option>
                                                @foreach($sessions as $session)
                                                    <option value="{{ $session->id }}" {{ request('filter_session') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Term</label>
                                            <select class="form-control" name="filter_term">
                                                <option value="">All Terms</option>
                                                @foreach($terms as $term)
                                                    <option value="{{ $term->id }}" {{ request('filter_term') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('timetables.index') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Display Active Filters -->
                                @if(request('filter_section') || request('filter_session') || request('filter_term'))
                                    <div class="mb-3">
                                        <h6>Active Filters:</h6>
                                        <div class="active-filters">
                                            @if(request('filter_section'))
                                                <span class="badge badge-info mr-2">Section: {{ $sections->find(request('filter_section'))->section_name ?? 'Unknown' }}</span>
                                            @endif
                                            @if(request('filter_session'))
                                                <span class="badge badge-info mr-2">Session: {{ $sessions->find(request('filter_session'))->name ?? 'Unknown' }}</span>
                                            @endif
                                            @if(request('filter_term'))
                                                <span class="badge badge-info mr-2">Term: {{ $terms->find(request('filter_term'))->name ?? 'Unknown' }}</span>
                                            @endif
                                            <a href="{{ route('timetables.index') }}" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i> Clear All
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="timetables-table" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Section</th>
                                                <th>Session</th>
                                                <th>Term</th>
                                                <th>Periods per Day</th>
                                                <th>Lesson Duration</th>
                                                <th>Break Duration</th>
                                                <th>Breaks per Day</th>
                                                <th>Free Periods</th>
                                                <th>Created By</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($timetables as $index => $timetable)
                                                <tr>
                                                    <td>{{ ($timetables->currentPage() - 1) * $timetables->perPage() + $index + 1 }}</td>
                                                    <td>{{ $timetable->section->section_name }}</td>
                                                    <td>{{ $timetable->session->name }}</td>
                                                    <td>{{ $timetable->term->name }}</td>
                                                    <td>{{ $timetable->num_periods }}</td>
                                                    <td>{{ $timetable->lesson_duration }} mins</td>
                                                    <td>{{ $timetable->break_duration }} mins</td>
                                                    <td>{{ $timetable->num_breaks }}</td>
                                                    <td>{{ $timetable->has_free_periods ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $timetable->createdBy->name }}</td>
                                                    <td>{{ $timetable->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('timetables.show', $timetable->id) }}" class="btn m-1 btn-sm btn-info mr-1">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('timetables.edit', $timetable->id) }}" class="btn m-1 btn-sm btn-primary mr-1">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn m-1 btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal-{{ $timetable->id }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Compact Pagination -->
                                <div class="mt-4">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-start">
                                            <li class="page-item {{ $timetables->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $timetables->appends(request()->query())->previousPageUrl() }}" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                            @php
                                                $currentPage = $timetables->currentPage();
                                                $lastPage = $timetables->lastPage();
                                                $range = 2;
                                                $start = max(1, $currentPage - $range);
                                                $end = min($lastPage, $currentPage + $range);
                                            @endphp
                                            @if($start > 1)
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $timetables->appends(request()->query())->url(1) }}">1</a>
                                                </li>
                                                @if($start > 2)
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                @endif
                                            @endif
                                            @for($i = $start; $i <= $end; $i++)
                                                <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $timetables->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endfor
                                            @if($end < $lastPage)
                                                @if($end < $lastPage - 1)
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                @endif
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $timetables->appends(request()->query())->url($lastPage) }}">{{ $lastPage }}</a>
                                                </li>
                                            @endif
                                            <li class="page-item {{ $timetables->hasMorePages() ? '' : 'disabled' }}">
                                                <a class="page-link" href="{{ $timetables->appends(request()->query())->nextPageUrl() }}" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modals (Outside Table) -->
    @foreach($timetables as $timetable)
        <div class="modal fade" id="deleteModal-{{ $timetable->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel-{{ $timetable->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel-{{ $timetable->id }}">Confirm Delete</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this timetable for {{ $timetable->section->section_name }} - {{ $timetable->session->name }} - {{ $timetable->term->name }}? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('timetables.destroy', $timetable->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @include('includes.edit_footer')
</body>