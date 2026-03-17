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

                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <h4 class="mb-0">
                                <i class="fas fa-cog text-primary mr-2"></i> Result Access & Term Settings
                            </h4>
                            <small class="text-muted">Block students from viewing results &bull; Set resumption date & school fees</small>
                        </div>
                    </div>

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

                    {{-- Session / Term picker --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('results.settings.index') }}"
                                  id="sessionTermForm" class="form-inline flex-wrap" style="gap:10px;">
                                <div class="form-group mr-3">
                                    <label class="mr-2 font-weight-bold">Session:</label>
                                    <select name="session_id" class="form-control"
                                            onchange="document.getElementById('sessionTermForm').submit()">
                                        @foreach($sessions as $sess)
                                            <option value="{{ $sess->id }}" {{ $selectedSession?->id == $sess->id ? 'selected' : '' }}>
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
                                            <option value="{{ $term->id }}" {{ $selectedTerm?->id == $term->id ? 'selected' : '' }}>
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
                    <div class="row">

                        {{-- LEFT: Restrictions --}}
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0"><i class="fas fa-ban text-danger mr-2"></i>Result Access Restrictions</h5>
                                        <small class="text-muted">
                                            {{ $selectedSession->name }} — {{ $selectedTerm->name }}
                                            &nbsp;|&nbsp;
                                            <span class="badge badge-danger">{{ $blockedIds->count() }} blocked</span>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-body">

                                    {{-- Filters --}}
                                    <form method="GET" action="{{ route('results.settings.index') }}" id="filterForm">
                                        <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                        <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">
                                        <div class="row mb-3 align-items-end">
                                            <div class="col-sm-3 mb-2">
                                                <label class="small font-weight-bold mb-1">Search</label>
                                                <input type="text" name="search" class="form-control form-control-sm"
                                                       placeholder="Name or Adm. No…" value="{{ $search }}">
                                            </div>
                                            <div class="col-sm-3 mb-2">
                                                <label class="small font-weight-bold mb-1">Section</label>
                                                <select name="section_id" class="form-control form-control-sm" id="sectionPicker">
                                                    <option value="">All Sections</option>
                                                    @foreach($sections as $sec)
                                                        <option value="{{ $sec->id }}" {{ $filterSectionId == $sec->id ? 'selected' : '' }}>
                                                            {{ $sec->section_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-3 mb-2">
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
                                            <div class="col-sm-1 mb-2">
                                                <label class="small font-weight-bold mb-1">Show</label>
                                                <select name="per_page" class="form-control form-control-sm" id="perPagePicker">
                                                    @foreach([10,20,50,100] as $pp)
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

                                    {{-- Bulk Block --}}
                                    <form method="POST" action="{{ route('results.settings.bulkBlock') }}" id="bulkForm">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                        <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">

                                        <div class="d-flex align-items-center mb-3 flex-wrap" style="gap:8px;">
                                            <input type="text" name="reason" id="bulkReasonInput"
                                                   class="form-control form-control-sm"
                                                   placeholder="Reason (default: Owing school fees)"
                                                   style="max-width:280px;">
                                            <button type="button" class="btn btn-sm btn-danger" id="blockSelectedBtn">
                                                <i class="fas fa-ban mr-1"></i> Block Selected
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn">Select All</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">Deselect All</button>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover table-sm mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th width="40" class="text-center">
                                                            <input type="checkbox" id="masterChk">
                                                        </th>
                                                        <th>Student Name</th>
                                                        <th>Adm. No</th>
                                                        <th>Section</th>
                                                        <th>Class</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Action</th>
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
                                                            @if(!$isBlocked)
                                                            <input type="checkbox" name="student_ids[]"
                                                                   value="{{ $student->id }}" class="rowChk">
                                                            @endif
                                                        </td>
                                                        <td class="font-weight-bold">{{ $student->name }}</td>
                                                        <td>{{ $student->admission_no }}</td>
                                                        <td>{{ $student->class?->section?->section_name ?? '—' }}</td>
                                                        <td>{{ $student->class?->name ?? '—' }}</td>
                                                        <td class="text-center">
                                                            @if($isBlocked)
                                                                <span class="badge badge-danger">Blocked</span>
                                                                @if($blockReason)
                                                                <br><small class="text-muted" style="font-size:10px;">{{ $blockReason }}</small>
                                                                @endif
                                                            @else
                                                                <span class="badge badge-success">Active</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($isBlocked)
                                                            <form method="POST" action="{{ route('results.settings.toggleBlock') }}" style="display:inline;">
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
                                                            <button type="button" class="btn btn-sm btn-danger quickBlockBtn"
                                                                    data-sid="{{ $student->id }}"
                                                                    data-sname="{{ $student->name }}">
                                                                <i class="fas fa-ban"></i> Block
                                                            </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-4">No students found matching the filters.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>

                                    {{-- Smart Pagination --}}
                                    @if($students->hasPages())
                                    @php
                                        $curPage  = $students->currentPage();
                                        $lastPage = $students->lastPage();
                                        $fromPg   = max(1, $curPage - 2);
                                        $toPg     = min($lastPage, $curPage + 2);
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap" style="gap:8px;">
                                        <small class="text-muted">
                                            Showing <strong>{{ $students->firstItem() }}</strong>–<strong>{{ $students->lastItem() }}</strong>
                                            of <strong>{{ $students->total() }}</strong>
                                            @if($search || $filterSectionId || $filterClassId)
                                                <span class="badge badge-info ml-1">filtered</span>
                                            @endif
                                        </small>
                                        <div class="d-flex flex-wrap" style="gap:3px;">
                                            @if($students->onFirstPage())
                                                <span class="btn btn-sm btn-outline-secondary disabled"><i class="fas fa-chevron-left"></i></span>
                                            @else
                                                <a href="{{ $students->previousPageUrl() }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-chevron-left"></i></a>
                                            @endif

                                            @if($fromPg > 1)
                                                <a href="{{ $students->url(1) }}" class="btn btn-sm btn-outline-primary">1</a>
                                                @if($fromPg > 2)<span class="btn btn-sm disabled">…</span>@endif
                                            @endif

                                            @for($pg = $fromPg; $pg <= $toPg; $pg++)
                                                @if($pg == $curPage)
                                                    <span class="btn btn-sm btn-primary">{{ $pg }}</span>
                                                @else
                                                    <a href="{{ $students->url($pg) }}" class="btn btn-sm btn-outline-primary">{{ $pg }}</a>
                                                @endif
                                            @endfor

                                            @if($toPg < $lastPage)
                                                @if($toPg < $lastPage - 1)<span class="btn btn-sm disabled">…</span>@endif
                                                <a href="{{ $students->url($lastPage) }}" class="btn btn-sm btn-outline-primary">{{ $lastPage }}</a>
                                            @endif

                                            @if($students->hasMorePages())
                                                <a href="{{ $students->nextPageUrl() }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-chevron-right"></i></a>
                                            @else
                                                <span class="btn btn-sm btn-outline-secondary disabled"><i class="fas fa-chevron-right"></i></span>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                </div>
                            </div>
                        </div>{{-- /col-lg-8 --}}

                        {{-- RIGHT: Term Settings --}}
                        <div class="col-lg-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-calendar-alt text-primary mr-2"></i>Term Settings</h5>
                                    <small class="text-muted">Shown on report card footer</small>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('results.settings.saveTermSettings') }}">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                        <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">

                                        <div class="form-group">
                                            <label class="font-weight-bold"><i class="fas fa-door-open mr-1 text-success"></i>Next Term Resumption Date</label>
                                            <input type="date" name="resumption_date" class="form-control"
                                                   value="{{ $termSettings?->resumption_date?->format('Y-m-d') ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold"><i class="fas fa-money-bill-wave mr-1 text-warning"></i>School Fees (₦)</label>
                                            <input type="number" name="school_fees" class="form-control"
                                                   step="0.01" min="0" placeholder="e.g. 50000"
                                                   value="{{ $termSettings?->school_fees ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold"><i class="fas fa-calendar-check mr-1 text-danger"></i>Fees Payable By</label>
                                            <input type="date" name="fees_payable_by" class="form-control"
                                                   value="{{ $termSettings?->fees_payable_by?->format('Y-m-d') ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold"><i class="fas fa-sticky-note mr-1 text-info"></i>Additional Notes <small class="font-weight-normal text-muted">(optional)</small></label>
                                            <textarea name="notes" class="form-control" rows="3"
                                                      placeholder="e.g. Please come with your textbooks…">{{ $termSettings?->notes ?? '' }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save mr-1"></i> Save Term Settings
                                        </button>
                                    </form>

                                    @if($termSettings)
                                    <div class="mt-4 p-3 bg-light rounded border">
                                        <small class="font-weight-bold text-muted d-block mb-2">CURRENTLY SAVED</small>
                                        <div class="small">
                                            <div class="mb-1"><strong>Resumption:</strong> {{ $termSettings->resumption_date?->format('d M Y') ?? '—' }}</div>
                                            <div class="mb-1"><strong>Fees:</strong> {{ $termSettings->school_fees ? '₦' . number_format($termSettings->school_fees, 2) : '—' }}</div>
                                            <div class="mb-1"><strong>Payable By:</strong> {{ $termSettings->fees_payable_by?->format('d M Y') ?? '—' }}</div>
                                            @if($termSettings->notes)<div><strong>Notes:</strong> {{ $termSettings->notes }}</div>@endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if($blockedIds->count())
                            <div class="card border-danger">
                                <div class="card-body py-3">
                                    <h6 class="text-danger mb-2">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        {{ $blockedIds->count() }} student(s) currently blocked
                                    </h6>
                                    <small class="text-muted">
                                        These students cannot view their results for
                                        <strong>{{ $selectedTerm->name }}</strong>, <strong>{{ $selectedSession->name }}</strong>.
                                    </small>
                                </div>
                            </div>
                            @endif
                        </div>{{-- /col-lg-4 --}}

                    </div>{{-- /row --}}
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

{{-- Quick Block Modal --}}
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
                    <h5 class="modal-title text-danger"><i class="fas fa-ban mr-2"></i>Block Student</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Block <strong id="modalSname"></strong> from viewing their result?</p>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Reason:</label>
                        <input type="text" name="reason" id="modalReason" class="form-control" value="Owing school fees">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-ban mr-1"></i> Confirm Block</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- <script src="{{ asset('js/jquery.min.js') }}"></script> --}}
<script>
(function () {
    'use strict';

    var filterForm    = document.getElementById('filterForm');
    var bulkForm      = document.getElementById('bulkForm');
    var sectionPicker = document.getElementById('sectionPicker');
    var classPicker   = document.getElementById('classPicker');
    var perPagePicker = document.getElementById('perPagePicker');
    var masterChk     = document.getElementById('masterChk');

    // Store all class options at page load
    var allClassData = classPicker
        ? Array.from(classPicker.options).filter(function (o) { return o.value; }).map(function (o) {
            return { value: o.value, text: o.textContent.trim(), sectionId: o.dataset.section || '' };
          })
        : [];

    // Section → cascade class dropdown then submit
    if (sectionPicker) {
        sectionPicker.addEventListener('change', function () {
            var sid = this.value;
            var prevClass = classPicker.value;

            classPicker.innerHTML = '<option value="">All Classes</option>';
            allClassData.forEach(function (item) {
                if (!sid || item.sectionId === sid) {
                    var opt = document.createElement('option');
                    opt.value          = item.value;
                    opt.textContent    = item.text;
                    opt.dataset.section = item.sectionId;
                    if (item.value === prevClass && (!sid || item.sectionId === sid)) opt.selected = true;
                    classPicker.appendChild(opt);
                }
            });

            filterForm.submit();
        });
    }

    // Per-page → auto-submit
    if (perPagePicker) {
        perPagePicker.addEventListener('change', function () { filterForm.submit(); });
    }

    // Master checkbox helpers
    function getRowChks() { return document.querySelectorAll('.rowChk'); }

    function syncMaster() {
        if (!masterChk) return;
        var all    = getRowChks();
        var ticked = document.querySelectorAll('.rowChk:checked');
        masterChk.indeterminate = ticked.length > 0 && ticked.length < all.length;
        masterChk.checked       = all.length > 0 && ticked.length === all.length;
    }

    if (masterChk) {
        masterChk.addEventListener('change', function () {
            var on = this.checked;
            getRowChks().forEach(function (c) { c.checked = on; });
        });
        getRowChks().forEach(function (c) { c.addEventListener('change', syncMaster); });
        syncMaster();
    }

    // Select All / Deselect All
    var selAllBtn = document.getElementById('selectAllBtn');
    var deselBtn  = document.getElementById('deselectAllBtn');

    if (selAllBtn) selAllBtn.addEventListener('click', function () {
        getRowChks().forEach(function (c) { c.checked = true; });
        syncMaster();
    });
    if (deselBtn) deselBtn.addEventListener('click', function () {
        getRowChks().forEach(function (c) { c.checked = false; });
        syncMaster();
    });

    // Block Selected
    var blockBtn = document.getElementById('blockSelectedBtn');
    if (blockBtn) {
        blockBtn.addEventListener('click', function () {
            var ticked = document.querySelectorAll('.rowChk:checked');
            if (!ticked.length) { alert('Select at least one student.'); return; }
            if (!confirm('Block ' + ticked.length + ' student(s) from viewing results?')) return;
            bulkForm.submit();
        });
    }

    // Quick Block (single) — event delegation, no class dependency
    document.addEventListener('click', function (evt) {
        var btn = evt.target.closest('.quickBlockBtn');
        if (!btn) return;
        document.getElementById('modalSid').value       = btn.dataset.sid;
        document.getElementById('modalSname').textContent = btn.dataset.sname;
        document.getElementById('modalReason').value    = 'Owing school fees';
        $('#quickBlockModal').modal('show');
    });

}());
</script>
</body>