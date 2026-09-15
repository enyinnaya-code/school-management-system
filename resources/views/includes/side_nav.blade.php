<div class="main-sidebar sidebar-style-2 mb-5" style="padding-bottom: 7rem;">
  <aside id="sidebar-wrapper">
    <div class="sidebar-brand">
      <a href="{{ route('dynamic.dashboard') }}">
        @if(school_logo())
        <img alt="{{ school_name() }}" src="{{ school_logo() }}" class="header-logo"
          style="max-height: 50px; object-fit: contain;" />
        @endif
      </a>
    </div>
    <ul class="sidebar-menu">

      <!-- DASHBOARD - All Users -->
      <li class="dropdown @if(request()->routeIs(['dynamic.dashboard', 'dashboard'])) active @endif">
        <a href="{{ route('dynamic.dashboard') }}" class="nav-link">
          <i data-feather="monitor"></i><span>Dashboard</span>
        </a>
      </li>

      <!-- ========== SUPER ADMIN & ADMIN (Type 1, 2, 7, 8, 9, 11) ========== -->
      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 7 || auth()->user()->user_type == 8 ||
      auth()->user()->user_type == 9 || auth()->user()->user_type == 11)
      <li class="menu-header">SCHOOL MANAGEMENT</li>

      <!-- School Settings -->
      <li class="dropdown @if(request()->routeIs(['settings.*', 'schoolClass.*', 'section.*', 'course.*', 'result_sheets.*', 'results.settings.primaryResultClass', 'results.settings.savePrimaryResultClass', 'results.settings.customSubjectNo', 'results.settings.saveCustomSubjectNo', 'results.settings.getClassesBySection'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="settings"></i><span>School Setup</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('settings.school') }}">General Settings</a></li>
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{ route('schoolClass.add') }}">Add Classes</a></li>
          <li><a class="nav-link" href="{{ route('schoolClass.manage') }}">Manage Classes</a></li>
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{ route('section.create') }}">Add Sections/Arms</a></li>
          <li><a class="nav-link" href="{{ route('section.index') }}">Manage Sections/Arms</a></li>
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{ route('course.create') }}">Create Subjects</a></li>
          <li><a class="nav-link" href="{{ route('course.manage') }}">Manage Subjects</a></li>
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{ route('result_sheets.create') }}">Custom Result Sheets</a></li>
          <li><a class="nav-link" href="{{ route('result_sheets.index') }}">Manage Result Sheets</a></li>
          <li class="divider mx-4"></li>
          <li>
            <a class="nav-link" href="{{ route('results.settings.primaryResultClass') }}">
              Set Pri. Result Class
            </a>
          </li>
          <li class="divider mx-4"></li>
          <li>
            <a class="nav-link" href="{{ route('results.settings.customSubjectNo') }}">
              Set Custom Subj. No
            </a>
          </li>
        </ul>
      </li>

      <!-- Academic Sessions -->
      <li class="dropdown @if(request()->routeIs('sessions.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="calendar"></i><span>Academic Sessions</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('sessions.create') }}">Set Current Session</a></li>
          <li><a class="nav-link" href="{{ route('sessions.index') }}">Manage Sessions</a></li>
        </ul>
      </li>

      <!-- Timetable -->
      <li class="dropdown @if(request()->routeIs('timetables.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="clock"></i><span>Timetable</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('timetables.create') }}">Create Timetable</a></li>
          <li><a class="nav-link" href="{{ route('timetables.index') }}">Manage Timetables</a></li>
        </ul>
      </li>

      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 7 || auth()->user()->user_type == 8 ||
      auth()->user()->user_type == 11)
      <li class="menu-header">USER MANAGEMENT</li>

      <!-- Students -->
      <li class="dropdown @if(request()->routeIs(['students.create', 'students.index', 'students.store', 'students.edit', 'students.update', 'students.destroy', 'students.profile', 'students.performance', 'students.suspend', 'students.activate', 'students.reset_password', 'students.export.pdf', 'students.search', 'students.reactivate_all', 'students.reset_all_passwords'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="users"></i><span>Students</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('students.create') }}">Add Student</a></li>
          <li><a class="nav-link" href="{{ route('students.index') }}">Manage Students</a></li>
        </ul>
      </li>

      <!-- Parents -->
      <li class="dropdown @if(request()->routeIs(['parent.add', 'parents.index', 'parent.destroy', 'parent.edit', 'parent.store', 'parent.update'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="user-check"></i><span>Parents</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('parent.add') }}">Add Parent</a></li>
          <li><a class="nav-link" href="{{ route('parents.index') }}">Manage Parents</a></li>
        </ul>
      </li>

      <!-- Staff & Administration -->
      <li class="dropdown @if(request()->routeIs(['teacher.add', 'teacher.store', 'teachers.index', 'teachers.edit', 'teachers.update', 'teachers.destroy', 'teachers.reset', 'teachers.toggleActive', 'user.add', 'user.store', 'users.index', 'users.edit', 'users.update', 'users.destroy', 'users.reset', 'users.toggleActive'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="briefcase"></i><span>Staff & Teachers</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('teacher.add') }}">Add Staff</a></li>
          <li><a class="nav-link" href="{{ route('teachers.index') }}">Manage Staff</a></li>
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{ route('user.add') }}">Add Admin</a></li>
          <li><a class="nav-link" href="{{ route('users.index') }}">Manage Admins</a></li>
        </ul>
      </li>

      @endif
      {{-- END: USER MANAGEMENT (types 1,2,7,8,11) --}}

      @endif
      {{-- END: SCHOOL MANAGEMENT (types 1,2,7,8,9,11) --}}

      <!-- ========== ATTENDANCE - Admins & Form Teachers ========== -->
      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 7 || auth()->user()->user_type == 8 ||
      auth()->user()->user_type == 11 ||
      (auth()->user()->user_type == 3 && auth()->user()->is_form_teacher))
      <li class="menu-header">ATTENDANCE</li>

      <!-- Staff Attendance - Admins only -->
      @if(in_array(auth()->user()->user_type, [1, 2, 7, 8, 11]))
      <li class="dropdown @if(request()->routeIs('attendance.teachers.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="clipboard"></i><span>Staff Attendance</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('attendance.teachers.mark') }}">Mark Attendance</a></li>
          <li><a class="nav-link" href="{{ route('attendance.teachers.index') }}">View Attendance</a></li>
        </ul>
      </li>
      @endif

      <!-- Student Attendance -->
      <li class="dropdown @if(request()->routeIs('attendance.students.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="check-square"></i><span>Student Attendance</span>
        </a>
        <ul class="dropdown-menu">
          @if(in_array(auth()->user()->user_type, [1, 2, 7, 8, 11]) || (auth()->user()->user_type == 3 &&
          auth()->user()->is_form_teacher))
          <li><a class="nav-link" href="{{ route('attendance.students.mark') }}">Mark Attendance</a></li>
          @endif
          <li><a class="nav-link" href="{{ route('attendance.students.index') }}">View Attendance</a></li>
        </ul>
      </li>

      @endif
      {{-- END: ATTENDANCE --}}

      <!-- ========== FINANCE (Types 1, 2, 6) ========== -->
      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 6)
      <li class="menu-header">FINANCE</li>

      <!-- Fee Management -->
      <li class="dropdown @if(request()->routeIs(['payment.*', 'fee.prospectus.*'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="credit-card"></i><span>Fee Management</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('payment.create') }}">New Payment</a></li>
          <li><a class="nav-link" href="{{ route('payment.manage') }}">Manage Payments</a></li>
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{ route('fee.prospectus.create') }}">Create Fee Prospectus</a></li>
          <li><a class="nav-link" href="{{ route('fee.prospectus.manage') }}">Manage Fee Prospectus</a></li>
        </ul>
      </li>

      <!-- Miscellaneous Fees -->
      <li class="dropdown @if(request()->routeIs('misc.fee.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="file-text"></i><span>Miscellaneous Fees</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('misc.fee.create') }}">Create Misc Fee Type</a></li>
          <li><a class="nav-link" href="{{ route('misc.fee.manage') }}">Manage Misc Fee Types</a></li>
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{ route('misc.fee.payments.create') }}">Record Misc Fee Payment</a></li>
          <li><a class="nav-link" href="{{ route('misc.fee.payments.manage') }}">Manage Misc Fee Payments</a></li>
        </ul>
      </li>

      <!-- Other Expenses -->
      <li class="dropdown @if(request()->routeIs('other.expense.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="trending-down"></i><span>Other Expenses</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('other.expense.create') }}">Create Other Expense</a></li>
          <li><a class="nav-link" href="{{ route('other.expense.manage') }}">Manage Other Expenses</a></li>
        </ul>
      </li>

      <!-- Financial Reports -->
      <li class="dropdown @if(request()->routeIs(['finance.overview', 'finance.analysis', 'finance.analysis.export'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="bar-chart-2"></i><span>Financial Reports</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('finance.overview') }}">Income & Expenses</a></li>
          <li><a class="nav-link" href="{{ route('finance.analysis') }}">Financial Analysis</a></li>
        </ul>
      </li>

      @endif
      {{-- END: FINANCE --}}

      <!-- ========== COMMUNICATION - All Users ========== -->
      <li class="menu-header">COMMUNICATION</li>

      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 7 || auth()->user()->user_type == 8 ||
      auth()->user()->user_type == 11)
      <li class="dropdown @if(request()->routeIs('announcements.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="message-circle"></i><span>Announcements</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('announcements.create') }}">Send Announcement</a></li>
          <li><a class="nav-link" href="{{ route('announcements.index') }}">All Announcements</a></li>
        </ul>
      </li>
      @endif

      <li class="dropdown @if(request()->routeIs('events.calendar')) active @endif">
        <a href="{{ route('events.calendar') }}" class="nav-link">
          <i data-feather="calendar"></i><span>School Calendar</span>
        </a>
      </li>

      <!-- ========== MY TEACHING - Admin, Teachers, Counsellors (Type 1,2,3,7,8,9,10,11) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 3, 7, 8, 9, 10, 11]))
      <li class="menu-header">MY TEACHING</li>

      <li class="dropdown @if(request()->routeIs('teachers.my_students')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="user"></i><span>Student Management</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('teachers.my_students') }}">View My Students</a></li>
        </ul>
      </li>

      <li class="dropdown @if(request()->routeIs('teachers.timetable')) active @endif">
        <a href="{{ route('teachers.timetable') }}" class="nav-link">
          <i data-feather="clock"></i><span>My Teaching Schedule</span>
        </a>
      </li>

      @if(in_array(Auth::user()->user_type, [1, 2]))
      <li class="dropdown @if(request()->routeIs('admin.teaching-schedules')) active @endif">
        <a href="{{ route('admin.teaching-schedules') }}" class="nav-link">
          <i data-feather="users"></i><span>All Teachers' Schedules</span>
        </a>
      </li>
      @endif

      <!-- ========== ACADEMICS & ASSESSMENT ========== -->
      <li class="menu-header">ACADEMICS & ASSESSMENT</li>

      <!-- Exam Questions -->
      <li class="dropdown @if(request()->routeIs('exam_questions.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="edit"></i><span>Exam Questions</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('exam_questions.create') }}">Create Exam</a></li>
          <li><a class="nav-link" href="{{ route('exam_questions.index') }}">Manage Exams</a></li>
          @if(in_array(Auth::user()->user_type, [1, 2]))
          <li><a class="nav-link" href="{{ route('exam_questions.all_exams') }}">View All Exams</a></li>
          @endif
        </ul>
      </li>

      <!-- Results -->
      <li class="dropdown @if(request()->routeIs([
          'results.upload', 'results.uploadResult', 'results.saveResult',
          'results.print', 'results.selectClass', 'results.selectClass.get', 'results.selectClassForPrint',
          'results.printStudent', 'results.printStudentSheet', 'results.masterList', 'results.exportMasterList',
          'results.class.view', 'results.class.export', 'results.cumulative', 'results.transcript',
          'results.remarks.edit', 'results.remarks.update', 'results.savePrimaryStudent', 'results.getTermsBySession', 'results.index',
          'students.promote', 'students.promote.preview', 'students.promote.preview.multiple',
          'students.promote.process', 'students.promote.process.enhanced',
          'students.promotion.history', 'students.promotion.details', 'students.promotion.rollback',
          'results.settings.index', 'results.settings.bulkBlock', 'results.settings.bulkUnblock',
          'results.settings.saveTermSettings', 'results.settings.toggleBlock',
          'student.result.upload', 'student.results.upload', 'student.results.save', 'student.result_sheet.save',
      ])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="file-text"></i><span>Results</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('results.upload') }}">Upload Results</a></li>
          @if(in_array(Auth::user()->user_type, [1, 2, 7, 8, 11]) || (Auth::user()->user_type == 3 &&
          Auth::user()->is_form_teacher))
          <li><a class="nav-link" href="{{ route('results.print') }}">Print Results</a></li>
          <li><a class="nav-link" href="{{ route('students.promote') }}">Promote Students</a></li>
          <li><a class="nav-link" href="{{ route('students.promotion.history') }}">Promotion History</a></li>
          @endif
          @if(in_array(Auth::user()->user_type, [1, 2]))
          <li><a class="nav-link" href="{{ route('results.settings.index') }}">Result Access Settings</a></li>
          @endif
        </ul>
      </li>

      @endif
      {{-- END: MY TEACHING (types 1,2,3,7,8,9,10,11) --}}

      <!-- ========== E-LEARNING (Types 1,2,3,4,7,8,9,10,11) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 3, 4, 7, 8, 9, 10, 11]))
      <li class="menu-header">E-LEARNING</li>

      <li class="dropdown @if(request()->routeIs('eclass.*')) active @endif">
        <a href="{{ route('eclass.index') }}" class="nav-link">
          <i data-feather="video"></i><span>E-Classroom</span>
        </a>
      </li>
      @endif
      {{-- END: E-LEARNING --}}

      <!-- ========== COMPUTER-BASED TESTING ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 3, 4, 7, 8, 9, 10, 11]))
      <li class="menu-header">COMPUTER-BASED TESTING</li>

      @if(in_array(Auth::user()->user_type, [1, 2, 7, 8, 9, 11]))
      <!-- CBT Dashboard - Admin Only -->
      <li class="dropdown @if(request()->routeIs(['dynamic.dashboard', 'dashboard'])) active @endif">
        <a href="{{ route('dashboard') }}" class="nav-link">
          <i data-feather="monitor"></i><span>CBT Dashboard</span>
        </a>
      </li>

      <!-- Tests Oversight - Admin Only -->
      <li class="dropdown @if(request()->routeIs(['tests.view', 'tests.schedule', 'tests.approve', 'tests.check', 'tests.delete', 'tests.comment', 'tests.saveSchedule', 'tests.cancelSchedule'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="check-square"></i><span>Tests Oversight</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('tests.view') }}">Approve Tests</a></li>
          <li><a class="nav-link" href="{{ route('tests.schedule') }}">Schedule Tests</a></li>
        </ul>
      </li>
      @endif

      @if(in_array(Auth::user()->user_type, [1, 2, 3, 7, 8, 9, 10, 11]))
      <!-- Create Tests - Admin & Teachers -->
      <li class="dropdown @if(request()->routeIs(['tests.create', 'tests.index', 'tests.edit', 'tests.update', 'tests.destroy', 'tests.show'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="file-plus"></i><span>Create CBT Test</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('tests.create') }}">Create Test</a></li>
          <li><a class="nav-link" href="{{ route('tests.index') }}">Manage Tests</a></li>
        </ul>
      </li>

      <!-- Questions Bank - Admin & Teachers -->
      <li class="dropdown @if(request()->routeIs(['questions.index', 'questions.store', 'questions.set', 'questions.view', 'tests.setQuestions', 'tests.submitForApproval'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="help-circle"></i><span>CBT Questions</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('questions.index') }}">Manage Questions</a></li>
        </ul>
      </li>
      @endif

      @if(in_array(Auth::user()->user_type, [1, 2, 4]))
      <!-- Take Tests - Admin & Students -->
      <li class="dropdown @if(request()->routeIs(['tests.available', 'tests.start', 'tests.past', 'tests.viewPast', 'tests.submit', 'tests.saveAnswer', 'tests.student', 'tests.take', 'tests.forceStop'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="play-circle"></i><span>Take Tests</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('tests.available') }}">Available Tests</a></li>
          <li><a class="nav-link" href="{{ route('tests.start') }}">Start Test</a></li>
          <li><a class="nav-link" href="{{ route('tests.past') }}">Previous Tests</a></li>
        </ul>
      </li>
      @endif

      @endif
      {{-- END: CBT --}}

      <!-- ========== LIBRARY (Types 1,2,3,4,7,8,9,10,11 + Librarians) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 3, 4, 7, 8, 9, 10, 11]) || (Auth::user()->user_type == 3 &&
      Auth::user()->is_librarian == 1))
      <li class="menu-header">LIBRARY</li>

      <!-- Physical Library -->
      <li class="dropdown @if(request()->routeIs('physical_library.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="book"></i><span>Physical Library</span>
        </a>
        <ul class="dropdown-menu">
          @if(in_array(Auth::user()->user_type, [1, 2]) || (Auth::user()->user_type == 3 && Auth::user()->is_librarian
          == 1))
          <li><a class="nav-link" href="{{ route('physical_library.add_book') }}">Add Books</a></li>
          <li><a class="nav-link" href="{{ route('physical_library.manage_books') }}">Manage Books</a></li>
          <li><a class="nav-link" href="{{ route('physical_library.borrowing_returns') }}">Borrowing & Returns</a></li>
          @if(in_array(Auth::user()->user_type, [1, 2]))
          <li><a class="nav-link" href="{{ route('physical_library.assign_librarian') }}">Assign Librarians</a></li>
          @endif
          <li class="divider mx-4"></li>
          @endif

          <li><a class="nav-link" href="{{ route('physical_library.request_borrow') }}">Borrow a Book</a></li>

          @if(!in_array(Auth::user()->user_type, [1, 2, 9]) && !(Auth::user()->user_type == 3 &&
          Auth::user()->is_librarian == 1))
          <li><a class="nav-link" href="{{ route('physical_library.my_borrows') }}">My Borrowed Books</a></li>
          @endif
        </ul>
      </li>

      <!-- e-Library -->
      <li class="dropdown @if(request()->routeIs('e_library.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="globe"></i><span>e-Library</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('e_library.view_resources') }}">Browse Resources</a></li>

          @if(in_array(Auth::user()->user_type, [1, 2, 9]) || (Auth::user()->user_type == 3 &&
          Auth::user()->is_librarian == 1))
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{ route('e_library.add_resource') }}">Add Resources</a></li>
          <li><a class="nav-link" href="{{ route('e_library.manage_resources') }}">Manage Resources</a></li>
          @endif
        </ul>
      </li>

      @endif
      {{-- END: LIBRARY --}}

      <!-- ========== COUNSELLING (Types 1, 2, 10) ========== -->
      @if(Auth::user()->user_type == 10 || in_array(Auth::user()->user_type, [1, 2]))
      <li class="menu-header">COUNSELLING</li>

      <li class="dropdown @if(request()->routeIs('counsellor.*')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="heart"></i><span>Student Counselling</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('counsellor.create') }}">Schedule Session</a></li>
          <li><a class="nav-link" href="{{ route('counsellor.index') }}">My Counselling Sessions</a></li>
        </ul>
      </li>
      @endif
      {{-- END: COUNSELLING --}}

      <!-- ========== STUDENT PORTAL (Type 4) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 3, 4]))

      @if(Auth::user()->user_type == 4)
      <li class="dropdown @if(request()->routeIs('students.timetable')) active @endif">
        <a href="{{ route('students.timetable') }}" class="nav-link">
          <i data-feather="calendar"></i><span>My Timetable</span>
        </a>
      </li>

      <li class="dropdown @if(request()->routeIs(['students.reportcards.index', 'students.reportcards.print', 'students.reportcards.sheet', 'students.reportcards.show', 'students.reportcards.verify'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="award"></i><span>Report Cards</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('students.reportcards.index') }}">View Report Cards</a></li>
        </ul>
      </li>

      <li class="dropdown @if(request()->routeIs('students.issuedpins')) active @endif">
        <a href="{{ route('students.issuedpins') }}" class="nav-link">
          <i data-feather="key"></i><span>My Issued PINs</span>
        </a>
      </li>
      @endif

      @endif
      {{-- END: STUDENT PORTAL --}}

      <!-- ========== PARENT PORTAL (Types 1, 2, 5) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 5]))
      <li class="menu-header">PARENT PORTAL</li>

      <li class="dropdown @if(request()->routeIs('wards.index')) active @endif">
        <a href="{{ route('wards.index') }}" class="nav-link">
          <i data-feather="users"></i><span>My Wards</span>
        </a>
      </li>

      <li class="dropdown @if(request()->routeIs('parent.ward.fee_prospectus')) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="credit-card"></i><span>Fees & Payments</span>
        </a>
        <ul class="dropdown-menu">
          @foreach(Auth::user()->students as $ward)
          <li>
            <a class="nav-link" href="{{ route('parent.ward.fee_prospectus', $ward->id) }}">
              {{ $ward->name }}'s Fee Prospectus
            </a>
          </li>
          @endforeach

          @if(Auth::user()->students->isEmpty())
          <li><a class="nav-link text-muted">No wards linked yet</a></li>
          @endif
        </ul>
      </li>

      <li class="dropdown @if(request()->routeIs(['parents.wards.reportcards', 'parents.wards.reportcards.pdf', 'parents.wards.reportcards.view', 'parents.wards.reportcards.verify'])) active @endif">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="award"></i><span>Wards Report Cards</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('parents.wards.reportcards') }}">View Wards Results</a></li>
        </ul>
      </li>

      <li class="dropdown @if(request()->routeIs('parents.wards.pins')) active @endif">
        <a href="{{ route('parents.wards.pins') }}" class="nav-link">
          <i data-feather="key"></i><span>Wards Issued PINs</span>
        </a>
      </li>

      <li class="dropdown @if(request()->routeIs('parents.transaction.history')) active @endif">
        <a href="{{ route('parents.transaction.history') }}" class="nav-link">
          <i data-feather="file-text"></i><span>Transaction History</span>
        </a>
      </li>

      @endif
      {{-- END: PARENT PORTAL --}}

      <!-- ========== SYSTEM ADMINISTRATION (Types 1, 2, 9) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 9]))
      <li class="menu-header">SYSTEM ADMINISTRATION</li>

      @if(Auth::user()->user_type == 1)
      <li class="dropdown @if(request()->routeIs('pins.create')) active @endif">
        <a href="{{ route('pins.create') }}" class="nav-link">
          <i data-feather="key"></i><span>Generate PINs</span>
        </a>
      </li>
      @endif

      <li class="dropdown @if(request()->routeIs(['pins.index', 'pins.details', 'pins.print', 'pins.reset'])) active @endif">
        <a href="{{ route('pins.index') }}" class="nav-link">
          <i data-feather="database"></i><span>View PINs</span>
        </a>
      </li>

      <li class="dropdown @if(request()->routeIs(['pins.issue', 'pins.issue.store', 'pins.sessions', 'pins.students', 'pins.terms'])) active @endif">
        <a href="{{ route('pins.issue') }}" class="nav-link">
          <i data-feather="users"></i><span>Issue PINs</span>
        </a>
      </li>

      @endif
      {{-- END: SYSTEM ADMINISTRATION --}}

      @if(in_array(Auth::user()->user_type, [1, 2]))
      <li class="menu-header">SYSTEM LOGS</li>
      <li class="@if(request()->routeIs('activity.log*')) active @endif">
        <a href="{{ route('activity.log') }}" class="nav-link">
          <i data-feather="activity"></i><span>Activity Log</span>
        </a>
      </li>
      @endif

    </ul>
  </aside>
