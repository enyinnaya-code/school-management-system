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
                            <form class="needs-validation" novalidate method="POST" action="{{ route('tests.store') }}">
                                @csrf
                                <div class="card-header">
                                    <h4>Create New Test</h4>
                                </div>
                                <div class="card-body">

                                    <!-- Test Name -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Test
                                            Name</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="text" placeholder="Test name" name="test_name"
                                                class="form-control" oninput="this.value = this.value.toUpperCase()"
                                                required>
                                            @error('test_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Test Type -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Test
                                            Type</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select name="test_type" class="form-control" required>
                                                <option value="multiple_choice">Multiple Choice</option>
                                            </select>
                                            @error('test_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Duration -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Test
                                            Duration (minutes)</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="number" placeholder="e.g. 30" name="duration"
                                                class="form-control" min="1" required>
                                            @error('duration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Passmark -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Pass
                                            Mark</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="number" placeholder="e.g. 50" name="pass_mark"
                                                class="form-control" min="1" required>
                                            @error('pass_mark')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Section -->
                                    <div class="form-group row mb-4">
                                        <label
                                            class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Section</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select name="section_id" id="section_id" class="form-control" required>
                                                <option value="">Select Section</option>
                                                @foreach($sections as $section)
                                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Classes (Dynamic Checkboxes) -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">
                                            Classes <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-sm-12 col-md-7">
                                            <div id="classes-container" class="border p-3 rounded"
                                                style="max-height: 200px; overflow-y: auto;">
                                                <p class="text-muted mb-0">Please select a section first to load
                                                    classes.</p>
                                            </div>
                                            <small class="form-text text-danger mt-1">
                                                You must select at least one class.
                                            </small>
                                            @error('class_ids')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            @error('class_ids.*')
                                            <div class="invalid-feedback d-block">One or more selected classes are
                                                invalid.</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Course -->
                                    <div class="form-group row mb-4">
                                        <label
                                            class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Course</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select name="course_id" class="form-control" required>
                                                <option value="">Select Course</option>
                                                @foreach($courses as $course)
                                                <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('course_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer text-left">
                                    <button type="submit" class="btn btn-primary">Create Test</button>
                                    <a href="{{ route('tests.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('section_id').addEventListener('change', function() {
            const sectionId = this.value;
            const container = document.getElementById('classes-container');

            if (!sectionId) {
                container.innerHTML = '<p class="text-muted mb-0">Please select a section first to load classes.</p>';
                return;
            }

            container.innerHTML = '<p class="mb-0"><i class="fas fa-spinner fa-spin"></i> Loading classes...</p>';

            fetch(`/tests/classes-by-section/${sectionId}`)  // Make sure this route matches your controller method
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    if (data.length === 0) {
                        container.innerHTML = '<p class="text-warning mb-0">No classes found in this section.</p>';
                        return;
                    }

                    let checkboxes = '';
                    data.forEach(cls => {
                        checkboxes += `
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="class_${cls.id}" 
                                       name="class_ids[]" 
                                       value="${cls.id}">
                                <label class="custom-control-label" for="class_${cls.id}">
                                    ${cls.name}
                                </label>
                            </div>
                        `;
                    });

                    container.innerHTML = checkboxes;
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<p class="text-danger mb-0">Error loading classes. Please try again.</p>';
                });
        });
    </script>

    @include('includes.edit_footer')
</body>

</html>