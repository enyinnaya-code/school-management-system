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
                                <h4>Mark Staff Attendance</h4>
                            </div>

                            <div class="card-body">
                                <!-- Display Success/Error Messages -->
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <!-- Display Current Session and Term -->
                                @if ($currentSession)
                                    <div class="alert alert-info">
                                        Current Session: {{ $currentSession->name }}
                                        @if ($currentSession->terms->where('is_current', true)->first())
                                            <br>Current Term: {{ $currentSession->terms->where('is_current', true)->first()->name }}
                                        @else
                                            <br>No current term set for this session.
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        No current session set. Please set a session and term before marking attendance.
                                    </div>
                                @endif

                                <!-- Attendance Form -->
                                @if ($currentSession && $currentSession->terms->where('is_current', true)->first())
                                    <form method="POST" action="{{ route('attendance.teachers.store') }}">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $currentSession->id }}">
                                        <input type="hidden" name="session_term" value="{{ $currentSession->terms->where('is_current', true)->first()->id }}">

                                        <!-- Attendance Date -->
                                        <div class="form-group row px-3">
                                            <label class="col-md-2 col-form-label">Date</label>
                                            <div class="col-md-4">
                                                <input type="date" name="attendance_date" class="form-control" value="{{ date('Y-m-d') }}" required readonly>
                                                @error('attendance_date')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Attendance Time -->
                                        <div class="form-group row px-3">
                                            <label class="col-md-2 col-form-label">Time</label>
                                            <div class="col-md-4">
                                                <input type="time" name="attendance_time" class="form-control" value="{{ date('H:i') }}" required>
                                                @error('attendance_time')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Teacher Selection Dropdown -->
                                        <div class="form-group row px-3">
                                            <label class="col-md-2 col-form-label">Select Teacher</label>
                                            <div class="col-md-4">
                                                <select class="form-control" name="teacher_id" required>
                                                    <option value="">Select a teacher...</option>
                                                    @foreach ($teachers as $teacher)
                                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('teacher_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Attendance Status Dropdown -->
                                        <div class="form-group row px-3">
                                            <label class="col-md-2 col-form-label">Attendance Status</label>
                                            <div class="col-md-4">
                                                <select class="form-control" name="attendance" required>
                                                    <option value="">Select status...</option>
                                                    <option value="Present">Present</option>
                                                    <option value="Absent">Absent</option>
                                                    {{-- <option value="Late">Late</option> --}}
                                                    {{-- <option value="On Leave">On Leave</option> --}}
                                                </select>
                                                @error('attendance')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="form-group mt-4 pt-4">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save"></i> Save Attendance
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')
</body>