
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
                                <h4>Borrow Book</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('physical_library.borrowing_returns') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Borrowing & Returns
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Display Notifications -->
                                @if (session('success'))
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    {{ session('success') }}
                                </div>
                                @endif
                                @if (session('error'))
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    {{ session('error') }}
                                </div>
                                @endif

                                <form action="{{ route('physical_library.borrow') }}" method="POST" class="row flex-column">
                                    @csrf
                                    <div class="form-group col-md-6">
                                        <label for="user_id">User</label>
                                        <select class="form-control select2" id="user_id" name="user_id" required>
                                            <option value="" disabled selected>Select User</option>
                                            @foreach($members as $member)
                                            <option value="{{ $member->user_id }}">{{ $member->user->name }} ({{ $member->membership_id }})</option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="book_id">Book</label>
                                        <select class="form-control select2" id="book_id" name="book_id" required>
                                            <option value="" disabled selected>Select Book</option>
                                            @foreach($books as $book)
                                            <option value="{{ $book->id }}" {{ $book->available_quantity <= 0 ? 'disabled' : '' }}>
                                                {{ $book->title }} ({{ $book->author }}) - {{ $book->available_quantity }} available
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('book_id')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-12 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-check"></i> Borrow Book
                                        </button>
                                        <a href="{{ route('physical_library.borrowing_returns') }}" class="btn btn-light">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <!-- Select2 Assets and Initialization -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Search...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
</body>
</html>
