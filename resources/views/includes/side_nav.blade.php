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
      <li class="dropdown">
        <a href="{{ route('dynamic.dashboard') }}" class="nav-link">
          <i data-feather="monitor"></i><span>Dashboard</span>
        </a>
      </li>

      <!-- ========== SUPER ADMIN & ADMIN (Type 1 & 2) ========== -->
      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 7 || auth()->user()->user_type == 8 ||
      auth()->user()->user_type == 9)
      <li class="menu-header">SCHOOL MANAGEMENT</li>

      <!-- School Settings -->
      <li class="dropdown">
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
        </ul>
      </li>

      <!-- Academic Sessions -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="calendar"></i><span>Academic Sessions</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('sessions.create') }}">Set Current Session</a></li>
          <li><a class="nav-link" href="{{ route('sessions.index') }}">Manage Sessions</a></li>
        </ul>
      </li>

      <!-- Timetable -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="clock"></i><span>Timetable</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('timetables.create') }}">Create Timetable</a></li>
          <li><a class="nav-link" href="{{ route('timetables.index') }}">Manage Timetables</a></li>
        </ul>
      </li>



      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="home"></i><span>Hostels</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('hostels.add') }}">Add Hostel</a></li>
          <li><a class="nav-link" href="{{ route('hostels.manage') }}">Manage Hostels</a></li>
          <li><a class="nav-link" href="{{ route('hostels.allocate') }}">Allocate Hostels</a></li>
        </ul>
      </li>


      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 7 || auth()->user()->user_type == 8)
      <li class="menu-header">USER MANAGEMENT</li>

      <!-- Students -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="users"></i><span>Students</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('students.create') }}">Add Student</a></li>
          <li><a class="nav-link" href="{{ route('students.index') }}">Manage Students</a></li>
        </ul>
      </li>

      <!-- Parents -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="user-check"></i><span>Parents</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('parent.add') }}">Add Parent</a></li>
          <li><a class="nav-link" href="{{ route('parents.index') }}">Manage Parents</a></li>
        </ul>
      </li>

      <!-- Staff & Administration -->
      <li class="dropdown">
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



      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 7 || auth()->user()->user_type == 8)
      <li class="menu-header">ATTENDANCE</li>

      <!-- Staff Attendance -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="clipboard"></i><span>Staff Attendance</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('attendance.teachers.mark') }}">Mark Attendance</a></li>
          <li><a class="nav-link" href="{{ route('attendance.teachers.index') }}">View Attendance</a></li>
        </ul>
      </li>

      <!-- Student Attendance -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="check-square"></i><span>Student Attendance</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('attendance.students.mark') }}">Mark Attendance</a></li>
          <li><a class="nav-link" href="{{ route('attendance.students.index') }}">View Attendance</a></li>
        </ul>
      </li>

      @endif
      @endif

      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 6)
      <li class="menu-header">FINANCE</li>

      <!-- Fee Management -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="credit-card"></i><span>Fee Management</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('payment.create') }}">New Payment</a></li>
          <li><a class="nav-link" href="{{ route('payment.manage') }}">Manage Payments</a></li>
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{route('fee.prospectus.create')}}">Create Fee Prospectus</a></li>
          <li><a class="nav-link" href="{{route('fee.prospectus.manage')}}">Manage Fee Prospectus</a></li>
        </ul>
      </li>

      <!-- Miscellaneous Fees -->
      <li class="dropdown">
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

      <!-- Payroll -->
      {{-- <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="dollar-sign"></i><span>Payroll</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('finance.payroll.create') }}">Payroll Capture</a></li>
          <li><a class="nav-link" href="{{ route('finance.payroll.index') }}">Manage Payroll Capture</a></li>
          <li><a class="nav-link" href="{{ route('finance.payroll.process') }}">Process Salary</a></li>
          <li><a class="nav-link" href="{{ route('finance.payroll.processed-salaries') }}">View Processed Salaries</a>
          </li>
        </ul>
      </li> --}}

      <!-- Other Expenses -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="trending-down"></i><span>Other Expenses</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('other.expense.create') }}">Create Other Expense</a></li>
          <li><a class="nav-link" href="{{ route('other.expense.manage') }}">Manage Other Expenses</a></li>
        </ul>
      </li>

      <!-- Financial Reports -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="bar-chart-2"></i><span>Financial Reports</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('finance.overview') }}">Income & Expenses</a></li>
        </ul>
      </li>

      @endif



      <li class="menu-header">COMMUNICATION</li>

      @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 2 ||
      auth()->user()->user_type == 7 || auth()->user()->user_type == 8)

      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="message-circle"></i><span>Announcements</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('announcements.create') }}">Send Announcement</a></li>
          <li><a class="nav-link" href="{{ route('announcements.index') }}">All Announcements</a></li>
        </ul>
      </li>
      @endif





      <li class="dropdown">
        <a href="{{ route('events.calendar') }}" class="nav-link">
          <i data-feather="calendar"></i><span>School Calendar</span>
        </a>
      </li>

      <!-- ========== TEACHERS (Type 3) - Student Management ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 3, 7, 8, 9, 10]))

      @if(in_array(Auth::user()->user_type, [1, 2, 3, 7, 8, 9, 10]))
      <li class="menu-header">MY TEACHING</li>

      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="user"></i><span>Student Management</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('teachers.my_students') }}">View My Students</a></li>
        </ul>
      </li>

      <li class="dropdown">
        <a href="{{ route('teachers.timetable') }}" class="nav-link">
          <i data-feather="clock"></i><span>My Teaching Schedule</span>
        </a>
      </li>

      {{-- Admin-only: View all teachers' schedules --}}
      @if(in_array(Auth::user()->user_type, [1, 2]))
      <li class="dropdown">
        <a href="{{ route('admin.teaching-schedules') }}" class="nav-link">
          <i data-feather="users"></i><span>All Teachers' Schedules</span>
        </a>
      </li>
      @endif
      @endif

      <!-- ========== ACADEMICS & ASSESSMENT - Admin & Teachers (Type 1, 2, 3) ========== -->
      <li class="menu-header">ACADEMICS & ASSESSMENT</li>

      <!-- Exam Questions -->
      <li class="dropdown">
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
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="file-text"></i><span>Results</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('results.upload') }}">Upload Results</a></li>
          @if(in_array(Auth::user()->user_type, [1, 2]) || (Auth::user()->user_type == 3 &&
          Auth::user()->is_form_teacher))
          <li><a class="nav-link" href="{{ route('results.print') }}">Print Results</a></li>
          @endif
        </ul>
      </li>

      @endif

      <!-- ========== E-CLASSROOM - Admin, Teachers & Students (Type 1, 2, 3, 4) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 3, 4, 7, 8, 9, 10]))

      <li class="menu-header">E-LEARNING</li>

      <li class="dropdown">
        <a href="{{ route('eclass.index') }}" class="nav-link">
          <i data-feather="video"></i><span>E-Classroom</span>
        </a>
      </li>

      @endif

      <!-- ========== CBT SECTION - Multiple User Types (1, 2, 3, 4) ========== -->
      @if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || in_array(Auth::user()->user_type, [1, 2, 3,
      4, 7, 8, 9, 10]))

      <li class="menu-header">COMPUTER-BASED TESTING</li>

      @if(Auth::user()->user_type == 1 || Auth::user()->user_type == 2 || Auth::user()->user_type == 7 ||
      Auth::user()->user_type == 8 || Auth::user()->user_type == 9)
      <!-- CBT Dashboard - Admin Only -->
      <li class="dropdown">
        <a href="{{ route('dashboard') }}" class="nav-link">
          <i data-feather="monitor"></i><span>CBT Dashboard</span>
        </a>
      </li>

      <!-- Tests Oversight - Admin Only -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="check-square"></i><span>Tests Oversight</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('tests.view') }}">Approve Tests</a></li>
          <li><a class="nav-link" href="{{ route('tests.schedule') }}">Schedule Tests</a></li>
        </ul>
      </li>
      @endif

      @if(in_array(Auth::user()->user_type, [1, 2, 3, 7, 8, 9, 10]))
      <!-- Create Tests - Admin & Teachers -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="file-plus"></i><span>Create CBT Test</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('tests.create') }}">Create Test</a></li>
          <li><a class="nav-link" href="{{ route('tests.index') }}">Manage Tests</a></li>
        </ul>
      </li>

      <!-- Questions Bank - Admin & Teachers -->
      <li class="dropdown">
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
      <li class="dropdown">
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

      <!-- ========== LIBRARY - All Users (1, 2, 3, 4, 5) + Librarians ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 3, 4, 7, 8, 9 , 10]) || (Auth::user()->user_type == 3 &&
      Auth::user()->is_librarian
      == 1))

      <li class="menu-header">LIBRARY</li>

      <!-- Physical Library -->
      <li class="dropdown">
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

          @if(!in_array(Auth::user()->user_type, [1,2, 9]) && !(Auth::user()->user_type == 3 &&
          Auth::user()->is_librarian
          == 1))
          <li><a class="nav-link" href="{{ route('physical_library.my_borrows') }}">My Borrowed Books</a></li>
          @endif
        </ul>
      </li>

      <!-- e-Library -->
      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="globe"></i><span>e-Library</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('e_library.view_resources') }}">Browse Resources</a></li>

          @if(in_array(Auth::user()->user_type, [1, 2, 9]) || (Auth::user()->user_type == 3 &&
          Auth::user()->is_librarian
          == 1))
          <li class="divider mx-4"></li>
          <li><a class="nav-link" href="{{ route('e_library.add_resource') }}">Add Resources</a></li>
          <li><a class="nav-link" href="{{ route('e_library.manage_resources') }}">Manage Resources</a></li>
          @endif
        </ul>
      </li>

      @endif

      <!-- ========== COUNSELLING MODULE - Guidance Counsellor (Type 10) ========== -->
      @if(Auth::user()->user_type == 10 || in_array(Auth::user()->user_type, [1, 2]))

      <li class="menu-header">COUNSELLING</li>

      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="heart"></i><span>Student Counselling</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('counsellor.create') }}">Schedule Session</a></li>
          <li><a class="nav-link" href="{{ route('counsellor.index') }}">My Counselling Sessions</a></li>
        </ul>
      </li>

      @endif


      <!-- ========== MY PROFILE - Admin, Teachers, Students (Type 1, 2, 3, 4) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 3, 4]))

      {{-- <li class="menu-header">MY PROFILE</li>

      <li class="dropdown">
        <a href="{{ route('students.profile', Auth::id()) }}" class="nav-link">
          <i data-feather="user"></i><span>My Profile</span>
        </a>
      </li> --}}

      @if(in_array(Auth::user()->user_type, [4]))
      <!-- FOR STUDENTS ONLY -->
      <li class="dropdown">
        <a href="{{ route('students.timetable') }}" class="nav-link">
          <i data-feather="calendar"></i><span>My Timetable</span>
        </a>
      </li>

      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="award"></i><span>Report Cards</span>
        </a>
        <ul class="dropdown-menu">
          <li>
            <a class="nav-link" href="{{ route('students.reportcards.index') }}">
              View Report Cards
            </a>
          </li>
        </ul>
      </li>

      <!-- NEW: My Issued PINs -->
      <li class="dropdown">
        <a href="{{ route('students.issuedpins') }}" class="nav-link">
          <i data-feather="key"></i><span>My Issued PINs</span>
        </a>
      </li>
      @endif


      @endif

      <!-- ========== PARENT PORTAL - Admin & Parents (Type 1, 2, 5) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 5]))

      <li class="menu-header">PARENT PORTAL</li>

      <li class="dropdown">
        <a href="{{ route('wards.index') }}" class="nav-link">
          <i data-feather="users"></i><span>My Wards</span>
        </a>
      </li>
      <li class="dropdown">
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


      <li class="dropdown">
        <a href="#" class="menu-toggle nav-link has-dropdown">
          <i data-feather="award"></i><span>Wards Report Cards</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="{{ route('parents.wards.reportcards') }}">View Wards Results</a></li>
        </ul>
      </li>

      <li class="dropdown">
        <a href="{{ route('parents.wards.pins') }}" class="nav-link">
          <i data-feather="key"></i><span>Wards Issued PINs</span>
        </a>
      </li>

      <li class="dropdown">
        <a href="{{ route('parents.transaction.history') }}" class="nav-link">
          <i data-feather="file-text"></i>
          <span>Transaction History</span>
        </a>
      </li>

      @endif

      <!-- ========== SYSTEM ADMINISTRATION - Super Admin Only (Type 1) ========== -->
      @if(in_array(Auth::user()->user_type, [1, 2, 9]))

      <li class="menu-header">SYSTEM ADMINISTRATION</li>


      @if(in_array(Auth::user()->user_type, [1]))
      <li class="dropdown">
        <a href="{{ route('pins.create') }}" class="nav-link">
          <i data-feather="key"></i><span>Generate PINs</span>
        </a>
      </li>
      @endif


      <li class="dropdown">
        <a href="{{ route('pins.index') }}" class="nav-link">
          <i data-feather="database"></i><span>View PINs</span>
        </a>
      </li>

      <li class="dropdown">
        <a href="{{ route('pins.issue') }}" class="nav-link">
          <i data-feather="users"></i><span>Issue PINs</span>
        </a>
      </li>

      @endif

    </ul>
  </aside>
</div>