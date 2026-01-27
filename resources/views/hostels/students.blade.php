<!-- resources/views/hostels/students.blade.php -->

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
                                <h4>{{ $hostel->name }} - Allocated Students</h4>
                                <a href="{{ route('hostels.manage') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Hostels
                                </a>
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

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <p><strong>Hostel Name:</strong> {{ $hostel->name }}</p>
                                    <p><strong>Description:</strong> {{ $hostel->description ?? 'N/A' }}</p>
                                    <p><strong>Wardens:</strong> 
                                        @if($hostel->wardens->count() > 0)
                                            @foreach($hostel->wardens as $warden)
                                                <span class="badge badge-info">{{ $warden->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No wardens assigned</span>
                                        @endif
                                    </p>
                                    <p><strong>Total Students:</strong> <span class="badge badge-primary">{{ $students->count() }}</span></p>
                                </div>

                                @if($students->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Name</th>
                                                    <th>Admission No</th>
                                                    <th>Email</th>
                                                    <th>Class</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($students as $index => $student)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $student->name }}</td>
                                                        <td>{{ $student->admission_no ?? 'N/A' }}</td>
                                                        <td>{{ $student->email }}</td>
                                                        <td>
                                                            @if($student->class)
                                                                {{ $student->class->name ?? 'N/A' }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deallocateModal" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}">
                                                                <i class="fas fa-times"></i> Deallocate
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No students allocated to this hostel yet.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')

        <!-- Deallocate Confirmation Modal -->
        <div class="modal fade" id="deallocateModal" tabindex="-1" role="dialog" aria-labelledby="deallocateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="deallocateModalLabel">Confirm Deallocation</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to deallocate <strong id="student-name"></strong> from this hostel?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <form id="deallocate-form" action="" method="POST" style="display: inline;">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-warning">Yes, Deallocate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $('#deallocateModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var studentId = button.data('student-id');
                var studentName = button.data('student-name');

                var modal = $(this);
                modal.find('#student-name').text(studentName);
                modal.find('#deallocate-form').attr('action', '{{ url('/hostels/deallocate') }}/' + studentId);
            });
        </script>
    </div>
</body>