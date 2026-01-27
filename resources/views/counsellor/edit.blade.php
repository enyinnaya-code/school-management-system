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
                            <form action="{{ route('counsellor.update', $session) }}" method="POST" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="card-header">
                                    <h4>Edit Counselling Session</h4>
                                </div>

                                <div class="card-body">
                                    <div class="form-group col-md-8 px-0">
                                        <label>Student</label>
                                        <input type="text" class="form-control" value="{{ $session->student->name }}" readonly>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6 px-0">
                                            <label>Session Date</label>
                                            <input type="date" name="session_date" class="form-control" value="{{ $session->session_date->format('Y-m-d') }}" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Session Time</label>
                                            <input type="time" name="session_time" class="form-control" value="{{ $session->session_time ? $session->session_time->format('H:i') : '' }}">
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12 px-0">
                                        <label>Reason</label>
                                        <textarea name="reason" class="form-control" rows="3" required>{{ $session->reason }}</textarea>
                                    </div>

                                    <div class="form-group col-md-12 px-0">
                                        <label>Counsellor's Notes</label>
                                        <textarea name="notes" class="form-control" rows="5" placeholder="Confidential session notes...">{{ $session->notes }}</textarea>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Status</label>
                                        <select name="status" class="form-control" required>
                                            <option value="scheduled" {{ $session->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="completed" {{ $session->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $session->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            <option value="no_show" {{ $session->status == 'no_show' ? 'selected' : '' }}>No Show</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Follow-up Date (Optional)</label>
                                        <input type="date" name="follow_up_date" class="form-control" value="{{ $session->follow_up_date?->format('Y-m-d') }}">
                                    </div>

                                    <div class="form-group col-md-12 px-0">
                                        <label>Follow-up Notes</label>
                                        <textarea name="follow_up_notes" class="form-control" rows="4">{{ $session->follow_up_notes }}</textarea>
                                    </div>
                                </div>

                                <div class="card-footer text-left pt-5 mt-3">
                                    <button class="btn btn-primary" type="submit">Update Session</button>
                                    <a href="{{ route('counsellor.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')

        <script>
            (function() {
                'use strict';
                window.addEventListener('load', function() {
                    const forms = document.getElementsByClassName('needs-validation');
                    Array.prototype.filter.call(forms, function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });
                }, false);
            })();
        </script>
    </div>
</body>