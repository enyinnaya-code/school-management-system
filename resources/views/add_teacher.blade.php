@include('includes.head')

{{-- user type
type = 3 = Teacher
type = 6 = Bursar
type = 7 = Principal
type = 8 = Vice-Principal
type = 9 = Dean of Studies
type = 10 = Guidance Counsellor
--}}
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
</style>

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
                            <form action="{{ route('teacher.store') }}" method="POST" class="needs-validation"
                                novalidate>
                                @csrf

                                <div class="card-header">
                                    <h4>Add Staff</h4>
                                </div>

                                <div class="card-body">
                                    <div class="form-group col-md-6 px-0">
                                        <label>Full Name</label>
                                        <input type="text" name="name" class="form-control" required
                                            placeholder="e.g. JOHN DOE" oninput="this.value = this.value.toUpperCase()">
                                        <div class="valid-feedback">Looks good!</div>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Email Address</label>
                                        <input type="email" name="email" class="form-control" required
                                            placeholder="e.g. user@example.com">
                                        <div class="invalid-feedback">Please enter a valid email.</div>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>User Type</label>
                                        <select class="form-control" name="user_type" id="userType" required>
                                            <option value="">Select User Type</option>
                                            <option value="3">Teacher</option>
                                            <option value="7">Principal</option>
                                            <option value="8">Vice-Principal</option>
                                            <option value="6">Bursar</option>
                                            <option value="9">Dean of Studies</option>
                                            <option value="10">Guidance Counsellor</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a user type.</div>
                                        <small class="text-muted">
                                            Note: Principal, Vice-Principal, and Dean of Studies can also teach classes
                                            if needed.
                                        </small>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Password <small>Default: 123456</small></label>
                                        <input type="password" name="password" class="form-control" required
                                            placeholder="Enter password" value="123456" readonly>
                                    </div>

                                    <!-- Sections -->
                                    <div class="form-group col-md-12 px-0" id="sectionSection" style="display: none;">
                                        <label>Select Sections (Arms)</label><br>
                                        @foreach ($sections as $section)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input section-checkbox" type="checkbox"
                                                name="section_ids[]" value="{{ $section->id }}"
                                                id="section_{{ $section->id }}">
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
                                    <div class="form-group col-md-12 px-0" id="subjectSection" style="display: none;">
                                        <label>Associated Courses</label>
                                        <div id="subjectCheckboxes"></div>
                                    </div>

                                    <!-- Form Teacher Option -->
                                    <div class="form-group col-md-6 px-0" id="formTeacherSection"
                                        style="display: none;">
                                        <label>Form Teacher Assignment</label>
                                        <select class="form-control" name="is_form_teacher" id="isFormTeacherSelect">
                                            <option value="0" selected>Not a Form Teacher</option>
                                            <option value="1">Yes, Assign as Form Teacher</option>
                                        </select>
                                    </div>

                                    <!-- Form Class Selection -->
                                    <div class="form-group col-md-12 px-0" id="formClassSection"
                                        style="display: none; margin-top: 15px;">
                                        <label>Select Form Class</label>
                                        <div id="formClassCheckboxes"></div>
                                        <small class="text-muted">
                                            The staff will be the form teacher for the selected class.
                                        </small>
                                    </div>
                                </div>

                                <div class="card-footer text-left pt-5 mt-3">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.footer')

        <script>
            const sectionCheckboxes = document.querySelectorAll('.section-checkbox');
            const classContainer = document.getElementById('classCheckboxes');
            const subjectContainer = document.getElementById('subjectCheckboxes');
            const userTypeSelect = document.getElementById('userType');

            const sectionSection = document.getElementById('sectionSection');
            const classSection = document.getElementById('classSection');
            const subjectSection = document.getElementById('subjectSection');
            const formTeacherSection = document.getElementById('formTeacherSection');
            const formClassSection = document.getElementById('formClassSection');
            const isFormTeacherSelect = document.getElementById('isFormTeacherSelect');
            const formClassCheckboxes = document.getElementById('formClassCheckboxes');

            let classMap = {};
            let subjectMap = {};
            let formClassMap = {};
            let assignedFormClassIds = [];
            let assignedFormTeachers = {};

            // Load assigned form teachers
            fetch('{{ route('get.assigned.form.classes.with.teachers') }}')
                .then(res => res.json())
                .then(data => {
                    assignedFormTeachers = data;
                    assignedFormClassIds = Object.keys(data).map(id => parseInt(id));
                });

            function updateTeacherFieldsVisibility() {
                const userType = userTypeSelect.value;
                const teachingRoles = ['3', '7', '8', '9', '10']; // Teacher, Principal, VP, Dean

                if (teachingRoles.includes(userType)) {
                    sectionSection.style.display = 'block';
                    classSection.style.display = 'block';
                    subjectSection.style.display = 'block';
                    formTeacherSection.style.display = 'block';
                } else {
                    // Hide and clear all teaching fields
                    sectionSection.style.display = 'none';
                    classSection.style.display = 'none';
                    subjectSection.style.display = 'none';
                    formTeacherSection.style.display = 'none';
                    formClassSection.style.display = 'none';

                    sectionCheckboxes.forEach(cb => cb.checked = false);
                    classContainer.innerHTML = '';
                    subjectContainer.innerHTML = '';
                    formClassCheckboxes.innerHTML = '';
                    isFormTeacherSelect.value = '0';
                    classMap = {};
                    subjectMap = {};
                    formClassMap = {};
                }
            }

            updateTeacherFieldsVisibility();
            userTypeSelect.addEventListener('change', updateTeacherFieldsVisibility);

            isFormTeacherSelect.addEventListener('change', function () {
                if (this.value === '1') {
                    formClassSection.style.display = 'block';
                    loadFormClassesFromSelectedSections();
                } else {
                    formClassSection.style.display = 'none';
                    formClassCheckboxes.innerHTML = '';
                }
            });

