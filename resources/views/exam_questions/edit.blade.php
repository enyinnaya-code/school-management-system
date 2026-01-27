{{-- Key UX Improvements:
1. Collapsible sections and questions
2. Sticky section navigation sidebar
3. Compact/expanded view toggle
4. Jump-to-question navigation
5. Bulk question operations
6. Question preview mode
--}}

@include('includes.head')

<style>
    /* Sticky Navigation Sidebar */
    .sections-nav {
        position: fixed;
        left: 20px;
        top: 120px;
        width: 220px;
        max-height: calc(100vh - 140px);
        overflow-y: auto;
        background: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 100;
    }

    .sections-nav h6 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }

    .sections-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sections-nav li {
        margin-bottom: 8px;
    }

    .sections-nav a {
        display: block;
        padding: 6px 10px;
        font-size: 13px;
        color: #666;
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .sections-nav a:hover {
        background: #f8f9fc;
        color: #4e73df;
    }

    .sections-nav a.active {
        background: #4e73df;
        color: #fff;
    }

    .sections-nav .question-nav {
        padding-left: 15px;
        margin-top: 5px;
    }

    .sections-nav .question-nav a {
        font-size: 12px;
        padding: 4px 8px;
    }

   

    /* Collapsible sections */
    .section-collapse-toggle {
        cursor: pointer;
        user-select: none;
    }

    .section-collapse-toggle i {
        transition: transform 0.3s;
    }

    .section-collapse-toggle.collapsed i {
        transform: rotate(-90deg);
    }

    .section-body-collapsible {
        transition: max-height 0.3s ease;
    }



    /* Question header for easy scanning */
    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        background: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        cursor: pointer;
    }

    .question-header:hover {
        background: #eef0f8;
    }

    .question-preview {
        font-size: 13px;
        color: #666;
        max-width: 70%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .question-meta {
        font-size: 12px;
        color: #999;
    }

    /* Floating action buttons */
    .floating-actions {
        position: fixed;
        bottom: 30px;
        right: 35vw;
        z-index: 1000;
        margin: auto;
    }

    .floating-actions .btn {
        margin-left: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }



    /* Scroll to top button */
    .scroll-top-btn {
        position: fixed;
        bottom: 100px;
        right: 30px;
        display: none;
        z-index: 999;
    }

    /* Question numbering badge */
    .question-number-badge {
        display: inline-block;
        background: #4e73df;
        color: #fff;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    @media (max-width: 1200px) {
        .sections-nav {
            display: none;
        }
        .main-content-with-nav {
            margin-left: 0;
        }
    }
</style>

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            {{-- Sticky Navigation Sidebar --}}
            <div class="sections-nav" id="sectionsNav">
                <h6><i class="fas fa-list"></i> Quick Navigation</h6>
                <ul id="navList">
                    <li><a href="#basic-info">Basic Information</a></li>
                </ul>
            </div>



            {{-- Scroll to Top Button --}}
            <button class="btn btn-primary scroll-top-btn" id="scrollTopBtn">
                <i class="fas fa-arrow-up"></i>
            </button>

            <div class="main-content pt-5 mt-5 main-content-with-nav">
                <section class="section mb-5">
                    <div class="section-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-edit"></i> Edit Exam Question Paper</h4>
                                        <div class="card-header-action">
                                            <a href="{{ route('exam_questions.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Back to List
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('exam_questions.update', $exam->id) }}" method="POST" id="examForm">
                                            @csrf
                                            @method('PUT')

                                            {{-- Basic Information --}}
                                            <div class="card mb-4" id="basic-info">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h6>
                                                </div>
                                                <div class="card-body">
                                                    {{-- Same basic info fields as before --}}
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Section/Arm <span class="text-danger">*</span></label>
                                                                <select name="section_id" id="section_id" class="form-control" required>
                                                                    <option value="">Select Section</option>
                                                                    @foreach($sections as $section)
                                                                    <option value="{{ $section->id }}" {{ $exam->section_id == $section->id ? 'selected' : '' }}>
                                                                        {{ $section->section_name }}
                                                                    </option>
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
                                                                    <option value="{{ $session->id }}" {{ $exam->session_id == $session->id ? 'selected' : '' }}>
                                                                        {{ $session->name }}
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
                                                                    <option value="{{ $term->id }}" {{ $exam->term_id == $term->id ? 'selected' : '' }}>
                                                                        {{ $term->name }}
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
                                                                    @foreach($classes as $class)
                                                                    <option value="{{ $class->id }}" {{ $exam->class_id == $class->id ? 'selected' : '' }}>
                                                                        {{ $class->name }}
                                                                    </option>
                                                                    @endforeach
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
                                                                    @foreach($subjects as $subject)
                                                                    <option value="{{ $subject->id }}" {{ $exam->subject_id == $subject->id ? 'selected' : '' }}>
                                                                        {{ $subject->course_name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Exam Title <span class="text-danger">*</span></label>
                                                                <input type="text" name="exam_title" class="form-control"
                                                                    value="{{ old('exam_title', $exam->exam_title) }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Exam Type <span class="text-danger">*</span></label>
                                                                <select name="exam_type" class="form-control" required>
                                                                    @foreach($examTypes as $type)
                                                                    <option value="{{ $type }}" {{ old('exam_type', $exam->exam_type) == $type ? 'selected' : '' }}>
                                                                        {{ $type }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Exam Date</label>
                                                                <input type="date" name="exam_date" class="form-control"
                                                                    value="{{ old('exam_date', $exam->exam_date?->format('Y-m-d')) }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Duration (minutes)</label>
                                                                <input type="number" name="duration_minutes" class="form-control"
                                                                    value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="1">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Total Marks <span class="text-danger">*</span></label>
                                                                <input type="number" name="total_marks" class="form-control"
                                                                    value="{{ old('total_marks', $exam->total_marks) }}" min="1" required>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>General Instructions</label>
                                                                <textarea name="instructions" class="form-control" rows="3">{{ old('instructions', $exam->instructions) }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>School Name (Optional)</label>
                                                                <input type="text" name="school_name" class="form-control"
                                                                    value="{{ old('school_name', $exam->school_name) }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>School Address (Optional)</label>
                                                                <input type="text" name="school_address" class="form-control"
                                                                    value="{{ old('school_address', $exam->school_address) }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Exam Sections with Collapsible Design --}}
                                            <div class="card mb-4">
                                                <div id="exam-sections-container">
                                                    @php
                                                    $sectionsData = old('sections', null);
                                                    if ($sectionsData === null) {
                                                        $sectionsData = is_string($exam->sections) 
                                                            ? json_decode($exam->sections, true) 
                                                            : ($exam->sections ?? []);
                                                    }
                                                    $sectionsData = is_array($sectionsData) ? $sectionsData : [];
                                                    @endphp

                                                    @if(!empty($sectionsData))
                                                    @foreach($sectionsData as $secIndex => $section)
                                                    <div class="card mb-3 exam-section" data-section-id="{{ $secIndex }}" id="section-{{ $secIndex }}">
                                                        <div class="card-header bg-light d-flex justify-content-between align-items-center section-collapse-toggle">
                                                            <h6 class="mb-0" style="flex: 1;">
                                                                <i class="fas fa-chevron-down"></i>
                                                                <span class="ml-2">Section {{ $loop->iteration }}</span>
                                                                <input type="text" name="sections[{{ $secIndex }}][title]"
                                                                    class="form-control d-inline-block ml-3" style="width: 50%;"
                                                                    value="{{ $section['title'] ?? '' }}"
                                                                    placeholder="e.g., Section A - Objective"
                                                                    onclick="event.stopPropagation();" required>
                                                            </h6>
                                                            <div>
                                                                <button type="button" class="btn btn-sm btn-info mr-2"
                                                                    onclick="event.stopPropagation(); collapseAllQuestions({{ $secIndex }})">
                                                                    <i class="fas fa-compress-alt"></i> Collapse All
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-warning mr-2"
                                                                    onclick="event.stopPropagation(); expandAllQuestions({{ $secIndex }})">
                                                                    <i class="fas fa-expand-alt"></i> Expand All
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger"
                                                                    onclick="event.stopPropagation(); removeSection({{ $secIndex }})">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="section-body-collapsible">
                                                            <div class="card-body">
                                                                <div class="form-group">
                                                                    <label>Section Instructions (Optional)</label>
                                                                    <textarea name="sections[{{ $secIndex }}][instructions]"
                                                                        class="form-control" rows="2">{{ $section['instructions'] ?? '' }}</textarea>
                                                                </div>

                                                                <div class="questions-container" id="questions-{{ $secIndex }}">
                                                                    @if(isset($section['questions']) && is_array($section['questions']))
                                                                    @foreach($section['questions'] as $qIndex => $question)
                                                                    <div class="card mb-2 question-item" data-question-id="{{ $qIndex }}" id="question-{{ $secIndex }}-{{ $qIndex }}">
                                                                        {{-- Question Header for Preview --}}
                                                                        <div class="question-header" onclick="toggleQuestionBody({{ $secIndex }}, {{ $qIndex }})">
                                                                            <div>
                                                                                <span class="question-number-badge">Q{{ $loop->iteration }}</span>
                                                                                <span class="question-preview ml-2">
                                                                                    {{ Str::limit($question['text'] ?? 'New Question', 60) }}
                                                                                </span>
                                                                            </div>
                                                                            <div class="question-meta">
                                                                                <span class="badge badge-primary">{{ ucfirst($question['type'] ?? 'multiple_choice') }}</span>
                                                                                <span class="badge badge-success">{{ $question['marks'] ?? 0 }} marks</span>
                                                                                <i class="fas fa-chevron-down ml-2"></i>
                                                                            </div>
                                                                        </div>

                                                                        {{-- Question Body (Collapsible) --}}
                                                                        <div class="card-body question-body" style="display:none;">
                                                                            <div class="row align-items-start">
                                                                                <div class="col-md-11">
                                                                                    <div class="form-group">
                                                                                        <label>Question {{ $loop->parent->iteration }}.{{ $loop->iteration }}</label>
                                                                                        <textarea name="sections[{{ $secIndex }}][questions][{{ $qIndex }}][text]"
                                                                                            class="form-control question-text-input" rows="3"
                                                                                            data-section="{{ $secIndex }}" data-qid="{{ $qIndex }}"
                                                                                            required>{{ $question['text'] ?? '' }}</textarea>
                                                                                    </div>

                                                                                    <div class="row">
                                                                                        <div class="col-md-4">
                                                                                            <label>Type</label>
                                                                                            <select name="sections[{{ $secIndex }}][questions][{{ $qIndex }}][type]"
                                                                                                class="form-control question-type"
                                                                                                data-section="{{ $secIndex }}" data-qid="{{ $qIndex }}">
                                                                                                <option value="essay" {{ ($question['type'] ?? '') == 'essay' ? 'selected' : '' }}>Essay</option>
                                                                                                <option value="short" {{ ($question['type'] ?? '') == 'short' ? 'selected' : '' }}>Short Answer</option>
                                                                                                <option value="multiple_choice" {{ ($question['type'] ?? 'multiple_choice') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                                                                                <option value="true_false" {{ ($question['type'] ?? '') == 'true_false' ? 'selected' : '' }}>True/False</option>
                                                                                                <option value="fill_blank" {{ ($question['type'] ?? '') == 'fill_blank' ? 'selected' : '' }}>Fill in the Blank</option>
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="col-md-4">
                                                                                            <label>Marks</label>
                                                                                            <input type="number"
                                                                                                name="sections[{{ $secIndex }}][questions][{{ $qIndex }}][marks]"
                                                                                                class="form-control" min="1"
                                                                                                value="{{ $question['marks'] ?? '' }}" required>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="options-container mt-3" id="options-{{ $secIndex }}-{{ $qIndex }}"
                                                                                        style="{{ ($question['type'] ?? 'multiple_choice') === 'multiple_choice' ? 'display:block' : 'display:none' }}">
                                                                                        <label>Options (A–D)</label>
                                                                                        @php $options = $question['options'] ?? ['', '', '', '']; @endphp
                                                                                        @foreach(['A', 'B', 'C', 'D'] as $i => $letter)
                                                                                        <div class="input-group mb-2">
                                                                                            <div class="input-group-prepend">
                                                                                                <span class="input-group-text">{{ $letter }}</span>
                                                                                            </div>
                                                                                            <input type="text"
                                                                                                name="sections[{{ $secIndex }}][questions][{{ $qIndex }}][options][]"
                                                                                                class="form-control" value="{{ $options[$i] ?? '' }}"
                                                                                                placeholder="Option {{ $letter }}">
                                                                                        </div>
                                                                                        @endforeach
                                                                                    </div>

                                                                                    <div class="form-group mt-3">
                                                                                        <label>Model Answer / Notes (Optional)</label>
                                                                                        <textarea name="sections[{{ $secIndex }}][questions][{{ $qIndex }}][answer]"
                                                                                            class="form-control" rows="2">{{ $question['answer'] ?? '' }}</textarea>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-1 text-right">
                                                                                    <button type="button" class="btn btn-danger btn-sm"
                                                                                        onclick="removeQuestion({{ $secIndex }}, {{ $qIndex }})">
                                                                                        <i class="fas fa-trash"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                    @endif
                                                                </div>

                                                                <button type="button" class="btn btn-sm btn-primary mt-2"
                                                                    onclick="addQuestion({{ $secIndex }})">
                                                                    <i class="fas fa-plus"></i> Add Question
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                    @endif
                                                </div>
                                             
                                                <div class="card-body">
                                                    <button type="button" class="btn btn-success" onclick="addSection()">
                                                        <i class="fas fa-plus"></i> Add Section
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Additional Options --}}
                                            <div class="card mb-4" id="additional-options">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0"><i class="fas fa-cog"></i> Additional Options</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="show_marking_scheme" name="show_marking_scheme"
                                                                {{ old('show_marking_scheme', $exam->show_marking_scheme) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="show_marking_scheme">
                                                                Show Marking Scheme on Print
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Marking Scheme / Answer Key (Optional)</label>
                                                        <textarea name="marking_scheme" class="form-control" rows="4">{{ old('marking_scheme', $exam->marking_scheme) }}</textarea>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Private Notes (Optional)</label>
                                                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $exam->notes) }}</textarea>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select name="status" class="form-control" required>
                                                            <option value="draft" {{ old('status', $exam->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                                            <option value="published" {{ old('status', $exam->status) == 'published' ? 'selected' : '' }}>Published</option>
                                                            <option value="archived" {{ old('status', $exam->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                                                        </select>
                                                    </div>
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

            {{-- Floating Action Buttons --}}
            <div class="floating-actions">
                <button type="button" class="btn btn-primary" onclick="$('#examForm').submit();">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="{{ route('exam_questions.show', $exam->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </div>

    <script>
    @php
        $sectionsData = old('sections', null);
        if ($sectionsData === null) {
            $sectionsData = is_string($exam->sections) 
                ? json_decode($exam->sections, true) 
                : ($exam->sections ?? []);
        }
        $sectionsData = is_array($sectionsData) ? $sectionsData : [];
    @endphp

    let sectionCounter = {{ count($sectionsData) }};
    let questionCounters = {};

    @if(!empty($sectionsData))
        @foreach($sectionsData as $secIndex => $section)
            questionCounters['{{ $secIndex }}'] = {{ count($section['questions'] ?? []) }};
        @endforeach
    @endif

    // === Navigation & UX Functions ===
    function updateNavigation() {
        const navList = $('#navList');
        navList.find('li:not(:first)').remove();

        $('.exam-section').each(function(index) {
            const sectionId = $(this).data('section-id');
            const sectionTitle = $(this).find('input[name*="[title]"]').val() || `Section ${index + 1}`;
            
            navList.append(`
                <li>
                    <a href="#section-${sectionId}">${sectionTitle}</a>
                    <ul class="question-nav" id="nav-questions-${sectionId}"></ul>
                </li>
            `);

            // Add questions for this section
            $(`#questions-${sectionId} .question-item`).each(function(qIndex) {
                const qId = $(this).data('question-id');
                $(`#nav-questions-${sectionId}`).append(`
                    <li><a href="#question-${sectionId}-${qId}">Q${qIndex + 1}</a></li>
                `);
            });
        });

        navList.append(`<li><a href="#additional-options">Additional Options</a></li>`);
    }

    // Toggle question body
    function toggleQuestionBody(sectionId, qId) {
        const $body = $(`#question-${sectionId}-${qId} .question-body`);
        const $icon = $(`#question-${sectionId}-${qId} .question-header i`);
        
        if ($body.is(':visible')) {
            $body.slideUp(200);
            $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            $body.slideDown(200);
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    }

    // Collapse all questions in a section
    function collapseAllQuestions(sectionId) {
        $(`#questions-${sectionId} .question-body`).slideUp(200);
        $(`#questions-${sectionId} .question-header i`).removeClass('fa-chevron-up').addClass('fa-chevron-down');
    }

    // Expand all questions in a section
    function expandAllQuestions(sectionId) {
        $(`#questions-${sectionId} .question-body`).slideDown(200);
        $(`#questions-${sectionId} .question-header i`).removeClass('fa-chevron-down').addClass('fa-chevron-up');
    }

    // Update question preview when typing
    $(document).on('input', '.question-text-input', function() {
        const sectionId = $(this).data('section');
        const qId = $(this).data('qid');
        const text = $(this).val();
        const preview = text.substring(0, 60) + (text.length > 60 ? '...' : '');
        
        $(`#question-${sectionId}-${qId} .question-preview`).text(preview || 'New Question');
    });

    // Scroll to top
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#scrollTopBtn').fadeIn();
        } else {
            $('#scrollTopBtn').fadeOut();
        }
    });

    $('#scrollTopBtn').click(function() {
        $('html, body').animate({scrollTop: 0}, 600);
    });

    // Smooth scroll for navigation
    $(document).on('click', '.sections-nav a', function(e) {
        e.preventDefault();
        const target = $(this).attr('href');
        if ($(target).length) {
            $('html, body').animate({
                scrollTop: $(target).offset().top - 100
            }, 500);
            
            $('.sections-nav a').removeClass('active');
            $(this).addClass('active');
        }
    });

    // Toggle section collapse
    $(document).on('click', '.section-collapse-toggle', function() {
        const $section = $(this).closest('.exam-section');
        const $body = $section.find('.section-body-collapsible');
        const $icon = $(this).find('i').first();
        
        if ($body.is(':visible')) {
            $body.slideUp(300);
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
            $(this).addClass('collapsed');
        } else {
            $body.slideDown(300);
            $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
            $(this).removeClass('collapsed');
        }
    });

    // === Original Functions (Modified for Navigation Update) ===
    function addSection() {
        sectionCounter++;
        const sectionHtml = `
            <div class="card mb-3 exam-section" data-section-id="${sectionCounter}" id="section-${sectionCounter}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center section-collapse-toggle">
                    <h6 class="mb-0" style="flex: 1;">
                        <i class="fas fa-chevron-down"></i>
                        <span class="ml-2">Section ${sectionCounter}</span>
                        <input type="text" name="sections[${sectionCounter}][title]" 
                               class="form-control d-inline-block ml-3" style="width: 50%;" 
                               placeholder="e.g., Section A - Objective" 
                               onclick="event.stopPropagation();" required>
                    </h6>
                    <div>
                        <button type="button" class="btn btn-sm btn-info mr-2"
                            onclick="event.stopPropagation(); collapseAllQuestions(${sectionCounter})">
                            <i class="fas fa-compress-alt"></i> Collapse All
                        </button>
                        <button type="button" class="btn btn-sm btn-warning mr-2"
                            onclick="event.stopPropagation(); expandAllQuestions(${sectionCounter})">
                            <i class="fas fa-expand-alt"></i> Expand All
                        </button>
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="event.stopPropagation(); removeSection(${sectionCounter})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="section-body-collapsible">
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
                </div>
            </div>`;
        
        $('#exam-sections-container').append(sectionHtml);
        updateNavigation();
    }

    function removeSection(id) {
        if (confirm('Remove this section and all its questions?')) {
            $(`.exam-section[data-section-id="${id}"]`).remove();
            updateNavigation();
        }
    }

    function addQuestion(sectionId) {
        if (!questionCounters[sectionId]) questionCounters[sectionId] = 0;
        questionCounters[sectionId]++;

        const qId = questionCounters[sectionId];

        const questionHtml = `
            <div class="card mb-2 question-item" data-question-id="${qId}" id="question-${sectionId}-${qId}">
                <div class="question-header" onclick="toggleQuestionBody(${sectionId}, ${qId})">
                    <div>
                        <span class="question-number-badge">Q${qId}</span>
                        <span class="question-preview ml-2">New Question</span>
                    </div>
                    <div class="question-meta">
                        <span class="badge badge-primary">Multiple Choice</span>
                        <span class="badge badge-success">0 marks</span>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </div>
                </div>

                <div class="card-body question-body" style="display:block;">
                    <div class="row align-items-start">
                        <div class="col-md-11">
                            <div class="form-group">
                                <label>Question ${qId}</label>
                                <textarea name="sections[${sectionId}][questions][${qId}][text]" 
                                          class="form-control question-text-input" rows="3"
                                          data-section="${sectionId}" data-qid="${qId}" required></textarea>
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
                                           class="form-control" min="1" required>
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
        updateNavigation();
    }

    function removeQuestion(sectionId, qId) {
        if (confirm('Delete this question?')) {
            $(`#question-${sectionId}-${qId}`).remove();
            updateNavigation();
        }
    }

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

    // === Dropdown Logic (Same as before) ===
    $('#section_id').on('change', function() {
        const sectionId = $(this).val();
        $('#session_id, #term_id, #class_id, #subject_id')
            .prop('disabled', true)
            .html('<option value="">Select...</option>');

        if (!sectionId) return;

        $.get('/ajax/sessions/' + sectionId)
            .done(function(data) {
                $('#session_id').prop('disabled', false)
                                .html('<option value="">Select Session</option>');
                data.sessions.forEach(session => {
                    const selected = session.id == data.current_session_id ? 'selected' : '';
                    $('#session_id').append(`<option value="${session.id}" ${selected}>${session.name}</option>`);
                });
                if (data.current_session_id) {
                    loadTerms(data.current_session_id);
                }
            });

        $.get('/ajax/classes/' + sectionId)
            .done(function(data) {
                $('#class_id').prop('disabled', false)
                              .html('<option value="">Select Class</option>');
                data.classes.forEach(cls => {
                    $('#class_id').append(`<option value="${cls.id}">${cls.name}</option>`);
                });
            });
    });

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
            });
    }

    $('#session_id').on('change', function() {
        loadTerms($(this).val());
    });

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
            });
    });

    // Initialize on page load
    $(document).ready(function() {
        updateNavigation();
        
        @if(empty($sectionsData))
            addSection();
        @endif

        // Collapse all questions by default for better UX
        $('.question-body').hide();
        $('.question-header i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });
    </script>

    @include('includes.edit_footer')
</body>
</html>