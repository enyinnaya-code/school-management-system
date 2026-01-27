@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')
            <!-- Main Content -->
            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Edit Master Timetable</h4>
                            </div>
                            <div class="card-body">
                                @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif
                                @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                @if (session('warning'))
                                <div class="alert alert-warning">
                                    {{ session('warning') }}
                                    @if (session('conflicts'))
                                    <ul>
                                        @foreach (session('conflicts') as $conflict)
                                        <li>{{ $conflict }}</li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>
                                @endif
                                <form action="{{ route('timetables.update', $timetable->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-6 px-0">
                                            <div class="form-group">
                                                <label>Section</label>
                                                <select class="form-control" id="section_id" name="section_id" required>
                                                    <option value="">Select Section</option>
                                                    @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{ $timetable->section_id ==
                                                        $section->id ? 'selected' : '' }}>{{ $section->section_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('section_id')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Session</label>
                                                <select class="form-control" id="session_id" name="session_id" required>
                                                    <option value="">Select Session</option>
                                                    @foreach($sessions as $session)
                                                    <option value="{{ $session->id }}" {{ $timetable->session_id ==
                                                        $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('session_id')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Term</label>
                                                <select class="form-control" id="term_id" name="term_id" required>
                                                    <option value="">Select Term</option>
                                                    @foreach($terms as $term)
                                                    <option value="{{ $term->id }}" {{ $timetable->term_id == $term->id
                                                        ? 'selected' : '' }}>{{ $term->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('term_id')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Number of Periods per Day (Default)</label>
                                                <input type="number" class="form-control" name="num_periods"
                                                    id="num_periods" min="1" max="12"
                                                    value="{{ $timetable->num_periods }}" required>
                                                @error('num_periods')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Custom Periods per Day -->
                                            <div class="card mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">Custom Periods for Specific Days</h6>
                                                    <small class="text-muted">Leave blank to use default periods</small>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-2">
                                                            <label><small>Monday Periods</small></label>
                                                            <input type="number"
                                                                class="form-control form-control-sm day-periods"
                                                                name="periods_monday" data-day="Monday" min="1" max="12"
                                                                placeholder="Default"
                                                                value="{{ $timetable->day_periods['Monday'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label><small>Tuesday Periods</small></label>
                                                            <input type="number"
                                                                class="form-control form-control-sm day-periods"
                                                                name="periods_tuesday" data-day="Tuesday" min="1"
                                                                max="12" placeholder="Default"
                                                                value="{{ $timetable->day_periods['Tuesday'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label><small>Wednesday Periods</small></label>
                                                            <input type="number"
                                                                class="form-control form-control-sm day-periods"
                                                                name="periods_wednesday" data-day="Wednesday" min="1"
                                                                max="12" placeholder="Default"
                                                                value="{{ $timetable->day_periods['Wednesday'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label><small>Thursday Periods</small></label>
                                                            <input type="number"
                                                                class="form-control form-control-sm day-periods"
                                                                name="periods_thursday" data-day="Thursday" min="1"
                                                                max="12" placeholder="Default"
                                                                value="{{ $timetable->day_periods['Thursday'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label><small>Friday Periods</small></label>
                                                            <input type="number"
                                                                class="form-control form-control-sm day-periods"
                                                                name="periods_friday" data-day="Friday" min="1" max="12"
                                                                placeholder="Default"
                                                                value="{{ $timetable->day_periods['Friday'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Lesson Duration (minutes)</label>
                                                <input type="number" class="form-control" name="lesson_duration"
                                                    min="10" max="120" value="{{ $timetable->lesson_duration }}"
                                                    required>
                                                @error('lesson_duration')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Break Duration (minutes)</label>
                                                <input type="number" class="form-control" name="break_duration" min="5"
                                                    max="60" value="{{ $timetable->break_duration }}" required>
                                                @error('break_duration')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Break Period Number (Which period is the break?)</label>
                                                <input type="number" class="form-control" name="break_period" min="1"
                                                    max="12" value="{{ $timetable->break_period }}" required>
                                                <small class="form-text text-muted">Enter the period number where break
                                                    should occur (e.g., 4 means break after 3rd period)</small>
                                                @error('break_period')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Allow Free Periods</label>
                                                <select class="form-control" name="has_free_periods" required>
                                                    <option value="1" {{ $timetable->has_free_periods ? 'selected' : ''
                                                        }}>Yes</option>
                                                    <option value="0" {{ !$timetable->has_free_periods ? 'selected' : ''
                                                        }}>No</option>
                                                </select>
                                                @error('has_free_periods')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group" id="timetable-section" style="display: none;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <label class="mb-0">Master Timetable Schedule</label>
                                                    <div>
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            id="auto-assign-btn">
                                                            <i class="fas fa-magic"></i> Auto-Assign Subjects
                                                        </button>
                                                        <button type="button" class="btn btn-warning btn-sm"
                                                            id="check-conflicts-btn">
                                                            <i class="fas fa-exclamation-triangle"></i> Check Conflicts
                                                        </button>
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            id="clear-all-btn">
                                                            <i class="fas fa-eraser"></i> Clear All
                                                        </button>
                                                    </div>
                                                </div>
                                                <div id="conflict-alert" class="alert alert-danger"
                                                    style="display: {{ $timetable->has_conflicts ? 'block' : 'none' }}; cursor: pointer;"
                                                    onclick="$('#conflict-list').slideToggle();">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong>⚠️ Conflicts Detected! (Click to
                                                            expand/collapse)</strong>
                                                        <span id="conflict-count" class="badge badge-danger">{{
                                                            count($timetable->conflicts ?? []) }}</span>
                                                    </div>
                                                    <ul id="conflict-list" style="display: none; margin-top: 10px;">
                                                        @foreach($timetable->conflicts ?? [] as $conflict)
                                                        <li>{{ $conflict }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="table-responsive"
                                                    style="max-height: 600px; overflow-x: auto; overflow-y: auto;">
                                                    <table class="table table-bordered table-sm" id="timetable-table"
                                                        style="font-size: 0.85rem; min-width: 1200px;">
                                                        <thead
                                                            style="position: sticky; top: 0; background-color: white; z-index: 10;">
                                                            <tr>
                                                                <th
                                                                    style="width: 80px; position: sticky; left: 0; background-color: white; z-index: 11;">
                                                                    Day</th>
                                                                <th
                                                                    style="width: 100px; position: sticky; left: 80px; background-color: white; z-index: 11;">
                                                                    Class</th>
                                                                <th colspan="999" class="text-center">Periods</th>
                                                            </tr>
                                                            <tr id="period-headers"
                                                                style="position: sticky; top: 38px; background-color: white; z-index: 10;">
                                                                <th
                                                                    style="position: sticky; left: 0; background-color: white; z-index: 11;">
                                                                </th>
                                                                <th
                                                                    style="position: sticky; left: 80px; background-color: white; z-index: 11;">
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Update Timetable</button>
                                        <a href="{{ route('timetables.index') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- JavaScript for Chained Dropdowns and Timetable Generation -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Replace the entire $(document).ready section in your edit view with this:

    $(document).ready(function() {
        let classes = {!! json_encode($classes->toArray()) !!};
        let subjects = {!! json_encode($subjects->toArray()) !!};
        let dayPeriodsConfig = {!! json_encode($timetable->day_periods) !!};
        let existingSchedule = {!! json_encode($timetable->schedule) !!};

        // Sort classes alphabetically first
        classes.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });

        // Fetch sessions, classes, and subjects on section change
        $('#section_id').change(function() {
            var sectionId = $(this).val();
            if (sectionId) {
                $.get('{{ url("/timetables/sessions") }}/' + sectionId, function(data) {
                    $('#session_id').empty().append('<option value="">Select Session</option>');
                    if (data.sessions.length > 0) {
                        data.sessions.forEach(function(session) {
                            $('#session_id').append('<option value="' + session.id + '">' + session.name + '</option>');
                        });
                        if (data.current_session_id) {
                            $('#session_id').val(data.current_session_id).trigger('change');
                        }
                    } else {
                        $('#session_id').append('<option value="">No sessions available</option>');
                    }
                }).fail(function() {
                    alert('Error fetching sessions.');
                });

                // Fetch classes and subjects
                $.get('{{ url("/timetables/classes-subjects") }}/' + sectionId, function(data) {
                    classes = data.classes.sort(function(a, b) {
                        return a.name.localeCompare(b.name);
                    });
                    subjects = data.subjects;
                    generateTimetable();
                }).fail(function() {
                    alert('Error fetching classes or subjects.');
                });

                $('#term_id').empty().append('<option value="">Select Term</option>');
            } else {
                $('#session_id').empty().append('<option value="">Select Session (after selecting section)</option>');
                $('#term_id').empty().append('<option value="">Select Term (after selecting session)</option>');
                $('#timetable-section').hide();
            }
        });

        // Fetch terms on session change
        $('#session_id').change(function() {
            var sessionId = $(this).val();
            if (sessionId) {
                $.get('{{ url("/timetables/terms") }}/' + sessionId, function(data) {
                    $('#term_id').empty().append('<option value="">Select Term</option>');
                    if (data.terms.length > 0) {
                        data.terms.forEach(function(term) {
                            $('#term_id').append('<option value="' + term.id + '">' + term.name + '</option>');
                        });
                        if (data.current_term_id) {
                            $('#term_id').val(data.current_term_id);
                        }
                    } else {
                        $('#term_id').append('<option value="">No terms available</option>');
                    }
                }).fail(function() {
                    alert('Error fetching terms.');
                });
            } else {
                $('#term_id').empty().append('<option value="">Select Term (after selecting session)</option>');
            }
        });

        // Generate timetable when inputs change
        $('[name="num_periods"], [name="lesson_duration"], [name="break_duration"], [name="break_period"], [name="has_free_periods"], .day-periods').on('input change', function() {
            if ($('#section_id').val() && classes.length > 0) {
                generateTimetable();
            }
        });

        // Auto-assign subjects button
        $('#auto-assign-btn').click(function() {
            autoAssignSubjects();
        });

        // Check conflicts button
        $('#check-conflicts-btn').click(function() {
            checkConflicts();
        });

        // Clear all button
        $('#clear-all-btn').click(function() {
            if (confirm('Are you sure you want to clear all subject assignments?')) {
                $('.period-select').val('').removeClass('conflict-subject');
                $('.break-checkbox').prop('checked', false).trigger('change');
                $('#conflict-alert').hide();
            }
        });

        function getDayPeriods(day) {
            const defaultPeriods = parseInt($('[name="num_periods"]').val()) || {{ $timetable->num_periods }};
            const dayInput = $('[name="periods_' + day.toLowerCase() + '"]').val();
            return dayInput ? parseInt(dayInput) : defaultPeriods;
        }

        function generateTimetable() {
            if (!$('#section_id').val() || classes.length === 0) return;

            const defaultPeriods = parseInt($('[name="num_periods"]').val()) || {{ $timetable->num_periods }};
            const lessonDuration = parseInt($('[name="lesson_duration"]').val()) || {{ $timetable->lesson_duration }};
            const breakDuration = parseInt($('[name="break_duration"]').val()) || {{ $timetable->break_duration }};
            const breakPeriod = parseInt($('[name="break_period"]').val()) || {{ $timetable->break_period }};
            const hasFreePeriods = $('[name="has_free_periods"]').val() == '1';
            const startTime = 8 * 60; // 8:00 AM in minutes

            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

            // Update day-specific periods configuration
            dayPeriodsConfig = {};
            days.forEach(function(day) {
                dayPeriodsConfig[day] = getDayPeriods(day);
            });

            // Find maximum periods for header generation
            const maxPeriods = Math.max(...Object.values(dayPeriodsConfig));

            // Generate period headers
            let periodHeaders = $('#period-headers');
            periodHeaders.find('th:gt(1)').remove();
            
            let currentTime = startTime;
            let periodCounter = 0;
            
            for (let p = 1; p <= maxPeriods + 1; p++) {
                let timeStr = formatTime(currentTime);
                
                if (p === breakPeriod) {
                    periodHeaders.append('<th style="background-color: #f8f9fa;">Break<br><small>' + formatTime(currentTime) + '-' + formatTime(currentTime + breakDuration) + '</small></th>');
                    currentTime += breakDuration;
                } else if (periodCounter < maxPeriods) {
                    periodCounter++;
                    let nextTime = formatTime(currentTime + lessonDuration);
                    periodHeaders.append('<th>Period ' + periodCounter + '<br><small>' + timeStr + '-' + nextTime + '</small></th>');
                    currentTime += lessonDuration;
                }
            }

            // Generate timetable body
            let tbody = $('#timetable-table tbody');
            tbody.empty();

            days.forEach(function(day, dayIndex) {
                const periodsForDay = dayPeriodsConfig[day];
                
                classes.forEach(function(cls, classIndex) {
                    let row = $('<tr></tr>');
                    
                    if (classIndex === 0) {
                        let dayCell = $('<td rowspan="' + classes.length + '" style="vertical-align: middle; font-weight: bold; position: sticky; left: 0; background-color: white; z-index: 5;"></td>');
                        dayCell.text(day);
                        if (periodsForDay !== defaultPeriods) {
                            dayCell.append('<br><small class="badge badge-info">' + periodsForDay + ' periods</small>');
                        }
                        row.append(dayCell);
                    }
                    
                    row.append($('<td style="font-weight: 500; position: sticky; left: 80px; background-color: white; z-index: 5;"></td>').text(cls.name));
                    
                    let periodCounter = 0;
                    
                    for (let p = 1; p <= maxPeriods + 1; p++) {
                        if (p === breakPeriod) {
                            let breakCell = $('<td style="background-color: #f8f9fa;"></td>');
                            
                            if (periodCounter < periodsForDay) {
                                let breakCheckbox = $('<input type="checkbox" class="break-checkbox" data-day="' + day + '" data-class="' + cls.id + '" data-period="break">');
                                let isChecked = existingSchedule[day] && existingSchedule[day]['break'] && existingSchedule[day]['break'][cls.id];
                                if (isChecked) {
                                    breakCheckbox.prop('checked', true);
                                }
                                let breakLabel = $('<label style="margin: 0; cursor: pointer;"><small>' + (isChecked ? '<strong>✓ Break</strong>' : 'Break') + '</small></label>');
                                breakCheckbox.on('change', function() {
                                    if ($(this).is(':checked')) {
                                        $(this).next('label').html('<small><strong>✓ Break</strong></small>');
                                    } else {
                                        $(this).next('label').html('<small>Break</small>');
                                    }
                                });
                                breakCell.append(breakCheckbox).append(' ').append(breakLabel);
                                breakCell.append($('<input type="hidden" name="schedule[' + day + '][break][' + cls.id + ']" value="' + (isChecked ? '1' : '0') + '" class="break-value-' + day + '-' + cls.id + '">'));
                            } else {
                                breakCell.css('background-color', '#d6d8db');
                            }
                            
                            row.append(breakCell);
                            continue;
                        }
                        
                        periodCounter++;
                        
                        if (periodCounter > periodsForDay) {
                            let cell = $('<td style="background-color: #e9ecef;"></td>');
                            row.append(cell);
                            continue;
                        }

                        let cell = $('<td></td>');
                        let select = $('<select class="form-control form-control-sm period-select" name="schedule[' + day + '][' + periodCounter + '][' + cls.id + ']" data-day="' + day + '" data-period="' + periodCounter + '" data-class="' + cls.id + '"></select>');
                        select.append('<option value="">-</option>');
                        select.append('<option value="free">Free Period</option>');
                        
                        subjects.forEach(function(subject) {
                            select.append('<option value="' + subject.id + '">' + subject.course_name + '</option>');
                        });

                        // Set existing schedule value
                        if (existingSchedule[day] && existingSchedule[day][periodCounter] && existingSchedule[day][periodCounter][cls.id]) {
                            let value = existingSchedule[day][periodCounter][cls.id];
                            select.val(value);
                        }
                        
                        select.on('change', function() {
                            $(this).removeClass('conflict-subject');
                            let selectedValue = $(this).val();
                            
                            if (selectedValue && selectedValue !== 'free' && selectedValue !== '') {
                                let nextPeriod = periodCounter + 1;
                                if (nextPeriod <= periodsForDay) {
                                    let nextSelect = $('select[name="schedule[' + day + '][' + nextPeriod + '][' + cls.id + ']"]');
                                    if (nextSelect.length && !nextSelect.val()) {
                                        if (confirm('Do you want to make this a double period?')) {
                                            nextSelect.val(selectedValue).prop('disabled', true).addClass('double-period-continuation');
                                            $(this).addClass('double-period-start');
                                        }
                                    }
                                }
                            } else if ($(this).hasClass('double-period-start')) {
                                let nextPeriod = periodCounter + 1;
                                let nextSelect = $('select[name="schedule[' + day + '][' + nextPeriod + '][' + cls.id + ']"]');
                                nextSelect.val('').prop('disabled', false).removeClass('double-period-continuation');
                                $(this).removeClass('double-period-start');
                            }
                            
                            checkConflicts();
                        });
                        
                        cell.append(select);
                        row.append(cell);
                    }
                    
                    tbody.append(row);
                });
            });

            $('.break-checkbox').on('change', function() {
                let day = $(this).data('day');
                let classId = $(this).data('class');
                let inputField = $('.break-value-' + day + '-' + classId);
                inputField.val($(this).is(':checked') ? '1' : '0');
            });

            $('#timetable-section').show();
        }

        function autoAssignSubjects() {
            if (subjects.length === 0 || classes.length === 0) {
                alert('No subjects or classes available for auto-assignment.');
                return;
            }

            if (!confirm('This will automatically assign subjects to all periods with conflict minimization. Continue?')) {
                return;
            }

            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            let periodUsage = {};

            days.forEach(function(day) {
                periodUsage[day] = {};
                const periodsForDay = dayPeriodsConfig[day];
                for (let p = 1; p <= periodsForDay; p++) {
                    periodUsage[day][p] = {};
                }
            });

            classes.forEach(function(cls, classIndex) {
                let subjectIndex = 0;
                let subjectsAssigned = {};
                
                days.forEach(function(day) {
                    const periodsForDay = dayPeriodsConfig[day];
                    
                    for (let p = 1; p <= periodsForDay; p++) {
                        let select = $('select[name="schedule[' + day + '][' + p + '][' + cls.id + ']"]');
                        if (select.length && !select.prop('disabled')) {
                            let assigned = false;
                            let attempts = 0;
                            
                            while (!assigned && attempts < subjects.length) {
                                let subject = subjects[subjectIndex % subjects.length];
                                
                                let wouldConflict = periodUsage[day][p][subject.id] > 0;
                                
                                if (!wouldConflict) {
                                    select.val(subject.id);
                                    if (!periodUsage[day][p][subject.id]) {
                                        periodUsage[day][p][subject.id] = 0;
                                    }
                                    periodUsage[day][p][subject.id]++;
                                    if (!subjectsAssigned[subject.id]) {
                                        subjectsAssigned[subject.id] = 0;
                                    }
                                    subjectsAssigned[subject.id]++;
                                    assigned = true;
                                    subjectIndex++;
                                } else {
                                    subjectIndex++;
                                    attempts++;
                                }
                            }
                            
                            if (!assigned) {
                                select.val('free');
                                subjectIndex++;
                            }
                        }
                    }
                });
            });

            let freePeriods = $('.period-select option:selected[value="free"]').length;
            
            checkConflicts();
            
            if (freePeriods > 0) {
                alert('Auto-assignment completed!\n\n' + freePeriods + ' free periods were assigned to minimize conflicts.\n\nPlease review the timetable.');
            } else {
                alert('Auto-assignment completed with no conflicts!\n\nPlease review the timetable.');
            }
        }

        function checkConflicts() {
            $('.period-select').removeClass('conflict-subject');
            $('#conflict-alert').hide();
            $('#conflict-list').empty();

            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            let conflicts = [];

            days.forEach(function(day) {
                const periodsForDay = dayPeriodsConfig[day];
                
                for (let p = 1; p <= periodsForDay; p++) {
                    let periodSubjects = {};
                    
                    classes.forEach(function(cls) {
                        let select = $('select[name="schedule[' + day + '][' + p + '][' + cls.id + ']"]');
                        let subjectId = select.val();
                        
                        if (subjectId && subjectId !== 'free' && subjectId !== '') {
                            if (!periodSubjects[subjectId]) {
                                periodSubjects[subjectId] = [];
                            }
                            periodSubjects[subjectId].push({
                                className: cls.name,
                                element: select
                            });
                        }
                    });
                    
                    Object.keys(periodSubjects).forEach(function(subjectId) {
                        if (periodSubjects[subjectId].length > 1) {
                            let subjectName = subjects.find(s => s.id == subjectId)?.course_name || 'Unknown';
                            let classNames = periodSubjects[subjectId].map(c => c.className).join(', ');
                            
                            conflicts.push({
                                day: day,
                                period: p,
                                subject: subjectName,
                                classes: classNames
                            });
                            
                            periodSubjects[subjectId].forEach(function(item) {
                                item.element.addClass('conflict-subject');
                            });
                        }
                    });
                }
            });

            if (conflicts.length > 0) {
                $('#conflict-alert').show();
                $('#conflict-count').text(conflicts.length);
                conflicts.forEach(function(conflict) {
                    $('#conflict-list').append(
                        '<li>' + conflict.day + ', Period ' + conflict.period + ': ' + 
                        '<strong>' + conflict.subject + '</strong> is assigned to multiple classes (' + 
                        conflict.classes + ')</li>'
                    );
                });
            } else {
                $('#conflict-alert').hide();
            }
        }

        function formatTime(minutes) {
            let hours = Math.floor(minutes / 60);
            let mins = minutes % 60;
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            return hours + ':' + (mins < 10 ? '0' : '') + mins + ' ' + ampm;
        }

        // Initialize timetable on page load
        if ($('#section_id').val() && classes.length > 0) {
            generateTimetable();
            
            // Check for existing conflicts
            if ({{ $timetable->has_conflicts ? 'true' : 'false' }}) {
                checkConflicts();
            }
        }
    });
    </script>

    <style>
        .conflict-subject {
            border: 2px solid #dc3545 !important;
            background-color: #f8d7da !important;
        }

        .double-period-start {
            border-left: 3px solid #28a745 !important;
        }

        .double-period-continuation {
            border-right: 3px solid #28a745 !important;
            background-color: #d4edda !important;
        }

        .badge-info {
            font-size: 0.7rem;
            padding: 2px 6px;
        }
    </style>

    @include('includes.edit_footer')