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
                                <form action="{{ route('bursar.processPayment') }}" method="POST"
                                    class="needs-validation px-3" novalidate>
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                                    <input type="hidden" name="class_id" value="{{ $class->id }}">
                                    <input type="hidden" name="term_id" value="{{ $currentTerm->id }}">
                                    <input type="hidden" name="session_id" value="{{ $sessionId }}">

                                    <div class="form-group row mb-3">
                                        <label class="col-md-3 col-form-label">Amount</label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control" name="amount" step="0.01" min="0"
                                                max="{{ $balance > 0 ? $balance : 0 }}" required>
                                            @error('amount')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row mb-3">
                                        <label class="col-md-3 col-form-label">Payment Type</label>
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
                                            <textarea class="form-control" name="description" rows="3"></textarea>
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
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" role="dialog"
        aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
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
                                    <td colspan="5" class="text-center text-muted py-4">No payments recorded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <strong>Total Paid:</strong> ₦{{ number_format($paid, 2) }}
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

<!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" role="dialog" aria-labelledby="paymentHistoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
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
                                <td colspan="5" class="text-center text-muted py-4">No payments recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <strong>Total Paid:</strong> ₦{{ number_format($paid, 2) }}
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
</body>