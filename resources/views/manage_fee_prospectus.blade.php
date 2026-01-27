{{-- resources/views/manage_fee_prospectus.blade.php --}}
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
                                <h4>Manage Fee Prospectus</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Prospectus
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('fee.prospectus.manage') }}" method="GET" class="row">
                                        <div class="form-group col-md-3">
                                            <label>Section/Arm</label>
                                            <select class="form-control" id="filter_section" name="filter_section">
                                                <option value="">All Sections</option>
                                                @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ request('filter_section') == $section->id ? 'selected' : '' }}>{{ $section->section_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Class</label>
                                            <select class="form-control" id="filter_class" name="filter_class">
                                                <option value="">All Classes</option>
                                                @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ request('filter_class') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Term</label>
                                            <select class="form-control" name="filter_term">
                                                <option value="">All Terms</option>
                                                @foreach($terms as $term)
                                                <option value="{{ $term->name }}" {{ request('filter_term') == $term->name ? 'selected' : '' }}>{{ $term->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Academic Session</label>
                                            <select class="form-control" name="filter_session">
                                                <option value="">All Sessions</option>
                                                @foreach($sessions as $session)
                                                <option value="{{ $session->name }}" {{ request('filter_session') == $session->name ? 'selected' : '' }}>{{ $session->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('fee.prospectus.manage') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Display Active Filters -->
                                @if(request('filter_section') || request('filter_class') || request('filter_term') || request('filter_session'))
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters">
                                        @if(request('filter_section'))
                                        <span class="badge badge-info mr-2">Section: {{ $sections->firstWhere('id', request('filter_section'))?->section_name ?? 'Unknown' }}</span>
                                        @endif
                                        @if(request('filter_class'))
                                        <span class="badge badge-info mr-2">Class: {{ $classes->firstWhere('id', request('filter_class'))?->name ?? 'Unknown' }}</span>
                                        @endif
                                        @if(request('filter_term'))
                                        <span class="badge badge-info mr-2">Term: {{ request('filter_term') }}</span>
                                        @endif
                                        @if(request('filter_session'))
                                        <span class="badge badge-info mr-2">Session: {{ request('filter_session') }}</span>
                                        @endif
                                        <a href="{{ route('fee.prospectus.manage') }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="prospectus-table" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Section/Arm</th>
                                                <th>Class</th>
                                                <th>Term</th>
                                                <th>Academic Session</th>
                                                <th>Total Amount</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($prospectuses as $index => $prospectus)
                                            <tr>
                                                <td>{{ ($prospectuses->currentPage() - 1) * $prospectuses->perPage() + $index + 1 }}</td>
                                                <td>{{ $prospectus->section->section_name ?? 'N/A' }}</td>
                                                <td>{{ $prospectus->schoolClass->name ?? 'N/A' }}</td>
                                                <td>{{ $prospectus->term->name ?? 'N/A' }}</td>
                                                <td>{{ $prospectus->term->session->name ?? 'N/A' }}</td>
                                                <td>₦{{ number_format($prospectus->total_amount, 2) }}</td>
                                                <td>
                                                    <a href="{{ route('fee.prospectus.preview', Crypt::encrypt($prospectus->id)) }}" class="btn btn-sm m-1 btn-info" title="Preview" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('fee.prospectus.edit', Crypt::encrypt($prospectus->id)) }}" class="btn btn-sm m-1 btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <form action="{{ route('fee.prospectus.destroy', Crypt::encrypt($prospectus->id)) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm m-1 btn-danger" data-toggle="modal" data-target="#deleteModal" data-id="{{ Crypt::encrypt($prospectus->id) }}" data-name="{{ ($prospectus->schoolClass->name ?? 'N/A') }} - {{ ($prospectus->term->name ?? 'N/A') }}" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No fee prospectuses found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Controls with filter parameters -->
                                <div class="mt-4">
                                    {{ $prospectuses->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the fee prospectus for <strong id="deleteProspectusName"></strong>?
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
        var allClassesData = @json($allClasses);
        var getClassesUrl = "{{ route('bursar.getClassesBySection', 0) }}";

        $(document).ready(function() {
            // Dynamic class loading based on section
            $('#filter_section').change(function() {
                var sectionId = $(this).val();
                var classSelect = $('#filter_class');
                var currentClassId = classSelect.val();

                classSelect.empty();
                classSelect.append('<option value="">All Classes</option>');

                if (sectionId) {
                    // Fetch classes for the selected section
                    $.get(getClassesUrl.replace('0', sectionId), function(data) {
                        $.each(data, function(index, classItem) {
                            classSelect.append('<option value="' + classItem.id + '">' + classItem.name + '</option>');
                        });
                        // Try to restore current class if it exists in the new list
                        if (currentClassId && classSelect.find('option[value="' + currentClassId + '"]').length) {
                            classSelect.val(currentClassId);
                        }
                    });
                } else {
                    // Load all classes
                    $.each(allClassesData, function(index, classItem) {
                        classSelect.append('<option value="' + classItem.id + '">' + classItem.name + '</option>');
                    });
                    // Restore current class
                    if (currentClassId) {
                        classSelect.val(currentClassId);
                    }
                }
            });

            // Delete Prospectus Modal
            $('#deleteModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const name = button.data('name');

                // Set the prospectus name in the modal
                $('#deleteProspectusName').text(name);

                // Set the form action URL to match your route name
                $('#deleteForm').attr('action', '{{ route("fee.prospectus.destroy", ":id") }}'.replace(':id', id));
            });

            @if(request()->hasAny(['filter_section', 'filter_class', 'filter_term', 'filter_session']))
            $('#filterCollapse').collapse('show');
            @endif
        });
    </script>

    @include('includes.edit_footer')
</body>