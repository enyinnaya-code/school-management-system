{{-- ============================================ --}}
{{-- FILE: resources/views/exam_questions/index.blade.php --}}
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
                                        <h4><i class="fas fa-edit"></i> My Exam Questions</h4>
                                        <div class="card-header-action">
                                            <a href="{{ route('exam_questions.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Create New Exam
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        {{-- Filters --}}
                                        <form method="GET" action="{{ route('exam_questions.index') }}" class="mb-4">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="">All Status</option>
                                                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Subject</label>
                                                        <select name="subject_id" class="form-control">
                                                            <option value="">All Subjects</option>
                                                            @foreach($subjects as $subject)
                                                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                                    {{ $subject->course_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Class</label>
                                                        <select name="class_id" class="form-control">
                                                            <option value="">All Classes</option>
                                                            @foreach($classes as $class)
                                                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                                    {{ $class->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="submit" class="btn btn-primary btn-block">
                                                            <i class="fas fa-filter"></i> Filter
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                        @if(session('success'))
                                            <div class="alert alert-success alert-dismissible fade show">
                                                {{ session('success') }}
                                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                            </div>
                                        @endif

                                        @if($exams->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Exam Title</th>
                                                            <th>Subject</th>
                                                            <th>Class</th>
                                                            <th>Term</th>
                                                            <th>Type</th>
                                                            <th>Marks</th>
                                                            <th>Date</th>
                                                            <th>Status</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($exams as $exam)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $exam->exam_title }}</strong>
                                                                </td>
                                                                <td>{{ $exam->subject->course_name }}</td>
                                                                <td>{{ $exam->schoolClass->name }}</td>
                                                                <td>{{ $exam->term->name }}</td>
                                                                <td>
                                                                    <span class="badge badge-info">{{ $exam->exam_type }}</span>
                                                                </td>
                                                                <td>{{ $exam->total_marks }}</td>
                                                                <td>{{ $exam->formatted_exam_date }}</td>
                                                                <td>
                                                                    @if($exam->status == 'draft')
                                                                        <span class="badge badge-warning">Draft</span>
                                                                    @elseif($exam->status == 'published')
                                                                        <span class="badge badge-success">Published</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">Archived</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group" style="display: flex; gap:0.5rem">
                                                                        <a href="{{ route('exam_questions.show', $exam->id) }}" 
                                                                           class="btn btn-sm btn-info" title="View">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                        <a href="{{ route('exam_questions.edit', $exam->id) }}" 
                                                                           class="btn btn-sm btn-primary" title="Edit">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                        <a href="{{ route('exam_questions.print', $exam->id) }}" 
                                                                           class="btn btn-sm btn-success" title="Print" target="_blank">
                                                                            <i class="fas fa-print"></i>
                                                                        </a>
                                                                        <a href="{{ route('exam_questions.duplicate', $exam->id) }}" 
                                                                           class="btn btn-sm btn-secondary" title="Duplicate">
                                                                            <i class="fas fa-copy"></i>
                                                                        </a>
                                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                                onclick="confirmDelete({{ $exam->id }})" title="Delete">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                    
                                                                    <form id="delete-form-{{ $exam->id }}" 
                                                                          action="{{ route('exam_questions.destroy', $exam->id) }}" 
                                                                          method="POST" style="display: none;">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="mt-3">
                                                {{ $exams->links() }}
                                            </div>
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> No exams found. 
                                                <a href="{{ route('exam_questions.create') }}">Create your first exam</a>
                                            </div>
                                        @endif
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
        function confirmDelete(examId) {
            if (confirm('Are you sure you want to delete this exam? This action cannot be undone.')) {
                document.getElementById('delete-form-' + examId).submit();
            }
        }
    </script>

    @include('includes.edit_footer')
</body>
</html>