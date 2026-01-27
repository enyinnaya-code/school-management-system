@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <!-- Main Content -->
            <div class="main-content" style="padding-top:90px;">
                <!-- Welcome Message -->
                <div class="card">
                    <div class="card-body">
                        <h6>Welcome back, {{ Auth::user()->name }}!</h6>
                    </div>
                </div>

                <section class="section mb-5 pb-5 mx-2">
                    <!-- My Wards Summary Cards -->
                    <div class="row">

                        <!-- Current Session -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-12 text-center pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Current Session</h5>
                                                    <h2 class="mb-3 font-18">{{ $currentSession->name ?? 'Not Set' }}</h2>
                                                </div>
                                            </div>
                                            <div class="col-12 text-center pt-2">
                                                <i class="fas fa-graduation-cap fa-2x text-dark" style="font-size: 1.2rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Term -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-12 text-center pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Current Term</h5>
                                                    <h2 class="mb-3 font-18">{{ $currentTerm->name ?? 'Not Set' }}</h2>
                                                </div>
                                            </div>
                                            <div class="col-12 text-center pt-2">
                                                <i class="fas fa-book-open fa-2x text-info" style="font-size: 1.2rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Wards -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">My Wards</h5>
                                                    <h2 class="mb-3 font-18">{{ $wards->count() }}</h2>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-child fa-3x text-success" style="font-size: 1.2rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Unread Announcements -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="card" style="height: 180px;">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row px-3 py-2">
                                            <div class="col-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Unread Announcements</h5>
                                                    <h2 class="mb-3 font-18">{{ $unreadAnnouncements }}</h2>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-0 text-center pt-4">
                                                <i class="fas fa-bell fa-3x text-warning" style="font-size: 1.2rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- My Wards Table -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>My Wards</h4>
                                    <div class="card-header-action">
                                        <a href="{{ route('wards.index') }}" class="btn btn-primary">View All</a>
                                    </div>
                                </div>
                                <div class="card-body p-0 px-4">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Admission No.</th>
                                                    <th>Class</th>
                                                    <th>Hostel</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($wards as $ward)
                                                <tr>
                                                    <td>{{ $ward->name }}</td>
                                                    <td>{{ $ward->admission_no ?? 'N/A' }}</td>
                                                    <td>{{ $ward->schoolClass?->name ?? 'Not Assigned' }}</td>
                                                    <td>
                                                        @if($ward->hostel)
                                                            <span class="badge badge-primary">
                                                                <i class="fas fa-bed"></i> {{ $ward->hostel->name }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('students.profile', $ward->id) }}"
                                                           class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> Profile
                                                        </a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        No wards assigned yet.
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            @include('includes.edit_footer')
        </div>
    </div>
</body>