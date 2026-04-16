{{-- resources/views/misc_fee_payments_manage.blade.php --}}
@include('includes.head')

<head>
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }

        .status-paid {
            background-color: #28a745;
            color: white;
        }

        .status-pending {
            background-color: #ffc107;
            color: black;
        }

        .status-cancelled {
            background-color: #dc3545;
            color: white;
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
                        <div class="card">
                            <div class="card-header">
                                <h4>Manage Miscellaneous Fee Payments</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Payments
                                    </button>
                                    <a href="{{ route('misc.fee.payments.create') }}" class="btn btn-success ml-2">
                                        <i class="fas fa-plus"></i> Record New Payment
                                    </a>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('misc.fee.payments.manage') }}" method="GET" class="row">
                                        <div class="form-group col-md-3">
                                            <label>Student</label>
                                            <select name="filter_student" class="form-control">
                                                <option value="">All Students</option>
                                                @foreach($students as $student)
                                                <option value="{{ $student->id }}" {{
                                                    request('filter_student')==$student->id ? 'selected' : '' }}>
                                                    {{ $student->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Section</label>
                                            <select name="filter_section" class="form-control" id="filter_section">
                                                <option value="">All Sections</option>
                                                @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{
                                                    request('filter_section')==$section->id ? 'selected' : '' }}>
                                                    {{ $section->section_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Class</label>
                                            <select name="filter_class" class="form-control" id="filter_class">
                                                <option value="">All Classes</option>
                                                @foreach($allClasses as $cls)
                                                <option value="{{ $cls->id }}" data-section="{{ $cls->section_id }}" {{
                                                    request('filter_class')==$cls->id ? 'selected' : '' }}>
                                                    {{ $cls->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Fee Type</label>
                                            <select name="filter_fee_type" class="form-control">
                                                <option value="">All Fee Types</option>
                                                @foreach($feeTypes as $feeType)
                                                <option value="{{ $feeType->id }}" {{
                                                    request('filter_fee_type')==$feeType->id ? 'selected' : '' }}>
                                                    {{ $feeType->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label>Date From</label>
                                            <input type="date" name="filter_date_from" class="form-control"
                                                value="{{ request('filter_date_from') }}">
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label>Date To</label>
                                            <input type="date" name="filter_date_to" class="form-control"
                                                value="{{ request('filter_date_to') }}">
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label>Status</label>
                                            <select name="filter_status" class="form-control">
                                                <option value="">All Status</option>
                                                <option value="paid" {{ request('filter_status')=='paid' ? 'selected'
                                                    : '' }}>Paid</option>
                                                <option value="pending" {{ request('filter_status')=='pending'
                                                    ? 'selected' : '' }}>Pending</option>
                                                <option value="cancelled" {{ request('filter_status')=='cancelled'
                                                    ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('misc.fee.payments.manage') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
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

                                <!-- Display Active Filters -->
                                @if(request('filter_student') || request('filter_fee_type') || request('filter_status')
                                !== null ||
                                request('filter_date_from') || request('filter_date_to'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_student'))
                                        @php
                                        $selectedStudent = $students->firstWhere('id', request('filter_student'));
                                        @endphp
                                        <span class="badge badge-info mr-2">Student: {{ $selectedStudent->name ??
                                            request('filter_student') }}</span>
                                        @endif
                                        @if(request('filter_fee_type'))
                                        @php
                                        $selectedFeeType = $feeTypes->firstWhere('id', request('filter_fee_type'));
                                        @endphp
                                        <span class="badge badge-info mr-2">Fee Type: {{ $selectedFeeType->name ??
                                            request('filter_fee_type') }}</span>
                                        @endif
                                        @if(request('filter_status') !== null && request('filter_status') !== '')
                                        <span class="badge badge-info mr-2">Status: {{ ucfirst(request('filter_status'))
                                            }}</span>
                                        @endif
                                        @if(request('filter_section'))
                                        <span class="badge badge-info mr-2">
                                            Section: {{ $sections->firstWhere('id',
                                            request('filter_section'))->section_name ?? 'N/A' }}
                                        </span>
                                        @endif

                                        @if(request('filter_class'))
                                        <span class="badge badge-info mr-2">
                                            Class: {{ $allClasses->firstWhere('id', request('filter_class'))->name ??
                                            'N/A' }}
                                        </span>
                                        @endif
                                        @if(request('filter_date_from'))
                                        <span class="badge badge-info mr-2">From: {{ request('filter_date_from')
                                            }}</span>
                                        @endif
                                        @if(request('filter_date_to'))
                                        <span class="badge badge-info mr-2">To: {{ request('filter_date_to') }}</span>
                                        @endif
                                        <a href="{{ route('misc.fee.payments.manage') }}"
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                @if($classSummary)
                                <div class="alert alert-light border mb-3 p-3">
                                    <p class="mb-2 text-muted" style="font-size: 13px;">
                                        Payment summary for <strong>{{ $classSummary['class']->name }}</strong>
                                        @if($classSummary['fee_type'])
                                        — {{ $classSummary['fee_type']->name }}
                                        @else
                                        — all misc fees
                                        @endif
                                    </p>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="card py-2">
                                                <div class="text-muted" style="font-size: 11px;">Total students</div>
                                                <div class="font-weight-bold" style="font-size: 22px;">{{
                                                    $classSummary['total'] }}</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="card py-2 border-success">
                                                <div class="text-success" style="font-size: 11px;">Paid</div>
                                                <div class="font-weight-bold text-success" style="font-size: 22px;">{{
                                                    $classSummary['paid'] }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{
                                                    $classSummary['paid_pct'] }}%</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="card py-2 border-danger">
                                                <div class="text-danger" style="font-size: 11px;">Yet to pay</div>
                                                <div class="font-weight-bold text-danger" style="font-size: 22px;">{{
                                                    $classSummary['unpaid'] }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ 100 -
                                                    $classSummary['paid_pct'] }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Receipt No.</th>
                                                <th>Student</th>
                                                <th>Fee Type</th>
                                                <th>Amount Paid (₦)</th>
                                                <th>Payment Date</th>
                                                <th>Status</th>
                                                <th>Paid By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($payments as $key => $payment)
                                            <tr>
                                                <td>{{ $payments->firstItem() + $key }}</td>
                                                <td>{{ $payment->receipt_number }}</td>
                                                <td>{{ $payment->student->name ?? 'N/A' }}<br><small>{{
                                                        $payment->student->admission_no ?? 'N/A' }}</small></td>
                                                <td>{{ $payment->miscFeeType->name ?? 'N/A' }}</td>
                                                <td>₦{{ number_format($payment->amount_paid, 2) }}</td>
                                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                                <td><span class="status-badge status-{{ $payment->status }}">{{
                                                        ucfirst($payment->status) }}</span></td>
                                                <td>{{ $payment->paidBy->name ?? 'N/A' }}</td>
                                                <td class="text-nowrap">
                                                    <!-- Add edit/delete actions if needed -->
                                                    <a href="{{ route('misc.fee.payments.receipt', $payment->id) }}" 
   class="btn btn-sm btn-info" 
   title="View Receipt" 
   target="_blank">
    <i class="fas fa-eye"></i>
</a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No miscellaneous fee payments found.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-3">
                                    {{ $payments->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>




        <script>
            document.addEventListener('DOMContentLoaded', function () {
    const sectionSelect = document.getElementById('filter_section');
    const classSelect   = document.getElementById('filter_class');

    function filterClasses() {
        const sectionId = sectionSelect.value;
        Array.from(classSelect.options).forEach(opt => {
            if (!opt.value) return;
            opt.hidden = sectionId && opt.dataset.section !== sectionId;
        });
        if (classSelect.selectedOptions[0]?.hidden) {
            classSelect.value = '';
        }
    }

    sectionSelect.addEventListener('change', filterClasses);
    filterClasses();
});
        </script>
        @include('includes.edit_footer')
    </div>

    <script>
    $(document).ready(function() {
        setTimeout(function() {
            $('.alert-dismissible').fadeOut('slow');
        }, 5000);
    });
</script>
</body>