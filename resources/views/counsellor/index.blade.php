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
                                <h4>My Counselling Sessions</h4>
                                <a href="{{ route('counsellor.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Schedule New Session
                                </a>
                            </div>

                            <div class="card-body">
                                @if($sessions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date & Time</th>
                                                    <th>Student</th>
                                                    <th>Reason</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sessions as $session)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $session->session_date->format('d M Y') }}</strong>
                                                        @if($session->session_time)
                                                            <br><small>{{ $session->session_time->format('h:i A') }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $session->student->name }}</td>
                                                    <td>{{ Str::limit($session->reason, 60) }}</td>
                                                    <td>
                                                        @php
                                                            $badgeClass = [
                                                                'scheduled' => 'badge-warning',
                                                                'completed' => 'badge-success',
                                                                'cancelled' => 'badge-danger',
                                                                'no_show'   => 'badge-secondary'
                                                            ][$session->status] ?? 'badge-info';
                                                        @endphp
                                                        <span class="badge {{ $badgeClass }}">
                                                            {{ ucwords(str_replace('_', ' ', $session->status)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('counsellor.show', $session) }}" class="btn btn-sm btn-info">
                                                            View
                                                        </a>
                                                        <a href="{{ route('counsellor.edit', $session) }}" class="btn btn-sm btn-warning">
                                                            Edit
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-3">
                                        {{ $sessions->links() }}
                                    </div>
                                @else
                                    <p class="text-center text-muted">No counselling sessions recorded yet.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.footer')
    </div>
</body>