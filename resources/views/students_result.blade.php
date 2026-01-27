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
                                <h4>Upload Results</h4>
                            </div>

                            <div class="card-body">
                                <!-- Results Upload Form -->
                                <form method="POST" action="{{ route('results.selectClass') }}">
                                    @csrf

                                    <!-- Section (Arm) Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">School Arm (Section)</label>
                                        <div class="col-md-6">
                                            <select class="form-control" name="section_id" id="section_id" required>
                                                <option value="">Select section...</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{ request()->section_id == $section->id ? 'selected' : '' }}>
                                                        {{ $section->section_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Class Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Class</label>
                                        <div class="col-md-6">
                                            <select class="form-control" name="class_id" id="class_id" required>
                                                <option value="">Select class...</option>
                                            </select>
                                            @error('class_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group mt-4 pt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-arrow-right"></i> Proceed to Students
                                        </button>
                                    </div>
                                </form>

                                <!-- Students List (shown only after selection) -->
                                @if(isset($students))
                                    @if($students->count() > 0)
                                        <div class="mt-5">
                                            <h4>Students in {{ $class->name ?? 'Selected Class' }}</h4>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Name</th>
                                                            <th>Admission No</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($students as $student)
                                                            <tr>
                                                                <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                                                                <td>{{ $student->name }}</td>
                                                                <td>{{ $student->admission_no }}</td>
                                                                <td>
                                                                    <a href="{{ route('student.result.upload', $student->id) }}" class="btn btn-sm btn-primary">
                                                                        Upload Result
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            {{ $students->links() }}
                                        </div>
                                    @else
                                        <div class="alert alert-info mt-4">
                                            No students found in the selected class/section.
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        $(document).ready(function() {
            var currentSectionId = '{{ request()->section_id ?? "" }}';
            var currentClassId = '{{ request()->class_id ?? "" }}';

            // If coming back after form submission, pre-load classes and select the class
            if (currentSectionId) {
                loadClasses(currentSectionId, currentClassId);
            }

            // Change handler for section
            $('#section_id').change(function() {
                var sectionId = $(this).val();
                if (sectionId) {
                    loadClasses(sectionId);
                } else {
                    $('#class_id').html('<option value="">Select class...</option>').prop('disabled', true);
                }
            });

            function loadClasses(sectionId, preselectClassId = null) {
                $('#class_id').html('<option value="">Loading...</option>').prop('disabled', true);

                $.get('/sections/' + sectionId + '/classes')
                    .done(function(data) {
                        $('#class_id').html('<option value="">Select class...</option>');
                        $.each(data, function(index, classItem) {
                            $('#class_id').append('<option value="' + classItem.id + '">' + classItem.name + '</option>');
                        });

                        // Pre-select class if provided (after submission)
                        if (preselectClassId || currentClassId) {
                            $('#class_id').val(preselectClassId || currentClassId);
                        }

                        $('#class_id').prop('disabled', false);
                    })
                    .fail(function() {
                        $('#class_id').html('<option value="">Error loading classes</option>');
                    });
            }
        });
    </script>
</body>