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
                                    Totals are calculated automatically in real time.
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
                                                {{-- Empty th for Total & Remarks --}}
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

                                                {{-- 1st Half Obtainable (fixed = 30, hidden input) --}}
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

                                                {{-- 2nd Half Obtainable (fixed = 70, hidden input) --}}
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

                                                {{-- Final Obtainable (fixed = 100, hidden input) --}}
                                                <td style="background:#fffef5;" class="text-center align-middle">
                                                    <input type="hidden"
                                                           name="results[{{ $subject->id }}][final_obtainable]"
                                                           value="100">
                                                    <span class="badge badge-warning text-dark px-2 py-1" style="font-size:13px;">100</span>
                                                </td>

                                                {{-- Final Obtained (auto-filled = 1st + 2nd, but editable) --}}
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
                                                           style="background:#fffde7; cursor:not-allowed;">
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
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                                    No subjects assigned to this class yet.
                                                </td>
                                            </tr>
                                            @endforelse
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

@include('includes.edit_footer')

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
$(function () {

    /**
     * Recalculate the total for a given row.
     * Total = first_half_obtained + second_half_obtained
     * Final obtained = same value (auto-filled, readonly).
     */
    function recalcRow($row) {
        var firstVal  = $row.find('.first-half-input').val();
        var secondVal = $row.find('.second-half-input').val();
        var courseId  = $row.find('.first-half-input').data('course');

        var first  = firstVal  !== '' ? parseFloat(firstVal)  : null;
        var second = secondVal !== '' ? parseFloat(secondVal) : null;

        if (first === null && second === null) {
            // Both blank — show dash, clear final
            $row.find('.live-total[data-course="' + courseId + '"]').text('—');
            $row.find('.final-obtained-input[data-course="' + courseId + '"]').val('');
            return;
        }

        var total = (first ?? 0) + (second ?? 0);
        total = Math.round(total * 10) / 10; // round to 1dp

        $row.find('.live-total[data-course="' + courseId + '"]').text(total.toFixed(1));
        $row.find('.final-obtained-input[data-course="' + courseId + '"]').val(total.toFixed(1));
    }

    // Recalc on any score input change
    $('#cognitiveTable tbody').on('input change', '.score-input', function () {
        var $row = $(this).closest('tr');

        // Inline validation: highlight red if exceeds max
        var max     = parseFloat($(this).data('max'));
        var val     = parseFloat($(this).val());
        if (!isNaN(val) && val > max) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }

        recalcRow($row);
    });

    // Run on page load for pre-filled rows
    $('#cognitiveTable tbody tr.result-row').each(function () {
        recalcRow($(this));
    });

    /**
     * Form submission validation
     */
    $('#primaryResultForm').on('submit', function (e) {
        var valid      = true;
        var firstError = null;

        $('#cognitiveTable tbody tr.result-row').each(function () {
            var $row       = $(this);
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
</style>
</body>