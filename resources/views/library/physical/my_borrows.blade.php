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
                                <h4>My Borrowed Books</h4>
                                <a href="{{ route('physical_library.request_borrow') }}" class="btn btn-primary">
                                    <i data-feather="plus"></i> Borrow New Book
                                </a>
                            </div>

                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if($borrowings->count() == 0)
                                    <div class="text-center py-5">
                                        <i data-feather="book-open" class="mb-3" style="width: 80px; height: 80px; color: #ddd;"></i>
                                        <p class="text-muted">You have no borrowed books yet.</p>
                                        <a href="{{ route('physical_library.request_borrow') }}" class="btn btn-primary">
                                            Browse Books to Borrow
                                        </a>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Book</th>
                                                    <th>Borrowed On</th>
                                                    <th>Due Date</th>
                                                    <th>Status</th>
                                                    <th>Approved By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($borrowings as $borrowing)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $borrowing->book->title }}</strong><br>
                                                            <small class="text-muted">
                                                                by {{ $borrowing->book->author }}
                                                                @if($borrowing->book->isbn) | ISBN: {{ $borrowing->book->isbn }} @endif
                                                            </small>
                                                        </td>
                                                        <td>
                                                            {{ $borrowing->borrowed_at ? $borrowing->borrowed_at->format('d M Y') : '-' }}
                                                        </td>
                                                        <td>
                                                            <strong>{{ $borrowing->due_date->format('d M Y') }}</strong>
                                                            @if($borrowing->status == 'approved' && $borrowing->due_date->isPast())
                                                                <span class="badge badge-danger ml-2">Overdue</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $statusClass = match($borrowing->status) {
                                                                    'pending' => 'warning',
                                                                    'approved' => 'success',
                                                                    'returned' => 'info',
                                                                    'overdue' => 'danger',
                                                                    default => 'secondary'
                                                                };
                                                            @endphp
                                                            <span class="badge badge-{{ $statusClass }}">
                                                                {{ ucfirst(str_replace('_', ' ', $borrowing->status)) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            {{ $borrowing->approver?->name ?? '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4">
                                        {{ $borrowings->onEachSide(1)->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>
</body>
</html>