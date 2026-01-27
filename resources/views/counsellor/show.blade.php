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
                                <h4>Counselling Session Details</h4>
                                <a href="{{ route('counsellor.edit', $session) }}" class="btn btn-warning">Edit Session</a>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Student:</strong> {{ $session->student->name }}</p>
                                        <p><strong>Date:</strong> {{ $session->session_date->format('d F Y') }}</p>
                                        <p><strong>Time:</strong> {{ $session->session_time ? $session->session_time->format('h:i A') : 'Not specified' }}</p>
                                        <p><strong>Status:</strong>
                                            <span class="badge {{ $session->status == 'completed' ? 'badge-success' : ($session->status == 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ ucwords(str_replace('_', ' ', $session->status)) }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Reason:</strong></p>
                                        <p>{{ $session->reason }}</p>
                                    </div>
                                </div>

                                @if($session->notes)
                                <hr>
                                <h6>Counsellor's Notes</h6>
                                <p>{{ $session->notes }}</p>
                                @endif

                                @if($session->follow_up_date)
                                <hr>
                                <h6>Follow-up</h6>
                                <p><strong>Date:</strong> {{ $session->follow_up_date->format('d F Y') }}</p>
                                @if($session->follow_up_notes)
                                    <p><strong>Notes:</strong> {{ $session->follow_up_notes }}</p>
                                @endif
                                @endif
                            </div>

                            <div class="card-footer text-left">
                                <a href="{{ route('counsellor.index') }}" class="btn btn-secondary">Back to Sessions</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.footer')
    </div>
</body>