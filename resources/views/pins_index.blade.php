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
                                <h4>Manage Pins</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Pins
                                    </button>
                                    <a href="{{ route('pins.create') }}" class="btn btn-success ml-2">Generate New Pins</a>
                                    <button class="btn btn-info ml-2" type="button" data-toggle="modal" data-target="#printModal">
                                        <i class="fas fa-print"></i> Print Issued PINs
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('pins.index') }}" method="GET" class="row">
                                        <div class="form-group col-md-3">
                                            <label>Section</label>
                                            <select class="form-control" name="filter_section">
                                                <option value="">All Sections</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{ request('filter_section') == $section->id ? 'selected' : '' }}>{{ $section->section_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Session</label>
                                            <select class="form-control" name="filter_session">
                                                <option value="">All Sessions</option>
                                                @foreach($sessions as $session)
                                                    <option value="{{ $session->id }}" {{ request('filter_session') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Term</label>
                                            <select class="form-control" name="filter_term">
                                                <option value="">All Terms</option>
                                                @foreach($terms as $term)
                                                    <option value="{{ $term->id }}" {{ request('filter_term') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Status</label>
                                            <select class="form-control" name="filter_status">
                                                <option value="">All Statuses</option>
                                                <option value="1" {{ request('filter_status') == '1' ? 'selected' : '' }}>Used</option>
                                                <option value="0" {{ request('filter_status') == '0' ? 'selected' : '' }}>Unused</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Issued Status</label>
                                            <select class="form-control" name="filter_issued">
                                                <option value="">All</option>
                                                <option value="1" {{ request('filter_issued') == '1' ? 'selected' : '' }}>Issued</option>
                                                <option value="0" {{ request('filter_issued') == '0' ? 'selected' : '' }}>Not Issued</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-12 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary mr-2">
                                                <i class="fas fa-search"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('pins.index') }}" class="btn btn-light">
                                                <i class="fas fa-sync"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Display Active Filters -->
                                @if(request('filter_section') || request('filter_session') || request('filter_term') || request('filter_status') !== null || request('filter_issued') !== null)
                                    <div class="mb-3">
                                        <h6>Active Filters:</h6>
                                        <div class="active-filters">
                                            @if(request('filter_section'))
                                                <span class="badge badge-info mr-2">Section: {{ $sections->find(request('filter_section'))->section_name ?? 'Unknown' }}</span>
                                            @endif
                                            @if(request('filter_session'))
                                                <span class="badge badge-info mr-2">Session: {{ $sessions->find(request('filter_session'))->name ?? 'Unknown' }}</span>
                                            @endif
                                            @if(request('filter_term'))
                                                <span class="badge badge-info mr-2">Term: {{ $terms->find(request('filter_term'))->name ?? 'Unknown' }}</span>
                                            @endif
                                            @if(request('filter_status') !== null)
                                                <span class="badge badge-info mr-2">Status: {{ request('filter_status') == '1' ? 'Used' : 'Unused' }}</span>
                                            @endif
                                            @if(request('filter_issued') !== null)
                                                <span class="badge badge-info mr-2">Issued: {{ request('filter_issued') == '1' ? 'Issued' : 'Not Issued' }}</span>
                                            @endif
                                            <a href="{{ route('pins.index') }}" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i> Clear All
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="pins-table" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Pin Code</th>
                                                <th>Section</th>
                                                <th>Session</th>
                                                <th>Term</th>
                                                <th>Usage Count</th>
                                                <th>Status</th>
                                                <th>Issued Status</th>
                                                <th>Created By</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pins as $index => $pin)
                                                <tr>
                                                    <td>{{ ($pins->currentPage() - 1) * $pins->perPage() + $index + 1 }}</td>
                                                    <td><strong>{{ $pin->pin_code }}</strong></td>
                                                    <td>{{ $pin->section->section_name }}</td>
                                                    <td>{{ $pin->session->name }}</td>
                                                    <td>{{ $pin->term->name }}</td>
                                                    <td>{{ $pin->usage_count }} / 5</td>
                                                    <td>
                                                        @if($pin->is_used)
                                                            <span class="badge badge-success">Used</span>
                                                        @else
                                                            <span class="badge badge-primary">Unused</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($pin->issuedPin)
                                                            <span class="badge badge-info">
                                                                <i class="fas fa-check-circle"></i> Issued
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary">
                                                                <i class="fas fa-times-circle"></i> Not Issued
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $pin->createdBy->name }}</td>
                                                    <td>{{ $pin->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <!-- View Details Button -->
                                                            <button class="btn btn-sm m-1 btn-info view-details-btn" 
                                                                data-id="{{ $pin->id }}"
                                                                title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </button>

                                                            @if(Auth::user()->user_type == 1)
                                                                <!-- Reset Usage Button -->
                                                                <button class="btn btn-sm m-1 btn-warning" 
                                                                    data-toggle="modal" 
                                                                    data-target="#resetModal"
                                                                    data-id="{{ $pin->id }}" 
                                                                    data-pin-code="{{ $pin->pin_code }}"
                                                                    title="Reset Usage">
                                                                    <i class="fas fa-undo"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Compact Pagination -->
                                <div class="mt-4">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-start">
                                            <!-- Previous Button -->
                                            <li class="page-item {{ $pins->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $pins->appends(request()->query())->previousPageUrl() }}" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>

                                            <!-- Page Numbers (Limited to ±2 around current page) -->
                                            @php
                                                $currentPage = $pins->currentPage();
                                                $lastPage = $pins->lastPage();
                                                $range = 2;
                                                $start = max(1, $currentPage - $range);
                                                $end = min($lastPage, $currentPage + $range);
                                            @endphp

                                            @if($start > 1)
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $pins->appends(request()->query())->url(1) }}">1</a>
                                                </li>
                                                @if($start > 2)
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                @endif
                                            @endif

                                            @for($i = $start; $i <= $end; $i++)
                                                <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $pins->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endfor

                                            @if($end < $lastPage)
                                                @if($end < $lastPage - 1)
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                @endif
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $pins->appends(request()->query())->url($lastPage) }}">{{ $lastPage }}</a>
                                                </li>
                                            @endif

                                            <!-- Next Button -->
                                            <li class="page-item {{ $pins->hasMorePages() ? '' : 'disabled' }}">
                                                <a class="page-link" href="{{ $pins->appends(request()->query())->nextPageUrl() }}" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Print Issued PINs Modal -->
    <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="printModalLabel">
                        <i class="fas fa-print"></i> Print Issued PINs
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('pins.print') }}" method="GET" target="_blank">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Select filters to print issued PINs. Leave all blank to print all issued PINs.
                        </div>
                        
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Section</label>
                                <select class="form-control" name="section_id" id="print_section">
                                    <option value="">All Sections</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Class</label>
                                <select class="form-control" name="class_id" id="print_class">
                                    <option value="">All Classes</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Session</label>
                                <select class="form-control" name="session_id" id="print_session">
                                    <option value="">All Sessions</option>
                                    @foreach($sessions as $session)
                                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Term</label>
                                <select class="form-control" name="term_id" id="print_term">
                                    <option value="">All Terms</option>
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-print"></i> Generate Print View
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pin Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="detailsModalLabel">
                        <i class="fas fa-info-circle"></i> Pin Details
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pinDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading pin details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Usage Confirmation Modal -->
    <div class="modal fade" id="resetModal" tabindex="-1" role="dialog" aria-labelledby="resetModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetModalLabel">Confirm Reset Pin Usage</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to reset the usage for pin: <strong id="pinCode"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="resetForm" method="POST" action="">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Reset Modal
            $('#resetModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const pinCode = button.data('pin-code');

                $('#pinCode').text(pinCode);
                $('#resetForm').attr('action', '{{ url("pins/reset") }}/' + id);
            });

            // Print Modal - Load classes when section changes
            $('#print_section').on('change', function() {
                const sectionId = $(this).val();
                const classSelect = $('#print_class');
                
                classSelect.html('<option value="">Loading...</option>');
                
                if (sectionId) {
                    $.get('{{ url("api/classes") }}/' + sectionId, function(data) {
                        classSelect.html('<option value="">All Classes</option>');
                        data.classes.forEach(function(cls) {
                            classSelect.append(`<option value="${cls.id}">${cls.name}</option>`);
                        });
                    });
                } else {
                    classSelect.html('<option value="">All Classes</option>');
                }
            });

            // View Details Button
            $('.view-details-btn').on('click', function() {
                const pinId = $(this).data('id');
                
                // Show modal
                $('#detailsModal').modal('show');
                
                // Reset content to loading state
                $('#pinDetailsContent').html(`
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading pin details...</p>
                    </div>
                `);

                // Fetch pin details
                $.get('{{ url("pins") }}/' + pinId + '/details', function(data) {
                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="fas fa-key"></i> Pin Information</h6>
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th width="40%">Pin Code:</th>
                                        <td><strong class="text-info">${data.pin_code}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Section:</th>
                                        <td>${data.section}</td>
                                    </tr>
                                    <tr>
                                        <th>Session:</th>
                                        <td>${data.session}</td>
                                    </tr>
                                    <tr>
                                        <th>Term:</th>
                                        <td>${data.term}</td>
                                    </tr>
                                    <tr>
                                        <th>Usage Count:</th>
                                        <td><span class="badge badge-secondary">${data.usage_count} / 5</span></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            ${data.is_used 
                                                ? '<span class="badge badge-success">Used</span>' 
                                                : '<span class="badge badge-primary">Unused</span>'}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created By:</th>
                                        <td>${data.created_by}</td>
                                    </tr>
                                    <tr>
                                        <th>Created At:</th>
                                        <td>${data.created_at}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="fas fa-user-graduate"></i> Issued Information</h6>
                    `;

                    if (data.is_issued && data.issued_to) {
                        html += `
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle"></i> This PIN has been issued
                            </div>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <th width="40%">Student Name:</th>
                                    <td><strong>${data.issued_to.student_name}</strong></td>
                                </tr>
                                <tr>
                                    <th>Admission No:</th>
                                    <td>${data.issued_to.admission_no || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>${data.issued_to.email || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Class:</th>
                                    <td>${data.issued_to.class}</td>
                                </tr>
                                <tr>
                                    <th>Issued By:</th>
                                    <td>${data.issued_to.issued_by}</td>
                                </tr>
                                <tr>
                                    <th>Issued At:</th>
                                    <td>${data.issued_to.issued_at}</td>
                                </tr>
                            </table>
                        `;
                    } else {
                        html += `
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> This PIN has not been issued to any student yet.
                            </div>
                            <p class="text-muted">
                                This PIN is available for issuance. Go to the "Issue PINs" page to assign it to a student.
                            </p>
                        `;
                    }

                    html += `
                            </div>
                        </div>
                    `;

                    $('#pinDetailsContent').html(html);
                }).fail(function() {
                    $('#pinDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> 
                            Error loading pin details. Please try again.
                        </div>
                    `);
                });
            });
        });
    </script>

    @include('includes.footer')
</body>