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
                                <h4>Manage Sessions</h4>
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

                                <!-- Section Filter Form -->
                                <form method="GET" action="{{ route('sessions.index') }}" id="sectionFilterForm" class="px-2">
                                    <div class="form-group row">
                                        <label class="col-md-2 col-form-label">Filter by Section</label>
                                        <div class="col-md-4">
                                            <select name="section_id" class="form-control" onchange="this.form.submit()">
                                                <option value="">-- All Sections --</option>
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

                                <!-- Sessions Table -->
                                @if ($sessions->isEmpty())
                                    <div class="alert alert-info">
                                        No sessions found{{ $sectionId ? ' for the selected section' : '' }}.
                                    </div>
                                @else
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Section</th>
                                                <th>Session Name</th>
                                                <th>Current Term</th>
                                                <th>Is Current</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($sessions as $session)
                                                <tr>
                                                    <td>{{ $session->section ? $session->section->section_name : 'N/A' }}</td>
                                                    <td>{{ $session->name }}</td>
                                                    <td>
                                                        @if ($session->terms->where('is_current', true)->first())
                                                            {{ $session->terms->where('is_current', true)->first()->name }}
                                                        @else
                                                            No current term
                                                        @endif
                                                        <br>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                                                                Set Term
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                @foreach ($session->terms as $term)
                                                                    <a class="dropdown-item {{ $term->is_current ? 'disabled' : '' }}" 
                                                                       href="{{ route('sessions.term.set', $term->id) }}"
                                                                       onclick="event.preventDefault(); document.getElementById('set-term-form-{{ $term->id }}').submit();">
                                                                        {{ $term->name }} {{ $term->is_current ? '(Current)' : '' }}
                                                                    </a>
                                                                    <form id="set-term-form-{{ $term->id }}" method="POST" action="{{ route('sessions.term.set', $term->id) }}">
                                                                        @csrf
                                                                        @method('POST')
                                                                    </form>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if ($session->is_current)
                                                            <span class="badge badge-success">Yes</span>
                                                        @else
                                                            <span class="badge badge-secondary">No</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $session->created_at->format('Y-m-d H:i:s') }}</td>
                                                    <td>
                                                        <!-- Edit Button with Modal Trigger -->
                                                        <button type="button" class="btn btn-sm btn-primary edit-session-btn" 
                                                                data-session-id="{{ $session->id }}" 
                                                                data-session-name="{{ $session->name }}"
                                                                data-toggle="modal" 
                                                                data-target="#editSessionModal">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <!-- Delete Button with Modal Trigger -->
                                                        <button type="button" class="btn btn-sm btn-danger delete-session-btn" data-session-id="{{ $session->id }}" data-toggle="modal" data-target="#deleteSessionModal">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                        <!-- Unset Button with Modal Trigger -->
                                                        @if ($session->is_current)
                                                            <button type="button" class="btn btn-sm btn-warning unset-session-btn" data-session-id="{{ $session->id }}" data-toggle="modal" data-target="#unsetSessionModal">
                                                                <i class="fas fa-times-circle"></i> Unset
                                                            </button>
                                                        @else
                                                            <!-- Set Button with Modal Trigger -->
                                                            <button type="button" class="btn btn-sm btn-success set-session-btn" data-session-id="{{ $session->id }}" data-toggle="modal" data-target="#setSessionModal">
                                                                <i class="fas fa-check-circle"></i> Set
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Edit Session Modal -->
            <div class="modal fade" id="editSessionModal" tabindex="-1" role="dialog" aria-labelledby="editSessionModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editSessionModalLabel">Edit Session</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="editSessionForm" method="POST" action="">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="session_name">Session Name</label>
                                    <input type="text" class="form-control" id="session_name" name="name" required>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteSessionModal" tabindex="-1" role="dialog" aria-labelledby="deleteSessionModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteSessionModalLabel">Confirm Deletion</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this session and its terms?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <form id="deleteSessionForm" method="POST" action="">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unset Confirmation Modal -->
            <div class="modal fade" id="unsetSessionModal" tabindex="-1" role="dialog" aria-labelledby="unsetSessionModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="unsetSessionModalLabel">Confirm Unset</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to unset this session and its terms as the current session?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <form id="unsetSessionForm" method="POST" action="">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-warning">Unset</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Set Confirmation Modal -->
            <div class="modal fade" id="setSessionModal" tabindex="-1" role="dialog" aria-labelledby="setSessionModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="setSessionModalLabel">Confirm Set</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to set this session as the current session? The First Term will be set as the current term.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <form id="setSessionForm" method="POST" action="">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-success">Set</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <!-- JavaScript for handling modals and dynamic form actions -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle Edit Modal
            const editButtons = document.querySelectorAll('.edit-session-btn');
            const editForm = document.getElementById('editSessionForm');
            const sessionNameInput = document.getElementById('session_name');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const sessionId = this.getAttribute('data-session-id');
                    const sessionName = this.getAttribute('data-session-name');
                    // Update the edit form action with the session ID
                    editForm.action = '{{ route("sessions.update", ["session" => "__SESSION_ID__"]) }}'.replace('__SESSION_ID__', sessionId);
                    // Prefill the session name
                    sessionNameInput.value = sessionName;
                });
            });

            // Handle Delete Modal
            const deleteButtons = document.querySelectorAll('.delete-session-btn');
            const deleteForm = document.getElementById('deleteSessionForm');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const sessionId = this.getAttribute('data-session-id');
                    // Update the delete form action with the session ID
                    deleteForm.action = '{{ route("sessions.destroy", ["session" => "__SESSION_ID__"]) }}'.replace('__SESSION_ID__', sessionId);
                });
            });

            // Handle Unset Modal
            const unsetButtons = document.querySelectorAll('.unset-session-btn');
            const unsetForm = document.getElementById('unsetSessionForm');

            unsetButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const sessionId = this.getAttribute('data-session-id');
                    // Update the unset form action with the session ID
                    unsetForm.action = '{{ route("sessions.unset", ["session" => "__SESSION_ID__"]) }}'.replace('__SESSION_ID__', sessionId);
                });
            });

            // Handle Set Modal
            const setButtons = document.querySelectorAll('.set-session-btn');
            const setForm = document.getElementById('setSessionForm');

            setButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const sessionId = this.getAttribute('data-session-id');
                    // Update the set form action with the session ID
                    setForm.action = '{{ route("sessions.set", ["session" => "__SESSION_ID__"]) }}'.replace('__SESSION_ID__', sessionId);
                });
            });
        });
    </script>
</body>