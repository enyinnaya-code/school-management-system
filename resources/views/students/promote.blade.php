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

                    <!-- Section Header with Breadcrumb -->

                    <!-- Quick Actions Card -->
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            <div class="section-header mb-3">
                                <h1><i class="fas fa-arrow-up"></i> Student Promotion</h1>
                                <div class="section-header-breadcrumb">
                                    <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                                    <div class="breadcrumb-item active">Student Promotion</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Create New Promotion Batch</h6>
                                    <small class="text-muted">Follow the steps below to promote students to the next
                                        class</small>
                                </div>
                                <a href="{{ route('students.promotion.history') }}" class="btn btn-info">
                                    <i class="fas fa-history"></i> View Promotion History
                                </a>
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <!-- Progress Indicator -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="promotion-steps">
                                <div class="step active" data-step="1">
                                    <div class="step-number">1</div>
                                    <div class="step-title">Select Classes</div>
                                </div>
                                <div class="step-line"></div>
                                <div class="step" data-step="2">
                                    <div class="step-number">2</div>
                                    <div class="step-title">Promotion Type</div>
                                </div>
                                <div class="step-line"></div>
                                <div class="step" data-step="3">
                                    <div class="step-number">3</div>
                                    <div class="step-title">Set Criteria & Capacity</div>
                                </div>
                                <div class="step-line"></div>
                                <div class="step" data-step="4">
                                    <div class="step-number">4</div>
                                    <div class="step-title">Preview & Confirm</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Select Multiple Classes -->
                    <div class="card step-card" id="step1Card">
                        <div class="card-header">
                            <h5><i class="fas fa-school"></i> Step 1: Select Current Classes</h5>
                        </div>
                        <div class="card-body">
                            <form id="selectClassForm">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Academic Session <span class="text-danger">*</span></label>
                                        <select class="form-control" id="session_id" name="session_id" required>
                                            <option value="">Select Session</option>
                                            @foreach($sessions as $session)
                                            <option value="{{ $session->id }}" {{ $session->is_current == 1 ? 'selected'
                                                : '' }}>
                                                {{ $session->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Section <span class="text-danger">*</span></label>
                                        <select class="form-control" id="section_id" name="section_id" required>
                                            <option value="">Select Section</option>
                                            @foreach($sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Select Current Classes <span class="text-danger">*</span></label>
                                    <div class="alert alert-info mb-2">
                                        <i class="fas fa-info-circle"></i> Select all classes from which students will
                                        be promoted.
                                        Example: Select JSS1A, JSS1B, JSS1C to promote all JSS1 students together.
                                    </div>

                                    <div id="classSelectionArea" style="display: none;">
                                        <div class="row" id="classCheckboxContainer">
                                            <!-- Classes will be loaded here -->
                                        </div>

                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-primary" id="selectAllClasses">
                                                <i class="fas fa-check-square"></i> Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                id="deselectAllClasses">
                                                <i class="fas fa-square"></i> Deselect All
                                            </button>
                                        </div>
                                    </div>

                                    <div id="noClassesMessage" class="alert alert-warning" style="display: none;">
                                        <i class="fas fa-exclamation-triangle"></i> No classes found for this section.
                                        Please select a different section.
                                    </div>
                                </div>

                                <div id="selectedClassesSummary" class="card bg-light mb-3" style="display: none;">
                                    <div class="card-body">
                                        <h6 class="mb-2"><i class="fas fa-list"></i> Selected Classes:</h6>
                                        <div id="selectedClassesList" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg" id="step1NextBtn" disabled>
                                        <i class="fas fa-arrow-right"></i> Next: Choose Promotion Type
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Step 2: Choose Promotion Type -->
                    <div class="card step-card" id="step2Card" style="display: none;">
                        <div class="card-header">
                            <h5><i class="fas fa-tasks"></i> Step 2: Choose Promotion Strategy</h5>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-md-5 mb-3">
                                    <div class="promotion-type-card" data-type="bulk">
                                        <div class="promotion-icon">
                                            <i class="fas fa-users fa-3x text-primary"></i>
                                        </div>
                                        <h5 class="mt-3">Promote All Students</h5>
                                        <p class="text-muted">Distribute all students across destination classes evenly
                                        </p>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" id="promotionTypeBulk"
                                                name="promotion_type" value="bulk">
                                            <label class="custom-control-label" for="promotionTypeBulk">
                                                Select this option
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5 mb-3">
                                    <div class="promotion-type-card" data-type="performance">
                                        <div class="promotion-icon">
                                            <i class="fas fa-chart-line fa-3x text-success"></i>
                                        </div>
                                        <h5 class="mt-3">Promote by Performance</h5>
                                        <p class="text-muted">Distribute students based on cumulative average with
                                            capacity limits</p>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input"
                                                id="promotionTypePerformance" name="promotion_type" value="performance">
                                            <label class="custom-control-label" for="promotionTypePerformance">
                                                Select this option
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary" onclick="goToStep(1)">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" id="step2NextBtn" disabled>
                                    <i class="fas fa-arrow-right"></i> Next: Configure Settings
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Configure Promotion Settings with Capacity -->
                    <div class="card step-card" id="step3Card" style="display: none;">
                        <div class="card-header">
                            <h5><i class="fas fa-cog"></i> Step 3: Configure Promotion Settings & Class Capacity</h5>
                        </div>
                        <div class="card-body">

                            <!-- Destination Classes Selection -->
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="fas fa-graduation-cap"></i> Select Destination Classes
                                    </h6>
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle"></i> Choose the classes where students will be
                                        promoted to and set the maximum capacity for each class.
                                    </div>

                                    <div id="destinationClassesArea">
                                        <div class="row" id="destinationClassCheckboxes">
                                            <!-- Destination classes will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Destination Classes with Capacity -->
                            <div id="destinationCapacitySettings" style="display: none;">
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-users-cog"></i> Set Class Capacity Limits</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Destination Class</th>
                                                        <th width="200">Maximum Capacity</th>
                                                        <th width="200">Current Students</th>
                                                        <th width="200">Available Slots</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="capacityTableBody">
                                                    <!-- Capacity settings will be populated here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bulk Promotion Settings -->
                            <div id="bulkSettings" style="display: none;">
                                <h6 class="mb-3">Bulk Promotion Distribution Strategy</h6>
                                <div class="form-group">
                                    <label>Distribution Method</label>
                                    <select class="form-control" id="bulk_distribution_method">
                                        <option value="balanced">Balanced - Distribute evenly across all classes
                                        </option>
                                        <option value="sequential">Sequential - Fill classes in order</option>
                                        <option value="random">Random - Randomly distribute students</option>
                                    </select>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Students will be distributed across selected
                                    destination classes based on the chosen method, respecting capacity limits.
                                </div>
                            </div>

                            <!-- Performance-Based Promotion Settings -->
                            <div id="performanceSettings" style="display: none;">
                                <h6 class="mb-3">Performance-Based Promotion Rules</h6>

                                <div class="alert alert-info">
                                    <i class="fas fa-lightbulb"></i> Define average score ranges. Students meeting
                                    criteria will be distributed across destination classes based on priority and
                                    capacity limits.
                                </div>

                                <div id="criteriaRulesContainer">
                                    <div class="card bg-light mb-2">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">Average Score Promotion Rules</h6>
                                                <button type="button" class="btn btn-sm btn-success"
                                                    onclick="addCriteriaRule()">
                                                    <i class="fas fa-plus"></i> Add Rule
                                                </button>
                                            </div>
                                            <small class="text-muted">Rules are applied in order. Students will be
                                                distributed across available destination classes.</small>
                                        </div>
                                    </div>

                                    <div id="rulesListContainer">
                                        <!-- Rules will be added here dynamically -->
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Distribution Strategy for Qualified Students</label>
                                    <select class="form-control" id="performance_distribution_method">
                                        <option value="balanced">Balanced - Distribute evenly</option>
                                        <option value="highest_first">Highest Scores First - Best students fill classes
                                            first</option>
                                        <option value="random">Random Distribution</option>
                                    </select>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Students Not Meeting Any Criteria</label>
                                    <select class="form-control" id="default_action">
                                        <option value="repeat">Repeat Current Class</option>
                                        <option value="promote_if_space">Promote if Space Available</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary" onclick="goToStep(2)">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" id="step3NextBtn">
                                    <i class="fas fa-arrow-right"></i> Next: Preview & Confirm
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Preview & Confirm -->
                    <div class="card step-card" id="step4Card" style="display: none;">
                        <div class="card-header">
                            <h5><i class="fas fa-check-circle"></i> Step 4: Preview & Confirm Promotion</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('students.promote.process') }}" method="POST" id="promotionForm">
                                @csrf
                                <input type="hidden" name="session_id" id="form_session_id">
                                <input type="hidden" name="term_id" id="form_term_id">
                                <input type="hidden" name="section_id" id="form_section_id">
                                <input type="hidden" name="current_class_ids" id="form_current_class_ids">
                                <input type="hidden" name="promotion_type" id="form_promotion_type">
                                <input type="hidden" name="promotion_config" id="form_promotion_config">

                                <!-- Summary Cards -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h3 class="mb-0" id="totalStudentsCount">0</h3>
                                                <small>Total Students</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h3 class="mb-0" id="promotedStudentsCount">0</h3>
                                                <small>To Be Promoted</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body">
                                                <h3 class="mb-0" id="repeatingStudentsCount">0</h3>
                                                <small>Repeating Class</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body">
                                                <h3 class="mb-0" id="classesAffectedCount">0</h3>
                                                <small>Destination Classes</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Class Capacity Overview -->
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Destination Class Capacity
                                            Overview</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="capacityOverview">
                                            <!-- Will be populated dynamically -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Promotion Criteria Display -->
                                <div class="card mb-3" id="promotionCriteriaCard" style="display: none;">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Promotion Criteria Applied
                                        </h6>
                                    </div>
                                    <div class="card-body" id="criteriaDisplayArea">
                                        <!-- Will be populated dynamically -->
                                    </div>
                                </div>

                                <!-- PROMOTED STUDENTS - Grouped by Destination Class -->
                                <div id="promotedStudentsSection">
                                    <h5 class="mb-3">
                                        <i class="fas fa-arrow-up text-success"></i> Students to be Promoted
                                        <span class="badge badge-success" id="promotedBadgeCount">0</span>
                                    </h5>

                                    <div id="promotedStudentsTables">
                                        <!-- Tables will be generated here grouped by destination class -->
                                    </div>
                                </div>

                                <!-- REPEATING STUDENTS -->
                                <div id="repeatingStudentsSection" style="display: none;">
                                    <h5 class="mb-3 mt-4">
                                        <i class="fas fa-redo text-warning"></i> Students Repeating Current Class
                                        <span class="badge badge-warning" id="repeatingBadgeCount">0</span>
                                    </h5>

                                    <div class="card">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="50">#</th>
                                                            <th>Admission No</th>
                                                            <th>Student Name</th>
                                                            <th>Current Class</th>
                                                            <th class="average-column">Average (%)</th>
                                                            <th>Reason</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="repeatingStudentsBody">
                                                        <!-- Will be populated by JavaScript -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning mt-4">
                                    <strong><i class="fas fa-exclamation-triangle"></i> Important Notice:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Only active students will be promoted</li>
                                        <li>Class capacity limits will be strictly enforced</li>
                                        <li>Students are distributed based on your selected strategy</li>
                                        <li>This action will update student records permanently</li>
                                        <li>Please review all assignments carefully before confirming</li>
                                    </ul>
                                </div>

                                <div class="form-group mt-4">
                                    <button type="button" class="btn btn-secondary" onclick="goToStep(3)">
                                        <i class="fas fa-arrow-left"></i> Back to Settings
                                    </button>
                                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                        <i class="fas fa-check-double"></i> Confirm & Process Promotion
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    @include('includes.edit_footer')

    <style>
        .promotion-steps {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
        }

        .step {
            text-align: center;
            flex: 0 0 auto;
            opacity: 0.5;
        }

        .step.active {
            opacity: 1;
        }

        .step.completed {
            opacity: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: bold;
            font-size: 16px;
        }

        .step.active .step-number {
            background: #007bff;
            color: white;
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .step.completed .step-number:after {
            content: "✓";
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: #e9ecef;
            margin: 0 10px;
            margin-top: -20px;
        }

        .step-title {
            font-size: 12px;
            font-weight: 500;
        }

        .promotion-type-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            height: 100%;
        }

        .promotion-type-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
            transform: translateY(-2px);
        }

        .promotion-type-card.selected {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .criteria-rule-card {
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }

        .rule-priority-badge {
            background: #007bff;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .class-checkbox-item {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .class-checkbox-item:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }

        .class-checkbox-item.selected {
            border-color: #28a745;
            background: #d4edda;
        }

        .class-checkbox-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .class-badge {
            display: inline-block;
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border-radius: 20px;
            margin: 5px;
            font-size: 14px;
        }

        .class-group-card {
            border-left: 4px solid #28a745;
            margin-bottom: 20px;
        }

        .class-group-header {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 2px solid #dee2e6;
        }

        .capacity-bar {
            height: 25px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .capacity-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }

        .capacity-fill.warning {
            background: linear-gradient(90deg, #ffc107, #ff9800);
        }

        .capacity-fill.danger {
            background: linear-gradient(90deg, #dc3545, #c82333);
        }
    </style>

    <script>
        // Global variables
let currentStep = 1;
let studentsData = [];
let allClasses = [];
let destinationClasses = [];
let selectedCurrentClasses = [];
let selectedDestinationClasses = [];
let classCapacities = {};
let promotionConfig = {};
let thirdTermId = null;

// Get third term ID for the selected session
function getThirdTermForSession(sessionId) {
    return $.ajax({
        url: `/get-third-term/${sessionId}`,
        method: 'GET'
    });
}

// Load classes when section is selected
$('#section_id').on('change', function() {
    const sectionId = $(this).val();
    
    if (sectionId) {
        $.ajax({
            url: `/get-classes/${sectionId}`,
            method: 'GET',
            success: function(response) {
                allClasses = response.classes || [];
                
                if (allClasses.length > 0) {
                    renderClassCheckboxes(allClasses);
                    $('#classSelectionArea').show();
                    $('#noClassesMessage').hide();
                } else {
                    $('#classSelectionArea').hide();
                    $('#noClassesMessage').show();
                }
                
                selectedCurrentClasses = [];
                updateSelectedClassesSummary();
                $('#step1NextBtn').prop('disabled', true);
            },
            error: function(xhr) {
                console.error('Error loading classes:', xhr.responseText);
                alert('Error loading classes');
            }
        });
    } else {
        $('#classSelectionArea').hide();
        $('#noClassesMessage').hide();
        selectedCurrentClasses = [];
        updateSelectedClassesSummary();
    }
});

// Render class checkboxes
function renderClassCheckboxes(classes) {
    let html = '';
    
    classes.forEach(cls => {
        html += `
            <div class="col-md-4 mb-3">
                <div class="class-checkbox-item" data-class-id="${cls.id}">
                    <label class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                        <input type="checkbox" class="class-checkbox" value="${cls.id}" data-class-name="${cls.name}">
                        <span class="ml-2"><strong>${cls.name}</strong></span>
                    </label>
                </div>
            </div>
        `;
    });
    
    $('#classCheckboxContainer').html(html);
    
    // Add event listeners
    $('.class-checkbox').on('change', function() {
        updateSelectedClasses();
    });
    
    $('.class-checkbox-item').on('click', function(e) {
        if (e.target.type !== 'checkbox') {
            const checkbox = $(this).find('.class-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });
}

// Update selected classes
function updateSelectedClasses() {
    selectedCurrentClasses = [];
    $('.class-checkbox:checked').each(function() {
        selectedCurrentClasses.push({
            id: $(this).val(),
            name: $(this).data('class-name')
        });
    });
    
    // Update UI
    $('.class-checkbox-item').removeClass('selected');
    $('.class-checkbox:checked').each(function() {
        $(this).closest('.class-checkbox-item').addClass('selected');
    });
    
    updateSelectedClassesSummary();
    $('#step1NextBtn').prop('disabled', selectedCurrentClasses.length === 0);
}

// Update selected classes summary
function updateSelectedClassesSummary() {
    if (selectedCurrentClasses.length > 0) {
        let html = '';
        selectedCurrentClasses.forEach(cls => {
            html += `<span class="class-badge">${cls.name}</span>`;
        });
        $('#selectedClassesList').html(html);
        $('#selectedClassesSummary').show();
    } else {
        $('#selectedClassesSummary').hide();
    }
}

// Select all classes
$('#selectAllClasses').on('click', function() {
    $('.class-checkbox').prop('checked', true).trigger('change');
});

// Deselect all classes
$('#deselectAllClasses').on('click', function() {
    $('.class-checkbox').prop('checked', false).trigger('change');
});

// Step 1: Load students from multiple classes
$('#selectClassForm').on('submit', function(e) {
    e.preventDefault();

    const sectionId = $('#section_id').val();
    const sessionId = $('#session_id').val();
    
    if (!sectionId || !sessionId || selectedCurrentClasses.length === 0) {
        alert('Please select session, section and at least one class');
        return;
    }

    showLoading('Loading students from selected classes...');
    
    // First get the third term for this session
    getThirdTermForSession(sessionId)
        .done(function(termResponse) {
            if (!termResponse.term_id) {
                hideLoading();
                alert('Third term not found for this session. Please ensure all three terms are created.');
                return;
            }
            
            thirdTermId = termResponse.term_id;
            
            // Load students from all selected classes
            const classIds = selectedCurrentClasses.map(c => c.id);
            
            $.ajax({
                url: '{{ route("students.promote.preview.multiple") }}',
                method: 'GET',
                data: {
                    session_id: sessionId,
                    term_id: thirdTermId,
                    section_id: sectionId,
                    class_ids: classIds
                },
                success: function(response) {
                    hideLoading();
                    
                    if (!response.students || response.students.length === 0) {
                        alert('No active students found in the selected classes');
                        return;
                    }
                    
                    studentsData = response.students;
                    destinationClasses = response.next_classes || [];
                    
                    console.log(`Loaded ${studentsData.length} students from ${classIds.length} classes`);
                    
                    goToStep(2);
                },
                error: function(xhr) {
                    hideLoading();
                    console.error('Error loading students:', xhr);
                    alert('Error loading students: ' + (xhr.responseJSON?.message || xhr.responseText));
                }
            });
        })
        .fail(function(xhr) {
            hideLoading();
            console.error('Error getting third term:', xhr);
            alert('Error loading third term for this session');
        });
});

// Promotion type selection
$('input[name="promotion_type"]').on('change', function() {
    const selectedType = $(this).val();
    
    $('.promotion-type-card').removeClass('selected');
    $(this).closest('.promotion-type-card').addClass('selected');
    
    $('#step2NextBtn').prop('disabled', false);
});

$('.promotion-type-card').on('click', function() {
    $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
});

// Step 2 next button
$('#step2NextBtn').on('click', function() {
    const promotionType = $('input[name="promotion_type"]:checked').val();
    
    if (!promotionType) {
        alert('Please select a promotion type');
        return;
    }
    
    // Load destination classes for selection
    renderDestinationClasses();
    
    $('#bulkSettings, #performanceSettings').hide();
    
    if (promotionType === 'bulk') {
        $('#bulkSettings').show();
    } else if (promotionType === 'performance') {
        $('#performanceSettings').show();
        initializeCriteriaRules();
    }
    
    goToStep(3);
});

// Render destination classes with checkboxes
function renderDestinationClasses() {
    let html = '';
    
    destinationClasses.forEach(cls => {
        html += `
            <div class="col-md-4 mb-3">
                <div class="class-checkbox-item" data-dest-class-id="${cls.id}">
                    <label class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                        <input type="checkbox" class="dest-class-checkbox" value="${cls.id}" data-class-name="${cls.name}">
                        <span class="ml-2"><strong>${cls.name}</strong></span>
                    </label>
                </div>
            </div>
        `;
    });
    
    $('#destinationClassCheckboxes').html(html);
    
    // Add event listeners
    $('.dest-class-checkbox').on('change', function() {
        updateDestinationClasses();
    });
    
    $('.class-checkbox-item[data-dest-class-id]').on('click', function(e) {
        if (e.target.type !== 'checkbox') {
            const checkbox = $(this).find('.dest-class-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });
}

// Update destination classes and show capacity settings
function updateDestinationClasses() {
    selectedDestinationClasses = [];
    $('.dest-class-checkbox:checked').each(function() {
        const classId = $(this).val();
        const className = $(this).data('class-name');
        selectedDestinationClasses.push({
            id: classId,
            name: className
        });
        
        // Initialize capacity if not set
        if (!classCapacities[classId]) {
            classCapacities[classId] = {
                max: 40,
                current: 0
            };
        }
    });
    
    // Update UI
    $('.class-checkbox-item[data-dest-class-id]').removeClass('selected');
    $('.dest-class-checkbox:checked').each(function() {
        $(this).closest('.class-checkbox-item').addClass('selected');
    });
    
    if (selectedDestinationClasses.length > 0) {
        renderCapacitySettings();
        $('#destinationCapacitySettings').show();
    } else {
        $('#destinationCapacitySettings').hide();
    }
}

// Render capacity settings table
function renderCapacitySettings() {
    let html = '';
    
    selectedDestinationClasses.forEach(cls => {
        const capacity = classCapacities[cls.id] || { max: 40, current: 0 };
        const available = capacity.max - capacity.current;
        
        html += `
            <tr>
                <td><strong>${cls.name}</strong></td>
                <td>
                    <input type="number" class="form-control capacity-input" 
                           data-class-id="${cls.id}" 
                           value="${capacity.max}" 
                           min="1" max="200">
                </td>
                <td><span class="badge badge-info">${capacity.current}</span></td>
                <td><span class="badge badge-success">${available}</span></td>
            </tr>
        `;
    });
    
    $('#capacityTableBody').html(html);
    
    // Add event listener for capacity changes
    $('.capacity-input').on('change', function() {
        const classId = $(this).data('class-id');
        const newMax = parseInt($(this).val()) || 40;
        
        if (!classCapacities[classId]) {
            classCapacities[classId] = { max: newMax, current: 0 };
        } else {
            classCapacities[classId].max = newMax;
        }
        
        updateCapacityDisplay();
    });
}

// Update capacity display
function updateCapacityDisplay() {
    selectedDestinationClasses.forEach(cls => {
        const capacity = classCapacities[cls.id] || { max: 40, current: 0 };
        const available = capacity.max - capacity.current;
        
        $(`tr:has(.capacity-input[data-class-id="${cls.id}"])`).find('.badge-success').text(available);
    });
}

// Initialize criteria rules
function initializeCriteriaRules() {
    if ($('#rulesListContainer .criteria-rule-card').length === 0) {
        addCriteriaRule();
    }
}

let ruleCounter = 0;
function addCriteriaRule() {
    ruleCounter++;
    const ruleId = `rule_${ruleCounter}`;
    const priority = $('#rulesListContainer .criteria-rule-card').length + 1;
    
    const ruleHtml = `
        <div class="card criteria-rule-card" id="${ruleId}">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="rule-priority-badge">Rule ${priority}</span>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Condition</label>
                        <select class="form-control form-control-sm rule-condition">
                            <option value="gte">Greater or Equal (≥)</option>
                            <option value="gt">Greater Than (>)</option>
                            <option value="lte">Less or Equal (≤)</option>
                            <option value="lt">Less Than (<)</option>
                            <option value="range">Between</option>
                        </select>
                    </div>
                    <div class="col-md-2 rule-value-container">
                        <label class="mb-1">Average Score (%)</label>
                        <input type="number" class="form-control form-control-sm rule-value" 
                               placeholder="e.g., 70" step="0.01" min="0" max="100">
                    </div>
                    <div class="col-md-2 rule-value2-container" style="display: none;">
                        <label class="mb-1">To</label>
                        <input type="number" class="form-control form-control-sm rule-value2" 
                               placeholder="e.g., 100" step="0.01" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1">Action</label>
                        <div class="text-muted small">Will distribute to selected destination classes</div>
                    </div>
                    <div class="col-auto">
                        <label class="mb-1">&nbsp;</label><br>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRule('${ruleId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#rulesListContainer').append(ruleHtml);
    
    $(`#${ruleId} .rule-condition`).on('change', function() {
        const isRange = $(this).val() === 'range';
        $(`#${ruleId} .rule-value2-container`).toggle(isRange);
    });
}

function removeRule(ruleId) {
    $(`#${ruleId}`).remove();
    updateRulePriorities();
}

function updateRulePriorities() {
    $('#rulesListContainer .criteria-rule-card').each(function(index) {
        $(this).find('.rule-priority-badge').text(`Rule ${index + 1}`);
    });
}

function validateCriteriaRules() {
    const rules = $('#rulesListContainer .criteria-rule-card');
    
    if (rules.length === 0) {
        alert('Please add at least one promotion rule');
        return false;
    }
    
    let isValid = true;
    rules.each(function() {
        const value = $(this).find('.rule-value').val();
        const condition = $(this).find('.rule-condition').val();
        
        if (!value) {
            isValid = false;
            $(this).addClass('border-danger');
        } else {
            $(this).removeClass('border-danger');
        }
        
        if (condition === 'range') {
            const value2 = $(this).find('.rule-value2').val();
            if (!value2) {
                isValid = false;
                $(this).addClass('border-danger');
            }
        }
    });
    
    if (!isValid) {
        alert('Please complete all promotion rules');
    }
    
    return isValid;
}

function getCriteriaRules() {
    const rules = [];
    
    $('#rulesListContainer .criteria-rule-card').each(function(index) {
        const condition = $(this).find('.rule-condition').val();
        const value = parseFloat($(this).find('.rule-value').val());
        const value2 = condition === 'range' ? parseFloat($(this).find('.rule-value2').val()) : null;
        
        rules.push({
            priority: index + 1,
            condition: condition,
            value: value,
            value2: value2
        });
    });
    
    return rules;
}

// Step 3 next button
$('#step3NextBtn').on('click', function() {
    if (selectedDestinationClasses.length === 0) {
        alert('Please select at least one destination class');
        return;
    }
    
    const promotionType = $('input[name="promotion_type"]:checked').val();
    
    if (promotionType === 'bulk') {
        const distributionMethod = $('#bulk_distribution_method').val();
        promotionConfig = {
            type: 'bulk',
            distribution_method: distributionMethod,
            destination_classes: selectedDestinationClasses,
            capacities: classCapacities
        };
    } else if (promotionType === 'performance') {
        if (!validateCriteriaRules()) {
            return;
        }
        
        const distributionMethod = $('#performance_distribution_method').val();
        const defaultAction = $('#default_action').val();
        
        promotionConfig = {
            type: 'performance',
            metric: 'cumulative_average',
            rules: getCriteriaRules(),
            distribution_method: distributionMethod,
            default_action: defaultAction,
            destination_classes: selectedDestinationClasses,
            capacities: classCapacities
        };
    }
    
    console.log('Promotion config:', promotionConfig);
    generatePreview();
    goToStep(4);
});

// Generate preview with capacity-aware distribution
function generatePreview() {
    showLoading('Generating preview with capacity distribution...');
    
    // Reset capacities current count
    Object.keys(classCapacities).forEach(classId => {
        classCapacities[classId].current = 0;
    });
    
    const processedStudents = applyPromotionLogicWithCapacity(studentsData, promotionConfig);
    
    updateSummaryCounts(processedStudents);
    displayPromotionCriteria(promotionConfig);
    displayCapacityOverview();
    generateSeparatePreviewTables(processedStudents);
    
    hideLoading();
}

// Apply promotion logic with capacity management
function applyPromotionLogicWithCapacity(students, config) {
    console.log(`Applying promotion logic to ${students.length} students`);
    
    let processedStudents = students.map(s => ({
        ...s,
        next_class_id: null,
        status: 'repeating',
        cumulative_average: parseFloat(s.cumulative_average) || 0
    }));
    
    if (config.type === 'bulk') {
        processedStudents = applyBulkPromotion(processedStudents, config);
    } else if (config.type === 'performance') {
        processedStudents = applyPerformancePromotion(processedStudents, config);
    }
    
    return processedStudents;
}

// Apply bulk promotion with distribution
function applyBulkPromotion(students, config) {
    const method = config.distribution_method;
    const destinationClasses = config.destination_classes;
    
    // Sort or shuffle based on method
    if (method === 'random') {
        students = shuffleArray(students);
    } else if (method === 'balanced') {
        // Keep original order for balanced distribution
    }
    
    let classIndex = 0;
    
    students.forEach(student => {
        if (method === 'sequential') {
            // Fill classes sequentially
            let assigned = false;
            for (let i = 0; i < destinationClasses.length && !assigned; i++) {
                const destClass = destinationClasses[i];
                const capacity = config.capacities[destClass.id];
                
                if (capacity && capacity.current < capacity.max) {
                    student.next_class_id = destClass.id;
                    student.status = 'promoted';
                    capacity.current++;
                    assigned = true;
                }
            }
        } else {
            // Balanced or random - distribute evenly
            let attempts = 0;
            let assigned = false;
            
            while (attempts < destinationClasses.length && !assigned) {
                const destClass = destinationClasses[classIndex % destinationClasses.length];
                const capacity = config.capacities[destClass.id];
                
                if (capacity && capacity.current < capacity.max) {
                    student.next_class_id = destClass.id;
                    student.status = 'promoted';
                    capacity.current++;
                    assigned = true;
                }
                
                classIndex++;
                attempts++;
            }
        }
    });
    
    return students;
}

// Apply performance-based promotion with capacity
function applyPerformancePromotion(students, config) {
    const method = config.distribution_method;
    const rules = config.rules;
    const destinationClasses = config.destination_classes;
    
    // Sort students by average if method is highest_first
    if (method === 'highest_first') {
        students.sort((a, b) => b.cumulative_average - a.cumulative_average);
    } else if (method === 'random') {
        students = shuffleArray(students);
    }
    
    let classIndex = 0;
    
    students.forEach(student => {
        const average = student.cumulative_average;
        let qualifies = false;
        
        // Check if student meets any rule
        for (let rule of rules) {
            if (matchesRule(average, rule)) {
                qualifies = true;
                break;
            }
        }
        
        if (qualifies || config.default_action === 'promote_if_space') {
            // Try to assign to a destination class
            let attempts = 0;
            let assigned = false;
            
            while (attempts < destinationClasses.length && !assigned) {
                const destClass = destinationClasses[classIndex % destinationClasses.length];
                const capacity = config.capacities[destClass.id];
                
                if (capacity && capacity.current < capacity.max) {
                    student.next_class_id = destClass.id;
                    student.status = 'promoted';
                    capacity.current++;
                    assigned = true;
                }
                
                classIndex++;
                attempts++;
            }
        }
    });
    
    return students;
}

function matchesRule(value, rule) {
    const numValue = parseFloat(value);
    const ruleValue = parseFloat(rule.value);
    const ruleValue2 = rule.value2 ? parseFloat(rule.value2) : null;
    
    switch(rule.condition) {
        case 'gte':
            return numValue >= ruleValue;
        case 'gt':
            return numValue > ruleValue;
        case 'lte':
            return numValue <= ruleValue;
        case 'lt':
            return numValue < ruleValue;
        case 'range':
            return numValue >= ruleValue && numValue <= ruleValue2;
        default:
            return false;
    }
}

// Shuffle array utility
function shuffleArray(array) {
    const newArray = [...array];
    for (let i = newArray.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
    }
    return newArray;
}

// Display capacity overview
function displayCapacityOverview() {
    let html = '<div class="row">';
    
    selectedDestinationClasses.forEach(cls => {
        const capacity = classCapacities[cls.id];
        const percentage = (capacity.current / capacity.max) * 100;
        let fillClass = '';
        
        if (percentage >= 90) {
            fillClass = 'danger';
        } else if (percentage >= 75) {
            fillClass = 'warning';
        }
        
        html += `
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6>${cls.name}</h6>
                        <div class="capacity-bar">
                            <div class="capacity-fill ${fillClass}" style="width: ${percentage}%">
                                ${capacity.current} / ${capacity.max}
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            ${capacity.max - capacity.current} slots available
                        </small>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    $('#capacityOverview').html(html);
}

// Display promotion criteria
function displayPromotionCriteria(config) {
    $('#promotionCriteriaCard').show();
    
    let html = '';
    
    if (config.type === 'bulk') {
        html = `
            <div class="alert alert-info mb-0">
                <strong>Promotion Type:</strong> Bulk Promotion<br>
                <strong>Distribution Method:</strong> ${config.distribution_method}<br>
                <strong>Destination Classes:</strong> ${config.destination_classes.map(c => c.name).join(', ')}
            </div>
        `;
    } else if (config.type === 'performance') {
        html = `
            <div class="mb-3">
                <strong>Promotion Type:</strong> Performance-Based<br>
                <strong>Distribution Method:</strong> ${config.distribution_method}<br>
                <strong>Destination Classes:</strong> ${config.destination_classes.map(c => c.name).join(', ')}
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Priority</th>
                            <th>Condition</th>
                            <th>Average Score</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        config.rules.forEach((rule, index) => {
            let conditionText = '';
            let scoreText = '';
            
            switch(rule.condition) {
                case 'gte': conditionText = 'Greater or Equal (≥)'; scoreText = `${rule.value}%`; break;
                case 'gt': conditionText = 'Greater Than (>)'; scoreText = `${rule.value}%`; break;
                case 'lte': conditionText = 'Less or Equal (≤)'; scoreText = `${rule.value}%`; break;
                case 'lt': conditionText = 'Less Than (<)'; scoreText = `${rule.value}%`; break;
                case 'range': conditionText = 'Between'; scoreText = `${rule.value}% - ${rule.value2}%`; break;
            }
            
            html += `
                <tr>
                    <td><span class="badge badge-primary">Rule ${index + 1}</span></td>
                    <td>${conditionText}</td>
                    <td><strong>${scoreText}</strong></td>
                    <td>Promote to destination classes</td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
            <div class="alert alert-warning mt-3 mb-0">
                <strong>Default Action:</strong> ${config.default_action === 'repeat' ? 'Repeat current class' : 'Promote if space available'}
            </div>
        `;
    }
    
    $('#criteriaDisplayArea').html(html);
}

// Generate separate preview tables
function generateSeparatePreviewTables(students) {
    const promotionType = $('input[name="promotion_type"]:checked').val();
    const showAverage = promotionType === 'performance';
    
    $('.average-column').toggle(showAverage);
    
    const promotedStudents = students.filter(s => s.status === 'promoted');
    const repeatingStudents = students.filter(s => s.status === 'repeating');
    
    $('#promotedBadgeCount').text(promotedStudents.length);
    $('#repeatingBadgeCount').text(repeatingStudents.length);
    
    generatePromotedStudentsTables(promotedStudents, showAverage);
    generateRepeatingStudentsTable(repeatingStudents, showAverage);
}

// Generate promoted students tables grouped by destination class
function generatePromotedStudentsTables(promotedStudents, showAverage) {
    if (promotedStudents.length === 0) {
        $('#promotedStudentsSection').hide();
        return;
    }
    
    $('#promotedStudentsSection').show();
    
    // Group by destination class
    const groupedByClass = {};
    promotedStudents.forEach(student => {
        const classId = student.next_class_id;
        if (!groupedByClass[classId]) {
            groupedByClass[classId] = [];
        }
        groupedByClass[classId].push(student);
    });
    
    let tablesHtml = '';
    let globalIndex = 0;
    
    Object.keys(groupedByClass).forEach(classId => {
        const studentsInClass = groupedByClass[classId];
        const destClass = selectedDestinationClasses.find(c => c.id == classId);
        const className = destClass ? destClass.name : 'Unknown Class';
        const capacity = classCapacities[classId];
        
        tablesHtml += `
            <div class="card class-group-card mb-3">
                <div class="class-group-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-graduation-cap"></i> Promoting to: ${className}
                            <span class="badge badge-success ml-2">${studentsInClass.length} student(s)</span>
                        </h6>
                        <span class="text-muted">Capacity: ${studentsInClass.length} / ${capacity.max}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Admission No</th>
                                    <th>Student Name</th>
                                    <th>Current Class</th>
                                    ${showAverage ? '<th class="average-column">Average (%)</th>' : ''}
                                    <th width="100">Status</th>
                                </tr>
                            </thead>
                            <tbody>
        `;
        
        studentsInClass.forEach(student => {
            globalIndex++;
            const admissionNo = student.admission_number || student.admission_no || 'N/A';
            const studentName = student.name || `${student.first_name || ''} ${student.last_name || ''}`.trim();
            const currentClass = student.school_class?.name || student.class?.name || 'N/A';
            
            const averageHtml = showAverage 
                ? `<td><strong>${student.cumulative_average ? student.cumulative_average.toFixed(2) + '%' : '0.00%'}</strong></td>` 
                : '';
            
            tablesHtml += `
                <tr>
                    <td>${globalIndex}</td>
                    <td>${admissionNo}</td>
                    <td>${studentName}</td>
                    <td>${currentClass}</td>
                    ${averageHtml}
                    <td><span class="badge badge-success">Promoted</span></td>
                    <input type="hidden" name="students[${globalIndex-1}][student_id]" value="${student.id}">
                    <input type="hidden" name="students[${globalIndex-1}][next_class_id]" value="${student.next_class_id}">
                </tr>
            `;
        });
        
        tablesHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#promotedStudentsTables').html(tablesHtml);
}

// Generate repeating students table
function generateRepeatingStudentsTable(repeatingStudents, showAverage) {
    if (repeatingStudents.length === 0) {
        $('#repeatingStudentsSection').hide();
        return;
    }
    
    $('#repeatingStudentsSection').show();
    
    let tableHtml = '';
    const startIndex = studentsData.filter(s => s.status === 'promoted').length;
    
    repeatingStudents.forEach((student, index) => {
        const admissionNo = student.admission_number || student.admission_no || 'N/A';
        const studentName = student.name || `${student.first_name || ''} ${student.last_name || ''}`.trim();
        const currentClass = student.school_class?.name || student.class?.name || 'N/A';
        
        const averageHtml = showAverage 
            ? `<td><strong>${student.cumulative_average ? student.cumulative_average.toFixed(2) + '%' : '0.00%'}</strong></td>` 
            : '';
        
        const reason = showAverage ? 'Did not meet criteria or no available slots' : 'No available slots';
        
        tableHtml += `
            <tr>
                <td>${index + 1}</td>
                <td>${admissionNo}</td>
                <td>${studentName}</td>
                <td>${currentClass}</td>
                ${averageHtml}
                <td><span class="text-muted">${reason}</span></td>
                <input type="hidden" name="students[${startIndex + index}][student_id]" value="${student.id}">
                <input type="hidden" name="students[${startIndex + index}][next_class_id]" value="">
            </tr>
        `;
    });
    
    $('#repeatingStudentsBody').html(tableHtml);
}

// Update summary counts
function updateSummaryCounts(students) {
    const total = students.length;
    const promoted = students.filter(s => s.status === 'promoted').length;
    const repeating = total - promoted;
    const uniqueClasses = new Set(students.filter(s => s.next_class_id).map(s => s.next_class_id)).size;
    
    $('#totalStudentsCount').text(total);
    $('#promotedStudentsCount').text(promoted);
    $('#repeatingStudentsCount').text(repeating);
    $('#classesAffectedCount').text(uniqueClasses);
}

// Form submission
$('#promotionForm').on('submit', function(e) {
    if (!confirm('Are you sure you want to promote these students? This action will update student records permanently.')) {
        e.preventDefault();
        return false;
    }
    
    $('#form_session_id').val($('#session_id').val());
    $('#form_term_id').val(thirdTermId);
    $('#form_section_id').val($('#section_id').val());
    $('#form_current_class_ids').val(JSON.stringify(selectedCurrentClasses.map(c => c.id)));
    $('#form_promotion_type').val($('input[name="promotion_type"]:checked').val());
    $('#form_promotion_config').val(JSON.stringify(promotionConfig));
    
    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
});

// Navigation functions
function goToStep(step) {
    $('.step-card').hide();
    
    $('.step').removeClass('active completed');
    for (let i = 1; i < step; i++) {
        $(`.step[data-step="${i}"]`).addClass('completed');
    }
    $(`.step[data-step="${step}"]`).addClass('active');
    
    $(`#step${step}Card`).show();
    
    currentStep = step;
    
    $('html, body').animate({ scrollTop: 0 }, 300);
}

function showLoading(message) {
    $('body').append(`
        <div class="loading-overlay">
            <div class="text-center text-white">
                <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                <h5>${message}</h5>
            </div>
        </div>
    `);
}

function hideLoading() {
    $('.loading-overlay').remove();
}

$('<style>')
    .text(`
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
    `)
    .appendTo('head');
    </script>
</body>