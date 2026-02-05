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
                    

                    <!-- Promotion Summary -->
                    <div class="card mb-4">
                        <div class="section-header">
                        <h1><i class="fas fa-info-circle"></i> Promotion Details</h1>
                        <div class="section-header-breadcrumb">
                            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                            <div class="breadcrumb-item"><a href="{{ route('students.promotion.history') }}">Promotion History</a></div>
                            <div class="breadcrumb-item active">{{ $promotion->promotion_batch_id }}</div>
                        </div>
                    </div>
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-clipboard-list"></i> Promotion Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="200">Batch ID:</th>
                                            <td><strong class="text-primary">{{ $promotion->promotion_batch_id }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Session:</th>
                                            <td>{{ $promotion->session_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Term:</th>
                                            <td>{{ $promotion->term_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Section:</th>
                                            <td><span class="badge badge-info">{{ $promotion->section_name }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Source Classes:</th>
                                            <td>{{ $promotion->source_class_names }}</td>
                                        </tr>
                                        <tr>
                                            <th>Promotion Type:</th>
                                            <td>
                                                @if($promotion->promotion_type === 'bulk')
                                                <span class="badge badge-secondary"><i class="fas fa-users"></i> Bulk Promotion</span>
                                                @else
                                                <span class="badge badge-success"><i class="fas fa-chart-line"></i> Performance-Based</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="200">Total Students:</th>
                                            <td><strong>{{ $promotion->total_students }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Promoted:</th>
                                            <td><span class="badge badge-success badge-lg">{{ $promotion->promoted_count }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Repeating:</th>
                                            <td><span class="badge badge-warning badge-lg">{{ $promotion->repeating_count }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Promotion Rate:</th>
                                            <td><strong class="text-success">{{ number_format($promotion->promotion_rate, 1) }}%</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($promotion->status === 'completed')
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Completed</span>
                                                @elseif($promotion->status === 'rolled_back')
                                                <span class="badge badge-warning"><i class="fas fa-undo"></i> Rolled Back</span>
                                                @elseif($promotion->status === 'failed')
                                                <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Failed</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Processed By:</th>
                                            <td>{{ $promotion->processed_by_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Processed At:</th>
                                            <td>{{ \Carbon\Carbon::parse($promotion->processed_at)->format('F d, Y H:i A') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if($promotion->status === 'rolled_back')
                            <div class="alert alert-warning mt-3">
                                <h5 class="alert-heading"><i class="fas fa-undo"></i> Rollback Information</h5>
                                <p class="mb-0">
                                    <strong>Rolled back by:</strong> {{ $promotion->rolled_back_by_name }}<br>
                                    <strong>Rolled back at:</strong> {{ \Carbon\Carbon::parse($promotion->rolled_back_at)->format('F d, Y H:i A') }}<br>
                                    <strong>Reason:</strong> {{ $promotion->rollback_reason }}
                                </p>
                            </div>
                            @endif

                            @if($promotion->notes)
                            <div class="alert alert-info mt-3">
                                <strong><i class="fas fa-sticky-note"></i> Notes:</strong><br>
                                {{ $promotion->notes }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Class Capacity Overview -->
                    @if($capacities->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Destination Class Capacity Analysis</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Class Name</th>
                                            <th width="120" class="text-center">Initial Enrollment</th>
                                            <th width="120" class="text-center">Students Assigned</th>
                                            <th width="120" class="text-center">Max Capacity</th>
                                            <th width="120" class="text-center">Final Enrollment</th>
                                            <th width="120" class="text-center">Available Slots</th>
                                            <th width="120" class="text-center">Utilization</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($capacities as $capacity)
                                        <tr>
                                            <td><strong>{{ $capacity->class_name }}</strong></td>
                                            <td class="text-center">{{ $capacity->initial_enrollment }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">{{ $capacity->students_assigned }}</span>
                                            </td>
                                            <td class="text-center">{{ $capacity->max_capacity }}</td>
                                            <td class="text-center">
                                                <strong>{{ $capacity->final_enrollment }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $capacity->available_slots > 0 ? 'success' : 'danger' }}">
                                                    {{ $capacity->available_slots }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $utilization = $capacity->utilization_percentage;
                                                    $badgeClass = 'success';
                                                    if ($utilization >= 90) $badgeClass = 'danger';
                                                    elseif ($utilization >= 75) $badgeClass = 'warning';
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }}">
                                                    {{ number_format($utilization, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Student Records Tabs -->
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#promotedTab" role="tab">
                                        <i class="fas fa-arrow-up text-success"></i> Promoted Students
                                        <span class="badge badge-success">{{ $students->where('promotion_status', 'promoted')->count() }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#repeatingTab" role="tab">
                                        <i class="fas fa-redo text-warning"></i> Repeating Students
                                        <span class="badge badge-warning">{{ $students->where('promotion_status', 'repeating')->count() }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#allTab" role="tab">
                                        <i class="fas fa-list"></i> All Students
                                        <span class="badge badge-secondary">{{ $students->count() }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content">
                                <!-- Promoted Students Tab -->
                                <div class="tab-pane fade show active" id="promotedTab" role="tabpanel">
                                    @php
                                        $promotedStudents = $students->where('promotion_status', 'promoted');
                                        $groupedByDestination = $promotedStudents->groupBy('new_class_name');
                                    @endphp

                                    @foreach($groupedByDestination as $className => $classStudents)
                                    <div class="p-3 border-bottom bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-graduation-cap"></i> Promoted to: <strong>{{ $className }}</strong>
                                            <span class="badge badge-success ml-2">{{ $classStudents->count() }} students</span>
                                        </h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="50">#</th>
                                                    <th>Admission No</th>
                                                    <th>Student Name</th>
                                                    <th>Gender</th>
                                                    <th>Original Class</th>
                                                    @if($promotion->promotion_type === 'performance')
                                                    <th width="120" class="text-center">Average</th>
                                                    @endif
                                                    <th>Reason</th>
                                                    @if($promotion->status === 'rolled_back')
                                                    <th width="100" class="text-center">Rollback Status</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($classStudents as $index => $student)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $student->admission_no }}</td>
                                                    <td><strong>{{ $student->student_name }}</strong></td>
                                                    <td>
                                                        <span class="badge badge-{{ $student->gender === 'Male' ? 'primary' : 'pink' }}">
                                                            {{ $student->gender }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $student->original_class_name }}</td>
                                                    @if($promotion->promotion_type === 'performance')
                                                    <td class="text-center">
                                                        <strong class="text-success">{{ number_format($student->cumulative_average, 2) }}%</strong>
                                                    </td>
                                                    @endif
                                                    <td><small class="text-muted">{{ $student->promotion_reason }}</small></td>
                                                    @if($promotion->status === 'rolled_back')
                                                    <td class="text-center">
                                                        @if($student->is_rolled_back)
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-undo"></i> Restored
                                                        </span>
                                                        @else
                                                        <span class="badge badge-secondary">-</span>
                                                        @endif
                                                    </td>
                                                    @endif
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endforeach

                                    @if($promotedStudents->count() === 0)
                                    <div class="p-5 text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No promoted students found</p>
                                    </div>
                                    @endif
                                </div>

                                <!-- Repeating Students Tab -->
                                <div class="tab-pane fade" id="repeatingTab" role="tabpanel">
                                    @php
                                        $repeatingStudents = $students->where('promotion_status', 'repeating');
                                    @endphp

                                    @if($repeatingStudents->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="50">#</th>
                                                    <th>Admission No</th>
                                                    <th>Student Name</th>
                                                    <th>Gender</th>
                                                    <th>Current Class</th>
                                                    @if($promotion->promotion_type === 'performance')
                                                    <th width="120" class="text-center">Average</th>
                                                    @endif
                                                    <th>Reason</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($repeatingStudents as $index => $student)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $student->admission_no }}</td>
                                                    <td><strong>{{ $student->student_name }}</strong></td>
                                                    <td>
                                                        <span class="badge badge-{{ $student->gender === 'Male' ? 'primary' : 'pink' }}">
                                                            {{ $student->gender }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $student->original_class_name }}</td>
                                                    @if($promotion->promotion_type === 'performance')
                                                    <td class="text-center">
                                                        <strong class="text-warning">{{ number_format($student->cumulative_average, 2) }}%</strong>
                                                    </td>
                                                    @endif
                                                    <td><small class="text-muted">{{ $student->promotion_reason }}</small></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="p-5 text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No repeating students found</p>
                                    </div>
                                    @endif
                                </div>

                                <!-- All Students Tab -->
                                <div class="tab-pane fade" id="allTab" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="50">#</th>
                                                    <th>Admission No</th>
                                                    <th>Student Name</th>
                                                    <th>Gender</th>
                                                    <th>Original Class</th>
                                                    <th>New Class</th>
                                                    @if($promotion->promotion_type === 'performance')
                                                    <th width="120" class="text-center">Average</th>
                                                    @endif
                                                    <th width="100" class="text-center">Status</th>
                                                    <th>Reason</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($students as $index => $student)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $student->admission_no }}</td>
                                                    <td><strong>{{ $student->student_name }}</strong></td>
                                                    <td>
                                                        <span class="badge badge-{{ $student->gender === 'Male' ? 'primary' : 'pink' }}">
                                                            {{ $student->gender }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $student->original_class_name }}</td>
                                                    <td>
                                                        @if($student->new_class_name)
                                                        <strong class="text-success">{{ $student->new_class_name }}</strong>
                                                        @else
                                                        <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    @if($promotion->promotion_type === 'performance')
                                                    <td class="text-center">
                                                        <strong>{{ number_format($student->cumulative_average, 2) }}%</strong>
                                                    </td>
                                                    @endif
                                                    <td class="text-center">
                                                        @if($student->promotion_status === 'promoted')
                                                        <span class="badge badge-success">Promoted</span>
                                                        @else
                                                        <span class="badge badge-warning">Repeating</span>
                                                        @endif
                                                    </td>
                                                    <td><small class="text-muted">{{ $student->promotion_reason }}</small></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-3">
                        <a href="{{ route('students.promotion.history') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to History
                        </a>
                        
                        @if($promotion->status === 'completed')
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#rollbackModal">
                            <i class="fas fa-undo"></i> Rollback This Promotion
                        </button>

                        <!-- Rollback Modal -->
                        <div class="modal fade" id="rollbackModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form action="{{ route('students.promotion.rollback', $promotion->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-warning text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-exclamation-triangle"></i> Rollback Promotion
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-warning">
                                                <strong>Warning:</strong> This will restore {{ $promotion->promoted_count }} students back to their original classes.
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Reason for Rollback <span class="text-danger">*</span></label>
                                                <textarea name="rollback_reason" 
                                                          class="form-control" 
                                                          rows="3" 
                                                          placeholder="Enter reason for rolling back this promotion..."
                                                          required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-undo"></i> Confirm Rollback
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </div>
    @include('includes.edit_footer')

    <style>
        @media print {
            .main-sidebar, .navbar-bg, .section-header-breadcrumb, .btn, .modal { display: none !important; }
            .main-content { margin-left: 0 !important; padding-top: 0 !important; }
        }
    </style>
</body>