// Updated addClassCheckboxes with "Select All"
function addClassCheckboxes(sectionId, classes) {
    if (classContainer.querySelector(`[data-section-id="${sectionId}"]`)) return;

    const wrapper = document.createElement('div');
    wrapper.classList.add('class-group', 'border', 'p-3', 'mb-3', 'rounded');
    wrapper.dataset.sectionId = sectionId;

    const section = document.querySelector(`#section_${sectionId}`).parentElement.textContent.trim();
    const title = document.createElement('h6');
    title.textContent = `Classes in: ${section}`;
    title.classList.add('mb-3', 'font-weight-bold');
    wrapper.appendChild(title);

    const selectAllDiv = document.createElement('div');
    selectAllDiv.classList.add('form-check', 'mb-2');

    const selectAllCheckbox = document.createElement('input');
    selectAllCheckbox.type = 'checkbox';
    selectAllCheckbox.classList.add('form-check-input', 'select-all-classes');
    selectAllCheckbox.id = `select_all_${sectionId}`;

    const selectAllLabel = document.createElement('label');
    selectAllLabel.classList.add('form-check-label', 'font-weight-bold');
    selectAllLabel.setAttribute('for', `select_all_${sectionId}`);
    selectAllLabel.textContent = 'Select All Classes';

    selectAllDiv.appendChild(selectAllCheckbox);
    selectAllDiv.appendChild(selectAllLabel);
    wrapper.appendChild(selectAllDiv);

    const classesWrapper = document.createElement('div');
    classesWrapper.classList.add('row');

    classes.forEach(cls => {
        const col = document.createElement('div');
        col.classList.add('col-md-4');

        const div = document.createElement('div');
        div.classList.add('form-check');

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.classList.add('form-check-input', 'section-class');
        checkbox.name = 'class_ids[]';
        checkbox.value = cls.id;
        checkbox.id = `class_${cls.id}`;
        checkbox.dataset.sectionId = sectionId;

        const label = document.createElement('label');
        label.classList.add('form-check-label');
        label.setAttribute('for', `class_${cls.id}`);
        label.innerText = cls.name;

        div.appendChild(checkbox);
        div.appendChild(label);
        col.appendChild(div);
        classesWrapper.appendChild(col);
    });

    wrapper.appendChild(classesWrapper);
    classContainer.appendChild(wrapper);

    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = wrapper.querySelectorAll('.section-class');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    wrapper.querySelectorAll('.section-class').forEach(cb => {
        cb.addEventListener('change', function() {
            const allChecked = Array.from(wrapper.querySelectorAll('.section-class')).every(c => c.checked);
            const someChecked = Array.from(wrapper.querySelectorAll('.section-class')).some(c => c.checked);

            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        });
    });
}

