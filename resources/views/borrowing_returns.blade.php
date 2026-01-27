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
                                <h4>Borrowing & Returns Management</h4>
                                <div class="card-header-action">
                                    <button class="btn btn-primary" type="button" data-toggle="collapse"
                                        data-target="#filterCollapse">
                                        <i class="fas fa-filter"></i> Filter Records
                                    </button>
                                </div>
                            </div>

                            <!-- Success/Error Messages -->
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <!-- Filter Collapse Panel -->
                            <div class="collapse" id="filterCollapse">
                                <div class="card-body pb-0">
                                    <form action="{{ route('physical_library.borrowing_returns') }}" method="GET">
                                        <div class="row">
                                            <!-- User Filters -->
                                            <div class="col-12">
                                                <h6 class="text-primary mb-3"><i class="fas fa-user"></i> User Filters</h6>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>User Name</label>
                                                <input type="text" class="form-control" name="filter_user_name"
                                                    placeholder="Search by name"
                                                    value="{{ request('filter_user_name') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>User Type</label>
                                                <select class="form-control" name="filter_user_type">
                                                    <option value="">All Types</option>
                                                    <option value="1" {{ request('filter_user_type') == '1' ? 'selected' : '' }}>Admin</option>
                                                    <option value="2" {{ request('filter_user_type') == '2' ? 'selected' : '' }}>Teacher</option>
                                                    <option value="3" {{ request('filter_user_type') == '3' ? 'selected' : '' }}>Parent</option>
                                                    <option value="4" {{ request('filter_user_type') == '4' ? 'selected' : '' }}>Student</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Class (Students Only)</label>
                                                <select class="form-control" name="filter_class">
                                                    <option value="">All Classes</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->id }}" {{ request('filter_class') == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Book Filters -->
                                            <div class="col-12 mt-3">
                                                <h6 class="text-primary mb-3"><i class="fas fa-book"></i> Book Filters</h6>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Book Title</label>
                                                <input type="text" class="form-control" name="filter_book_title"
                                                    placeholder="Search by title"
                                                    value="{{ request('filter_book_title') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Author</label>
                                                <input type="text" class="form-control" name="filter_author"
                                                    placeholder="Search by author"
                                                    value="{{ request('filter_author') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>ISBN</label>
                                                <input type="text" class="form-control" name="filter_isbn"
                                                    placeholder="Search by ISBN"
                                                    value="{{ request('filter_isbn') }}">
                                            </div>

                                            <!-- Status & Approval Filters -->
                                            <div class="col-12 mt-3">
                                                <h6 class="text-primary mb-3"><i class="fas fa-info-circle"></i> Status & Approval</h6>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Status</label>
                                                <select class="form-control" name="filter_status">
                                                    <option value="">All Statuses</option>
                                                    <option value="pending" {{ request('filter_status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                                                    <option value="approved" {{ request('filter_status') == 'approved' ? 'selected' : '' }}>Approved/Borrowed</option>
                                                    <option value="overdue" {{ request('filter_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                                    <option value="returned" {{ request('filter_status') == 'returned' ? 'selected' : '' }}>Returned</option>
                                                    <option value="rejected" {{ request('filter_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Approved By</label>
                                                <input type="text" class="form-control" name="filter_approver"
                                                    placeholder="Librarian name"
                                                    value="{{ request('filter_approver') }}">
                                            </div>

                                            <!-- Date Filters -->
                                            <div class="col-12 mt-3">
                                                <h6 class="text-primary mb-3"><i class="fas fa-calendar"></i> Date Filters</h6>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Request Date From</label>
                                                <input type="date" class="form-control" name="filter_request_from"
                                                    value="{{ request('filter_request_from') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Request Date To</label>
                                                <input type="date" class="form-control" name="filter_request_to"
                                                    value="{{ request('filter_request_to') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Borrowed Date From</label>
                                                <input type="date" class="form-control" name="filter_borrowed_from"
                                                    value="{{ request('filter_borrowed_from') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Borrowed Date To</label>
                                                <input type="date" class="form-control" name="filter_borrowed_to"
                                                    value="{{ request('filter_borrowed_to') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Due Date From</label>
                                                <input type="date" class="form-control" name="filter_due_from"
                                                    value="{{ request('filter_due_from') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Due Date To</label>
                                                <input type="date" class="form-control" name="filter_due_to"
                                                    value="{{ request('filter_due_to') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Returned Date From</label>
                                                <input type="date" class="form-control" name="filter_returned_from"
                                                    value="{{ request('filter_returned_from') }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Returned Date To</label>
                                                <input type="date" class="form-control" name="filter_returned_to"
                                                    value="{{ request('filter_returned_to') }}">
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="form-group col-md-12 d-flex align-items-end mt-3">
                                                <button type="submit" class="btn btn-primary mr-2">
                                                    <i class="fas fa-search"></i> Apply Filters
                                                </button>
                                                <a href="{{ route('physical_library.borrowing_returns') }}" class="btn btn-light">
                                                    <i class="fas fa-sync"></i> Reset All
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Display Active Filters -->
                                @php
                                    $hasFilters = request('filter_user_name') || request('filter_book_title') || 
                                                  request('filter_status') || request('filter_borrowed_from') || 
                                                  request('filter_borrowed_to') || request('filter_user_type') ||
                                                  request('filter_class') || request('filter_isbn') || 
                                                  request('filter_author') || request('filter_approver') ||
                                                  request('filter_request_from') || request('filter_request_to') ||
                                                  request('filter_due_from') || request('filter_due_to') ||
                                                  request('filter_returned_from') || request('filter_returned_to');
                                @endphp

                                @if($hasFilters)
                                <div class="mb-3">
                                    <h6>Active Filters:</h6>
                                    <div class="active-filters d-flex flex-wrap align-items-center">
                                        @if(request('filter_user_name'))
                                        <span class="badge badge-info mr-2 mb-2">User: {{ request('filter_user_name') }}</span>
                                        @endif
                                        @if(request('filter_user_type'))
                                        <span class="badge badge-info mr-2 mb-2">Type: {{ ['1' => 'Admin', '2' => 'Teacher', '3' => 'Parent', '4' => 'Student'][request('filter_user_type')] }}</span>
                                        @endif
                                        @if(request('filter_class'))
                                        <span class="badge badge-info mr-2 mb-2">Class: {{ $classes->firstWhere('id', request('filter_class'))->name ?? 'Unknown' }}</span>
                                        @endif
                                        @if(request('filter_book_title'))
                                        <span class="badge badge-info mr-2 mb-2">Book: {{ request('filter_book_title') }}</span>
                                        @endif
                                        @if(request('filter_author'))
                                        <span class="badge badge-info mr-2 mb-2">Author: {{ request('filter_author') }}</span>
                                        @endif
                                        @if(request('filter_isbn'))
                                        <span class="badge badge-info mr-2 mb-2">ISBN: {{ request('filter_isbn') }}</span>
                                        @endif
                                        @if(request('filter_status'))
                                        <span class="badge badge-info mr-2 mb-2">Status: {{ ucfirst(request('filter_status')) }}</span>
                                        @endif
                                        @if(request('filter_approver'))
                                        <span class="badge badge-info mr-2 mb-2">Approver: {{ request('filter_approver') }}</span>
                                        @endif
                                        @if(request('filter_request_from'))
                                        <span class="badge badge-info mr-2 mb-2">Request From: {{ request('filter_request_from') }}</span>
                                        @endif
                                        @if(request('filter_request_to'))
                                        <span class="badge badge-info mr-2 mb-2">Request To: {{ request('filter_request_to') }}</span>
                                        @endif
                                        @if(request('filter_borrowed_from'))
                                        <span class="badge badge-info mr-2 mb-2">Borrowed From: {{ request('filter_borrowed_from') }}</span>
                                        @endif
                                        @if(request('filter_borrowed_to'))
                                        <span class="badge badge-info mr-2 mb-2">Borrowed To: {{ request('filter_borrowed_to') }}</span>
                                        @endif
                                        @if(request('filter_due_from'))
                                        <span class="badge badge-info mr-2 mb-2">Due From: {{ request('filter_due_from') }}</span>
                                        @endif
                                        @if(request('filter_due_to'))
                                        <span class="badge badge-info mr-2 mb-2">Due To: {{ request('filter_due_to') }}</span>
                                        @endif
                                        @if(request('filter_returned_from'))
                                        <span class="badge badge-info mr-2 mb-2">Returned From: {{ request('filter_returned_from') }}</span>
                                        @endif
                                        @if(request('filter_returned_to'))
                                        <span class="badge badge-info mr-2 mb-2">Returned To: {{ request('filter_returned_to') }}</span>
                                        @endif
                                        <a href="{{ route('physical_library.borrowing_returns') }}" class="btn btn-sm btn-outline-danger mb-2">
                                            <i class="fas fa-times"></i> Clear All
                                        </a>
                                    </div>
                                </div>
                                @endif

                                @if($borrowings->count() == 0)
                                    <div class="text-center py-5">
                                        <i class="fas fa-book-reader" style="font-size: 64px; color: #ddd;"></i>
                                        <p class="text-muted mt-3">No borrowing records found.</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>User</th>
                                                    <th>Book</th>
                                                    <th>Request Date</th>
                                                    <th>Due Date</th>
                                                    <th>Borrowed Date</th>
                                                    <th>Returned Date</th>
                                                    <th>Status</th>
                                                    <th>Approved By</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($borrowings as $index => $borrowing)
                                                <tr>
                                                    <td>{{ ($borrowings->currentPage() - 1) * $borrowings->perPage() + $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $borrowing->user->name }}</strong>
                                                        <br>
                                                        @if($borrowing->user->user_type == 4 && $borrowing->user->schoolClass)
                                                            <small class="text-muted">
                                                                <i class="fas fa-user-graduate"></i> Student
                                                                | <i class="fas fa-school"></i> {{ $borrowing->user->schoolClass->name }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>{{ $borrowing->book->title }}</strong><br>
                                                        <small class="text-muted">by {{ $borrowing->book->author }}</small>
                                                    </td>
                                                    <td>{{ $borrowing->created_at->format('d M Y, h:i A') }}</td>
                                                    <td>
                                                        <strong>{{ $borrowing->due_date->format('d M Y') }}</strong>
                                                        @if($borrowing->status == 'approved' && $borrowing->due_date->isPast())
                                                            <br><span class="badge badge-danger">Overdue</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $borrowing->borrowed_at ? $borrowing->borrowed_at->format('d M Y, h:i A') : '-' }}</td>
                                                    <td>{{ $borrowing->returned_at ? $borrowing->returned_at->format('d M Y, h:i A') : '-' }}</td>
                                                    <td>
                                                        @php
                                                            $statusConfig = [
    'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pending Approval'],
    'approved' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Approved'],
    'overdue' => ['class' => 'danger', 'icon' => 'exclamation-circle', 'text' => 'Overdue'],
    'returned' => ['class' => 'info', 'icon' => 'undo', 'text' => 'Returned'],
    'rejected' => ['class' => 'danger', 'icon' => 'ban', 'text' => 'Rejected'],
];

                                                            $config = $statusConfig[$borrowing->status] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => $borrowing->status];
                                                        @endphp
                                                        <span class="badge badge-{{ $config['class'] }}">
                                                            <i class="fas fa-{{ $config['icon'] }}"></i> {{ $config['text'] }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $borrowing->approver?->name ?? '-' }}</td>
                                                    <td>
                                                        <div class="btn-group-vertical" role="group">
                                                            @if($borrowing->status == 'pending')
                                                                <!-- Approve Button - Triggers Modal -->
                                                                <button type="button" class="btn btn-sm btn-success mb-1" 
                                                                    data-toggle="modal" 
                                                                    data-target="#approveModal"
                                                                    data-borrowing-id="{{ $borrowing->id }}"
                                                                    data-user-name="{{ $borrowing->user->name }}"
                                                                    data-book-title="{{ $borrowing->book->title }}"
                                                                    data-due-date="{{ $borrowing->due_date->format('Y-m-d') }}"
                                                                    title="Approve Request">
                                                                    <i class="fas fa-check"></i> Approve
                                                                </button>
                                                                <!-- Reject Button - Triggers Confirmation Modal -->
                                                                <button type="button" class="btn btn-sm btn-danger" 
                                                                    data-toggle="modal" 
                                                                    data-target="#confirmModal"
                                                                    data-action="{{ route('physical_library.reject_borrow', $borrowing->id) }}"
                                                                    data-title="Reject Borrow Request"
                                                                    data-message="Are you sure you want to reject this borrow request?"
                                                                    data-button-text="Yes, Reject"
                                                                    data-button-class="btn-danger"
                                                                    title="Reject Request">
                                                                    <i class="fas fa-times"></i> Reject
                                                                </button>
                                                            @elseif($borrowing->status == 'approved' || $borrowing->status == 'overdue')
                                                                <!-- Mark as Returned - Triggers Confirmation Modal -->
                                                                <button type="button" class="btn btn-sm btn-primary" 
                                                                    data-toggle="modal" 
                                                                    data-target="#confirmModal"
                                                                    data-action="{{ route('physical_library.return', ['id' => Crypt::encrypt($borrowing->id)]) }}"
                                                                    data-title="Mark as Returned"
                                                                    data-message="Are you sure you want to mark this book as returned?"
                                                                    data-button-text="Yes, Mark Returned"
                                                                    data-button-class="btn-primary"
                                                                    title="Mark as Returned">
                                                                    <i class="fas fa-undo"></i> Mark Returned
                                                                </button>
                                                            @elseif($borrowing->status == 'returned')
                                                                <!-- Undo Return - Triggers Confirmation Modal -->
                                                                <button type="button" class="btn btn-sm btn-warning" 
                                                                    data-toggle="modal" 
                                                                    data-target="#confirmModal"
                                                                    data-action="{{ route('physical_library.undo_return', ['id' => Crypt::encrypt($borrowing->id)]) }}"
                                                                    data-title="Undo Return"
                                                                    data-message="Undo this return? The book will be marked as borrowed again."
                                                                    data-button-text="Yes, Undo Return"
                                                                    data-button-class="btn-warning"
                                                                    title="Undo Return">
                                                                    <i class="fas fa-history"></i> Undo Return
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination Controls with filter parameters -->
                                    <div class="mt-4">
                                        {{ $borrowings->appends(request()->query())->onEachSide(1)->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Approve Borrow Modal (Outside Loop) -->
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="approveForm" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="approveModalLabel">
                            <i class="fas fa-check-circle"></i> Approve Borrow Request
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Please review and confirm the borrow approval details.
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-user"></i> User:</strong>
                            <p id="modalUserName" class="ml-4 mb-0"></p>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-book"></i> Book:</strong>
                            <p id="modalBookTitle" class="ml-4 mb-0"></p>
                        </div>

                        <div class="form-group">
                            <label for="modalDueDate"><strong><i class="fas fa-calendar-alt"></i> Due Date:</strong></label>
                            <input type="date" class="form-control" id="modalDueDate" name="due_date" required>
                            <small class="form-text text-muted">You can change the due date if needed.</small>
                        </div>

                        <input type="hidden" id="modalBorrowingId" name="borrowing_id" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Approve Borrow
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Generic Confirmation Modal (Reusable) -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="confirmForm" method="POST" action="">
                    @csrf
                    <div class="modal-header" id="confirmModalHeader">
                        <h5 class="modal-title" id="confirmModalTitle">
                            <i class="fas fa-exclamation-circle"></i> Confirm Action
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmModalMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" id="confirmModalButton" class="btn">
                            Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        // Script to populate approve modal with borrowing details
        $('#approveModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var borrowingId = button.data('borrowing-id');
            var userName = button.data('user-name');
            var bookTitle = button.data('book-title');
            var dueDate = button.data('due-date');
            
            var modal = $(this);
            modal.find('#modalUserName').text(userName);
            modal.find('#modalBookTitle').text(bookTitle);
            modal.find('#modalDueDate').val(dueDate);
            modal.find('#modalBorrowingId').val(borrowingId);
            
            // Set form action dynamically
            var actionUrl = "{{ route('physical_library.approve_borrow', ':id') }}";
            actionUrl = actionUrl.replace(':id', borrowingId);
            modal.find('#approveForm').attr('action', actionUrl);
            
            // Set minimum date to today
            var today = new Date().toISOString().split('T')[0];
            modal.find('#modalDueDate').attr('min', today);
        });

        // Script to populate generic confirmation modal
        $('#confirmModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var action = button.data('action');
            var title = button.data('title');
            var message = button.data('message');
            var buttonText = button.data('button-text');
            var buttonClass = button.data('button-class');
            
            var modal = $(this);
            modal.find('#confirmModalTitle').html('<i class="fas fa-exclamation-circle"></i> ' + title);
            modal.find('#confirmModalMessage').text(message);
            modal.find('#confirmModalButton').text(buttonText).removeClass().addClass('btn ' + buttonClass);
            modal.find('#confirmForm').attr('action', action);
            
            // Set header color based on button class
            var headerClass = 'bg-secondary text-white';
            if (buttonClass === 'btn-danger') {
                headerClass = 'bg-danger text-white';
            } else if (buttonClass === 'btn-warning') {
                headerClass = 'bg-warning text-dark';
            } else if (buttonClass === 'btn-primary') {
                headerClass = 'bg-primary text-white';
            }
            
            modal.find('#confirmModalHeader').removeClass().addClass('modal-header ' + headerClass);
        });
    </script>
</body>
</html>