<!-- resources/views/hostels/allocate.blade.php -->

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
                            <form action="{{ route('hostels.allocate.store') }}" method="POST" class="needs-validation" novalidate>
                                @csrf

                                <div class="card-header">
                                    <h4>Allocate Hostels</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group col-md-6 px-0">
                                        <label>Select Hostel</label>
                                        <select name="hostel_id" id="hostel_select" class="form-control" required style="width: 100%;">
                                            <option value="">Select Hostel</option>
                                            @foreach($hostels as $hostel)
                                                <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a hostel.
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Select Students</label>
                                        <select name="student_ids[]" id="student_select" class="form-control" multiple required style="width: 100%;">
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" 
                                                    @if($student->hostel_id) disabled @endif>
                                                    {{ $student->name }} ({{ $student->admission_no ?? 'N/A' }})
                                                    @if($student->hostel_id)
                                                        - Already in {{ $student->hostel->name ?? 'a hostel' }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select at least one student.
                                        </div>
                                        <small class="form-text text-muted">Students already allocated to hostels are disabled.</small>
                                    </div>
                                </div>
                                <div class="card-footer text-left pt-5 mt-3">
                                    <button class="btn btn-primary" type="submit">Allocate</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- jQuery (required for Select2) -->
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Validation & Select2 Initialization Script -->
    <script>
        $(document).ready(function() {
            // Initialize Select2 for hostel
            $('#hostel_select').select2({
                placeholder: 'Select a hostel',
                allowClear: true,
                width: '100%'
            });

            // Initialize Select2 for students
            $('#student_select').select2({
                placeholder: 'Select students to allocate',
                allowClear: true,
                width: '100%'
            });

            // Form validation
            (function() {
                'use strict';
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
            })();
        });
    </script>
</body>