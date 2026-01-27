{{-- resources/views/misc_fee_payment_create.blade.php --}}
@include('includes.head')

<head>
   
    {{-- Assuming Select2 CSS is included here or in head include --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

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
                                <h4>Receive Miscellaneous Fee Payment</h4>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif

                                <form id="paymentForm" class="mx-2">
                                    @csrf
                                    <div class="form-card">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Section <span class="text-danger">*</span></label>
                                                    <select name="section_id" id="section_id" class="form-control @error('section_id') is-invalid @enderror" required>
                                                        <option value="">Select Section</option>
                                                        @foreach($sections as $section)
                                                            <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                                {{ $section->section_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('section_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Session <span class="text-danger">*</span></label>
                                                    <select name="session_id" id="session_id" class="form-control @error('session_id') is-invalid @enderror" required disabled>
                                                        <option value="">Select Session</option>
                                                    </select>
                                                    @error('session_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Class <span class="text-danger">*</span></label>
                                                    <select name="class_id" id="class_id" class="form-control @error('class_id') is-invalid @enderror" required disabled>
                                                        <option value="">Select Class</option>
                                                    </select>
                                                    @error('class_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Student <span class="text-danger">*</span></label>
                                                    <select name="student_id" id="student_id" class="form-control select2 @error('student_id') is-invalid @enderror" required disabled>
                                                        <option value="">Select Student</option>
                                                    </select>
                                                    @error('student_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Misc Fee Type <span class="text-danger">*</span></label>
                                                    <select name="misc_fee_type_id" id="fee_type_select" class="form-control select2 @error('misc_fee_type_id') is-invalid @enderror" required>
                                                        <option value="">Select Fee Type</option>
                                                        @foreach($feeTypes as $feeType)
                                                            <option value="{{ $feeType->id }}" data-amount="{{ $feeType->amount }}" {{ old('misc_fee_type_id') == $feeType->id ? 'selected' : '' }}>
                                                                {{ $feeType->name }} - ₦{{ number_format($feeType->amount, 2) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('misc_fee_type_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Amount Paid (₦) <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control @error('amount_paid') is-invalid @enderror" 
                                                           value="{{ old('amount_paid') }}" required min="0">
                                                    @error('amount_paid')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Payment Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" 
                                                           value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                                                    @error('payment_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 text-right">
                                           
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="fas fa-save"></i> Record Payment
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    {{-- Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for fee type
            $('#fee_type_select').select2({
                placeholder: "Search for Fee Type...",
                allowClear: true
            });

            // Function to initialize Select2 for student select
            function initStudentSelect2() {
                $('#student_id').select2({
                    placeholder: "Search for Student...",
                    allowClear: true
                });
            }

            // Auto-fill amount based on fee type selection
            $('#fee_type_select').on('select2:select', function (e) {
                var amount = $(e.params.data.element).data('amount');
                if (amount) {
                    $('#amount_paid').val(amount);
                }
            });

            // Dynamic loading on section change
            $('#section_id').change(function() {
                var sectionId = $(this).val();
                if (sectionId) {
                    // Load sessions
                    $.get('{{ url("/misc-fee/payments/sessions") }}/' + sectionId, function(data) {
                        var sessionSelect = $('#session_id');
                        sessionSelect.empty().append('<option value="">Select Session</option>');
                        $.each(data, function(key, session) {
                            sessionSelect.append('<option value="' + session.id + '">' + session.name + (session.is_current ? ' (Current)' : '') + '</option>');
                        });
                        sessionSelect.prop('disabled', false);
                    });

                    // Load classes
                    $.get('{{ url("/misc-fee/payments/classes") }}/' + sectionId, function(data) {
                        var classSelect = $('#class_id');
                        classSelect.empty().append('<option value="">Select Class</option>');
                        $.each(data, function(key, classItem) {
                            classSelect.append('<option value="' + classItem.id + '">' + classItem.name + '</option>');
                        });
                        classSelect.prop('disabled', false);
                    });

                    // Clear dependent selects
                    $('#session_id').val('').prop('disabled', true);
                    $('#class_id').val('').prop('disabled', true);
                    $('#student_id').empty().append('<option value="">Select Student</option>').val('').trigger('change');
                } else {
                    // Clear all
                    $('#session_id, #class_id, #student_id').empty().append('<option value="">Select ...</option>').val('').prop('disabled', true).trigger('change');
                }
            });

            // Dynamic loading on session change - if needed, but currently not affecting students
            $('#session_id').change(function() {
                var sessionId = $(this).val();
                // If session affects classes or students, add logic here
                // For now, no additional action
            });

            // Dynamic loading on class change
            $('#class_id').change(function() {
                var classId = $(this).val();
                if (classId) {
                    // Load students
                    $.get('{{ url("/misc-fee/payments/students") }}/' + classId, function(data) {
                        var studentSelect = $('#student_id');
                        // Destroy Select2 if initialized
                        if (studentSelect.hasClass('select2-hidden-accessible')) {
                            studentSelect.select2('destroy');
                        }
                        studentSelect.empty().append('<option value="">Select Student</option>');
                        $.each(data, function(key, student) {
                            studentSelect.append('<option value="' + student.id + '">' + student.name + ' (' + (student.admission_no || 'N/A') + ')</option>');
                        });
                        studentSelect.prop('disabled', false);
                        // Reinitialize Select2
                        initStudentSelect2();
                    }).fail(function() {
                        console.error('Failed to fetch students');
                    });
                } else {
                    var studentSelect = $('#student_id');
                    if (studentSelect.hasClass('select2-hidden-accessible')) {
                        studentSelect.select2('destroy');
                    }
                    studentSelect.empty().append('<option value="">Select Student</option>').prop('disabled', true).val('').trigger('change');
                }
            });

            // AJAX Form Submission to prevent duplicates and handle receipt in new tab
            $('#paymentForm').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var submitBtn = $('#submitBtn');
                var originalText = submitBtn.html();
                
                // Disable submit button to prevent duplicates
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                
                // Clear previous alerts
                $('.alert').remove();
                
                $.ajax({
                    url: form.attr('action') || '{{ route("misc.fee.payments.store") }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            // Show success alert on current page
                            var successAlert = '<div class="alert alert-success alert-dismissible fade show">' +
                                '<i class="fas fa-check-circle"></i> ' + response.message +
                                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                '</div>';
                            $('.card-body').prepend(successAlert);
                            
                            // Reset form
                            form[0].reset();
                            $('#section_id').trigger('change'); // Reset selects
                            $('#fee_type_select').val('').trigger('change');
                            $('#student_id').val('').trigger('change');
                            
                            // Open receipt PDF in new tab for printing
                            window.open(response.receipt_url, '_blank');
                        } else {
                            // Show error alert
                            var errorAlert = '<div class="alert alert-danger alert-dismissible fade show">' +
                                '<i class="fas fa-exclamation-circle"></i> ' + response.message +
                                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                '</div>';
                            $('.card-body').prepend(errorAlert);
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'An error occurred while processing the request.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        var errorAlert = '<div class="alert alert-danger alert-dismissible fade show">' +
                            '<i class="fas fa-exclamation-circle"></i> ' + errorMsg +
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                            '</div>';
                        $('.card-body').prepend(errorAlert);
                    },
                    complete: function() {
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Auto-dismiss alerts after 5 seconds
            $(document).on('DOMNodeInserted', function(e) {
                if ($(e.target).hasClass('alert')) {
                    setTimeout(function() {
                        $(e.target).fadeOut('slow');
                    }, 5000);
                }
            });
        });
    </script>
</body>