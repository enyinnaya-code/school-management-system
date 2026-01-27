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
                                <h4>My Wards</h4>
                            </div>
                            <div class="card-body">
                                <!-- Search Bar for Admins/Superadmins -->
                                @if(auth()->user()->user_type != 5)
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <form method="GET" action="{{ route('wards.index') }}">
                                            <div class="input-group">
                                                <input type="text" name="search_student" class="form-control" placeholder="Search by student name or admission number" value="{{ request('search_student') }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary m-1 btn-sm" type="submit"><i class="fas fa-search"></i> Search</button>
                                                    <a href="{{ route('wards.index') }}" class="btn btn-secondary btn-sm m-1"><i class="fas fa-times"></i> Clear</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @endif

                                <!-- Error Message -->
                                @if(session('error'))
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                                </div>
                                @endif

                                <!-- Wards List -->
                                @if($students->isNotEmpty())
                                <div class="row">
                                    @foreach($students as $student)
                                    <div class="col-md-4 col-sm-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <img src="{{ asset('images/profile-place-holder.jpg') }}" alt="Student Image" class="rounded-circle mb-3" style="width: 100px; height: 100px;">
                                                <h5 class="card-title">{{ $student->name }}</h5>
                                                <p class="card-text text-muted">
                                                    <strong>Class:</strong> {{ $student->class->name ?? 'Not Assigned' }}<br>
                                                    <strong>Admission No:</strong> {{ $student->admission_no }}
                                                </p>
                                                <div class="d-flex justify-content-center">
                                                    <a href="{{ route('students.performance', $student->id) }}" class="btn btn-primary btn-sm mr-2">
                                                        <i class="fas fa-chart-line"></i> Performance
                                                    </a>
                                                    <a href="{{ route('students.profile', $student->id) }}" class="btn btn-info btn-sm">
                                                        <i class="fas fa-user"></i> Profile
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Pagination for Admins/Superadmins -->
                                @if(auth()->user()->user_type != 5)
                                <div class="d-flex justify-content-center">
                                    {{ $students->links() }}
                                </div>
                                @endif
                                @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No wards found.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            @include('includes.edit_footer')
        </div>
    </div>

    <script src="{{ asset('js/app.min.js') }}"></script>
</body>
</html>