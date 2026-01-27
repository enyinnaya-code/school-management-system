@include('includes.head')

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
                            <div class="card-header">
                                <h4>Upload Results for {{ $student->name }} ({{ $class->name ?? '' }} - {{ $section->section_name ?? '' }})</h4>
                                @if(isset($currentSession) && isset($currentTerm))
                                    <p class="mb-0"><strong>Academic Session:</strong> {{ $currentSession->name }} | <strong>Term:</strong> {{ $currentTerm->name }}</p>
                                @else
                                    <p class="mb-0 text-danger">No current academic session or term is set.</p>
                                @endif
                            </div>

                            <div class="card-body">
                               
                                <form method="POST" action="{{ route('student.results.save', $student->id) }}">
                                    @csrf

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>First CA<br><small>(e.g., /10)</small></th>
                                                    <th>Second CA<br><small>(e.g., /10)</small></th>
                                                    <th>Mid Term Test<br><small>(e.g., /20)</small></th>
                                                    <th>Examination<br><small>(e.g., /60)</small></th>
                                                    <th>Total<br><small>/100</small></th>
                                                    <th>Grade</th>
                                                    <th>Comment</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($subjects as $subject)
                                                    @php
                                                        $result = $existingResults->get($subject->id);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $subject->course_name }}</td>
                                                        <td>
                                                            <input type="number" name="results[{{ $subject->id }}][first_ca]" 
                                                                   value="{{ $result->first_ca ?? 0 }}" 
                                                                   class="form-control score-input" step="0.01" min="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="results[{{ $subject->id }}][second_ca]" 
                                                                   value="{{ $result->second_ca ?? 0 }}" 
                                                                   class="form-control score-input" step="0.01" min="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="results[{{ $subject->id }}][mid_term_test]" 
                                                                   value="{{ $result->mid_term_test ?? 0 }}" 
                                                                   class="form-control score-input" step="0.01" min="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="results[{{ $subject->id }}][examination]" 
                                                                   value="{{ $result->examination ?? 0 }}" 
                                                                   class="form-control score-input" step="0.01" min="0" required>
                                                        </td>
                                                        <td class="text-center total-score">
                                                            {{ $result ? number_format($result->total, 2) : '0.00' }}
                                                        </td>
                                                        <td class="text-center student-grade">
                                                            {{ $result->grade ?? '-' }}
                                                        </td>
                                                        <td>
                                                            <textarea name="results[{{ $subject->id }}][comment]" 
                                                                      class="form-control" rows="2">{{ $result->comment ?? '' }}</textarea>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center text-danger">
                                                            @if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2)
                                                                No subjects available in the system.
                                                            @else
                                                                No subjects assigned to you for this class. Contact admin.
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save All Results
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

    <script>
        function getGrade(total) {
            if (total >= 70) return 'A';
            if (total >= 60) return 'B';
            if (total >= 50) return 'C';
            if (total >= 45) return 'D';
            if (total >= 40) return 'E';
            return 'F';
        }

        function updateRow(row) {
            let sum = 0;
            row.find('.score-input').each(function() {
                sum += parseFloat($(this).val()) || 0;
            });
            row.find('.total-score').text(sum.toFixed(2));
            row.find('.student-grade').text(getGrade(sum));
        }

        $(document).ready(function() {
            // Initial calculation on page load
            $('tbody tr').each(function() {
                updateRow($(this));
            });

            // Live update on input change
            $(document).on('input', '.score-input', function() {
                updateRow($(this).closest('tr'));
            });
        });
    </script>
</body>