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
                        <form action="{{ route('fee.prospectus.select') }}" method="POST" class="needs-validation" novalidate>
                                @csrf

                                <div class="card-header">
                                    <h4>Create Fee Prospectus</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Section (Arm) Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">School Arm (Section)</label>
                                        <div class="col-md-6">
                                            <select class="form-control" name="section_id" id="section_id" required>
                                                <option value="">Select section...</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Classes Selection (Checkboxes) -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Classes</label>
                                        <div class="col-md-6">
                                            <div id="class_checkboxes" style="max-height: 200px; overflow-y: auto;">
                                                <!-- Checkboxes will be loaded here -->
                                            </div>
                                            <small class="form-text text-muted">Select multiple classes (e.g., JSS1A, JSS1B for whole JSS1).</small>
                                            @error('class_ids')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Term Selection -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Term</label>
                                        <div class="col-md-6">
                                            <select class="form-control" name="term_id" id="term_id" disabled required>
                                                <option value="">Select term after choosing classes...</option>
                                            </select>
                                            @error('term_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group mt-4 pt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Prospectus
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')

        <script>
            $(document).ready(function() {
                $('#section_id').change(function() {
                    var sectionId = $(this).val();
                    if (sectionId) {
                        // Load classes as checkboxes
                        $.get('/bursar/classes/' + sectionId, function(data) {
                            var checkboxesHtml = '';
                            $.each(data, function(index, classItem) {
                                checkboxesHtml += '<div class="form-check"><input class="form-check-input" type="checkbox" name="class_ids[]" value="' + classItem.id + '" id="class_' + classItem.id + '"><label class="form-check-label" for="class_' + classItem.id + '">' + classItem.name + '</label></div>';
                            });
                            $('#class_checkboxes').html(checkboxesHtml);

                            // Load terms based on section
                            $.get('/bursar/terms/' + sectionId, function(termData) {
                                $('#term_id').html('<option value="">Loading...</option>');
                                $('#term_id').prop('disabled', true);
                                setTimeout(function() {
                                    $('#term_id').html('<option value="">Select term...</option>');
                                    $.each(termData, function(index, term) {
                                        $('#term_id').append('<option value="' + term.id + '">' + term.name + '</option>');
                                    });
                                    $('#term_id').prop('disabled', false);
                                }, 500);
                            }).fail(function() {
                                $('#term_id').html('<option value="">Error loading terms</option>');
                            });
                        }).fail(function() {
                            $('#class_checkboxes').html('<p class="text-danger">Error loading classes</p>');
                        });
                    } else {
                        $('#class_checkboxes').html('');
                        $('#term_id').html('<option value="">Select term after choosing classes...</option>').prop('disabled', true);
                    }
                });

                // Listen for changes on checkboxes to enable/disable term if needed
                // But since term is loaded on section, and selection is just for form validation
                $(document).on('change', 'input[name="class_ids[]"]', function() {
                    var checkedBoxes = $('input[name="class_ids[]"]:checked').length;
                    if (checkedBoxes === 0) {
                        $('#term_id').prop('disabled', true);
                    } else {
                        $('#term_id').prop('disabled', false);
                    }
                });
            });
        </script>
    </body>
</body>