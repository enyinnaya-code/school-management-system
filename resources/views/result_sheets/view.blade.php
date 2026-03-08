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

                    {{-- ── PAGE HEADER ── --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-eye mr-2"></i>
                                {{ $template->name }}
                                @if(!$template->is_active)
                                    <span class="badge badge-secondary ml-2" style="font-size:.65rem">Inactive</span>
                                @endif
                            </h4>
                            <div style="gap:6px" class="d-flex">
                                <a href="{{ route('result_sheets.edit', $template->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                {{-- <a href="{{ route('result_sheets.rate', $template->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-star"></i> Rate Students
                                </a> --}}
                                <a href="{{ route('result_sheets.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            @if($template->description)
                                <p class="text-muted mb-3">{{ $template->description }}</p>
                            @endif

                            {{-- Meta info row --}}
                            <div class="row">
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small text-uppercase font-weight-bold mb-1">
                                            <i class="fas fa-school mr-1"></i> Section
                                        </div>
                                        <div class="font-weight-bold">
                                            {{ $template->section_name ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small text-uppercase font-weight-bold mb-1">
                                            <i class="fas fa-calendar mr-1"></i> Term / Session
                                        </div>
                                        <div class="font-weight-bold">
                                            @if($term)
                                                {{ $term->name }}
                                                @if($session)
                                                    <br><small class="text-muted font-weight-normal">{{ $session->name }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small text-uppercase font-weight-bold mb-1">
                                            <i class="fas fa-chalkboard mr-1"></i> Applicable Classes
                                        </div>
                                        <div>
                                            @forelse($applicableClasses as $cls)
                                                <span class="badge badge-light border mr-1 mb-1">{{ $cls->name }}</span>
                                            @empty
                                                <span class="text-muted">—</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small text-uppercase font-weight-bold mb-1">
                                            <i class="fas fa-columns mr-1"></i> Rating Columns
                                        </div>
                                        <div>
                                            @foreach($template->rating_columns as $col)
                                                <span class="badge badge-secondary mr-1 mb-1">{{ $col }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer fields --}}
                            @php $ff = $template->footer_fields ?? []; @endphp
                            @if(array_filter($ff))
                            <div class="mt-1">
                                <small class="text-muted font-weight-bold">Footer fields on print:</small>
                                @php
                                    $ffLabels = [
                                        'footer_remark'        => 'Remark',
                                        'footer_class_teacher' => "Class Teacher's Signature",
                                        'footer_headmistress'  => "Headmistress' Signature",
                                        'footer_reopening'     => 'Re-Opening Date',
                                    ];
                                @endphp
                                @foreach($ffLabels as $key => $label)
                                    @if(!empty($ff[$key]))
                                        <span class="badge badge-info mr-1">{{ $label }}</span>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- ── SUBJECTS STRUCTURE ── --}}
                    <div class="card mb-4">
                        <div class="card-header bg-warning py-2">
                            <i class="fas fa-book mr-1"></i>
                            <strong>Sheet Structure</strong>
                            <span class="badge badge-dark ml-2">{{ count($subjects) }} subject(s)</span>
                        </div>
                        <div class="card-body">
                            @forelse($subjects as $subject)
                            <div class="card mb-3" style="border-left:4px solid #ffc107">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">
                                        <i class="fas fa-book-open text-warning mr-1"></i>
                                        {{ $subject->subject_number }}.&nbsp;{{ $subject->subject_name }}
                                    </span>
                                    @php
                                        $itemCount = collect($subject->subcategories)
                                            ->sum(fn($s) => count($s->items));
                                    @endphp
                                    <small class="text-muted">
                                        {{ count($subject->subcategories) }} sub-topic(s),
                                        {{ $itemCount }} item(s)
                                    </small>
                                </div>
                                <div class="card-body py-2">
                                    @forelse($subject->subcategories as $sub)
                                    <div class="mb-3">
                                        <div class="font-weight-bold text-info mb-1">
                                            @if($sub->label)
                                                <span class="badge badge-outline-info border border-info text-info mr-1">
                                                    {{ $sub->label }}
                                                </span>
                                            @endif
                                            {{ $sub->name }}
                                        </div>
                                        @if(count($sub->items))
                                        <ul class="mb-0 pl-4">
                                            @foreach($sub->items as $item)
                                                <li class="small text-dark">{{ $item->item_text }}</li>
                                            @endforeach
                                        </ul>
                                        @else
                                            <small class="text-muted ml-3">No items.</small>
                                        @endif
                                    </div>
                                    @empty
                                        <small class="text-muted">No sub-topics defined.</small>
                                    @endforelse
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                No subjects added to this template yet.
                                <a href="{{ route('result_sheets.edit', $template->id) }}" class="ml-1">Edit template</a>
                                to add subjects.
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ── STUDENTS IN APPLICABLE CLASSES ── --}}
                    {{-- <div class="card mb-4">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-user-graduate mr-1"></i>
                                <strong>Students</strong>
                                <span class="badge badge-secondary ml-1">{{ $students->count() }}</span>
                            </span>
                            @if($session && $term)
                            <small class="text-muted">
                                Showing ratings for
                                <strong>{{ $term->name }}</strong>
                                — {{ $session->name }}
                            </small>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @if($students->count())
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th>Items Rated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $i => $student)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $student->name }}</td>
                                            <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                            <td>
                                                @php
                                                    $rated = $ratingCounts[$student->id] ?? 0;
                                                @endphp
                                                @if($rated > 0)
                                                    <span class="badge badge-success">{{ $rated }} rated</span>
                                                @else
                                                    <span class="badge badge-light border text-muted">Not rated</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex" style="gap:4px">
                                                    <a href="{{ route('result_sheets.rate', $template->id) }}?student_id={{ $student->id }}{{ $session ? '&session_id='.$session->id : '' }}{{ $term ? '&term_id='.$term->id : '' }}{{ $student->class_id ? '&class_id='.$student->class_id : '' }}"
                                                       class="btn btn-xs btn-warning" title="Rate this student">
                                                        <i class="fas fa-star"></i> Rate
                                                    </a>
                                                    <a href="{{ route('result_sheets.print', [$template->id, $student->id]) }}{{ $session ? '?session_id='.$session->id : '' }}{{ $term ? ($session ? '&' : '?').'term_id='.$term->id : '' }}"
                                                       class="btn btn-xs btn-outline-primary" title="Print result sheet" target="_blank">
                                                        <i class="fas fa-print"></i> Print
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                No students found in the applicable classes.
                            </div>
                            @endif
                        </div>
                    </div> --}}

                </div>
            </section>
        </div>
    </div>
</div>
@include('includes.edit_footer')

<style>
.btn-xs { padding:2px 8px; font-size:.75rem; }
</style>
</body>