function removeClassCheckboxes(sectionId) {
    const group = classContainer.querySelector(`[data-section-id="${sectionId}"]`);
    if (group) group.remove();
}

function addSubjectCheckboxes(sectionId, subjects) {
    if (subjectContainer.querySelector(`[data-section-id="${sectionId}"]`)) return;

    const wrapper = document.createElement('div');
    wrapper.classList.add('subject-group', 'border', 'p-3', 'mb-3', 'rounded');
    wrapper.dataset.sectionId = sectionId;

    const section = document.querySelector(`#section_${sectionId}`).parentElement.textContent.trim();
    const title = document.createElement('h6');
    title.textContent = `Courses in: ${section}`;
    title.classList.add('mb-3', 'font-weight-bold');
    wrapper.appendChild(title);

    const row = document.createElement('div');
    row.classList.add('row');

    subjects.forEach(subject => {
        const col = document.createElement('div');
        col.classList.add('col-md-4');

        const div = document.createElement('div');
        div.classList.add('form-check');

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.classList.add('form-check-input');
        checkbox.name = 'course_ids[]';
        checkbox.value = subject.id;
        checkbox.id = `subject_${subject.id}`;

        const label = document.createElement('label');
        label.classList.add('form-check-label');
        label.setAttribute('for', `subject_${subject.id}`);
        label.innerText = subject.course_name;

        div.appendChild(checkbox);
        div.appendChild(label);
        col.appendChild(div);
        row.appendChild(col);
    });

    wrapper.appendChild(row);
    subjectContainer.appendChild(wrapper);
}

function removeSubjectCheckboxes(sectionId) {
    const group = subjectContainer.querySelector(`[data-section-id="${sectionId}"]`);
    if (group) group.remove();
}

sectionCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const sectionId = this.value;

        if (this.checked) {
            if (!classMap[sectionId]) {
                fetch(`/get-classes-by-sections?section_ids[]=${sectionId}`)
                    .then(res => res.json())
                    .then(data => {
                        classMap[sectionId] = data;
                        addClassCheckboxes(sectionId, data);
                    });
            } else {
                addClassCheckboxes(sectionId, classMap[sectionId]);
            }

            if (!subjectMap[sectionId]) {
                fetch(`/get-subjects-by-section/${sectionId}`)
                    .then(res => res.json())
                    .then(data => {
                        subjectMap[sectionId] = data;
                        addSubjectCheckboxes(sectionId, data);
                    });
            } else {
                addSubjectCheckboxes(sectionId, subjectMap[sectionId]);
            }

            if (isFormTeacherSelect.value === '1') {
                loadFormClassesFromSelectedSections();
            }
        } else {
            removeClassCheckboxes(sectionId);
            removeSubjectCheckboxes(sectionId);
            if (isFormTeacherSelect.value === '1') {
                loadFormClassesFromSelectedSections();
            }
        }
    });
});

