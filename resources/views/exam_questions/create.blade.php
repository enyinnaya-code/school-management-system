{{-- ============================================ --}}
{{-- FILE: resources/views/exam_questions/create.blade.php --}}
{{-- ============================================ --}}

@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <div class="main-content pt-5 mt-5">
                <section class="section">
                    <div class="section-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Create Exam Question Paper</h4>
                                        <div class="d-flex align-items-center">
                                            {{-- Autosave indicator --}}
                                            <span id="autosave-indicator" class="mr-3 small text-muted" style="display:none !important;">
                                                <i class="fas fa-circle-notch fa-spin mr-1" id="autosave-spinner"></i>
                                                <i class="fas fa-check-circle text-success mr-1" id="autosave-ok" style="display:none;"></i>
                                                <span id="autosave-text">Saving draft...</span>
                                            </span>
                                            <a href="{{ route('exam_questions.index') }}" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-arrow-left"></i> Back to List
                                            </a>
                                        </div>
                                    </div>

                                    <div class="card-body">

                                        {{-- Autosave restore banner --}}
                                        <div id="restore-banner" class="alert alert-warning d-none">
                                            <i class="fas fa-history mr-1"></i>
                                            We found an unsaved draft from your last session.
                                            <button type="button" class="btn btn-sm btn-warning ml-2" onclick="restoreDraft()">Restore it</button>
                                            <button type="button" class="btn btn-sm btn-secondary ml-1" onclick="discardDraft()">Discard</button>
                                        </div>

                                        <form action="{{ route('exam_questions.store') }}" method="POST" id="examForm">
                                            @csrf

                                            {{-- ── Basic Information ── --}}
                                            <div class="card mb-4">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h6>
                                                </div>
                                                <div class="card-body">

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Section/Arm <span class="text-danger">*</span></label>
                                                                <select name="section_id" id="section_id" class="form-control" required>
                                                                    <option value="">Select Section</option>
                                                                    @foreach($sections as $section)
                                                                        <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Session <span class="text-danger">*</span></label>
                                                                <select name="session_id" id="session_id" class="form-control" required>
                                                                    <option value="">Select Session</option>
                                                                    @foreach($sessions as $session)
                                                                        <option value="{{ $session->id }}"
                                                                            {{ isset($currentSession) && $currentSession->id == $session->id ? 'selected' : '' }}>
                                                                            {{ $session->name }}
                                                                            @if($session->is_current) (Current) @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Term <span class="text-danger">*</span></label>
                                                                <select name="term_id" id="term_id" class="form-control" required>
                                                                    <option value="">Select Term</option>
                                                                    @foreach($terms as $term)
                                                                        <option value="{{ $term->id }}"
                                                                            {{ $term->is_current ? 'selected' : '' }}>
                                                                            {{ $term->name }}
                                                                            @if($term->is_current) (Current) @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Class <span class="text-danger">*</span></label>
                                                                <select name="class_id" id="class_id" class="form-control" required>
                                                                    <option value="">Select Class</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Subject <span class="text-danger">*</span></label>
                                                                <select name="subject_id" id="subject_id" class="form-control" required>
                                                                    <option value="">Select Subject</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Exam Title <span class="text-danger">*</span></label>
                                                                <input type="text" name="exam_title" id="exam_title"
                                                                    class="form-control" placeholder="e.g., First Term Mathematics Exam" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Exam Type <span class="text-danger">*</span></label>
                                                                <select name="exam_type" class="form-control" required>
                                                                    @foreach($examTypes as $type)
                                                                        <option value="{{ $type }}">{{ $type }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Exam Date</label>
                                                                <input type="date" name="exam_date" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Duration (minutes)</label>
                                                                <input type="number" name="duration_minutes" class="form-control" placeholder="e.g., 90" min="1">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Total Marks <span class="text-danger">*</span></label>
                                                                <input type="number" name="total_marks" id="total_marks"
                                                                    class="form-control" placeholder="e.g., 100" min="1" required>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>General Instructions</label>
                                                                <textarea name="instructions" class="form-control" rows="3"
                                                                    placeholder="e.g., Answer all questions. Write legibly."></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>School Name (Optional)</label>
                                                                <input type="text" name="school_name" class="form-control"
                                                                    placeholder="e.g., St. Mary's High School">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>School Address (Optional)</label>
                                                                <input type="text" name="school_address" class="form-control"
                                                                    placeholder="e.g., 123 Education St.">
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            {{-- ── Exam Sections & Questions ── --}}
                                            <div class="card mb-4">
                                                <div class="card-header bg-success text-white">
                                                    <h6 class="mb-0"><i class="fas fa-list"></i> Exam Sections & Questions</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="exam-sections-container"></div>
                                                    <button type="button" class="btn btn-success" onclick="addSection()">
                                                        <i class="fas fa-plus"></i> Add Section
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- ── Additional Options ── --}}
                                            <div class="card mb-4">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0"><i class="fas fa-cog"></i> Additional Options</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="show_marking_scheme" name="show_marking_scheme">
                                                            <label class="custom-control-label" for="show_marking_scheme">
                                                                Show Marking Scheme on Print
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Marking Scheme / Answer Key (Optional)</label>
                                                        <textarea name="marking_scheme" class="form-control" rows="4"
                                                            placeholder="Enter the marking scheme or answer key here..."></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Private Notes (Optional)</label>
                                                        <textarea name="notes" class="form-control" rows="3"
                                                            placeholder="Personal notes that won't appear on the printed exam..."></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ── Submit Buttons ── --}}
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button type="submit" name="status" value="draft" class="btn btn-warning">
                                                        <i class="fas fa-save"></i> Save as Draft
                                                    </button>
                                                    <button type="submit" name="status" value="published" class="btn btn-success">
                                                        <i class="fas fa-check"></i> Save & Publish
                                                    </button>
                                                    <a href="{{ route('exam_questions.index') }}" class="btn btn-secondary">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </a>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
    // ── Config ──────────────────────────────────────────────────────────────
    const USER_TYPE      = {{ Auth::user()->user_type }};
    const ADMIN_ROLES    = [1, 2, 7, 8, 9, 10]; // fetch ALL subjects for section
    const AUTOSAVE_KEY   = 'exam_draft_{{ Auth::id() }}';
    const AUTOSAVE_DELAY = 30000; // 30 seconds

    let sectionCounter   = 0;
    let questionCounters = {};
    let autosaveTimer    = null;
    let autosaveDirty    = false;

    // ── Session → Terms (school-wide, no section filter needed) ─────────────
    $('#session_id').on('change', function () {
        loadTerms($(this).val());
    });

    function loadTerms(sessionId) {
        const $term = $('#term_id');
        $term.html('<option value="">Loading...</option>');

        if (!sessionId) { $term.html('<option value="">Select Term</option>'); return; }

        $.get('/ajax/terms/' + sessionId)
            .done(function (data) {
                $term.html('<option value="">Select Term</option>');
                data.terms.forEach(function (t) {
                    const sel = t.id == data.current_term_id ? 'selected' : '';
                    $term.append(`<option value="${t.id}" ${sel}>${t.name}${t.is_current ? ' (Current)' : ''}</option>`);
                });
            })
            .fail(function () { $term.html('<option value="">Failed to load</option>'); });
    }

    // ── Section → Classes ────────────────────────────────────────────────────
    $('#section_id').on('change', function () {
        const sectionId = $(this).val();

        $('#class_id, #subject_id')
            .html('<option value="">Select...</option>');

        if (!sectionId) return;

        $.get('/ajax/classes/' + sectionId)
            .done(function (data) {
                $('#class_id').html('<option value="">Select Class</option>');
                data.classes.forEach(function (c) {
                    $('#class_id').append(`<option value="${c.id}">${c.name}</option>`);
                });
            })
            .fail(function () { alert('Failed to load classes.'); });
    });

    // ── Class → Subjects ─────────────────────────────────────────────────────
    // Admin/Principal/VP/Dean: fetch ALL subjects for that class in the section.
    // Teacher: fetch only assigned subjects.
    $('#class_id').on('change', function () {
        const sectionId = $('#section_id').val();
        const classId   = $(this).val();
        const $subj     = $('#subject_id');

        $subj.html('<option value="">Loading...</option>');

        if (!sectionId || !classId) {
            $subj.html('<option value="">Select Subject</option>');
            return;
        }

        // All admin-like roles get the full subject list via the same AJAX endpoint,
        // but the controller already handles role-based filtering server-side.
        // For admin roles we pass a flag so the controller returns all subjects.
        const url = `/ajax/subjects/${sectionId}/${classId}`;

        $.get(url)
            .done(function (data) {
                $subj.html('<option value="">Select Subject</option>');
                if (!data.subjects || data.subjects.length === 0) {
                    $subj.append('<option value="" disabled>No subjects found</option>');
                    return;
                }
                data.subjects.forEach(function (s) {
                    $subj.append(`<option value="${s.id}">${s.course_name}</option>`);
                });
            })
            .fail(function () { alert('Failed to load subjects.'); });
    });

    // ── Exam Sections & Questions ─────────────────────────────────────────────
    function addSection() {
        sectionCounter++;
        const html = `
            <div class="card mb-3 exam-section" data-section-id="${sectionCounter}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 w-100">
                        <i class="fas fa-folder mr-1"></i>
                        <input type="text" name="sections[${sectionCounter}][title]"
                            class="form-control d-inline-block ml-2 autosave-trigger" style="width:65%;"
                            placeholder="e.g., Section A - Objective" required>
                    </h6>
                    <button type="button" class="btn btn-sm btn-danger ml-2" onclick="removeSection(${sectionCounter})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Section Instructions (Optional)</label>
                        <textarea name="sections[${sectionCounter}][instructions]"
                            class="form-control autosave-trigger" rows="2"></textarea>
                    </div>
                    <div class="questions-container" id="questions-${sectionCounter}"></div>
                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addQuestion(${sectionCounter})">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                </div>
            </div>`;

        $('#exam-sections-container').append(html);
        triggerAutosave();
    }

    function removeSection(id) {
        if (confirm('Remove this section and all its questions?')) {
            $(`.exam-section[data-section-id="${id}"]`).remove();
            triggerAutosave();
        }
    }

    function addQuestion(sectionId) {
        if (!questionCounters[sectionId]) questionCounters[sectionId] = 0;
        questionCounters[sectionId]++;
        const qId = questionCounters[sectionId];

        const html = `
            <div class="card mb-3 question-item" data-section="${sectionId}" data-question-id="${qId}">
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-md-11">
                            <div class="form-group">
                                <label>Question ${qId}</label>
                                <textarea name="sections[${sectionId}][questions][${qId}][text]"
                                    class="form-control autosave-trigger" rows="3" required
                                    placeholder="Enter question text..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Type</label>
                                    <select name="sections[${sectionId}][questions][${qId}][type]"
                                        class="form-control question-type autosave-trigger"
                                        data-section="${sectionId}" data-qid="${qId}">
                                        <option value="multiple_choice" selected>Multiple Choice</option>
                                        <option value="essay">Essay</option>
                                        <option value="short">Short Answer</option>
                                        <option value="true_false">True/False</option>
                                        <option value="fill_blank">Fill in the Blank</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>Marks</label>
                                    <input type="number" name="sections[${sectionId}][questions][${qId}][marks]"
                                        class="form-control autosave-trigger" min="1" required placeholder="e.g., 5">
                                </div>
                            </div>

                            <div class="options-container mt-3" id="options-${sectionId}-${qId}">
                                <label>Options (A–D)</label>
                                ${['A','B','C','D'].map(l => `
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend"><span class="input-group-text">${l}</span></div>
                                        <input type="text" name="sections[${sectionId}][questions][${qId}][options][]"
                                            class="form-control autosave-trigger" placeholder="Option ${l}">
                                    </div>`).join('')}
                            </div>

                            <div class="form-group mt-3">
                                <label>Model Answer / Notes (Optional)</label>
                                <textarea name="sections[${sectionId}][questions][${qId}][answer]"
                                    class="form-control autosave-trigger" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-1 text-right">
                            <button type="button" class="btn btn-danger btn-sm"
                                onclick="removeQuestion(${sectionId}, ${qId})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

        $(`#questions-${sectionId}`).append(html);
        triggerAutosave();
    }

    function removeQuestion(sectionId, qId) {
        if (confirm('Delete this question?')) {
            $(`.question-item[data-section="${sectionId}"][data-question-id="${qId}"]`).remove();
            triggerAutosave();
        }
    }

    // Toggle options based on question type
    $(document).on('change', '.question-type', function () {
        const sectionId = $(this).data('section');
        const qId       = $(this).data('qid');
        const $options  = $(`#options-${sectionId}-${qId}`);
        $(this).val() === 'multiple_choice' ? $options.show() : $options.hide();
    });

    // ── Autosave ──────────────────────────────────────────────────────────────
    function triggerAutosave() {
        autosaveDirty = true;
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(saveDraft, AUTOSAVE_DELAY);
    }

    function saveDraft() {
        if (!autosaveDirty) return;

        const formData = {};
        $('#examForm').find('input, select, textarea').each(function () {
            const name = $(this).attr('name');
            if (!name || name === '_token') return;

            if ($(this).is(':checkbox')) {
                formData[name] = $(this).is(':checked');
            } else {
                formData[name] = $(this).val();
            }
        });

        // Also store the raw sections HTML so we can restore the DOM
        formData['__sections_html__'] = $('#exam-sections-container').html();
        formData['__section_counter__']  = sectionCounter;
        formData['__question_counters__'] = JSON.stringify(questionCounters);
        formData['__saved_at__']          = new Date().toISOString();

        showAutosaveState('saving');

        try {
            localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(formData));
            autosaveDirty = false;
            showAutosaveState('saved');
        } catch (e) {
            showAutosaveState('error');
        }
    }

    function showAutosaveState(state) {
        const $indicator = $('#autosave-indicator');
        const $spinner   = $('#autosave-spinner');
        const $ok        = $('#autosave-ok');
        const $text      = $('#autosave-text');

        $indicator.css('display', 'inline-flex !important').show();

        if (state === 'saving') {
            $spinner.show(); $ok.hide();
            $text.text('Saving draft...');
        } else if (state === 'saved') {
            $spinner.hide(); $ok.show();
            $text.text('Draft saved');
            setTimeout(function () { $indicator.hide(); }, 3000);
        } else if (state === 'error') {
            $spinner.hide(); $ok.hide();
            $text.html('<span class="text-danger">Save failed</span>');
        }
    }

    function restoreDraft() {
        try {
            const raw = localStorage.getItem(AUTOSAVE_KEY);
            if (!raw) return;

            const data = JSON.parse(raw);

            // Restore sections DOM
            if (data['__sections_html__']) {
                $('#exam-sections-container').html(data['__sections_html__']);
            }
            sectionCounter   = parseInt(data['__section_counter__'] || 0);
            questionCounters = JSON.parse(data['__question_counters__'] || '{}');

            // Restore form field values
            $('#examForm').find('input, select, textarea').each(function () {
                const name = $(this).attr('name');
                if (!name || name === '_token' || !(name in data)) return;

                if ($(this).is(':checkbox')) {
                    $(this).prop('checked', data[name] === true || data[name] === 'true');
                } else {
                    $(this).val(data[name]);
                }
            });

            $('#restore-banner').addClass('d-none');
        } catch (e) {
            console.error('Draft restore failed:', e);
        }
    }

    function discardDraft() {
        localStorage.removeItem(AUTOSAVE_KEY);
        $('#restore-banner').addClass('d-none');
    }

    // ── On input: mark dirty and schedule autosave ────────────────────────────
    $(document).on('input change', '.autosave-trigger, #exam_title, #total_marks, #session_id, #term_id, #section_id, #class_id, #subject_id, [name="exam_type"], [name="exam_date"], [name="duration_minutes"], [name="instructions"], [name="school_name"], [name="school_address"], [name="marking_scheme"], [name="notes"], #show_marking_scheme', function () {
        triggerAutosave();
    });

    // ── On form submit: clear draft ───────────────────────────────────────────
    $('#examForm').on('submit', function () {
        localStorage.removeItem(AUTOSAVE_KEY);
    });

    // ── On page load ──────────────────────────────────────────────────────────
    $(document).ready(function () {
        // Check for saved draft
        try {
            const raw = localStorage.getItem(AUTOSAVE_KEY);
            if (raw) {
                const data = JSON.parse(raw);
                const savedAt = data['__saved_at__'] ? new Date(data['__saved_at__']).toLocaleString() : '';
                $('#restore-banner').removeClass('d-none')
                    .find('i.fa-history').after(
                        savedAt ? ` <strong>Draft from ${savedAt}.</strong>` : ''
                    );
            }
        } catch (e) {}

        // Start with one blank section
        addSection();

        // Save draft every 30s even without input
        setInterval(saveDraft, AUTOSAVE_DELAY);
    });
    </script>

    @include('includes.edit_footer')
</body>
</html>