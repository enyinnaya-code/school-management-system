{{-- resources/views/other_expense_show.blade.php --}}
@include('includes.head')

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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">
                                    <i class="fas fa-receipt mr-2 text-primary"></i> Expense Details
                                </h4>
                                <div>
                                    <a href="{{ route('other.expense.edit', $otherExpense) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="{{ route('other.expense.manage') }}" class="btn btn-secondary btn-sm ml-1">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">

                                {{-- Amount highlight banner --}}
                                <div class="alert alert-danger text-center mb-4" style="border-radius: 8px;">
                                    <div class="text-dark small mb-1">TOTAL AMOUNT</div>
                                    <h2 class="mb-0 text-dark font-weight-bold">
                                        ₦{{ number_format($otherExpense->amount, 2) }}
                                    </h2>
                                </div>

                                <div class="row">

                                    {{-- Left column --}}
                                    <div class="col-md-6">
                                        <div class="card border shadow-none mb-3">
                                            <div class="card-header bg-light py-2">
                                                <h6 class="mb-0 text-muted">
                                                    <i class="fas fa-info-circle mr-1"></i> Expense Information
                                                </h6>
                                            </div>
                                            <div class="card-body py-3">

                                                <div class="mb-3">
                                                    <div class="text-muted small font-weight-bold text-uppercase mb-1">Description</div>
                                                    <div>{{ $otherExpense->description }}</div>
                                                </div>

                                                <hr class="my-2">

                                                <div class="mb-3">
                                                    <div class="text-muted small font-weight-bold text-uppercase mb-1">Section</div>
                                                    <div>
                                                        @if($otherExpense->section_id && $otherExpense->section)
                                                            <span class="badge badge-info px-2 py-1">
                                                                {{ $otherExpense->section->section_name }}
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary px-2 py-1">All Sections</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <hr class="my-2">

                                                <div>
                                                    <div class="text-muted small font-weight-bold text-uppercase mb-1">Status</div>
                                                    <span class="badge badge-success px-2 py-1">Recorded</span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    {{-- Right column --}}
                                    <div class="col-md-6">
                                        <div class="card border shadow-none mb-3">
                                            <div class="card-header bg-light py-2">
                                                <h6 class="mb-0 text-muted">
                                                    <i class="fas fa-calendar-alt mr-1"></i> Session & Record Info
                                                </h6>
                                            </div>
                                            <div class="card-body py-3">

                                                <div class="mb-3">
                                                    <div class="text-muted small font-weight-bold text-uppercase mb-1">Session</div>
                                                    <div>{{ $otherExpense->session->name ?? '—' }}</div>
                                                </div>

                                                <hr class="my-2">

                                                <div class="mb-3">
                                                    <div class="text-muted small font-weight-bold text-uppercase mb-1">Term</div>
                                                    <div>{{ $otherExpense->term->name ?? '—' }}</div>
                                                </div>

                                                <hr class="my-2">

                                                <div class="mb-3">
                                                    <div class="text-muted small font-weight-bold text-uppercase mb-1">Date Recorded</div>
                                                    <div>{{ $otherExpense->created_at->format('d M Y, h:i A') }}</div>
                                                </div>

                                                <hr class="my-2">

                                                <div>
                                                    <div class="text-muted small font-weight-bold text-uppercase mb-1">Recorded By</div>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm mr-2"
                                                             style="width:30px; height:30px; border-radius:50%;
                                                                    background:#6c757d; color:#fff;
                                                                    display:flex; align-items:center;
                                                                    justify-content:center; font-size:12px;
                                                                    font-weight:bold; flex-shrink:0;">
                                                            {{ $otherExpense->createdBy ? strtoupper(substr($otherExpense->createdBy->name, 0, 1)) : '?' }}
                                                        </div>
                                                        <span>{{ $otherExpense->createdBy->name ?? 'Unknown' }}</span>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>{{-- /row --}}

                                {{-- Last updated row --}}
                                @if($otherExpense->updated_at != $otherExpense->created_at)
                                <div class="text-muted small text-right mt-1">
                                    <i class="fas fa-pencil-alt mr-1"></i>
                                    Last updated: {{ $otherExpense->updated_at->format('d M Y, h:i A') }}
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    @include('includes.edit_footer')
</body>