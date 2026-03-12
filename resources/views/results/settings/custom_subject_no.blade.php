@include('includes.head')

<style>
    .class-row { transition: background 0.2s; }
    .class-row.enabled { background-color: #f0fff4; }
    .class-row.disabled-row { opacity: 0.5; }
    /* .section-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white; padding: 10px 15px; border-radius: 5px;
        margin-bottom: 0; font-weight: 600; cursor: pointer;
        display: flex; justify-content: space-between; align-items: center;
    } */
    /* .section-header:hover { background: linear-gradient(135deg, #0069d9, #004494); } */
    .section-accordion { margin-bottom: 16px; border-radius: 5px; overflow: hidden; border: 1px solid #dee2e6; }
    .section-body { display: none; }
    .section-body.show { display: block; }
    .accordion-arrow { transition: transform 0.3s; }
    .section-header.open .accordion-arrow { transform: rotate(180deg); }
</style>

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
                            <h4>Set Custom Subject Number Per Class</h4>
                            <small class="text-muted">
                                Enable classes that allow students to offer a custom number of
                                subjects (e.g. SS2, SS3 where students can offer 8, 9, or 10 subjects).
                                Set the minimum and maximum allowed subjects for each class.
                            </small>
                        </div>
                        <div class="card-body">

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            @if($existingLimits->count() > 0)
                            <div class="alert alert-info mb-4">
                                <strong><i class="fas fa-info-circle"></i>
                                    {{ $existingLimits->count() }} class(es)
                                </strong> currently have custom subject limits configured.
                            </div>
                            @endif

                            <form method="POST"
                                  action="{{ route('results.settings.saveCustomSubjectNo') }}"
                                  id="customSubjectForm">
                                @csrf

                                @foreach($sections as $section)
                                @php
                                    $sectionHasActive = $section->classes->contains(fn($c) => $existingLimits->has($c->id));
                                @endphp
                                <div class="section-accordion">
                                    <div class="section-header {{ $sectionHasActive ? 'open' : '' }}"
                                         data-target="section_body_{{ $section->id }}">
                                        <span>
                                            <i class="fas fa-layer-group mr-2"></i>
                                            {{ $section->section_name }}
                                            <span class="badge badge-light ml-2">
                                                {{ $section->classes->count() }} classes
                                            </span>
                                            @if($sectionHasActive)
                                                <span class="badge badge-warning ml-1">
                                                    {{ $section->classes->filter(fn($c) => $existingLimits->has($c->id))->count() }} configured
                                                </span>
                                            @endif
                                        </span>
                                        <i class="fas fa-chevron-down accordion-arrow"></i>
                                    </div>

                                    <div class="section-body {{ $sectionHasActive ? 'show' : '' }}"
                                         id="section_body_{{ $section->id }}">

                                        @if($section->classes->isEmpty())
                                            <p class="text-muted p-3 mb-0">No classes in this section.</p>
                                        @else
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width:50px;">Enable</th>
                                                        <th>Class Name</th>
                                                        <th style="width:180px;">
                                                            Min Subjects
                                                            <small class="text-muted d-block">
                                                                (least a student can offer)
                                                            </small>
                                                        </th>
                                                        <th style="width:180px;">
                                                            Max Subjects
                                                            <small class="text-muted d-block">
                                                                (most a student can offer)
                                                            </small>
                                                        </th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($section->classes as $class)
                                                    @php
                                                        $limit    = $existingLimits->get($class->id);
                                                        $isActive = $limit !== null;
                                                        $minVal   = $limit?->min_subjects ?? 8;
                                                        $maxVal   = $limit?->max_subjects ?? 10;
                                                    @endphp
                                                    <tr class="class-row {{ $isActive ? 'enabled' : '' }}"
                                                        id="row_{{ $class->id }}">
                                                        <td class="text-center">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox"
                                                                       class="custom-control-input class-toggle"
                                                                       id="toggle_{{ $class->id }}"
                                                                       data-class-id="{{ $class->id }}"
                                                                       {{ $isActive ? 'checked' : '' }}>
                                                                <label class="custom-control-label"
                                                                       for="toggle_{{ $class->id }}">
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <strong>{{ $class->name }}</strong>
                                                            <input type="hidden"
                                                                   name="limits[{{ $class->id }}][class_id]"
                                                                   value="{{ $class->id }}"
                                                                   class="limit-class-id"
                                                                   {{ $isActive ? '' : 'disabled' }}>
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                   name="limits[{{ $class->id }}][min]"
                                                                   class="form-control limit-min"
                                                                   value="{{ $minVal }}"
                                                                   min="1" max="20"
                                                                   {{ $isActive ? '' : 'disabled' }}>
                                                            @error("limits.{$class->id}.min")
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                   name="limits[{{ $class->id }}][max]"
                                                                   class="form-control limit-max"
                                                                   value="{{ $maxVal }}"
                                                                   min="1" max="20"
                                                                   {{ $isActive ? '' : 'disabled' }}>
                                                            @error("limits.{$class->id}.max")
                                                                <small class="text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            @if($isActive)
                                                                <span class="badge badge-success status-badge">
                                                                    <i class="fas fa-check"></i>
                                                                    Custom: {{ $minVal }}–{{ $maxVal }} subjects
                                                                </span>
                                                            @else
                                                                <span class="badge badge-secondary status-badge">
                                                                    Standard (no limit)
                                                                </span>
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
                                @endforeach

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                    <a href="{{ route('results.settings.customSubjectNo') }}"
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
    // Accordion toggle
    $(document).on('click', '.section-header', function () {
        const targetId = $(this).data('target');
        const body     = $('#' + targetId);

        body.toggleClass('show');
        $(this).toggleClass('open');
    });

    // Toggle enable/disable of inputs when the switch is toggled
    $(document).on('change', '.class-toggle', function () {
        const classId  = $(this).data('class-id');
        const isActive = $(this).is(':checked');
        const row      = $('#row_' + classId);

        if (isActive) {
            row.addClass('enabled').removeClass('disabled-row');
            row.find('.limit-class-id, .limit-min, .limit-max').prop('disabled', false);
            row.find('.status-badge').removeClass('badge-secondary').addClass('badge-success');

            const min = row.find('.limit-min').val();
            const max = row.find('.limit-max').val();
            row.find('.status-badge').html(
                `<i class="fas fa-check"></i> Custom: ${min}–${max} subjects`
            );
        } else {
            row.removeClass('enabled').addClass('disabled-row');
            row.find('.limit-class-id, .limit-min, .limit-max').prop('disabled', true);
            row.find('.status-badge').removeClass('badge-success').addClass('badge-secondary');
            row.find('.status-badge').html('Standard (no limit)');
        }
    });

    // Live update the status badge when min/max changes
    $(document).on('input', '.limit-min, .limit-max', function () {
        const row = $(this).closest('tr');
        const min = row.find('.limit-min').val();
        const max = row.find('.limit-max').val();

        if (row.find('.class-toggle').is(':checked')) {
            row.find('.status-badge').html(
                `<i class="fas fa-check"></i> Custom: ${min}–${max} subjects`
            );
        }
    });

    // Validate: max must be >= min
    $('#customSubjectForm').on('submit', function (e) {
        let valid = true;

        $('.class-toggle:checked').each(function () {
            const classId = $(this).data('class-id');
            const row     = $('#row_' + classId);
            const min     = parseInt(row.find('.limit-min').val());
            const max     = parseInt(row.find('.limit-max').val());

            if (max < min) {
                alert(`Max subjects (${max}) cannot be less than Min subjects (${min}) — please check your settings.`);
                valid = false;
                return false;
            }
        });

        if (!valid) e.preventDefault();
    });
</script>
</body>