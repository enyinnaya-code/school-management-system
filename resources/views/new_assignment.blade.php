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
                                <h4>Create New Assignment</h4>
                            </div>

                            <div class="card-body">
                                <!-- Assignment Form -->
                                <form method="POST" action="{{ route('assignments.store') }}">
                                    @csrf

                                    <!-- Section Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Section <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="section_id" id="section_id" onchange="fetchClassesAndSessions()" required>
                                                <option value="">Select section...</option>
                                                @foreach ($sections as $section)
                                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Session Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Session <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="session_id" id="session_id" onchange="fetchTerms()" required>
                                                <option value="">Select session...</option>
                                            </select>
                                            @error('session_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Term Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Term <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="term_id" id="term_id" required>
                                                <option value="">Select term...</option>
                                            </select>
                                            @error('term_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Class Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Class <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="class_id" id="class_id" onchange="fetchSubjects()" required>
                                                <option value="">Select class...</option>
                                            </select>
                                            @error('class_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Subject Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Subject <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="course_id" id="course_id" required>
                                                <option value="">Select subject...</option>
                                            </select>
                                            @error('course_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Assignment Title -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Title <span class="text-danger">*</span></label>
                                        <div class="col-md-6">
                                            <input type="text" name="title" class="form-control" placeholder="Enter assignment title" required>
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Assignment Description -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Description</label>
                                        <div class="col-md-6">
                                            <textarea name="description" class="form-control" rows="4" placeholder="Enter assignment description"></textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Due Date -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Due Date <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <input type="date" name="due_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                                            @error('due_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Total Marks -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Total Marks <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <input type="number" name="total_marks" class="form-control" min="1" placeholder="Enter total marks" required>
                                            @error('total_marks')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group mt-4 pt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-plus-circle"></i> Create Assignment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        function fetchClassesAndSessions() {
            const sectionId = document.getElementById('section_id').value;
            if (!sectionId) {
                // Clear dependent fields
                document.getElementById('class_id').innerHTML = '<option value="">Select class...</option>';
                document.getElementById('course_id').innerHTML = '<option value="">Select subject...</option>';
                document.getElementById('session_id').innerHTML = '<option value="">Select session...</option>';
                document.getElementById('term_id').innerHTML = '<option value="">Select term...</option>';
                return;
            }

            // Fetch classes
            fetch(`/assignments/classes/${sectionId}`)
                .then(response => response.json())
                .then(data => {
                    const classSelect = document.getElementById('class_id');
                    classSelect.innerHTML = '<option value="">Select class...</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.text = item.name;
                        classSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching classes:', error));

            // Fetch sessions
            fetch(`/assignments/sessions/${sectionId}`)
                .then(response => response.json())
                .then(data => {
                    const sessionSelect = document.getElementById('session_id');
                    sessionSelect.innerHTML = '<option value="">Select session...</option>';
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
                document.getElementById('term_id').innerHTML = '<option value="">Select term...</option>';
                return;
            }

            fetch(`/assignments/terms/${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    const termSelect = document.getElementById('term_id');
                    termSelect.innerHTML = '<option value="">Select term...</option>';
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
                })
                .catch(error => console.error('Error fetching terms:', error));
        }

        function fetchSubjects() {
            const classId = document.getElementById('class_id').value;
            if (!classId) {
                document.getElementById('course_id').innerHTML = '<option value="">Select subject...</option>';
                return;
            }

            fetch(`/assignments/subjects/${classId}`)
                .then(response => response.json())
                .then(data => {
                    const subjectSelect = document.getElementById('course_id');
                    subjectSelect.innerHTML = '<option value="">Select subject...</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.text = item.name;
                        subjectSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching subjects:', error));
        }
    </script>
</body>