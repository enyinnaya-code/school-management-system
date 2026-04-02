@include('includes.head')

<body>
<div class="loader"></div>
<div id="app">
    <div class="main-wrapper main-wrapper-1">
        <div class="navbar-bg"></div>
        @include('includes.right_top_nav')
        @include('includes.side_nav')

        <div class="main-content pt-5 mt-5">
            <section class="section mb-5 pb-5 px-0">
                <div class="col-12">

                    {{-- Page Header --}}
                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-child text-primary mr-2"></i>
                                        Primary School Result Upload
                                    </h5>
                                    <small class="text-muted">
                                        <strong>{{ $student->name }}</strong> &mdash;
                                        {{ $class->name }} &mdash;
                                        {{ $section->section_name ?? '' }} &mdash;
                                        {{ $currentSession->name }} /
                                        {{ $currentTerm->name }}
                                    </small>
                                </div>

                                {{-- Navigation buttons --}}
                                <div class="d-flex align-items-center flex-wrap" style="gap:6px;">
                                    @if($prevStudent ?? null)
                                        <a href="{{ route('student.result.upload', $prevStudent->id) }}"
                                           class="btn btn-outline-primary btn-sm"
                                           title="Go to {{ $prevStudent->name }}">
                                            <i class="fas fa-chevron-left mr-1"></i>
                                            <span class="d-none d-md-inline">{{ $prevStudent->name }}</span>
                                            <span class="d-md-none">Prev</span>
                                        </a>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="fas fa-chevron-left mr-1"></i> Prev
                                        </button>
                                    @endif

                                    @if(isset($studentPosition) && isset($totalStudents))
                                        <small class="text-muted px-1">
                                            {{ $studentPosition }} / {{ $totalStudents }}
                                        </small>
                                    @endif

                                    @if($nextStudent ?? null)
                                        <a href="{{ route('student.result.upload', $nextStudent->id) }}"
                                           class="btn btn-primary btn-sm"
                                           title="Go to {{ $nextStudent->name }}">
                                            <span class="d-none d-md-inline">{{ $nextStudent->name }}</span>
                                            <span class="d-md-none">Next</span>
                                            <i class="fas fa-chevron-right ml-1"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            Next <i class="fas fa-chevron-right ml-1"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('results.savePrimaryStudent', $student->id) }}"
                          id="primaryResultForm">
                        @csrf

                        {{-- ══════════════════════════════════════════════ --}}
                        {{-- SECTION: COGNITIVE ABILITY                    --}}
                        {{-- ══════════════════════════════════════════════ --}}
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="section-title mb-2">
                                    <i class="fas fa-brain mr-2"></i> Cognitive Ability
                                </div>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-info-circle"></i>
                                    Enter <strong>Marks Obtained</strong> for each half per subject.
                                    Obtainable marks are fixed: <span class="badge badge-info">1st Half = 30</span>
                                    <span class="badge badge-success">2nd Half = 70</span>
                                    <span class="badge badge-warning text-dark">Final = 100</span>.
                                    Totals and grades are calculated automatically in real time.
                                    Leave a row completely blank to skip that subject.
                                </p>

                                {{-- Score Legend --}}
                                <div class="d-flex flex-wrap mb-3" style="gap:8px;">
                                    <span class="badge badge-info px-3 py-2" style="font-size:13px;">
                                        <i class="fas fa-lock mr-1"></i> 1st Half Max: 30
                                    </span>
                                    <span class="badge badge-success px-3 py-2" style="font-size:13px;">
                                        <i class="fas fa-lock mr-1"></i> 2nd Half Max: 70
                                    </span>
                                    <span class="badge badge-warning text-dark px-3 py-2" style="font-size:13px;">
                                        <i class="fas fa-lock mr-1"></i> Final Max: 100
                                    </span>
                                    <span class="badge badge-secondary px-3 py-2" style="font-size:13px;">
                                        <i class="fas fa-calculator mr-1"></i> Total = 1st + 2nd Half
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered subject-table" id="cognitiveTable">
                                        <thead>
                                            <tr>
                                                <th rowspan="2"
                                                    class="align-middle text-center"
                                                    style="background:#f8f9fa; min-width:160px;">
                                                    Subject
                                                </th>
                                                <th colspan="2"
                                                    class="text-center"
                                                    style="background:#dbeafe;">
                                                    1st Half
                                                    <small class="d-block text-muted font-weight-normal">(Max: 30)</small>
                                                </th>
                                                <th colspan="2"
                                                    class="text-center"
                                                    style="background:#dcfce7;">
                                                    2nd Half
                                                    <small class="d-block text-muted font-weight-normal">(Max: 70)</small>
                                                </th>
                                                <th colspan="2"
                                                    class="text-center"
                                                    style="background:#fef9c3;">
                                                    Final Result
                                                    <small class="d-block text-muted font-weight-normal">(Max: 100)</small>
                                                </th>
                                                <th class="text-center align-middle"
                                                    style="background:#f3e8ff; min-width:90px;">
                                                    Total
                                                    <small class="d-block text-muted font-weight-normal">(Live)</small>
                                                </th>
                                                <th rowspan="2"
                                                    class="text-center align-middle"
                                                    style="background:#ede9fe; min-width:70px;">
                                                    Grade
                                                </th>
                                                <th class="text-center align-middle"
                                                    style="background:#fce7f3; min-width:120px;">
                                                    Remarks
                                                </th>
                                            </tr>
                                            <tr>
                                                {{-- 1st Half --}}
                                                <th style="background:#eff6ff; font-size:12px;">Obtainable</th>
                                                <th style="background:#eff6ff; font-size:12px;">Obtained</th>
                                                {{-- 2nd Half --}}
                                                <th style="background:#f0fdf4; font-size:12px;">Obtainable</th>
                                                <th style="background:#f0fdf4; font-size:12px;">Obtained</th>
                                                {{-- Final --}}
                                                <th style="background:#fefce8; font-size:12px;">Obtainable</th>
                                                <th style="background:#fefce8; font-size:12px;">Obtained</th>
                                                {{-- Empty th for Total & Remarks (Grade uses rowspan="2") --}}
                                                <th style="background:#f5f3ff;"></th>
                                                <th style="background:#fdf2f8;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($subjects as $subject)
                                            @php $r = $existingResults->get($subject->id); @endphp
                                            <tr class="result-row">
                                                <td class="subject-name align-middle font-weight-bold">
                                                    {{ $subject->course_name }}
                                                </td>

                                                {{-- 1st Half Obtainable (fixed = 30) --}}
                                                <td style="background:#f8fbff;" class="text-center align-middle">
                                                    <input type="hidden"
                                                           name="results[{{ $subject->id }}][first_half_obtainable]"
                                                           value="30">
                                                    <span class="badge badge-info px-2 py-1" style="font-size:13px;">30</span>
                                                </td>

                                                {{-- 1st Half Obtained --}}
                                                <td style="background:#f8fbff;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][first_half_obtained]"
                                                           class="form-control form-control-sm score-input first-half-input"
                                                           data-max="30"
                                                           data-course="{{ $subject->id }}"
                                                           data-half="first"
                                                           value="{{ $r?->first_half_obtained ?? '' }}"
                                                           min="0" max="30" step="0.5"
                                                           placeholder="0–30">
                                                </td>

                                                {{-- 2nd Half Obtainable (fixed = 70) --}}
                                                <td style="background:#f8fdf9;" class="text-center align-middle">
                                                    <input type="hidden"
                                                           name="results[{{ $subject->id }}][second_half_obtainable]"
                                                           value="70">
                                                    <span class="badge badge-success px-2 py-1" style="font-size:13px;">70</span>
                                                </td>

                                                {{-- 2nd Half Obtained --}}
                                                <td style="background:#f8fdf9;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][second_half_obtained]"
                                                           class="form-control form-control-sm score-input second-half-input"
                                                           data-max="70"
                                                           data-course="{{ $subject->id }}"
                                                           data-half="second"
                                                           value="{{ $r?->second_half_obtained ?? '' }}"
                                                           min="0" max="70" step="0.5"
                                                           placeholder="0–70">
                                                </td>

                                                {{-- Final Obtainable (fixed = 100) --}}
                                                <td style="background:#fffef5;" class="text-center align-middle">
                                                    <input type="hidden"
                                                           name="results[{{ $subject->id }}][final_obtainable]"
                                                           value="100">
                                                    <span class="badge badge-warning text-dark px-2 py-1" style="font-size:13px;">100</span>
                                                </td>

                                                {{-- Final Obtained (auto-filled, readonly) --}}
                                                <td style="background:#fffef5;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][final_obtained]"
                                                           class="form-control form-control-sm final-obtained-input"
                                                           data-max="100"
                                                           data-course="{{ $subject->id }}"
                                                           value="{{ $r?->final_obtained ?? '' }}"
                                                           min="0" max="100" step="0.5"
                                                           placeholder="Auto"
                                                           readonly
                                                           style="background:#fffde7; cursor:not-allowed; font-weight:600;">
                                                </td>

                                                {{-- Live Total --}}
                                                <td style="background:#f5f3ff;" class="text-center align-middle">
                                                    <strong class="live-total text-purple"
                                                            data-course="{{ $subject->id }}"
                                                            style="font-size:17px; color:#7c3aed;">
                                                        {{ $r && ($r->first_half_obtained !== null || $r->second_half_obtained !== null)
                                                            ? number_format(($r->first_half_obtained ?? 0) + ($r->second_half_obtained ?? 0), 1)
                                                            : '—' }}
                                                    </strong>
                                                </td>

                                                {{-- Live Grade --}}
                                                <td style="background:#f5f3ff;" class="text-center align-middle">
                                                    <input type="hidden"
                                                           name="results[{{ $subject->id }}][grade]"
                                                           class="grade-hidden-input"
                                                           value="{{ $r?->grade ?? '' }}">
                                                    <strong class="live-grade" style="font-size:15px;">
                                                        {{ $r?->grade ?? '—' }}
                                                    </strong>
                                                </td>

                                                {{-- Remarks --}}
                                                <td style="background:#fdf2f8;">
                                                    <input type="text"
                                                           name="results[{{ $subject->id }}][teacher_remark]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $r?->teacher_remark ?? '' }}"
                                                           placeholder="e.g. Good"
                                                           maxlength="50">
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">
                                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                                    No subjects assigned to this class yet.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Grading Key --}}
                                <div class="mt-4">
                                    <h6 class="font-weight-bold text-muted mb-2">
                                        <i class="fas fa-info-circle mr-1"></i> Grading Key
                                    </h6>
                                    <table class="table table-sm table-bordered" style="max-width:420px;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">Grade</th>
                                                <th class="text-center">Score Range</th>
                                                <th>Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td class="text-center"><span class="badge badge-success px-2">A</span></td><td class="text-center">70 – 100</td><td>Excellent</td></tr>
                                            <tr><td class="text-center"><span class="badge badge-primary px-2">B</span></td><td class="text-center">60 – 69</td><td>Very Good</td></tr>
                                            <tr><td class="text-center"><span class="badge badge-info px-2">C</span></td><td class="text-center">50 – 59</td><td>Good</td></tr>
                                            <tr><td class="text-center"><span class="badge badge-warning px-2">D</span></td><td class="text-center">45 – 49</td><td>Pass</td></tr>
                                            <tr><td class="text-center"><span class="badge badge-secondary px-2">E</span></td><td class="text-center">40 – 44</td><td>Below Average</td></tr>
                                            <tr><td class="text-center"><span class="badge badge-danger px-2">F</span></td><td class="text-center">0 – 39</td><td>Fail</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="card">
                            <div class="card-body d-flex align-items-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-save mr-2"></i> Save Results
                                </button>
                                <a href="{{ url()->previous() }}"
                                   class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-times mr-2"></i> Cancel
                                </a>
                                <small class="text-muted ml-4">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Final = 1st Half + 2nd Half (calculated automatically).
                                    Blank rows are skipped.
                                </small>
                            </div>
                        </div>

                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     STICKY BOTTOM NAVIGATION BAR
