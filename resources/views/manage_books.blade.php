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
                                <h4>Manage Books</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Books
                                    </button>
                                    <a href="{{ route('physical_library.add_book') }}" class="btn btn-success ml-2">
                                        <i class="fas fa-plus"></i> Add Book
                                    </a>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('physical_library.manage_books') }}" method="GET"
                                        class="row">
                                        <div class="form-group col-md-4">
                                            <label>Title</label>
                                            <input type="text" class="form-control" name="filter_title"
                                                value="{{ request('filter_title') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Author</label>
                                            <input type="text" class="form-control" name="filter_author"
                                                value="{{ request('filter_author') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>ISBN</label>
                                            <input type="text" class="form-control" name="filter_isbn"
                                                value="{{ request('filter_isbn') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Availability</label>
                                            <select class="form-control" name="filter_availability">
                                                <option value="">All</option>
                                                <option value="1" {{ request('filter_availability')=='1' ? 'selected'
                                                    : '' }}>Available</option>
                                                <option value="0" {{ request('filter_availability')=='0' ? 'selected'
                                                    : '' }}>Unavailable</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Date Added From</label>
                                            <input type="date" class="form-control" name="filter_date_from"
                                                value="{{ request('filter_date_from') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Date Added To</label>
                                            <input type="date" class="form-control" name="filter_date_to"
                                                value="{{ request('filter_date_to') }}">
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('physical_library.manage_books') }}"
                                                class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Display Active Filters -->
                                @if(request('filter_title') || request('filter_author') || request('filter_isbn') ||
                                request('filter_availability') !== null || request('filter_date_from') ||
                                request('filter_date_to'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_title'))
                                        <span class="badge badge-info mr-2">Title: {{ request('filter_title') }}</span>
                                        @endif
                                        @if(request('filter_author'))
                                        <span class="badge badge-info mr-2">Author: {{ request('filter_author')
                                            }}</span>
                                        @endif
                                        @if(request('filter_isbn'))
                                        <span class="badge badge-info mr-2">ISBN: {{ request('filter_isbn') }}</span>
                                        @endif
                                        @if(request('filter_availability') !== null && request('filter_availability')
                                        !== '')
                                        <span class="badge badge-info mr-2">Availability: {{
                                            request('filter_availability') == '1' ? 'Available' : 'Unavailable'
                                            }}</span>
                                        @endif
                                        @if(request('filter_date_from'))
                                        <span class="badge badge-info mr-2">From: {{ request('filter_date_from')
                                            }}</span>
                                        @endif
                                        @if(request('filter_date_to'))
                                        <span class="badge badge-info mr-2">To: {{ request('filter_date_to') }}</span>
                                        @endif
                                        <a href="{{ route('physical_library.manage_books') }}"
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="books-table" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>ISBN</th>
                                                <th>Quantity</th>
                                                <th>Available</th>
                                                <th>Date Added</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($books as $index => $book)
                                            <tr>
                                                <td>{{ ($books->currentPage() - 1) * $books->perPage() + $index + 1 }}
                                                </td>
                                                <td>{{ $book->title }}</td>
                                                <td>{{ $book->author }}</td>
                                                <td>{{ $book->isbn ?? 'N/A' }}</td>
                                                <td>{{ $book->quantity }}</td>
                                                <td>
                                                    @if($book->available_quantity > 0)
                                                    <span class="badge badge-success">Available ({{
                                                        $book->available_quantity }})</span>
                                                    @else
                                                    <span class="badge badge-danger">Unavailable</span>
                                                    @endif
                                                </td>
                                                <td>{{ $book->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <a href="{{ route('physical_library.edit_book', ['encryptedId' => Crypt::encrypt($book->id)]) }}"
                                                        class="btn btn-sm m-1 btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    
                                                    <form
                                                        action="{{ route('physical_library.delete_book', ['id' => Crypt::encrypt($book->id)]) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm m-1 btn-danger"
                                                            title="Delete" data-toggle="modal"
                                                            data-target="#deleteModal"
                                                            data-id="{{ Crypt::encrypt($book->id) }}"
                                                            data-name="{{ $book->title }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Controls with filter parameters -->
                                <div class="mt-4">
                                    {{ $books->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Delete Book Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete book: <strong id="deleteBookName"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Scripts -->
    <script>
        $(document).ready(function() {
            // Delete Book Modal
            $('#deleteModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const name = button.data('name');

                // Set the book name in the modal
                $('#deleteBookName').text(name);

                // Set the form action URL
                $('#deleteForm').attr('action', '{{ url("library/physical/delete-book") }}/' + id);
            });
        });
    </script>
    @include('includes.edit_footer')
</body>

</html>