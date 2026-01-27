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
                                <h4><i class="fas fa-book-reader"></i> Browse e-Library Resources</h4>
                            </div>
                            <div class="card-body">
                                <!-- Filters Section -->
                                <div class="card mb-3 bg-light">
                                    <div class="card-body">
                                        <form method="GET" action="{{ route('e_library.view_resources') }}" id="filter-form">
                                            <div class="row align-items-end">
                                                <!-- Search -->
                                                <div class="col-md-4 mb-2">
                                                    <label for="search"><i class="fas fa-search"></i> Search</label>
                                                    <input type="text" name="search" id="search" class="form-control" 
                                                           placeholder="Search by title, author, or publisher..." 
                                                           value="{{ request('search') }}">
                                                </div>

                                                <!-- Resource Type Filter -->
                                                <div class="col-md-2 mb-2">
                                                    <label for="type"><i class="fas fa-file"></i> Type</label>
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
                                                    <label for="year"><i class="fas fa-calendar"></i> Year</label>
                                                    <input type="number" name="year" id="year" class="form-control" 
                                                           placeholder="e.g. 2023" 
                                                           value="{{ request('year') }}"
                                                           min="1800" max="{{ date('Y') }}">
                                                </div>

                                                <!-- Sort By -->
                                                <div class="col-md-2 mb-2">
                                                    <label for="sort"><i class="fas fa-sort"></i> Sort By</label>
                                                    <select name="sort" id="sort" class="form-control">
                                                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                                                        <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                                                        <option value="author_asc" {{ request('sort') == 'author_asc' ? 'selected' : '' }}>Author (A-Z)</option>
                                                    </select>
                                                </div>

                                                <!-- Buttons -->
                                                <div class="col-md-2 mb-2">
                                                    <button type="submit" class="btn btn-primary btn-block">
                                                        <i class="fas fa-filter"></i> Filter
                                                    </button>
                                                    <a href="{{ route('e_library.view_resources') }}" class="btn btn-secondary btn-block mt-1">
                                                        <i class="fas fa-redo"></i> Reset
                                                    </a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Results Summary -->
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        Showing {{ $resources->firstItem() ?? 0 }} to {{ $resources->lastItem() ?? 0 }} 
                                        of {{ $resources->total() }} resources
                                        @if(request()->has('search') || request()->has('type') || request()->has('year'))
                                            <span class="badge badge-info ml-2">Filtered Results</span>
                                        @endif
                                    </p>
                                    <div>
                                        <label for="per_page" class="mb-0 mr-2">Show:</label>
                                        <select name="per_page" id="per_page" class="form-control form-control-sm d-inline-block" style="width: auto;" onchange="window.location.href = updateQueryStringParameter(window.location.href, 'per_page', this.value)">
                                            <option value="12" {{ request('per_page', 12) == '12' ? 'selected' : '' }}>12</option>
                                            <option value="24" {{ request('per_page') == '24' ? 'selected' : '' }}>24</option>
                                            <option value="48" {{ request('per_page') == '48' ? 'selected' : '' }}>48</option>
                                            <option value="96" {{ request('per_page') == '96' ? 'selected' : '' }}>96</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Resources Grid -->
                                <div class="row">
                                    @forelse($resources as $resource)
                                    <div class="col-md-4 col-lg-3 mb-4">
                                        <div class="card h-100 shadow-sm resource-card">
                                            <div class="card-body d-flex flex-column">
                                                <!-- Resource Icon -->
                                                <div class="text-center mb-3">
                                                    @php
                                                        $iconClass = match($resource->resource_type) {
                                                            'pdf' => 'fas fa-file-pdf text-danger',
                                                            'docx' => 'fas fa-file-word text-primary',
                                                            'xlsx' => 'fas fa-file-excel text-success',
                                                            'pptx' => 'fas fa-file-powerpoint text-warning',
                                                            'ebook' => 'fas fa-book text-info',
                                                            'link' => 'fas fa-link text-secondary',
                                                            default => 'fas fa-file text-muted'
                                                        };
                                                    @endphp
                                                    <i class="{{ $iconClass }}" style="font-size: 3rem;"></i>
                                                </div>

                                                <!-- Title -->
                                                <h6 class="card-title font-weight-bold mb-2" title="{{ $resource->title }}">
                                                    {{ Str::limit($resource->title, 50) }}
                                                </h6>

                                                <!-- Author -->
                                                <p class="text-muted small mb-1">
                                                    <i class="fas fa-user"></i> {{ $resource->author }}
                                                </p>

                                                <!-- Type Badge -->
                                                <div class="mb-2">
                                                    <span class="badge badge-info">
                                                        {{ strtoupper($resource->resource_type) }}
                                                    </span>
                                                </div>

                                                <!-- Publisher & Year -->
                                                @if($resource->publisher || $resource->publication_year)
                                                <p class="text-muted small mb-2">
                                                    @if($resource->publisher)
                                                        <i class="fas fa-building"></i> {{ $resource->publisher }}
                                                    @endif
                                                    @if($resource->publication_year)
                                                        <br><i class="fas fa-calendar"></i> {{ $resource->publication_year }}
                                                    @endif
                                                </p>
                                                @endif

                                                <!-- Description -->
                                                @if($resource->description)
                                                <p class="text-muted small flex-grow-1">
                                                    {{ Str::limit($resource->description, 100) }}
                                                </p>
                                                @endif

                                                <!-- View Button -->
                                                <div class="mt-auto">
                                                    <a href="{{ route('e_library.view_resource', $resource->id) }}" 
                                                       class="btn btn-primary btn-block" 
                                                       target="_blank">
                                                        <i class="fas fa-eye"></i> View Resource
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="col-12">
                                        <div class="text-center py-5">
                                            <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                                            <h5 class="text-muted">No resources found</h5>
                                            <p class="text-muted">Try adjusting your search or filter criteria</p>
                                            @if(request()->hasAny(['search', 'type', 'year']))
                                                <a href="{{ route('e_library.view_resources') }}" class="btn btn-secondary">
                                                    <i class="fas fa-redo"></i> Clear All Filters
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    @endforelse
                                </div>

                                <!-- Pagination -->
                                @if($resources->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-4">
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

    <!-- Custom CSS -->
    <style>
        .resource-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
        }
        .card-title {
            min-height: 40px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <!-- JavaScript -->
    <script>
        // Auto-submit on filter change
        $('#type, #sort').on('change', function() {
            $('#filter-form').submit();
        });

        // Function to update query string parameter
        function updateQueryStringParameter(uri, key, value) {
            var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
            var separator = uri.indexOf('?') !== -1 ? "&" : "?";
            if (uri.match(re)) {
                return uri.replace(re, '$1' + key + "=" + value + '$2');
            } else {
                return uri + separator + key + "=" + value;
            }
        }
    </script>
</body>
</html>