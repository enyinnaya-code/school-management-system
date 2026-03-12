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
                                {{-- <a href="{{ url()->previous() }}"
                                   class="btn btn-secondary btn-sm mt-2 mt-md-0">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a> --}}
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
                        {{-- SECTION 1: TERMLY SCORE (auto-calculated)     --}}
                        {{-- ══════════════════════════════════════════════ --}}
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="section-title">
                                    <i class="fas fa-chart-bar mr-2"></i>
                                    1. Termly Score
                                    <small class="font-weight-normal ml-2">
                                        (auto-calculated from saved results)
                                    </small>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                @foreach($termlyScores['terms'] as $termData)
                                                <th class="text-center {{ $termData['is_current'] ? 'table-primary' : '' }}">
                                                    {{ $termData['name'] }} Total
                                                    @if($termData['is_current'])
                                                        <span class="badge badge-primary ml-1">Current</span>
                                                    @endif
                                                </th>
                                                @endforeach
                                                <th class="text-center table-warning">
                                                    Cumulative Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="termly-score-row">
                                                @foreach($termlyScores['terms'] as $termData)
                                                <td class="text-center">
                                                    <strong class="text-primary" style="font-size:18px;">
                                                        {{ $termData['total'] > 0
                                                            ? number_format($termData['total'], 1)
                                                            : '—' }}
                                                    </strong>
                                                </td>
                                                @endforeach
                                                <td class="text-center">
                                                    <strong class="text-warning" style="font-size:18px;">
                                                        {{ $termlyScores['cumulative'] > 0
                                                            ? number_format($termlyScores['cumulative'], 1)
                                                            : '—' }}
                                                    </strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- ══════════════════════════════════════════════ --}}
                        {{-- SECTION 2: COGNITIVE ABILITY                  --}}
                        {{-- ══════════════════════════════════════════════ --}}
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="section-title">
                                    <i class="fas fa-brain mr-2"></i> 2. Cognitive Ability
                                </div>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-info-circle"></i>
                                    Enter Marks Obtainable and Marks Obtained for each half per subject.
                                    Leave a row completely blank to skip that subject.
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-bordered subject-table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2"
                                                    class="subject-name align-middle"
                                                    style="background:#f8f9fa;">
                                                    Subject
                                                </th>
                                                <th colspan="2"
                                                    class="text-center"
                                                    style="background:#dbeafe;">
                                                    1st Half
                                                </th>
                                                <th colspan="2"
                                                    class="text-center"
                                                    style="background:#dcfce7;">
                                                    2nd Half
                                                </th>
                                                <th colspan="4"
                                                    class="text-center"
                                                    style="background:#fef9c3;">
                                                    Final Result
                                                </th>
                                            </tr>
                                            <tr>
                                                {{-- 1st Half --}}
                                                <th style="background:#eff6ff;">Mks Obtainable</th>
                                                <th style="background:#eff6ff;">Mks Obtained</th>
                                                {{-- 2nd Half --}}
                                                <th style="background:#f0fdf4;">Mks Obtainable</th>
                                                <th style="background:#f0fdf4;">Mks Obtained</th>
                                                {{-- Final --}}
                                                <th style="background:#fefce8;">Mks Obtainable</th>
                                                <th style="background:#fefce8;">Mks Obtained</th>
                                                <th style="background:#fefce8;">Class Avg</th>
                                                <th style="background:#fefce8;">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($subjects as $subject)
                                            @php $r = $existingResults->get($subject->id); @endphp
                                            <tr>
                                                <td class="subject-name">
                                                    {{ $subject->course_name }}
                                                </td>

                                                {{-- 1st Half --}}
                                                <td style="background:#f8fbff;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][first_half_obtainable]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $r?->first_half_obtainable ?? '' }}"
                                                           min="0" step="0.5"
                                                           placeholder="Max">
                                                </td>
                                                <td style="background:#f8fbff;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][first_half_obtained]"
                                                           class="form-control form-control-sm obtained-input"
                                                           data-half="first"
                                                           data-course="{{ $subject->id }}"
                                                           value="{{ $r?->first_half_obtained ?? '' }}"
                                                           min="0" step="0.5"
                                                           placeholder="Score">
                                                </td>

                                                {{-- 2nd Half --}}
                                                <td style="background:#f8fdf9;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][second_half_obtainable]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $r?->second_half_obtainable ?? '' }}"
                                                           min="0" step="0.5"
                                                           placeholder="Max">
                                                </td>
                                                <td style="background:#f8fdf9;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][second_half_obtained]"
                                                           class="form-control form-control-sm obtained-input"
                                                           data-half="second"
                                                           data-course="{{ $subject->id }}"
                                                           value="{{ $r?->second_half_obtained ?? '' }}"
                                                           min="0" step="0.5"
                                                           placeholder="Score">
                                                </td>

                                                {{-- Final Result --}}
                                                <td style="background:#fffef5;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][final_obtainable]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $r?->final_obtainable ?? '' }}"
                                                           min="0" step="0.5"
                                                           placeholder="Max">
                                                </td>
                                                <td style="background:#fffef5;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][final_obtained]"
                                                           class="form-control form-control-sm obtained-input"
                                                           data-half="final"
                                                           data-course="{{ $subject->id }}"
                                                           value="{{ $r?->final_obtained ?? '' }}"
                                                           min="0" step="0.5"
                                                           placeholder="Score">
                                                </td>
                                                <td style="background:#fffef5;">
                                                    <input type="number"
                                                           name="results[{{ $subject->id }}][class_average]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $r?->class_average ?? '' }}"
                                                           min="0" step="0.5"
                                                           placeholder="Avg">
                                                </td>
                                                <td style="background:#fffef5;">
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
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-save mr-2"></i> Save Results
                                </button>
                                <a href="{{ url()->previous() }}"
                                   class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-times mr-2"></i> Cancel
                                </a>
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
    // Validate obtained does not exceed obtainable on submit
    $('#primaryResultForm').on('submit', function (e) {
        let valid      = true;
        let firstError = null;

        $('tbody tr').each(function () {
            const row = $(this);

            const checkPair = (obtainableSelector, obtainedSelector, halfLabel) => {
                const obtainableVal = obtainableSelector.val();
                const obtainedVal   = obtainedSelector.val();

                if (obtainableVal === '' || obtainedVal === '') return;

                const obtainable = parseFloat(obtainableVal);
                const obtained   = parseFloat(obtainedVal);

                if (!isNaN(obtainable) && !isNaN(obtained) && obtained > obtainable) {
                    valid = false;
                    obtainedSelector.addClass('is-invalid');
                    if (!firstError) {
                        const subjectName = row.find('.subject-name').text().trim();
                        firstError = `"${subjectName}" — ${halfLabel}: Score obtained (${obtained}) cannot exceed max obtainable (${obtainable}).`;
                    }
                } else {
                    obtainedSelector.removeClass('is-invalid');
                }
            };

            checkPair(
                row.find('input[name$="[first_half_obtainable]"]'),
                row.find('input[name$="[first_half_obtained]"]'),
                '1st Half'
            );
            checkPair(
                row.find('input[name$="[second_half_obtainable]"]'),
                row.find('input[name$="[second_half_obtained]"]'),
                '2nd Half'
            );
            checkPair(
                row.find('input[name$="[final_obtainable]"]'),
                row.find('input[name$="[final_obtained]"]'),
                'Final Result'
            );
        });

        if (!valid) {
            e.preventDefault();
            alert(firstError + '\n\nPlease correct the highlighted fields before saving.');
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 150
            }, 400);
        }
    });
</script>
</body>