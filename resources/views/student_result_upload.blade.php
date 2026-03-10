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
                                        <thead class="">
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
                                        </thead>
                                        <tbody>
                                            @foreach($subjects as $subject)
                                                @php $existing = $existingResults->get($subject->id); @endphp
                                                <tr class="item-row">
                                                    <td class="font-weight-bold">{{ $subject->course_name }}</td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100"
                                                               name="results[{{ $subject->id }}][first_ca]"
                                                               class="form-control form-control-sm"
                                                               value="{{ old('results.'.$subject->id.'.first_ca', $existing?->first_ca ?? '') }}"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100"
                                                               name="results[{{ $subject->id }}][second_ca]"
                                                               class="form-control form-control-sm"
                                                               value="{{ old('results.'.$subject->id.'.second_ca', $existing?->second_ca ?? '') }}"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100"
                                                               name="results[{{ $subject->id }}][mid_term_test]"
                                                               class="form-control form-control-sm"
                                                               value="{{ old('results.'.$subject->id.'.mid_term_test', $existing?->mid_term_test ?? '') }}"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100"
                                                               name="results[{{ $subject->id }}][examination]"
                                                               class="form-control form-control-sm"
                                                               value="{{ old('results.'.$subject->id.'.examination', $existing?->examination ?? '') }}"
                                                               style="min-width:70px">
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        {{ $existing?->total ?? '—' }}
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        {{ $existing?->grade ?? '—' }}
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
</style>
</body>