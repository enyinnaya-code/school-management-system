{{-- ============================================ --}}
{{-- FILE: resources/views/exam_questions/show.blade.php --}}
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
                                        <h4><i class="fas fa-eye"></i> View Exam Question Paper</h4>
                                        <div class="card-header-action">
                                            <a href="{{ route('exam_questions.edit', $exam->id) }}" class="btn btn-primary mr-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="{{ route('exam_questions.print', $exam->id) }}" class="btn btn-success mr-2" target="_blank">
                                                <i class="fas fa-print"></i> Print
                                            </a>
                                            <a href="{{ route('exam_questions.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Back
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(session('success'))
                                            <div class="alert alert-success alert-dismissible fade show">
                                                {{ session('success') }}
                                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                            </div>
                                        @endif

                                        {{-- Basic Information --}}
                                        <div class="card mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Exam Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <table class="table table-borderless">
                                                            <tr>
                                                                <th width="200">Exam Title:</th>
                                                                <td><strong>{{ $exam->exam_title }}</strong></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Subject:</th>
                                                                <td>{{ $exam->subject->course_name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Class:</th>
                                                                <td>{{ $exam->schoolClass->name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Section:</th>
                                                                <td>{{ $exam->section->section_name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Exam Type:</th>
                                                                <td><span class="badge badge-info">{{ $exam->exam_type }}</span></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <table class="table table-borderless">
                                                            <tr>
                                                                <th width="200">Session:</th>
                                                                <td>{{ $exam->session->name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Term:</th>
                                                                <td>{{ $exam->term->name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Exam Date:</th>
                                                                <td>{{ $exam->formatted_exam_date }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Duration:</th>
                                                                <td>{{ $exam->duration_formatted }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Total Marks:</th>
                                                                <td><strong class="text-success">{{ $exam->total_marks }} marks</strong></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Status:</th>
                                                                <td>
                                                                    @if($exam->status == 'draft')
                                                                        <span class="badge badge-warning">Draft</span>
                                                                    @elseif($exam->status == 'published')
                                                                        <span class="badge badge-success">Published</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">Archived</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                @if($exam->instructions)
                                                    <div class="mt-3">
                                                        <strong>General Instructions:</strong>
                                                        <div class="alert alert-light mt-2">
                                                            {!! nl2br(e($exam->instructions)) !!}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Exam Sections & Questions --}}
                                        <div class="card mb-4">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0"><i class="fas fa-list"></i> Exam Questions</h6>
                                            </div>
                                            <div class="card-body">
                                                @if($exam->sections)
                                                    @foreach($exam->sections as $sectionIndex => $section)
                                                        <div class="card mb-3 border-success">
                                                            <div class="card-header bg-light">
                                                                <h6 class="mb-0">
                                                                    <i class="fas fa-folder"></i> 
                                                                    {{ isset($section['title']) && $section['title'] ? $section['title'] : 'Section ' . $sectionIndex }}
                                                                </h6>
                                                            </div>
                                                            <div class="card-body">
                                                                @if(isset($section['instructions']) && $section['instructions'])
                                                                    <div class="alert alert-info">
                                                                        <strong>Instructions:</strong> {{ $section['instructions'] }}
                                                                    </div>
                                                                @endif

                                                                @if(isset($section['questions']))
                                                                    @foreach($section['questions'] as $questionIndex => $question)
                                                                        <div class="question-item mb-3 p-3" style="background: #f8f9fa; border-left: 4px solid #28a745;">
                                                                            <div class="mb-2">
                                                                                <strong>Question {{ $questionIndex }}:</strong>
                                                                                @if(isset($question['marks']))
                                                                                    <span class="badge badge-primary float-right">{{ $question['marks'] }} marks</span>
                                                                                @endif
                                                                            </div>
                                                                            <div class="mb-2">{{ $question['text'] }}</div>
                                                                            
                                                                            @if(isset($question['type']))
                                                                                <div class="mb-2">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-tag"></i> Type: 
                                                                                        <span class="badge badge-secondary">{{ ucwords(str_replace('_', ' ', $question['type'])) }}</span>
                                                                                    </small>
                                                                                </div>
                                                                            @endif

                                                                            @if(isset($question['type']) && $question['type'] == 'multiple_choice' && isset($question['options']))
                                                                                <div class="ml-3 mt-2">
                                                                                    @foreach($question['options'] as $optionIndex => $option)
                                                                                        @if($option)
                                                                                            <div class="mb-1">
                                                                                                <strong>{{ chr(65 + $optionIndex) }}.</strong> {{ $option }}
                                                                                            </div>
                                                                                        @endif
                                                                                    @endforeach
                                                                                </div>
                                                                            @endif

                                                                            @if(isset($question['answer']) && $question['answer'])
                                                                                <div class="mt-2 p-2" style="background: #fff3cd; border-left: 3px solid #ffc107;">
                                                                                    <small><strong>Answer/Notes:</strong></small><br>
                                                                                    <small>{{ $question['answer'] }}</small>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="alert alert-warning">
                                                        No questions have been added to this exam yet.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Marking Scheme --}}
                                        @if($exam->marking_scheme)
                                            <div class="card mb-4">
                                                <div class="card-header bg-warning text-white">
                                                    <h6 class="mb-0"><i class="fas fa-check-square"></i> Marking Scheme</h6>
                                                </div>
                                                <div class="card-body">
                                                    {!! nl2br(e($exam->marking_scheme)) !!}
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Private Notes --}}
                                        @if($exam->notes)
                                            <div class="card mb-4">
                                                <div class="card-header bg-secondary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Private Notes</h6>
                                                </div>
                                                <div class="card-body">
                                                    {!! nl2br(e($exam->notes)) !!}
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Metadata --}}
                                        <div class="card">
                                            <div class="card-body">
                                                <small class="text-muted">
                                                    <strong>Created by:</strong> {{ $exam->creator->name }} | 
                                                    <strong>Created on:</strong> {{ $exam->created_at->format('F j, Y g:i A') }}
                                                    @if($exam->updated_at != $exam->created_at)
                                                        | <strong>Last updated:</strong> {{ $exam->updated_at->format('F j, Y g:i A') }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
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
</html>s