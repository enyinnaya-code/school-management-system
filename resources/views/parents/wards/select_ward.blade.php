@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <div class="main-content pt-5 mt-5 mb-5">
                <section class="section">
                    <div class="col-12 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>View Ward's Report Card</h4>
                            </div>
                            <div class="card-body">
                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif
                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                <form action="{{ route('parents.wards.verify') }}" method="POST" class="needs-validation col-md-6" novalidate>
                                    @csrf

                                    <!-- Select Ward -->
                                    <div class="form-group">
                                        <label>Select Ward</label>
                                        <select name="student_id" id="student_id" class="form-control" required>
                                            <option value="">Choose a ward</option>
                                            @foreach($wards as $ward)
                                                <option value="{{ $ward->id }}" {{ old('student_id') == $ward->id ? 'selected' : '' }}>
                                                    {{ $ward->name }} ({{ $ward->class->name ?? 'No Class' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Academic Session -->
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

                                    <!-- Term -->
                                    <div class="form-group">
                                        <label>Term</label>
                                        <select name="term_id" id="term_id" class="form-control" required>
                                            <option value="">Select Term First</option>
                                        </select>
                                    </div>

                                    <!-- PIN -->
                                    <div class="form-group">
                                        <label>PIN</label>
                                        <input type="text" name="pin" class="form-control" placeholder="Enter the PIN" required>
                                        <small class="text-muted">This is the PIN issued to your ward by the school.</small>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            View Report Card
                                        </button>
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
            $.each(data.terms, function(index, term) {
                termSelect.append(`<option value="${term.id}">${term.name}</option>`);
            });
        }).fail(function() {
            termSelect.html('<option value="">Failed to load terms</option>');
        });
    });
</script>