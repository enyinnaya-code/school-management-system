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
    <h4>Students Result For Class {{ $class->name ?? 'Selected Class' }}</h4>

    <a href="{{ route('results.class.view', ['class_id' => $class->id]) }}" 
       class="btn btn-success btn-sm">
        <i class="fas fa-eye"></i> View All Class Results
    </a>
</div>

                            <div class="card-body">
                                @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif

                                <!-- Students Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Serial No</th>

                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Email</th>
                                                <th>Admission No</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($students as $student)
                                            <tr>
                                                <td>{{ ($students->currentPage() - 1) * $students->perPage() +
                                                    $loop->iteration }}</td>

                                                <td>{{ $student->name }}</td>
                                                <td>{{ $student->gender }}</td>
                                                <td>{{ $student->email }}</td>
                                                <td>{{ $student->admission_no }}</td>
                                                <td>
                                                    <a href="{{ route('student.results.upload', $student->id) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fas fa-upload"></i> Upload Results
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No students found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center">
                                    {{ $students->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')
</body>