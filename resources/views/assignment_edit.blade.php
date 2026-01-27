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
                                <h4>Edit Assignment</h4>
                            </div>

                            <div class="card-body">
                                <!-- Assignment Form -->
                                <form method="POST" action="{{ route('assignments.update', $assessment->id) }}">
                                    @csrf
                                    @method('PUT')

                                    <!-- Section Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Section <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="section_id" id="section_id" onchange="fetchClassesAndSessions()" required>
                                                <option value="">Select section...</option>
                                                @foreach ($sections as $section)
                                                <option value="{{ $section->id }}" {{ $assessment->section_id == $section->id ? 'selected' : '' }}>{{ $section->section_name }}</option>
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
                                                @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}" {{ $assessment->session_id == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                                                @endforeach
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
                                                @foreach ($terms as $term)
                                                <option value="{{ $term->id }}" {{ $assessment->term_id == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                                @endforeach
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
                                                @foreach ($classes as $class)
                                                <option value="{{ $class->id }}" {{ $assessment->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                @endforeach
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
                                                @foreach ($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ $assessment->course_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                                @endforeach
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
                                            <input type="text" name="title" class="form-control" value="{{ old('title', $assessment->title) }}" placeholder="Enter assignment title" required>
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Assignment Description -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Description</label>
                                        <div class="col-md-6">
                                            <textarea name="description" class="form-control" rows="4" placeholder="Enter assignment description">{{ old('description', $assessment->description) }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Due Date -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Due Date <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $assessment->due_date) }}" required>
                                            @error('due_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Total Marks -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Total Marks <span class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <input type="number" name="total_marks" class="form-control" value="{{ old('total_marks', $assessment->total_marks) }}" min="1" placeholder="Enter total marks" required>
                                            @error('total_marks')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group mt-4 pt-4">
                                        <a href="{{ route('assignments.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to List
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Assignment
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
                    data.forEach(session => {
                        const option = document.createElement('option');
                        option.value = session.id;
                        option.text = session.name;
                        sessionSelect.appendChild(option);
                    });
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
                    data.forEach(term => {
                        const option = document.createElement('option');
                        option.value = term.id;
                        option.text = term.name;
                        termSelect.appendChild(option);
                    });
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