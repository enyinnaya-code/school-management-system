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
                        <div class="card-header">
                            <h4>Set Primary Result Class</h4>
                            <small class="text-muted">
                                Select the primary section and then choose the classes
                                that will use the primary result template.
                            </small>
                        </div>
                        <div class="card-body">

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            {{-- Current Setting Summary --}}
                            @if($primarySection)
                            <div class="alert alert-info mb-4">
                                <strong><i class="fas fa-info-circle"></i> Current Setting:</strong>
                                Primary Section is set to
                                <strong>{{ $primarySection->section->section_name }}</strong>
                                with
                                <strong>{{ count($primaryClassIds) }}</strong>
                                class(es) selected.
                            </div>
                            @endif

                            <form method="POST"
                                  action="{{ route('results.settings.savePrimaryResultClass') }}"
                                  id="primaryResultForm">
                                @csrf

                                {{-- Step 1: Select Section --}}
                                <div class="card mb-4 border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <strong>Step 1:</strong> Select Primary Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Section <span class="text-danger">*</span></label>
                                            <select name="section_id"
                                                    id="section_id"
                                                    class="form-control @error('section_id') is-invalid @enderror"
                                                    style="max-width: 400px;">
                                                <option value="">-- Select Section --</option>
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}"
                                                        {{ $primarySection && $primarySection->section_id == $section->id ? 'selected' : '' }}>
                                                        {{ $section->section_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('section_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Step 2: Select Classes --}}
                                <div class="card mb-4 border" id="classesCard"
                                     style="{{ $primarySection ? '' : 'display:none;' }}">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <strong>Step 2:</strong> Select Classes
                                        </h6>
                                        <div>
                                            <button type="button"
                                                    id="selectAllBtn"
                                                    class="btn btn-sm btn-outline-primary mr-2">
                                                <i class="fas fa-check-double"></i> Select All
                                            </button>
                                            <button type="button"
                                                    id="deselectAllBtn"
                                                    class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-times"></i> Deselect All
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-3">
                                            <i class="fas fa-info-circle"></i>
                                            Check the classes that should use the primary result template.
                                        </p>

                                        @error('class_ids')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror

                                        <div id="classesContainer" class="row">
                                            @foreach($classes as $class)
                                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                           class="custom-control-input class-checkbox"
                                                           id="class_{{ $class->id }}"
                                                           name="class_ids[]"
                                                           value="{{ $class->id }}"
                                                           {{ in_array($class->id, $primaryClassIds) ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                           for="class_{{ $class->id }}">
                                                        {{ $class->name }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>

                                        <div id="noClassesMsg" class="text-muted" style="display:none;">
                                            No classes found for this section.
                                        </div>
                                    </div>
                                </div>

                                <div id="submitSection"
                                     style="{{ $primarySection ? '' : 'display:none;' }}">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                    <a href="{{ route('results.settings.primaryResultClass') }}"
                                       class="btn btn-secondary ml-2">
                                        <i class="fas fa-sync"></i> Reset
                                    </a>
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

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
    const getClassesUrl = "{{ route('results.settings.getClassesBySection', ':id') }}";
    const savedClassIds = @json($primaryClassIds);

    $('#section_id').on('change', function () {
        const sectionId = $(this).val();

        if (!sectionId) {
            $('#classesCard').hide();
            $('#submitSection').hide();
            return;
        }

        const url = getClassesUrl.replace(':id', sectionId);

        $.get(url, function (classes) {
            let html = '';

            if (classes.length === 0) {
                $('#classesContainer').html('');
                $('#noClassesMsg').show();
            } else {
                $('#noClassesMsg').hide();
                classes.forEach(function (cls) {
                    const checked = savedClassIds.includes(cls.id) ? 'checked' : '';
                    html += `
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input class-checkbox"
                                       id="class_${cls.id}"
                                       name="class_ids[]"
                                       value="${cls.id}"
                                       ${checked}>
                                <label class="custom-control-label" for="class_${cls.id}">
                                    ${cls.name}
                                </label>
                            </div>
                        </div>`;
                });
                $('#classesContainer').html(html);
            }

            $('#classesCard').show();
            $('#submitSection').show();
        });
    });

    // Select All
    $('#selectAllBtn').on('click', function () {
        $('.class-checkbox').prop('checked', true);
    });

    // Deselect All
    $('#deselectAllBtn').on('click', function () {
        $('.class-checkbox').prop('checked', false);
    });

    // Validate at least one class checked before submit
    $('#primaryResultForm').on('submit', function (e) {
        const checked = $('.class-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('Please select at least one class.');
        }
    });
</script>
</body>