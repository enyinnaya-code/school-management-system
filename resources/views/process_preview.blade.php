{{-- resources/views/payroll/process_preview.blade.php --}}
@include('includes.head')

<head>
    <style>
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table {
            min-width: 1300px;
        }
        .editable-cell {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .editable-cell:hover {
            background-color: #f8f9fa;
        }
        .editable-input {
            width: 100%;
            border: 1px solid #6777ef;
            padding: 5px;
            border-radius: 3px;
        }
        .total-column {
            background-color: #f0f9ff;
            font-weight: bold;
        }
        .net-pay-column {
            background-color: #f0fdf4;
            font-weight: bold;
            color: #059669;
        }
        @media print {
            .no-print { display: none !important; }
            .table-wrapper { overflow-x: visible; }
            .card { border: none; box-shadow: none; }
            .editable-cell { cursor: default; }
            .editable-cell:hover { background-color: transparent; }
            @page { size: landscape; margin: 1cm; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .main-content { padding: 0 !important; margin: 0 !important; }
            .card-body { padding: 0 !important; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>

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
                            <div class="card-header d-flex justify-content-between align-items-center no-print">
                                <h4>
                                    {{ $isProcessed ? 'Edit' : 'Process' }} Salary for 
                                    <strong>{{ $displaySection->section_name }}</strong> 
                                    - {{ $term->name }} - {{ date('F', mktime(0, 0, 0, $month, 10)) }}
                                    @if($isProcessed)
                                        <span class="badge badge-success">Processed</span>
                                    @endif
                                </h4>
                                <div class="card-header-action">
                                    <a href="{{ route('finance.payroll.process') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <button class="btn btn-info ml-2" onclick="exportToExcel()">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </button>
                                    <button class="btn btn-primary ml-2" onclick="printTable()">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                </div>
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

                                @if(empty($payrollData))
                                    <div class="alert alert-warning text-center">
                                        <i class="fas fa-exclamation-triangle"></i> No payroll records found for the selected criteria.
                                    </div>
                                @else
                                    <div class="alert alert-info no-print">
                                        <i class="fas fa-info-circle"></i> Click on any cell to edit values. 
                                        @if($isProcessed)
                                            This salary has been processed. You can make edits and update.
                                        @else
                                            Changes are temporary until you click "Confirm & Process".
                                        @endif
                                    </div>

                                    <!-- Print Header -->
                                    <div style="display: none;" id="printHeader">
                                        <div class="text-center mb-4">
                                            <h3>{{ $displaySection->section_name }}</h3>
                                            <h4>Salary Payment for {{ date('F Y', mktime(0, 0, 0, $month, 10)) }}</h4>
                                            <p>{{ $term->name }} Term</p>
                                        </div>
                                    </div>

                                    <form action="{{ route('finance.payroll.confirm-process') }}" method="POST" id="payrollForm">
                                        @csrf
                                        <input type="hidden" name="section_id" value="{{ $request->section_id ?? 0 }}">
                                        <input type="hidden" name="term_id" value="{{ $term->id }}">
                                        <input type="hidden" name="month" value="{{ $month }}">

                                        <div class="table-wrapper">
                                            <table class="table table-bordered table-hover" id="payrollTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Staff Name</th>
                                                        <th>Basic Salary</th>
                                                        <th>Allowances</th>
                                                        <th>Total</th>
                                                        <th>Deductions</th>
                                                        <th>Net Pay</th>
                                                        <th>Description</th>
                                                        <th>Section</th>
                                                        <th>Bank Name</th>
                                                        <th>Account Number</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $counter = 1; @endphp
                                                    @foreach($payrollData as $index => $data)
                                                        <tr data-id="{{ $data['payroll_id'] }}">
                                                            <td>{{ $counter++ }}</td>
                                                            <td>{{ $data['employee_name'] }}</td>
                                                            <td class="editable-cell" data-field="basic_salary" data-value="{{ $data['basic_salary'] }}">
                                                                ₦{{ number_format($data['basic_salary'], 2) }}
                                                                <input type="hidden" name="payrolls[{{ $index }}][id]" value="{{ $data['payroll_id'] }}">
                                                                <input type="hidden" name="payrolls[{{ $index }}][basic_salary]" value="{{ $data['basic_salary'] }}" class="form-basic-salary">
                                                            </td>
                                                            <td class="editable-cell" data-field="allowances" data-value="{{ $data['allowances'] }}">
                                                                ₦{{ number_format($data['allowances'], 2) }}
                                                                <input type="hidden" name="payrolls[{{ $index }}][allowances]" value="{{ $data['allowances'] }}" class="form-allowances">
                                                            </td>
                                                            <td class="total-column total-cell">
                                                                ₦{{ number_format($data['basic_salary'] + $data['allowances'], 2) }}
                                                            </td>
                                                            <td class="editable-cell" data-field="deductions" data-value="{{ $data['deductions'] }}">
                                                                ₦{{ number_format($data['deductions'], 2) }}
                                                                <input type="hidden" name="payrolls[{{ $index }}][deductions]" value="{{ $data['deductions'] }}" class="form-deductions">
                                                            </td>
                                                            <td class="net-pay-column net-pay-cell">
                                                                ₦{{ number_format(($data['basic_salary'] + $data['allowances']) - $data['deductions'], 2) }}
                                                            </td>
                                                            <td class="editable-cell" data-field="description" data-value="{{ $data['description'] }}">
                                                                {{ $data['description'] }}
                                                                <input type="hidden" name="payrolls[{{ $index }}][description]" value="{{ $data['description'] }}" class="form-description">
                                                            </td>
                                                            <td>{{ $data['section_name'] }}</td>
                                                            <td class="editable-cell" data-field="bank_name" data-value="{{ $data['bank_name'] }}">
                                                                {{ $data['bank_name'] }}
                                                                <input type="hidden" name="payrolls[{{ $index }}][bank_name]" value="{{ $data['bank_name'] }}" class="form-bank-name">
                                                            </td>
                                                            <td class="editable-cell" data-field="account_number" data-value="{{ $data['account_number'] }}">
                                                                {{ $data['account_number'] }}
                                                                <input type="hidden" name="payrolls[{{ $index }}][account_number]" value="{{ $data['account_number'] }}" class="form-account-number">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="font-weight-bold">
                                                    <tr>
                                                        <td colspan="2" class="text-right">TOTAL:</td>
                                                        <td id="totalBasicSalary">₦0.00</td>
                                                        <td id="totalAllowances">₦0.00</td>
                                                        <td id="totalGross">₦0.00</td>
                                                        <td id="totalDeductions">₦0.00</td>
                                                        <td id="totalNetPay">₦0.00</td>
                                                        <td colspan="4"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <div class="text-center mt-4 no-print">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-check"></i> 
                                                @if($isProcessed)
                                                    Update Salary Payment
                                                @else
                                                    Confirm & Process Salary
                                                @endif
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
        @include('includes.edit_footer')
    </div>

   <script>
    $(document).ready(function() {
        // Calculate initial totals
        calculateGrandTotals();
        
        // Make cells editable
        $('.editable-cell').on('click', function() {
            const cell = $(this);
            const field = cell.data('field');
            const currentValue = cell.data('value');
            const isNumeric = ['basic_salary', 'allowances', 'deductions'].includes(field);
            
            if (cell.find('input:not([type="hidden"])').length > 0) return;
            
            const input = $('<input>')
                .addClass('editable-input')
                .attr('type', isNumeric ? 'number' : 'text')
                .val(currentValue);
            
            if (isNumeric) {
                input.attr('step', '0.01').attr('min', '0');
            }
            
            const hiddenInputs = cell.find('input[type="hidden"]').detach();
            
            cell.html(input);
            cell.append(hiddenInputs);
            
            input.focus().select();
            
            const saveEdit = function() {
                const newValue = input.val();
                const row = cell.closest('tr');
                
                cell.data('value', newValue);
                
                const fieldClass = 'form-' + field.replace('_', '-');
                cell.find('.' + fieldClass).val(newValue);
                
                if (isNumeric) {
                    const numValue = parseFloat(newValue) || 0;
                    cell.html('₦' + numValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    calculateRowTotals(row);
                    calculateGrandTotals();
                } else {
                    cell.html(newValue);
                }
                
                cell.append(hiddenInputs);
            };
            
            input.on('blur', saveEdit);
            input.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    saveEdit();
                }
            });
            
            input.on('keydown', function(e) {
                if (e.which === 27) {
                    const displayValue = isNumeric 
                        ? '₦' + parseFloat(currentValue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                        : currentValue;
                    cell.html(displayValue);
                    cell.append(hiddenInputs);
                }
            });
        });
        
        function calculateRowTotals(row) {
            const basicSalary = parseFloat(row.find('[data-field="basic_salary"]').data('value')) || 0;
            const allowances = parseFloat(row.find('[data-field="allowances"]').data('value')) || 0;
            const deductions = parseFloat(row.find('[data-field="deductions"]').data('value')) || 0;
            
            const total = basicSalary + allowances;
            const netPay = total - deductions;
            
            row.find('.total-cell').html('₦' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            row.find('.net-pay-cell').html('₦' + netPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }
        
        function calculateGrandTotals() {
            let totalBasic = 0, totalAllow = 0, totalGross = 0, totalDeduct = 0, totalNet = 0;
            
            $('#payrollTable tbody tr').each(function() {
                const row = $(this);
                const basic = parseFloat(row.find('[data-field="basic_salary"]').data('value')) || 0;
                const allow = parseFloat(row.find('[data-field="allowances"]').data('value')) || 0;
                const deduct = parseFloat(row.find('[data-field="deductions"]').data('value')) || 0;
                
                totalBasic += basic;
                totalAllow += allow;
                totalGross += (basic + allow);
                totalDeduct += deduct;
                totalNet += ((basic + allow) - deduct);
            });
            
            $('#totalBasicSalary').text('₦' + totalBasic.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#totalAllowances').text('₦' + totalAllow.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#totalGross').text('₦' + totalGross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#totalDeductions').text('₦' + totalDeduct.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#totalNetPay').text('₦' + totalNet.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }
        
        $('#payrollForm').on('submit', function(e) {
            const isProcessed = {{ $isProcessed ? 'true' : 'false' }};
            const message = isProcessed 
                ? 'Are you sure you want to update these salary payments?' 
                : 'Are you sure you want to process these salary payments? This action will save the data to the database.';
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
            
            $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        });
        
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
    
    // Export to Excel — Now uses safe section name
    function exportToExcel() {
        const wb = XLSX.utils.book_new();
        const data = [];
        
        // Headers
        data.push(['#', 'Staff Name', 'Basic Salary', 'Allowances', 'Total', 'Deductions', 'Net Pay', 'Description', 'Section', 'Bank Name', 'Account Number']);
        
        // Data rows
        let rowNum = 1;
        $('#payrollTable tbody tr').each(function() {
            const row = $(this);
            data.push([
                rowNum++,
                row.find('td:eq(1)').text().trim(),
                parseFloat(row.find('[data-field="basic_salary"]').data('value')) || 0,
                parseFloat(row.find('[data-field="allowances"]').data('value')) || 0,
                parseFloat(row.find('[data-field="basic_salary"]').data('value') || 0) + parseFloat(row.find('[data-field="allowances"]').data('value') || 0),
                parseFloat(row.find('[data-field="deductions"]').data('value')) || 0,
                (parseFloat(row.find('[data-field="basic_salary"]').data('value') || 0) + parseFloat(row.find('[data-field="allowances"]').data('value') || 0)) - parseFloat(row.find('[data-field="deductions"]').data('value') || 0),
                row.find('[data-field="description"]').data('value'),
                row.find('td:eq(8)').text().trim(),
                row.find('[data-field="bank_name"]').data('value'),
                "'" + row.find('[data-field="account_number"]').data('value') // Force text format
            ]);
        });
        
        // Totals row
        data.push([
            '', 'TOTAL:',
            parseFloat($('#totalBasicSalary').text().replace(/[₦,]/g, '')) || 0,
            parseFloat($('#totalAllowances').text().replace(/[₦,]/g, '')) || 0,
            parseFloat($('#totalGross').text().replace(/[₦,]/g, '')) || 0,
            parseFloat($('#totalDeductions').text().replace(/[₦,]/g, '')) || 0,
            parseFloat($('#totalNetPay').text().replace(/[₦,]/g, '')) || 0,
            '', '', '', ''
        ]);
        
        const ws = XLSX.utils.aoa_to_sheet(data);
        ws['!cols'] = [
            {wch: 5}, {wch: 25}, {wch: 15}, {wch: 15}, {wch: 15}, 
            {wch: 15}, {wch: 15}, {wch: 20}, {wch: 20}, {wch: 20}, {wch: 20}
        ];
        
        XLSX.utils.book_append_sheet(wb, ws, 'Salary Payment');
        
        // SAFE: Use displaySection name (passed safely via Blade)
        const sectionName = "{{ addslashes($displaySection->section_name) }}";
        const monthYear = "{{ date('F_Y', mktime(0, 0, 0, $month, 10)) }}";
        const filename = `Salary_Payment_${sectionName}_${monthYear}.xlsx`;
        
        XLSX.writeFile(wb, filename);
    }
    
    function printTable() {
        document.getElementById('printHeader').style.display = 'block';
        const printArea = document.getElementById('printHeader').outerHTML + 
                         document.querySelector('.table-wrapper').outerHTML;
        
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = printArea;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload(); // Ensures page resets properly
    }
</script>
</body>