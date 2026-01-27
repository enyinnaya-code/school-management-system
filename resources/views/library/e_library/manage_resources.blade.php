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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Manage Resources</h4>
                                <a href="{{ route('e_library.add_resource') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add New Resource
                                </a>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>
                                @endif

                                <!-- Filters Section -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <form method="GET" action="{{ route('e_library.manage_resources') }}" id="filter-form">
                                            <div class="row align-items-end">
                                                <!-- Search -->
                                                <div class="col-md-3 mb-2">
                                                    <label for="search">Search</label>
                                                    <input type="text" name="search" id="search" class="form-control" 
                                                           placeholder="Title, author, publisher..." 
                                                           value="{{ request('search') }}">
                                                </div>

                                                <!-- Resource Type Filter -->
                                                <div class="col-md-2 mb-2">
                                                    <label for="type">Resource Type</label>
                                                    <select name="type" id="type" class="form-control">
                                                        <option value="">All Types</option>
                                                        <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                                        <option value="docx" {{ request('type') == 'docx' ? 'selected' : '' }}>Word</option>
                                                        <option value="xlsx" {{ request('type') == 'xlsx' ? 'selected' : '' }}>Excel</option>
                                                        <option value="pptx" {{ request('type') == 'pptx' ? 'selected' : '' }}>PowerPoint</option>
                                                        <option value="ebook" {{ request('type') == 'ebook' ? 'selected' : '' }}>Ebook</option>
                                                        <option value="link" {{ request('type') == 'link' ? 'selected' : '' }}>Link</option>
                                                    </select>
                                                </div>

                                                <!-- Year Filter -->
                                                <div class="col-md-2 mb-2">
                                                    <label for="year">Publication Year</label>
                                                    <input type="number" name="year" id="year" class="form-control" 
                                                           placeholder="e.g. 2023" 
                                                           value="{{ request('year') }}"
                                                           min="1800" max="{{ date('Y') }}">
                                                </div>

                                                <!-- Sort By -->
                                                <div class="col-md-2 mb-2">
                                                    <label for="sort">Sort By</label>
                                                    <select name="sort" id="sort" class="form-control">
                                                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                                                        <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                                                        <option value="author_asc" {{ request('sort') == 'author_asc' ? 'selected' : '' }}>Author (A-Z)</option>
                                                    </select>
                                                </div>

                                                <!-- Per Page -->
                                                <div class="col-md-1 mb-2">
                                                    <label for="per_page">Show</label>
                                                    <select name="per_page" id="per_page" class="form-control">
                                                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                                                        <option value="25" {{ request('per_page', 25) == '25' ? 'selected' : '' }}>25</option>
                                                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                                    </select>
                                                </div>

                                                <!-- Buttons -->
                                                <div class="col-md-2 mb-2">
                                                    <button type="submit" class="btn btn-primary btn-block">
                                                        <i class="fas fa-filter"></i> Filter
                                                    </button>
                                                    <a href="{{ route('e_library.manage_resources') }}" class="btn btn-secondary btn-block mt-1">
                                                        <i class="fas fa-redo"></i> Reset
                                                    </a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Results Summary -->
                                <div class="mb-3">
                                    <p class="text-muted mb-0">
                                        Showing {{ $resources->firstItem() ?? 0 }} to {{ $resources->lastItem() ?? 0 }} 
                                        of {{ $resources->total() }} resources
                                        @if(request()->has('search') || request()->has('type') || request()->has('year'))
                                            <span class="badge badge-info">Filtered</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>Type</th>
                                                <th>Publisher</th>
                                                <th>Year</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($resources as $resource)
                                            <tr>
                                                <td>{{ ($resources->currentPage() - 1) * $resources->perPage() + $loop->iteration }}</td>
                                                <td>{{ $resource->title }}</td>
                                                <td>{{ $resource->author }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ strtoupper($resource->resource_type) }}
                                                    </span>
                                                </td>
                                                <td>{{ $resource->publisher ?? '-' }}</td>
                                                <td>{{ $resource->publication_year ?? '-' }}</td>
                                                <td>
                                                    <!-- View Button -->
                                                    <a href="{{ route('e_library.view_resource', $resource->id) }}"
                                                        class="btn btn-sm btn-info" target="_blank" title="View Resource">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <!-- Edit Button -->
                                                    <a href="{{ route('e_library.edit_resource', $resource->id) }}"
                                                        class="btn btn-sm btn-warning" title="Edit Resource">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <!-- Delete Button -->
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        data-toggle="modal" data-target="#deleteModal"
                                                        data-resource-id="{{ $resource->id }}"
                                                        data-resource-title="{{ $resource->title }}"
                                                        title="Delete Resource">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">No resources found.</p>
                                                    @if(request()->hasAny(['search', 'type', 'year']))
                                                        <a href="{{ route('e_library.manage_resources') }}" class="btn btn-sm btn-secondary">
                                                            Clear Filters
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                @if($resources->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <p class="text-muted mb-0">
                                            Page {{ $resources->currentPage() }} of {{ $resources->lastPage() }}
                                        </p>
                                    </div>
                                    <div>
                                        {{ $resources->appends(request()->query())->links() }}
                                    </div>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the resource:</p>
                    <strong id="resource-title-display"></strong>?
                    <br><br>
                    <small class="text-danger">This action cannot be undone.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="delete-form" action="" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Delete Modal Handler
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var resourceId = button.data('resource-id');
            var resourceTitle = button.data('resource-title');

            var modal = $(this);
            modal.find('#resource-title-display').text(resourceTitle);
            modal.find('#delete-form').attr('action', '/library/e-library/delete-resource/' + resourceId);
        });

        // Auto-submit on filter change (optional, for better UX)
        $('#type, #sort, #per_page').on('change', function() {
            $('#filter-form').submit();
        });

        // Clear individual filters with X button
        $('.clear-filter').on('click', function() {
            var input = $(this).prev('input');
            input.val('');
        });
    </script>
</body>
</html>