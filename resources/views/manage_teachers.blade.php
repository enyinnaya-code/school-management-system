@include('includes.head')

<style>
    .detail-label {
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .detail-value {
        margin-bottom: 15px;
    }

    .modal-header .close {
        color: white;
        opacity: 0.8;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    /* Optional: Reduce badge margins for tighter layout */
    #modal-sections .badge,
    #modal-classes .badge,
    #modal-courses .badge {
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
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
                                <h4>Manage Staff</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse" aria-expanded="false">
                                        <i class="fas fa-filter"></i> Filter Staff
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Panel -->
                            <div class="collapse {{ request()->hasAny(['filter_name','filter_email','filter_teacher_type','filter_status','filter_form_teacher','filter_section_ids','filter_class_ids','filter_date_from','filter_date_to']) ? 'show' : '' }}"
                                id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('teachers.index') }}" method="GET" class="row">
                                        <div class="form-group col-md-3">
                                            <label>Name</label>
                                            <input type="text" class="form-control" name="filter_name"
                                                value="{{ request('filter_name') }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Email</label>
                                            <input type="text" class="form-control" name="filter_email"
                                                value="{{ request('filter_email') }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Staff Type</label>
                                            <select class="form-control" name="filter_teacher_type">
                                                <option value="">All Types</option>
                                                <option value="3" {{ request('filter_teacher_type')=='3' ? 'selected' : '' }}>Teacher</option>
                                                <option value="6" {{ request('filter_teacher_type')=='6' ? 'selected' : '' }}>Bursar</option>
                                                <option value="7" {{ request('filter_teacher_type')=='7' ? 'selected' : '' }}>Principal</option>
                                                <option value="8" {{ request('filter_teacher_type')=='8' ? 'selected' : '' }}>Vice-Principal</option>
                                                <option value="9" {{ request('filter_teacher_type')=='9' ? 'selected' : '' }}>Dean of Studies</option>
                                                <option value="10" {{ request('filter_teacher_type')=='10' ? 'selected' : '' }}>Guidance Counsellor</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Form Teacher</label>
                                            <select class="form-control" name="filter_form_teacher">
                                                <option value="">All Staff</option>
                                                <option value="1" {{ request('filter_form_teacher')=='1' ? 'selected' : '' }}>
                                                    Form Teachers Only
                                                </option>
                                                <option value="0" {{ request('filter_form_teacher')=='0' ? 'selected' : '' }}>
                                                    Not Form Teachers
                                                </option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Status</label>
                                            <select class="form-control" name="filter_status">
                                                <option value="">All Statuses</option>
                                                <option value="1" {{ request('filter_status')=='1' ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ request('filter_status')=='0' ? 'selected' : '' }}>Deactivated</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Section(s)</label>
                                            <select class="form-control" name="filter_section_ids[]">
                                                @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ in_array($section->id, (array)request('filter_section_ids')) ? 'selected' : '' }}>
                                                    {{ $section->section_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('teachers.index') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Active Filters -->
                            @if(request()->hasAny(['filter_name','filter_email','filter_teacher_type','filter_status','filter_form_teacher','filter_section_ids','filter_class_ids','filter_date_from','filter_date_to']))
                            <div class="card-body pt-0">
                                <h6>Active Filters:</h6>
                                <div class="active-filters">
                                    @if(request('filter_name'))
                                    <span class="badge badge-info mr-2">Name: {{ request('filter_name') }}</span>
                                    @endif
                                    @if(request('filter_email'))
                                    <span class="badge badge-info mr-2">Email: {{ request('filter_email') }}</span>
                                    @endif

                                    @if(request('filter_teacher_type') !== null && request('filter_teacher_type') !== '')
                                    <span class="badge badge-info mr-2">Type:
                                        @switch(request('filter_teacher_type'))
                                        @case(3) Teacher @break
                                        @case(6) Bursar @break
                                        @case(7) Principal @break
                                        @case(8) Vice-Principal @break
                                        @case(9) Dean of Studies @break
                                        @case(10) Guidance Counsellor @break
                                        @endswitch
                                    </span>
                                    @endif

                                    @if(request('filter_form_teacher') !== null && request('filter_form_teacher') !== '')
                                    <span class="badge badge-info mr-2">
                                        Form Teacher: {{ request('filter_form_teacher') == '1' ? 'Yes' : 'No' }}
                                    </span>
                                    @endif

                                    @if(request('filter_status') !== null && request('filter_status') !== '')
                                    <span class="badge badge-info mr-2">
                                        Status: {{ request('filter_status') == '1' ? 'Active' : 'Deactivated' }}
                                    </span>
                                    @endif

                                    @if(is_array(request('filter_section_ids')) && count(request('filter_section_ids')))
                                    @foreach($sections->whereIn('id', request('filter_section_ids')) as $section)
                                    <span class="badge badge-info mr-2">Section: {{ $section->section_name }}</span>
                                    @endforeach
                                    @endif

                                    @if(is_array(request('filter_class_ids')) && count(request('filter_class_ids')))
                                    @foreach($classes->whereIn('id', request('filter_class_ids')) as $class)
                                    <span class="badge badge-info mr-2">Class: {{ $class->name ?? $class->class_name }}</span>
                                    @endforeach
                                    @endif

                                    @if(request('filter_date_from'))
                                    <span class="badge badge-info mr-2">From: {{ request('filter_date_from') }}</span>
                                    @endif
                                    @if(request('filter_date_to'))
                                    <span class="badge badge-info mr-2">To: {{ request('filter_date_to') }}</span>
                                    @endif

                                    <a href="{{ route('teachers.index') }}" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times"></i> Clear All
                                    </a>
                                </div>
                            </div>
                            @endif

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="Teachers-table">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Staff Type</th>
                                                <th>Date Added</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($teachers as $index => $teacher)
                                            <tr>
                                                <td>{{ ($teachers->currentPage() - 1) * $teachers->perPage() + $index + 1 }}</td>
                                                <td>{{ $teacher->name }}</td>
                                                <td>{{ $teacher->email }}</td>
                                                <td>
                                                    @switch($teacher->user_type)
                                                    @case(3) Teacher @break
                                                    @case(6) Bursar @break
                                                    @case(7) Principal @break
                                                    @case(8) Vice-Principal @break
                                                    @case(9) Dean of Studies @break
                                                    @case(10) Guidance Counsellor @break
                                                    @default Unknown
                                                    @endswitch
                                                </td>
                                                <td>{{ $teacher->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <span class="badge {{ $teacher->is_active ? 'badge-success' : 'badge-danger' }}">
                                                        {{ $teacher->is_active ? 'Active' : 'Deactivated' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm m-1 btn-info view-staff-btn"
                                                        data-teacher-id="{{ $teacher->id }}" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>

                                                    <a href="{{ route('teachers.edit', ['id' => Crypt::encrypt($teacher->id)]) }}"
                                                        class="btn btn-sm m-1 btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <form action="{{ route('teachers.toggleActive', ['id' => Crypt::encrypt($teacher->id)]) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf @method('PATCH')
                                                        <button type="submit"
                                                            class="btn btn-sm m-1 {{ $teacher->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                            title="{{ $teacher->is_active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fas {{ $teacher->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                                        </button>
                                                    </form>

                                                    <a href="{{ route('teachers.reset', Crypt::encrypt($teacher->id)) }}"
                                                        class="btn btn-sm m-1 btn-primary" title="Reset Password">
                                                        <i class="fas fa-key"></i>
                                                    </a>

                                                    <form action="{{ route('teachers.destroy', ['id' => Crypt::encrypt($teacher->id)]) }}"
                                                        method="POST" style="display:inline;"
                                                        onsubmit="return confirm('Are you sure you want to delete this staff?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm m-1 btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4">
                                    {{ $teachers->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Single Shared View Details Modal - Compact Side-by-Side Layout -->
    <div class="modal fade" id="viewStaffModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-circle mr-2"></i>Staff Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Top: Personal Info (2 columns) -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value" id="modal-name"></div>

                            <div class="detail-label">Staff Type</div>
                            <div class="detail-value" id="modal-type"></div>

                            <div class="detail-label">Form Teacher</div>
                            <div class="detail-value" id="modal-form-teacher"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Email Address</div>
                            <div class="detail-value" id="modal-email"></div>

                            <div class="detail-label">Date Added</div>
                            <div class="detail-value" id="modal-date"></div>

                            <div class="detail-label">Status</div>
                            <div class="detail-value" id="modal-status"></div>
                        </div>
                    </div>

                    <hr>

                    <!-- Bottom: Assignments (2 or 3 columns depending on content) -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="detail-label">
                                <i class="fas fa-layer-group mr-1"></i>Sections (Arms)
                            </div>
                            <div class="detail-value" id="modal-sections"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">
                                <i class="fas fa-school mr-1"></i>Classes
                            </div>
                            <div class="detail-value" id="modal-classes"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">
                                <i class="fas fa-book mr-1"></i>Courses Taught
                            </div>
                            <div class="detail-value" id="modal-courses"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-warning" id="modal-edit-link">
                        <i class="fas fa-edit"></i> Edit Staff
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('includes.footer')

    <!-- JavaScript to populate the shared modal dynamically -->
    <script>
        document.querySelectorAll('.view-staff-btn').forEach(button => {
            button.addEventListener('click', function () {
                const teacherId = this.getAttribute('data-teacher-id');
                const teacher = teachersData.find(t => t.id == teacherId);

                if (!teacher) return;

                document.getElementById('modal-name').textContent = teacher.name;
                document.getElementById('modal-email').textContent = teacher.email;

                let typeText = 'Unknown';
                switch (parseInt(teacher.user_type)) {
                    case 3: typeText = 'Teacher'; break;
                    case 6: typeText = 'Bursar'; break;
                    case 7: typeText = 'Principal'; break;
                    case 8: typeText = 'Vice-Principal'; break;
                    case 9: typeText = 'Dean of Studies'; break;
                    case 10: typeText = 'Guidance Counsellor'; break;
                }
                document.getElementById('modal-type').innerHTML = `<span class="badge badge-primary">${typeText}</span>`;

                document.getElementById('modal-date').textContent = teacher.created_at;

                // Form Teacher
                if (teacher.is_form_teacher && teacher.form_class_name) {
                    document.getElementById('modal-form-teacher').innerHTML = `<span class="badge badge-success">Yes → ${teacher.form_class_name}</span>`;
                } else {
                    document.getElementById('modal-form-teacher').innerHTML = '<span class="text-muted">Not a Form Teacher</span>';
                }

                // Status
                const statusClass = teacher.is_active ? 'badge-success' : 'badge-danger';
                const statusText = teacher.is_active ? 'Active' : 'Deactivated';
                document.getElementById('modal-status').innerHTML = `<span class="badge ${statusClass}">${statusText}</span>`;

                // Sections
                if (teacher.sections.length > 0) {
                    document.getElementById('modal-sections').innerHTML = teacher.sections.map(s => `<span class="badge badge-info">${s}</span>`).join(' ');
                } else {
                    document.getElementById('modal-sections').innerHTML = '<span class="text-muted">No sections assigned</span>';
                }

                // Classes
                if (teacher.classes.length > 0) {
                    document.getElementById('modal-classes').innerHTML = teacher.classes.map(c => `<span>${c}</span>`).join(' ');
                } else {
                    document.getElementById('modal-classes').innerHTML = '<span class="text-muted">No classes assigned</span>';
                }

                // Courses
                if (teacher.courses.length > 0) {
                    document.getElementById('modal-courses').innerHTML = teacher.courses.map(c => `<span>${c}</span>`).join(' ');
                } else {
                    document.getElementById('modal-courses').innerHTML = '<span class="text-muted">No courses assigned</span>';
                }

                // Edit link
                document.getElementById('modal-edit-link').href = teacher.edit_url;

                // Show modal
                $('#viewStaffModal').modal('show');
            });
        });

        const teachersData = [
            @foreach($teachers as $teacher)
            {
                id: {{ $teacher->id }},
                name: "{{ addslashes($teacher->name) }}",
                email: "{{ $teacher->email }}",
                user_type: {{ $teacher->user_type }},
                created_at: "{{ $teacher->created_at->format('M d, Y') }}",
                is_active: {{ $teacher->is_active ? 'true' : 'false' }},
                is_form_teacher: {{ $teacher->is_form_teacher ? 'true' : 'false' }},
                form_class_name: "{{ $teacher->formClass?->name ?? '' }}",
                sections: [@foreach($teacher->sections as $section) "{{ addslashes($section->section_name) }}", @endforeach],
                classes: [@foreach($teacher->classes as $class) "{{ addslashes($class->name ?? $class->class_name ?? '') }}", @endforeach],
                courses: [@foreach($teacher->courses->unique('id') as $course) "{{ addslashes($course->course_name) }}", @endforeach],
                edit_url: "{{ route('teachers.edit', ['id' => Crypt::encrypt($teacher->id)]) }}"
            },
            @endforeach
        ];
    </script>
</body>
</html>