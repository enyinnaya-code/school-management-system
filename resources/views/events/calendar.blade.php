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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0"><i data-feather="calendar"></i> School Calendar</h4>
                                <div>
                                    <button class="btn btn-sm btn-info mr-2" id="todayBtn">
                                        <i data-feather="calendar"></i> Today
                                    </button>
                                    <button class="btn btn-sm btn-success mr-2" id="viewUpcomingBtn">
                                        <i data-feather="list"></i> Upcoming Events
                                    </button>
                                    @if(in_array(auth()->user()->user_type, [1, 2]))
                                    <button class="btn btn-primary" id="addEventBtn">
                                        <i data-feather="plus"></i> Add Event
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Calendar Legend -->
                                <div class="mb-3 d-flex align-items-center flex-wrap">
                                    @if(in_array(auth()->user()->user_type, [1, 2, 7]))
                                    <small class="text-muted mr-3"><i data-feather="info" class="feather-sm"></i> Click
                                        on any date to create an event, click an event to edit</small>
                                    <span class="badge badge-info">Drag to move</span>
                                    <span class="badge badge-primary ml-2">Resize to change duration</span>
                                    @else
                                    <small class="text-muted mr-3"><i data-feather="info" class="feather-sm"></i> View
                                        school events and important dates</small>
                                    <span class="badge badge-secondary">Read-only view</span>
                                    @endif
                                </div>
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Add/Edit/View Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="eventForm">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTitle">
                            <i data-feather="calendar" class="feather-sm"></i> Add New Event
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="eventId">
                        <input type="hidden" id="eventCreatedBy">

                        <!-- Alert for validation errors -->
                        <div class="alert alert-danger d-none" id="errorAlert"></div>

                        <!-- Original form groups (will be replaced in view mode) -->
                        <div id="originalFormContent">
                            <div class="form-group">
                                <label for="title">Event Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title"
                                    placeholder="e.g., Parent-Teacher Meeting" required>
                                <small class="form-text text-muted">Enter a descriptive title for your event</small>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" rows="3"
                                    placeholder="Add event details, location, or notes..."></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date">Start Date & Time <span
                                                class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" id="start_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date">End Date & Time</label>
                                        <input type="datetime-local" class="form-control" id="end_date">
                                        <small class="form-text text-muted">Optional</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input type="checkbox" class="custom-control-input" id="is_all_day">
                                    <label class="custom-control-label" for="is_all_day">
                                        <i data-feather="clock" class="feather-sm"></i> All Day Event
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="color">Event Color</label>
                                <div class="d-flex align-items-center">
                                    <input type="color" class="form-control" id="color" value="#3788d8"
                                        style="width: 80px; height: 40px;">
                                    <div class="ml-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary color-preset"
                                            data-color="#3788d8">Blue</button>
                                        <button type="button" class="btn btn-sm btn-outline-success color-preset"
                                            data-color="#28a745">Green</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger color-preset"
                                            data-color="#dc3545">Red</button>
                                        <button type="button" class="btn btn-sm btn-outline-warning color-preset"
                                            data-color="#ffc107">Yellow</button>
                                        <button type="button" class="btn btn-sm btn-outline-info color-preset"
                                            data-color="#17a2b8">Cyan</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Creator Info -->
                        <div class="alert alert-info d-none" id="creatorInfo">
                            <i data-feather="user" class="feather-sm"></i> <span id="creatorName"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i data-feather="x"></i> <span id="closeText">Cancel</span>
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i data-feather="save"></i> <span id="saveBtnText">Save Event</span>
                        </button>
                        <button type="button" class="btn btn-danger d-none" id="deleteBtn">
                            <i data-feather="trash-2"></i> Delete Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upcoming Events Modal -->
    <div class="modal fade" id="upcomingEventsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i data-feather="calendar" class="feather-sm"></i> Upcoming Events
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="upcomingEventsList"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i data-feather="x"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

    <style>
        #calendar {
            font-family: 'Nunito', sans-serif;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .fc-event:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .fc-daygrid-event {
            white-space: normal;
        }

        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 600;
        }

        .fc-button {
            text-transform: capitalize !important;
            font-weight: 500 !important;
        }

        .fc-day-today {
            background-color: rgba(55, 136, 216, 0.1) !important;
        }

        .color-preset {
            margin: 2px;
            padding: 4px 12px;
            font-size: 0.85rem;
        }

        .feather-sm {
            width: 16px;
            height: 16px;
            vertical-align: middle;
        }

        .modal-header.bg-primary,
        .modal-header.bg-success {
            border-bottom: none;
        }

        .fc-loading {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Upcoming events list styles */
        .upcoming-event-item {
            border-left: 4px solid;
            transition: all 0.2s;
        }

        .upcoming-event-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .event-color-badge {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        /* Enhanced drag and resize cursor */
        .fc-event-draggable {
            cursor: move;
        }

        .fc-event-resizable {
            cursor: ew-resize;
        }
    </style>

    <script>
    // Permissions
    const currentUserId = {{ auth()->id() }};
    const userType = {{ auth()->user()->user_type }};
    const canEditAnyEvent = [1, 2, 7].includes(userType);
    const canCreateEvents = [1, 2].includes(userType);

    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        let calendar;

        // Store original modal body content for restoration
        const originalFormHTML = document.getElementById('originalFormContent').innerHTML;

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day',
                list: 'List'
            },
            height: 'auto',
            events: '{{ route("events.fetch") }}',
            editable: canEditAnyEvent,
            selectable: canCreateEvents,
            selectMirror: true,
            dayMaxEvents: true,
            navLinks: true,
            nowIndicator: true,
            eventTimeFormat: { 
                hour: '2-digit', 
                minute: '2-digit', 
                meridiem: 'short' 
            },
            displayEventEnd: true,
            eventResizableFromStart: true,
            eventDurationEditable: true,
            dragRevertDuration: 500,

            loading: function(isLoading) {
                calendarEl.classList.toggle('fc-loading', isLoading);
            },

            select: function(info) {
                if (canCreateEvents) openModal('add', info);
            },

            eventClick: function(info) {
                const eventCreatedBy = info.event.extendedProps.created_by;
                const canEdit = canEditAnyEvent || eventCreatedBy === currentUserId;
                openModal(canEdit ? 'edit' : 'view', info);
            },

            eventDrop: function(info) {
                const eventCreatedBy = info.event.extendedProps.created_by;
                if (canEditAnyEvent || eventCreatedBy === currentUserId) {
                    updateEventDates(info.event);
                } else {
                    info.revert();
                    showToast('error', 'You do not have permission to edit this event');
                }
            },

            eventResize: function(info) {
                const eventCreatedBy = info.event.extendedProps.created_by;
                if (canEditAnyEvent || eventCreatedBy === currentUserId) {
                    updateEventDates(info.event);
                } else {
                    info.revert();
                    showToast('error', 'You do not have permission to edit this event');
                }
            },

            eventAllow: function(dropInfo, draggedEvent) {
                const eventCreatedBy = draggedEvent.extendedProps.created_by;
                return canEditAnyEvent || eventCreatedBy === currentUserId;
            },

            eventDidMount: function(info) {
                const tooltipContent = info.event.extendedProps.description || info.event.title;
                const creatorName = info.event.extendedProps.creator_name || 'Unknown';
                $(info.el).tooltip({
                    title: `${tooltipContent}<br><small class="text-muted">By: ${creatorName}</small>`,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body',
                    html: true
                });
            }
        });

        calendar.render();

        if (typeof feather !== 'undefined') feather.replace();

        $('#todayBtn').on('click', () => calendar.today());
        $('#addEventBtn').on('click', () => canCreateEvents && openModal('add'));
        $('#viewUpcomingBtn').on('click', function() {
            showUpcomingEvents();
        });

        $('.color-preset').on('click', function() {
            if (!$(this).prop('disabled')) {
                $('#color').val($(this).data('color'));
                $('.color-preset').removeClass('active');
                $(this).addClass('active');
            }
        });

        $('#is_all_day').on('change', function() {
            if ($(this).prop('disabled')) return;
            const checked = $(this).is(':checked');
            const startVal = $('#start_date').val();
            const endVal = $('#end_date').val();
            if (checked) {
                $('#start_date').attr('type', 'date').val(startVal.split('T')[0]);
                if (endVal) $('#end_date').attr('type', 'date').val(endVal.split('T')[0]);
            } else {
                $('#start_date').attr('type', 'datetime-local');
                $('#end_date').attr('type', 'datetime-local');
            }
        });

        $('#eventForm').on('submit', function(e) {
            e.preventDefault();
            hideError();

            const id = $('#eventId').val();
            const url = id ? `/events/${id}` : '/events';
            const type = id ? 'PUT' : 'POST';

            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            if (endDate && new Date(endDate) < new Date(startDate)) {
                showError('End date must be after start date');
                return;
            }

            $('#saveBtn').prop('disabled', true).html('<i data-feather="loader" class="feather-sm"></i> Saving...');

            $.ajax({
                url, type,
                data: {
                    _token: '{{ csrf_token() }}',
                    title: $('#title').val(),
                    description: $('#description').val(),
                    start_date: startDate,
                    end_date: endDate || null,
                    is_all_day: $('#is_all_day').is(':checked') ? 1 : 0,
                    color: $('#color').val()
                },
                success: () => {
                    $('#eventModal').modal('hide');
                    calendar.refetchEvents();
                    showToast('success', id ? 'Event updated successfully!' : 'Event created successfully!');
                },
                error: (xhr) => {
                    const msg = xhr.status === 403 ? 'You do not have permission' : (xhr.responseJSON?.message || 'Something went wrong.');
                    showError(msg);
                },
                complete: () => {
                    $('#saveBtn').prop('disabled', false).html('<i data-feather="save"></i> <span id="saveBtnText">Save Event</span>');
                    feather.replace();
                }
            });
        });

        $('#deleteBtn').on('click', function() {
            if (!confirm('Are you sure you want to delete this event?')) return;
            const id = $('#eventId').val();
            $(this).prop('disabled', true).html('<i data-feather="loader"></i> Deleting...');
            $.ajax({
                url: `/events/${id}`, type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: () => {
                    $('#eventModal').modal('hide');
                    calendar.refetchEvents();
                    showToast('success', 'Event deleted successfully!');
                },
                error: (xhr) => {
                    showError(xhr.status === 403 ? 'No permission to delete' : 'Failed to delete');
                    $(this).prop('disabled', false).html('<i data-feather="trash-2"></i> Delete Event');
                }
            });
        });

        function showUpcomingEvents() {
            const now = new Date();
            const events = calendar.getEvents()
                .filter(event => new Date(event.start) >= now)
                .sort((a, b) => new Date(a.start) - new Date(b.start))
                .slice(0, 30);

            if (events.length === 0) {
                $('#upcomingEventsList').html(
                    '<div class="alert alert-info text-center py-5">' +
                    '<i data-feather="calendar" style="width: 48px; height: 48px; margin-bottom: 16px;"></i>' +
                    '<h5>No Upcoming Events</h5>' +
                    '<p class="mb-0">There are no events scheduled for the future.</p>' +
                    '</div>'
                );
            } else {
                let html = '<div class="list-group list-group-flush">';
                events.forEach(event => {
                    const startDate = formatDisplayDate(event.start, event.allDay);
                    const endDate = event.end ? formatDisplayDate(event.end, event.allDay) : '';
                    const description = event.extendedProps.description || '';
                    const creatorName = event.extendedProps.creator_name || 'Unknown';
                    
                    html += `
                        <div class="list-group-item upcoming-event-item" style="border-left-color: ${event.backgroundColor}">
                            <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 font-weight-bold">
                                    <span class="event-color-badge" style="background-color: ${event.backgroundColor}"></span>
                                    ${event.title}
                                </h6>
                                <span class="badge badge-light">${creatorName}</span>
                            </div>
                            <div class="mb-1">
                                <small class="text-success">
                                    <i data-feather="clock" class="feather-sm"></i> 
                                    <strong>Start:</strong> ${startDate}
                                </small>
                            </div>
                            ${endDate ? `
                            <div class="mb-1">
                                <small class="text-danger">
                                    <i data-feather="clock" class="feather-sm"></i> 
                                    <strong>End:</strong> ${endDate}
                                </small>
                            </div>
                            ` : ''}
                            ${description ? `
                            <div class="mt-2">
                                <small class="text-muted">${description}</small>
                            </div>
                            ` : ''}
                            ${event.allDay ? '<span class="badge badge-info badge-sm mt-2">All Day</span>' : ''}
                        </div>
                    `;
                });
                html += '</div>';
                
                if (events.length === 30) {
                    html += '<div class="alert alert-warning mt-3 mb-0"><small><i data-feather="info" class="feather-sm"></i> Showing the next 30 events only</small></div>';
                }
                
                $('#upcomingEventsList').html(html);
            }
            
            $('#upcomingEventsModal').modal('show');
            feather.replace();
        }

        function openModal(mode, info = null) {
            resetModal();
            hideError();

            if (mode === 'add') {
                $('#modalTitle').html('<i data-feather="calendar"></i> Add New Event');
                $('#saveBtnText').text('Create Event');
                $('#closeText').text('Cancel');

                if (info) {
                    $('#start_date').val(formatDateForInput(info.start, info.allDay));
                    if (info.end) $('#end_date').val(formatDateForInput(info.end, info.allDay));
                    $('#is_all_day').prop('checked', info.allDay);
                } else {
                    $('#start_date').val(formatDateForInput(new Date(), false));
                }

            } else if (mode === 'edit') {
                $('#modalTitle').html('<i data-feather="edit-2"></i> Edit Event');
                $('#saveBtnText').text('Update Event');
                $('#closeText').text('Cancel');
                $('#eventId').val(info.event.id);
                $('#title').val(info.event.title);
                $('#description').val(info.event.extendedProps.description || '');
                $('#start_date').val(formatDateForInput(info.event.start, info.event.allDay));
                if (info.event.end) $('#end_date').val(formatDateForInput(info.event.end, info.event.allDay));
                $('#is_all_day').prop('checked', info.event.allDay);
                $('#color').val(info.event.backgroundColor);
                $('#deleteBtn').removeClass('d-none');

            } else if (mode === 'view') {
                $('#modalTitle').html('<i data-feather="eye"></i> View Event');
                $('#closeText').text('Close');
                $('#saveBtn, #deleteBtn').addClass('d-none');

                const title = info.event.title;
                const description = info.event.extendedProps.description || '<em>No description</em>';
                const start = formatDisplayDate(info.event.start, info.event.allDay);
                const end = info.event.end ? formatDisplayDate(info.event.end, info.event.allDay) : '<em>No end date</em>';
                const allDay = info.event.allDay ? 'Yes' : 'No';
                const creatorName = info.event.extendedProps.creator_name || 'Unknown';

                $('#originalFormContent').html(`
                    <div class="form-group"><label>Event Title</label><p class="font-weight-bold mb-0">${title}</p></div>
                    <div class="form-group"><label>Description</label><div class="p-3 bg-light rounded border" style="min-height:60px;">${description.replace(/\n/g, '<br>')}</div></div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Start Date & Time</label><p class="mb-0">${start}</p></div></div>
                        <div class="col-md-6"><div class="form-group"><label>End Date & Time</label><p class="mb-0">${end}</p></div></div>
                    </div>
                    <div class="form-group"><label>All Day Event</label><p class="mb-0">${allDay}</p></div>
                `);

                $('#creatorName').text('Created by: ' + creatorName);
                $('#creatorInfo').removeClass('d-none');
            }

            $('#eventModal').modal('show');
            feather.replace();
        }

        function resetModal() {
            $('#eventForm')[0].reset();
            $('#eventId, #eventCreatedBy').val('');
            $('#deleteBtn').addClass('d-none');
            $('#saveBtn').removeClass('d-none');
            $('#color').val('#3788d8');
            $('#start_date, #end_date').attr('type', 'datetime-local');
            $('.color-preset').removeClass('active');
            $('#creatorInfo').addClass('d-none');
            $('#originalFormContent').html(originalFormHTML);
            hideError();
        }

        function updateEventDates(event) {
            $.ajax({
                url: `/events/${event.id}`,
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    title: event.title,
                    description: event.extendedProps.description || '',
                    start_date: formatDateForServer(event.start),
                    end_date: event.end ? formatDateForServer(event.end) : null,
                    is_all_day: event.allDay ? 1 : 0,
                    color: event.backgroundColor
                },
                success: () => {
                    showToast('success', 'Event updated successfully!');
                    calendar.refetchEvents();
                },
                error: (xhr) => {
                    calendar.refetchEvents();
                    showToast('error', xhr.status === 403 ? 'No permission' : 'Failed to update event');
                }
            });
        }

        function formatDateForInput(date, allDay) {
            const d = new Date(date);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            if (allDay) return `${y}-${m}-${day}`;
            const h = String(d.getHours()).padStart(2, '0');
            const min = String(d.getMinutes()).padStart(2, '0');
            return `${y}-${m}-${day}T${h}:${min}`;
        }

        function formatDateForServer(date) {
            const d = new Date(date);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const h = String(d.getHours()).padStart(2, '0');
            const min = String(d.getMinutes()).padStart(2, '0');
            const sec = String(d.getSeconds()).padStart(2, '0');
            return `${y}-${m}-${day} ${h}:${min}:${sec}`;
        }

        function formatDisplayDate(date, allDay) {
            const d = new Date(date);
            const options = allDay 
                ? { year: 'numeric', month: 'long', day: 'numeric' }
                : { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            return d.toLocaleDateString(undefined, options);
        }

        function showError(msg) { 
            $('#errorAlert').text(msg).removeClass('d-none'); 
        }
        
        function hideError() { 
            $('#errorAlert').addClass('d-none').text(''); 
        }

        function showToast(type, message) {
            iziToast[type]({
                title: type === 'success' ? 'Success' : 'Error',
                message: message,
                position: 'topRight',
                timeout: 3000
            });
        }
    });
    </script>
</body>