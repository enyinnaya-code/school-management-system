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
                            <form class="needs-validation" novalidate class="col-md-6">
                                <div class="card-header">
                                    <h4>Manage Subjects</h4>
                                    <!-- Filter Button -->
                                    <div class="card-header-action">
                                        <button class="btn btn-primary" type="button" data-toggle="collapse"
                                            data-target="#filterCollapse">
                                            <i class="fas fa-filter"></i> Filter Subjects
                                        </button>
                                    </div>
                                </div>

                                <!-- Filter Collapse Panel -->
                                <div class="collapse" id="filterCollapse">
                                    <div class="card-body row px-5 pb-0">
                                        <form action="{{ route('course.manage') }}" method="GET" class="row mb-4">
                                            <div class="form-group col-md-4">
                                                <label>Course/Subject</label>
                                                <input type="text" class="form-control" name="filter_name"
                                                    value="{{ request('filter_name') }}">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>Date From</label>
                                                <input type="date" class="form-control" name="filter_date_from"
                                                    value="{{ request('filter_date_from') }}">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>Date To</label>
                                                <input type="date" class="form-control" name="filter_date_to"
                                                    value="{{ request('filter_date_to') }}">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>Section</label>
                                                <select class="form-control" name="filter_section">
                                                    <option value="">-- Select Section --</option>
                                                    @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{
                                                        request('filter_section')==$section->id ? 'selected' : '' }}>
                                                        {{ $section->section_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-12 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary mr-2">
                                                    <i class="fas fa-search"></i> Apply Filters
                                                </button>
                                                <a href="{{ route('course.manage') }}" class="btn btn-light">
                                                    <i class="fas fa-sync"></i> Reset
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <!-- Display Active Filters -->
                                    @if(request('filter_name') || request('filter_date_from') ||
                                    request('filter_date_to') || request('filter_section'))
                                    <div class="mb-3">
                                        <h6>Active Filters:</h6>
                                        <div class="active-filters">
                                            @if(request('filter_name'))
                                            <span class="badge badge-info mr-2">Class Name: {{ request('filter_name')
                                                }}</span>
                                            @endif
                                            @if(request('filter_date_from'))
                                            <span class="badge badge-info mr-2">From: {{ request('filter_date_from')
                                                }}</span>
                                            @endif
                                            @if(request('filter_date_to'))
                                            <span class="badge badge-info mr-2">To: {{ request('filter_date_to')
                                                }}</span>
                                            @endif
                                            @if(request('filter_section'))
                                            @php
                                            $selectedSection = $sections->firstWhere('id', request('filter_section'));
                                            @endphp
                                            <span class="badge badge-info mr-2">
                                                Section: {{ $selectedSection ? $selectedSection->section_name :
                                                'Unknown' }}
                                            </span>
                                            @endif

                                            <a href="{{ route('course.manage') }}"
                                                class="btn btn-sm m-1 btn-outline-danger">
                                                <i class="fas fa-times"></i> Clear All
                                            </a>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Course Name</th>
                                                    <th>Section</th>
                                                    <th>Classes</th>
                                                    <th>Added On</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($courses as $index => $course)
                                                <tr>
                                                    <td>{{ $courses->firstItem() + $index }}</td>
                                                    <td>{{ $course->course_name }}</td>
                                                    <td>{{ $course->section->section_name ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($course->schoolClasses->count() > 0)
                                                        <button type="button"
                                                            class="btn btn-info btn-sm view-classes-btn"
                                                            data-course-id="{{ $course->id }}"
                                                            data-course-name="{{ $course->course_name }}"
                                                            data-section-name="{{ $course->section->section_name ?? 'N/A' }}"
                                                            data-classes-count="{{ $course->schoolClasses->count() }}"
                                                            data-classes='@json($course->schoolClasses)'>
                                                            <i class="fas fa-list"></i> View Classes ({{
                                                            $course->schoolClasses->count() }})
                                                        </button>
                                                        @else
                                                        <span class="badge badge-warning">No Classes</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $course->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('course.edit', $course->id) }}"
                                                            class="btn btn-primary btn-sm m-1" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('course.delete', $course->id) }}"
                                                            method="POST" style="display:inline-block;"
                                                            onsubmit="return confirm('Are you sure you want to delete this course?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm m-1"
                                                                title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <div class="card-footer">
                                            {{ $courses->appends(request()->query())->links() }}
                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Single Classes Modal (Outside Loop) -->
    <!-- Single Classes Modal (Outside Loop) - SCROLLABLE VERSION -->
    <div class="modal fade" id="classesModal" tabindex="-1" role="dialog" aria-labelledby="classesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="classesModalLabel">
                        <i class="fas fa-users"></i> Classes Offering: <span id="modalCourseName"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p class="text-muted">
                            <strong>Section:</strong> <span id="modalSectionName"></span>
                        </p>
                        <p class="text-muted">
                            <strong>Total Classes:</strong> <span id="modalClassesCount"></span>
                        </p>
                    </div>
                    <hr>
                    <div class="row" id="modalClassesList">
                        <!-- Classes will be loaded here dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <a href="#" id="modalEditLink" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Course
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- End Single Classes Modal -->

    <!-- Add this CSS for better styling -->
    <style>
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }

        .modal-header.bg-primary {
            background-color: #4e73df !important;
        }

        .shadow {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
        }

        .text-xs {
            font-size: 0.7rem;
        }

        /* Make modal body scrollable with fixed height */
        .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 250px);
            overflow-y: auto;
        }

        /* Alternative: Set specific height */
        #classesModal .modal-body {
            max-height: 400px;
            overflow-y: auto;
        }

        /* Smooth scrolling */
        #classesModal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        #classesModal .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #classesModal .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        #classesModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <!-- jQuery Script for Modal -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle view classes button click
            $('.view-classes-btn').on('click', function() {
                const courseId = $(this).data('course-id');
                const courseName = $(this).data('course-name');
                const sectionName = $(this).data('section-name');
                const classesCount = $(this).data('classes-count');
                const classes = $(this).data('classes');
                
                // Update modal header and info
                $('#modalCourseName').text(courseName);
                $('#modalSectionName').text(sectionName);
                $('#modalClassesCount').text(classesCount);
                $('#modalEditLink').attr('href', `/courses/edit/${courseId}`);
                
                // Clear previous classes
                $('#modalClassesList').empty();
                
                // Populate classes
                if (classes && classes.length > 0) {
                    classes.forEach(function(classItem) {
                        const pivotDate = classItem.pivot && classItem.pivot.created_at 
                            ? new Date(classItem.pivot.created_at).toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            })
                            : 'N/A';
                        
                        const classCard = `
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-primary shadow py-1">
                                    <div class="card-body py-1">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="h6 mb-0 font-weight-bold text-dark">
                                                    <i class="fas fa-chalkboard-teacher"></i> ${classItem.name}
                                                </div>
                                                <div class="text-xs text-muted mt-1">
                                                    Added: ${pivotDate}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-check-circle fa-2x text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#modalClassesList').append(classCard);
                    });
                } else {
                    $('#modalClassesList').html(`
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle"></i> No classes are currently offering this course.
                            </div>
                        </div>
                    `);
                }
                
                // Show the modal
                $('#classesModal').modal('show');
            });
        });
    </script>

    @include('includes.edit_footer')
</body>