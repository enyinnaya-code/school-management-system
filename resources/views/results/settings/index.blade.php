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

                    {{-- Page Header --}}
                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <h4 class="mb-0">
                                <i class="fas fa-cog text-primary mr-2"></i>
                                Result Access & Term Settings
                            </h4>
                            <small class="text-muted">
                                Block students from viewing results • Set resumption date & school fees
                            </small>
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

                    {{-- ── Session / Term Filter ─────────────────────────────── --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('results.settings.index') }}"
                                  class="form-inline flex-wrap" style="gap:10px;">
                                <div class="form-group mr-3">
                                    <label class="mr-2 font-weight-bold">Session:</label>
                                    <select name="session_id" class="form-control" id="sessionSelect">
                                        @foreach($sessions as $session)
                                            <option value="{{ $session->id }}"
                                                {{ $selectedSession?->id == $session->id ? 'selected' : '' }}>
                                                {{ $session->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label class="mr-2 font-weight-bold">Term:</label>
                                    <select name="term_id" class="form-control" id="termSelect">
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

                    <div class="row">

                        {{-- ── LEFT: Student Block/Unblock ──────────────────── --}}
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">
                                            <i class="fas fa-ban text-danger mr-2"></i>
                                            Result Access Restrictions
                                        </h5>
                                        <small class="text-muted">
                                            {{ $selectedSession->name }} — {{ $selectedTerm->name }}
                                            &nbsp;|&nbsp;
                                            <span class="badge badge-danger">
                                                {{ $blockedIds->count() }} blocked
                                            </span>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-body">

                                    {{-- Search & Class Filter --}}
                                    <form method="GET" action="{{ route('results.settings.index') }}"
                                          class="form-inline mb-3 flex-wrap" style="gap:8px;">
                                        <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                        <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">

                                        <input type="text" name="search" class="form-control form-control-sm"
                                               placeholder="Search name / admission no…"
                                               value="{{ $search }}">

                                        <select name="class_id" class="form-control form-control-sm">
                                            <option value="">All Classes</option>
                                            @foreach($classes as $cls)
                                                <option value="{{ $cls->id }}"
                                                    {{ $filterClassId == $cls->id ? 'selected' : '' }}>
                                                    {{ $cls->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </form>

                                    {{-- Bulk Block Form --}}
                                    <form method="POST" action="{{ route('results.settings.bulkBlock') }}"
                                          id="bulkBlockForm">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                        <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">

                                        <div class="d-flex align-items-center mb-3" style="gap:8px;">
                                            <input type="text" name="reason"
                                                   class="form-control form-control-sm"
                                                   placeholder="Reason (default: Owing school fees)"
                                                   style="max-width:300px;">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="submitBulkBlock()">
                                                <i class="fas fa-ban mr-1"></i> Block Selected
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="selectAll()">
                                                Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="deselectAll()">
                                                Deselect All
                                            </button>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover table-sm mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th width="40" class="text-center">
                                                            <input type="checkbox" id="selectAllChk"
                                                                   onchange="toggleAll(this)">
                                                        </th>
                                                        <th>Student Name</th>
                                                        <th>Adm. No</th>
                                                        <th>Class</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($students as $student)
                                                    @php $isBlocked = $blockedIds->contains($student->id); @endphp
                                                    <tr class="{{ $isBlocked ? 'table-danger' : '' }}">
                                                        <td class="text-center">
                                                            @if(!$isBlocked)
                                                            <input type="checkbox" name="student_ids[]"
                                                                   value="{{ $student->id }}"
                                                                   class="student-chk">
                                                            @endif
                                                        </td>
                                                        <td class="font-weight-bold">{{ $student->name }}</td>
                                                        <td>{{ $student->admission_no }}</td>
                                                        <td>{{ $student->class?->name ?? '—' }}</td>
                                                        <td class="text-center">
                                                            @if($isBlocked)
                                                                <span class="badge badge-danger">Blocked</span>
                                                            @else
                                                                <span class="badge badge-success">Active</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($isBlocked)
                                                            {{-- Unblock --}}
                                                            <form method="POST"
                                                                  action="{{ route('results.settings.toggleBlock') }}"
                                                                  style="display:inline;">
                                                                @csrf
                                                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                                <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                                                <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">
                                                                <input type="hidden" name="action"     value="unblock">
                                                                <button type="submit"
                                                                        class="btn btn-sm btn-success"
                                                                        title="Restore access"
                                                                        onclick="return confirm('Restore result access for {{ $student->name }}?')">
                                                                    <i class="fas fa-unlock"></i> Unblock
                                                                </button>
                                                            </form>
                                                            @else
                                                            {{-- Block --}}
                                                            <button type="button"
                                                                    class="btn btn-sm btn-danger"
                                                                    onclick="quickBlock({{ $student->id }}, '{{ addslashes($student->name) }}')">
                                                                <i class="fas fa-ban"></i> Block
                                                            </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">
                                                            No students found.
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                    </form>{{-- /bulkBlockForm --}}

                                    {{-- Pagination --}}
                                    <div class="mt-3">
                                        {{ $students->links() }}
                                    </div>

                                </div>
                            </div>
                        </div>{{-- /col-lg-8 --}}

                        {{-- ── RIGHT: Term Settings ──────────────────────────── --}}
                        <div class="col-lg-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                        Term Settings
                                    </h5>
                                    <small class="text-muted">
                                        Shown on report card footer
                                    </small>
                                </div>
                                <div class="card-body">
                                    <form method="POST"
                                          action="{{ route('results.settings.saveTermSettings') }}">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                                        <input type="hidden" name="term_id"    value="{{ $selectedTerm->id }}">

                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                <i class="fas fa-door-open mr-1 text-success"></i>
                                                Next Term Resumption Date
                                            </label>
                                            <input type="date" name="resumption_date"
                                                   class="form-control"
                                                   value="{{ $termSettings?->resumption_date?->format('Y-m-d') ?? '' }}">
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                <i class="fas fa-money-bill-wave mr-1 text-warning"></i>
                                                School Fees (₦)
                                            </label>
                                            <input type="number" name="school_fees"
                                                   class="form-control"
                                                   step="0.01" min="0"
                                                   placeholder="e.g. 50000"
                                                   value="{{ $termSettings?->school_fees ?? '' }}">
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                <i class="fas fa-calendar-check mr-1 text-danger"></i>
                                                Fees Payable By (Date)
                                            </label>
                                            <input type="date" name="fees_payable_by"
                                                   class="form-control"
                                                   value="{{ $termSettings?->fees_payable_by?->format('Y-m-d') ?? '' }}">
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                <i class="fas fa-sticky-note mr-1 text-info"></i>
                                                Additional Notes
                                                <small class="font-weight-normal text-muted">(optional)</small>
                                            </label>
                                            <textarea name="notes" class="form-control" rows="3"
                                                      placeholder="e.g. Please come with your textbooks…">{{ $termSettings?->notes ?? '' }}</textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save mr-1"></i> Save Term Settings
                                        </button>
                                    </form>

                                    {{-- Current saved values preview --}}
                                    @if($termSettings)
                                    <div class="mt-4 p-3 bg-light rounded border">
                                        <small class="font-weight-bold text-muted d-block mb-2">
                                            CURRENTLY SAVED
                                        </small>
                                        <div class="small">
                                            <div class="mb-1">
                                                <strong>Resumption:</strong>
                                                {{ $termSettings->resumption_date?->format('d M Y') ?? '—' }}
                                            </div>
                                            <div class="mb-1">
                                                <strong>Fees:</strong>
                                                {{ $termSettings->school_fees ? '₦' . number_format($termSettings->school_fees, 2) : '—' }}
                                            </div>
                                            <div class="mb-1">
                                                <strong>Payable By:</strong>
                                                {{ $termSettings->fees_payable_by?->format('d M Y') ?? '—' }}
                                            </div>
                                            @if($termSettings->notes)
                                            <div>
                                                <strong>Notes:</strong> {{ $termSettings->notes }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                </div>
                            </div>

                            {{-- Blocked students summary --}}
                            @if($blockedIds->count())
                            <div class="card border-danger">
                                <div class="card-body py-3">
                                    <h6 class="text-danger mb-2">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        {{ $blockedIds->count() }} student(s) currently blocked
                                    </h6>
                                    <small class="text-muted">
                                        These students cannot view their results for
                                        <strong>{{ $selectedTerm->name }}</strong>,
                                        <strong>{{ $selectedSession->name }}</strong>.
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

{{-- Quick block modal --}}
<div class="modal fade" id="quickBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('results.settings.toggleBlock') }}" id="quickBlockForm">
                @csrf
                <input type="hidden" name="action"     value="block">
                <input type="hidden" name="student_id" id="qbStudentId">
                <input type="hidden" name="session_id" value="{{ $selectedSession?->id }}">
                <input type="hidden" name="term_id"    value="{{ $selectedTerm?->id }}">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-ban mr-2"></i>Block Student
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>You are about to block <strong id="qbStudentName"></strong> from viewing their result.</p>
                    <div class="form-group">
                        <label><strong>Reason:</strong></label>
                        <input type="text" name="reason" class="form-control"
                               value="Owing school fees"
                               placeholder="Reason for blocking">
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

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
function quickBlock(studentId, studentName) {
    document.getElementById('qbStudentId').value   = studentId;
    document.getElementById('qbStudentName').textContent = studentName;
    $('#quickBlockModal').modal('show');
}

function toggleAll(chk) {
    document.querySelectorAll('.student-chk').forEach(c => c.checked = chk.checked);
}

function selectAll() {
    document.querySelectorAll('.student-chk').forEach(c => c.checked = true);
    document.getElementById('selectAllChk').checked = true;
}

function deselectAll() {
    document.querySelectorAll('.student-chk').forEach(c => c.checked = false);
    document.getElementById('selectAllChk').checked = false;
}

function submitBulkBlock() {
    const checked = document.querySelectorAll('.student-chk:checked');
    if (checked.length === 0) {
        alert('Please select at least one student to block.');
        return;
    }
    if (!confirm('Block ' + checked.length + ' selected student(s) from viewing results?')) return;
    document.getElementById('bulkBlockForm').submit();
}
</script>
</body>