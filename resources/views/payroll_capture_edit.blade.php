{{-- resources/views/payrolls/edit.blade.php --}}
@include('includes.head')

<head>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px);
        }
    </style>
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
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Validation Error!</strong>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="card">
                            <form action="{{ route('finance.payroll.update', $payroll) }}" method="POST" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="card-header">
                                    <h4>Edit Payroll</h4>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <!-- Staff Type Selection -->
                                        <div class="col-md-6 mb-3">
                                            <label for="staff_type" class="form-label">Staff Type</label>
                                            <select id="staff_type" class="form-control form-control-sm">
                                                <option value="existing" {{ $payroll->employee_id ? 'selected' : '' }}>
                                                    Existing Staff (in system)
                                                </option>
                                                <option value="external" {{ $payroll->staff_name ? 'selected' : '' }}>
                                                    External / Contract Staff
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Existing Staff Selection -->
                                        <div class="col-md-6 mb-3" id="existing_staff_field" style="display: {{ $payroll->employee_id ? 'block' : 'none' }};">
                                            <label for="employee_id" class="form-label">Select Staff</label>
                                            <select name="employee_id" id="employee_id" class="form-control form-control-sm employee-select">
                                                <option value="">-- Search Existing Staff --</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->id }}" {{ $payroll->employee_id == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->name }} ({{ $employee->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- External Staff Name -->
                                        <div class="col-md-6 mb-3" id="external_staff_field" style="display: {{ $payroll->staff_name ? 'block' : 'none' }};">
                                            <label for="staff_name" class="form-label">Full Name</label>
                                            <input type="text" name="staff_name" id="staff_name" class="form-control form-control-sm" 
                                                   value="{{ old('staff_name', $payroll->staff_name) }}" 
                                                   placeholder="e.g. Musa Ibrahim (Security)">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="section_id" class="form-label">Section</label>
                                            <select name="section_id" id="section_id" class="form-control form-control-sm section-select" required>
                                                <option value="">-- Select Section --</option>
                                                
                                                <!-- Special option for administrative staff -->
                                                <option value="0" {{ $payroll->section_id == 0 ? 'selected' : '' }}>
                                                    Not Applicable (Administrative Staff - e.g., Principal, Bursar)
                                                </option>
                                                
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{ $payroll->section_id == $section->id ? 'selected' : '' }}>
                                                        {{ $section->section_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="basic_salary" class="form-label">Basic Salary</label>
                                            <input type="number" name="basic_salary" id="basic_salary" class="form-control form-control-sm" 
                                                   value="{{ old('basic_salary', $payroll->basic_salary) }}" step="0.01" required>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="allowances" class="form-label">Allowances</label>
                                            <input type="number" name="allowances" id="allowances" class="form-control form-control-sm" 
                                                   value="{{ old('allowances', $payroll->allowances ?? 0) }}" step="0.01">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="bank_name" class="form-label">Bank Name</label>
                                            <select name="bank_name" id="bank_name" class="form-control form-control-sm bank-select" required>
                                                <option value="">-- Select Bank --</option>
                                                @php
                                                    $nigerianBanks = [
                                                        'Access Bank Plc',
                                                        'United Bank for Africa (UBA) Plc',
                                                        'Zenith Bank Plc',
                                                        'First Bank of Nigeria Limited',
                                                        'Guaranty Trust Bank (GTBank) Plc',
                                                        'Ecobank Nigeria Limited',
                                                        'Fidelity Bank Plc',
                                                        'First City Monument Bank (FCMB) Plc',
                                                        'Keystone Bank Limited',
                                                        'Polaris Bank Limited',
                                                        'Stanbic IBTC Bank Plc',
                                                        'Union Bank of Nigeria Plc',
                                                        'Wema Bank Plc',
                                                        'Providus Bank Limited',
                                                        'Parallex Bank Limited'
                                                    ];
                                                @endphp
                                                @foreach($nigerianBanks as $bank)
                                                    <option value="{{ $bank }}" {{ $payroll->bank_name == $bank ? 'selected' : '' }}>
                                                        {{ $bank }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-7 mb-3">
                                            <label for="account_number" class="form-label">Bank Account Number</label>
                                            <input type="text" name="account_number" id="account_number" class="form-control form-control-sm" 
                                                   value="{{ old('account_number', $payroll->account_number) }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-left pt-4 mt-3">
                                    <a href="{{ route('finance.payroll.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Payroll
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.employee-select').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Search Existing Staff --',
                allowClear: true,
                width: '100%'
            });

            $('.section-select').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Select Section --',
                allowClear: true,
                width: '100%'
            });

            $('.bank-select').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Select Bank --',
                allowClear: true,
                width: '100%'
            });

            // Toggle between existing and external staff
            $('#staff_type').on('change', function() {
                const staffType = $(this).val();
                
                if (staffType === 'existing') {
                    $('#existing_staff_field').show();
                    $('#external_staff_field').hide();
                    $('#staff_name').val('').removeAttr('required');
                    $('#employee_id').attr('required', 'required');
                } else {
                    $('#existing_staff_field').hide();
                    $('#external_staff_field').show();
                    $('#employee_id').val(null).trigger('change').removeAttr('required');
                    $('#staff_name').attr('required', 'required');
                }
            });

            // Form validation
            $('form').on('submit', function(e) {
                const staffType = $('#staff_type').val();
                const employeeId = $('#employee_id').val();
                const staffName = $('#staff_name').val()?.trim();

                if (staffType === 'existing' && !employeeId) {
                    e.preventDefault();
                    alert('Please select an existing staff member.');
                    return false;
                }

                if (staffType === 'external' && !staffName) {
                    e.preventDefault();
                    alert('Please enter the staff name for external/contract staff.');
                    return false;
                }
            });
        });
    </script>
</body>