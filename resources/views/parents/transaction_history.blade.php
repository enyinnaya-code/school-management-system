{{-- resources/views/parents/transaction_history.blade.php --}}
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
                            <div class="card-header">
                                <h4>Transaction History</h4>
                            </div>

                            @if($wards->isEmpty())
                                <div class="card-body">
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle"></i>
                                        No wards are currently linked to your account. Please contact the school administration.
                                    </div>
                                </div>
                            @else
                                <!-- Filters Form -->
                                <form method="GET" action="{{ route('parents.transaction.history') }}" class="needs-validation" novalidate>
                                    @csrf

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-6 px-0">
                                                <label>Ward</label>
                                                <select name="ward_id" class="form-control">
                                                    <option value="">All Wards</option>
                                                    @foreach($wards as $ward)
                                                        <option value="{{ $ward->id }}" {{ request('ward_id') == $ward->id ? 'selected' : '' }}>
                                                            {{ $ward->name }} ({{ $ward->admission_no }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-6 px-0">
                                                <label>Session</label>
                                                <select name="session_id" class="form-control" id="sessionFilter">
                                                    <option value="">All Sessions</option>
                                                    @foreach($sessions as $session)
                                                        <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                                            {{ $session->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-6 px-0">
                                                <label>Term</label>
                                                <select name="term_id" class="form-control" id="termFilter">
                                                    <option value="">All Terms</option>
                                                    @if(request('session_id'))
                                                        @foreach($terms as $term)
                                                            <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                                                                {{ $term->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="" disabled>Select a session first</option>
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="form-group col-md-6 px-0">
                                                <label>Date From</label>
                                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                            </div>

                                            <div class="form-group col-md-6 px-0">
                                                <label>Date To</label>
                                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('parents.transaction.history') }}" class="btn btn-secondary">
                                                <i class="fas fa-undo"></i> Clear Filters
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                <!-- Payment Records Table -->
                                <div class="card-body pt-0">
                                    @if($payments->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-striped table-md">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Ward</th>
                                                        <th>Class</th>
                                                        <th>Session / Term</th>
                                                        <th>Amount</th>
                                                        <th>Type</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($payments as $payment)
                                                        <tr>
                                                            <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                                                            <td>{{ $payment->student->name }}</td>
                                                            <td>{{ $payment->schoolClass->name ?? 'N/A' }}</td>
                                                            <td>
                                                                {{ $payment->term->session->name ?? 'N/A' }}
                                                                / {{ $payment->term->name ?? 'N/A' }}
                                                            </td>
                                                            <td><strong>₦{{ number_format($payment->amount, 2) }}</strong></td>
                                                            <td>
                                                                <span class="badge badge-info">{{ $payment->payment_type }}</span>
                                                            </td>
                                                            <td>{{ Str::limit($payment->description ?? 'N/A', 50) }}</td>
                                                            <td>
                                                                <a href="{{ route('bursar.payment.receipt', $payment->id) }}"
                                                                   class="btn btn-sm btn-primary" target="_blank">
                                                                    <i class="fas fa-print"></i> Receipt
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Pagination -->
                                        <div class="d-flex justify-content-center mt-4">
                                            {{ $payments->appends(request()->query())->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No payment records found matching your filters.</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Auto-submit session filter to load terms -->
    <script>
        document.getElementById('sessionFilter')?.addEventListener('change', function () {
            this.closest('form').submit();
        });
    </script>

    <!-- Form Validation Script (same as your other pages) -->
    <script>
        (function () {
            'use strict';
            window.addEventListener('load', function () {
                const forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function (form) {
                    form.addEventListener('submit', function (event) {
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
</body>