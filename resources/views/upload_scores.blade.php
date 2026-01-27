{{-- resources/views/upload_scores.blade.php --}}

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
                            <div class="card-header">
                                <h4>Upload Scores for {{ $student->name }} - {{ $course->course_name }} (Class {{ $student->class_id ?? '' }})</h4>
                            </div>

                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                @if(isset($existingResult))
                                    <div class="alert alert-info">
                                        Scores for this Subject has already been uploaded. You can update the scores below if needed.
                                    </div>
                                @endif

                                <!-- Scores Upload Form -->
                                <form method="POST" action="{{ route('results.saveResult') }}">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                                    <div class="form-group row px-3 mb-3">
                                        <label class="col-md-2 col-form-label">CA Score</label>
                                        <div class="col-md-6">
                                            <input type="number" class="form-control" name="ca" step="0.01" min="0" max="100" value="{{ $existingResult->ca ?? '' }}" required>
                                            @error('ca')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row px-3 mb-3">
                                        <label class="col-md-2 col-form-label">Test Score</label>
                                        <div class="col-md-6">
                                            <input type="number" class="form-control" name="test" step="0.01" min="0" max="100" value="{{ $existingResult->test ?? '' }}" required>
                                            @error('test')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row px-3 mb-3">
                                        <label class="col-md-2 col-form-label">Exam Score</label>
                                        <div class="col-md-6">
                                            <input type="number" class="form-control" name="exam" step="0.01" min="0" max="100" value="{{ $existingResult->exam ?? '' }}" required>
                                            @error('exam')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row px-3 mb-3">
                                        <label class="col-md-2 col-form-label">Comment</label>
                                        <div class="col-md-6">
                                            <textarea class="form-control" name="comment" rows="3" maxlength="500" placeholder="Optional comment...">{{ $existingResult->comment ?? '' }}</textarea>
                                            @error('comment')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group mt-4 pt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> {{ isset($existingResult) ? 'Update' : 'Save' }} Result
                                        </button>
                                        <a href="{{ route('student.result.upload', $student->id) }}" class="btn btn-secondary ml-2">
                                            <i class="fas fa-arrow-left"></i> Back to Subjects
                                        </a>
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
</body>