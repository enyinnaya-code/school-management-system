@include('includes.head')

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
                            <form action="{{ route('bursar.selectStudentForPayment') }}" method="POST" class="needs-validation" novalidate>
                                @csrf

                                <div class="card-header">
                                    <h4>Create New Payment</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Section (Arm) Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">School Arm (Section)</label>
                                        <div class="col-md-6">
                                            <select class="form-control" name="section_id" id="section_id" required>
                                                <option value="">Select section...</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}">{{ $section->section_name }}</option>
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
                                            <select class="form-control" name="class_id" id="class_id" disabled required>
                                                <option value="">Select class after choosing section...</option>
                                            </select>
                                            @error('class_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Student Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Student</label>
                                        <div class="col-md-6">
                                            <select class="form-control" name="student_id" id="student_id" disabled required>
                                                <option value="">Select student after choosing class...</option>
                                            </select>
                                            @error('student_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group mt-4 pt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-arrow-right"></i> Proceed to Payment Details
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')

        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        
        <script>
            $(document).ready(function() {
                // Initialize Select2 on student dropdown
                $('#student_id').select2({
                    placeholder: "Select student...",
                    allowClear: true
                });

                $('#section_id').change(function() {
                    var sectionId = $(this).val();
                    if (sectionId) {
                        $.get('/bursar/classes/' + sectionId, function(data) {
                            $('#class_id').html('<option value="">Loading...</option>');
                            $('#class_id').prop('disabled', true);
                            $('#student_id').html('<option value="">Select student after choosing class...</option>').prop('disabled', true);
                            $('#student_id').val(null).trigger('change');
                            setTimeout(function() {
                                $('#class_id').html('<option value="">Select class...</option>');
                                $.each(data, function(index, classItem) {
                                    $('#class_id').append('<option value="' + classItem.id + '">' + classItem.name + '</option>');
                                });
                                $('#class_id').prop('disabled', false);
                            }, 500);
                        }).fail(function() {
                            $('#class_id').html('<option value="">Error loading classes</option>');
                        });
                    } else {
                        $('#class_id').html('<option value="">Select class after choosing section...</option>').prop('disabled', true);
                        $('#student_id').html('<option value="">Select student after choosing class...</option>').prop('disabled', true);
                        $('#student_id').val(null).trigger('change');
                    }
                });

                $('#class_id').change(function() {
                    var classId = $(this).val();
                    var sectionId = $('#section_id').val();
                    if (classId && sectionId) {
                        $.get('/bursar/students/' + classId, function(data) {
                            $('#student_id').html('<option value="">Loading...</option>');
                            $('#student_id').prop('disabled', true);
                            $('#student_id').val(null).trigger('change');
                            setTimeout(function() {
                                $('#student_id').html('<option value="">Select student...</option>');
                                $.each(data, function(index, student) {
                                    $('#student_id').append('<option value="' + student.id + '">' + student.name + ' (' + student.admission_no + ')</option>');
                                });
                                $('#student_id').prop('disabled', false);
                                $('#student_id').trigger('change');
                            }, 500);
                        }).fail(function() {
                            $('#student_id').html('<option value="">Error loading students</option>');
                            $('#student_id').prop('disabled', false);
                        });
                    } else {
                        $('#student_id').html('<option value="">Select student after choosing class...</option>').prop('disabled', true);
                        $('#student_id').val(null).trigger('change');
                    }
                });
            });
        </script>
</body>