══════════════════════════════════════════════════════════════════════════ --}}
<div id="stickyNav" style="
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: #ffffff;
    border-top: 2px solid #dee2e6;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 1040;
    box-shadow: 0 -3px 10px rgba(0,0,0,.10);
">
    {{-- Left: placeholder --}}
    <div></div>

    {{-- Centre: student position indicator --}}
    @if(isset($studentPosition) && isset($totalStudents))
        <small class="text-muted d-none d-md-block">
            <i class="fas fa-user mr-1"></i>
            {{ $student->name }}
            &nbsp;&bull;&nbsp;
            {{ $studentPosition }} / {{ $totalStudents }}
        </small>
    @endif

    {{-- Right: prev / next --}}
    <div style="display:flex; gap:8px;">
        @if($prevStudent ?? null)
            <a href="{{ route('student.result.upload', $prevStudent->id) }}"
               class="btn btn-outline-primary"
               title="{{ $prevStudent->name }}">
                <i class="fas fa-chevron-left mr-1"></i>
                <span class="d-none d-sm-inline">{{ $prevStudent->name }}</span>
                <span class="d-sm-none">Prev</span>
            </a>
        @else
            <button class="btn btn-outline-secondary" disabled>
                <i class="fas fa-chevron-left mr-1"></i>
                <span class="d-none d-sm-inline">Previous</span>
                <span class="d-sm-none">Prev</span>
            </button>
        @endif

        @if($nextStudent ?? null)
            <a href="{{ route('student.result.upload', $nextStudent->id) }}"
               class="btn btn-primary"
               title="{{ $nextStudent->name }}">
                <span class="d-none d-sm-inline">{{ $nextStudent->name }}</span>
                <span class="d-sm-none">Next</span>
                <i class="fas fa-chevron-right ml-1"></i>
            </a>
        @else
            <button class="btn btn-secondary" disabled>
                <span class="d-none d-sm-inline">Next</span>
                <span class="d-sm-none">Next</span>
                <i class="fas fa-chevron-right ml-1"></i>
            </button>
        @endif
    </div>
