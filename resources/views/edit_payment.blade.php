{{-- resources/views/edit_payment.blade.php --}}
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
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Edit Payment for {{ $student->name }} ({{ $student->admission_no }})</h4>
                                        @if($prospectus)
                                        <a href="{{ route('fee.prospectus.preview', Crypt::encrypt($prospectus->id)) }}" class="btn btn-info btn-sm float-right" target="_blank">
                                            <i class="fas fa-eye"></i> View Prospectus
                                        </a>
                                        @endif
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
                                                <strong>Term:</strong> {{ $currentTerm->name }} ({{ $currentTerm->session->name ?? 'N/A' }})
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
                                                <strong>Balance:</strong> <span class="text-{{ $balance > 0 ? 'danger' : 'success' }}">₦{{ number_format($balance, 2) }}</span>
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
                                                                <td class="text-danger">₦{{ number_format($bal['balance'], 2) }}</td>
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

                                        <!-- Edit Payment Form -->
                                        <form action="{{ route('payment.update', $payment->id) }}" method="POST" class="needs-validation" novalidate>
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                                            <input type="hidden" name="section_id" value="{{ $section->id }}">
                                            <input type="hidden" name="class_id" value="{{ $class->id }}">
                                            <input type="hidden" name="term_id" value="{{ $currentTerm->id }}">
                                            <input type="hidden" name="session_id" value="{{ $session->id }}">

                                            <div class="form-group row mb-3">
                                                <label class="col-md-3 col-form-label">Amount</label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control" name="amount" step="0.01" min="0" value="{{ old('amount', $payment->amount) }}" required>
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
                                                        <option value="Cash" {{ old('payment_type', $payment->payment_type) == 'Cash' ? 'selected' : '' }}>Cash</option>
                                                        <option value="Bank Transfer" {{ old('payment_type', $payment->payment_type) == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                        <option value="Online Payment" {{ old('payment_type', $payment->payment_type) == 'Online Payment' ? 'selected' : '' }}>Online Payment</option>
                                                        <option value="Cheque" {{ old('payment_type', $payment->payment_type) == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                                    </select>
                                                    @error('payment_type')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row mb-3">
                                                <label class="col-md-3 col-form-label">Description (Optional)</label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control" name="description" rows="3">{{ old('description', $payment->description) }}</textarea>
                                                    @error('description')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Update Payment
                                            </button>
                                            <a href="{{ route('payment.manage') }}" class="btn btn-secondary">Back to Manage Payments</a>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <!-- Payment History Table -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Payment History</h5>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Amount</th>
                                                        <th>Type</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($payments as $p)
                                                    <tr>
                                                        <td>{{ $p->created_at->format('M d, Y') }}</td>
                                                        <td>₦{{ number_format($p->amount, 2) }}</td>
                                                        <td>{{ $p->payment_type }}</td>
                                                        <td>{{ Str::limit($p->description ?? '', 20) }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">No payments recorded yet.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        {{ $payments->appends(request()->query())->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>
</body>