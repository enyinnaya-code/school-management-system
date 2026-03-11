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
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-clipboard-check mr-2"></i>
                                    Upload Results — {{ $student->name }}
                                </h4>
                                <p class="mb-0 text-muted">
                                    {{ $class->name }} &bull; {{ $section->section_name ?? '' }} &bull;
                                    <strong>{{ $currentSession->name }}</strong> &mdash;
                                    <strong>{{ $currentTerm->name }}</strong>
                                </p>
                            </div>
                        </div>

                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST"
                                  action="{{ route('student.results.save', ['studentId' => $student->id]) }}">
                                @csrf

                                <input type="hidden" name="session_id" value="{{ $currentSession->id }}">
                                <input type="hidden" name="term_id" value="{{ $currentTerm->id }}">

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle" id="resultsTable">
                                        <thead>
                                            <tr>
                                                <th style="min-width:200px">Subject</th>
                                                <th class="text-center" style="min-width:80px">1st CA</th>
                                                <th class="text-center" style="min-width:80px">2nd CA</th>
                                                <th class="text-center" style="min-width:80px">Mid-Term</th>
                                                <th class="text-center" style="min-width:80px">Exam</th>
                                                <th class="text-center" style="min-width:70px">Total</th>
                                                <th class="text-center" style="min-width:60px">Grade</th>
                                                <th style="min-width:160px">Comment</th>
                                            </tr>
                                            {{-- Marks Obtainable Row --}}
                                            <tr class="table-secondary text-center small font-weight-bold">
                                                <td class="text-left text-muted" style="font-size:0.78rem;">Marks Obtainable</td>
                                                <td><span class="badge badge-secondary">10</span></td>
                                                <td><span class="badge badge-secondary">10</span></td>
                                                <td><span class="badge badge-secondary">20</span></td>
                                                <td><span class="badge badge-secondary">60</span></td>
                                                <td><span class="badge badge-dark">100</span></td>
                                                <td>—</td>
                                                <td>—</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjects as $subject)
                                                @php $existing = $existingResults->get($subject->id); @endphp
                                                <tr class="item-row">
                                                    <td class="font-weight-bold">{{ $subject->course_name }}</td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="10"
                                                               name="results[{{ $subject->id }}][first_ca]"
                                                               class="form-control form-control-sm score-input"
                                                               data-max="10"
                                                               value="{{ old('results.'.$subject->id.'.first_ca', $existing?->first_ca ?? '') }}"
                                                               placeholder="0–10"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="10"
                                                               name="results[{{ $subject->id }}][second_ca]"
                                                               class="form-control form-control-sm score-input"
                                                               data-max="10"
                                                               value="{{ old('results.'.$subject->id.'.second_ca', $existing?->second_ca ?? '') }}"
                                                               placeholder="0–10"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="20"
                                                               name="results[{{ $subject->id }}][mid_term_test]"
                                                               class="form-control form-control-sm score-input"
                                                               data-max="20"
                                                               value="{{ old('results.'.$subject->id.'.mid_term_test', $existing?->mid_term_test ?? '') }}"
                                                               placeholder="0–20"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="60"
                                                               name="results[{{ $subject->id }}][examination]"
                                                               class="form-control form-control-sm score-input"
                                                               data-max="60"
                                                               value="{{ old('results.'.$subject->id.'.examination', $existing?->examination ?? '') }}"
                                                               placeholder="0–60"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        <span class="live-total">{{ $existing?->total ?? '—' }}</span>
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        <span class="live-grade">{{ $existing?->grade ?? '—' }}</span>
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                               name="results[{{ $subject->id }}][comment]"
                                                               class="form-control form-control-sm"
                                                               value="{{ old('results.'.$subject->id.'.comment', $existing?->comment ?? '') }}"
                                                               placeholder="Optional comment">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save mr-1"></i> Save Results
                                    </button>
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

<style>
#resultsTable td, #resultsTable th { vertical-align: middle; }
.item-row:hover { background: #f0f7ff; }
.score-input.is-invalid { border-color: #dc3545; }
.live-total { font-size: 1rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function calculateGrade(total) {
        if (total === null || total === '') return '—';
        if (total >= 70) return 'A';
        if (total >= 60) return 'B';
        if (total >= 50) return 'C';
        if (total >= 45) return 'D';
        if (total >= 40) return 'E';
        return 'F';
    }

    function recalculateRow(row) {
        const inputs = row.querySelectorAll('.score-input');
        let total = null;
        let allEmpty = true;

        inputs.forEach(function (input) {
            const val = input.value.trim();
            if (val !== '') {
                allEmpty = false;
                const num = parseFloat(val);
                const max = parseFloat(input.dataset.max);

                // Clamp value to its max
                if (num > max) {
                    input.value = max;
                }

                total = (total === null ? 0 : total) + parseFloat(input.value);
            }

            // Inline validation highlight
            if (val !== '') {
                const num2 = parseFloat(val);
                const max2 = parseFloat(input.dataset.max);
                input.classList.toggle('is-invalid', num2 > max2 || num2 < 0);
            } else {
                input.classList.remove('is-invalid');
            }
        });

        const totalSpan = row.querySelector('.live-total');
        const gradeSpan = row.querySelector('.live-grade');

        if (allEmpty) {
            totalSpan.textContent = '—';
            gradeSpan.textContent = '—';
        } else {
            const rounded = Math.round(total * 100) / 100;
            totalSpan.textContent = rounded;
            gradeSpan.textContent = calculateGrade(rounded);
        }
    }

    // Attach listeners to all score inputs
    document.querySelectorAll('#resultsTable .item-row').forEach(function (row) {
        // Recalculate on page load for pre-filled rows
        recalculateRow(row);

        row.querySelectorAll('.score-input').forEach(function (input) {
            input.addEventListener('input', function () {
                recalculateRow(row);
            });
        });
    });
});
</script>
</body>