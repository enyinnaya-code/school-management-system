<!-- resources/views/hostels/manage.blade.php -->

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
                                <h4>Manage Hostels</h4>
                                <a href="{{ route('hostels.add') }}" class="btn btn-primary">Add New Hostel</a>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Name</th>
                                                <th>Wardens</th>
                                                <th>Students</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hostels as $index => $hostel)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $hostel->name }}</td>
                                                    <td>
                                                        @if($hostel->wardens->count() > 0)
                                                            @foreach($hostel->wardens as $warden)
                                                                <span class="badge badge-primary m-1">{{ $warden->name }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted">No wardens</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary">{{ $hostel->students->count() }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hostels.students', $hostel->id) }}" class="btn btn-sm m-1 btn-info" title="View Students">
                                                            <i class="fas fa-users"></i> View Students
                                                        </a>
                                                        <a href="{{ route('hostels.edit', $hostel->id) }}" class="btn btn-sm m-1 btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <button type="button" class="btn btn-sm m-1 btn-danger" data-toggle="modal" data-target="#deleteModal" data-hostel-id="{{ $hostel->id }}" data-hostel-name="{{ $hostel->name }}" title="Delete">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete the hostel: <strong id="hostel-name"></strong>?
                        <br><small class="text-muted">Note: Students will be deallocated from this hostel.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <form id="delete-form" action="" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $('#deleteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var hostelId = button.data('hostel-id');
                var hostelName = button.data('hostel-name');

                var modal = $(this);
                modal.find('#hostel-name').text(hostelName);
                modal.find('#delete-form').attr('action', '{{ url('/hostels/delete') }}/' + hostelId);
            });
        </script>
    </div>
</body>