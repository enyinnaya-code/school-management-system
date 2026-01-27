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
                            <form action="{{ route('course.update', $course->id) }}" method="POST" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="card-header">
                                    <h4>Edit Course/Subject</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group col-md-6 px-0">
                                        <label>Course/Subject Name</label>
                                        <input type="text" name="course_name" class="form-control" required placeholder="e.g. Mathematics"
                                            value="{{ old('course_name', $course->course_name) }}">
                                        <div class="invalid-feedback">
                                            Please provide a course name.
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Section/Arm</label>
                                        <select name="section_id" id="section_id" class="form-control" required>
                                            <option value="">Select a Section</option>
                                            @foreach($sections as $section)
                                            <option value="{{ $section->id }}" {{ $section->id == old('section_id', $course->section_id) ? 'selected' : '' }}>
                                                {{ $section->section_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a section.
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12 px-0" id="classes_container">
                                        <label>Select Classes That Offer This Course</label>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" class="form-check-input" id="select_all_classes">
                                                    <label class="form-check-label font-weight-bold" for="select_all_classes">
                                                        Select All Classes
                                                    </label>
                                                </div>
                                                <hr>
                                                <div id="classes_list" class="row">
                                                    @if($classes->count() > 0)
                                                        @foreach($classes as $class)
                                                        <div class="col-md-4 col-sm-6 mb-2">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input class-checkbox" 
                                                                       name="class_ids[]" value="{{ $class->id }}" 
                                                                       id="class_{{ $class->id }}"
                                                                       {{ $course->schoolClasses->contains($class->id) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="class_{{ $class->id }}">
                                                                    {{ $class->name }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <div class="col-12">
                                                            <p class="text-warning">No classes found for this section.</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div id="loading_classes" style="display: none;">
                                                    <p class="text-muted">Loading classes...</p>
                                                </div>
                                                <div id="no_classes" style="display: none;">
                                                    <p class="text-warning">No classes found for this section.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback" id="class_error" style="display: none;">
                                            Please select at least one class.
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-left mt-5 pt-5">
                                    <button class="btn btn-primary" type="submit">Update Course</button>
                                    <a href="{{ route('course.manage') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>

                        <!-- Validation and AJAX Script -->
                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                        <script>
                            $(document).ready(function() {
                                // Store currently selected class IDs
                                let selectedClassIds = [];
                                $('.class-checkbox:checked').each(function() {
                                    selectedClassIds.push($(this).val());
                                });

                                // Check "Select All" on page load if all checkboxes are checked
                                checkSelectAllStatus();

                                // AJAX to fetch classes when section changes
                                $('#section_id').on('change', function() {
                                    const sectionId = $(this).val();
                                    const classesList = $('#classes_list');
                                    const loadingClasses = $('#loading_classes');
                                    const noClasses = $('#no_classes');
                                    
                                    // Store currently selected IDs before clearing
                                    selectedClassIds = [];
                                    $('.class-checkbox:checked').each(function() {
                                        selectedClassIds.push($(this).val());
                                    });
                                    
                                    // Reset
                                    classesList.empty();
                                    $('#select_all_classes').prop('checked', false);
                                    
                                    if (sectionId) {
                                        loadingClasses.show();
                                        noClasses.hide();
                                        
                                        // Fetch classes
                                        $.ajax({
                                            url: `/courses/get-classes/${sectionId}`,
                                            type: 'GET',
                                            dataType: 'json',
                                            success: function(classes) {
                                                loadingClasses.hide();
                                                
                                                if (classes.length > 0) {
                                                    classes.forEach(function(classItem) {
                                                        // Check if this class was previously selected
                                                        const isChecked = selectedClassIds.includes(classItem.id.toString());
                                                        const checkedAttr = isChecked ? 'checked' : '';
                                                        
                                                        const checkbox = `
                                                            <div class="col-md-4 col-sm-6 mb-2">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input class-checkbox" 
                                                                           name="class_ids[]" value="${classItem.id}" 
                                                                           id="class_${classItem.id}" ${checkedAttr}>
                                                                    <label class="form-check-label" for="class_${classItem.id}">
                                                                        ${classItem.name}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        `;
                                                        classesList.append(checkbox);
                                                    });
                                                    
                                                    // Check "Select All" status after loading
                                                    checkSelectAllStatus();
                                                } else {
                                                    noClasses.show();
                                                }
                                            },
                                            error: function(xhr, status, error) {
                                                loadingClasses.hide();
                                                console.error('AJAX Error:', status, error);
                                                classesList.html('<div class="col-12"><p class="text-danger">Error loading classes. Please try again.</p></div>');
                                            }
                                        });
                                    } else {
                                        classesList.html('<div class="col-12"><p class="text-muted">Please select a section first.</p></div>');
                                    }
                                });
                                
                                // Select all functionality
                                $('#select_all_classes').on('change', function() {
                                    $('.class-checkbox').prop('checked', $(this).is(':checked'));
                                });
                                
                                // Uncheck "Select All" if any checkbox is unchecked
                                $(document).on('change', '.class-checkbox', function() {
                                    checkSelectAllStatus();
                                });

                                // Function to check if all checkboxes are checked
                                function checkSelectAllStatus() {
                                    const totalCheckboxes = $('.class-checkbox').length;
                                    const checkedCheckboxes = $('.class-checkbox:checked').length;
                                    
                                    if (totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes) {
                                        $('#select_all_classes').prop('checked', true);
                                    } else {
                                        $('#select_all_classes').prop('checked', false);
                                    }
                                }
                                
                                // Form validation
                                (function() {
                                    'use strict';
                                    window.addEventListener('load', function() {
                                        const forms = document.getElementsByClassName('needs-validation');
                                        Array.prototype.filter.call(forms, function(form) {
                                            form.addEventListener('submit', function(event) {
                                                let isValid = true;

                                                // Custom validation for classes
                                                const checkedClasses = $('.class-checkbox:checked').length;
                                                const sectionId = $('#section_id').val();
                                                
                                                if (sectionId && checkedClasses === 0) {
                                                    isValid = false;
                                                    $('#class_error').show();
                                                    $('#classes_container').addClass('is-invalid');
                                                } else {
                                                    $('#class_error').hide();
                                                    $('#classes_container').removeClass('is-invalid');
                                                }
                                                
                                                // Check form validity
                                                if (!form.checkValidity()) {
                                                    isValid = false;
                                                }

                                                if (!isValid) {
                                                    event.preventDefault();
                                                    event.stopPropagation();
                                                }
                                                
                                                form.classList.add('was-validated');
                                            }, false);
                                        });
                                    }, false);
                                })();
                            });
                        </script>
                    </div>
                </section>
            </div>
        </div>

        @include('includes.edit_footer')
    </div>
</body>