function loadFormClassesFromSelectedSections() {
    const checkedSectionIds = Array.from(sectionCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    console.log('Loading form classes for sections:', checkedSectionIds);

    formClassCheckboxes.innerHTML = '';

    if (checkedSectionIds.length === 0) {
        formClassCheckboxes.innerHTML = '<p class="text-warning">Please select at least one section first.</p>';
        return;
    }

    let allClasses = [];
    let fetchPromises = [];

    checkedSectionIds.forEach(sectionId => {
        if (formClassMap[sectionId]) {
            allClasses = allClasses.concat(formClassMap[sectionId]);
        } else {
            const promise = fetch(`/get-classes-by-sections?section_ids[]=${sectionId}`)
                .then(res => res.json())
                .then(data => {
                    formClassMap[sectionId] = data;
                    allClasses = allClasses.concat(data);
                });
            fetchPromises.push(promise);
        }
    });

    if (fetchPromises.length > 0) {
        Promise.all(fetchPromises).then(() => renderFormClassCheckboxes(allClasses));
    } else {
        renderFormClassCheckboxes(allClasses);
    }
}

function renderFormClassCheckboxes(classes) {
    console.log('Rendering form classes:', classes);
    console.log('Assigned IDs:', assignedFormClassIds);
    console.log('Assigned Teachers:', assignedFormTeachers);
    
    formClassCheckboxes.innerHTML = '';

    if (classes.length === 0) {
        formClassCheckboxes.innerHTML = '<p class="text-warning">No classes found in selected sections.</p>';
        return;
    }

    const availableClasses = classes.filter(cls => !assignedFormClassIds.includes(cls.id));
    const assignedClasses = classes.filter(cls => assignedFormClassIds.includes(cls.id));

    console.log('Available classes:', availableClasses);
    console.log('Assigned classes:', assignedClasses);

    function createRadio(cls, isAssigned = false) {
        const div = document.createElement('div');
        div.classList.add('form-check', 'mb-2');
        div.style.display = 'block';
        div.style.padding = '5px 0';

        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.classList.add('form-check-input');
        radio.name = 'form_class_id';
        radio.value = cls.id;
        radio.id = `form_class_${cls.id}`;
        radio.required = (isFormTeacherSelect.value === '1');
        
        if (isAssigned) {
            radio.disabled = false;
        }

        const label = document.createElement('label');
        label.classList.add('form-check-label');
        label.setAttribute('for', `form_class_${cls.id}`);
        label.style.marginLeft = '8px';
        label.style.cursor = 'pointer';
        
        const textNode = document.createTextNode(cls.name || 'Unnamed Class');
        label.appendChild(textNode);

        if (isAssigned) {
            // Get teacher name from assignedFormTeachers
            const teacherInfo = assignedFormTeachers[cls.id];
            const teacherName = teacherInfo ? teacherInfo.name : 'Unknown Teacher';
            
            const badge = document.createElement('span');
            badge.className = 'badge badge-warning ml-2';
            badge.style.marginLeft = '10px';
            badge.textContent = `Assigned to: ${teacherName}`;
            label.appendChild(badge);
            
            div.title = `Currently assigned to ${teacherName}. Selecting this will replace them as form teacher.`;
        }

        div.appendChild(radio);
        div.appendChild(label);
        
        return div;
    }

    if (availableClasses.length > 0) {
        const availableSection = document.createElement('div');
        availableSection.className = 'mb-3';
        availableSection.style.display = 'block';
        
        const heading = document.createElement('h6');
        heading.className = 'font-weight-bold mb-2';
        heading.style.display = 'block';
        heading.textContent = 'Available Classes:';
        availableSection.appendChild(heading);
        
        availableClasses.forEach(cls => {
            const radioDiv = createRadio(cls, false);
            availableSection.appendChild(radioDiv);
        });
        
        formClassCheckboxes.appendChild(availableSection);
    }

    if (assignedClasses.length > 0) {
        const warning = document.createElement('div');
        warning.className = 'alert alert-info mt-3 mb-2';
        warning.style.display = 'block';
        warning.innerHTML = '<strong>Note:</strong> The following classes already have a form teacher. Selecting one will <strong>replace</strong> the current assignment.';
        formClassCheckboxes.appendChild(warning);

        const assignedSection = document.createElement('div');
        assignedSection.className = 'mb-3';
        assignedSection.style.display = 'block';
        
        assignedClasses.forEach(cls => {
            const radioDiv = createRadio(cls, true);
            assignedSection.appendChild(radioDiv);
        });
        
        formClassCheckboxes.appendChild(assignedSection);
    }
    
    // console.log('Rendering complete. FormClassSection display:', formClassSection.style.display);
    // console.log('FormClassCheckboxes innerHTML length:', formClassCheckboxes.innerHTML.length);
}
        </script>
    </div>
</body>