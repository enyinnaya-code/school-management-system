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
                                <h4>Generate Pins</h4>
                            </div>
                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                <form action="{{ route('pins.store') }}" method="POST" class="col-md-6 px-0">
                                    @csrf
                                    <div class="form-group">
                                        <label>Section</label>
                                        <select class="form-control" id="section_id" name="section_id" required>
                                            <option value="">Select Section</option>
                                            @foreach($sections as $section)
                                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('section_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Session</label>
                                        <select class="form-control" id="session_id" name="session_id" required>
                                            <option value="">Select Session (after selecting section)</option>
                                        </select>
                                        @error('session_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Term</label>
                                        <select class="form-control" id="term_id" name="term_id" required>
                                            <option value="">Select Term (after selecting session)</option>
                                        </select>
                                        @error('term_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Number of Pins</label>
                                        <input type="number" class="form-control" name="num_pins" min="1" max="100" required>
                                        @error('num_pins')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Generate Pins</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- JavaScript for Chained Dropdowns -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#section_id').change(function() {
                var sectionId = $(this).val();
                if (sectionId) {
                    // Fetch sessions for the selected section
                    $.get('{{ url("/pins/sessions") }}/' + sectionId, function(data) {
                        $('#session_id').empty().append('<option value="">Select Session</option>');
                        if (data.sessions.length > 0) {
                            data.sessions.forEach(function(session) {
                                $('#session_id').append('<option value="' + session.id + '">' + session.name + '</option>');
                            });
                            // Auto-select current session if available
                            if (data.current_session_id) {
                                $('#session_id').val(data.current_session_id).trigger('change');
                            }
                        } else {
                            $('#session_id').append('<option value="">No sessions available</option>');
                        }
                    }).fail(function() {
                        alert('Error fetching sessions.');
                    });

                    // Clear terms
                    $('#term_id').empty().append('<option value="">Select Term</option>');
                } else {
                    // Reset if section is cleared
                    $('#session_id').empty().append('<option value="">Select Session (after selecting section)</option>');
                    $('#term_id').empty().append('<option value="">Select Term (after selecting session)</option>');
                }
            });

            $('#session_id').change(function() {
                var sessionId = $(this).val();
                if (sessionId) {
                    // Fetch terms for the selected session
                    $.get('{{ url("/pins/terms") }}/' + sessionId, function(data) {
                        $('#term_id').empty().append('<option value="">Select Term</option>');
                        if (data.terms.length > 0) {
                            data.terms.forEach(function(term) {
                                $('#term_id').append('<option value="' + term.id + '">' + term.name + '</option>');
                            });
                            // Auto-select current term if available
                            if (data.current_term_id) {
                                $('#term_id').val(data.current_term_id);
                            }
                        } else {
                            $('#term_id').append('<option value="">No terms available</option>');
                        }
                    }).fail(function() {
                        alert('Error fetching terms.');
                    });
                } else {
                    // Reset if session is cleared
                    $('#term_id').empty().append('<option value="">Select Term (after selecting session)</option>');
                }
            });
        });
    </script>

    @include('includes.edit_footer')