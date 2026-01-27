@include('includes.head')

<head>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px);
        }

        .payroll-card {
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            margin-bottom: 1rem;
            background: #fff;
        }

        .payroll-card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
            border-radius: calc(0.35rem - 1px) calc(0.35rem - 1px) 0 0;
        }

        .payroll-card-body {
            padding: 1.25rem;
        }

        .payroll-row {
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .payroll-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .form-label-sm {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #5a5c69;
        }

        .remove-row-btn {
            margin-top: 1.75rem;
        }

        @media (max-width: 768px) {
            .payroll-card-body {
                padding: 1rem;
            }

            .payroll-row {
                padding-bottom: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .remove-row-btn {
                margin-top: 0.5rem;
            }
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
                            <form action="{{ route('finance.payroll.store') }}" method="POST" class="needs-validation"
                                novalidate id="payrollForm">
                                @csrf
                                <div class="card-header">
                                    <h4>Payroll Capture</h4>

                                </div>
                                <div class="card-body">
                                    <!-- Payroll Rows Container -->
                                    <div id="payrollContainer">
                                        <!-- Rows added dynamically -->
                                    </div>

                                    <!-- Add Row Button -->
                                    <div class="mt-4 pt-3 border-top">
                                        <button type="button" class="btn btn-success" id="addRowBtn">
                                            <i class="fas fa-plus"></i> Add Another Staff
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer text-left pt-4 mt-3">
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save"></i> Create Payroll
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
    let rowIndex = 0;

    // Data passed from controller
    const employees = {!! json_encode($employees) !!};
    const sections = {!! json_encode($sections) !!};

    // Nigerian Banks
    const nigerianBanks = [
        'Access Bank Plc', 'United Bank for Africa (UBA) Plc', 'Zenith Bank Plc',
        'First Bank of Nigeria Limited', 'Guaranty Trust Bank (GTBank) Plc',
        'Ecobank Nigeria Limited', 'Fidelity Bank Plc', 'First City Monument Bank (FCMB) Plc',
        'Keystone Bank Limited', 'Polaris Bank Limited', 'Stanbic IBTC Bank Plc',
        'Union Bank of Nigeria Plc', 'Wema Bank Plc', 'Providus Bank Limited',
        'Parallex Bank Limited'
    ];

    function getEmployeeOptions() {
        let options = '<option value="">-- Search Existing Staff --</option>';
        employees.forEach(emp => {
            options += `<option value="${emp.id}" data-name="${emp.name}" data-email="${emp.email}">
                ${emp.name} (${emp.email})
            </option>`;
        });
        return options;
    }

    function getBankOptions() {
        let options = '<option value="">-- Select Bank --</option>';
        nigerianBanks.forEach(bank => {
            options += `<option value="${bank}">${bank}</option>`;
        });
        return options;
    }

    function getSectionOptions() {
        let options = `
            <option value="">-- Select Section --</option>
            <option value="0">Not Applicable (e.g., Principal, Bursar)</option>
        `;
        sections.forEach(section => {
            options += `<option value="${section.id}">${section.section_name}</option>`;
        });
        return options;
    }

    function initializeSelect2(selectElement, placeholder) {
        selectElement.select2({
            theme: 'bootstrap-5',
            placeholder: placeholder,
            allowClear: true,
            width: '100%'
        });
    }

    function createPayrollRowHTML(index) {
        return `
            <div class="payroll-card" id="payrollRow-${index}">
                <div class="payroll-card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Staff #${index + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-row" data-index="${index}" ${index === 0 ? 'disabled' : ''}>
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
                <div class="payroll-card-body">
                    <div class="row payroll-row">
                        <!-- Staff Type -->
                        <div class="col-md-6 col-lg-4 mb-3">
                            <label class="form-label-sm">Staff Type</label>
                            <select class="form-control staff-type-select">
                                <option value="existing" selected>Existing Staff (in system)</option>
                                <option value="external">External / Contract Staff</option>
                            </select>
                        </div>

                        <!-- Existing Staff Selection -->
                        <div class="col-md-6 col-lg-4 mb-3 existing-staff-fields">
                            <label class="form-label-sm">Select Staff</label>
                            <select class="form-control employee-select-row" data-row-index="${index}">
                                ${getEmployeeOptions()}
                            </select>
                            <input type="hidden" name="payrolls[${index}][employee_id]" class="employee-id" value="">
                        </div>

                        <!-- External Staff Name -->
                        <div class="col-md-6 col-lg-4 mb-3 external-staff-fields" style="display:none;">
                            <label class="form-label-sm">Full Name</label>
                            <input type="text" name="payrolls[${index}][staff_name]" class="form-control staff-name"
                                placeholder="e.g. Musa Ibrahim (Security)">
                        </div>

                        <!-- Other Fields -->
                        <div class="col-md-6 col-lg-4 mb-3">
                            <label class="form-label-sm">Basic Salary</label>
                            <input type="number" name="payrolls[${index}][basic_salary]" class="form-control basic-salary"
                                placeholder="0.00" step="0.01" value="0">
                        </div>

                        <div class="col-md-6 col-lg-4 mb-3">
                            <label class="form-label-sm">Allowances</label>
                            <input type="number" name="payrolls[${index}][allowances]" class="form-control allowances"
                                placeholder="0.00" step="0.01" value="0">
                        </div>

                        <div class="col-md-6 col-lg-4 mb-3">
                            <label class="form-label-sm">Section</label>
                            <select name="payrolls[${index}][section_id]" class="form-control section-select">
                                ${getSectionOptions()}
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-3">
                            <label class="form-label-sm">Bank Name</label>
                            <select name="payrolls[${index}][bank_name]" class="form-control bank-select">
                                ${getBankOptions()}
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-3">
                            <label class="form-label-sm">Account Number</label>
                            <input type="text" name="payrolls[${index}][account_number]" class="form-control account-number"
                                placeholder="Enter Account Number">
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function addPayrollRow() {
        const rowHTML = createPayrollRowHTML(rowIndex);
        $('#payrollContainer').append(rowHTML);

        const $row = $(`#payrollRow-${rowIndex}`);
        const $employeeSelect = $row.find('.employee-select-row');
        const $bankSelect = $row.find('.bank-select');
        const $sectionSelect = $row.find('.section-select');

        initializeSelect2($employeeSelect, '-- Search Existing Staff --');
        initializeSelect2($bankSelect, '-- Select Bank --');
        initializeSelect2($sectionSelect, '-- Select Section --');

        rowIndex++;
        updateRowNumbers();
    }

    function updateRowNumbers() {
        $('#payrollContainer .payroll-card').each(function(index) {
            $(this).find('.payroll-card-header h6').text(`Staff #${index + 1}`);
            $(this).find('.remove-row').prop('disabled', index === 0);
        });
    }

    // Toggle between existing and external staff
    $(document).on('change', '.staff-type-select', function() {
        const $row = $(this).closest('.payroll-card');
        const type = $(this).val();

        if (type === 'external') {
            $row.find('.existing-staff-fields').hide();
            $row.find('.external-staff-fields').show();
            $row.find('.employee-select-row').val(null).trigger('change');
            $row.find('.employee-id').val('');
        } else {
            $row.find('.existing-staff-fields').show();
            $row.find('.external-staff-fields').hide();
        }
    });

    // Prevent duplicate existing employees
    $(document).on('change', '.employee-select-row', function() {
        const selectedId = $(this).val();
        const $currentRow = $(this).closest('.payroll-card');
        const $currentHidden = $currentRow.find('.employee-id');

        if (selectedId) {
            let duplicate = false;
            $('.employee-id').each(function() {
                if ($(this).val() == selectedId && $(this).closest('.payroll-card')[0] !== $currentRow[0]) {
                    duplicate = true;
                }
            });

            if (duplicate) {
                alert('This staff member has already been added!');
                $(this).val(null).trigger('change');
                $currentHidden.val('');
                return;
            }
        }

        $currentHidden.val(selectedId || '');
    });

    // Add initial row
    addPayrollRow();

    // Add new row
    $('#addRowBtn').on('click', addPayrollRow);

    // Remove row
    $(document).on('click', '.remove-row', function(e) {
        e.preventDefault();
        if ($('#payrollContainer .payroll-card').length === 1) {
            alert('You must have at least one staff record.');
            return;
        }
        $(this).closest('.payroll-card').remove();
        updateRowNumbers();
    });

    // Enhanced form validation with better error handling
    $('#payrollForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default submission first
        
        let valid = true;
        let errorMessages = [];

        // Clear any previous error highlights
        $('.is-invalid').removeClass('is-invalid');

        $('#payrollContainer .payroll-card').each(function(index) {
            const $row = $(this);
            const staffType = $row.find('.staff-type-select').val();
            const employeeId = $row.find('.employee-id').val();
            const staffName = $row.find('.staff-name').val()?.trim();
            const basicSalary = parseFloat($row.find('.basic-salary').val()) || 0;
            const section = $row.find('.section-select').val();
            const bank = $row.find('.bank-select').val();
            const account = $row.find('.account-number').val()?.trim();

            // Validate staff selection
            if (staffType === 'existing' && !employeeId) {
                errorMessages.push(`Please select a staff member for Staff #${index + 1}`);
                $row.find('.employee-select-row').next('.select2-container').addClass('is-invalid');
                valid = false;
            }

            if (staffType === 'external' && !staffName) {
                errorMessages.push(`Please enter a full name for external staff #${index + 1}`);
                $row.find('.staff-name').addClass('is-invalid');
                valid = false;
            }

            // Validate basic salary
            if (!basicSalary || basicSalary <= 0) {
                errorMessages.push(`Please enter a valid basic salary for Staff #${index + 1}`);
                $row.find('.basic-salary').addClass('is-invalid');
                valid = false;
            }

            // Validate section
            if (!section) {
                errorMessages.push(`Please select a section for Staff #${index + 1}`);
                $row.find('.section-select').next('.select2-container').addClass('is-invalid');
                valid = false;
            }

            // Validate bank details
            if (!bank) {
                errorMessages.push(`Please select a bank for Staff #${index + 1}`);
                $row.find('.bank-select').next('.select2-container').addClass('is-invalid');
                valid = false;
            }

            if (!account) {
                errorMessages.push(`Please enter an account number for Staff #${index + 1}`);
                $row.find('.account-number').addClass('is-invalid');
                valid = false;
            }
        });

        if (!valid) {
            // Show all error messages
            const errorHtml = '<strong>Please fix the following errors:</strong><ul>' + 
                errorMessages.map(msg => `<li>${msg}</li>`).join('') + 
                '</ul>';
            
            // Scroll to top to show errors
            $('html, body').animate({ scrollTop: 0 }, 300);
            
            // Show error alert if not already present
            if ($('.alert-danger').length === 0) {
                $('.card').before(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${errorHtml}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            }
            
            return false;
        }

        // If validation passes, disable submit button and submit form
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        this.submit();
    });
});
    </script>
</body>