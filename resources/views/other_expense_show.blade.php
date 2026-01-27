{{-- resources/views/other_expense_show.blade.php --}}
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
                                <h4>Other Expense Details</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('other.expense.edit', $otherExpense) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="{{ route('other.expense.manage') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Description:</strong> {{ $otherExpense->description }}</p>
                                        <p><strong>Amount:</strong> ₦{{ number_format($otherExpense->amount, 2) }}</p>
                                        <p><strong>Date:</strong> {{ $otherExpense->created_at->format('d M Y') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Section:</strong> 
                                            {{ $otherExpense->section_id ? ($otherExpense->section->section_name ?? 'N/A') : 'All Sections' }}
                                        </p>
                                        <p><strong>Created By:</strong> {{ $otherExpense->created_by ?? 'N/A' }}</p>
                                    </div>
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