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
                            <form action="{{ route('counsellor.store') }}" method="POST" class="needs-validation"
                                novalidate>
                                @csrf

                                <div class="card-header">
                                    <h4>Schedule Counselling Session</h4>
                                </div>

                                <div class="card-body">
                                    <div class="form-group col-md-8 px-0">
                                        <label>Student <span class="text-danger">*</span></label>
                                        <select name="student_id" id="student-select" class="form-control" required>
                                            <option value="">Select Student</option>
                                            @foreach($students as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Please select a student.</div>
                                    </div>

                                    <div class="row mx-1">
                                        <div class="form-group col-md-6">
                                            <label>Session Date <span class="text-danger">*</span></label>
                                            <input type="date" name="session_date" class="form-control" required
                                                min="{{ date('Y-m-d') }}">
                                            <div class="invalid-feedback">Please select a valid date.</div>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Session Time (Optional)</label>
                                            <input type="time" name="session_time" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12 px-0">
                                        <label>Reason for Counselling <span class="text-danger">*</span></label>
                                        <textarea name="reason" class="form-control" rows="4" required
                                            placeholder="e.g. Academic concerns, behavioral issues, personal matters..."></textarea>
                                        <div class="invalid-feedback">Please provide a reason.</div>
                                    </div>
                                </div>

                                <div class="card-footer text-left pt-5 mt-3">
                                    <button class="btn btn-primary" type="submit">Schedule Session</button>
                                    <a href="{{ route('counsellor.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.footer')

        <!-- Select2 CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        
        <!-- Select2 JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

        <!-- Validation Script -->
        <script>
            (function() {
                'use strict';
                
                // Initialize Select2
                $(document).ready(function() {
                    $('#student-select').select2({
                        placeholder: 'Search for a student...',
                        allowClear: true,
                        width: '100%'
                    });
                });

                // Form validation
                window.addEventListener('load', function() {
                    const forms = document.getElementsByClassName('needs-validation');
                    Array.prototype.filter.call(forms, function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });
                }, false);
            })();
        </script>
    </div>
</body>