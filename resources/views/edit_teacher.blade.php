@include('includes.head')

<style>
    #formClassSection[style*="display: block"] {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        min-height: 100px !important;
    }

    #formClassCheckboxes {
        display: block !important;
        min-height: 50px;
    }

    #formClassCheckboxes .form-check {
        display: block !important;
        margin-bottom: 10px;
        clear: both;
    }

    #formClassCheckboxes .form-check-input {
        display: inline-block !important;
        position: static !important;
        margin-right: 5px;
    }

    #formClassCheckboxes .form-check-label {
        display: inline-block !important;
        position: static !important;
    }

    #formClassCheckboxes .alert,
    #formClassCheckboxes h6 {
        display: block !important;
    }

    .form-check .form-check-label span {
        display: inline;
        position: relative;
        padding-left: 1rem;
    }

    .select-all-wrapper {
        /* background-color: #f8f9fa; */
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        /* border: 2px solid #dee2e6; */
    }

    .select-all-wrapper .form-check-input {
        width: 18px;
        height: 18px;
        margin-top: 2px;
    }

    .select-all-wrapper .form-check-label {
        font-weight: 600;
        font-size: 15px;
        color: #495057;
    }
</style>

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

                            @php
                                $roleNames = [
                                    3 => 'Teacher',
                                    6 => 'Bursar',
                                    7 => 'Principal',
                                    8 => 'Vice-Principal',
                                    9 => 'Dean of Studies',
                                    10 => 'Guidance Counsellor',
                                ];
                            @endphp

                            <form action="{{ route('teachers.update', Crypt::encrypt($teacher->id)) }}" method="POST"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="card-header">
                                    <h4>Edit {{ $roleNames[old('user_type', $teacher->user_type)] ?? 'Staff' }}</h4>
                                </div>

                                <div class="card-body">
                                    <!-- Full Name -->
                                    <div class="form-group col-md-6 px-0">
                                        <label>Full Name</label>
                                        <input type="text" name="name" class="form-control" required
                                            value="{{ old('name', $teacher->name) }}"
                                            oninput="this.value = this.value.toUpperCase()">
                                        <div class="valid-feedback">Looks good!</div>
                                    </div>

                                    <!-- Email -->
                                    <div class="form-group col-md-6 px-0">
                                        <label>Email Address</label>
                                        <input type="email" name="email" class="form-control" required
                                            value="{{ old('email', $teacher->email) }}">
                                        <div class="invalid-feedback">Please enter a valid email.</div>
                                    </div>

                                    <!-- User Type -->
                                    <div class="form-group col-md-6 px-0">
                                        <label>User Type</label>
                                        <select class="form-control" name="user_type" id="userTypeSelect" required>
                                            <option value="">-- Select Role --</option>
                                            @foreach ($roleNames as $id => $name)
                                                <option value="{{ $id }}"
                                                    {{ old('user_type', $teacher->user_type) == $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">
                                            Note: Principal, Vice-Principal, and Dean of Studies can also teach classes.
                                        </small>
                                    </div>

                                    <!-- Sections -->
                                    <div class="form-group col-md-12 px-0" id="sectionSection" style="display: none;">
                                        <label>Select Sections (Arms)</label><br>
                                        @foreach ($sections as $section)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input section-checkbox" type="checkbox"
                                                    name="section_ids[]" value="{{ $section->id }}"
                                                    id="section_{{ $section->id }}"
                                                    {{ in_array($section->id, old('section_ids', $assignedSectionIds ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="section_{{ $section->id }}">
                                                    {{ $section->section_name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Classes -->
                                    <div class="form-group col-md-12 px-0" id="classSection" style="display: none;">
                                        <label>Associated Classes</label>
                                        <div id="classCheckboxes"></div>
                                    </div>

                                    <!-- Courses -->
                                    <div class="form-group col-md-12 px-0" id="courseSection" style="display: none;">
                                        <label>Associated Courses</label>
                                        <div id="courseCheckboxes"></div>
                                    </div>

                                    <!-- Form Teacher Assignment -->
                                    <div class="form-group col-md-6 px-0" id="formTeacherSection" style="display: none;">
                                        <label>Form Teacher Assignment</label>
                                        <select class="form-control" name="is_form_teacher" id="isFormTeacherSelect">
                                            <option value="0" {{ old('is_form_teacher', $teacher->is_form_teacher ? 1 : 0) == 0 ? 'selected' : '' }}>
                                                Not a Form Teacher
                                            </option>
                                            <option value="1" {{ old('is_form_teacher', $teacher->is_form_teacher ? 1 : 0) == 1 ? 'selected' : '' }}>
                                                Yes, Assign as Form Teacher
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Form Class Selection -->
                                    <div class="form-group col-md-12 px-0" id="formClassSection" style="display: none; margin-top: 15px;">
                                        <label>Select Form Class</label>
                                        <div id="formClassCheckboxes"></div>
                                        <small class="text-muted">
                                            The staff will be the form teacher for the selected class.
                                        </small>
                                    </div>
                                </div>

                                <div class="card-footer text-left pt-5 mt-3">
                                    <button class="btn btn-primary" type="submit">Update Staff</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <script>
        const sectionCheckboxes = document.querySelectorAll('.section-checkbox');
        const classContainer = document.getElementById('classCheckboxes');
        const courseContainer = document.getElementById('courseCheckboxes');
        const formClassCheckboxes = document.getElementById('formClassCheckboxes');

        const userTypeSelect = document.getElementById('userTypeSelect');
        const isFormTeacherSelect = document.getElementById('isFormTeacherSelect');

        const sectionSection = document.getElementById('sectionSection');
        const classSection = document.getElementById('classSection');
        const courseSection = document.getElementById('courseSection');
        const formTeacherSection = document.getElementById('formTeacherSection');
        const formClassSection = document.getElementById('formClassSection');

        let classMap = {};
        let courseMap = {};
        let formClassMap = {};

        const assignedClassIds = @json(old('class_ids', $assignedClassIds ?? []));
        const assignedCourseIds = @json(old('course_ids', $assignedCourseIds ?? []));
        const currentFormClassId = {{ $teacher->form_class_id ?? 'null' }};

        // Pre-loaded assigned form teachers from controller
        const assignedFormTeachers = @json($assignedFormTeachers);
        const assignedFormClassIds = Object.keys(assignedFormTeachers).map(id => parseInt(id));

        // Roles that can teach
        const teachingRoles = ['3', '7', '8', '9', '10'];

        function updateTeacherFieldsVisibility() {
            const userType = userTypeSelect.value;
            const canTeach = teachingRoles.includes(userType);

            const displayStyle = canTeach ? 'block' : 'none';

            sectionSection.style.display = displayStyle;
            classSection.style.display = displayStyle;
            courseSection.style.display = displayStyle;
            formTeacherSection.style.display = displayStyle;

            if (!canTeach) {
                formClassSection.style.display = 'none';
                isFormTeacherSelect.value = '0';
                classContainer.innerHTML = '';
                courseContainer.innerHTML = '';
                formClassCheckboxes.innerHTML = '';
                classMap = {};
                courseMap = {};
                formClassMap = {};
                // Uncheck all section checkboxes
                sectionCheckboxes.forEach(cb => cb.checked = false);
            }

            // If can teach, but current form teacher status is no → hide form class section
            if (canTeach && isFormTeacherSelect.value !== '1') {
                formClassSection.style.display = 'none';
            }
        }

        // Initial visibility
        updateTeacherFieldsVisibility();
        userTypeSelect.addEventListener('change', updateTeacherFieldsVisibility);

        // Form teacher toggle
        isFormTeacherSelect.addEventListener('change', function () {
            if (this.value === '1' && teachingRoles.includes(userTypeSelect.value)) {
                formClassSection.style.display = 'block';
                loadFormClassesFromSelectedSections();
            } else {
                formClassSection.style.display = 'none';
                formClassCheckboxes.innerHTML = '';
            }
        });

        // Add class checkboxes with Select All
        function addClassCheckboxes(sectionId, classes) {
            if (classContainer.querySelector(`[data-section-id="${sectionId}"]`)) return;

            const wrapper = document.createElement('div');
            wrapper.classList.add('class-group', 'border', 'p-3', 'mb-3', 'rounded');
            wrapper.dataset.sectionId = sectionId;

            const sectionLabel = document.querySelector(`#section_${sectionId}`)?.nextElementSibling?.textContent.trim() || 'Unknown Section';
            const title = document.createElement('h6');
            title.textContent = `Classes in: ${sectionLabel}`;
            title.classList.add('mb-3', 'font-weight-bold');
            wrapper.appendChild(title);

            // Add Select All checkbox
            const selectAllWrapper = document.createElement('div');
            selectAllWrapper.classList.add('select-all-wrapper');

            const selectAllDiv = document.createElement('div');
            selectAllDiv.classList.add('form-check');

            const selectAllCheckbox = document.createElement('input');
            selectAllCheckbox.type = 'checkbox';
            selectAllCheckbox.classList.add('form-check-input', 'select-all-classes');
            selectAllCheckbox.id = `select_all_${sectionId}`;
            selectAllCheckbox.dataset.sectionId = sectionId;

            const selectAllLabel = document.createElement('label');
            selectAllLabel.classList.add('form-check-label');
            selectAllLabel.setAttribute('for', `select_all_${sectionId}`);
            selectAllLabel.innerHTML = 'Select All Classes';

            selectAllDiv.appendChild(selectAllCheckbox);
            selectAllDiv.appendChild(selectAllLabel);
            selectAllWrapper.appendChild(selectAllDiv);
            wrapper.appendChild(selectAllWrapper);

            const row = document.createElement('div');
            row.classList.add('row');

            classes.forEach(cls => {
                const col = document.createElement('div');
                col.classList.add('col-md-4');

                const div = document.createElement('div');
                div.classList.add('form-check');

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.classList.add('form-check-input', 'class-checkbox');
                checkbox.name = 'class_ids[]';
                checkbox.value = cls.id;
                checkbox.id = `class_${cls.id}`;
                checkbox.dataset.sectionId = sectionId;
                if (assignedClassIds.includes(cls.id)) checkbox.checked = true;

                const label = document.createElement('label');
                label.classList.add('form-check-label');
                label.setAttribute('for', `class_${cls.id}`);
                label.textContent = cls.name || 'Unnamed Class';

                div.appendChild(checkbox);
                div.appendChild(label);
                col.appendChild(div);
                row.appendChild(col);
            });

            wrapper.appendChild(row);
            classContainer.appendChild(wrapper);

            // Add event listener for Select All
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = wrapper.querySelectorAll('.class-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
            });

            // Add event listeners to individual checkboxes to update Select All state
            const classCheckboxes = wrapper.querySelectorAll('.class-checkbox');
            classCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    updateSelectAllState(sectionId);
                });
            });

            // Initialize Select All state
            updateSelectAllState(sectionId);
        }

        function updateSelectAllState(sectionId) {
            const wrapper = classContainer.querySelector(`[data-section-id="${sectionId}"]`);
            if (!wrapper) return;

            const selectAllCheckbox = wrapper.querySelector('.select-all-classes');
            const classCheckboxes = wrapper.querySelectorAll('.class-checkbox');
            
            const allChecked = Array.from(classCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(classCheckboxes).some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }

        function removeClassCheckboxes(sectionId) {
            const group = classContainer.querySelector(`[data-section-id="${sectionId}"]`);
            if (group) group.remove();
        }

        // Add course checkboxes
        function addCourseCheckboxes(sectionId, courses) {
            if (courseContainer.querySelector(`[data-section-id="${sectionId}"]`)) return;

            const wrapper = document.createElement('div');
            wrapper.classList.add('course-group', 'border', 'p-3', 'mb-3', 'rounded');
            wrapper.dataset.sectionId = sectionId;

            const sectionLabel = document.querySelector(`#section_${sectionId}`)?.nextElementSibling?.textContent.trim() || 'Unknown Section';
            const title = document.createElement('h6');
            title.textContent = `Courses in: ${sectionLabel}`;
            title.classList.add('mb-3', 'font-weight-bold');
            wrapper.appendChild(title);

            const row = document.createElement('div');
            row.classList.add('row');

            courses.forEach(course => {
                const col = document.createElement('div');
                col.classList.add('col-md-4');

                const div = document.createElement('div');
                div.classList.add('form-check');

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.classList.add('form-check-input');
                checkbox.name = 'course_ids[]';
                checkbox.value = course.id;
                checkbox.id = `course_${course.id}`;
                if (assignedCourseIds.includes(course.id)) checkbox.checked = true;

                const label = document.createElement('label');
                label.classList.add('form-check-label');
                label.setAttribute('for', `course_${course.id}`);
                label.textContent = course.course_name;

                div.appendChild(checkbox);
                div.appendChild(label);
                col.appendChild(div);
                row.appendChild(col);
            });

            wrapper.appendChild(row);
            courseContainer.appendChild(wrapper);
        }

        function removeCourseCheckboxes(sectionId) {
            const group = courseContainer.querySelector(`[data-section-id="${sectionId}"]`);
            if (group) group.remove();
        }

        // Section checkbox handler
        sectionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const sectionId = this.value;

                if (this.checked) {
                    if (!classMap[sectionId]) {
                        fetch(`/get-classes-by-sections?section_ids[]=${sectionId}`)
                            .then(r => r.json())
                            .then(data => {
                                classMap[sectionId] = data;
                                addClassCheckboxes(sectionId, data);
                            });
                    } else {
                        addClassCheckboxes(sectionId, classMap[sectionId]);
                    }

                    if (!courseMap[sectionId]) {
                        fetch(`/get-subjects-by-section/${sectionId}`)
                            .then(r => r.json())
                            .then(data => {
                                courseMap[sectionId] = data;
                                addCourseCheckboxes(sectionId, data);
                            });
                    } else {
                        addCourseCheckboxes(sectionId, courseMap[sectionId]);
                    }

                    if (isFormTeacherSelect.value === '1') {
                        loadFormClassesFromSelectedSections();
                    }
                } else {
                    removeClassCheckboxes(sectionId);
                    removeCourseCheckboxes(sectionId);
                    if (isFormTeacherSelect.value === '1') {
                        loadFormClassesFromSelectedSections();
                    }
                }
            });
        });

        // Form class loading and rendering
        function loadFormClassesFromSelectedSections() {
            const checkedIds = Array.from(sectionCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => parseInt(cb.value));

            formClassCheckboxes.innerHTML = '<p class="text-muted">Loading classes...</p>';

            if (checkedIds.length === 0) {
                formClassCheckboxes.innerHTML = '<p class="text-warning">Please select at least one section.</p>';
                return;
            }

            let allClasses = [];
            let promises = [];

            checkedIds.forEach(id => {
                if (formClassMap[id]) {
                    allClasses = allClasses.concat(formClassMap[id]);
                } else {
                    const p = fetch(`/get-classes-by-sections?section_ids[]=${id}`)
                        .then(r => r.json())
                        .then(data => {
                            formClassMap[id] = data;
                            allClasses = allClasses.concat(data);
                        });
                    promises.push(p);
                }
            });

            if (promises.length > 0) {
                Promise.all(promises).then(() => renderFormClassCheckboxes(allClasses));
            } else {
                renderFormClassCheckboxes(allClasses);
            }
        }

        function renderFormClassCheckboxes(classes) {
            formClassCheckboxes.innerHTML = '';

            if (classes.length === 0) {
                formClassCheckboxes.innerHTML = '<p class="text-warning">No classes found.</p>';
                return;
            }

            const available = classes.filter(c => !assignedFormClassIds.includes(c.id) || c.id === currentFormClassId);
            const assignedToOthers = classes.filter(c => assignedFormClassIds.includes(c.id) && c.id !== currentFormClassId);

            function createRadio(cls, isAssignedToOther = false) {
                const div = document.createElement('div');
                div.classList.add('form-check', 'mb-2');

                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'form_class_id';
                radio.value = cls.id;
                radio.id = `form_class_${cls.id}`;
                radio.classList.add('form-check-input');
                radio.required = isFormTeacherSelect.value === '1';
                if (cls.id === currentFormClassId) radio.checked = true;

                const label = document.createElement('label');
                label.classList.add('form-check-label');
                label.setAttribute('for', `form_class_${cls.id}`);
                label.textContent = cls.name || 'Unnamed Class';

                if (isAssignedToOther) {
                    const info = assignedFormTeachers[cls.id];
                    const name = info ? info.name : 'Someone';
                    const badge = document.createElement('span');
                    badge.className = 'badge badge-warning ml-2';
                    badge.textContent = `Assigned to: ${name}`;
                    label.appendChild(badge);
                }

                div.appendChild(radio);
                div.appendChild(label);
                return div;
            }

            if (available.length > 0) {
                const h = document.createElement('h6');
                h.className = 'font-weight-bold mb-2';
                h.textContent = 'Available Classes:';
                formClassCheckboxes.appendChild(h);

                available.forEach(cls => formClassCheckboxes.appendChild(createRadio(cls)));
            }

            if (assignedToOthers.length > 0) {
                const alert = document.createElement('div');
                alert.className = 'alert alert-info mb-3';
                alert.innerHTML = '<strong>Note:</strong> Selecting one will <strong>replace</strong> the current form teacher.';
                formClassCheckboxes.appendChild(alert);

                assignedToOthers.forEach(cls => formClassCheckboxes.appendChild(createRadio(cls, true)));
            }
        }

        // On page load: initialize checked sections
        document.addEventListener('DOMContentLoaded', () => {
            // Trigger visibility based on current role
            updateTeacherFieldsVisibility();

            // Load classes/courses for already checked sections
            sectionCheckboxes.forEach(cb => {
                if (cb.checked) {
                    const sid = cb.value;
                    fetch(`/get-classes-by-sections?section_ids[]=${sid}`)
                        .then(r => r.json())
                        .then(d => { classMap[sid] = d; addClassCheckboxes(sid, d); });

                    fetch(`/get-subjects-by-section/${sid}`)
                        .then(r => r.json())
                        .then(d => { courseMap[sid] = d; addCourseCheckboxes(sid, d); });
                }
            });

            // If currently a form teacher, load form classes
            if (isFormTeacherSelect.value === '1') {
                loadFormClassesFromSelectedSections();
            }
        });
    </script>
</body>