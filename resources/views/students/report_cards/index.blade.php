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
                    <div class="col-12 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>View Report Card</h4>
                            </div>
                            <div class="card-body">
                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif
                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                <form action="{{ route('students.reportcards.verify') }}" method="POST" class="needs-validation col-md-6" novalidate>
                                    @csrf

                                    <div class="form-group">
                                        <label>Academic Session</label>
                                        <select name="session_id" id="session_id" class="form-control" required>
                                            <option value="">Select Session</option>
                                            @foreach($sessions as $session)
                                                <option value="{{ $session->id }}" {{ old('session_id') == $session->id ? 'selected' : '' }}>
                                                    {{ $session->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Term</label>
                                        <select name="term_id" id="term_id" class="form-control" required>
                                            <option value="">Select Term First</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>PIN</label>
                                        <input type="text" name="pin" class="form-control" placeholder="Enter your PIN" required>
                                        <small class="text-muted">This is the PIN issued to you by the school.</small>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">View Report Card</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>
</body>

{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script>
    $('#session_id').on('change', function() {
        const sessionId = $(this).val();
        const termSelect = $('#term_id');

        if (!sessionId) {
            termSelect.html('<option value="">Select Term First</option>');
            return;
        }

        $.get("{{ url('/get-terms') }}/" + sessionId, function(data) {
            termSelect.html('<option value="">Select Term</option>');
            $.each(data.terms, function(id, term) {
                termSelect.append(`<option value="${term.id}">${term.name}</option>`);
            });
        });
    });
</script>