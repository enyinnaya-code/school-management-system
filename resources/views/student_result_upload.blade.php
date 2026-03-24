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
                    <div class="card">

                        {{-- ══════════════════════════════════════════════════════
                             CARD HEADER — student info + top navigation bar
                        ══════════════════════════════════════════════════════ --}}
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:12px;">

                                {{-- Left: student name & context --}}
                                <div>
                                    <h4 class="mb-1">
                                        <i class="fas fa-clipboard-check mr-2"></i>
                                        Upload Results &mdash; {{ $student->name }}
                                    </h4>
                                    <p class="mb-0 text-muted">
                                        {{ $class->name }}
                                        @if($section) &bull; {{ $section->section_name }} @endif
                                        &bull; <strong>{{ $currentSession->name }}</strong>
                                        &mdash; <strong>{{ $currentTerm->name }}</strong>
                                    </p>
                                    @if(isset($studentPosition) && isset($totalStudents))
                                        <small class="text-muted">
                                            <i class="fas fa-users mr-1"></i>
                                            Student {{ $studentPosition }} of {{ $totalStudents }}
                                        </small>
                                    @endif
                                </div>

                                {{-- Right: navigation buttons --}}
                                <div class="d-flex align-items-center flex-wrap" style="gap:6px;">

                                    {{-- Back to list --}}
                                    <a href="{{ route('results.selectClass.get', [
                                            'class_id'   => $class->id,
                                            'section_id' => $class->section_id,
                                        ]) }}"
                                       class="btn btn-outline-secondary btn-sm"
                                       title="Back to student list">
                                        <i class="fas fa-list mr-1"></i> Back to List
                                    </a>

                                    {{-- Previous student --}}
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

                                    {{-- Next student --}}
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
                        {{-- END card-header --}}

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

                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle"></i>
                                Enter <strong>Marks Obtained</strong> for each half per subject.
                                Obtainable marks are fixed:
                                <span class="badge badge-info">1st Half = 30</span>
                                <span class="badge badge-success">2nd Half = 70</span>
                                <span class="badge badge-warning text-dark">Total = 100</span>.
                                Totals calculate in real time. Leave a row blank to skip.
                            </p>

                            <form method="POST"
                                  action="{{ route('student.results.save', ['studentId' => $student->id]) }}"
                                  id="resultsForm">
                                @csrf

                                <input type="hidden" name="session_id" value="{{ $currentSession->id }}">
                                <input type="hidden" name="term_id"    value="{{ $currentTerm->id }}">

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle" id="resultsTable">
                                        <thead>
                                            <tr class="text-center">
                                                <th rowspan="2" class="align-middle" style="min-width:180px;">Subject</th>
                                                <th colspan="2" style="background:#dbeafe;">
                                                    1st Half <span class="badge badge-info ml-1">Max: 30</span>
                                                </th>
                                                <th colspan="2" style="background:#dcfce7;">
                                                    2nd Half <span class="badge badge-success ml-1">Max: 70</span>
                                                </th>
                                                <th colspan="2" style="background:#fef9c3;">
                                                    Total <span class="badge badge-warning text-dark ml-1">Max: 100</span>
                                                </th>
                                                <th rowspan="2" class="align-middle text-center" style="min-width:70px; background:#f3e8ff;">
                                                    Grade
                                                </th>
                                                <th rowspan="2" class="align-middle" style="min-width:160px;">Comment</th>
                                            </tr>
                                            <tr class="text-center small">
                                                <th style="background:#eff6ff; min-width:90px;">Obtainable</th>
                                                <th style="background:#eff6ff; min-width:90px;">Obtained</th>
                                                <th style="background:#f0fdf4; min-width:90px;">Obtainable</th>
                                                <th style="background:#f0fdf4; min-width:90px;">Obtained</th>
                                                <th style="background:#fefce8; min-width:90px;">Obtainable</th>
                                                <th style="background:#fefce8; min-width:90px;">Obtained</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjects as $subject)
                                                @php $existing = $existingResults->get($subject->id); @endphp
                                                <tr class="item-row">
                                                    <td class="font-weight-bold">{{ $subject->course_name }}</td>

                                                    {{-- 1st Half Obtainable --}}
                                                    <td class="text-center" style="background:#f8fbff;">
                                                        <input type="hidden"
                                                               name="results[{{ $subject->id }}][first_half_obtainable]"
                                                               value="30">
                                                        <span class="badge badge-info px-2 py-1" style="font-size:13px;">30</span>
                                                    </td>

                                                    {{-- 1st Half Obtained --}}
                                                    <td style="background:#f8fbff;">
                                                        <input type="number" step="0.5" min="0" max="30"
                                                               name="results[{{ $subject->id }}][first_half_obtained]"
                                                               class="form-control form-control-sm score-input first-half-input"
                                                               data-max="30"
                                                               value="{{ old('results.'.$subject->id.'.first_half_obtained', $existing?->first_half_obtained ?? '') }}"
                                                               placeholder="0–30">
                                                    </td>

                                                    {{-- 2nd Half Obtainable --}}
                                                    <td class="text-center" style="background:#f8fdf9;">
                                                        <input type="hidden"
                                                               name="results[{{ $subject->id }}][second_half_obtainable]"
                                                               value="70">
                                                        <span class="badge badge-success px-2 py-1" style="font-size:13px;">70</span>
                                                    </td>

                                                    {{-- 2nd Half Obtained --}}
                                                    <td style="background:#f8fdf9;">
                                                        <input type="number" step="0.5" min="0" max="70"
                                                               name="results[{{ $subject->id }}][second_half_obtained]"
                                                               class="form-control form-control-sm score-input second-half-input"
                                                               data-max="70"
                                                               value="{{ old('results.'.$subject->id.'.second_half_obtained', $existing?->second_half_obtained ?? '') }}"
                                                               placeholder="0–70">
                                                    </td>

                                                    {{-- Total Obtainable --}}
                                                    <td class="text-center" style="background:#fffef5;">
                                                        <input type="hidden"
                                                               name="results[{{ $subject->id }}][final_obtainable]"
                                                               value="100">
                                                        <span class="badge badge-warning text-dark px-2 py-1" style="font-size:13px;">100</span>
                                                    </td>

                                                    {{-- Total Obtained (auto-calculated, readonly) --}}
                                                    <td style="background:#fffef5;">
                                                        <input type="number"
                                                               name="results[{{ $subject->id }}][final_obtained]"
                                                               class="form-control form-control-sm final-obtained-input"
                                                               value="{{ old('results.'.$subject->id.'.final_obtained', $existing?->final_obtained ?? '') }}"
                                                               placeholder="Auto"
                                                               readonly
                                                               style="background:#fffde7; cursor:not-allowed; font-weight:600;">
                                                    </td>

                                                    {{-- Live Grade --}}
                                                    <td class="text-center" style="background:#f5f3ff;">
                                                        <input type="hidden"
                                                               name="results[{{ $subject->id }}][grade]"
                                                               class="grade-hidden-input"
                                                               value="{{ $existing?->grade ?? '' }}">
                                                        <strong class="live-grade" style="font-size:15px;">
                                                            {{ $existing?->grade ?? '—' }}
                                                        </strong>
                                                    </td>

                                                    {{-- Comment --}}
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

                                {{-- ── Form action buttons ────────────────────── --}}
                                <div class="mt-3 d-flex flex-wrap align-items-center" style="gap:8px;">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save mr-1"></i> Save Results
                                    </button>
                                    <button type="submit" id="saveAndNextBtn" class="btn btn-success btn-lg"
                                            @if(!($nextStudent ?? null)) disabled @endif>
                                        <i class="fas fa-save mr-1"></i>
                                        Save &amp; Next
                                        @if($nextStudent ?? null)
                                            &rarr; {{ $nextStudent->name }}
                                        @endif
                                    </button>
                                    <a href="{{ route('results.selectClass.get', [
                                            'class_id'   => $class->id,
                                            'section_id' => $class->section_id,
                                        ]) }}"
                                       class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </a>
                                </div>

                            </form>

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
                        {{-- END card-body --}}

                    </div>
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
    {{-- Left: back to list --}}
    <a href="{{ route('results.selectClass.get', [
            'class_id'   => $class->id,
            'section_id' => $class->section_id,
        ]) }}"
       class="btn btn-outline-secondary">
        <i class="fas fa-list mr-1"></i>
        <span class="d-none d-sm-inline">Back to List</span>
        <span class="d-sm-none">List</span>
    </a>

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
#resultsTable td, #resultsTable th { vertical-align: middle; }
.item-row:hover { background: #f0f7ff; }
.score-input.is-invalid { border-color: #dc3545 !important; background-color: #fff5f5 !important; }
/* Hide number input spinners */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
input[type=number] { -moz-appearance: textfield; }
</style>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
$(function () {

    // ── Grade helpers ─────────────────────────────────────────────────────────
    function calculateGrade(total) {
        if (total >= 70) return 'A';
        if (total >= 60) return 'B';
        if (total >= 50) return 'C';
        if (total >= 45) return 'D';
        if (total >= 40) return 'E';
        return 'F';
    }

    function gradeColor(grade) {
        var map = { 'A':'#16a34a', 'B':'#2563eb', 'C':'#0891b2', 'D':'#d97706', 'E':'#6b7280', 'F':'#dc2626' };
        return map[grade] || '#374151';
    }

    // ── Row recalculation ─────────────────────────────────────────────────────
    function recalcRow($row) {
        var firstVal  = $row.find('.first-half-input').val();
        var secondVal = $row.find('.second-half-input').val();

        var first  = firstVal  !== '' ? parseFloat(firstVal)  : null;
        var second = secondVal !== '' ? parseFloat(secondVal) : null;

        var $final       = $row.find('.final-obtained-input');
        var $gradeSpan   = $row.find('.live-grade');
        var $gradeHidden = $row.find('.grade-hidden-input');

        if (first === null && second === null) {
            $final.val('');
            $gradeSpan.text('—').css('color', '#374151');
            $gradeHidden.val('');
            return;
        }

        var total = (first ?? 0) + (second ?? 0);
        total = Math.round(total * 10) / 10;
        $final.val(total.toFixed(1));

        var grade = calculateGrade(total);
        $gradeSpan.text(grade).css('color', gradeColor(grade));
        $gradeHidden.val(grade);
    }

    // ── Live validation + recalc on input ─────────────────────────────────────
    $('#resultsTable tbody').on('input', '.score-input', function () {
        var max = parseFloat($(this).data('max'));
        var val = parseFloat($(this).val());
        $(this).toggleClass('is-invalid', !isNaN(val) && val > max);
        recalcRow($(this).closest('tr'));
    });

    // ── Recalc pre-filled rows on page load ───────────────────────────────────
    $('#resultsTable tbody tr.item-row').each(function () {
        recalcRow($(this));
    });

    // ── Save & Next button — stores the next-student URL then submits ─────────
    @if($nextStudent ?? null)
    var nextStudentUrl = "{{ route('student.result.upload', $nextStudent->id) }}";
    @endif

    $('#saveAndNextBtn').on('click', function (e) {
        e.preventDefault();

        // Run the same validation as the normal submit
        var valid = true;
        var firstError = null;

        $('#resultsTable tbody tr.item-row').each(function () {
            var $row = $(this);
            var subjectName = $row.find('td:first').text().trim();

            $row.find('.score-input').each(function () {
                var max = parseFloat($(this).data('max'));
                var val = parseFloat($(this).val());
                if (!isNaN(val) && val > max) {
                    valid = false;
                    $(this).addClass('is-invalid');
                    if (!firstError) {
                        var half = $(this).hasClass('first-half-input') ? '1st Half' : '2nd Half';
                        firstError = '"' + subjectName + '" — ' + half + ': Score (' + val + ') exceeds max (' + max + ').';
                    }
                }
            });
        });

        if (!valid) {
            alert(firstError + '\n\nPlease correct the highlighted fields before saving.');
            $('html, body').animate({ scrollTop: $('.is-invalid').first().offset().top - 150 }, 400);
            return;
        }

        @if($nextStudent ?? null)
        // Redirect to next student after successful form submission
        $('#resultsForm').attr('action',
            $('#resultsForm').attr('action') + '?redirect_to=' + encodeURIComponent(nextStudentUrl)
        );
        @endif

        $('#resultsForm').submit();
    });

    // ── Normal submit validation ───────────────────────────────────────────────
    $('#resultsForm').on('submit', function (e) {
        // Skip extra validation if triggered by Save & Next (already validated above)
        if ($(document.activeElement).attr('id') === 'saveAndNextBtn') return;

        var valid = true;
        var firstError = null;

        $('#resultsTable tbody tr.item-row').each(function () {
            var $row = $(this);
            var subjectName = $row.find('td:first').text().trim();

            $row.find('.score-input').each(function () {
                var max = parseFloat($(this).data('max'));
                var val = parseFloat($(this).val());
                if (!isNaN(val) && val > max) {
                    valid = false;
                    $(this).addClass('is-invalid');
                    if (!firstError) {
                        var half = $(this).hasClass('first-half-input') ? '1st Half' : '2nd Half';
                        firstError = '"' + subjectName + '" — ' + half + ': Score (' + val + ') exceeds max (' + max + ').';
                    }
                }
            });
        });

        if (!valid) {
            e.preventDefault();
            alert(firstError + '\n\nPlease correct the highlighted fields before saving.');
            $('html, body').animate({ scrollTop: $('.is-invalid').first().offset().top - 150 }, 400);
        }
    });

});
</script>
</body>