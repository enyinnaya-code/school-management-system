{{-- resources/views/payroll/process.blade.php --}}
@include('includes.head')

<head>
    <style>
        .form-group {
            margin-bottom: 1rem;
        }
        .text-muted-small {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>

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
                                <h4>Process Salary</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('finance.payroll.process.preview') }}" method="POST" id="processForm">
                                    @csrf
                                    <div class="row">
                                        <div class="form-group col-md-3">
                                            <label for="section_id">Select Section <span class="text-danger">*</span></label>
                                            <select name="section_id" id="section_id" class="form-control" required>
                                                <option value="">Choose Section</option>
                                                
                                                <!-- Special option for Administrative Staff -->
                                                <option value="0">
                                                    Administrative Staff (Principal, Bursar, etc.)
                                                </option>
                                                
                                                <optgroup label="Academic Sections">
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}">
                                                            {{ $section->section_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                            @error('section_id')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="session_id">Session <span class="text-danger">*</span></label>
                                            <select name="session_id" id="session_id" class="form-control" required>
                                                <option value="">Choose Session</option>
                                                <!-- Sessions will be populated on page load or via AJAX -->
                                            </select>
                                            @error('session_id')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="term_id">Term <span class="text-danger">*</span></label>
                                            <select name="term_id" id="term_id" class="form-control" required>
                                                <option value="">Choose Term</option>
                                                <!-- Terms will be populated based on selected session -->
                                            </select>
                                            @error('term_id')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="month">Select Month <span class="text-danger">*</span></label>
                                            <select name="month" id="month" class="form-control" required>
                                                <option value="">Choose Month</option>
                                                @for ($m = 1; $m <= 12; $m++)
                                                    <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                    </option>
                                                @endfor
                                            </select>
                                            @error('month')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-forward"></i> Proceed to Process
                                            </button>
                                            <a href="{{ route('finance.payroll.index') }}" class="btn btn-secondary ml-2">
                                                Cancel
                                            </a>
                                        </div>
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

    <script>
        $(document).ready(function() {
            // Load all sessions on page load (for admin staff)
            function loadAllSessions() {
                $.ajax({
                    url: '{{ route("finance.payroll.all.sessions") }}', // You'll need to create this route
                    type: 'GET',
                    success: function(sessions) {
                        var sessionSelect = $('#session_id');
                        sessionSelect.empty().append('<option value="">Choose Session</option>');

                        var currentSessionId = null;
                        sessions.forEach(function(session) {
                            var option = $('<option></option>')
                                .val(session.id)
                                .text(session.name);
                            sessionSelect.append(option);

                            if (session.is_current) {
                                currentSessionId = session.id;
                            }
                        });

                        if (currentSessionId) {
                            sessionSelect.val(currentSessionId).trigger('change');
                        }
                    },
                    error: function() {
                        alert('Error loading sessions');
                    }
                });
            }

            // Load terms for a session
            function loadTerms(sessionId) {
                var termSelect = $('#term_id');
                termSelect.empty().append('<option value="">Choose Term</option>');

                if (!sessionId) return;

                $.ajax({
                    url: '{{ route("finance.payroll.terms", ":session_id") }}'.replace(':session_id', sessionId),
                    type: 'GET',
                    success: function(terms) {
                        var currentTermId = null;
                        terms.forEach(function(term) {
                            var option = $('<option></option>')
                                .val(term.id)
                                .text(term.name);
                            termSelect.append(option);

                            if (term.is_current) {
                                currentTermId = term.id;
                            }
                        });

                        if (currentTermId) {
                            termSelect.val(currentTermId);
                        }
                    },
                    error: function() {
                        alert('Error loading terms');
                    }
                });
            }

            // On section change
            $('#section_id').change(function() {
                var sectionId = $(this).val();
                var sessionSelect = $('#session_id');
                var termSelect = $('#term_id');

                termSelect.empty().append('<option value="">Choose Term</option>');

                if (sectionId === "0") {
                    // Administrative Staff → load ALL sessions (not tied to section)
                    loadAllSessions();
                } else if (sectionId) {
                    // Regular section → load sessions for that section
                    $.ajax({
                        url: '{{ route("finance.payroll.sessions", ":section_id") }}'.replace(':section_id', sectionId),
                        type: 'GET',
                        success: function(sessions) {
                            sessionSelect.empty().append('<option value="">Choose Session</option>');
                            var currentSessionId = null;

                            sessions.forEach(function(session) {
                                var option = $('<option></option>')
                                    .val(session.id)
                                    .text(session.name);
                                sessionSelect.append(option);

                                if (session.is_current) {
                                    currentSessionId = session.id;
                                }
                            });

                            if (currentSessionId) {
                                sessionSelect.val(currentSessionId).trigger('change');
                            }
                        },
                        error: function() {
                            alert('Error fetching sessions for section');
                        }
                    });
                } else {
                    // No section selected
                    sessionSelect.empty().append('<option value="">Choose Session</option>');
                    termSelect.empty().append('<option value="">Choose Term</option>');
                }
            });

            // On session change → load terms
            $('#session_id').change(function() {
                loadTerms($(this).val());
            });

            // Initial load: if nothing selected, show all sessions for flexibility
            loadAllSessions();
        });
    </script>
</body>