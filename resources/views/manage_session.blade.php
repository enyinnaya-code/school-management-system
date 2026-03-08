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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Manage Sessions</h4>
                                <a href="{{ route('sessions.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Set New Session
                                </a>
                            </div>

                            <div class="card-body">

                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    </div>
                                @endif

                                @if($sessions->isEmpty())
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        No sessions found. <a href="{{ route('sessions.create') }}">Create one now</a>.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Session Name</th>
                                                    <th>Current Term</th>
                                                    <th class="text-center">Status</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sessions as $session)
                                                <tr class="{{ $session->is_current ? 'table-success' : '' }}">
                                                    <td class="font-weight-bold">{{ $session->name }}</td>
                                                    <td>
                                                        {{-- Current term label --}}
                                                        @php $currentTerm = $session->terms->where('is_current', true)->first(); @endphp
                                                        @if($currentTerm)
                                                            <span class="badge badge-info">{{ $currentTerm->name }}</span>
                                                        @else
                                                            <span class="text-muted small">None set</span>
                                                        @endif

                                                        {{-- Set term dropdown --}}
                                                        <div class="dropdown d-inline-block ml-2">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                    type="button" data-toggle="dropdown">
                                                                Change
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                @foreach($session->terms as $term)
                                                                    <a class="dropdown-item {{ $term->is_current ? 'disabled font-weight-bold' : '' }}"
                                                                       href="#"
                                                                       onclick="event.preventDefault();
                                                                                document.getElementById('set-term-form-{{ $term->id }}').submit();">
                                                                        {{ $term->name }}
                                                                        @if($term->is_current)
                                                                            <i class="fas fa-check ml-1 text-success"></i>
                                                                        @endif
                                                                    </a>
                                                                    <form id="set-term-form-{{ $term->id }}"
                                                                          method="POST"
                                                                          action="{{ route('sessions.term.set', $term->id) }}"
                                                                          style="display:none">
                                                                        @csrf
                                                                    </form>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($session->is_current)
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check-circle mr-1"></i>Current
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td class="small text-muted">
                                                        {{ $session->created_at->format('d M Y') }}
                                                    </td>
                                                    <td>
                                                        {{-- Edit --}}
                                                        <button type="button"
                                                                class="btn btn-sm btn-primary edit-session-btn"
                                                                data-session-id="{{ $session->id }}"
                                                                data-session-name="{{ $session->name }}"
                                                                data-toggle="modal"
                                                                data-target="#editSessionModal">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>

                                                        {{-- Set / Unset --}}
                                                        @if($session->is_current)
                                                            <button type="button"
                                                                    class="btn btn-sm btn-warning unset-session-btn"
                                                                    data-session-id="{{ $session->id }}"
                                                                    data-toggle="modal"
                                                                    data-target="#unsetSessionModal">
                                                                <i class="fas fa-times-circle"></i> Unset
                                                            </button>
                                                        @else
                                                            <button type="button"
                                                                    class="btn btn-sm btn-success set-session-btn"
                                                                    data-session-id="{{ $session->id }}"
                                                                    data-session-name="{{ $session->name }}"
                                                                    data-toggle="modal"
                                                                    data-target="#setSessionModal">
                                                                <i class="fas fa-check-circle"></i> Set
                                                            </button>

                                                            {{-- Delete only for inactive sessions --}}
                                                            <button type="button"
                                                                    class="btn btn-sm btn-danger delete-session-btn"
                                                                    data-session-id="{{ $session->id }}"
                                                                    data-toggle="modal"
                                                                    data-target="#deleteSessionModal">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- ── MODALS ── --}}

    {{-- Edit Modal --}}
    <div class="modal fade" id="editSessionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Session</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="editSessionForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Session Name</label>
                            <input type="text" class="form-control" id="edit_session_name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteSessionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <i class="fas fa-exclamation-triangle text-danger mr-1"></i>
                    Are you sure you want to delete this session and all its terms? This cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteSessionForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Unset Modal --}}
    <div class="modal fade" id="unsetSessionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Unset</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                    This will unset the current session and term for the <strong>whole school</strong>.
                    Are you sure?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="unsetSessionForm" method="POST" action="">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-times-circle mr-1"></i> Unset
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Set Modal --}}
    <div class="modal fade" id="setSessionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Set as Current</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>
                        You are about to set <strong id="setSessionName"></strong> as the current session
                        for the <strong>whole school</strong>.
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        First Term will automatically become the current term.
                        Any previously active session will be unset.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="setSessionForm" method="POST" action="">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle mr-1"></i> Confirm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Edit
            document.querySelectorAll('.edit-session-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id   = this.dataset.sessionId;
                    const name = this.dataset.sessionName;
                    document.getElementById('editSessionForm').action =
                        '{{ route("sessions.update", ["session" => "__ID__"]) }}'.replace('__ID__', id);
                    document.getElementById('edit_session_name').value = name;
                });
            });

            // Delete
            document.querySelectorAll('.delete-session-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.sessionId;
                    document.getElementById('deleteSessionForm').action =
                        '{{ route("sessions.destroy", ["session" => "__ID__"]) }}'.replace('__ID__', id);
                });
            });

            // Unset
            document.querySelectorAll('.unset-session-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.sessionId;
                    document.getElementById('unsetSessionForm').action =
                        '{{ route("sessions.unset", ["session" => "__ID__"]) }}'.replace('__ID__', id);
                });
            });

            // Set
            document.querySelectorAll('.set-session-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id   = this.dataset.sessionId;
                    const name = this.dataset.sessionName;
                    document.getElementById('setSessionForm').action =
                        '{{ route("sessions.set", ["session" => "__ID__"]) }}'.replace('__ID__', id);
                    document.getElementById('setSessionName').textContent = '"' + name + '"';
                });
            });

        });
    </script>
</body>