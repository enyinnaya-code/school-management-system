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
                                <h4>Set Current Session</h4>
                            </div>

                            <div class="card-body">
                                <!-- Display Success/Error Messages -->
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <!-- Section Selection Form -->
                                <form method="GET" action="{{ route('sessions.create') }}" id="sectionForm">
                                    <div class="form-group row">
                                        <label class="col-md-2 col-form-label">Select School Section</label>
                                        <div class="col-md-4">
                                            <select name="section_id" class="form-control" onchange="this.form.submit()">
                                                <option value="">-- Select Section --</option>
                                                @foreach ($sections as $section)
                                                    <option value="{{ $section->id }}" {{ $sectionId == $section->id ? 'selected' : '' }}>
                                                        {{ $section->section_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </form>

                                <!-- Display Current Session and Term -->
                                @if ($sectionId)
                                    @if ($currentSession)
                                        <div class="alert alert-info">
                                            Current Session for {{ $currentSession->section->section_name }}: {{ $currentSession->name }}
                                            @if ($currentSession->terms->where('is_current', true)->first())
                                                <br>Current Term: {{ $currentSession->terms->where('is_current', true)->first()->name }}
                                            @else
                                                <br>No current term set for this session.
                                            @endif
                                        </div>
                                        <!-- Set Current Term -->
                                        <h6 class="mt-4">Set Current Term for {{ $currentSession->name }}</h6>
                                        @foreach ($currentSession->terms as $term)
                                            <div class="mb-4">
                                                {{ $term->name }}
                                                @if ($term->is_current)
                                                    <span class="badge badge-success">Current</span>
                                                @else
                                                    <form method="POST" action="{{ route('sessions.term.set', $term->id) }}" style="display: inline; padding-left:1rem;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary">Set as Current</button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="alert alert-info">
                                            No current session set for the selected section.
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-info">
                                        Please select a section to view or set the current session.
                                    </div>
                                @endif

                                <!-- Session Form (only show if a section is selected) -->
                                @if ($sectionId)
                                    <form method="POST" action="{{ route('sessions.store') }}" id="sessionForm">
                                        @csrf
                                        <input type="hidden" name="section_id" value="{{ $sectionId }}">

                                        <div class="form-group row px-2 pt-4">
                                            <label class="col-md-2 col-form-label">Session Name</label>
                                            <div class="col-md-4">
                                                <input type="text" name="name" id="sessionName" class="form-control" placeholder="e.g., 2025/2026" value="{{ old('name') }}" required>
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group mt-4">
                                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#confirmSessionModal">
                                                <i class="fas fa-save"></i> Set Session
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal for Session Creation -->
    <div class="modal fade" id="confirmSessionModal" tabindex="-1" role="dialog" aria-labelledby="confirmSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmSessionModalLabel">Confirm Session Creation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    This will create the session <strong id="modalSessionName"></strong> and the following Terms:
                    <ul>
                        <li>First Term</li>
                        <li>Second Term</li>
                        <li>Third Term</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmCreateBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <!-- JavaScript for Modal Handling -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const confirmModal = document.getElementById('confirmSessionModal');
            const modalSessionName = document.getElementById('modalSessionName');
            const sessionNameInput = document.getElementById('sessionName');
            const confirmCreateBtn = document.getElementById('confirmCreateBtn');
            const sessionForm = document.getElementById('sessionForm');

            // Update modal content when shown
            if (confirmModal) {
                confirmModal.addEventListener('show.bs.modal', function () {
                    if (sessionNameInput && modalSessionName) {
                        if (sessionNameInput.value.trim() === '') {
                            alert('Please enter a session name.');
                            return;
                        }
                        modalSessionName.textContent = sessionNameInput.value;
                    }
                });
            }

            // Submit form on confirm
            if (confirmCreateBtn) {
                confirmCreateBtn.addEventListener('click', function () {
                    if (sessionForm) {
                        sessionForm.submit();
                    }
                });
            }
        });
    </script>
</body>