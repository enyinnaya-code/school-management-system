@include('includes.head')

<style>
    .analysis-stat {
        border-radius: 10px; padding: 15px;
        text-align: center; color: white;
    }
    .analysis-stat h3 { font-size: 1.8rem; font-weight: 700; margin: 0; }
    .analysis-stat p  { margin: 0; font-size: 12px; opacity: 0.9; }
    .question-card {
        border: 1px solid #dee2e6; border-radius: 8px;
        padding: 15px; margin-bottom: 15px;
    }
    .question-card.correct { border-left: 5px solid #28a745; background: #f8fff9; }
    .question-card.wrong   { border-left: 5px solid #dc3545; background: #fff8f8; }
    .question-card.skipped { border-left: 5px solid #ffc107; background: #fffdf0; }
    .option-item { padding: 6px 12px; border-radius: 4px; margin-bottom: 4px; }
    .option-correct { background: #d4edda; border-left: 3px solid #28a745; }
    .option-wrong   { background: #f8d7da; border-left: 3px solid #dc3545; }
    .score-circle {
        width: 120px; height: 120px; border-radius: 50%;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        font-weight: 700; margin: 0 auto; border: 6px solid;
    }
    .score-circle.pass { border-color: #28a745; color: #28a745; }
    .score-circle.fail { border-color: #dc3545; color: #dc3545; }
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

                    {{-- Header --}}
                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 font-weight-bold">
                                        In-depth Analysis: {{ $student->name }}
                                    </h5>
                                    <small class="text-muted">
                                        Test: {{ $test->test_name }} |
                                        Subject: {{ $test->course->course_name ?? '-' }}
                                    </small>
                                </div>
                                <a href="{{ route('tests.studentsPerformance', $test->id) }}"
                                   class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Performance
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">

                        {{-- Score Circle --}}
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column
                                     align-items-center justify-content-center">
                                    <div class="score-circle
                                         {{ $examRecord->is_passed ? 'pass' : 'fail' }}">
                                        <span style="font-size:1.5rem;">
                                            {{ $earnedMarks }}/{{ $totalMarks }}
                                        </span>
                                        <span style="font-size:1.1rem;">{{ $percentage }}%</span>
                                    </div>
                                    <div class="mt-3 text-center">
                                        @if($examRecord->is_passed)
                                            <span class="badge badge-success px-3 py-2"
                                                  style="font-size:14px;">PASSED</span>
                                        @else
                                            <span class="badge badge-danger px-3 py-2"
                                                  style="font-size:14px;">FAILED</span>
                                        @endif
                                        @if($position)
                                        <div class="mt-2 text-muted" style="font-size:13px;">
                                            Ranked <strong>{{ $position }}</strong>
                                            of <strong>{{ $totalTakers }}</strong>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Stats Grid --}}
                        <div class="col-md-9 mb-3">
                            <div class="row">
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="analysis-stat"
                                         style="background:linear-gradient(135deg,#28a745,#20c997);">
                                        <h3>{{ $correctCount }}</h3>
                                        <p>Correct</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="analysis-stat"
                                         style="background:linear-gradient(135deg,#dc3545,#e74c3c);">
                                        <h3>{{ $wrongCount }}</h3>
                                        <p>Wrong</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="analysis-stat"
                                         style="background:linear-gradient(135deg,#ffc107,#fd7e14);">
                                        <h3>{{ $unansweredCount }}</h3>
                                        <p>Unanswered</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="analysis-stat"
                                         style="background:linear-gradient(135deg,#007bff,#6610f2);">
                                        <h3>{{ $timeSpent ?? '-' }}</h3>
                                        <p>Time Spent</p>
                                    </div>
                                </div>

                                {{-- Progress bar breakdown --}}
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body py-2 px-3">
                                            @php
                                                $total = $correctCount + $wrongCount + $unansweredCount;
                                            @endphp
                                            <small class="text-muted d-block mb-1">
                                                Answer Breakdown
                                            </small>
                                            <div class="progress"
                                                 style="height:18px; border-radius:6px;">
                                                @if($total > 0)
                                                <div class="progress-bar bg-success"
                                                     style="width:{{ round(($correctCount/$total)*100) }}%"
                                                     title="Correct">
                                                    {{ $correctCount > 0 ? $correctCount.' Correct' : '' }}
                                                </div>
                                                <div class="progress-bar bg-danger"
                                                     style="width:{{ round(($wrongCount/$total)*100) }}%"
                                                     title="Wrong">
                                                    {{ $wrongCount > 0 ? $wrongCount.' Wrong' : '' }}
                                                </div>
                                                <div class="progress-bar bg-warning"
                                                     style="width:{{ round(($unansweredCount/$total)*100) }}%"
                                                     title="Unanswered">
                                                    {{ $unansweredCount > 0 ? $unansweredCount.' Skipped' : '' }}
                                                </div>
                                                @endif
                                            </div>
                                            <div class="mt-2 d-flex"
                                                 style="font-size:12px; gap:15px;">
                                                <span>
                                                    <i class="fas fa-circle text-success"></i> Correct
                                                </span>
                                                <span>
                                                    <i class="fas fa-circle text-danger"></i> Wrong
                                                </span>
                                                <span>
                                                    <i class="fas fa-circle text-warning"></i> Skipped
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Exam Meta --}}
                    <div class="card mb-4">
                        <div class="card-body py-2">
                            <div class="row text-center">
                                <div class="col-md-3 border-right">
                                    <small class="text-muted d-block">Start Time</small>
                                    <strong>
                                        {{ $examRecord->start_time
                                            ? \Carbon\Carbon::parse($examRecord->start_time)->format('j M Y, g:i A')
                                            : '-' }}
                                    </strong>
                                </div>
                                <div class="col-md-3 border-right">
                                    <small class="text-muted d-block">End Time</small>
                                    <strong>
                                        {{ $examRecord->end_time
                                            ? \Carbon\Carbon::parse($examRecord->end_time)->format('j M Y, g:i A')
                                            : '-' }}
                                    </strong>
                                </div>
                                <div class="col-md-3 border-right">
                                    <small class="text-muted d-block">Time Spent</small>
                                    <strong>{{ $timeSpent ?? '-' }}</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Pass Mark</small>
                                    <strong>{{ $test->pass_mark }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Per-Question Breakdown --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Question-by-Question Breakdown</h5>
                        </div>
                        <div class="card-body">
                            @php $qNum = 1; @endphp
                            @foreach($questionBreakdown as $item)
                            <div class="question-card
                                {{ !$item['is_answered'] ? 'skipped' : ($item['is_correct'] ? 'correct' : 'wrong') }}">

                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">
                                        <strong>Q{{ $qNum }}:</strong>
                                        {!! $item['question']->question !!}
                                        <small class="text-muted ml-1">
                                            [{{ $item['mark'] }} mark{{ $item['mark'] > 1 ? 's' : '' }}]
                                        </small>
                                    </h6>
                                    <div class="ml-3 flex-shrink-0">
                                        @if(!$item['is_answered'])
                                            <span class="badge badge-warning">Skipped</span>
                                        @elseif($item['is_correct'])
                                            <span class="badge badge-success">
                                                +{{ $item['mark'] }} ✓
                                            </span>
                                        @else
                                            <span class="badge badge-danger">0 ✗</span>
                                        @endif
                                    </div>
                                </div>

                                <ul class="list-unstyled mb-2">
                                    @foreach(json_decode($item['question']->options, true) as $key => $option)
                                    @php
                                        $isCorrectOpt  = strtoupper($key) === $item['correct_answer'];
                                        $isStudentPick = $item['student_answer'] !== null
                                            && strtoupper($item['student_answer']) === strtoupper($key);
                                    @endphp
                                    <li class="option-item
                                        {{ $isCorrectOpt ? 'option-correct' : '' }}
                                        {{ $isStudentPick && !$isCorrectOpt ? 'option-wrong' : '' }}">

                                        <strong>{{ $key }}:</strong> {!! nl2br(e($option)) !!}

                                        @if($isCorrectOpt && !$isStudentPick)
                                            <span class="text-success ml-1">
                                                <i class="fas fa-check"></i> Correct Answer
                                            </span>
                                        @endif

                                        @if($isStudentPick && !$isCorrectOpt)
                                            <span class="text-danger ml-1">
                                                <i class="fas fa-times"></i> Your Answer
                                            </span>
                                        @endif

                                        @if($isStudentPick && $isCorrectOpt)
                                            <span class="text-success ml-1">
                                                <i class="fas fa-check-double"></i> Your Answer (Correct)
                                            </span>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>

                                <small>
                                    @if(!$item['is_answered'])
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Not answered. Correct answer was:
                                            <strong>{{ $item['correct_answer'] }}</strong>
                                        </span>
                                    @elseif($item['is_correct'])
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i>
                                            Answered <strong>{{ strtoupper($item['student_answer']) }}</strong>
                                            — Correct
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-times-circle"></i>
                                            Answered <strong>{{ strtoupper($item['student_answer']) }}</strong>,
                                            correct was <strong>{{ $item['correct_answer'] }}</strong>
                                        </span>
                                    @endif
                                </small>
                            </div>
                            @php $qNum++; @endphp
                            @endforeach
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>
</div>
@include('includes.footer')
</body>