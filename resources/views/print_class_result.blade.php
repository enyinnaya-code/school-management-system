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
                                <h4>
                                    Print Results — {{ $class->name }} ({{ $section->section_name }})
                                    @if($usesResultSheet ?? false)
                                        <span class="badge badge-success ml-2" style="font-size:.65rem">
                                            <i class="fas fa-clipboard-check"></i> Skill Sheet
                                        </span>
                                    @endif
                                </h4>
                            </div>

                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif

                                {{-- ══ FILTER FORM — always shown for ALL classes ══ --}}
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <form method="GET"
                                              action="{{ request()->fullUrlWithoutQuery(['session_id','term_id','page']) }}"
                                              class="d-flex flex-wrap align-items-end" style="gap:8px">
                                            <input type="hidden" name="section_id" value="{{ $section->id }}">
                                            <input type="hidden" name="class_id"   value="{{ $class->id }}">

                                            {{-- Session selector --}}
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold mb-1">Session:</label>
                                                <select name="session_id" class="form-control"
                                                        onchange="this.form.submit()">
                                                    <option value="">-- Select Session --</option>
                                                    @foreach($sessions as $sess)
                                                        <option value="{{ $sess->id }}"
                                                            {{ $selectedSession?->id == $sess->id ? 'selected' : '' }}>
                                                            {{ $sess->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Term selector --}}
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold mb-1">Term:</label>
                                                <select name="term_id" class="form-control"
                                                        {{ $terms->isEmpty() ? 'disabled' : '' }}>
                                                    <option value="">-- Select Term --</option>
                                                    @foreach($terms as $term)
                                                        <option value="{{ $term->id }}"
                                                            {{ $selectedTerm?->id == $term->id ? 'selected' : '' }}>
                                                            {{ $term->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Apply Filter always visible --}}
                                            <button type="submit" class="btn btn-primary mb-0">
                                                <i class="fas fa-filter"></i> Apply Filter
                                            </button>

                                            @if(request()->has('session_id') || request()->has('term_id'))
                                                <a href="{{ route('results.selectClassForPrint', ['section_id' => $section->id, 'class_id' => $class->id]) }}"
                                                   class="btn btn-outline-secondary mb-0">
                                                    <i class="fas fa-sync"></i> Reset
                                                </a>
                                            @endif

                                            {{-- Extra buttons only for standard result classes --}}
                                            @if(!($usesResultSheet ?? false))
                                                @if($selectedSession && $selectedTerm)
                                                    <a href="{{ route('results.masterList', $class->id) }}?session_id={{ $selectedSession->id }}&term_id={{ $selectedTerm->id }}"
                                                       class="btn btn-success mb-0">
                                                        <i class="fas fa-table"></i> Master List
                                                    </a>
                                                    <a href="{{ route('results.cumulative', $class->id) }}"
                                                       class="btn btn-info mb-0">
                                                        <i class="fas fa-chart-line"></i> Cumulative Results
                                                    </a>
                                                @endif
                                            @endif
                                        </form>
                                    </div>
                                </div>

                                {{-- ══ INFO BANNER ══ --}}
                                @if($usesResultSheet ?? false)
                                    <div class="alert alert-success mb-4">
                                        <i class="fas fa-clipboard-check mr-1"></i>
                                        <strong>{{ $class->name }}</strong> uses a
                                        <strong>custom skill result sheet</strong>.
                                        @if($selectedSession && $selectedTerm)
                                            Showing: <strong>{{ $selectedSession->name }} — {{ $selectedTerm->name }}</strong>.
                                            Click <em>Preview</em> to view a student's skill sheet for this term.
                                        @else
                                            Please select a session and term to preview results.
                                        @endif
                                    </div>
                                @elseif($selectedSession && $selectedTerm)
                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Currently viewing:</strong>
                                        {{ $selectedSession->name }} — {{ $selectedTerm->name }}
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-4">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Please select a Session and Term to view results.
                                    </div>
                                @endif

                                {{-- ══ STUDENTS TABLE ══ --}}
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>S/N</th>
                                                <th>Student Name</th>
                                                <th>Admission No</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($students as $index => $student)
                                                <tr>
                                                    <td>{{ $students->firstItem() + $index }}</td>
                                                    <td>{{ $student->name }}</td>
                                                    <td>{{ $student->admission_no }}</td>
                                                    <td>
                                                        @if($usesResultSheet ?? false)
                                                            {{-- SKILL SHEET CLASS --}}
                                                            @if($selectedSession && $selectedTerm)
                                                                <a href="{{ route('results.printStudentSheet', $student->id) }}?session_id={{ $selectedSession->id }}&term_id={{ $selectedTerm->id }}"
                                                                   class="btn btn-sm btn-success" target="_blank"
                                                                   title="Preview Skill Sheet">
                                                                    <i class="fas fa-eye"></i> Preview
                                                                </a>
                                                            @else
                                                                <span class="text-muted small">Select session &amp; term first</span>
                                                            @endif

                                                        @elseif($selectedSession && $selectedTerm)
                                                            {{-- STANDARD RESULT CLASS --}}
                                                            <a href="{{ route('results.printStudent', [$student->id, 'stream']) }}?session_id={{ $selectedSession->id }}&term_id={{ $selectedTerm->id }}"
                                                               class="btn btn-sm btn-info" target="_blank"
                                                               title="Preview Report Card">
                                                                <i class="fas fa-eye"></i> Preview Only
                                                            </a>
                                                            <a href="{{ route('results.remarks.edit', $student->id) }}?session_id={{ $selectedSession->id }}&term_id={{ $selectedTerm->id }}"
                                                               class="btn btn-sm btn-warning ml-1"
                                                               title="Edit Skills & Remarks">
                                                                <i class="fas fa-comment-dots"></i> Remarks
                                                            </a>
                                                            <a href="{{ route('results.transcript', $student->id) }}"
                                                               class="btn btn-sm btn-secondary ml-1" target="_blank">
                                                                <i class="fas fa-file-alt"></i> Transcript
                                                            </a>

                                                        @else
                                                            <span class="text-muted small">
                                                                Select session &amp; term first
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">
                                                        No students found in this class.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-center mt-4">
                                    {{ $students->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')
</body>