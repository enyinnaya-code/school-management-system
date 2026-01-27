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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Print Results - {{ $class->name }} ({{ $section->section_name }})</h4>
                                <div>
                                    @if($selectedSession && $selectedTerm)
                                    {{-- <a
                                        href="{{ route('results.masterList', $class->id) }}?session_id={{ $selectedSession->id }}&term_id={{ $selectedTerm->id }}"
                                        class="btn btn-success mr-2">
                                        <i class="fas fa-table"></i> View Master List
                                    </a> --}}
                                    @endif
                                    {{-- <a href="{{ route('results.print') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Select Class
                                    </a> --}}
                                </div>
                            </div>

                            <div class="card-body">
                                @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                </div>
                                @endif
                                @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                </div>
                                @endif

                                <!-- Session & Term Filter -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <form method="GET"
                                            action="{{ request()->fullUrlWithoutQuery(['session_id', 'term_id', 'page']) }}"
                                            class="d-flex flex-wrap align-items-end gap-3">
                                            <input type="hidden" name="section_id" value="{{ $section->id }}">
                                            <input type="hidden" name="class_id" value="{{ $class->id }}">

                                            <div class="form-group mb-0 m-1">
                                                <label for="session_id"
                                                    class="font-weight-bold mr-2 mb-1">Session:</label>
                                                <select name="session_id" id="session_id" class="form-control"
                                                    onchange="this.form.submit()">
                                                    <option value="">-- Select Session --</option>
                                                    @foreach($sessions as $sess)
                                                    <option value="{{ $sess->id }}" {{ $selectedSession?->id ==
                                                        $sess->id ? 'selected' : '' }}>
                                                        {{ $sess->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group mb-0 m-1 mx-0">
                                                <label for="term_id" class="font-weight-bold mr-2 mb-1">Term:</label>
                                                <select name="term_id" id="term_id" class="form-control" {{
                                                    $terms->isEmpty() ? 'disabled' : '' }}>
                                                    <option value="">-- Select Term --</option>
                                                    @foreach($terms as $term)
                                                    <option value="{{ $term->id }}" {{ $selectedTerm?->id == $term->id ?
                                                        'selected' : '' }}>
                                                        {{ $term->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <button type="submit" class="btn btn-primary mb-0 m-1">
                                                <i class="fas fa-filter"></i> Apply Filter
                                            </button>

                                            @if(request()->has('session_id') || request()->has('term_id'))
                                            <a href="{{ route('results.selectClassForPrint', ['section_id' => $section->id, 'class_id' => $class->id]) }}"
                                                class="btn btn-outline-secondary mb-0 m-1">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                            @endif

                                            @if($selectedSession && $selectedTerm)
                                            <a href="{{ route('results.masterList', $class->id) }}?session_id={{ $selectedSession->id }}&term_id={{ $selectedTerm->id }}"
                                                class="btn btn-success mb-0 m-1">
                                                <i class="fas fa-table"></i> Master List
                                            </a>
                                            @endif
                                        </form>
                                    </div>
                                </div>

                                <!-- Current Selection Info -->
                                @if($selectedSession && $selectedTerm)
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Currently viewing results for:</strong>
                                    {{ $selectedSession->name }} — {{ $selectedTerm->name }}
                                </div>
                                @else
                                <div class="alert alert-warning mb-4">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Please select a Session and Term to view results.
                                </div>
                                @endif

                                <!-- Students Table -->
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
                                                <!-- Inside the Actions column -->
                                                <td>
                                                    @if($selectedSession && $selectedTerm)
                                                    <!-- Only Preview (streams in browser/tab) -->
                                                    <a href="{{ route('results.printStudent', [$student->id, 'stream']) }}?session_id={{ $selectedSession->id }}&term_id={{ $selectedTerm->id }}"
                                                        class="btn btn-sm btn-info" title="Preview Report Card"
                                                        target="_blank">
                                                        <i class="fas fa-eye"></i> Preview Only
                                                    </a>

                                                    <!-- Optional: Add Remarks button if needed -->
                                                    <a href="{{ route('results.remarks.edit', $student->id) }}?session_id={{ $selectedSession->id }}&term_id={{ $selectedTerm->id }}"
                                                        class="btn btn-sm btn-warning ml-1"
                                                        title="Edit Skills & Remarks">
                                                        <i class="fas fa-comment-dots"></i> Remarks
                                                    </a>
                                                    @else
                                                    <span class="text-muted">Select session & term first</span>
                                                    @endif
                                                </td>

                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    No students found in this class.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination (preserves filters) -->
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