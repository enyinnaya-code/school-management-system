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
                            <h4><i class="fas fa-file-alt mr-2"></i>Custom Result Sheet Templates</h4>
                            <a href="{{ route('result_sheets.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> New Template
                            </a>
                        </div>

                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width:40px">#</th>
                                            <th>Template Name</th>
                                            <th>Section</th>
                                            <th>Term</th>
                                            <th>Classes</th>
                                            <th>Subjects</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th style="width:160px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($templates as $index => $template)
                                        <tr>
                                            <td>{{ $templates->firstItem() + $index }}</td>

                                            <td>
                                                <strong>{{ $template->name }}</strong>
                                                @if($template->description)
                                                    <br><small class="text-muted">{{ Str::limit($template->description, 60) }}</small>
                                                @endif
                                            </td>

                                            <td>{{ $template->section_name ?? '—' }}</td>

                                            {{-- term_name is now stored directly — no session join needed --}}
                                            <td>
                                                @if($template->term_name)
                                                    <span class="badge badge-primary">{{ $template->term_name }}</span>
                                                    <br><small class="text-muted">All sessions</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td>
                                                <small>{{ $template->class_names ?: '—' }}</small>
                                            </td>

                                            <td>
                                                <span class="badge badge-info">{{ $template->subject_count }} subject(s)</span>
                                            </td>

                                            <td>
                                                <form action="{{ route('result_sheets.toggle_active', $template->id) }}"
                                                      method="POST" style="display:inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $template->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                    </button>
                                                </form>
                                            </td>

                                            <td>
                                                <small>{{ \Carbon\Carbon::parse($template->created_at)->format('d M Y') }}</small>
                                                @if($template->creator_name)
                                                    <br><small class="text-muted">by {{ $template->creator_name }}</small>
                                                @endif
                                            </td>

                                            <td>
                                                <div class="d-flex flex-wrap" style="gap:4px">
                                                    <a href="{{ route('result_sheets.view', $template->id) }}"
                                                       class="btn btn-sm btn-outline-secondary" title="View Template Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('result_sheets.edit', $template->id) }}"
                                                       class="btn btn-sm btn-primary" title="Edit Template">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('result_sheets.destroy', $template->id) }}"
                                                          method="POST" style="display:inline"
                                                          onsubmit="return confirm('Delete this template?\n\nAll subjects, items and student ratings will also be permanently deleted.')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="fas fa-file-alt fa-3x mb-3 d-block"></i>
                                                No result sheet templates yet.
                                                <a href="{{ route('result_sheets.create') }}">Create one now</a>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                {{ $templates->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@include('includes.edit_footer')
</body>