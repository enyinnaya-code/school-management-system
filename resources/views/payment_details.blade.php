{{-- resources/views/payment_details.blade.php --}}
@include('includes.head')

<style>
    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
    }

    .payment-history-table {
        margin-bottom: 0;
    }

    .modal-footer .pagination {
        margin: 0;
    }
</style>

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

                            {{-- Session just paid: show warning and stop rendering the form --}}
                            @if (session('just_paid'))
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <h4><i class="fas fa-exclamation-triangle"></i> Payment Session Ended</h4>
                                    <p>
                                        A payment has already been recorded for this student in this session.
                                        The form below has been disabled to prevent duplicate entries.
                                    </p>
                                    <p>
                                        To record another payment, please start from the beginning.
                                    </p>
                                    <div class="mt-4">
                                        <a href="{{ route('payment.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Create New Payment
                                        </a>
                                        <a href="{{ route('bursar.dashboard') }}" class="btn btn-secondary">
                                            <i class="fas fa-tachometer-alt"></i> Back to Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                        const form = document.querySelector('form');
                                        if (form) {
                                            form.querySelectorAll('input, select, textarea, button').forEach(el => {
                                                el.disabled = true;
                                            });
                                            const submitBtn = form.querySelector('button[type="submit"]');
                                            if (submitBtn) {
                                                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Payment Already Recorded';
                                                submitBtn.classList.remove('btn-primary');
                                                submitBtn.classList.add('btn-secondary');
                                            }
                                        }
                                    });
                            </script>

                            {{-- Prevent the rest of the page from rendering --}}
                            @php return; @endphp
                            @endif

                            {{-- Normal page content starts here --}}
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Payment Details for {{ $student->name }} ({{ $student->admission_no }})</h4>
                                <div>
                                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal"
                                        data-target="#paymentHistoryModal">
                                        <i class="fas fa-history"></i> View Payment History
                                    </button>
                                    @if($prospectus)
                                    <a href="{{ route('fee.prospectus.preview', Crypt::encrypt($prospectus->id)) }}"
                                        class="btn btn-secondary btn-sm" target="_blank">
                                        <i class="fas fa-eye"></i> View Prospectus
                                    </a>
                                    @endif
                                </div>
                            </div>

                            <div class="card-body">
                                @if($prospectus)
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong>Section:</strong> {{ $section->section_name }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Class:</strong> {{ $class->name }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Term:</strong> {{ $currentTerm->name }} ({{ $currentTerm->session->name
                                        ?? 'N/A' }})
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <strong>Total Due:</strong> ₦{{ number_format($totalDue, 2) }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Amount Paid:</strong> ₦{{ number_format($paid, 2) }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Balance:</strong>
                                        <span class="text-{{ $balance > 0 ? 'danger' : 'success' }}">
                                            ₦{{ number_format($balance, 2) }}
                                        </span>
                                    </div>
                                </div>

                                @if($previousBalances->isNotEmpty())
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6>Previous Outstanding Balances</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Session</th>
                                                        <th>Term</th>
                                                        <th>Total Due</th>
                                                        <th>Paid</th>
                                                        <th>Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($previousBalances as $bal)
                                                    <tr>
                                                        <td>{{ $bal['session_name'] }}</td>
                                                        <td>{{ $bal['term_name'] }}</td>
                                                        <td>₦{{ number_format($bal['total'], 2) }}</td>
                                                        <td>₦{{ number_format($bal['paid'], 2) }}</td>
                                                        <td class="text-danger">₦{{ number_format($bal['balance'], 2) }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-info mb-4">
                                    No previous outstanding balances found.
                                </div>
                                @endif
                                @else
                                <div class="alert alert-warning">
                                    No fee prospectus found for this class, section, and term.
                                </div>
                                @endif

                                <!-- Payment Form -->
                                <!-- Payment Form -->
<form action="{{ route('bursar.processPayment') }}" method="POST"
    class="needs-validation px-3" novalidate id="paymentForm">
    @csrf
    <input type="hidden" name="student_id" value="{{ $student->id }}">
    <input type="hidden" name="section_id" value="{{ $section->id }}">
    <input type="hidden" name="class_id" value="{{ $class->id }}">
    <input type="hidden" name="term_id" value="{{ $currentTerm->id }}" id="selected_term_id">
    <input type="hidden" name="session_id" value="{{ $sessionId }}" id="selected_session_id">

    <!-- Payment Allocation Section -->
    <div class="alert alert-info mb-4">
        <h6><i class="fas fa-info-circle"></i> Payment Allocation</h6>
        <p class="mb-2">Choose how to allocate this payment:</p>
        
        <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_allocation" 
                   id="current_term" value="current" checked>
            <label class="form-check-label" for="current_term">
                <strong class="text-white">Current Term ({{ $currentTerm->name }})</strong>
                <br>
                <small class="text-white">
                    Balance: ₦{{ number_format($balance, 2) }}
                </small>
            </label>
        </div>

        @if($previousBalances->isNotEmpty())
        <div class="form-check mt-2">
            <input class="form-check-input" type="radio" name="payment_allocation" 
                   id="oldest_first" value="oldest">
            <label class="form-check-label" for="oldest_first">
                <strong>Oldest Outstanding Balance First</strong>
                <br>
                <small class="text-muted">
                    Will apply to: {{ $previousBalances->last()['session_name'] }} - {{ $previousBalances->last()['term_name'] }}
                    (₦{{ number_format($previousBalances->last()['balance'], 2) }})
                </small>
            </label>
        </div>

        <div class="form-check mt-2">
            <input class="form-check-input" type="radio" name="payment_allocation" 
                   id="custom_term" value="custom">
            <label class="form-check-label" for="custom_term">
                <strong>Select Specific Term</strong>
            </label>
        </div>

        <div id="custom_term_selector" class="mt-3" style="display: none;">
            <label>Select Session & Term:</label>
            <select class="form-control" id="custom_term_select">
                <option value="">-- Select a term to pay --</option>
                @foreach($previousBalances as $bal)
                <option value="{{ $bal['term_id'] }}" 
                        data-session="{{ $bal['session_id'] }}"
                        data-balance="{{ $bal['balance'] }}">
                    {{ $bal['session_name'] }} - {{ $bal['term_name'] }} 
                    (Balance: ₦{{ number_format($bal['balance'], 2) }})
                </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    <!-- Total Outstanding Summary -->
    @if($previousBalances->isNotEmpty())
    <div class="alert alert-warning mb-4">
        <h6><i class="fas fa-exclamation-triangle"></i> Outstanding Balance Summary</h6>
        <div class="row">
            <div class="col-md-4">
                <strong>Current Term:</strong> ₦{{ number_format($balance, 2) }}
            </div>
            <div class="col-md-4">
                <strong>Previous Terms:</strong> ₦{{ number_format($previousBalances->sum('balance'), 2) }}
            </div>
            <div class="col-md-4">
                <strong>Total Outstanding:</strong> 
                <span class="text-danger">₦{{ number_format($totalOutstanding, 2) }}</span>
            </div>
        </div>
    </div>
    @endif

    <div class="form-group row mb-3">
        <label class="col-md-3 col-form-label">Amount <span class="text-danger">*</span></label>
        <div class="col-md-9">
            <input type="number" class="form-control" name="amount" id="payment_amount" 
                   step="0.01" min="0" max="{{ $totalOutstanding > 0 ? $totalOutstanding : 0 }}" required>
            <small class="form-text text-muted" id="amount_helper">
                Maximum: ₦{{ number_format($balance > 0 ? $balance : 0, 2) }}
            </small>
            @error('amount')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="form-group row mb-3">
        <label class="col-md-3 col-form-label">Payment Type <span class="text-danger">*</span></label>
        <div class="col-md-9">
            <select class="form-control" name="payment_type" required>
                <option value="">Select payment type...</option>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Online Payment">Online Payment</option>
                <option value="Cheque">Cheque</option>
            </select>
            @error('payment_type')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="form-group row mb-3">
        <label class="col-md-3 col-form-label">Description (Optional)</label>
        <div class="col-md-9">
            <textarea class="form-control" name="description" rows="3" id="payment_description"></textarea>
            @error('description')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Record Payment
    </button>
    <a href="{{ route('payment.create') }}" class="btn btn-secondary">
        Back to Selection
    </a>
</form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Payment History Modal -->
    <!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" role="dialog"
    aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentHistoryModalLabel">
                    Payment History - {{ $student->name }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" id="paymentHistoryContent">
                <div class="table-responsive">
                    <table class="table table-striped payment-history-table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Session</th>
                                <th>Term</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <span class="badge badge-secondary">
                                        {{ $payment->term->session->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $payment->term->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td><strong>₦{{ number_format($payment->amount, 2) }}</strong></td>
                                <td><span class="badge badge-info">{{ $payment->payment_type }}</span></td>
                                <td>{{ $payment->description ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('bursar.payment.receipt', $payment->id) }}"
                                        class="btn btn-sm btn-primary" target="_blank" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No payments recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <strong>Total Paid (Current Term):</strong> ₦{{ number_format($paid, 2) }}<br>
                    <strong>All-Time Total:</strong> ₦{{ number_format($payments->sum('amount'), 2) }}
                </div>
                <div id="paginationContainer">
                    {{ $payments->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
        $(document).ready(function() {
            // Handle pagination clicks inside modal
            $('#paymentHistoryModal').on('click', '.pagination a', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');

                $('#paymentHistoryContent').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading...</p></div>');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        var $response = $(response);
                        var tableContent = $response.find('#paymentHistoryContent').html();
                        var paginationContent = $response.find('#paginationContainer').html();

                        $('#paymentHistoryContent').html(tableContent);
                        $('#paginationContainer').html(paginationContent);
                    },
                    error: function() {
                        $('#paymentHistoryContent').html('<div class="alert alert-danger m-3">Error loading payment history. Please try again.</div>');
                    }
                });
            });
        });

        // Extra safety: reload page if coming from back/forward cache
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>



<script>
$(document).ready(function() {
    // Handle payment allocation changes
    $('input[name="payment_allocation"]').change(function() {
        const selectedValue = $(this).val();
        const $customSelector = $('#custom_term_selector');
        const $amountHelper = $('#amount_helper');
        const $paymentAmount = $('#payment_amount');
        const $description = $('#payment_description');
        
        if (selectedValue === 'current') {
            $customSelector.hide();
            $('#selected_term_id').val('{{ $currentTerm->id }}');
            $('#selected_session_id').val('{{ $sessionId }}');
            $paymentAmount.attr('max', {{ $balance > 0 ? $balance : 0 }});
            $amountHelper.text('Maximum: ₦{{ number_format($balance > 0 ? $balance : 0, 2) }}');
            $description.val('');
        } else if (selectedValue === 'oldest') {
            $customSelector.hide();
            @if($previousBalances->isNotEmpty())
            const oldestTerm = {{ $previousBalances->last()['term_id'] }};
            const oldestSession = {{ $previousBalances->last()['session_id'] }};
            const oldestBalance = {{ $previousBalances->last()['balance'] }};
            
            $('#selected_term_id').val(oldestTerm);
            $('#selected_session_id').val(oldestSession);
            $paymentAmount.attr('max', oldestBalance);
            $amountHelper.text('Maximum: ₦' + oldestBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $description.val('Payment for {{ $previousBalances->last()["session_name"] }} - {{ $previousBalances->last()["term_name"] }}');
            @endif
        } else if (selectedValue === 'custom') {
            $customSelector.show();
            $('#custom_term_select').val('');
            $amountHelper.text('Please select a term first');
        }
    });
    
    // Handle custom term selection
    $('#custom_term_select').change(function() {
        const $selected = $(this).find('option:selected');
        const termId = $(this).val();
        const sessionId = $selected.data('session');
        const balance = $selected.data('balance');
        const termText = $selected.text();
        
        if (termId) {
            $('#selected_term_id').val(termId);
            $('#selected_session_id').val(sessionId);
            $('#payment_amount').attr('max', balance);
            $('#amount_helper').text('Maximum: ₦' + balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#payment_description').val('Payment for ' + termText.split('(')[0].trim());
        }
    });
    
    // Form validation
    $('#paymentForm').on('submit', function(e) {
        if ($('input[name="payment_allocation"]:checked').val() === 'custom' && !$('#custom_term_select').val()) {
            e.preventDefault();
            alert('Please select a specific term for payment');
            return false;
        }
    });
});
</script>
<script>
    $(document).ready(function() {
            // Handle pagination clicks inside modal
            $('#paymentHistoryModal').on('click', '.pagination a', function(e) {
                e.preventDefault();
                
                var url = $(this).attr('href');
                
                // Show loading state
                $('#paymentHistoryContent').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading...</p></div>');
                
                // Fetch paginated data
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        // Extract table content from response
                        var $response = $(response);
                        var tableContent = $response.find('#paymentHistoryContent').html();
                        var paginationContent = $response.find('#paginationContainer').html();
                        
                        // Update modal content
                        $('#paymentHistoryContent').html(tableContent);
                        $('#paginationContainer').html(paginationContent);
                    },
                    error: function() {
                        $('#paymentHistoryContent').html('<div class="alert alert-danger m-3">Error loading payment history. Please try again.</div>');
                    }
                });
            });

            // Keep modal open when clicking pagination
            $('#paymentHistoryModal').on('hidden.bs.modal', function (e) {
                // Only reset if not triggered by pagination
                if (!$(e.target).hasClass('pagination')) {
                    // Reset modal state if needed
                }
            });
        });
</script>

<style>
    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
    }

    .payment-history-table {
        margin-bottom: 0;
    }

    .modal-footer .pagination {
        margin: 0;
    }
    
    /* New styles for better readability */
    .payment-history-table th {
        white-space: nowrap;
    }
    
    .payment-history-table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }
</style>
</body>