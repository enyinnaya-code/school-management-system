@include('includes.head')

<body>
<div class="loader"></div>
<div id="app">
    <div class="main-wrapper main-wrapper-1">
        <div class="navbar-bg"></div>
        @include('includes.right_top_nav')
        @include('includes.side_nav')

        <div class="main-content pt-5 mt-5">
            <section class="section mb-5 pb-5 px-0">
                <div class="col-12">

                    {{-- ── Page Header ─────────────────────────────────────────── --}}
                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <h4 class="mb-0">
                                <i class="fas fa-cog text-primary mr-2"></i> Result Access & Term Settings
                            </h4>
                            <small class="text-muted">
                                Block or unblock students from viewing results &bull; Set resumption date &amp; school fees
                            </small>
                        </div>
                    </div>

                    {{-- ── Flash Messages ──────────────────────────────────────── --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    {{-- ── Session / Term Picker ───────────────────────────────── --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('results.settings.index') }}"
                                  id="sessionTermForm" class="form-inline flex-wrap" style="gap:10px;">
                                <div class="form-group mr-3">
                                    <label class="mr-2 font-weight-bold">Session:</label>
                                    <select name="session_id" class="form-control"
                                            onchange="document.getElementById('sessionTermForm').submit()">
                                        @foreach($sessions as $sess)
                                            <option value="{{ $sess->id }}"
                                                {{ $selectedSession?->id == $sess->id ? 'selected' : '' }}>
                                                {{ $sess->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label class="mr-2 font-weight-bold">Term:</label>
                                    <select name="term_id" class="form-control"
                                            onchange="document.getElementById('sessionTermForm').submit()">
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}"
                                                {{ $selectedTerm?->id == $term->id ? 'selected' : '' }}>
                                                {{ $term->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter mr-1"></i> Apply
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($selectedSession && $selectedTerm)

                    {{-- ══════════════════════════════════════════════════════════
                         SECTION 1 — Result Access Restrictions
                    ══════════════════════════════════════════════════════════ --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    <i class="fas fa-ban text-danger mr-2"></i>Result Access Restrictions
                                </h5>
                                <small class="text-muted">
                                    {{ $selectedSession->name }} — {{ $selectedTerm->name }}
                                    &nbsp;|&nbsp;
                                    <span class="badge badge-danger">{{ $blockedIds->count() }} blocked</span>
                                    &nbsp;
                                    <span class="badge badge-success" id="activeCountBadge"></span>
                                </small>
                            </div>
                        </div>
                        <div class="card-body">

                            {{-- ── Filters Form ─────────────────────────────────── --}}
                            <form method="GET" action="{{ route('results.settings.index') }}" id="filterForm">
                                <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">
                                <div class="row mb-3 align-items-end">
                                    <div class="col-sm-3 mb-2">
                                        <label class="small font-weight-bold mb-1">Search</label>
                                        <input type="text" name="search" class="form-control form-control-sm"
                                               placeholder="Name or Adm. No…" value="{{ $search }}">
                                    </div>
                                    <div class="col-sm-2 mb-2">
                                        <label class="small font-weight-bold mb-1">Section</label>
                                        <select name="section_id" class="form-control form-control-sm" id="sectionPicker">
                                            <option value="">All Sections</option>
                                            @foreach($sections as $sec)
                                                <option value="{{ $sec->id }}"
                                                    {{ $filterSectionId == $sec->id ? 'selected' : '' }}>
                                                    {{ $sec->section_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-2 mb-2">
                                        <label class="small font-weight-bold mb-1">Class</label>
                                        <select name="class_id" class="form-control form-control-sm" id="classPicker">
                                            <option value="">All Classes</option>
                                            @foreach($classes as $cls)
                                                <option value="{{ $cls->id }}"
                                                    {{ $filterClassId == $cls->id ? 'selected' : '' }}
                                                    data-section="{{ $cls->section_id }}">
                                                    {{ $cls->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-2 mb-2">
                                        <label class="small font-weight-bold mb-1">Status</label>
                                        <select name="status" class="form-control form-control-sm" id="statusPicker">
                                            <option value="">All Students</option>
                                            <option value="blocked" {{ ($filterStatus ?? '') === 'blocked' ? 'selected' : '' }}>Blocked Only</option>
                                            <option value="active"  {{ ($filterStatus ?? '') === 'active'  ? 'selected' : '' }}>Active Only</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-1 mb-2">
                                        <label class="small font-weight-bold mb-1">Show</label>
                                        <select name="per_page" class="form-control form-control-sm" id="perPagePicker">
                                            @foreach([10, 20, 50, 100] as $pp)
                                                <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-2 mb-2 d-flex" style="gap:5px;">
                                        <button type="submit" class="btn btn-sm btn-secondary flex-fill">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('results.settings.index', ['session_id' => $selectedSession->id, 'term_id' => $selectedTerm->id]) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Clear filters">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>

                            {{-- ── Selection Summary Bar ────────────────────────── --}}
                            <div id="selectionBar" class="alert alert-info py-2 px-3 mb-3 d-none"
                                 style="border-radius:6px;">
                                <span id="selectionSummary"></span>
                            </div>

                            {{-- ── Bulk Action Toolbars ──────────────────────────── --}}
                            <div class="d-flex align-items-center flex-wrap mb-2" style="gap:6px;">

                                {{-- Select helpers --}}
                                <div class="btn-group btn-group-sm mr-2">
                                    <button type="button" class="btn btn-outline-secondary" id="selectAllBtn"
                                            title="Select all visible students">
                                        <i class="fas fa-check-square mr-1"></i>All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="deselectAllBtn"
                                            title="Deselect all">
                                        <i class="fas fa-square mr-1"></i>None
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="selectBlockedBtn"
                                            title="Select all blocked students on this page">
                                        <i class="fas fa-ban mr-1"></i>Blocked
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="selectActiveBtn"
                                            title="Select all active students on this page">
                                        <i class="fas fa-check mr-1"></i>Active
                                    </button>
                                </div>

                                <div class="border-left pl-2 d-flex align-items-center flex-wrap" style="gap:6px;">
                                    {{-- Block selected (active rows only) --}}
                                    <input type="text" id="bulkReasonInput"
                                           class="form-control form-control-sm"
                                           placeholder="Block reason (default: Owing school fees)"
                                           style="max-width:260px;">
                                    <button type="button" class="btn btn-sm btn-danger" id="blockSelectedBtn">
                                        <i class="fas fa-ban mr-1"></i> Block Selected
                                    </button>

                                    <div class="border-left mx-1"></div>

                                    {{-- Unblock selected (blocked rows only) --}}
                                    <button type="button" class="btn btn-sm btn-success" id="unblockSelectedBtn">
                                        <i class="fas fa-unlock mr-1"></i> Unblock Selected
                                    </button>
                                </div>
                            </div>

                            {{-- ── Hidden Bulk Block Form ────────────────────────── --}}
                            <form method="POST" action="{{ route('results.settings.bulkBlock') }}"
                                  id="bulkBlockForm" style="display:none;">
                                @csrf
                                <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">
                                <input type="hidden" name="reason"     id="bulkBlockReason">
                                {{-- student_ids[] injected by JS --}}
                            </form>

                            {{-- ── Hidden Bulk Unblock Form ─────────────────────── --}}
                            <form method="POST" action="{{ route('results.settings.bulkUnblock') }}"
                                  id="bulkUnblockForm" style="display:none;">
                                @csrf
                                <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">
                                {{-- student_ids[] injected by JS --}}
                            </form>

                            {{-- ── Students Table ───────────────────────────────── --}}
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="40" class="text-center">
                                                <input type="checkbox" id="masterChk" title="Toggle all">
                                            </th>
                                            <th>Student Name</th>
                                            <th>Adm. No</th>
                                            <th>Section</th>
                                            <th>Class</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Single Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $student)
                                        @php
                                            $isBlocked   = $blockedIds->contains($student->id);
                                            $blockReason = $blockedReasons->get($student->id, '');
                                        @endphp
                                        <tr class="{{ $isBlocked ? 'table-danger' : '' }}">
                                            <td class="text-center">
                                                <input type="checkbox"
                                                       value="{{ $student->id }}"
                                                       class="rowChk {{ $isBlocked ? 'blockedChk' : 'activeChk' }}"
                                                       data-state="{{ $isBlocked ? 'blocked' : 'active' }}">
                                            </td>
                                            <td class="font-weight-bold">{{ $student->name }}</td>
                                            <td>{{ $student->admission_no }}</td>
                                            <td>{{ $student->class?->section?->section_name ?? '—' }}</td>
                                            <td>{{ $student->class?->name ?? '—' }}</td>
                                            <td class="text-center">
                                                @if($isBlocked)
                                                    <span class="badge badge-danger">Blocked</span>
                                                    @if($blockReason)
                                                        <br>
                                                        <small class="text-muted" style="font-size:10px;">
                                                            {{ $blockReason }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-success">Active</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($isBlocked)
                                                    {{-- Single Unblock --}}
                                                    <form method="POST"
                                                          action="{{ route('results.settings.toggleBlock') }}"
                                                          style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                        <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                                        <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">
                                                        <input type="hidden" name="action"     value="unblock">
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                                onclick="return confirm('Restore access for {{ addslashes($student->name) }}?')">
                                                            <i class="fas fa-unlock"></i> Unblock
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Single Block (opens modal) --}}
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger quickBlockBtn"
                                                            data-sid="{{ $student->id }}"
                                                            data-sname="{{ $student->name }}">
                                                        <i class="fas fa-ban"></i> Block
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-search mr-2"></i>No students found matching the filters.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- ── Pagination ───────────────────────────────────── --}}
                            @if($students->hasPages())
                            @php
                                $curPage  = $students->currentPage();
                                $lastPage = $students->lastPage();
                                $fromPg   = max(1, $curPage - 2);
                                $toPg     = min($lastPage, $curPage + 2);
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap"
                                 style="gap:8px;">
                                <small class="text-muted">
                                    Showing <strong>{{ $students->firstItem() }}</strong>–<strong>{{ $students->lastItem() }}</strong>
                                    of <strong>{{ $students->total() }}</strong>
                                    @if($search || $filterSectionId || $filterClassId || ($filterStatus ?? ''))
                                        <span class="badge badge-info ml-1">filtered</span>
                                    @endif
                                </small>
                                <div class="d-flex flex-wrap" style="gap:3px;">
                                    {{-- Prev --}}
                                    @if($students->onFirstPage())
                                        <span class="btn btn-sm btn-outline-secondary disabled">
                                            <i class="fas fa-chevron-left"></i>
                                        </span>
                                    @else
                                        <a href="{{ $students->previousPageUrl() }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    @endif

                                    {{-- First page + ellipsis --}}
                                    @if($fromPg > 1)
                                        <a href="{{ $students->url(1) }}" class="btn btn-sm btn-outline-primary">1</a>
                                        @if($fromPg > 2)
                                            <span class="btn btn-sm disabled">…</span>
                                        @endif
                                    @endif

                                    {{-- Page window --}}
                                    @for($pg = $fromPg; $pg <= $toPg; $pg++)
                                        @if($pg == $curPage)
                                            <span class="btn btn-sm btn-primary">{{ $pg }}</span>
                                        @else
                                            <a href="{{ $students->url($pg) }}"
                                               class="btn btn-sm btn-outline-primary">{{ $pg }}</a>
                                        @endif
                                    @endfor

                                    {{-- Ellipsis + last page --}}
                                    @if($toPg < $lastPage)
                                        @if($toPg < $lastPage - 1)
                                            <span class="btn btn-sm disabled">…</span>
                                        @endif
                                        <a href="{{ $students->url($lastPage) }}"
                                           class="btn btn-sm btn-outline-primary">{{ $lastPage }}</a>
                                    @endif

                                    {{-- Next --}}
                                    @if($students->hasMorePages())
                                        <a href="{{ $students->nextPageUrl() }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @else
                                        <span class="btn btn-sm btn-outline-secondary disabled">
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>{{-- /restrictions card --}}


                    {{-- ══════════════════════════════════════════════════════════
                         SECTION 2 — Term Settings
                    ══════════════════════════════════════════════════════════ --}}
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-alt text-primary mr-2"></i>Term Settings
                                    </h5>
                                    <small class="text-muted">
                                        Shown on the report card footer for
                                        {{ $selectedSession->name }} — {{ $selectedTerm->name }}
                                    </small>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('results.settings.saveTermSettings') }}">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                        <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <i class="fas fa-door-open mr-1 text-success"></i>
                                                        Next Term Resumption Date
                                                    </label>
                                                    <input type="date" name="resumption_date" class="form-control"
                                                           value="{{ $termSettings?->resumption_date?->format('Y-m-d') ?? '' }}">
                                                </div>
                                            </div>
                                            {{-- <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <i class="fas fa-money-bill-wave mr-1 text-warning"></i>
                                                        School Fees (₦)
                                                    </label>
                                                    <input type="number" name="school_fees" class="form-control"
                                                           step="0.01" min="0" placeholder="e.g. 50000"
                                                           value="{{ $termSettings?->school_fees ?? '' }}">
                                                </div>
                                            </div> --}}
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <i class="fas fa-calendar-check mr-1 text-danger"></i>
                                                        Fees Payable By
                                                    </label>
                                                    <input type="date" name="fees_payable_by" class="form-control"
                                                           value="{{ $termSettings?->fees_payable_by?->format('Y-m-d') ?? '' }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                <i class="fas fa-sticky-note mr-1 text-info"></i>
                                                Additional Notes
                                                <small class="font-weight-normal text-muted">(optional)</small>
                                            </label>
                                            <textarea name="notes" class="form-control" rows="2"
                                                      placeholder="e.g. Please come with your textbooks…">{{ $termSettings?->notes ?? '' }}</textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save mr-1"></i> Save Term Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            {{-- Currently Saved Settings --}}
                            @if($termSettings)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-check-circle text-success mr-2"></i>Currently Saved
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>
                                            <i class="fas fa-door-open mr-1 text-success"></i>Resumption:
                                        </strong><br>
                                        {{ $termSettings->resumption_date?->format('l, d F Y') ?? '—' }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>
                                            <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Fees:
                                        </strong><br>
                                        {{ $termSettings->school_fees ? '₦' . number_format($termSettings->school_fees, 2) : '—' }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>
                                            <i class="fas fa-calendar-check mr-1 text-danger"></i>Payable By:
                                        </strong><br>
                                        {{ $termSettings->fees_payable_by?->format('l, d F Y') ?? '—' }}
                                    </div>
                                    @if($termSettings->notes)
                                    <div>
                                        <strong>
                                            <i class="fas fa-sticky-note mr-1 text-info"></i>Notes:
                                        </strong><br>
                                        {{ $termSettings->notes }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Blocked Count Summary --}}
                            @if($blockedIds->count())
                            <div class="card border-danger">
                                <div class="card-body py-3">
                                    <h6 class="text-danger mb-2">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        {{ $blockedIds->count() }} student(s) currently blocked
                                    </h6>
                                    <small class="text-muted">
                                        Cannot view results for
                                        <strong>{{ $selectedTerm->name }}</strong>,
                                        <strong>{{ $selectedSession->name }}</strong>.
                                    </small>
                                    <br>
                                    <a href="{{ route('results.settings.index', [
                                            'session_id' => $selectedSession->id,
                                            'term_id'    => $selectedTerm->id,
                                            'status'     => 'blocked',
                                       ]) }}"
                                       class="btn btn-sm btn-outline-danger mt-2">
                                        <i class="fas fa-list mr-1"></i> View Blocked Students
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>{{-- /term settings row --}}

                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Please select a session and term above to manage settings.
                    </div>
                    @endif

                </div>
            </section>
        </div>
    </div>
</div>

@include('includes.edit_footer')

{{-- ══════════════════════════════════════════════════════════
     Quick Block Modal
══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="quickBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('results.settings.toggleBlock') }}" id="quickBlockForm">
                @csrf
                <input type="hidden" name="action"     value="block">
                <input type="hidden" name="student_id" id="modalSid">
                <input type="hidden" name="session_id" value="{{ $selectedSession?->id }}">
                <input type="hidden" name="term_id"    value="{{ $selectedTerm?->id }}">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-ban mr-2"></i>Block Student
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>
                        Block <strong id="modalSname"></strong> from viewing their result for
                        <strong>{{ $selectedTerm?->name }}</strong>,
                        <strong>{{ $selectedSession?->name }}</strong>?
                    </p>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Reason:</label>
                        <input type="text" name="reason" id="modalReason"
                               class="form-control" value="Owing school fees">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban mr-1"></i> Confirm Block
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     JavaScript
══════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── DOM refs ──────────────────────────────────────────────────────────────
    var filterForm      = document.getElementById('filterForm');
    var bulkBlockForm   = document.getElementById('bulkBlockForm');
    var bulkUnblockForm = document.getElementById('bulkUnblockForm');
    var sectionPicker   = document.getElementById('sectionPicker');
    var classPicker     = document.getElementById('classPicker');
    var perPagePicker   = document.getElementById('perPagePicker');
    var statusPicker    = document.getElementById('statusPicker');
    var masterChk       = document.getElementById('masterChk');
    var selectionBar    = document.getElementById('selectionBar');
    var selectionSummary= document.getElementById('selectionSummary');
    var activeCountBadge= document.getElementById('activeCountBadge');

    // ── Section → Class cascade ───────────────────────────────────────────────
    var allClassData = classPicker
        ? Array.from(classPicker.options)
              .filter(function (o) { return o.value; })
              .map(function (o) {
                  return {
                      value    : o.value,
                      text     : o.textContent.trim(),
                      sectionId: o.dataset.section || ''
                  };
              })
        : [];

    if (sectionPicker) {
        sectionPicker.addEventListener('change', function () {
            var sid       = this.value;
            var prevClass = classPicker.value;
            classPicker.innerHTML = '<option value="">All Classes</option>';
            allClassData.forEach(function (item) {
                if (!sid || item.sectionId === sid) {
                    var opt             = document.createElement('option');
                    opt.value           = item.value;
                    opt.textContent     = item.text;
                    opt.dataset.section = item.sectionId;
                    if (item.value === prevClass) opt.selected = true;
                    classPicker.appendChild(opt);
                }
            });
            filterForm.submit();
        });
    }

    if (perPagePicker) perPagePicker.addEventListener('change', function () { filterForm.submit(); });
    if (statusPicker)  statusPicker.addEventListener('change',  function () { filterForm.submit(); });

    // ── Checkbox helpers ──────────────────────────────────────────────────────
    function getAllChks()      { return Array.from(document.querySelectorAll('.rowChk')); }
    function getBlockedChks() { return Array.from(document.querySelectorAll('.blockedChk')); }
    function getActiveChks()  { return Array.from(document.querySelectorAll('.activeChk')); }
    function getChecked()     { return Array.from(document.querySelectorAll('.rowChk:checked')); }
    function getCheckedBlocked() { return Array.from(document.querySelectorAll('.blockedChk:checked')); }
    function getCheckedActive()  { return Array.from(document.querySelectorAll('.activeChk:checked')); }

    // Update master checkbox state + selection summary bar
    function syncMaster() {
        var all    = getAllChks();
        var ticked = getChecked();

        if (masterChk) {
            masterChk.indeterminate = ticked.length > 0 && ticked.length < all.length;
            masterChk.checked       = all.length > 0 && ticked.length === all.length;
        }

        // Update selection summary bar
        var cBlocked = getCheckedBlocked().length;
        var cActive  = getCheckedActive().length;
        var total    = ticked.length;

        if (total === 0) {
            selectionBar.classList.add('d-none');
        } else {
            selectionBar.classList.remove('d-none');
            var parts = [];
            if (cActive  > 0) parts.push('<span class="badge badge-danger mr-1">' + cActive  + ' active</span>');
            if (cBlocked > 0) parts.push('<span class="badge badge-success mr-1">' + cBlocked + ' blocked</span>');
            selectionSummary.innerHTML =
                '<i class="fas fa-info-circle mr-1"></i>' +
                '<strong>' + total + ' student(s) selected</strong> — ' +
                parts.join(' ') +
                (cActive  > 0 ? ' &nbsp;<em class="text-dark small">→ click <strong>Block Selected</strong> to block active ones</em>' : '') +
                (cBlocked > 0 ? ' &nbsp;<em class="text-dark small">→ click <strong>Unblock Selected</strong> to restore blocked ones</em>' : '');
        }
    }

    // Set active count badge
    var totalActive = getActiveChks().length;
    if (activeCountBadge && totalActive > 0) {
        activeCountBadge.className = 'badge badge-success';
        activeCountBadge.textContent = totalActive + ' active';
    }

    // Wire master checkbox
    if (masterChk) {
        masterChk.addEventListener('change', function () {
            var on = this.checked;
            getAllChks().forEach(function (c) { c.checked = on; });
            syncMaster();
        });
    }

    // Wire all row checkboxes
    getAllChks().forEach(function (c) {
        c.addEventListener('change', syncMaster);
    });

    syncMaster(); // initial state

    // ── Select helpers ────────────────────────────────────────────────────────
    var selAllBtn     = document.getElementById('selectAllBtn');
    var deselBtn      = document.getElementById('deselectAllBtn');
    var selBlockedBtn = document.getElementById('selectBlockedBtn');
    var selActiveBtn  = document.getElementById('selectActiveBtn');

    if (selAllBtn) {
        selAllBtn.addEventListener('click', function () {
            getAllChks().forEach(function (c) { c.checked = true; });
            syncMaster();
        });
    }
    if (deselBtn) {
        deselBtn.addEventListener('click', function () {
            getAllChks().forEach(function (c) { c.checked = false; });
            syncMaster();
        });
    }
    if (selBlockedBtn) {
        selBlockedBtn.addEventListener('click', function () {
            getActiveChks().forEach(function  (c) { c.checked = false; });
            getBlockedChks().forEach(function (c) { c.checked = true; });
            syncMaster();
        });
    }
    if (selActiveBtn) {
        selActiveBtn.addEventListener('click', function () {
            getBlockedChks().forEach(function (c) { c.checked = false; });
            getActiveChks().forEach(function  (c) { c.checked = true; });
            syncMaster();
        });
    }

    // ── Helper: inject hidden student_ids[] into a form then submit ───────────
    function injectIdsAndSubmit(form, checkedEls) {
        // Remove any previously injected ids
        form.querySelectorAll('input[data-injected]').forEach(function (el) { el.remove(); });

        checkedEls.forEach(function (chk) {
            var h            = document.createElement('input');
            h.type           = 'hidden';
            h.name           = 'student_ids[]';
            h.value          = chk.value;
            h.dataset.injected = '1';
            form.appendChild(h);
        });

        form.submit();
    }

    // ── Bulk Block ────────────────────────────────────────────────────────────
    var blockBtn = document.getElementById('blockSelectedBtn');
    if (blockBtn) {
        blockBtn.addEventListener('click', function () {
            var ticked = getCheckedActive();
            if (!ticked.length) {
                alert('Please select at least one active (green) student to block.\n\nTip: Use the "Active" button to quickly select all active students.');
                return;
            }
            if (!confirm('Block ' + ticked.length + ' student(s) from viewing results?')) return;

            // Copy reason
            document.getElementById('bulkBlockReason').value =
                (document.getElementById('bulkReasonInput').value || '').trim();

            injectIdsAndSubmit(bulkBlockForm, ticked);
        });
    }

    // ── Bulk Unblock ──────────────────────────────────────────────────────────
    var unblockBtn = document.getElementById('unblockSelectedBtn');
    if (unblockBtn) {
        unblockBtn.addEventListener('click', function () {
            var ticked = getCheckedBlocked();
            if (!ticked.length) {
                alert('Please select at least one blocked (red) student to unblock.\n\nTip: Use the "Blocked" button to quickly select all blocked students.');
                return;
            }
            if (!confirm('Restore result access for ' + ticked.length + ' student(s)?')) return;

            injectIdsAndSubmit(bulkUnblockForm, ticked);
        });
    }

    // ── Quick Block Modal ─────────────────────────────────────────────────────
    document.addEventListener('click', function (evt) {
        var btn = evt.target.closest('.quickBlockBtn');
        if (!btn) return;
        document.getElementById('modalSid').value         = btn.dataset.sid;
        document.getElementById('modalSname').textContent = btn.dataset.sname;
        document.getElementById('modalReason').value      = 'Owing school fees';
        jQuery('#quickBlockModal').modal('show');
    });

});
</script>
</body>