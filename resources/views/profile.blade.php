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
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">User Profile</h4>
                            </div>
                            <div class="card-body">
                                <section class="section">
                                    <div class="section-body">
                                        <div class="row mt-sm-4">
                                            <div class="col-12 col-md-12 col-lg-4">
                                                <div class="card author-box shadow-sm">
                                                    <div class="card-body">
                                                        <div class="author-box-center">
                                                            <img src="{{ asset('images/profile-place-holder.jpg') }}"
                                                                alt="image" class="rounded-circle author-box-picture mb-3" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #f4f6f9;">

                                                            <div class="clearfix"></div>
                                                            <div class="author-box-name">
                                                                <a href="#" class="h5">{{ $user->name }}</a>
                                                            </div>
                                                            <div class="author-box-job mt-2">
                                                                <span class="badge badge-lg 
                                                                    @if($user->user_type == 4) badge-primary
                                                                    @elseif($user->user_type == 3) badge-success
                                                                    @elseif(in_array($user->user_type, [1,2])) badge-danger
                                                                    @elseif($user->user_type == 5) badge-info
                                                                    @elseif($user->user_type == 6) badge-warning
                                                                    @endif">
                                                                    @if($user->user_type == 4)
                                                                        <i class="fas fa-user-graduate mr-1"></i> Student
                                                                    @elseif($user->user_type == 3)
                                                                        <i class="fas fa-chalkboard-teacher mr-1"></i> Teacher
                                                                    @elseif(in_array($user->user_type, [1,2]))
                                                                        <i class="fas fa-user-shield mr-1"></i> Administrator
                                                                    @elseif($user->user_type == 5)
                                                                        <i class="fas fa-user-friends mr-1"></i> Parent
                                                                    @elseif($user->user_type == 6)
                                                                        <i class="fas fa-money-check-alt mr-1"></i> Bursar
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            @if($user->user_type == 4 && $user->class)
                                                                <div class="mt-2">
                                                                    <small class="text-muted">{{ $user->class->name ?? 'Not Assigned' }}</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="text-center mt-4">
                                                            @if($user->user_type == 4)
                                                                <div class="author-box-description">
                                                                    <div class="alert alert-light mb-0 py-2">
                                                                        <small class="text-muted d-block mb-1">Admission Number</small>
                                                                        <strong class="h6">{{ $user->admission_no }}</strong>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card shadow-sm">
                                                    <div class="card-header">
                                                        <h4 class="mb-0"><i class="fas fa-user-circle mr-2"></i>Personal Details</h4>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="py-2">
                                                            @if($user->user_type == 4 || $user->dob)
                                                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                                    <span class="font-weight-600"><i class="fas fa-birthday-cake mr-2 text-muted"></i>Date of Birth</span>
                                                                    <span class="text-muted">
                                                                        {{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('d-m-Y') : 'Not provided' }}
                                                                    </span>
                                                                </div>
                                                            @endif

                                                            @if($user->user_type == 4 || $user->gender)
                                                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                                    <span class="font-weight-600"><i class="fas fa-venus-mars mr-2 text-muted"></i>Gender</span>
                                                                    <span class="text-muted">
                                                                        {{ $user->gender ? ucfirst($user->gender) : 'Not provided' }}
                                                                    </span>
                                                                </div>
                                                            @endif

                                                            @if($user->user_type == 4 || $user->phone)
                                                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                                    <span class="font-weight-600"><i class="fas fa-phone mr-2 text-muted"></i>Phone</span>
                                                                    <span class="text-muted">
                                                                        {{ $user->phone ?? 'Not provided' }}
                                                                    </span>
                                                                </div>
                                                            @endif

                                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                                <span class="font-weight-600"><i class="fas fa-envelope mr-2 text-muted"></i>Email</span>
                                                                <span class="text-muted text-break">
                                                                    {{ $user->email }}
                                                                </span>
                                                            </div>

                                                            @if($user->user_type == 4)
                                                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                                    <span class="font-weight-600"><i class="fas fa-school mr-2 text-muted"></i>Class</span>
                                                                    <span class="text-muted">
                                                                        {{ $user->class->name ?? 'Not Assigned' }}
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center py-2">
                                                                    <span class="font-weight-600"><i class="fas fa-layer-group mr-2 text-muted"></i>Section</span>
                                                                    <span class="text-muted">
                                                                        {{ $user->class?->section?->section_name ?? 'Not Assigned' }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-12 col-lg-8">
                                                <div class="card shadow-sm">
                                                    <div class="padding-20">
                                                        <div class="card-header">
                                                            <h4 class="mb-0">
                                                                <i class="fas fa-info-circle mr-2"></i>
                                                                @if($user->user_type == 4)
                                                                    Student Information
                                                                @elseif($user->user_type == 3)
                                                                    Teacher Information
                                                                @elseif($user->user_type == 5)
                                                                    Parent Information
                                                                @else
                                                                    Profile Information
                                                                @endif
                                                            </h4>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6 col-12 mb-4">
                                                                    <div class="form-group mb-0">
                                                                        <label class="font-weight-600 mb-2"><i class="fas fa-user mr-1"></i> Full Name</label>
                                                                        <p class="text-muted mb-0">{{ $user->name }}</p>
                                                                    </div>
                                                                </div>

                                                                @if($user->user_type == 4 || $user->phone)
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-phone mr-1"></i> Phone Number</label>
                                                                            <p class="text-muted mb-0">{{ $user->phone ?? 'Not provided' }}</p>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                <div class="col-md-6 col-12 mb-4">
                                                                    <div class="form-group mb-0">
                                                                        <label class="font-weight-600 mb-2"><i class="fas fa-envelope mr-1"></i> Email Address</label>
                                                                        <p class="text-muted mb-0 text-break">{{ $user->email }}</p>
                                                                    </div>
                                                                </div>

                                                                @if($user->user_type == 4 || $user->dob)
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-birthday-cake mr-1"></i> Date of Birth</label>
                                                                            <p class="text-muted mb-0">
                                                                                {{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('F d, Y') : 'Not provided' }}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if($user->user_type == 4 || $user->gender)
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-venus-mars mr-1"></i> Gender</label>
                                                                            <p class="text-muted mb-0">{{ $user->gender ? ucfirst($user->gender) : 'Not provided' }}</p>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if($user->user_type == 4)
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-id-card mr-1"></i> Admission Number</label>
                                                                            <p class="text-muted mb-0">{{ $user->admission_no }}</p>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if($user->address || $user->user_type == 4)
                                                                    <div class="col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-map-marker-alt mr-1"></i> Address</label>
                                                                            <p class="text-muted mb-0">{{ $user->address ?? 'Not provided' }}</p>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            @if($user->user_type == 4)
                                                                <!-- Student: Guardian Information -->
                                                                <div class="separator my-4"></div>
                                                                <div class="section-title mb-4 mt-4">
                                                                    <h5 class="font-weight-700"><i class="fas fa-user-shield mr-2"></i>Guardian Information</h5>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-user mr-1"></i> Guardian Name</label>
                                                                            <p class="text-muted mb-0">{{ $user->guardian_name ?? 'Not provided' }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-phone mr-1"></i> Guardian Phone</label>
                                                                            <p class="text-muted mb-0">{{ $user->guardian_phone ?? 'Not provided' }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-envelope mr-1"></i> Guardian Email</label>
                                                                            <p class="text-muted mb-0 text-break">{{ $user->guardian_email ?? 'Not provided' }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-map-marker-alt mr-1"></i> Guardian Address</label>
                                                                            <p class="text-muted mb-0">{{ $user->guardian_address ?? 'Not provided' }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Student: Academic Information -->
                                                                <div class="separator my-4"></div>
                                                                <div class="section-title mb-4 mt-4">
                                                                    <h5 class="font-weight-700"><i class="fas fa-graduation-cap mr-2"></i>Academic Information</h5>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-school mr-1"></i> Current Class</label>
                                                                            <p class="text-muted mb-0">{{ $user->class->name ?? 'Not Assigned' }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-layer-group mr-1"></i> Section</label>
                                                                            <p class="text-muted mb-0">{{ $user->class?->section?->section_name ?? 'Not Assigned' }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Academic Session</label>
                                                                            <p class="text-muted mb-0">{{ date('Y') . '/' . (date('Y') + 1) }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-sign-in-alt mr-1"></i> Enrollment Date</label>
                                                                            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($user->created_at)->format('F d, Y') }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            @elseif($user->user_type == 3)
                                                                <!-- Teacher: Assigned Classes & Courses -->
                                                                <div class="separator my-4"></div>
                                                                <div class="section-title mb-4 mt-4">
                                                                    <h5 class="font-weight-700"><i class="fas fa-chalkboard mr-2"></i>Assigned Classes</h5>
                                                                </div>
                                                                @if($user->classes->count() > 0)
                                                                    <div class="row">
                                                                        @foreach($user->classes as $class)
                                                                            <div class="col-md-6 col-12 mb-3">
                                                                                <div class="alert alert-light border-left-primary py-2">
                                                                                    <strong class="d-block">{{ $class->name }}</strong>
                                                                                    <small class="text-muted">Section: {{ $class->section->section_name ?? 'N/A' }}</small>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <div class="alert alert-light">
                                                                        <i class="fas fa-info-circle mr-2"></i>No classes assigned yet.
                                                                    </div>
                                                                @endif

                                                                <div class="separator my-4"></div>
                                                                <div class="section-title mb-4">
                                                                    <h5 class="font-weight-700"><i class="fas fa-book mr-2"></i>Courses Taught</h5>
                                                                </div>
                                                                @if($user->courses->count() > 0)
                                                                    <div class="row">
                                                                        @foreach($user->courses as $course)
                                                                            <div class="col-md-6 col-12 mb-3">
                                                                                <div class="alert alert-light border-left-success py-2">
                                                                                    <strong class="d-block">{{ $course->course_name }}</strong>
                                                                                    <small class="text-muted">Section: {{ $course->section->section_name ?? 'N/A' }}</small>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <div class="alert alert-light">
                                                                        <i class="fas fa-info-circle mr-2"></i>No courses assigned yet.
                                                                    </div>
                                                                @endif

                                                                <div class="separator my-4"></div>
                                                                <div class="section-title mb-3">
                                                                    <h5 class="font-weight-700"><i class="fas fa-calendar-check mr-2"></i>Joined Date</h5>
                                                                </div>
                                                                <p class="text-muted">{{ \Carbon\Carbon::parse($user->created_at)->format('F d, Y') }}</p>

                                                            @elseif($user->user_type == 5)
                                                                <!-- Parent: Linked Students -->
                                                                <div class="separator my-4"></div>
                                                                <div class="section-title mb-4 mt-4">
                                                                    <h5 class="font-weight-700"><i class="fas fa-child mr-2"></i>My Children</h5>
                                                                </div>
                                                                @if($user->students->count() > 0)
                                                                    <div class="row">
                                                                        @foreach($user->students as $student)
                                                                            <div class="col-12 mb-3">
                                                                                <div class="card card-primary shadow-sm">
                                                                                    <div class="card-header bg-primary">
                                                                                        <h4 class="text-white mb-0"><i class="fas fa-user-graduate mr-2"></i>{{ $student->name }}</h4>
                                                                                    </div>
                                                                                    <div class="card-body">
                                                                                        <div class="row">
                                                                                            <div class="col-md-4 mb-2">
                                                                                                <small class="text-muted d-block">Admission No</small>
                                                                                                <strong>{{ $student->admission_no ?? 'N/A' }}</strong>
                                                                                            </div>
                                                                                            <div class="col-md-4 mb-2">
                                                                                                <small class="text-muted d-block">Class</small>
                                                                                                <strong>{{ $student->class->name ?? 'N/A' }} @if($student->class?->section) ({{ $student->class->section->section_name }}) @endif</strong>
                                                                                            </div>
                                                                                            <div class="col-md-4 mb-2">
                                                                                                <small class="text-muted d-block">Gender</small>
                                                                                                <strong>{{ $student->gender ? ucfirst($student->gender) : 'N/A' }}</strong>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <div class="alert alert-light">
                                                                        <i class="fas fa-info-circle mr-2"></i>No children linked yet.
                                                                    </div>
                                                                @endif

                                                                <div class="separator my-4"></div>
                                                                <div class="section-title mb-3">
                                                                    <h5 class="font-weight-700"><i class="fas fa-calendar-check mr-2"></i>Joined Date</h5>
                                                                </div>
                                                                <p class="text-muted">{{ \Carbon\Carbon::parse($user->created_at)->format('F d, Y') }}</p>

                                                            @else
                                                                <!-- Admin (1,2) and Bursar (6): Basic Info -->
                                                                <div class="separator my-4"></div>
                                                                <div class="section-title mb-4 mt-4">
                                                                    <h5 class="font-weight-700"><i class="fas fa-cog mr-2"></i>Account Information</h5>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-user-tag mr-1"></i> Role</label>
                                                                            <p class="text-muted mb-0">
                                                                                @if(in_array($user->user_type, [1,2])) Administrator
                                                                                @elseif($user->user_type == 6) Bursar
                                                                                @endif
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 mb-4">
                                                                        <div class="form-group mb-0">
                                                                            <label class="font-weight-600 mb-2"><i class="fas fa-calendar-check mr-1"></i> Joined Date</label>
                                                                            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($user->created_at)->format('F d, Y') }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <style>
        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important;
        }
        
        .badge-lg {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .separator {
            border-top: 2px solid #f4f6f9;
        }
        
        .border-left-primary {
            border-left: 3px solid #6777ef !important;
        }
        
        .border-left-success {
            border-left: 3px solid #66bb6a !important;
        }
        
        .font-weight-600 {
            font-weight: 600;
        }
        
        .font-weight-700 {
            font-weight: 700;
        }
        
        .alert-light {
            background-color: #f8f9fa;
            border-color: #e9ecef;
        }
        
        .text-break {
            word-break: break-word;
        }
    </style>

    <script src="{{ asset('js/app.min.js') }}"></script>
    @include('includes.footer')