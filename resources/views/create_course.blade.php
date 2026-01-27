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
                            <form action="{{ route('course.store') }}" method="POST" class="needs-validation" novalidate>
                                @csrf
                                <div class="card-header">
                                    <h4>Create Course/Subject</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group col-md-6 px-0">
                                        <label>Course/Subject Name</label>
                                        <input type="text" name="course_name" class="form-control" required placeholder="e.g. Mathematics">
                                        <div class="invalid-feedback">
                                            Please provide a course name.
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Section/Arm</label>
                                        <select name="section_id" id="section_id" class="form-control" required>
                                            <option value="">Select a Section</option>
                                            @foreach($sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a section.
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12 px-0" id="classes_container" style="display: none;">
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
                                                    <!-- Classes will be loaded here via AJAX -->
                                                </div>
                                                <div id="loading_classes" style="display: none;">
                                                    <p class="text-muted">Loading classes...</p>
                                                </div>
                                                <div id="no_classes" style="display: none;">
                                                    <p class="text-warning">No classes found for this section.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback" id="class_error">
                                            Please select at least one class.
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-left mt-5 pt-5">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                            </form>
                        </div>

                        <!-- Validation and AJAX Script -->
                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                        <script>
                            $(document).ready(function() {
                                // AJAX to fetch classes when section changes
                                $('#section_id').on('change', function() {
                                    const sectionId = $(this).val();
                                    const classesContainer = $('#classes_container');
                                    const classesList = $('#classes_list');
                                    const loadingClasses = $('#loading_classes');
                                    const noClasses = $('#no_classes');
                                    
                                    // Reset
                                    classesList.empty();
                                    $('#select_all_classes').prop('checked', false);
                                    
                                    if (sectionId) {
                                        classesContainer.show();
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
                                                        const checkbox = `
                                                            <div class="col-md-4 col-sm-6 mb-2">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input class-checkbox" 
                                                                           name="class_ids[]" value="${classItem.id}" 
                                                                           id="class_${classItem.id}">
                                                                    <label class="form-check-label" for="class_${classItem.id}">
                                                                        ${classItem.name}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        `;
                                                        classesList.append(checkbox);
                                                    });
                                                } else {
                                                    noClasses.show();
                                                }
                                            },
                                            error: function() {
                                                loadingClasses.hide();
                                                classesList.html('<p class="text-danger">Error loading classes. Please try again.</p>');
                                            }
                                        });
                                    } else {
                                        classesContainer.hide();
                                    }
                                });
                                
                                // Select all functionality
                                $('#select_all_classes').on('change', function() {
                                    $('.class-checkbox').prop('checked', $(this).is(':checked'));
                                });
                                
                                // Uncheck "Select All" if any checkbox is unchecked
                                $(document).on('change', '.class-checkbox', function() {
                                    if (!$(this).is(':checked')) {
                                        $('#select_all_classes').prop('checked', false);
                                    } else {
                                        // Check if all are checked
                                        const allChecked = $('.class-checkbox:checked').length === $('.class-checkbox').length;
                                        $('#select_all_classes').prop('checked', allChecked);
                                    }
                                });
                                
                                // Form validation
                                (function() {
                                    'use strict';
                                    window.addEventListener('load', function() {
                                        const forms = document.getElementsByClassName('needs-validation');
                                        Array.prototype.filter.call(forms, function(form) {
                                            form.addEventListener('submit', function(event) {
                                                // Custom validation for classes
                                                const checkedClasses = $('.class-checkbox:checked').length;
                                                const sectionId = $('#section_id').val();
                                                
                                                if (sectionId && checkedClasses === 0) {
                                                    event.preventDefault();
                                                    event.stopPropagation();
                                                    $('#class_error').show();
                                                    $('#classes_container').addClass('is-invalid');
                                                } else {
                                                    $('#class_error').hide();
                                                    $('#classes_container').removeClass('is-invalid');
                                                }
                                                
                                                if (!form.checkValidity() || (sectionId && checkedClasses === 0)) {
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