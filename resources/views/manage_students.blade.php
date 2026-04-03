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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Manage Students</h4>
                                <div class="d-flex gap-2">
                                    {{-- Export PDF button --}}
                                    <a href="{{ route('students.export.pdf', request()->only(['filter_section', 'filter_class'])) }}"
                                        class="btn btn-danger btn-sm mr-2" target="_blank"
                                        title="Export credentials as PDF">
                                        <i class="fas fa-file-pdf"></i> Export Credentials PDF
                                    </a>

                                    {{-- Reactivate All: only for admins AND only when filtering by suspended --}}
                                    @if(in_array(Auth::user()->user_type, [1, 2]) && request('filter_status') ===
                                    'suspended')
                                    <form action="{{ route('students.reactivate_all') }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to reactivate ALL suspended student accounts?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm mr-2"
                                            title="Reactivate all suspended students">
                                            <i class="fas fa-users"></i> Reactivate All Suspended
                                        </button>
                                    </form>
                                    @endif


                                    @if(in_array(Auth::user()->user_type, [1, 2]))
                                    <form action="{{ route('students.reset_all_passwords') }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to reset ALL student passwords to 12345?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning btn-sm mr-2"
                                            title="Reset all student passwords">
                                            <i class="fas fa-key"></i> Reset All Passwords
                                        </button>
                                    </form>
                                    @endif

                                    <button class="btn btn-primary btn-sm" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter students
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse {{ request()->hasAny(['filter_name','filter_admission_no','filter_section','filter_class','filter_gender','filter_date_added','filter_status']) ? 'show' : '' }}"
                                id="filterCollapse">
                                <div class="card-body row px-5 pb-0">
                                    <form action="{{ route('students.index') }}" method="GET" class="row mb-4 w-100">
                                        <div class="form-group col-md-3">
                                            <label>Student Name</label>
                                            <input type="text" class="form-control" name="filter_name"
                                                value="{{ request('filter_name') }}" placeholder="Search by name...">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Student ID (Admission No.)</label>
                                            <input type="text" class="form-control" name="filter_admission_no"
                                                value="{{ request('filter_admission_no') }}" placeholder="e.g. 0042">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Section</label>
                                            <select class="form-control" name="filter_section" id="sectionSelect">
                                                <option value="">-- All Sections --</option>
                                                @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{
                                                    request('filter_section')==$section->id ? 'selected' : '' }}>
                                                    {{ $section->section_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Class</label>
                                            <select class="form-control" name="filter_class" id="classSelect">
                                                <option value="">-- Select Class --</option>
                                                @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ request('filter_class')==$class->id
                                                    ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Gender</label>
                                            <select class="form-control" name="filter_gender">
                                                <option value="">-- Select Gender --</option>
                                                <option value="Male" {{ request('filter_gender')=='Male' ? 'selected'
                                                    : '' }}>Male</option>
                                                <option value="Female" {{ request('filter_gender')=='Female'
                                                    ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Account Status</label>
                                            <select class="form-control" name="filter_status">
                                                <option value="">-- All Statuses --</option>
                                                <option value="active" {{ request('filter_status')=='active'
                                                    ? 'selected' : '' }}>Active</option>
                                                <option value="suspended" {{ request('filter_status')=='suspended'
                                                    ? 'selected' : '' }}>Suspended</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Date Added</label>
                                            <input type="date" class="form-control" name="filter_date_added"
                                                value="{{ request('filter_date_added') }}">
                                        </div>
                                        <div class="form-group col-md-6 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('students.index') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">

                                {{-- Active filter badges --}}
                                @if(request()->hasAny(['filter_name','filter_admission_no','filter_section','filter_class','filter_gender','filter_date_added','filter_status']))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_name'))
                                        <span class="badge badge-info mr-2">Name: {{ request('filter_name') }}</span>
                                        @endif

                                        @if(request('filter_admission_no'))
                                        <span class="badge badge-info mr-2">Student ID: {{
                                            request('filter_admission_no') }}</span>
                                        @endif

                                        @if(request('filter_section'))
                                        @php $selectedSection = $sections->firstWhere('id', request('filter_section'));
                                        @endphp
                                        <span class="badge badge-info mr-2">
                                            Section: {{ $selectedSection ? $selectedSection->section_name : 'N/A' }}
                                        </span>
                                        @endif

                                        @if(request('filter_class'))
                                        @php $selectedClass = $classes->firstWhere('id', request('filter_class'));
                                        @endphp
                                        <span class="badge badge-info mr-2">
                                            Class: {{ $selectedClass ? $selectedClass->name : 'N/A' }}
                                        </span>
                                        @endif

                                        @if(request('filter_status'))
                                        <span class="badge badge-info mr-2">Status: {{ ucfirst(request('filter_status'))
                                            }}</span>
                                        @endif

                                        @if(request('filter_gender'))
                                        <span class="badge badge-info mr-2">Gender: {{ request('filter_gender')
                                            }}</span>
                                        @endif

                                        @if(request('filter_date_added'))
                                        <span class="badge badge-info mr-2">Date Added: {{ request('filter_date_added')
                                            }}</span>
                                        @endif

                                        <a href="{{ route('students.index') }}"
                                            class="btn btn-sm m-1 btn-outline-danger">
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
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Date Added</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($students as $index => $student)
                                            <tr>
                                                <td>{{ $students->firstItem() + $index }}</td>
                                                <td>
                                                    <span class="badge badge-secondary">
                                                        {{ $student->admission_no ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>{{ $student->name }}</td>
                                                <td>{{ $student->email ?? '-' }}</td>
                                                <td>{{ ucfirst($student->gender ?? '-') }}</td>
                                                <td>{{ $student->class->name ?? 'N/A' }}</td>
                                                <td>{{ $student->created_at->format('d M, Y') }}</td>
                                                <td>
                                                    @if($student->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                    @else
                                                    <span class="badge badge-danger">Suspended</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('students.edit', array_merge(['student' => $student->id], request()->only(['filter_name','filter_admission_no','filter_section','filter_class','filter_gender','filter_date_added','filter_status','page']))) }}"
                                                        class="btn m-1 btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('students.profile', $student->id) }}"
                                                        class="btn m-1 btn-sm btn-primary" title="View Profile">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                    <a href="{{ route('students.performance', $student->id) }}"
                                                        class="btn m-1 btn-sm btn-success" title="View Performance">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>

                                                    @if($student->is_active)
                                                    <button type="button" class="btn m-1 btn-sm btn-warning"
                                                        title="Suspend" data-toggle="modal"
                                                        data-target="#suspendModal{{ $student->id }}">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                    @else
                                                    <button type="button" class="btn btn-sm m-1 btn-success"
                                                        title="Activate" data-toggle="modal"
                                                        data-target="#activateModal{{ $student->id }}">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                    @endif

                                                    <button type="button" class="btn btn-sm m-1 btn-secondary"
                                                        title="Reset Password" data-toggle="modal"
                                                        data-target="#resetPasswordModal{{ $student->id }}">
                                                        <i class="fas fa-key"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm m-1 btn-danger"
                                                        title="Delete" data-toggle="modal"
                                                        data-target="#deleteModal{{ $student->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                                    No students found matching your filters.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    <div class="mt-3">
                                        {{ $students->appends(request()->query())->links() }}
                                    </div>
                                </div>
                            </div><!-- /.card-body -->
                        </div><!-- /.card -->
                    </div>
                </section>
            </div><!-- /.main-content -->
        </div>
    </div>

    {{-- ============================================================
    MODALS — one set per student
    ============================================================ --}}
    @foreach($students as $student)

    {{-- Current filters as hidden inputs (reused in all action forms for this student) --}}
    @php
    $filterInputs = '';
    foreach
    (request()->only(['filter_name','filter_admission_no','filter_section','filter_class','filter_gender','filter_date_added','filter_status','page'])
    as $key => $val)
    if ($val) $filterInputs .= '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars($val) . '">';
    @endphp

    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal{{ $student->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Student</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to suspend <strong>{{ $student->name }}</strong>?
                    <p class="text-warning mt-2">This will prevent the student from accessing the system.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form action="{{ route('students.suspend', $student->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        {!! $filterInputs !!}
                        <button type="submit" class="btn btn-warning">Yes, Suspend Student</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Activate Modal -->
    <div class="modal fade" id="activateModal{{ $student->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Activate Student</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to activate <strong>{{ $student->name }}</strong>?
                    <p class="text-success mt-2">This will restore the student's access to the system.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form action="{{ route('students.activate', $student->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        {!! $filterInputs !!}
                        <button type="submit" class="btn btn-success">Yes, Activate Student</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal{{ $student->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to reset the password for <strong>{{ $student->name }}</strong>?
                    <p class="text-info mt-2">Password will be reset to <strong>12345</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form action="{{ route('students.reset_password', $student->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        {!! $filterInputs !!}
                        <button type="submit" class="btn btn-info">Yes, Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal{{ $student->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Student</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Warning: This action cannot be undone!
                    </div>
                    <p>Are you sure you want to permanently delete <strong>{{ $student->name }}</strong>?</p>
                    <p>All associated student data will be removed from the system.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form action="{{ route('students.destroy', $student->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, Delete Student</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @endforeach

    @include('includes.edit_footer')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const sectionSelect = document.getElementById('sectionSelect');
        const classSelect   = document.getElementById('classSelect');

        if (!sectionSelect || !classSelect) return;

        const preSelectedClass = '{{ request('filter_class') }}';

        function updateClasses(sectionId, selectedClassId) {
            if (!sectionId) {
                classSelect.innerHTML = '<option value="">-- Select Class --</option>';
                return;
            }

            classSelect.innerHTML = '<option value="">Loading...</option>';
            classSelect.disabled = true;

            fetch(`{{ url('/get-classes') }}/${sectionId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    let options = '<option value="">-- Select Class --</option>';

                    if (data.classes && data.classes.length > 0) {
                        data.classes.forEach(cls => {
                            const selected = (cls.id == selectedClassId) ? 'selected' : '';
                            options += `<option value="${cls.id}" ${selected}>${cls.name}</option>`;
                        });
                    } else {
                        options = '<option value="">No classes in this section</option>';
                    }

                    classSelect.innerHTML = options;
                    classSelect.disabled = false;
                })
                .catch(err => {
                    console.error('Error loading classes:', err);
                    classSelect.innerHTML = '<option value="">Error loading classes</option>';
                    classSelect.disabled = false;
                });
        }

        sectionSelect.addEventListener('change', function () {
            updateClasses(this.value, null);
        });

        if (sectionSelect.value) {
            updateClasses(sectionSelect.value, preSelectedClass);
        }
    });
    </script>
</body>