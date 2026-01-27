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
                                <h4>My Students</h4>

                                @if($assignedClasses->isNotEmpty())
                                    <div class="card-header-action">
                                        <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#filterCollapse">
                                            <i class="fas fa-filter"></i> Filter by Class
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <!-- Filter Collapse Panel (always open by default) -->
                            @if($assignedClasses->isNotEmpty())
                                <div class="collapse show" id="filterCollapse">
                                    <div class="card-body">
                                        <form action="{{ route('teachers.my_students') }}" method="GET">
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label>Select Class <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="filter_class" required>
                                                        <option value="">-- Select a Class --</option>
                                                        @foreach($assignedClasses as $class)
                                                            <option value="{{ $class->id }}" {{ request('filter_class') == $class->id ? 'selected' : '' }}>
                                                                {{ $class->name }} ({{ $class->section->section_name ?? 'N/A' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6 d-flex align-items-end">
                                                    <button type="submit" class="btn btn-primary mr-2">
                                                        <i class="fas fa-search"></i> View Students
                                                    </button>
                                                    <a href="{{ route('teachers.my_students') }}" class="btn btn-light">
                                                        <i class="fas fa-sync"></i> Reset
                                                    </a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            <!-- Active Filter Badge -->
                            @if(request('filter_class'))
                                <div class="card-body pt-0">
                                    <div class="mb-3">
                                        <h6>Active Filter:</h6>
                                        @php
                                            $selectedClass = $assignedClasses->firstWhere('id', request('filter_class'));
                                        @endphp
                                        <span class="badge badge-info mr-2">
                                            Class: {{ $selectedClass?->name ?? 'N/A' }}
                                            @if($selectedClass?->section)
                                                ({{ $selectedClass->section->section_name }})
                                            @endif
                                        </span>
                                        <a href="{{ route('teachers.my_students') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear Filter
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div class="card-body">
                                @if($assignedClasses->isEmpty())
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle"></i>
                                        You have no classes assigned yet. Contact the administrator to assign classes to you.
                                    </div>
                                @elseif(!request('filter_class'))
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle"></i>
                                        Please select a class from the filter above to view its students.
                                    </div>
                                @elseif($students->isEmpty())
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle"></i>
                                        No students found in the selected class.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Gender</th>
                                                    <th>Class</th>
                                                    <th>Section</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($students as $index => $student)
                                                    <tr>
                                                        <td>{{ $students->firstItem() + $index }}</td>
                                                        <td>
                                                            <a href="{{ route('students.profile', $student->id) }}">
                                                                {{ $student->name }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $student->email }}</td>
                                                        <td>{{ $student->gender ? ucfirst($student->gender) : '-' }}</td>
                                                        <td>{{ $student->class->name ?? 'N/A' }}</td>
                                                        <td>{{ $student->class?->section?->section_name ?? 'N/A' }}</td>
                                                        <td>
                                                            @if($student->is_active)
                                                                <span class="badge badge-success">Active</span>
                                                            @else
                                                                <span class="badge badge-danger">Deactivated</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <div class="mt-3">
                                            {{ $students->appends(request()->query())->links() }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.min.js') }}"></script>
    @include('includes.footer')
</body>