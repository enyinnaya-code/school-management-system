@php
    $examRecord = DB::table('students_exams')
        ->where('user_id', Auth::id())
        ->where('test_id', $test->id)
        ->first();
@endphp

@include('includes.head')

<body>

    {{-- ── Server-side blade guard: redirect immediately if already submitted ── --}}
    @if($examRecord && $examRecord->is_submited == 1)
    <script>
        window.location.replace("{{ route('tests.start') }}");
    </script>
    @endif

    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <!-- Timer - Now movable -->
            <div id="timer" style="position: fixed; top: 10px; right: 10px; z-index: 9999; font-size: 18px; font-weight:600; background-color: white; color: green; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); cursor: move;">
                Time Left: <span id="time-left"></span>
            </div>

            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Take Test: {{ $test->test_name }}</h4>
                            </div>

                            <div class="card-body">
                                @if($questions->count() > 0)
                                <form id="test-form" action="{{ route('tests.submit', $test->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="exhausted_time" id="exhausted_time" value="0">

                                    @php $questionNumber = 1; @endphp
                                    @foreach($questions as $question)
                                        @if($question->not_question)
                                            <div class="instruction bg-light p-3 mb-4 rounded border">
                                                <p class="mb-0">{!! $question->question !!}</p>
                                            </div>
                                        @else
                                            <div class="question mb-4">
                                                <h5><strong>Q{{ $questionNumber }}:</strong> {!! $question->question !!}</h5>
                                                <div class="options">
                                                    @foreach(json_decode($question->options, true) as $key => $option)
                                                    <div class="form-check">
                                                        <input class="form-check-input answer-option"
                                                            type="radio"
                                                            name="answers[{{ $question->id }}]"
                                                            id="q{{ $question->id }}_{{ $key }}"
                                                            value="{{ $key }}"
                                                            data-question-id="{{ $question->id }}"
                                                            data-test-id="{{ $test->id }}"
                                                            @if(isset($savedAnswers[$question->id]) && strtoupper($savedAnswers[$question->id]) === strtoupper($key)) checked @endif
                                                        >
                                                        <label class="form-check-label" for="q{{ $question->id }}_{{ $key }}">
                                                            <strong>{{ $key }}:</strong> {!! nl2br(e($option)) !!}
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @php $questionNumber++; @endphp
                                        @endif
                                    @endforeach

                                    <div class="text-center mt-4 pt-5 pb-4">
                                        <button type="button" id="submit-btn" class="btn btn-success btn-lg">Submit Test</button>
                                    </div>
                                </form>
                                @else
                                    <p class="text-center">No questions available for this test.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Test Submission</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Are you sure you want to submit your test?</strong></p>
                    <p>Once submitted, you will not be able to make any changes to your answers.</p>
                    <div id="submission-summary" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-submit" class="btn btn-success">Yes, Submit Test</button>
                </div>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.min.js"></script>

    <script>

        // ── Layer 2 JS Guard: double-check submission status on page load ────────
        // This fires even if someone views cached HTML — it hits the server fresh
        (function checkAlreadySubmitted() {
            fetch("{{ route('tests.checkSubmitted', $test->id) }}", {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.submitted) {
                    window.location.replace("{{ route('tests.start') }}");
                }
            })
            .catch(() => {}); // fail silently — server-side guard is the primary one
        })();

        // ─── Draggable Timer ──────────────────────────────────────────────────────
        const timer = document.getElementById('timer');
        let isDragging = false, offsetX, offsetY;

        timer.addEventListener('mousedown', function (e) {
            isDragging = true;
            offsetX = e.clientX - timer.getBoundingClientRect().left;
            offsetY = e.clientY - timer.getBoundingClientRect().top;
            timer.style.cursor = 'grabbing';
        });
        document.addEventListener('mousemove', function (e) {
            if (isDragging) {
                timer.style.left  = (e.clientX - offsetX) + 'px';
                timer.style.top   = (e.clientY - offsetY) + 'px';
                timer.style.right = 'auto';
            }
        });
        document.addEventListener('mouseup', function () {
            if (isDragging) { isDragging = false; timer.style.cursor = 'move'; }
        });

        // ─── Core State ───────────────────────────────────────────────────────────
        const duration         = {{ $test->duration }};
        const examStartTime    = new Date('{{ $examStartTime }}');
        const formElement      = document.getElementById('test-form');
        const warningThreshold = 10 * 60; // 10 minutes in seconds

        let isSubmitting           = false;
        let isLegitimateNavigation = false;

        // ─── Time Helpers ─────────────────────────────────────────────────────────
        function getElapsedTime() {
            return Math.floor((new Date().getTime() - examStartTime.getTime()) / 1000);
        }

        function getRemainingTime() {
            return Math.max(0, (duration * 60) - getElapsedTime());
        }

        function formatTime(seconds) {
            return String(Math.floor(seconds / 60)).padStart(2, '0')
                 + ':' + String(seconds % 60).padStart(2, '0');
        }

        // ─── Core Submit Function ─────────────────────────────────────────────────
        function submitFormWithExhaustedTime() {
            if (isSubmitting) return;
            isSubmitting           = true;
            isLegitimateNavigation = true;

            document.getElementById('exhausted_time').value = Math.floor(getElapsedTime());
            formElement.submit();
        }

        // ─── Countdown Timer ──────────────────────────────────────────────────────
        let timeLeft = getRemainingTime();
        document.getElementById('time-left').textContent = formatTime(timeLeft);
        if (timeLeft <= warningThreshold) timer.style.color = 'red';

        // If student somehow loads the page after time already expired, submit immediately
        if (timeLeft <= 0) {
            submitFormWithExhaustedTime();
        }

        const timerInterval = setInterval(function () {
            timeLeft = getRemainingTime();
            document.getElementById('time-left').textContent = formatTime(timeLeft);

            if (timeLeft <= warningThreshold) timer.style.color = 'red';

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                submitFormWithExhaustedTime();
            }
        }, 1000);

        // ─── Back / Forward Button ────────────────────────────────────────────────
        for (let i = 0; i < 20; i++) {
            history.pushState(null, null, window.location.href);
        }

        window.addEventListener('popstate', function (e) {
            if (isSubmitting || isLegitimateNavigation) return;
            history.pushState(null, null, window.location.href);
            submitFormWithExhaustedTime();
        });

        // ─── Tab Switch / Minimize Window ────────────────────────────────────────
        document.addEventListener('visibilitychange', function () {
            if (document.hidden && !isSubmitting && !isLegitimateNavigation) {
                submitFormWithExhaustedTime();
            }
        });

        // ─── Page Unload: closing tab, typing new URL, external link ─────────────
        window.addEventListener('beforeunload', function (e) {
            if (isLegitimateNavigation || isSubmitting) return;

            // sendBeacon fires reliably even while the page is tearing down
            const exhaustedTime = Math.floor(getElapsedTime());
            const formData = new FormData(formElement);
            formData.set('exhausted_time', exhaustedTime);
            navigator.sendBeacon(formElement.action, formData);

            // Show native browser dialog as a last-resort warning
            e.preventDefault();
            e.returnValue = '';
        });

        // ─── Disable Dev Tools & Right-Click ─────────────────────────────────────
        document.addEventListener('contextmenu', e => e.preventDefault());

        document.addEventListener('keydown', function (e) {
            if (e.keyCode === 123)                              { e.preventDefault(); return false; } // F12
            if (e.ctrlKey && e.shiftKey && e.keyCode === 73)   { e.preventDefault(); return false; } // Ctrl+Shift+I
            if (e.ctrlKey && e.keyCode === 85)                  { e.preventDefault(); return false; } // Ctrl+U
            if (e.ctrlKey && e.shiftKey && e.keyCode === 67)   { e.preventDefault(); return false; } // Ctrl+Shift+C
            if (e.altKey  && e.keyCode === 115)                 { e.preventDefault(); return false; } // Alt+F4
        });

        // ─── Mark Legitimate Form Submits ─────────────────────────────────────────
        formElement.addEventListener('submit', function () {
            isLegitimateNavigation = true;
        });

        // ─── Background Answer Save (auto-save on selection) ─────────────────────
        document.querySelectorAll('.answer-option').forEach(function (radio) {
            radio.addEventListener('change', function () {
                const questionId      = this.dataset.questionId;
                const testId          = this.dataset.testId;
                const answer          = this.value;
                const questionElement = this.closest('.question');

                if (!questionElement.querySelector('.save-indicator')) {
                    const indicator = document.createElement('div');
                    indicator.className = 'save-indicator';
                    indicator.style.cssText = 'position:absolute;right:10px;top:5px;display:inline-block;width:10px;height:10px;border-radius:50%;background-color:#ccc;transition:background-color 0.5s;';
                    questionElement.style.position = 'relative';
                    questionElement.appendChild(indicator);
                }

                const indicator = questionElement.querySelector('.save-indicator');
                indicator.style.backgroundColor = '#ffcc00'; // saving — yellow

                fetch("{{ route('tests.saveAnswer') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ test_id: testId, question_id: questionId, answer: answer })
                })
                .then(r => r.json())
                .then(data => {
                    indicator.style.backgroundColor = data.status === 'saved' ? '#4CAF50' : '#f44336';
                    setTimeout(() => indicator.style.backgroundColor = 'transparent', 2000);
                })
                .catch(() => {
                    indicator.style.backgroundColor = '#f44336'; // error — red
                });
            });
        });

        // ─── Manual Submit Button ─────────────────────────────────────────────────
        document.getElementById('submit-btn').addEventListener('click', function () {
            const totalQuestions      = document.querySelectorAll('.question').length;
            const answeredQuestions   = document.querySelectorAll('input[type="radio"]:checked').length;
            const unansweredQuestions = totalQuestions - answeredQuestions;

            document.getElementById('submission-summary').innerHTML = `
                <div class="alert alert-info">
                    <strong>Submission Summary:</strong><br>
                    Total Questions: ${totalQuestions}<br>
                    Answered: ${answeredQuestions}<br>
                    Unanswered: ${unansweredQuestions}
                </div>
                ${unansweredQuestions > 0
                    ? `<div class="alert alert-warning">
                            <strong>Warning:</strong> You have ${unansweredQuestions} unanswered question(s).
                       </div>`
                    : '<div class="alert alert-success">All questions answered. Ready to submit!</div>'
                }
            `;

            $('#confirmationModal').modal('show');
        });

        document.getElementById('confirm-submit').addEventListener('click', function () {
            $('#confirmationModal').modal('hide');
            submitFormWithExhaustedTime();
        });

    </script>
</body>