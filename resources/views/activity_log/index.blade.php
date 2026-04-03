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

                        {{-- Page Header --}}
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h4 class="mb-0">Activity Log</h4>
                                        <small class="text-muted">Track all user actions across the system</small>
                                    </div>
                                    @if(auth()->user()->user_type == 1)
                                    <form action="{{ route('activity.log.clear') }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to clear ALL activity logs? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i data-feather="trash-2" style="width:14px;height:14px;"></i> Clear All Logs
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                        @endif

                        {{-- Filter Card --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i data-feather="filter" style="width:16px;height:16px;"></i> Filter Logs
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('activity.log') }}">
                                    <div class="row align-items-end">
                                        <div class="form-group col-md-3">
                                            <label class="text-sm font-weight-bold">User (Name or Email)</label>
                                            <input type="text" name="user_search" class="form-control form-control-sm"
                                                value="{{ request('user_search') }}"
                                                placeholder="Search by name or email...">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="text-sm font-weight-bold">Action Type</label>
                                            <select name="action" class="form-control form-control-sm">
                                                <option value="">All Actions</option>
                                                <option value="Created" {{ request('action') == 'Created' ? 'selected' : '' }}>Created</option>
                                                <option value="Updated" {{ request('action') == 'Updated' ? 'selected' : '' }}>Updated</option>
                                                <option value="Deleted" {{ request('action') == 'Deleted' ? 'selected' : '' }}>Deleted</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="text-sm font-weight-bold">From Date</label>
                                            <input type="date" name="from" class="form-control form-control-sm"
                                                value="{{ request('from') }}">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="text-sm font-weight-bold">To Date</label>
                                            <input type="date" name="to" class="form-control form-control-sm"
                                                value="{{ request('to') }}">
                                        </div>
                                        <div class="form-group col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                                <i data-feather="search" style="width:14px;height:14px;"></i> Filter
                                            </button>
                                            <a href="{{ route('activity.log') }}" class="btn btn-secondary btn-sm">
                                                <i data-feather="x" style="width:14px;height:14px;"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Active Filter Badges --}}
                        @if(request()->hasAny(['user_search', 'action', 'from', 'to']))
                        <div class="mb-3">
                            <h6>Active Filters:</h6>
                            <div class="active-filters">
                                @if(request('user_search'))
                                    <span class="badge badge-info mr-2">User: {{ request('user_search') }}</span>
                                @endif
                                @if(request('action'))
                                    <span class="badge badge-info mr-2">Action: {{ request('action') }}</span>
                                @endif
                                @if(request('from'))
                                    <span class="badge badge-info mr-2">From: {{ request('from') }}</span>
                                @endif
                                @if(request('to'))
                                    <span class="badge badge-info mr-2">To: {{ request('to') }}</span>
                                @endif
                                <a href="{{ route('activity.log') }}" class="btn btn-sm m-1 btn-outline-danger">
                                    <i class="fas fa-times"></i> Clear All
                                </a>
                            </div>
                        </div>
                        @endif

                        {{-- Results Summary + Per-page --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}
                                of {{ $logs->total() }} records
                            </small>
                            <form method="GET" action="{{ route('activity.log') }}" class="d-flex align-items-center">
                                {{-- Preserve existing filters --}}
                                @foreach(request()->except('per_page', 'page') as $key => $val)
                                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                @endforeach
                                <label class="text-muted small mr-2 mb-0">Rows per page:</label>
                                <select name="per_page" class="form-control form-control-sm" style="width:75px;"
                                    onchange="this.form.submit()">
                                    @foreach([25, 50, 100, 200] as $size)
                                        <option value="{{ $size }}" {{ request('per_page', 50) == $size ? 'selected' : '' }}>
                                            {{ $size }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>

                        {{-- Logs Table --}}
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:50px;">#</th>
                                                <th style="width:180px;">User</th>
                                                <th style="width:100px;">Action</th>
                                                <th>Description</th>
                                                <th style="width:130px;">IP Address</th>
                                                <th style="width:140px;">Date & Time</th>
                                                <th style="width:60px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($logs as $index => $log)
                                            @php
                                                $props = is_string($log->properties)
                                                    ? json_decode($log->properties)
                                                    : $log->properties;
                                                $props = $props ?? new stdClass();

                                                $ip    = $props->ip ?? '—';
                                                $url   = $props->url ?? '—';
                                                $agent = $props->user_agent ?? null;
                                                $input = (array) ($props->input ?? []);

                                                $badgeClass = match(true) {
                                                    str_starts_with($log->description, 'Created') => 'badge-success',
                                                    str_starts_with($log->description, 'Updated') => 'badge-warning',
                                                    str_starts_with($log->description, 'Deleted') => 'badge-danger',
                                                    default => 'badge-secondary',
                                                };
                                                $actionLabel = explode(':', $log->description)[0] ?? 'Action';
                                                $causer      = $causers[$log->causer_id] ?? null;
                                                $createdAt   = \Carbon\Carbon::parse($log->created_at);
                                            @endphp
                                            <tr>
                                                <td class="text-muted small align-middle">
                                                    {{ $logs->firstItem() + $index }}
                                                </td>
                                                <td class="align-middle">
                                                    @if($causer)
                                                        <div class="font-weight-bold" style="line-height:1.2;">{{ $causer->name }}</div>
                                                        <small class="text-muted">{{ $causer->email }}</small>
                                                    @else
                                                        <span class="text-muted">System</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge {{ $badgeClass }}">{{ $actionLabel }}</span>
                                                </td>
                                                <td class="align-middle" style="max-width:320px;">
                                                    <div class="text-truncate" style="max-width:300px;"
                                                        title="{{ $log->description }}">
                                                        {{ $log->description }}
                                                    </div>
                                                    <small class="text-muted text-truncate d-block"
                                                        style="max-width:300px;" title="{{ $url }}">
                                                        {{ $url }}
                                                    </small>
                                                </td>
                                                <td class="align-middle">
                                                    <code style="font-size:12px;">{{ $ip }}</code>
                                                </td>
                                                <td class="align-middle">
                                                    <div style="line-height:1.2;">{{ $createdAt->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ $createdAt->format('h:i A') }}</small>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <button type="button"
                                                        class="btn btn-xs btn-outline-info btn-view-log"
                                                        data-id="{{ $log->id }}"
                                                        data-user="{{ $causer->name ?? 'System' }}"
                                                        data-email="{{ $causer->email ?? '' }}"
                                                        data-timestamp="{{ $createdAt->format('D, M d Y \a\t h:i:s A') }}"
                                                        data-humantime="{{ $createdAt->diffForHumans() }}"
                                                        data-action="{{ $actionLabel }}"
                                                        data-badge="{{ $badgeClass }}"
                                                        data-ip="{{ $ip }}"
                                                        data-url="{{ $url }}"
                                                        data-agent="{{ $agent }}"
                                                        data-description="{{ $log->description }}"
                                                        data-input="{{ htmlspecialchars(json_encode($input), ENT_QUOTES) }}">
                                                        <i data-feather="eye" style="width:13px;height:13px;"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <i data-feather="inbox" style="width:40px;height:40px;color:#ccc;"></i>
                                                    <p class="text-muted mt-2 mb-0">No activity logs found.</p>
                                                    <small class="text-muted">Actions performed by users will appear here.</small>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @if($logs->hasPages())
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
                                </small>
                                {{ $logs->links() }}
                            </div>
                            @endif
                        </div>

                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    {{-- ====== Single Shared Modal ====== --}}
    <div class="modal fade" id="logDetailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i data-feather="activity" style="width:16px;height:16px;"></i>
                        Activity Detail
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">User</label>
                            <p class="font-weight-bold mb-1" id="modal-user">—</p>
                            <p class="text-muted small mb-0" id="modal-email"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Timestamp</label>
                            <p class="mb-0" id="modal-timestamp">—</p>
                            <p class="text-muted small mb-0" id="modal-humantime"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Action</label>
                            <p><span class="badge" id="modal-action-badge"></span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">IP Address</label>
                            <p><code id="modal-ip">—</code></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase">Description</label>
                        <p class="mb-1" id="modal-description"></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase">Full URL</label>
                        <p class="text-break"><code id="modal-url">—</code></p>
                    </div>
                    <div class="mb-3" id="modal-agent-row">
                        <label class="text-muted small text-uppercase">User Agent</label>
                        <p class="small text-muted text-break mb-0" id="modal-agent"></p>
                    </div>
                    <div id="modal-input-row" class="mb-0" style="display:none;">
                        <label class="text-muted small text-uppercase">Submitted Data</label>
                        <div class="bg-light rounded p-3" style="font-size:13px;">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-input-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{-- ====== End Modal ====== --}}

    <script>
        $(document).ready(function () {
            if (typeof feather !== 'undefined') feather.replace();

            $('#logDetailModal').on('shown.bs.modal', function () {
                if (typeof feather !== 'undefined') feather.replace();
            });

            $(document).on('click', '.btn-view-log', function () {
                var $btn = $(this);

                $('#modal-user').text($btn.data('user'));
                $('#modal-email').text($btn.data('email'));
                $('#modal-timestamp').text($btn.data('timestamp'));
                $('#modal-humantime').text($btn.data('humantime'));
                $('#modal-ip').text($btn.data('ip'));
                $('#modal-url').text($btn.data('url'));
                $('#modal-description').text($btn.data('description'));

                var badge = $('#modal-action-badge');
                badge.text($btn.data('action'));
                badge.attr('class', 'badge ' + $btn.data('badge'));

                var agent = $btn.data('agent');
                if (agent) {
                    $('#modal-agent').text(agent);
                    $('#modal-agent-row').show();
                } else {
                    $('#modal-agent-row').hide();
                }

                try {
                    var input = $btn.data('input');
                    if (typeof input === 'string') input = JSON.parse(input);
                    var keys = Object.keys(input || {});
                    if (keys.length > 0) {
                        var rows = '';
                        keys.forEach(function (key) {
                            var val = Array.isArray(input[key])
                                ? input[key].join(', ')
                                : input[key];
                            rows += '<tr><td><code>' + $('<span>').text(key).html() + '</code></td>'
                                  + '<td>' + $('<span>').text(val).html() + '</td></tr>';
                        });
                        $('#modal-input-body').html(rows);
                        $('#modal-input-row').show();
                    } else {
                        $('#modal-input-row').hide();
                    }
                } catch (e) {
                    $('#modal-input-row').hide();
                }

                $('#logDetailModal').modal('show');
            });
        });
    </script>
</body>