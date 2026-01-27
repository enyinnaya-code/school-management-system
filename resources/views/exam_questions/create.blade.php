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
                                    <div class="card-header">
                                        <h4><i class="fas fa-plus-circle"></i> Create Exam Question Paper</h4>
                                        <div class="card-header-action">
                                            <a href="{{ route('exam_questions.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Back to List
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('exam_questions.store') }}" method="POST" id="examForm">
                                            @csrf

                                            {{-- Basic Information --}}
                                            <div class="card mb-4">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Basic
                                                        Information</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Section/Arm <span
                                                                        class="text-danger">*</span></label>
                                                                <select name="section_id" id="section_id"
                                                                    class="form-control" required>
                                                                    <option value="">Select Section</option>
                                                                    @foreach($sections as $section)
                                                                    <option value="{{ $section->id }}">{{
                                                                        $section->section_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Session <span
                                                                        class="text-danger">*</span></label>
                                                                <select name="session_id" id="session_id"
                                                                    class="form-control" required disabled>
                                                                    <option value="">Select Session</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Term <span class="text-danger">*</span></label>
                                                                <select name="term_id" id="term_id" class="form-control"
                                                                    required disabled>
                                                                    <option value="">Select Term</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Class <span class="text-danger">*</span></label>
                                                                <select name="class_id" id="class_id"
                                                                    class="form-control" required disabled>
                                                                    <option value="">Select Class</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Subject <span
                                                                        class="text-danger">*</span></label>
                                                                <select name="subject_id" id="subject_id"
                                                                    class="form-control" required disabled>
                                                                    <option value="">Select Subject</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Exam Title <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="exam_title"
                                                                    class="form-control"
                                                                    placeholder="e.g., First Term Mathematics Exam"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Exam Type <span
                                                                        class="text-danger">*</span></label>
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
                                                                <input type="date" name="exam_date"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Duration (minutes)</label>
                                                                <input type="number" name="duration_minutes"
                                                                    class="form-control" placeholder="e.g., 90" min="1">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Total Marks <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="number" name="total_marks" id="total_marks"
                                                                    class="form-control" placeholder="e.g., 100" min="1"
                                                                    required>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>General Instructions</label>
                                                                <textarea name="instructions" class="form-control"
                                                                    rows="3"
                                                                    placeholder="e.g., Answer all questions. Write legibly."></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>School Name (Optional)</label>
                                                                <input type="text" name="school_name"
                                                                    class="form-control"
                                                                    placeholder="e.g., St. Mary's High School">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>School Address (Optional)</label>
                                                                <input type="text" name="school_address"
                                                                    class="form-control"
                                                                    placeholder="e.g., 123 Education St.">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Exam Sections --}}
                                            <div class="card mb-4">
                                                <div class="card-header bg-success text-white">
                                                    <h6 class="mb-0"><i class="fas fa-list"></i> Exam Sections &
                                                        Questions</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="exam-sections-container"></div>

                                                    <button type="button" class="btn btn-success"
                                                        onclick="addSection()">
                                                        <i class="fas fa-plus"></i> Add Section
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Additional Options --}}
                                            <div class="card mb-4">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0"><i class="fas fa-cog"></i> Additional Options</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="show_marking_scheme" name="show_marking_scheme">
                                                            <label class="custom-control-label"
                                                                for="show_marking_scheme">
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

                                            {{-- Submit Buttons --}}
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button type="submit" name="status" value="draft"
                                                        class="btn btn-warning">
                                                        <i class="fas fa-save"></i> Save as Draft
                                                    </button>
                                                    <button type="submit" name="status" value="published"
                                                        class="btn btn-success">
                                                        <i class="fas fa-check"></i> Save & Publish
                                                    </button>
                                                    <a href="{{ route('exam_questions.index') }}"
                                                        class="btn btn-secondary">
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
        let sectionCounter = 0;
    let questionCounters = {};

        // Main handler when Section is selected
        $('#section_id').on('change', function() {
            const sectionId = $(this).val();

            // Reset all dependent dropdowns
            $('#session_id, #term_id, #class_id, #subject_id')
                .prop('disabled', true)
                .html('<option value="">Select...</option>');

            if (!sectionId) return;

            // 1. Load Sessions + auto-select current
            $.get('/ajax/sessions/' + sectionId)
                .done(function(data) {
                    $('#session_id').prop('disabled', false)
                                    .html('<option value="">Select Session</option>');

                    data.sessions.forEach(session => {
                        const selected = session.id == data.current_session_id ? 'selected' : '';
                        $('#session_id').append(`<option value="${session.id}" ${selected}>${session.name}</option>`);
                    });

                    // If there is a current session, auto-load its terms
                    if (data.current_session_id) {
                        loadTerms(data.current_session_id);
                    }
                })
                .fail(function() {
                    alert('Failed to load sessions.');
                });

            // 2. Load Classes (independent of session/term)
            $.get('/ajax/classes/' + sectionId)
                .done(function(data) {
                    $('#class_id').prop('disabled', false)
                                .html('<option value="">Select Class</option>');

                    data.classes.forEach(cls => {
                        $('#class_id').append(`<option value="${cls.id}">${cls.name}</option>`);
                    });
                })
                .fail(function() {
                    alert('Failed to load classes.');
                });
        });

        // Function to load terms for a given session and auto-select current term
        function loadTerms(sessionId) {
            if (!sessionId) {
                $('#term_id').prop('disabled', true).html('<option value="">Select Term</option>');
                return;
            }

            $.get('/ajax/terms/' + sessionId)
                .done(function(data) {
                    $('#term_id').prop('disabled', false)
                                .html('<option value="">Select Term</option>');

                    data.terms.forEach(term => {
                        const selected = term.id == data.current_term_id ? 'selected' : '';
                        $('#term_id').append(`<option value="${term.id}" ${selected}>${term.name}</option>`);
                    });
                })
                .fail(function() {
                    alert('Failed to load terms.');
                });
        }

        // Manual session change (in case user changes from current session)
        $('#session_id').on('change', function() {
            const sessionId = $(this).val();
            loadTerms(sessionId);
        });

        // Load subjects when class is selected
        $('#class_id').on('change', function() {
            const sectionId = $('#section_id').val();
            const classId = $(this).val();

            $('#subject_id').prop('disabled', true)
                            .html('<option value="">Select Subject</option>');

            if (!sectionId || !classId) return;

            $.get(`/ajax/subjects/${sectionId}/${classId}`)
                .done(function(data) {
                    $('#subject_id').prop('disabled', false)
                                    .html('<option value="">Select Subject</option>');

                    if (data.subjects.length === 0) {
                        $('#subject_id').append('<option value="">No subjects assigned</option>');
                        return;
                    }

                    data.subjects.forEach(subject => {
                        $('#subject_id').append(`<option value="${subject.id}">${subject.course_name}</option>`);
                    });
                })
                .fail(function() {
                    alert('No subjects found or access denied for this class.');
                });
        });

        // === Exam Sections & Questions Management ===
        function addSection() {
            sectionCounter++;
            const sectionHtml = `
                <div class="card mb-3 exam-section" data-section-id="${sectionCounter}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-folder"></i> Section ${sectionCounter}
                            <input type="text" name="sections[${sectionCounter}][title]" 
                                class="form-control d-inline-block ml-3" style="width: 60%;" 
                                placeholder="e.g., Section A - Objective" required>
                        </h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeSection(${sectionCounter})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Section Instructions (Optional)</label>
                            <textarea name="sections[${sectionCounter}][instructions]" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="questions-container" id="questions-${sectionCounter}"></div>

                        <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addQuestion(${sectionCounter})">
                            <i class="fas fa-plus"></i> Add Question
                        </button>
                    </div>
                </div>`;
            
            $('#exam-sections-container').append(sectionHtml);
        }

            function removeSection(id) {
                if (confirm('Remove this section and all its questions?')) {
                    $(`.exam-section[data-section-id="${id}"]`).remove();
                }
            }

            function addQuestion(sectionId) {
                if (!questionCounters[sectionId]) questionCounters[sectionId] = 0;
                questionCounters[sectionId]++;

                const qId = questionCounters[sectionId];

                const questionHtml = `
                    <div class="card mb-3 question-item" data-question-id="${qId}">
                        <div class="card-body">
                            <div class="row align-items-start">
                                <div class="col-md-11">
                                    <div class="form-group">
                                        <label>Question ${qId}</label>
                                        <textarea name="sections[${sectionId}][questions][${qId}][text]" 
                                                class="form-control" rows="3" required placeholder="Enter question text..."></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Type</label>
                                            <select name="sections[${sectionId}][questions][${qId}][type]" 
                                                    class="form-control question-type" 
                                                    data-section="${sectionId}" data-qid="${qId}">
                                                <option value="essay">Essay</option>
                                                <option value="short">Short Answer</option>
                                                <option value="multiple_choice" selected>Multiple Choice</option>
                                                <option value="true_false">True/False</option>
                                                <option value="fill_blank">Fill in the Blank</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Marks</label>
                                            <input type="number" name="sections[${sectionId}][questions][${qId}][marks]" 
                                                class="form-control" min="1" required placeholder="e.g., 5">
                                        </div>
                                    </div>

                                    <div class="options-container mt-3" id="options-${sectionId}-${qId}">
                                        <label>Options (A–D)</label>
                                        ${['A', 'B', 'C', 'D'].map(letter => `
                                            <div class="input-group mb-2">
                                                <div class="input-group-prepend"><span class="input-group-text">${letter}</span></div>
                                                <input type="text" name="sections[${sectionId}][questions][${qId}][options][]" 
                                                    class="form-control" placeholder="Option ${letter}">
                                            </div>
                                        `).join('')}
                                    </div>

                                    <div class="form-group mt-3">
                                        <label>Model Answer / Notes (Optional)</label>
                                        <textarea name="sections[${sectionId}][questions][${qId}][answer]" 
                                                class="form-control" rows="2"></textarea>
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

                $(`#questions-${sectionId}`).append(questionHtml);
            }

            function removeQuestion(sectionId, qId) {
                if (confirm('Delete this question?')) {
                    $(`.question-item[data-question-id="${qId}"]`).remove();
                }
            }

            // Toggle options visibility based on question type
            $(document).on('change', '.question-type', function() {
                const sectionId = $(this).data('section');
                const qId = $(this).data('qid');
                const $options = $(`#options-${sectionId}-${qId}`);

                if ($(this).val() === 'multiple_choice') {
                    $options.show();
                } else {
                    $options.hide();
                }
            });

            // Initialize one section when page loads
            $(document).ready(function() {
                addSection();
            });
    </script>

    @include('includes.edit_footer')
</body>

</html>