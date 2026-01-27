@include('includes.head')

<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
                            <form action="{{ route('parent.store') }}" method="POST" class="needs-validation" novalidate>
                                @csrf

                                <div class="card-header">
                                    <h4>Activate Parent</h4>
                                </div>

                                <div class="card-body">
                                    <!-- Student Selection (Searchable Multiple, Global Search) -->
                                    <div class="form-group col-md-12 px-0">
                                        <label>Search and Select Student(s) <span class="text-danger">*</span></label>
                                        <select id="student-select" name="student_ids[]" multiple required style="width: 100%;">
                                            <option value="">Search for a student by name...</option>
                                        </select>
                                        <div class="invalid-feedback">Please select at least one student.</div>
                                        <small class="form-text text-muted">Selecting a student will prefill parent details from guardian info (only for the first selection).</small>
                                    </div>

                                    <!-- Parent Details (Prefilled) -->
                                    <div class="form-group col-md-6 px-0">
                                        <label>Parent Full Name <span class="text-danger">*</span></label>
                                        <input type="text" id="parent-name" name="parent_name" class="form-control" required placeholder="e.g. MARY DOE" oninput="this.value = this.value.toUpperCase()">
                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">Please enter parent name.</div>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Parent Email Address <span class="text-danger">*</span></label>
                                        <input type="email" id="parent-email" name="parent_email" class="form-control" required placeholder="e.g. parent@example.com">
                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">Please enter a valid email.</div>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Parent Phone</label>
                                        <input type="text" id="parent-phone" name="parent_phone" class="form-control" placeholder="e.g. +1234567890">
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Password <small>Default: 123456</small></label>
                                        <input type="password" name="password" class="form-control" required placeholder="Enter password" value="123456" readonly>
                                        <div class="invalid-feedback">Please provide a password.</div>
                                    </div>

                                    <div class="form-group col-md-6 px-0" style="display: none;">
                                        <label>User Type</label>
                                        <input type="hidden" name="user_type" value="5">
                                    </div>

                                </div>

                                <div class="card-footer text-left pt-5 mt-3">
                                    <button class="btn btn-primary" type="submit">Activate Parent</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')
    <!-- Include Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            let isPrefilled = false; // Flag to prefill only once (for first student)

            let studentSelect = $('#student-select').select2({
                placeholder: "Search for a student by name...",
                allowClear: true,
                ajax: {
                    url: '/students/search',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: (params.page * 10) < data.total
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1,
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }
                    return data.text;
                },
                templateSelection: function(data) {
                    return data.text;
                }
            });

            // On student selection, fetch and prefill guardian details only for the first selection
            studentSelect.on('select2:select', function(e) {
                const selectedData = e.params.data;
                if (selectedData && selectedData.id && !isPrefilled) {
                    fetchGuardianDetails(selectedData.id);
                    isPrefilled = true;
                }
            });

            // Allow clearing to reset prefill flag if all students are removed
            studentSelect.on('select2:unselecting', function(e) {
                if ($('#student-select').select2('data').length === 0) {
                    isPrefilled = false;
                    clearParentFields();
                }
            });

            function fetchGuardianDetails(studentId) {
                $.ajax({
                    url: `/student/${studentId}/guardian`,
                    type: 'GET',
                    success: function(data) {
                        $('#parent-name').val(data.guardian_name || '');
                        $('#parent-email').val(data.guardian_email || '');
                        $('#parent-phone').val(data.guardian_phone || '');
                        // Trigger validation if needed
                        $('#parent-name').trigger('input');
                        $('#parent-email').trigger('input');
                    },
                    error: function() {
                        alert('Error fetching guardian details.');
                        clearParentFields();
                    }
                });
            }

            function clearParentFields() {
                $('#parent-name').val('');
                $('#parent-email').val('');
                $('#parent-phone').val('');
            }

            // Bootstrap validation
            (function() {
                'use strict';
                window.addEventListener('load', function() {
                    var forms = document.getElementsByClassName('needs-validation');
                    var validation = Array.prototype.filter.call(forms, function(form) {
                        form.addEventListener('submit', function(event) {
                            if (form.checkValidity() === false) {
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
</body>