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
                                <h4>Manage Assignments</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Assignments
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('assignments.index') }}" method="GET" class="row">
                                        <div class="form-group col-md-2">
                                            <label>Section</label>
                                            <select class="form-control" id="section_id" name="filter_section" onchange="fetchClassesAndSessions()" data-saved="{{ request('filter_section') }}">
                                                <option value="">All Sections</option>
                                                @foreach ($sections as $section)
                                                <option value="{{ $section->id }}" {{ request('filter_section') == $section->id ? 'selected' : '' }}>{{ $section->section_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Class</label>
                                            <select class="form-control" id="class_id" name="filter_class" data-saved="{{ request('filter_class') }}">
                                                <option value="">All Classes</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Session</label>
                                            <select class="form-control" id="session_id" name="filter_session" onchange="fetchTerms()" data-saved="{{ request('filter_session') }}">
                                                <option value="">All Sessions</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Term</label>
                                            <select class="form-control" id="term_id" name="filter_term" data-saved="{{ request('filter_term') }}">
                                                <option value="">All Terms</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Title</label>
                                            <input type="text" class="form-control" name="filter_title"
                                                value="{{ request('filter_title') }}">
                                        </div>
                                        <div class="form-group col-md-1">
                                            <label>Date From</label>
                                            <input type="date" class="form-control" name="filter_due_from"
                                                value="{{ request('filter_due_from') }}">
                                        </div>
                                        <div class="form-group col-md-1">
                                            <label>Date To</label>
                                            <input type="date" class="form-control" name="filter_due_to"
                                                value="{{ request('filter_due_to') }}">
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('assignments.index') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Display Active Filters -->
                                @if(request('filter_section') || request('filter_class') || request('filter_session') || request('filter_term') || request('filter_title') || request('filter_due_from') || request('filter_due_to'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_section'))
                                        @php $sec = $sections->firstWhere('id', request('filter_section')); @endphp
                                        <span class="badge badge-info mr-2">Section: {{ $sec->section_name ?? '' }}</span>
                                        @endif
                                        @if(request('filter_class'))
                                        @php $cls = $classes->firstWhere('id', request('filter_class')); @endphp
                                        <span class="badge badge-info mr-2">Class: {{ $cls->name ?? '' }}</span>
                                        @endif
                                        @if(request('filter_session'))
                                        @php $ses = $sessions->firstWhere('id', request('filter_session')); @endphp
                                        <span class="badge badge-info mr-2">Session: {{ $ses->name ?? '' }}</span>
                                        @endif
                                        @if(request('filter_term'))
                                        @php $tr = $terms->firstWhere('id', request('filter_term')); @endphp
                                        <span class="badge badge-info mr-2">Term: {{ $tr->name ?? '' }}</span>
                                        @endif
                                        @if(request('filter_title'))
                                        <span class="badge badge-info mr-2">Title: {{ request('filter_title') }}</span>
                                        @endif
                                        @if(request('filter_due_from'))
                                        <span class="badge badge-info mr-2">From: {{ request('filter_due_from') }}</span>
                                        @endif
                                        @if(request('filter_due_to'))
                                        <span class="badge badge-info mr-2">To: {{ request('filter_due_to') }}</span>
                                        @endif
                                        <a href="{{ route('assignments.index') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Section</th>
                                                <th>Class</th>
                                                <th>Term</th>
                                                <th>Subject</th>
                                                <th>Title</th>
                                                <th>Due Date</th>
                                                <th>Total Marks</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($assessments as $assessment)
                                            <tr>
                                                <td>{{ $assessments->firstItem() + $loop->index }}</td>
                                                <td>{{ $assessment->section->section_name ?? 'N/A' }}</td>
                                                <td>{{ $assessment->schoolClass->name ?? 'N/A' }}</td>
                                                <td>{{ $assessment->term->name ?? 'N/A' }}</td>
                                                <td>{{ $assessment->course->course_name ?? 'N/A' }}</td>
                                                <td>{{ $assessment->title }}</td>
                                                <td>{{ $assessment->due_date->format('Y-m-d') }}</td>
                                                <td>{{ $assessment->total_marks }}</td>
                                                <td>
                                                    <a href="{{ route('assignments.edit', $assessment->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                                    <button type="button" onclick="confirmDelete({{ $assessment->id }})" class="btn btn-sm btn-danger">Delete</button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No assignments found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-center">
                                    {{ $assessments->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this assignment? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        function fetchClassesAndSessions() {
            const sectionId = document.getElementById('section_id').value;
            if (!sectionId) {
                // Clear dependent fields
                document.getElementById('class_id').innerHTML = '<option value="">All Classes</option>';
                document.getElementById('session_id').innerHTML = '<option value="">All Sessions</option>';
                document.getElementById('term_id').innerHTML = '<option value="">All Terms</option>';
                return;
            }

            // Fetch classes
            fetch(`/assignments/classes/${sectionId}`)
                .then(response => response.json())
                .then(data => {
                    const classSelect = document.getElementById('class_id');
                    classSelect.innerHTML = '<option value="">All Classes</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.text = item.name;
                        classSelect.appendChild(option);
                    });
                    // Set saved class if exists
                    const savedClass = classSelect.dataset.saved;
                    if (savedClass) {
                        classSelect.value = savedClass;
                    }
                })
                .catch(error => console.error('Error fetching classes:', error));

            // Fetch sessions
            fetch(`/assignments/sessions/${sectionId}`)
                .then(response => response.json())
                .then(data => {
                    const sessionSelect = document.getElementById('session_id');
                    sessionSelect.innerHTML = '<option value="">All Sessions</option>';
                    let hasCurrent = false;
                    data.forEach(session => {
                        const option = document.createElement('option');
                        option.value = session.id;
                        option.text = session.name;
                        if (session.is_current) {
                            option.selected = true;
                            hasCurrent = true;
                        }
                        sessionSelect.appendChild(option);
                    });
                    if (!hasCurrent && data.length > 0) {
                        sessionSelect.value = data[0].id;
                    }
                    // Set saved session if exists
                    const savedSession = sessionSelect.dataset.saved;
                    if (savedSession) {
                        sessionSelect.value = savedSession;
                    }
                    // Fetch terms if session selected
                    if (sessionSelect.value) {
                        fetchTerms();
                    }
                })
                .catch(error => console.error('Error fetching sessions:', error));
        }

        function fetchTerms() {
            const sessionId = document.getElementById('session_id').value;
            if (!sessionId) {
                document.getElementById('term_id').innerHTML = '<option value="">All Terms</option>';
                return;
            }

            fetch(`/assignments/terms/${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    const termSelect = document.getElementById('term_id');
                    termSelect.innerHTML = '<option value="">All Terms</option>';
                    let hasCurrent = false;
                    data.forEach(term => {
                        const option = document.createElement('option');
                        option.value = term.id;
                        option.text = term.name;
                        if (term.is_current) {
                            option.selected = true;
                            hasCurrent = true;
                        }
                        termSelect.appendChild(option);
                    });
                    if (!hasCurrent && data.length > 0) {
                        termSelect.value = data[0].id;
                    }
                    // Set saved term if exists
                    const savedTerm = termSelect.dataset.saved;
                    if (savedTerm) {
                        termSelect.value = savedTerm;
                    }
                })
                .catch(error => console.error('Error fetching terms:', error));
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sectionId = document.getElementById('section_id').value;
            if (sectionId) {
                fetchClassesAndSessions();
            }
        });

        function confirmDelete(id) {
            document.getElementById('deleteForm').action = `/assignments/${id}`;
            $('#deleteModal').modal('show');
        }
    </script>
</body>