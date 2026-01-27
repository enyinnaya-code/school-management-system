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
                                <h4>Print Class Results</h4>
                            </div>

                            <div class="card-body">
                                @if(Auth::user()->user_type == 3 && Auth::user()->is_form_teacher && Auth::user()->form_class_id)
                                    @php
                                        $formClass = \App\Models\SchoolClass::with('section')->find(Auth::user()->form_class_id);
                                    @endphp

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>As Form Teacher,</strong> you can only print results for your assigned class:
                                        <strong>{{ $formClass->name }} ({{ $formClass->section->section_name }})</strong>
                                    </div>

                                    <div class="text-center mt-4">
                                        <a href="{{ route('results.selectClassForPrint', [
                                            'section_id' => $formClass->section_id,
                                            'class_id'   => $formClass->id
                                        ]) }}" class="btn btn-primary btn-lg">
                                            <i class="fas fa-print"></i> Proceed to Print Results for {{ $formClass->name }}
                                        </a>
                                    </div>
                                @else
                                    <!-- Full Selection Form for Admins -->
                                    <form method="POST" action="{{ route('results.selectClassForPrint') }}">
                                        @csrf

                                        <!-- Section (Arm) Selection -->
                                        <div class="form-group row px-3">
                                            <label class="col-md-2 col-form-label">School Arm (Section)</label>
                                            <div class="col-md-6">
                                                <select class="form-control" name="section_id" id="section_id" required>
                                                    <option value="">Select section...</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                            {{ $section->section_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('section_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Class Selection -->
                                        <div class="form-group row px-3">
                                            <label class="col-md-2 col-form-label">Class</label>
                                            <div class="col-md-6">
                                                <select class="form-control" name="class_id" id="class_id" disabled required>
                                                    <option value="">Select class after choosing section...</option>
                                                </select>
                                                @error('class_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="form-group mt-4 pt-4 text-center">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-arrow-right"></i> Proceed to Print Results
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        $(document).ready(function() {
            $('#section_id').change(function() {
                var sectionId = $(this).val();
                var classSelect = $('#class_id');

                if (sectionId) {
                    classSelect.html('<option value="">Loading classes...</option>').prop('disabled', true);

                    $.get('/sections/' + sectionId + '/classes', function(data) {
                        classSelect.html('<option value="">Select class...</option>');
                        $.each(data, function(index, classItem) {
                            classSelect.append('<option value="' + classItem.id + '">' + classItem.name + '</option>');
                        });
                        classSelect.prop('disabled', false);
                    }).fail(function() {
                        classSelect.html('<option value="">Error loading classes</option>');
                    });
                } else {
                    classSelect.html('<option value="">Select class after choosing section...</option>').prop('disabled', true);
                }
            });

            // Auto-trigger if section is pre-selected (e.g., from old input)
            @if(old('section_id'))
                $('#section_id').trigger('change');
            @endif
        });
    </script>
</body>
</html>