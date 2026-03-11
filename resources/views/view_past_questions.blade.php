@include('includes.head')

<style>
    .instruction {
        background-color: #f4f6f8;
        padding: 15px;
        border-left: 4px solid #007bff;
        border-radius: 5px;
        font-style: italic;
        margin-bottom: 20px;
    }

    .option-item {
        padding: 6px 10px;
        border-radius: 4px;
        margin-bottom: 4px;
    }

    .option-correct {
        background-color: #d4edda;
        border-left: 4px solid #28a745;
    }

    .option-wrong {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
    }

    .question-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 20px;
    }

    .question-card.correct-question {
        border-left: 5px solid #28a745;
    }

    .question-card.wrong-question {
        border-left: 5px solid #dc3545;
    }

    .question-card.not-answered {
        border-left: 5px solid #ffc107;
    }
</style>

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
                            <div class="row justify-content-between px-2">
                                <div class="p-4">
                                    <h6>Questions for Test: {{ $test->test_name }}</h6>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="question-paper">
                                    @php $questionNumber = 1; @endphp

                                    @foreach($test->questions as $question)

                                        @if($question->not_question == 1)
                                            <div class="instruction">
                                                {!! $question->question !!}
                                            </div>

                                        @else
                                            @php
                                                $studentAnswer = $studentAnswers[$question->id] ?? null;
                                                $correctAnswer = strtoupper($question->answer);
                                                $isCorrect     = $studentAnswer !== null && strtoupper($studentAnswer) === $correctAnswer;
                                                $notAnswered   = $studentAnswer === null;
                                                $mark          = $question->mark ?? 1;
                                            @endphp

                                            <div class="question-card {{ $notAnswered ? 'not-answered' : ($isCorrect ? 'correct-question' : 'wrong-question') }}">

                                                {{-- Question Header --}}
                                                <h5 class="mb-3">
                                                    <strong>Q{{ $questionNumber }}:</strong> {!! $question->question !!}
                                                    <span class="ml-2 text-dark" style="font-size: 13px;">
                                                        [{{ $mark }} Mark{{ $mark > 1 ? 's' : '' }}]
                                                    </span>

                                                    @if($notAnswered)
                                                        <span class="badge badge-warning ml-2">
                                                            <i class="fas fa-minus-circle"></i> Not Answered
                                                        </span>
                                                    @elseif($isCorrect)
                                                        <span class="badge badge-success ml-2">
                                                            <i class="fas fa-check-circle"></i> Correct
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger ml-2">
                                                            <i class="fas fa-times-circle"></i> Wrong
                                                        </span>
                                                    @endif
                                                </h5>

                                                {{-- Options --}}
                                                <div class="options mb-3">
                                                    <strong>Options:</strong>
                                                    <ul class="list-unstyled mt-2">
                                                        @foreach(json_decode($question->options, true) as $key => $option)
                                                            @php
                                                                $isThisCorrect  = strtoupper($key) === $correctAnswer;
                                                                $isStudentPick  = $studentAnswer !== null && strtoupper($studentAnswer) === strtoupper($key);
                                                            @endphp
                                                            <li class="option-item
                                                                {{ $isThisCorrect ? 'option-correct' : '' }}
                                                                {{ $isStudentPick && !$isThisCorrect ? 'option-wrong' : '' }}">

                                                                <strong>{{ $key }}:</strong> {!! nl2br(e($option)) !!}

                                                                @if($isThisCorrect)
                                                                    <span class="text-success ml-2 font-weight-bold">
                                                                        <i class="fas fa-check"></i> Correct Answer
                                                                    </span>
                                                                @endif

                                                                @if($isStudentPick && !$isThisCorrect)
                                                                    <span class="text-danger ml-2 font-weight-bold">
                                                                        <i class="fas fa-times"></i> Your Answer
                                                                    </span>
                                                                @endif

                                                                @if($isStudentPick && $isThisCorrect)
                                                                    <span class="text-success ml-2 font-weight-bold">
                                                                        <i class="fas fa-check-double"></i> Your Answer (Correct)
                                                                    </span>
                                                                @endif

                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>

                                                {{-- Summary line --}}
                                                <div class="mt-2" style="font-size: 13px;">
                                                    @if($notAnswered)
                                                        <span class="text-warning">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            You did not answer this question.
                                                            Correct answer was: <strong>{{ $correctAnswer }}</strong>
                                                        </span>
                                                    @elseif($isCorrect)
                                                        <span class="text-success">
                                                            <i class="fas fa-check-circle"></i>
                                                            You answered <strong>{{ strtoupper($studentAnswer) }}</strong> — correct! +{{ $mark }} mark{{ $mark > 1 ? 's' : '' }}
                                                        </span>
                                                    @else
                                                        <span class="text-danger">
                                                            <i class="fas fa-times-circle"></i>
                                                            You answered <strong>{{ strtoupper($studentAnswer) }}</strong>
                                                            but the correct answer was <strong>{{ $correctAnswer }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                            </div>{{-- end question-card --}}

                                            @php $questionNumber++; @endphp
                                        @endif

                                    @endforeach

                                    @if($test->questions->isEmpty())
                                        <p class="text-center">No questions available for this test.</p>
                                    @endif
                                </div>

                                <div class="text-center mt-4">
                                    <a href="{{ route('tests.past') }}" class="btn btn-primary">Back to Past Tests</a>
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