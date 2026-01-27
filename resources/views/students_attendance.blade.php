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
                                <h4>Mark Student Attendance</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Select Criteria
                                    </button>
                                </div>
                            </div>

                            <!-- Attendance Form -->
                            <form method="POST" action="{{ route('attendance.students.store') }}" id="attendanceForm">
                                @csrf

                                <!-- Filter Collapse Panel -->
                                <div class="collapse show" id="filterCollapse"> {{-- 'show' to expand by default --}}
                                    <div class="card-body pb-0">
                                        <div class="row">
                                            <!-- Session Selection -->
                                            <div class="form-group col-md-2">
                                                <label class="col-form-label">Session</label>
                                                <select class="form-control" id="session_select" name="session_id" required>
                                                    <option value="">Select a session...</option>
                                                    @foreach($sessions ?? [] as $session)
                                                        <option value="{{ $session->id }}" {{ ($currentSession && $session->id == $currentSession->id) ? 'selected' : '' }}>{{ $session->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('session_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Term Selection -->
                                            <div class="form-group col-md-2">
                                                <label class="col-form-label">Term</label>
                                                <select class="form-control" id="term_select" name="term_id" disabled required>
                                                    <option value="">Select a term...</option>
                                                </select>
                                                @error('term_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Attendance Date -->
                                            <div class="form-group col-md-2">
                                                <label class="col-form-label">Date</label>
                                                <input type="date" name="attendance_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>

                                            <!-- Attendance Time -->
                                            <div class="form-group col-md-2">
                                                <label class="col-form-label">Time</label>
                                                <input type="time" name="attendance_time" class="form-control" value="{{ date('H:i') }}" required>
                                            </div>

                                            <!-- Section Selection -->
                                            <div class="form-group col-md-2">
                                                <label class="col-form-label">Select Section</label>
                                                <select class="form-control" id="section" name="section_id" required>
                                                    <option value="">Select a section...</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('section_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Class Selection -->
                                            <div class="form-group col-md-2">
                                                <label class="col-form-label">Select Class</label>
                                                <select class="form-control" id="class" name="class_id" disabled required>
                                                    <option value="">Select a class...</option>
                                                </select>
                                                @error('class_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <!-- Students Table -->
                                    <div class="form-group px-3" id="studentsTable" style="display: none;">
                                        <label class="form-label">Students Attendance</label>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Attendance Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="studentsBody">
                                                <!-- Dynamically populated -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group mt-4 pt-4">
                                        <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                                            <i class="fas fa-save"></i> Save Attendance
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        $(document).ready(function() {
            // Initial load of terms for current session
            if ($('#session_select').val()) {
                loadTerms($('#session_select').val());
            }

            // When session changes, fetch terms
            $('#session_select').change(function() {
                var sessionId = $(this).val();
                if (sessionId) {
                    loadTerms(sessionId);
                } else {
                    $('#term_select').empty().append('<option value="">Select a term...</option>').prop('disabled', true);
                }
                // Reset other fields
                $('#section').val('').change();
                $('#studentsTable').hide();
                $('#submitBtn').prop('disabled', true);
            });

            function loadTerms(sessionId) {
                $.ajax({
                    url: '{{ route("attendance.students.terms") }}',
                    type: 'GET',
                    data: {
                        session_id: sessionId
                    },
                    success: function(data) {
                        $('#term_select').empty().append('<option value="">Select a term...</option>');
                        $.each(data, function(index, term) {
                            $('#term_select').append('<option value="' + term.id + '">' + term.name + '</option>');
                        });
                        // Set current term if exists
                        @if($currentTerm)
                            $('#term_select').val({{ $currentTerm->id }});
                        @endif
                        $('#term_select').prop('disabled', false);
                    },
                    error: function() {
                        alert('Error fetching terms. Please try again.');
                    }
                });
            }

            // When section changes, fetch classes
            $('#section').change(function() {
                var sectionId = $(this).val();
                if (sectionId) {
                    $.ajax({
                        url: '{{ route("attendance.students.classes") }}',
                        type: 'GET',
                        data: {
                            section_id: sectionId
                        },
                        success: function(data) {
                            $('#class').empty().append('<option value="">Select a class...</option>');
                            $('#studentsTable').hide();
                            $('#submitBtn').prop('disabled', true);
                            $('#class').prop('disabled', false);

                            $.each(data, function(index, classItem) {
                                $('#class').append('<option value="' + classItem.id + '">' + classItem.name + '</option>');
                            });
                        },
                        error: function() {
                            alert('Error fetching classes. Please try again.');
                        }
                    });
                } else {
                    $('#class').empty().append('<option value="">Select a class...</option>').prop('disabled', true);
                    $('#studentsTable').hide();
                    $('#submitBtn').prop('disabled', true);
                }
            });

            // When class changes, fetch students and populate table
            $('#class').change(function() {
                var classId = $(this).val();
                var sessionId = $('#session_select').val();
                var termId = $('#term_select').val();
                var attendanceDate = $('input[name="attendance_date"]').val();
                if (classId && termId && sessionId && attendanceDate) {
                    $.ajax({
                        url: '{{ route("attendance.students.get") }}',
                        type: 'GET',
                        data: {
                            class_id: classId,
                            session_id: sessionId,
                            term_id: termId,
                            attendance_date: attendanceDate
                        },
                        success: function(data) {
                            var tbody = $('#studentsBody');
                            tbody.empty();
                            if (data.length === 0) {
                                tbody.append(
                                    '<tr><td colspan="2" class="text-center text-muted">All students have attendance marked for this date.</td></tr>'
                                );
                                $('#submitBtn').prop('disabled', true);
                            } else {
                                $.each(data, function(index, student) {
                                    tbody.append(
                                        '<tr>' +
                                        '<td>' + student.name + '<input type="hidden" name="attendances[' + index + '][student_id]" value="' + student.id + '"></td>' +
                                        '<td>' +
                                        '<select class="form-control" name="attendances[' + index + '][status]" required>' +
                                        '<option value="">Select status...</option>' +
                                        '<option value="Present">Present</option>' +
                                        '<option value="Absent">Absent</option>' +
                                       
                                        '</select>' +
                                        '</td>' +
                                        '</tr>'
                                    );
                                });
                                $('#submitBtn').prop('disabled', false);
                            }
                            $('#studentsTable').show();
                        },
                        error: function() {
                            alert('Error fetching students. Please try again.');
                        }
                    });
                } else {
                    $('#studentsTable').hide();
                    $('#submitBtn').prop('disabled', true);
                }
            });

            // Listen for changes in term or date to refresh students if class is already selected
            $('#term_select, input[name="attendance_date"]').change(function() {
                if ($('#class').val()) {
                    $('#class').change(); // Trigger refetch
                }
            });
        });
    </script>
</body>