</div>

<script>
  // Every sidebar <li> above already gets an "active" class from the server
  // (via request()->routeIs(...)), covering both pages that are directly
  // linked in the sidebar AND pages that aren't (editing a specific student,
  // a promotion's details page, printing a specific result, etc.) - those
  // still match the route-name pattern for their section even though no
  // sidebar link points at their exact URL.
  //
  // scripts.js (loaded later, in the footer) auto-expands any top-level
  // dropdown <li> that has that "active" class - this script is registered
  // before scripts.js's own $(document).ready handler (it's included
  // earlier in the page), so that's already guaranteed to happen.
  //
  // What's still missing: on a long menu, the now-expanded active section
  // can be scrolled out of view, so it's technically open but not actually
  // visible without hunting for it. Bring it on screen regardless of
  // whether it came from a direct link or a "not directly in the menu"
  // page - both are marked "active" the same way, so both are handled here.
  $(function () {
    var $activeSection = $('.main-sidebar .sidebar-menu > li.active').first();

    if (!$activeSection.length) {
      return;
    }

    // Wait for scripts.js to expand the dropdown and for niceScroll to
    // finish initializing before measuring/scrolling.
    setTimeout(function () {
      var el = $activeSection.get(0);
      if (el && el.scrollIntoView) {
        el.scrollIntoView({ block: 'center' });
      }
    }, 300);
  });
</script>