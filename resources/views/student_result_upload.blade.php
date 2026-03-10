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
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-clipboard-check mr-2"></i>
                                    Skill Sheet — {{ $student->name }}
                                </h4>
                                <p class="mb-0 text-muted">
                                    {{ $class->name }} &bull; {{ $section->section_name ?? '' }} &bull;
                                    <strong>{{ $currentSession->name }}</strong> &mdash;
                                    <strong>{{ $currentTerm->name }}</strong>
                                </p>
                                <p class="mb-0 text-muted small">Template: <em>{{ $template->name }}</em></p>
                            </div>
                            <a href="javascript:history.back()" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>

                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST"
                                  action="{{ route('student.result_sheet.save', ['templateId' => $template->id, 'studentId' => $student->id]) }}">
                                @csrf

                                {{-- Pass session/term as hidden fields --}}
                                <input type="hidden" name="session_id" value="{{ $currentSession->id }}">
                                <input type="hidden" name="term_id"    value="{{ $currentTerm->id }}">

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle" id="sheetTable">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th style="min-width:280px">Skill / Competency</th>
                                                @foreach($template->rating_columns as $col)
                                                    <th class="text-center" style="min-width:80px">{{ $col }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjects as $subject)
                                                {{-- Subject heading row --}}
                                                <tr class="table-warning">
                                                    <td colspan="{{ count($template->rating_columns) + 1 }}"
                                                        class="font-weight-bold py-1">
                                                        <i class="fas fa-book-open mr-1"></i>
                                                        ({{ $subject->subject_number }}) {{ $subject->subject_name }}
                                                    </td>
                                                </tr>

                                                @foreach($subject->subcategories as $sub)
                                                    {{-- Subcategory heading row --}}
                                                    <tr class="table-light">
                                                        <td colspan="{{ count($template->rating_columns) + 1 }}"
                                                            class="pl-3 font-italic py-1 text-info">
                                                            {{ $sub->label }} {{ $sub->name }}
                                                        </td>
                                                    </tr>

                                                    @foreach($sub->items as $item)
                                                        <tr class="item-row">
                                                            <td class="pl-4" style="font-size:.88rem">
                                                                &mdash; {{ $item->item_text }}
                                                            </td>
                                                            @foreach($template->rating_columns as $colIdx => $col)
                                                                <td class="text-center">
                                                                    <input
                                                                        type="checkbox"
                                                                        class="rating-checkbox"
                                                                        name="ratings[{{ $item->id }}]"
                                                                        value="{{ $col }}"
                                                                        data-item="{{ $item->id }}"
                                                                        {{ ($existingRatings[$item->id] ?? null) === $col ? 'checked' : '' }}>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                @endforeach

                                                {{-- Direct items (no subcategory) --}}
                                                @foreach($subject->items as $item)
                                                    <tr class="item-row">
                                                        <td class="pl-3" style="font-size:.88rem">
                                                            &mdash; {{ $item->item_text }}
                                                        </td>
                                                        @foreach($template->rating_columns as $col)
                                                            <td class="text-center">
                                                                <input
                                                                    type="checkbox"
                                                                    class="rating-checkbox"
                                                                    name="ratings[{{ $item->id }}]"
                                                                    value="{{ $col }}"
                                                                    data-item="{{ $item->id }}"
                                                                    {{ ($existingRatings[$item->id] ?? null) === $col ? 'checked' : '' }}>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Footer fields --}}
                                @php $ff = $template->footer_fields ?? []; @endphp
                                <div class="row mt-4">
                                    @if($ff['footer_remark'] ?? false)
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Remark</label>
                                            <input type="text" name="footer_remark"
                                                   class="form-control"
                                                   value="{{ old('footer_remark') }}">
                                        </div>
                                    @endif
                                    @if($ff['footer_class_teacher'] ?? false)
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Class Teacher's Signature</label>
                                            <input type="text" name="footer_class_teacher"
                                                   class="form-control"
                                                   value="{{ old('footer_class_teacher') }}">
                                        </div>
                                    @endif
                                    @if($ff['footer_headmistress'] ?? false)
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Headmistress' Signature</label>
                                            <input type="text" name="footer_headmistress"
                                                   class="form-control"
                                                   value="{{ old('footer_headmistress') }}">
                                        </div>
                                    @endif
                                    @if($ff['footer_reopening'] ?? false)
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-bold">Re-Opening Date</label>
                                            <input type="text" name="footer_reopening"
                                                   class="form-control"
                                                   value="{{ old('footer_reopening') }}">
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Save Ratings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

@include('includes.edit_footer')

<style>
#sheetTable td, #sheetTable th { vertical-align: middle; }
.item-row:hover { background: #f0f7ff; }
.rating-checkbox { width: 18px; height: 18px; cursor: pointer; }
</style>

<script>
// Enforce: only ONE checkbox checked per item row
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.rating-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            if (this.checked) {
                // Uncheck all other checkboxes for the same item
                const itemId = this.dataset.item;
                document.querySelectorAll(`.rating-checkbox[data-item="${itemId}"]`).forEach(function (other) {
                    if (other !== cb) other.checked = false;
                });
            }
        });
    });
});
</script>
</body>