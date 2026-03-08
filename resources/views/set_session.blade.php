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
                                <h4>Set Current Session</h4>
                                <a href="{{ route('sessions.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-list"></i> All Sessions
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

                                {{-- ── CURRENT SESSION STATUS ── --}}
                                @if($currentSession)
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Current Session:</strong> {{ $currentSession->name }}
                                        &nbsp;&bull;&nbsp;
                                        <strong>Current Term:</strong>
                                        {{ $currentSession->terms->where('is_current', true)->first()->name ?? 'None set' }}
                                    </div>

                                    {{-- Set Current Term --}}
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-calendar-check mr-1"></i>
                                                Set Current Term for <strong>{{ $currentSession->name }}</strong>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap" style="gap:12px">
                                                @foreach($currentSession->terms as $term)
                                                    <div class="d-flex align-items-center border rounded px-3 py-2"
                                                         style="gap:10px; background: {{ $term->is_current ? '#e8f5e9' : '#fafafa' }}">
                                                        <span class="font-weight-bold">{{ $term->name }}</span>
                                                        @if($term->is_current)
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check mr-1"></i>Current
                                                            </span>
                                                        @else
                                                            <form method="POST"
                                                                  action="{{ route('sessions.term.set', $term->id) }}"
                                                                  style="margin:0">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                                    Set as Current
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Unset current session --}}
                                    <div class="card border-warning mb-4">
                                        <div class="card-body d-flex justify-content-between align-items-center py-2">
                                            <span class="text-muted small">
                                                <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                                                To start a new session, unset the current one first.
                                            </span>
                                            <form method="POST"
                                                  action="{{ route('sessions.unset', $currentSession->id) }}"
                                                  onsubmit="return confirm('Are you sure you want to unset the current session?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-times mr-1"></i> Unset Current Session
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        No session is currently active. Create one below.
                                    </div>
                                @endif

                                {{-- ── CREATE NEW SESSION (only when none is active) ── --}}
                                @if(!$currentSession)
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-plus-circle mr-1"></i> Create New Session
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('sessions.store') }}" id="sessionForm">
                                            @csrf
                                            <div class="form-group row align-items-center">
                                                <label class="col-md-2 col-form-label font-weight-bold">
                                                    Session Name
                                                </label>
                                                <div class="col-md-4">
                                                    <input type="text"
                                                           name="name"
                                                           id="sessionName"
                                                           class="form-control @error('name') is-invalid @enderror"
                                                           placeholder="e.g. 2025/2026"
                                                           value="{{ old('name') }}"
                                                           required>
                                                    @error('name')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">
                                                        This session will apply to <strong>all arms/sections</strong> of the school.
                                                        Three terms (First, Second, Third) will be created automatically.
                                                    </small>
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="button"
                                                            class="btn btn-success"
                                                            data-toggle="modal"
                                                            data-target="#confirmSessionModal">
                                                        <i class="fas fa-save mr-1"></i> Set Session
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div class="modal fade" id="confirmSessionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Session Creation</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>
                        You are about to create the session
                        <strong id="modalSessionName"></strong>
                        for <strong>the whole school</strong>.
                    </p>
                    <p>The following terms will be created automatically:</p>
                    <ul>
                        <li>First Term <span class="badge badge-success">set as current</span></li>
                        <li>Second Term</li>
                        <li>Third Term</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmCreateBtn">
                        <i class="fas fa-check mr-1"></i> Confirm & Create
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sessionNameInput = document.getElementById('sessionName');
            const modalSessionName = document.getElementById('modalSessionName');
            const confirmCreateBtn = document.getElementById('confirmCreateBtn');
            const sessionForm      = document.getElementById('sessionForm');
            const confirmModal     = document.getElementById('confirmSessionModal');

            if (confirmModal) {
                confirmModal.addEventListener('show.bs.modal', function () {
                    const name = sessionNameInput ? sessionNameInput.value.trim() : '';
                    if (!name) {
                        // Prevent modal from opening if name is empty
                        alert('Please enter a session name first.');
                        // Bootstrap 4 way to stop modal
                        $(confirmModal).modal('hide');
                        return;
                    }
                    if (modalSessionName) modalSessionName.textContent = '"' + name + '"';
                });
            }

            if (confirmCreateBtn) {
                confirmCreateBtn.addEventListener('click', function () {
                    if (sessionForm) sessionForm.submit();
                });
            }
        });
    </script>
</body>