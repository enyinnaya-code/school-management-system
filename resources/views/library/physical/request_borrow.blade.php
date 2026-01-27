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
                            <div class="card-header">
                                <h4>Borrow a Book</h4>
                            </div>

                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                <!-- Search Form -->
                                <form method="GET" action="{{ route('physical_library.request_borrow') }}" class="mb-4">
                                    <div class="row">
                                        <div class="col-md-6 ml-3">
                                            <input type="text" name="search" class="form-control" placeholder="Search by Title, Author, ISBN, Publisher..." value="{{ request('search') }}">
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i data-feather="search"></i> Search Books
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Borrow Request Form -->
                                <form action="{{ route('physical_library.store_borrow_request') }}" method="POST">
                                    @csrf

                                    <div class="">
                                        <div class="form-group col-md-6">
                                            <label for="book_id">Select Book to Borrow</label>
                                            <select name="book_id" id="book_id" class="form-control @error('book_id') is-invalid @enderror" required>
                                                <option value="">-- Choose a book --</option>
                                                @foreach($books as $book)
                                                   @php
    $available = $book->available_quantity;
@endphp
                                                    @if($available > 0)
                                                        <option value="{{ $book->id }}">
                                                            {{ $book->title }} by {{ $book->author }}
                                                            @if($book->isbn) (ISBN: {{ $book->isbn }}) @endif
                                                            - Available: {{ $available }}/{{ $book->quantity }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('book_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror

                                            @if($books->isEmpty())
                                                <div class="text-muted mt-2">
                                                    No available books found. Try adjusting your search.
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="due_date">Desired Return Date</label>
                                            <input type="date" name="due_date" id="due_date"
                                                   class="form-control @error('due_date') is-invalid @enderror"
                                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                                   max="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                            @error('due_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Max 30 days from today</small>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary">Submit Borrow Request</button>
                                        <a href="{{ route('physical_library.my_borrows') }}" class="btn btn-secondary ml-2">
                                            My Borrowed Books
                                        </a>
                                    </div>
                                </form>
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