</div>

{{-- Push page content above the sticky bar --}}
<div style="height:62px;"></div>

@include('includes.edit_footer')

<style>
.score-input.is-invalid {
    border-color: #dc3545 !important;
    background-color: #fff5f5 !important;
}
.score-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.15rem rgba(102,126,234,.25);
}
.final-obtained-input {
    font-weight: 600;
    color: #374151;
}
.live-total {
    display: inline-block;
    min-width: 40px;
}
td.align-middle { vertical-align: middle !important; }
.subject-name { vertical-align: middle !important; }
#cognitiveTable td, #cognitiveTable th { vertical-align: middle; }
.result-row:hover { background: #f0f7ff; }

/* Remove number input spinner arrows */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
$(function () {

    // ── Grade helpers (mirrors secondary school logic) ────────────────────────
    function calculateGrade(total) {
        if (total >= 70) return 'A';
        if (total >= 60) return 'B';
        if (total >= 50) return 'C';
        if (total >= 45) return 'D';
        if (total >= 40) return 'E';
        return 'F';
    }

    function gradeColor(grade) {
        var map = {
            'A': '#16a34a',
            'B': '#2563eb',
            'C': '#0891b2',
            'D': '#d97706',
            'E': '#6b7280',
            'F': '#dc2626'
        };
        return map[grade] || '#374151';
    }

    // ── Row recalculation ─────────────────────────────────────────────────────
    function recalcRow($row) {
        var firstVal  = $row.find('.first-half-input').val();
        var secondVal = $row.find('.second-half-input').val();
        var courseId  = $row.find('.first-half-input').data('course');

        var first  = firstVal  !== '' ? parseFloat(firstVal)  : null;
        var second = secondVal !== '' ? parseFloat(secondVal) : null;

        var $liveTotal   = $row.find('.live-total[data-course="' + courseId + '"]');
        var $finalInput  = $row.find('.final-obtained-input[data-course="' + courseId + '"]');
        var $gradeSpan   = $row.find('.live-grade');
        var $gradeHidden = $row.find('.grade-hidden-input');

        if (first === null && second === null) {
            $liveTotal.text('—');
            $finalInput.val('');
            $gradeSpan.text('—').css('color', '#374151');
            $gradeHidden.val('');
            return;
        }

        var total = (first ?? 0) + (second ?? 0);
        total = Math.round(total * 10) / 10;

        $liveTotal.text(total.toFixed(1));
        $finalInput.val(total.toFixed(1));

        var grade = calculateGrade(total);
        $gradeSpan.text(grade).css('color', gradeColor(grade));
        $gradeHidden.val(grade);
    }

    // ── Live validation + recalc on input ────────────────────────────────────
    $('#cognitiveTable tbody').on('input change', '.score-input', function () {
        var max = parseFloat($(this).data('max'));
        var val = parseFloat($(this).val());
        $(this).toggleClass('is-invalid', !isNaN(val) && val > max);
        recalcRow($(this).closest('tr'));
    });

    // ── Recalc pre-filled rows on page load ──────────────────────────────────
    $('#cognitiveTable tbody tr.result-row').each(function () {
        recalcRow($(this));
    });

    // ── Form submission validation ────────────────────────────────────────────
    $('#primaryResultForm').on('submit', function (e) {
        var valid      = true;
        var firstError = null;

        $('#cognitiveTable tbody tr.result-row').each(function () {
            var $row        = $(this);
            var subjectName = $row.find('.subject-name').text().trim();

            $row.find('.score-input').each(function () {
                var max = parseFloat($(this).data('max'));
                var val = parseFloat($(this).val());
                if (!isNaN(val) && val > max) {
                    valid = false;
                    $(this).addClass('is-invalid');
                    if (!firstError) {
                        var half = $(this).data('half') === 'first' ? '1st Half' : '2nd Half';
                        firstError = '"' + subjectName + '" — ' + half + ': Score (' + val + ') cannot exceed max (' + max + ').';
                    }
                }
            });
        });

        if (!valid) {
            e.preventDefault();
            alert(firstError + '\n\nPlease correct the highlighted fields before saving.');
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 150
            }, 400);
        }
    });

});
</script>
</body>