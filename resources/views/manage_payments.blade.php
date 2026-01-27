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
                                <h4>Manage Payments</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Payments
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('payment.manage') }}" method="GET" class="row">
                                        <div class="form-group col-md-3">
                                            <label>Student Name/ID</label>
                                            <input type="text" class="form-control" name="filter_student"
                                                value="{{ request('filter_student') }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Section</label>
                                            <select class="form-control" name="filter_section">
                                                <option value="">All Sections</option>
                                                @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ request('filter_section') == $section->id ? 'selected' : '' }}>
                                                    {{ $section->section_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Term</label>
                                            <select class="form-control" name="filter_term">
                                                <option value="">All Terms</option>
                                                @foreach($terms as $term)
                                                <option value="{{ $term->id }}" {{ request('filter_term') == $term->id ? 'selected' : '' }}>
                                                    {{ $term->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Session</label>
                                            <select class="form-control" name="filter_session">
                                                <option value="">All Sessions</option>
                                                @foreach($sessions as $session)
                                                <option value="{{ $session->id }}" {{ request('filter_session') == $session->id ? 'selected' : '' }}>
                                                    {{ $session->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Date From</label>
                                            <input type="date" class="form-control" name="filter_date_from"
                                                value="{{ request('filter_date_from') }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Date To</label>
                                            <input type="date" class="form-control" name="filter_date_to"
                                                value="{{ request('filter_date_to') }}">
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('payment.manage') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Display Active Filters -->
                                @if(request('filter_student') || request('filter_section') || request('filter_term') !== null ||
                                request('filter_session') !== null || request('filter_date_from') || request('filter_date_to'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_student'))
                                        <span class="badge badge-info mr-2">Student: {{ request('filter_student') }}</span>
                                        @endif

                                        @if(request('filter_section'))
                                        <span class="badge badge-info mr-2">Section: {{ $sections->where('id', request('filter_section'))->first()->section_name ?? 'N/A' }}</span>
                                        @endif

                                        @if(request('filter_term') !== null && request('filter_term') !== '')
                                        <span class="badge badge-info mr-2">Term: {{ $terms->where('id', request('filter_term'))->first()->name ?? 'N/A' }}</span>
                                        @endif

                                        @if(request('filter_session') !== null && request('filter_session') !== '')
                                        <span class="badge badge-info mr-2">Session: {{ $sessions->where('id', request('filter_session'))->first()->name ?? 'N/A' }}</span>
                                        @endif
                                        @if(request('filter_date_from'))
                                        <span class="badge badge-info mr-2">From: {{ request('filter_date_from') }}</span>
                                        @endif
                                        @if(request('filter_date_to'))
                                        <span class="badge badge-info mr-2">To: {{ request('filter_date_to') }}</span>
                                        @endif
                                        <a href="{{ route('payment.manage') }}" class="btn btn-sm  btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="payments-table" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Student Name</th>
                                                <th>Student ID</th>
                                                <th>Section</th>
                                                <th>Term</th>
                                                <th>Academic Session</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Created By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($payments as $index => $payment)
                                            <tr>
                                                <td>{{ ($payments->currentPage() - 1) * $payments->perPage() + $index + 1 }}</td>
                                                <td>{{ $payment->student->name ?? 'N/A' }}</td>
                                                <td>{{ $payment->student->admission_no ?? 'N/A' }}</td>
                                                <td>{{ $payment->section->section_name ?? 'N/A' }}</td>
                                                <td>{{ $payment->term->name ?? 'N/A' }}</td>
                                                <td>{{ $payment->term->session->name ?? 'N/A' }}</td>
                                                <td>₦{{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->created_at->format('j F Y g:i A') }}</td>
                                                <td>{{ $payment->createdBy->name ?? 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('payment.edit', $payment->id) }}"
                                                        class="btn btn-sm m-1 btn-warning">Edit</a>
                                                    <a href="{{ route('payment.history', $payment->student_id) }}"
                                                        class="btn btn-sm m-1 btn-info">ViewHistory</a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No payments found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Controls with filter parameters -->
                                <div class="mt-4">
                                    {{ $payments->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')
</body>