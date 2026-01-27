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
                            <form class="needs-validation" novalidate method="POST" action="{{ route('tests.update', $test->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="card-header">
                                    <h4>Edit Test</h4>
                                </div>
                                <div class="card-body">

                                    <!-- Test Name -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Test Name</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="text"
                                                   name="test_name"
                                                   class="form-control"
                                                   value="{{ old('test_name', $test->test_name) }}"
                                                   oninput="this.value = this.value.toUpperCase()"
                                                   required>
                                            @error('test_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Test Type -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Test Type</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select name="test_type" class="form-control" required>
                                                <option value="multiple_choice" {{ old('test_type', $test->test_type) == 'multiple_choice' ? 'selected' : '' }}>
                                                    Multiple Choice
                                                </option>
                                                <!-- Add others if needed -->
                                            </select>
                                            @error('test_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Duration -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Test Duration (minutes)</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="number"
                                                   name="duration"
                                                   class="form-control"
                                                   value="{{ old('duration', $test->duration) }}"
                                                   min="1">
                                            @error('duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Pass Mark -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Pass Mark</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="number"
                                                   name="pass_mark"
                                                   class="form-control"
                                                   value="{{ old('pass_mark', $test->pass_mark) }}"
                                                   min="1"
                                                   required>
                                            @error('pass_mark')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Section -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Section</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select name="section_id" id="section_id" class="form-control" required>
                                                <option value="">Select Section</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}"
                                                        {{ old('section_id', $test->section_id) == $section->id ? 'selected' : '' }}>
                                                        {{ $section->section_name }}
                                                    </option>
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
                                                @if(old('section_id', $test->section_id))
                                                    <p><i class="fas fa-spinner fa-spin"></i> Loading classes...</p>
                                                @else
                                                    <p class="text-muted mb-0">Please select a section to load classes.</p>
                                                @endif
                                            </div>
                                            <small class="form-text text-danger mt-1">
                                                You must select at least one class.
                                            </small>
                                            @error('class_ids')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Course -->
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Course</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select name="course_id" class="form-control" required>
                                                <option value="">Select Course</option>
                                                @foreach($courses as $course)
                                                    <option value="{{ $course->id }}"
                                                        {{ old('course_id', $test->course_id) == $course->id ? 'selected' : '' }}>
                                                        {{ $course->course_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('course_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>

                                <div class="card-footer text-left">
                                    <button type="submit" class="btn btn-primary">Update Test</button>
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
        // Pre-selected data from the test
        const currentSectionId = "{{ old('section_id', $test->section_id) }}";
        const selectedClassIds = @json($test->classes->pluck('id')->toArray());

        function loadClasses(sectionId, preSelected = []) {
            const container = document.getElementById('classes-container');
            container.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Loading classes...</p>';

            fetch(`/tests/classes-by-section/${sectionId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load');
                    return response.json();
                })
                .then(data => {
                    if (data.length === 0) {
                        container.innerHTML = '<p class="text-warning mb-0">No classes found in this section.</p>';
                        return;
                    }

                    let checkboxes = '';
                    data.forEach(cls => {
                        const checked = preSelected.includes(cls.id) ? 'checked' : '';
                        checkboxes += `
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="class_${cls.id}"
                                       name="class_ids[]"
                                       value="${cls.id}"
                                       ${checked}>
                                <label class="custom-control-label" for="class_${cls.id}">
                                    ${cls.name}
                                </label>
                            </div>
                        `;
                    });

                    container.innerHTML = checkboxes;
                })
                .catch(() => {
                    container.innerHTML = '<p class="text-danger mb-0">Error loading classes.</p>';
                });
        }

        // Load classes on page load if section is already selected
        if (currentSectionId) {
            loadClasses(currentSectionId, selectedClassIds);
        }

        // Reload when section changes
        document.getElementById('section_id').addEventListener('change', function() {
            if (this.value) {
                loadClasses(this.value, []); // Clear selection when section changes
            } else {
                document.getElementById('classes-container').innerHTML =
                    '<p class="text-muted mb-0">Please select a section to load classes.</p>';
            }
        });
    </script>

    @include('includes.edit_footer')
</body>
</html>