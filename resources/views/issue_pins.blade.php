@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')
            
            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Issue PINs to Students</h4>
                            </div>
                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif

                                <form action="{{ route('pins.issue.store') }}" method="POST" id="issuePinForm">
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Section <span class="text-danger">*</span></label>
                                                <select class="form-control" id="section_id" name="section_id" required>
                                                    <option value="">Select Section</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('section_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Class <span class="text-danger">*</span></label>
                                                <select class="form-control" id="class_id" name="class_id" required>
                                                    <option value="">Select Section First</option>
                                                </select>
                                                @error('class_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Session <span class="text-danger">*</span></label>
                                                <select class="form-control" id="session_id" name="session_id" required>
                                                    <option value="">Select Section First</option>
                                                </select>
                                                @error('session_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Term <span class="text-danger">*</span></label>
                                                <select class="form-control" id="term_id" name="term_id" required>
                                                    <option value="">Select Session First</option>
                                                </select>
                                                @error('term_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="button" id="loadStudentsBtn" class="btn btn-info" disabled>
                                            Load Students
                                        </button>
                                    </div>

                                    <div id="studentsSection" style="display: none;">
                                        <hr>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5>Select Students</h5>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="selectAll">
                                                <label class="custom-control-label" for="selectAll">Select All</label>
                                            </div>
                                        </div>

                                        <div id="studentsList" class="border p-3" style="max-height: 400px; overflow-y: auto;">
                                            <!-- Students will be loaded here -->
                                        </div>

                                        <div class="form-group mt-3">
                                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                                Issue PINs to Selected Students
                                            </button>
                                            <span id="selectedCount" class="ml-3 text-muted">No students selected</span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load classes when section changes
            $('#section_id').change(function() {
                var sectionId = $(this).val();
                if (sectionId) {
                    $.get('{{ url("/pins/sessions") }}/' + sectionId, function(data) {
                        $('#session_id').empty().append('<option value="">Select Session</option>');
                        if (data.sessions.length > 0) {
                            data.sessions.forEach(function(session) {
                                $('#session_id').append('<option value="' + session.id + '">' + session.name + '</option>');
                            });
                            if (data.current_session_id) {
                                $('#session_id').val(data.current_session_id).trigger('change');
                            }
                        }
                    });

                    // Load classes for this section
                    $.get('/api/sections/' + sectionId + '/classes', function(data) {
                        $('#class_id').empty().append('<option value="">Select Class</option>');
                        if (data.classes && data.classes.length > 0) {
                            data.classes.forEach(function(cls) {
                                $('#class_id').append('<option value="' + cls.id + '">' + cls.name + '</option>');
                            });
                        }
                    }).fail(function() {
                        // Fallback: load all classes and filter by section
                        $('#class_id').empty().append('<option value="">Select Class</option>');
                    });

                    $('#term_id').empty().append('<option value="">Select Session First</option>');
                } else {
                    $('#session_id').empty().append('<option value="">Select Section First</option>');
                    $('#class_id').empty().append('<option value="">Select Section First</option>');
                    $('#term_id').empty().append('<option value="">Select Session First</option>');
                }
                checkLoadButton();
            });

            // Load terms when session changes
            $('#session_id').change(function() {
                var sessionId = $(this).val();
                if (sessionId) {
                    $.get('{{ url("/pins/terms") }}/' + sessionId, function(data) {
                        $('#term_id').empty().append('<option value="">Select Term</option>');
                        if (data.terms.length > 0) {
                            data.terms.forEach(function(term) {
                                $('#term_id').append('<option value="' + term.id + '">' + term.name + '</option>');
                            });
                            if (data.current_term_id) {
                                $('#term_id').val(data.current_term_id);
                            }
                        }
                    });
                } else {
                    $('#term_id').empty().append('<option value="">Select Session First</option>');
                }
                checkLoadButton();
            });

            $('#class_id, #term_id').change(function() {
                checkLoadButton();
            });

            function checkLoadButton() {
                var allSelected = $('#section_id').val() && $('#class_id').val() && 
                                $('#session_id').val() && $('#term_id').val();
                $('#loadStudentsBtn').prop('disabled', !allSelected);
            }

            // Load students
            $('#loadStudentsBtn').click(function() {
                var sectionId = $('#section_id').val();
                var classId = $('#class_id').val();
                
                $.get('{{ url("/pins/students") }}/' + sectionId + '/' + classId, function(data) {
                    $('#studentsList').empty();
                    
                    if (data.students.length > 0) {
                        data.students.forEach(function(student) {
                            $('#studentsList').append(`
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input student-checkbox" 
                                           id="student_${student.id}" name="students[]" value="${student.id}">
                                    <label class="custom-control-label" for="student_${student.id}">
                                        ${student.name} ${student.admission_no ? '(' + student.admission_no + ')' : ''}
                                    </label>
                                </div>
                            `);
                        });
                        $('#studentsSection').show();
                    } else {
                        $('#studentsList').html('<p class="text-muted">No students found in this class.</p>');
                        $('#studentsSection').show();
                    }
                    updateSelectedCount();
                }).fail(function() {
                    alert('Error loading students. Please try again.');
                });
            });

            // Select all checkbox
            $('#selectAll').change(function() {
                $('.student-checkbox').prop('checked', $(this).is(':checked'));
                updateSelectedCount();
            });

            // Individual checkbox change
            $(document).on('change', '.student-checkbox', function() {
                var totalCheckboxes = $('.student-checkbox').length;
                var checkedCheckboxes = $('.student-checkbox:checked').length;
                $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
                updateSelectedCount();
            });

            function updateSelectedCount() {
                var count = $('.student-checkbox:checked').length;
                $('#submitBtn').prop('disabled', count === 0);
                
                if (count === 0) {
                    $('#selectedCount').text('No students selected');
                } else if (count === 1) {
                    $('#selectedCount').text('1 student selected');
                } else {
                    $('#selectedCount').text(count + ' students selected');
                }
            }
        });
    </script>

    @include('includes.edit_footer')
</body>