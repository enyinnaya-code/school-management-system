@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <h4>E-Classroom Sessions</h4>
                                @if(in_array(Auth::user()->user_type, [1,2,3]))
                                    <a href="{{ route('eclass.create') }}" class="btn btn-primary">Create New Session</a>
                                @endif
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Class</th>
                                                <th>Subject</th>
                                                <th>Start Time</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($sessions as $session)
                                                <tr>
                                                    <td>{{ $session->title }}</td>
                                                    <td>{{ $session->schoolClass?->name ?? 'All' }}</td>
                                                    <td>{{ $session->course?->course_name ?? '-' }}</td>
                                                    <td>{{ $session->start_time->format('d M Y h:i A') }}</td>
                                                    <td>
                                                        @if($session->isOngoing())
                                                            <span class="badge badge-success">Live Now</span>
                                                        @elseif($session->start_time->isFuture())
                                                            <span class="badge badge-warning">Upcoming</span>
                                                        @else
                                                            <span class="badge badge-secondary">Ended</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($session->isOngoing() || $session->start_time->isFuture())
                                                            <a href="{{ route('eclass.join', $session->id) }}" class="btn btn-sm btn-info">Join</a>
                                                        @else
                                                            <span class="text-muted">Ended</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No sessions found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{ $sessions->links() }}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.footer')
    </div>
</body>