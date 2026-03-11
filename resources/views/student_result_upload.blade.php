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
                                                               placeholder="-"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="10"
                                                               name="results[{{ $subject->id }}][second_ca]"
                                                               class="form-control form-control-sm score-input"
                                                               data-max="10"
                                                               value="{{ old('results.'.$subject->id.'.second_ca', $existing?->second_ca ?? '') }}"
                                                               placeholder="-"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="20"
                                                               name="results[{{ $subject->id }}][mid_term_test]"
                                                               class="form-control form-control-sm score-input"
                                                               data-max="20"
                                                               value="{{ old('results.'.$subject->id.'.mid_term_test', $existing?->mid_term_test ?? '') }}"
                                                               placeholder="-"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="60"
                                                               name="results[{{ $subject->id }}][examination]"
                                                               class="form-control form-control-sm score-input"
                                                               data-max="60"
                                                               value="{{ old('results.'.$subject->id.'.examination', $existing?->examination ?? '') }}"
                                                               placeholder="-"
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

                            {{-- ── Grading Key ── --}}
                            <div class="mt-4">
                                <h6 class="font-weight-bold text-muted mb-2">
                                    <i class="fas fa-info-circle mr-1"></i> Grading Key
                                </h6>
                                <table class="table table-sm table-bordered grading-key-table" style="max-width: 420px;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">Grade</th>
                                            <th class="text-center">Score Range</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center"><span class="badge badge-success px-2">A</span></td>
                                            <td class="text-center">70 – 100</td>
                                            <td>Excellent</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span class="badge badge-primary px-2">B</span></td>
                                            <td class="text-center">60 – 69</td>
                                            <td>Very Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span class="badge badge-info px-2">C</span></td>
                                            <td class="text-center">50 – 59</td>
                                            <td>Good</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span class="badge badge-warning px-2">D</span></td>
                                            <td class="text-center">45 – 49</td>
                                            <td>Pass</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span class="badge badge-secondary px-2">E</span></td>
                                            <td class="text-center">40 – 44</td>
                                            <td>Below Average</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><span class="badge badge-danger px-2">F</span></td>
                                            <td class="text-center">0 – 39</td>
                                            <td>Fail</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

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
.grading-key-table td, .grading-key-table th { vertical-align: middle; }
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

    function gradeClass(grade) {
        const map = { 'A': 'text-success', 'B': 'text-primary', 'C': 'text-info', 'D': 'text-warning', 'E': 'text-secondary', 'F': 'text-danger' };
        return map[grade] || '';
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

        // Clear previous grade colour classes
        gradeSpan.className = 'live-grade';

        if (allEmpty) {
            totalSpan.textContent = '—';
            gradeSpan.textContent = '—';
        } else {
            const rounded = Math.round(total * 100) / 100;
            const grade   = calculateGrade(rounded);
            totalSpan.textContent = rounded;
            gradeSpan.textContent = grade;
            gradeSpan.classList.add(gradeClass(grade));
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