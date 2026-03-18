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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4><i class="fas fa-edit mr-2"></i>Edit Result Sheet — <em>{{ $template->name }}</em>
                                </h4>
                                <a href="{{ route('result_sheets.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>

                            <div class="card-body">
                                @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                                    </ul>
                                </div>
                                @endif

                                <form method="POST" action="{{ route('result_sheets.update', $template->id) }}"
                                    id="mainForm">
                                    @csrf @method('PUT')

                                    {{-- ══ STEP 1 ══ --}}
                                    <div class="card mb-4 border-left border-primary"
                                        style="border-left-width:4px!important">
                                        <div class="card-header bg-primary text-white py-2">
                                            <i class="fas fa-info-circle mr-1"></i> Step 1 — Basic Information
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">Template Name <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" name="name"
                                                            class="form-control @error('name') is-invalid @enderror"
                                                            value="{{ old('name', $template->name) }}">
                                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">Description</label>
                                                        <input type="text" name="description" class="form-control"
                                                            value="{{ old('description', $template->description) }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold">Rating Columns <span
                                                        class="text-danger">*</span></label>
                                                <small class="text-muted d-block mb-2">
                                                    These become the column headers on the printed sheet.
                                                </small>
                                                <div id="ratingColumnsContainer" class="d-flex flex-wrap"
                                                    style="gap:8px">
                                                    @foreach(old('rating_columns', $template->rating_columns) as $col)
                                                    <div class="input-group rating-col-row mb-1" style="width:185px">
                                                        <input type="text" name="rating_columns[]"
                                                            class="form-control form-control-sm" value="{{ $col }}"
                                                            placeholder="Column">
                                                        <div class="input-group-append">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger remove-rating-col">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" id="addRatingCol"
                                                    class="btn btn-sm btn-outline-primary mt-2">
                                                    <i class="fas fa-plus"></i> Add Column
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ══ STEP 2 ══ --}}
                                    <div class="card mb-4 border-left border-success"
                                        style="border-left-width:4px!important">
                                        <div class="card-header bg-success text-white py-2">
                                            <i class="fas fa-school mr-1"></i> Step 2 — Section, Classes &amp; Term
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">Section <span
                                                                class="text-danger">*</span></label>
                                                        <select name="section_id" id="sectionSelect"
                                                            class="form-control @error('section_id') is-invalid @enderror">
                                                            <option value="">-- Select Section --</option>
                                                            @foreach($sections as $section)
                                                            <option value="{{ $section->id }}" {{ old('section_id',
                                                                $template->section_id) == $section->id ? 'selected' : ''
                                                                }}>
                                                                {{ $section->section_name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        @error('section_id')<div class="invalid-feedback">{{ $message }}
                                                        </div>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    {{-- ── TERM: plain name dropdown, not session-specific ── --}}
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">
                                                            Term <span class="text-danger">*</span>
                                                            <small class="text-muted font-weight-normal ml-1">
                                                                (applies to all sessions)
                                                            </small>
                                                        </label>
                                                        <select name="term_name" id="termSelect"
                                                            class="form-control @error('term_name') is-invalid @enderror">
                                                            <option value="">-- Loading Terms --</option>
                                                        </select>
                                                        @error('term_name')<div class="invalid-feedback">{{ $message }}
                                                        </div>@enderror
                                                        <small class="text-muted">
                                                            <i class="fas fa-info-circle"></i>
                                                            This template will work for the selected term across
                                                            <strong>every</strong> academic session.
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">Applicable Classes <span
                                                        class="text-danger">*</span></label>
                                                <small class="text-muted d-block mb-2">
                                                    Subjects will be re-fetched when you reload below.
                                                </small>
                                                <div id="classesContainer">
                                                    <span class="text-muted small">
                                                        <i class="fas fa-spinner fa-spin"></i> Loading classes...
                                                    </span>
                                                </div>
                                                @error('applicable_classes')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <button type="button" id="loadSubjectsBtn" class="btn btn-success" disabled>
                                                <i class="fas fa-sync"></i> Reload Subjects for Selected Classes
                                            </button>
                                            <small class="text-muted ml-2">
                                                (existing structure is pre-loaded below — only reload if you changed
                                                classes)
                                            </small>
                                        </div>
                                    </div>

                                    {{-- ══ STEP 3 ══ --}}
                                    <div class="card mb-4 border-left border-warning"
                                        style="border-left-width:4px!important" id="subjectsCard">
                                        <div class="card-header bg-warning py-2">
                                            <i class="fas fa-book mr-1"></i>
                                            Step 3 — Build Sheet Structure
                                            <small class="ml-2 text-dark font-italic">
                                                Click a subject to edit its sub-topics and skill items
                                            </small>
                                        </div>
                                        <div class="card-body">
                                            <div id="availableSubjectsArea" class="mb-3">
                                                <p class="text-muted small">
                                                    <i class="fas fa-info-circle"></i>
                                                    Pre-loaded from saved template. Click a pill to edit.
                                                </p>
                                            </div>
                                            <div id="subjectBuilderArea"></div>
                                            <input type="hidden" name="subjects_json" id="subjectsJson" value="[]">
                                        </div>
                                    </div>

                                    {{-- ══ STEP 4 ══ --}}
                                    <div class="card mb-4 border-left border-info"
                                        style="border-left-width:4px!important">
                                        <div class="card-header bg-info text-white py-2">
                                            <i class="fas fa-signature mr-1"></i> Step 4 — Footer Fields
                                        </div>
                                        <div class="card-body">
                                            <div class="mt-1 d-flex flex-wrap" style="gap:16px">
                                                @php
                                                $ff = $template->footer_fields ?? [];
                                                $footerDefs = [
                                                'footer_remark' => 'Remark',
                                                'footer_class_teacher' => "Class Teacher's Signature",
                                                'footer_headmistress' => "Headmistress' Signature",
                                                'footer_reopening' => 'Re-Opening Date',
                                                ];
                                                @endphp
                                                @foreach($footerDefs as $fname => $flabel)
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="{{ $fname }}" name="{{ $fname }}" value="1" {{ ($ff[$fname]
                                                        ?? true) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="{{ $fname }}">{{ $flabel
                                                        }}</label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mt-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                        <a href="{{ route('result_sheets.index') }}"
                                            class="btn btn-secondary btn-lg ml-2">Cancel</a>
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

    <style>
        .subject-pill {
            cursor: pointer;
            border-radius: 20px;
            padding: 5px 16px;
            font-size: .85rem;
            border: 2px solid #dee2e6;
            background: #fff;
            transition: all .2s;
            user-select: none;
            display: inline-block;
        }

        .subject-pill:hover {
            border-color: #007bff;
        }

        .subject-pill.active {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .subject-pill.has-data {
            border-color: #28a745;
        }

        .subject-pill.active.has-data {
            background: #28a745;
            border-color: #28a745;
        }

        .subject-block {
            border-left: 4px solid #ffc107 !important;
        }

        .sub-cat-block {
            border-left: 3px solid #17a2b8 !important;
        }

        .item-row-ui {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 4px 8px;
            margin-bottom: 4px;
            gap: 8px;
        }

        .item-row-ui span {
            flex: 1;
            font-size: .85rem;
        }

        .btn-xs {
            padding: 2px 6px;
            font-size: .75rem;
        }
    </style>

    <script>
        // ── PRE-LOAD EXISTING DATA FROM SERVER ───────────────────────────────────
const existingData     = @json($existingSubjects);
const currentTermName  = @json(old('term_name', $template->term_name ?? ''));
const currentSectionId = {{ $template->section_id ?? 'null' }};
const currentClassIds  = @json($template->applicable_classes);

// ── DATA STORE ────────────────────────────────────────────────────────────
const store = { subjects: {}, activeSubjectId: null };

// Pre-populate store from existing data
existingData.forEach(s => {
    const key = String(s.course_id || ('existing_' + s.subject_number));
    store.subjects[key] = {
        course_id:   key,
        course_name: s.course_name,
        subtopics:   s.subtopics,
    };
});

// ── INIT ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    // Rating columns
    document.getElementById('addRatingCol').addEventListener('click', addRatingColRow);
    document.getElementById('ratingColumnsContainer').addEventListener('click', removeRatingColHandler);

    // Load all distinct term names immediately
    loadTermNames();

    // Load classes for pre-selected section
    if (currentSectionId) {
        loadClasses(currentSectionId, currentClassIds);
    }

    // Section change
    document.getElementById('sectionSelect').addEventListener('change', function () {
        loadClasses(this.value, []);
        resetSubjectArea();
    });

    // Load subjects btn
    document.getElementById('loadSubjectsBtn').addEventListener('click', loadSubjectsFromClasses);

    // Render existing subject pills and auto-open first one
    if (existingData.length) {
        renderPillsFromStore();
        setTimeout(() => {
            const firstPill = document.querySelector('.subject-pill');
            if (firstPill) firstPill.click();
        }, 100);
    }

    syncJson();

    document.getElementById('mainForm').addEventListener('submit', validateForm);
});

// ── RATING COLUMNS ────────────────────────────────────────────────────────
function addRatingColRow() {
    const row = document.createElement('div');
    row.className = 'input-group rating-col-row mb-1';
    row.style.width = '185px';
    row.innerHTML = `
        <input type="text" name="rating_columns[]" class="form-control form-control-sm" placeholder="Column">
        <div class="input-group-append">
            <button type="button" class="btn btn-sm btn-outline-danger remove-rating-col">
                <i class="fas fa-times"></i>
            </button>
        </div>`;
    document.getElementById('ratingColumnsContainer').appendChild(row);
}
function removeRatingColHandler(e) {
    if (e.target.closest('.remove-rating-col')) {
        if (document.querySelectorAll('.rating-col-row').length > 2)
            e.target.closest('.rating-col-row').remove();
        else alert('You need at least 2 rating columns.');
    }
}

// ── TERMS (distinct names only, not session-specific) ─────────────────────
function loadTermNames() {
    const sel = document.getElementById('termSelect');
    fetch('/api/terms-by-section')
        .then(r => r.json())
        .then(data => {
            const terms = data.terms || [];
            let opts = '<option value="">-- Select Term --</option>';
            terms.forEach(t => {
                const selected = (t.name === currentTermName) ? 'selected' : '';
                opts += `<option value="${t.name}" ${selected}>${t.name}</option>`;
            });
            sel.innerHTML = opts;
        })
        .catch(() => { sel.innerHTML = '<option value="">Could not load terms</option>'; });
}

// ── CLASSES ───────────────────────────────────────────────────────────────
function loadClasses(sectionId, preChecked) {
    const container = document.getElementById('classesContainer');
    if (!sectionId) {
        container.innerHTML = '<span class="text-muted small">Select a section above.</span>';
        document.getElementById('loadSubjectsBtn').disabled = true;
        return;
    }
    container.innerHTML = '<span class="text-muted small"><i class="fas fa-spinner fa-spin"></i> Loading...</span>';
    document.getElementById('loadSubjectsBtn').disabled = true;

    fetch(`/api/sections/${sectionId}/classes`)
        .then(r => r.json())
        .then(data => {
            const classes = data.classes || data;
            if (!classes.length) {
                container.innerHTML = '<span class="text-muted">No classes in this section.</span>';
                return;
            }
            let html = '<div class="row">';
            classes.forEach(cls => {
                const checked = (preChecked.includes(cls.id) || preChecked.includes(String(cls.id))) ? 'checked' : '';
                html += `
                <div class="col-md-3 col-6">
                    <div class="custom-control custom-checkbox mb-1">
                        <input type="checkbox" class="custom-control-input class-checkbox"
                            id="cls_${cls.id}" name="applicable_classes[]" value="${cls.id}" ${checked}>
                        <label class="custom-control-label" for="cls_${cls.id}">${cls.name}</label>
                    </div>
                </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
            updateLoadBtn();
            container.addEventListener('change', updateLoadBtn);
        });
}
function updateLoadBtn() {
    document.getElementById('loadSubjectsBtn').disabled =
        document.querySelectorAll('.class-checkbox:checked').length === 0;
}

// ── LOAD SUBJECTS FROM API ────────────────────────────────────────────────
function loadSubjectsFromClasses() {
    const classIds = [...document.querySelectorAll('.class-checkbox:checked')].map(c => c.value);
    if (!classIds.length) return;
    fetch(`/api/subjects-by-classes?class_ids=${classIds.join(',')}`)
        .then(r => r.json())
        .then(courses => {
            courses.forEach(c => {
                const key = String(c.id);
                if (!store.subjects[key])
                    store.subjects[key] = { course_id: key, course_name: c.course_name, subtopics: [] };
            });
            renderPillsFromStore(courses);
        });
}
function resetSubjectArea() {
    document.getElementById('availableSubjectsArea').innerHTML =
        '<p class="text-muted small">Select classes and click "Reload Subjects".</p>';
    document.getElementById('subjectBuilderArea').innerHTML = '';
}

// ── PILLS ─────────────────────────────────────────────────────────────────
function renderPillsFromStore(freshCourses) {
    const subjects = freshCourses
        ? freshCourses
        : Object.values(store.subjects).map(s => ({ id: s.course_id, course_name: s.course_name }));

    const area = document.getElementById('availableSubjectsArea');
    area.innerHTML = `
        <div class="mb-2 font-weight-bold text-muted small text-uppercase">
            Click a subject to edit its structure:
        </div>
        <div class="d-flex flex-wrap" style="gap:8px" id="pillsRow"></div>`;

    subjects.forEach(c => {
        const courseId = String(c.id || c.course_id);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'subject-pill';
        btn.dataset.courseId = courseId;
        btn.textContent = c.course_name;
        refreshPillState(btn, courseId);
        btn.addEventListener('click', () => {
            document.querySelectorAll('.subject-pill').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            const strId = String(courseId);
            if (!store.subjects[strId])
                store.subjects[strId] = { course_id: strId, course_name: c.course_name, subtopics: [] };
            store.activeSubjectId = strId;
            renderBuilder(strId, c.course_name);
        });
        document.getElementById('pillsRow').appendChild(btn);
    });
}
function refreshPillState(btn, courseId) {
    const subj = store.subjects[courseId];
    btn.classList.toggle('has-data', !!(subj && subj.subtopics.some(st => st.name || st.items.length)));
}

// ── BUILDER ───────────────────────────────────────────────────────────────
function renderBuilder(courseId, courseName) {
    courseId = String(courseId);
    const area = document.getElementById('subjectBuilderArea');
    area.innerHTML = `
        <div class="card subject-block mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">
                    <i class="fas fa-book-open mr-1 text-warning"></i>
                    <em>${escHtml(courseName)}</em>
                </span>
                <small class="text-muted">Add or edit sub-topics and skill items</small>
            </div>
            <div class="card-body">
                <div id="subtopicsArea_${courseId}"></div>
                <button type="button" class="btn btn-sm btn-outline-info mt-2"
                    onclick="addSubtopic('${courseId}')">
                    <i class="fas fa-plus"></i> Add Sub-topic
                    <span class="text-muted">(e.g. (a) Oral English)</span>
                </button>
            </div>
        </div>`;
    store.subjects[courseId].subtopics.forEach((_, idx) => renderSubtopicDOM(courseId, idx));
}

// ── SUBTOPIC ──────────────────────────────────────────────────────────────
function nextLabel(courseId) {
    courseId = String(courseId);
    const subtopics = store.subjects[courseId].subtopics;
    if (!subtopics.length) return '(a)';
    const last = [...subtopics].reverse().find(st => /^\([a-z]\)$/i.test(st.label?.trim()));
    if (!last) return `(${String.fromCharCode(97 + subtopics.length)})`;
    const char = last.label.trim().replace(/[()]/g, '');
    return `(${String.fromCharCode(char.charCodeAt(0) + 1)})`;
}
function addSubtopic(courseId) {
    courseId = String(courseId);
    store.subjects[courseId].subtopics.push({ label: nextLabel(courseId), name: '', items: [] });
    const idx = store.subjects[courseId].subtopics.length - 1;
    renderSubtopicDOM(courseId, idx);
    afterChange(courseId);
}
function renderSubtopicDOM(courseId, stIdx) {
    courseId = String(courseId);
    const container = document.getElementById(`subtopicsArea_${courseId}`);
    const st  = store.subjects[courseId].subtopics[stIdx];
    const old = document.getElementById(`st_${courseId}_${stIdx}`);
    if (old) old.remove();
    const div = document.createElement('div');
    div.className = 'card sub-cat-block mb-2';
    div.id = `st_${courseId}_${stIdx}`;
    div.innerHTML = `
        <div class="card-header bg-white py-1 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center flex-grow-1 mr-2" style="gap:6px">
                <input type="text"
                    class="form-control form-control-sm st-label-input" style="width:75px"
                    placeholder="(a)" value="${escHtml(st.label)}"
                    data-course="${courseId}" data-idx="${stIdx}" data-field="label"
                    oninput="fieldChange(this.dataset.course, this.dataset.idx, this.dataset.field, this.value)">
                <input type="text"
                    class="form-control form-control-sm st-name-input"
                    placeholder="Sub-topic name e.g. Oral English" value="${escHtml(st.name)}"
                    data-course="${courseId}" data-idx="${stIdx}" data-field="name"
                    oninput="fieldChange(this.dataset.course, this.dataset.idx, this.dataset.field, this.value)">
            </div>
            <button type="button" class="btn btn-xs btn-outline-danger"
                onclick="removeSubtopic('${courseId}', ${stIdx})">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <div class="card-body py-2">
            <div id="itemsArea_${courseId}_${stIdx}">
                ${st.items.map((item, i) => itemHtml(courseId, stIdx, i, item)).join('')}
            </div>
            <div class="input-group mt-2" style="max-width:640px">
                <input type="text" class="form-control form-control-sm"
                    placeholder="Type skill/competency text then press Enter or click Add"
                    id="newItem_${courseId}_${stIdx}"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();addItem('${courseId}',${stIdx});}">
                <div class="input-group-append">
                    <button type="button" class="btn btn-sm btn-success"
                        onclick="addItem('${courseId}',${stIdx})">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
        </div>`;
    container.appendChild(div);
}
function itemHtml(courseId, stIdx, iIdx, text) {
    return `
        <div class="item-row-ui" id="item_${courseId}_${stIdx}_${iIdx}">
            <i class="fas fa-minus text-muted" style="font-size:.7rem"></i>
            <span>${escHtml(text)}</span>
            <button type="button" class="btn btn-xs btn-outline-danger"
                onclick="removeItem('${courseId}',${stIdx},${iIdx})">
                <i class="fas fa-times"></i>
            </button>
        </div>`;
}

// ── MUTATIONS ─────────────────────────────────────────────────────────────
function fieldChange(courseId, stIdx, field, val) {
    courseId = String(courseId);
    stIdx    = parseInt(stIdx, 10);
    store.subjects[courseId].subtopics[stIdx][field] = val;
    afterChange(courseId);
}
function removeSubtopic(courseId, stIdx) {
    courseId = String(courseId);
    if (!confirm('Remove this sub-topic and all its items?')) return;
    store.subjects[courseId].subtopics.splice(stIdx, 1);
    renderBuilder(courseId, store.subjects[courseId].course_name);
    afterChange(courseId);
}
function addItem(courseId, stIdx) {
    courseId = String(courseId);
    const input = document.getElementById(`newItem_${courseId}_${stIdx}`);
    const text  = input.value.trim();
    if (!text) { input.focus(); return; }
    store.subjects[courseId].subtopics[stIdx].items.push(text);
    const iIdx = store.subjects[courseId].subtopics[stIdx].items.length - 1;
    document.getElementById(`itemsArea_${courseId}_${stIdx}`)
        .insertAdjacentHTML('beforeend', itemHtml(courseId, stIdx, iIdx, text));
    input.value = ''; input.focus();
    afterChange(courseId);
}
function removeItem(courseId, stIdx, iIdx) {
    courseId = String(courseId);
    store.subjects[courseId].subtopics[stIdx].items.splice(iIdx, 1);
    const area = document.getElementById(`itemsArea_${courseId}_${stIdx}`);
    area.innerHTML = store.subjects[courseId].subtopics[stIdx].items
        .map((t, i) => itemHtml(courseId, stIdx, i, t)).join('');
    afterChange(courseId);
}

// ── HELPERS ───────────────────────────────────────────────────────────────
function afterChange(courseId) {
    courseId = String(courseId);
    const pill = document.querySelector(`.subject-pill[data-course-id="${courseId}"]`);
    if (pill) refreshPillState(pill, courseId);
    syncJson();
}
function syncJson() {
    const payload = Object.values(store.subjects)
        .filter(s => s.subtopics && s.subtopics.length > 0)
        .map((s, i) => ({
            course_id:      s.course_id,
            course_name:    s.course_name,
            subject_number: i + 1,
            subtopics:      s.subtopics,
        }));
    document.getElementById('subjectsJson').value = JSON.stringify(payload);
}
function escHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function scrapeBuilderIntoStore() {
    document.querySelectorAll('.st-label-input, .st-name-input').forEach(input => {
        const courseId = String(input.dataset.course);
        const stIdx    = parseInt(input.dataset.idx, 10);
        const field    = input.dataset.field;
        if (store.subjects[courseId] && store.subjects[courseId].subtopics[stIdx] !== undefined) {
            store.subjects[courseId].subtopics[stIdx][field] = input.value;
        }
    });
}
function validateForm(e) {
    scrapeBuilderIntoStore();
    syncJson();

    if (!document.getElementById('termSelect').value) {
        e.preventDefault(); return alert('Please select a term.');
    }
    if (!document.querySelectorAll('.class-checkbox:checked').length) {
        e.preventDefault(); return alert('Please select at least one class.');
    }
    let payload = [];
    try { payload = JSON.parse(document.getElementById('subjectsJson').value || '[]'); } catch (_) {}
    if (!payload.length) {
        e.preventDefault();
        return alert('No subject data found. Please click a subject pill to confirm your structure is loaded.');
    }
}
    </script>
</body>