<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SummernoteController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\PhysicalLibraryController; // New controller
use App\Http\Controllers\ELibraryController; // New controller
use App\Http\Controllers\SessionController; // New controller
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\BursarController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\ProcessPayrollController;
use App\Http\Controllers\MiscFeePaymentController;
use App\Http\Controllers\MiscFeeController;
use App\Http\Controllers\OtherExpenseController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ExamQuestionController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\EClassController;
use App\Http\Controllers\HostelController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StudentReportCardController;
use App\Http\Controllers\ParentReportCardController;
use App\Http\Controllers\CounsellorController;



Route::get('/', function () {
    return view('index');
})->name('login'); // Consider renaming to 'home' for clarity

// Login routes
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Logout route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/amic-dashboard', [DashboardController::class, 'redirectToDashboard'])->name('dynamic.dashboard');

    // Existing routes (unchanged)
    Route::get('/calendar', function () {
        return view('calendar');
    })->name('calendar');

    // Dashboard routes
    Route::get('/students/dashboard', [StudentController::class, 'dashboard'])->name('students.dashboard');
    Route::get('/teachers/dashboard', [UserController::class, 'teacherDashboard'])->name('teachers.dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admins.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route::get('/get-classes/{section_id}', [StudentController::class, 'getClasses']);
    //passwordchange
    Route::get('/change-password', [PasswordController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [PasswordController::class, 'changePassword'])->name('password.update');

    Route::get('/get-third-term/{sessionId}', [StudentController::class, 'getThirdTerm'])->name('get.third.term');

    Route::get('/teachers/my-students', [StudentController::class, 'myStudents'])
        ->name('teachers.my_students');

    // Section routes
    Route::get('/add-section', [SectionController::class, 'create'])->name('section.create');
    Route::get('/manage-sections', [SectionController::class, 'index'])->name('section.index');
    Route::post('/add-section', [SectionController::class, 'store'])->name('section.store');
    Route::get('/add_section', [SectionController::class, 'showSections'])->name('add_section');
    Route::get('/edit_section/{id}', [SectionController::class, 'edit'])->name('edit_section');
    Route::put('/section/{id}', [SectionController::class, 'update'])->name('section.update');
    Route::delete('/delete_section/{id}', [SectionController::class, 'destroy'])->name('delete_section');

    // User/Teacher routes
    Route::get('/add-user', [UserController::class, 'create'])->name('user.add');
    Route::post('/add-user', [UserController::class, 'store'])->name('user.store');
    Route::get('/manage-user', [UserController::class, 'manageUsers'])->name('users.index');
    Route::get('/edit-user/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('users/toggle-active/{id}', [UserController::class, 'toggleActive'])->name('users.toggleActive');
    Route::get('/reset-password/{encryptedId}', [UserController::class, 'resetPassword'])->name('users.reset');
    Route::delete('/delete-user/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/edit-user/{id}', [UserController::class, 'update'])->name('users.update');

    Route::get('/add-teacher', [UserController::class, 'createTeacher'])->name('teacher.add');
    Route::post('/add-teacher', [UserController::class, 'storeTeacher'])->name('teacher.store');
    Route::get('/manage-teacher', [UserController::class, 'manageTeacher'])->name('teachers.index');
    Route::get('/get-classes-by-sections', [UserController::class, 'getClassesBySections'])
        ->name('users.classes_by_sections');
    Route::get('/edit-teacher/{id}', [UserController::class, 'editTeacher'])->name('teachers.edit');
    Route::patch('teachers/toggle-active/{id}', [UserController::class, 'toggleActiveTeacher'])->name('teachers.toggleActive');
    Route::get('/reset-password/{id}', [UserController::class, 'resetPasswordTeacher'])->name('teachers.reset');
    Route::delete('/delete-teacher/{id}', [UserController::class, 'destroyTeacher'])->name('teachers.destroy');
    Route::put('/edit-teacher/{id}', [UserController::class, 'updateTeacher'])->name('teachers.update');

    Route::get('/get-subjects-by-section/{sectionId}', [UserController::class, 'getSubjectsBySection']);

    Route::get('/get-assigned-form-classes', [UserController::class, 'getAssignedFormClassIds'])->name('get.assigned.form.classes');


    Route::get('/get-assigned-form-classes-with-teachers', [UserController::class, 'getAssignedFormClassesWithTeachers'])->name('get.assigned.form.classes.with.teachers');

    // School Class routes
    Route::get('/add-school-class', [SchoolClassController::class, 'create'])->name('schoolClass.add');
    Route::get('/manage-school-classes', [SchoolClassController::class, 'index'])->name('schoolClass.manage');
    Route::post('/store-school-class', [SchoolClassController::class, 'store'])->name('schoolClass.store');
    Route::get('/edit-school-class/{id}', [SchoolClassController::class, 'edit'])->name('schoolClass.edit');
    Route::delete('/delete-school-class/{id}', [SchoolClassController::class, 'destroy'])->name('schoolClass.delete');
    Route::put('/update-school-class/{id}', [SchoolClassController::class, 'update'])->name('schoolClass.update');

    // Course routes
    Route::get('/courses/add', [CourseController::class, 'create'])->name('course.create');
    Route::post('/courses/add', [CourseController::class, 'store'])->name('course.store');
    Route::get('/courses/manage', [CourseController::class, 'index'])->name('course.manage');
    Route::get('/courses/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');
    Route::put('/courses/update/{id}', [CourseController::class, 'update'])->name('course.update');
    Route::delete('/courses/delete/{id}', [CourseController::class, 'destroy'])->name('course.delete');
    // In your routes file (web.php)

    Route::get('/courses/get-classes/{sectionId}', [CourseController::class, 'getClassesBySection'])
        ->name('course.getClasses');

    // Test routes
    Route::get('/tests/create', [TestController::class, 'create'])->name('tests.create');
    Route::post('/tests', [TestController::class, 'store'])->name('tests.store');
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
    Route::get('/tests/{id}/edit', [TestController::class, 'edit'])->name('tests.edit');
    Route::put('/tests/{id}', [TestController::class, 'update'])->name('tests.update');
    Route::delete('/tests/{id}', [TestController::class, 'destroy'])->name('tests.destroy');
    Route::get('/sections/{id}/classes', [TestController::class, 'getClassesBySection'])->name('sections.classes');
    Route::get('/tests/{id}', [TestController::class, 'show'])->name('tests.show');
    // web.php
    Route::get('/tests/classes-by-section/{sectionId}', [TestController::class, 'getClassesBySection'])
        ->name('tests.classes-by-section');

    // Question routes
    Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::get('/tests/{test}/set-questions', [QuestionController::class, 'setQuestions'])->name('tests.setQuestions');
    Route::post('/questions/store', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('/tests/{test}/questions', [QuestionController::class, 'setQuestions'])->name('questions.set');
    Route::get('/questions/view/{test}', [QuestionController::class, 'viewQuestions'])->name('questions.view');
    Route::post('/tests/{test}/submit-for-approval', [QuestionController::class, 'submitForApproval'])->name('tests.submitForApproval');

    // Additional Test routes
    Route::get('/view-tests', [TestController::class, 'viewTests'])->name('tests.view');
    Route::get('/tests/{test}/approve', [TestController::class, 'approveTest'])->name('tests.approve');
    Route::get('/tests/{test}/check', [TestController::class, 'editCheck'])->name('tests.check');
    Route::delete('/tests/{test}', [TestController::class, 'deleteTest'])->name('tests.delete');
    Route::post('/tests/{test}/comment', [TestController::class, 'submitComment'])->name('tests.comment');

    // Student routes
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/get-classes/{section_id}', [StudentController::class, 'getClasses']);
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::get('/students/profile/{id}', [StudentController::class, 'profile'])->name('students.profile');
    Route::get('/students/{student}/performance', [StudentController::class, 'performance'])->name('students.performance');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::patch('/students/{student}/suspend', [StudentController::class, 'suspend'])->name('students.suspend');
    Route::patch('/students/{student}/activate', [StudentController::class, 'activate'])->name('students.activate');
    Route::patch('/students/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset_password');
    // Student Attendance routes
    Route::get('/attendance/students/mark', [StudentAttendanceController::class, 'create'])->name('attendance.students.mark');
    Route::post('/attendance/students', [StudentAttendanceController::class, 'store'])->name('attendance.students.store');
    Route::get('/attendance/students', [StudentAttendanceController::class, 'index'])->name('attendance.students.index');
    Route::get('/attendance/students/report', [StudentAttendanceController::class, 'report'])->name('attendance.students.report');
    Route::get('/attendance/students/sessions', [StudentAttendanceController::class, 'getSessions'])->name('attendance.students.sessions'); // New: Fetch sessions per section
    Route::get('/attendance/students/terms', [StudentAttendanceController::class, 'getTerms'])->name('attendance.students.terms');
    Route::get('/attendance/students/classes', [StudentAttendanceController::class, 'getClasses'])->name('attendance.students.classes');
    Route::get('/attendance/students/get', [StudentAttendanceController::class, 'getStudents'])->name('attendance.students.get');

    // Teacher Attendance routes
    Route::get('/attendance/teachers/mark', [TeacherAttendanceController::class, 'create'])->name('attendance.teachers.mark');
    Route::post('/attendance/teachers', [TeacherAttendanceController::class, 'store'])->name('attendance.teachers.store');
    Route::get('/attendance/teachers', [TeacherAttendanceController::class, 'index'])->name('attendance.teachers.index');
    Route::get('/attendance/teachers/report', [TeacherAttendanceController::class, 'report'])->name('attendance.teachers.report');
    Route::get('/attendance/teachers/search', [TeacherAttendanceController::class, 'searchTeachers'])->name('attendance.teachers.search');
    Route::get('/attendance/teachers/create', [TeacherAttendanceController::class, 'create'])->name('attendance.teachers.create');

    // API routes for dynamic dropdowns
    Route::get('/api/sessions', [SessionController::class, 'getBySection'])->name('api.sessions');
    Route::get('/api/terms', [SessionController::class, 'getTermsBySession'])->name('api.terms');

    // Session routes
    Route::get('/sessions/set', [SessionController::class, 'create'])->name('sessions.create');
    Route::post('/sessions', [SessionController::class, 'store'])->name('sessions.store');
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::post('/sessions/{session}/unset', [SessionController::class, 'unset'])->name('sessions.unset');
    Route::delete('/sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::post('sessions/{session}/set', [SessionController::class, 'set'])->name('sessions.set');
    Route::put('sessions/{session}', [SessionController::class, 'update'])->name('sessions.update');
    Route::post('sessions/term/{term}/set', [SessionController::class, 'setTerm'])->name('sessions.term.set');

    // Student Test routes
    Route::get('/available-tests', [TestController::class, 'available'])->name('tests.available');
    Route::get('/past-tests', [TestController::class, 'past'])->name('tests.past');
    Route::get('/past-tests/view/{testId}', [TestController::class, 'viewPast'])->name('tests.viewPast');
    Route::get('/student/analytics', [TestController::class, 'studentAnalytics'])->name('student.analytics');
    Route::post('/tests/{id}/submit', [TestController::class, 'submitTest'])->name('tests.submit');
    Route::post('/tests/save-answer', [TestController::class, 'saveAnswer'])->name('tests.saveAnswer');
    Route::get('/schedule-test', [TestController::class, 'schedule'])->name('tests.schedule');
    Route::post('/schedule-test/{id}', [TestController::class, 'saveSchedule'])->name('tests.saveSchedule');
    Route::post('/tests/{id}/cancel-schedule', [TestController::class, 'cancelSchedule'])->name('tests.cancelSchedule');
    Route::get('/calendar/events', [TestController::class, 'calendarEvents'])->name('calendar.events');
    Route::get('/start-test', [TestController::class, 'startTest'])->name('tests.start');
    Route::get('/my-tests', [TestController::class, 'viewTests'])->name('tests.student');
    Route::get('/test/{id}/take', [TestController::class, 'takeTest'])->name('tests.take');
    Route::post('/tests/{id}/force-stop', [TestController::class, 'forceStop'])->name('tests.forceStop');

    // Announcement routes
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::post('/announcements/mark-all-read', [AnnouncementController::class, 'markAllAsRead'])->name('announcements.markAllRead');
    Route::post('/announcements/{id}/mark-read', [AnnouncementController::class, 'markAsRead'])->name('announcements.markAsRead');

    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');


    Route::post('/summernote/upload-image', [SummernoteController::class, 'uploadImage'])->name('summernote.image.upload');

    // Add these routes to your web.php file
    Route::get('/parent/ward/{studentId}/fee-prospectus', [ParentController::class, 'wardFeeProspectus'])
        ->name('parent.ward.fee_prospectus')
        ->where('studentId', '[0-9]+');
    Route::get('/parents', [ParentController::class, 'index'])->name('parents.index');
    Route::get('/parent/add', [ParentController::class, 'create'])->name('parent.add');
    Route::post('/parent/store', [ParentController::class, 'store'])->name('parent.store');
    // Add this route to your web.php file (replace the previous students/by-section route)
    Route::get('/students/search', [ParentController::class, 'searchStudents'])->name('students.search');
    Route::get('/wards', [ParentController::class, 'myWards'])->name('wards.index');
    Route::get('/parent/{parent}/edit', [ParentController::class, 'edit'])->name('parent.edit');
    Route::put('/parent/{parent}', [ParentController::class, 'update'])->name('parent.update');
    Route::delete('/parent/{parent}', [ParentController::class, 'destroy'])->name('parent.destroy');
    Route::get('/student/{student}/guardian', [ParentController::class, 'getStudentGuardian'])->name('student.guardian');
    // Parent Dashboard route
    Route::get('/parents/dashboard', [ParentController::class, 'dashboard'])
        ->name('parents.dashboard');


    Route::get('/parents/transaction-history', [ParentController::class, 'transactionHistory'])
        ->name('parents.transaction.history');
    // Physical Library routes
    Route::get('/library/physical/add-book', [PhysicalLibraryController::class, 'createBook'])
        ->name('physical_library.add_book');

    Route::post('/library/physical/add-book', [PhysicalLibraryController::class, 'storeBook'])
        ->name('physical_library.store_book');

    Route::get('/library/physical/manage-books', [PhysicalLibraryController::class, 'indexBooks'])
        ->name('physical_library.manage_books');

    // Encrypted ID routes
    Route::get('/library/physical/edit-book/{encryptedId}', [PhysicalLibraryController::class, 'editBook'])
        ->name('physical_library.edit_book');

    Route::put('/library/physical/update-book/{encryptedId}', [PhysicalLibraryController::class, 'updateBook'])
        ->name('physical_library.update_book');

    Route::delete('/library/physical/delete-book/{id}', [PhysicalLibraryController::class, 'destroyBook'])
        ->name('physical_library.delete_book');

    Route::get('/library/physical/borrowing-returns', [PhysicalLibraryController::class, 'borrowingReturns'])
        ->name('physical_library.borrowing_returns');

    Route::get('/library/physical/borrow', [PhysicalLibraryController::class, 'createBorrow'])
        ->name('physical_library.borrow');

    Route::post('/library/physical/borrow', [PhysicalLibraryController::class, 'borrowBook'])
        ->name('physical_library.borrow_store');

    Route::post('/library/physical/return/{id}', [PhysicalLibraryController::class, 'returnBook'])
        ->name('physical_library.return');

    Route::get('/library/physical/members', [PhysicalLibraryController::class, 'members'])
        ->name('physical_library.members');

    Route::get('/library/physical/assign-librarian', [PhysicalLibraryController::class, 'assignLibrarian'])
        ->name('physical_library.assign_librarian');

    Route::post('/library/physical/assign-librarian', [PhysicalLibraryController::class, 'storeAssignLibrarian'])
        ->name('physical_library.store_assign_librarian');

    Route::get('/library/physical/request-borrow', [PhysicalLibraryController::class, 'requestBorrow'])
        ->name('physical_library.request_borrow');

    Route::post('/library/physical/request-borrow', [PhysicalLibraryController::class, 'storeBorrowRequest'])
        ->name('physical_library.store_borrow_request');

    // User: View my borrows
    Route::get('/library/physical/my-borrows', [PhysicalLibraryController::class, 'myBorrows'])
        ->name('physical_library.my_borrows');

    // Librarian only actions
    Route::post('/library/physical/approve-borrow/{id}', [PhysicalLibraryController::class, 'approveBorrow'])
        ->name('physical_library.approve_borrow');

    Route::post('/library/physical/reject-borrow/{id}', [PhysicalLibraryController::class, 'rejectBorrow'])
        ->name('physical_library.reject_borrow');
    Route::post('/library/physical/approve-borrow/{id}', [PhysicalLibraryController::class, 'approveBorrow'])->name('physical_library.approve_borrow');
    Route::post('/library/physical/reject-borrow/{id}', [PhysicalLibraryController::class, 'rejectBorrow'])->name('physical_library.reject_borrow');
    Route::post('/library/physical/return/{id}', [PhysicalLibraryController::class, 'returnBook'])->name('physical_library.return');
    Route::post('/library/physical/undo-return/{id}', [PhysicalLibraryController::class, 'undoReturn'])->name('physical_library.undo_return');


    // e-Library routes
    Route::get('/library/e-library/add-resource', [ELibraryController::class, 'createResource'])->name('e_library.add_resource');
    Route::post('/library/e-library/add-resource', [ELibraryController::class, 'storeResource'])->name('e_library.store_resource');
    Route::get('/library/e-library/manage-resources', [ELibraryController::class, 'indexResources'])->name('e_library.manage_resources');
    Route::get('/library/e-library/edit-resource/{id}', [ELibraryController::class, 'editResource'])->name('e_library.edit_resource');
    Route::put('/library/e-library/update-resource/{id}', [ELibraryController::class, 'updateResource'])->name('e_library.update_resource');
    Route::delete('/library/e-library/delete-resource/{id}', [ELibraryController::class, 'destroyResource'])->name('e_library.delete_resource');
    Route::get('/library/e-library/members', [ELibraryController::class, 'members'])->name('e_library.members');

    // Route accessible by all authenticated users (browse resources)
    Route::get('/library/e-library/resources', [ELibraryController::class, 'viewResources'])
        ->name('e_library.view_resources');

    Route::get('/library/e-library/view/{id}', [ELibraryController::class, 'viewResource'])
        ->name('e_library.view_resource');





    // Assessment routes
    Route::get('/assignments/create', [AssessmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssessmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments', [AssessmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/reports', [AssessmentController::class, 'reports'])->name('assignments.reports');
    Route::get('/assignments/classes/{section}', [AssessmentController::class, 'getClasses'])->name('assignments.classes');
    Route::get('/assignments/sessions/{section}', [AssessmentController::class, 'getSessions'])->name('assignments.sessions');
    Route::get('/assignments/terms/{session}', [AssessmentController::class, 'getTerms'])->name('assignments.terms');
    Route::get('/assignments/subjects/{class}', [AssessmentController::class, 'getSubjects'])->name('assignments.subjects');

    Route::get('/assignments/subjects/{class}', [AssessmentController::class, 'getSubjects'])->name('assignments.subjects');

    // Add these routes to your web.php file
    Route::get('/assignments/{assessment}/edit', [AssessmentController::class, 'edit'])->name('assignments.edit');
    Route::put('/assignments/{assessment}', [AssessmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assessment}', [AssessmentController::class, 'destroy'])->name('assignments.destroy');

    // Route::get('/tests/create', [AssessmentController::class, 'createTest'])->name('tests.create');

    // Results routes (grouped and duplicates removed)
    Route::get('/results/upload', [ResultsController::class, 'uploadForm'])->name('results.upload');
    Route::post('/results/select-class', [ResultsController::class, 'selectClass'])->name('results.selectClass');
    Route::get('/results', [ResultsController::class, 'index'])->name('results.index');
    Route::get('/student-result-upload/{student}', [ResultsController::class, 'studentResultUpload'])->name('student.result.upload');
    Route::post('/upload-result', [ResultsController::class, 'uploadResult'])->name('results.uploadResult');
    Route::post('/save-result', [ResultsController::class, 'saveResult'])->name('results.saveResult');

    // Class Promotion Routes
    Route::get('/students/promote', [StudentController::class, 'promoteForm'])->name('students.promote');
    Route::get('/students/promote/preview', [StudentController::class, 'getPromotionPreview'])->name('students.promote.preview');
    Route::post('/students/promote/process', [StudentController::class, 'processPromotion'])->name('students.promote.process');

    Route::get('/students/promote/preview-multiple', [StudentController::class, 'getPromotionPreviewMultiple'])->name('students.promote.preview.multiple');
    Route::post('/students/promote/process-enhanced', [StudentController::class, 'processPromotionEnhanced'])->name('students.promote.process.enhanced');

      // NEW: Enhanced promotion processing with tracking
    Route::post('/students/promote/process', [StudentController::class, 'processPromotionEnhanced'])->name('students.promote.process');
    
    // NEW: Promotion history and tracking routes
    Route::get('/students/promotion/history', [StudentController::class, 'promotionHistory'])->name('students.promotion.history');
    Route::get('/students/promotion/{id}/details', [StudentController::class, 'viewPromotionDetails'])->name('students.promotion.details');
    Route::post('/students/promotion/{id}/rollback', [StudentController::class, 'rollbackPromotion'])->name('students.promotion.rollback');
    

    Route::get('/student/{studentId}/results/upload', [ResultsController::class, 'studentResultUpload'])->name('student.results.upload');
    Route::post('/student/{studentId}/results/save', [ResultsController::class, 'saveStudentResults'])->name('student.results.save');


    // Results Print routes
    Route::get('/results/print', [ResultsController::class, 'printForm'])->name('results.print');
    Route::any('/results/print/select-class', [ResultsController::class, 'selectClassForPrint'])
        ->name('results.selectClassForPrint');
    Route::get('/results/print/student/{student}', [ResultsController::class, 'printStudent'])->name('results.printStudent');
    // Master List Route
    Route::get('/results/master-list/{classId}', [ResultsController::class, 'masterList'])->name('results.masterList');

    Route::get('/results/master-list/{class}/export', [ResultsController::class, 'exportMasterList'])
        ->name('results.exportMasterList');

    // Add these routes to your existing routes
    Route::get('/results/cumulative/{classId}', [ResultsController::class, 'cumulativeResults'])->name('results.cumulative');
    Route::get('/results/transcript/{studentId}', [ResultsController::class, 'printTranscript'])->name('results.transcript');

    // Edit Remarks for a student
    Route::get('/results/remarks/{student}', [ResultsController::class, 'editRemarks'])
        ->name('results.remarks.edit');

    Route::post('/results/remarks/{student}', [ResultsController::class, 'updateRemarks'])
        ->name('results.remarks.update');

    // Payments 
    Route::get('/bursar/dashboard', [BursarController::class, 'dashboard'])
        ->name('bursar.dashboard');
    Route::get('/payments/create', [BursarController::class, 'createPayment'])->name('payment.create');
    Route::post('/payments', [BursarController::class, 'storePayment'])->name('bursar.storePayment');
    Route::get('/bursar/classes/{sectionId}', [BursarController::class, 'getClassesBySection']);
    Route::get('/bursar/students/{classId}', [BursarController::class, 'getStudentsByClass']);
    Route::post('/payments', [BursarController::class, 'storePayment'])->name('bursar.storePayment');


    Route::get('/finance/payroll/all-sessions', [ProcessPayrollController::class, 'getAllSessions'])
        ->name('finance.payroll.all.sessions');
    Route::get('/payroll', [ProcessPayrollController::class, 'index'])->name('finance.payroll.index');
    Route::get('/payroll/create', [ProcessPayrollController::class, 'create'])->name('finance.payroll.create'); // Add this line
    Route::post('/payroll', [ProcessPayrollController::class, 'store'])->name('finance.payroll.store');
    Route::get('/payroll/{payroll}/edit', [ProcessPayrollController::class, 'edit'])->name('finance.payroll.edit');
    Route::put('/payroll/{payroll}', [ProcessPayrollController::class, 'update'])->name('finance.payroll.update');
    Route::delete('/payroll/{payroll}', [ProcessPayrollController::class, 'destroy'])->name('finance.payroll.destroy');

    Route::get('/payroll/process', [ProcessPayrollController::class, 'processForm'])->name('finance.payroll.process');
    Route::post('/payroll/process', [ProcessPayrollController::class, 'processPreview'])->name('finance.payroll.process.preview');



    Route::get('/payroll/sessions/{section_id}', [ProcessPayrollController::class, 'getSessions'])->name('finance.payroll.sessions');
    Route::get('/payroll/terms/{session_id}', [ProcessPayrollController::class, 'getTerms'])->name('finance.payroll.terms');


    // Add these new routes
    Route::post('/payroll/{payroll}/update-field', [ProcessPayrollController::class, 'updateField'])->name('finance.payroll.update-field');
    Route::post('/payroll/confirm-process', [ProcessPayrollController::class, 'confirmProcess'])->name('finance.payroll.confirm-process');

    // Add these routes to your web.php
    Route::get('/payroll/processed-salaries', [ProcessPayrollController::class, 'processedSalaries'])->name('finance.payroll.processed-salaries');
    Route::get('/payroll/payment-slip/{salaryPayment}', [ProcessPayrollController::class, 'generatePaymentSlip'])->name('finance.payroll.payment-slip');
    Route::post('/payroll/bulk-payment-slips', [ProcessPayrollController::class, 'bulkPaymentSlips'])->name('finance.payroll.bulk-payment-slips');


    // Add these routes to your web.php for Miscellaneous Fee Types
    Route::get('/misc-fee/create', [MiscFeeController::class, 'create'])->name('misc.fee.create');
    Route::post('/misc-fee', [MiscFeeController::class, 'store'])->name('misc.fee.store');
    Route::get('/misc-fee', [MiscFeeController::class, 'index'])->name('misc.fee.manage');
    Route::get('/misc-fee/{miscFee}/edit', [MiscFeeController::class, 'edit'])->name('misc.fee.edit');
    Route::put('/misc-fee/{miscFee}', [MiscFeeController::class, 'update'])->name('misc.fee.update');
    Route::delete('/misc-fee/{miscFee}', [MiscFeeController::class, 'destroy'])->name('misc.fee.destroy');

    // Add these routes for Miscellaneous Fee Payments
    Route::get('/misc-fee/payments/create', [MiscFeePaymentController::class, 'create'])->name('misc.fee.payments.create');
    Route::post('/misc-fee/payments', [MiscFeePaymentController::class, 'store'])->name('misc.fee.payments.store');
    Route::get('/misc-fee/payments', [MiscFeePaymentController::class, 'index'])->name('misc.fee.payments.manage');

    // Add these additional routes to your web.php
    Route::get('/misc-fee/payments/sessions/{section_id}', [MiscFeePaymentController::class, 'getSessions'])->name('misc.fee.payments.sessions');
    Route::get('/misc-fee/payments/classes/{section_id}', [MiscFeePaymentController::class, 'getClasses'])->name('misc.fee.payments.classes');
    Route::get('/misc-fee/payments/students/{class_id}', [MiscFeePaymentController::class, 'getStudents'])->name('misc.fee.payments.students');
    Route::get('/misc-fee/payments/{id}/receipt', [MiscFeePaymentController::class, 'receipt'])->name('misc.fee.payments.receipt');


    // Create Other Expense
    Route::get('/other-expense/create', [OtherExpenseController::class, 'create'])->name('other.expense.create');
    Route::post('/other-expense', [OtherExpenseController::class, 'store'])->name('other.expense.store');

    // Manage Other Expenses (List/Index)
    Route::get('/other-expense', [OtherExpenseController::class, 'index'])->name('other.expense.manage');

    // Edit Other Expense
    Route::get('/other-expense/{otherExpense}/edit', [OtherExpenseController::class, 'edit'])->name('other.expense.edit');
    Route::put('/other-expense/{otherExpense}', [OtherExpenseController::class, 'update'])->name('other.expense.update');

    // Delete Other Expense
    Route::delete('/other-expense/{otherExpense}', [OtherExpenseController::class, 'destroy'])->name('other.expense.destroy');

    Route::get('/finance/overview', [FinanceReportController::class, 'overview'])->name('finance.overview');

    Route::get('/payroll/slip/{salaryPayment}', [ProcessPayrollController::class, 'generatePaymentSlip'])->name('finance.payroll.slip');
    Route::get('/payment/{payment}/receipt', [BursarController::class, 'printReceipt'])->name('payment.receipt');

    // Add to web.php routes file
    Route::get('/bursary/fee-prospectus/create', [BursarController::class, 'createFeeProspectus'])->name('fee.prospectus.create');
    Route::get('/bursar/terms/{sectionId}', [BursarController::class, 'getTermsBySection']);
    // Add to web.php routes file
    Route::post('/bursary/fee-prospectus/select', [BursarController::class, 'selectForProspectus'])->name('fee.prospectus.select');
    Route::post('/bursary/fee-prospectus/store', [BursarController::class, 'storeFeeProspectus'])->name('fee.prospectus.store');
    // Add to web.php routes file
    Route::get('/bursary/fee-prospectus/manage', [BursarController::class, 'manageFeeProspectus'])->name('fee.prospectus.manage');
    Route::get('/bursary/fee-prospectus/{id}/edit', [BursarController::class, 'editFeeProspectus'])->name('fee.prospectus.edit');
    Route::put('/bursary/fee-prospectus/{id}', [BursarController::class, 'updateFeeProspectus'])->name('fee.prospectus.update');
    Route::delete('/bursary/fee-prospectus/{id}', [BursarController::class, 'destroyFeeProspectus'])->name('fee.prospectus.destroy');
    Route::get('/bursar/classes/{sectionId}', [BursarController::class, 'getClassesBySection'])->name('bursar.getClassesBySection');
    Route::get('/bursary/fee-prospectus/{id}/preview', [BursarController::class, 'previewFeeProspectus'])->name('fee.prospectus.preview');

    Route::get('/payments/create', [BursarController::class, 'createPayment'])->name('payment.create');

    // In routes/web.php
    Route::get('/bursar/payment/details/{studentId}/{sectionId}/{classId}', [BursarController::class, 'paymentDetails'])
        ->name('bursar.payment.details.signed')
        ->middleware('signed'); // This enforces signature check

    Route::get('/bursar/receipt/{payment}', [BursarController::class, 'printReceipt'])
        ->name('bursar.payment.receipt');

    // Route::get('/bursar/classes/{sectionId}', [BursarController::class, 'getClassesBySection']);
    Route::get('/bursar/students/{sectionId}/{classId}', [BursarController::class, 'getStudentsByClass']);
    // Add this route to your web.php for OtherExpense show
    Route::get('/other-expense/{otherExpense}', [OtherExpenseController::class, 'show'])->name('other.expense.show');

    // Add this in the authenticated routes group
Route::get('/finance/analysis', [FinanceReportController::class, 'analysis'])->name('finance.analysis');
Route::get('/finance/analysis/export', [FinanceReportController::class, 'exportAnalysis'])->name('finance.analysis.export');


    Route::post('/payments/select', [BursarController::class, 'selectStudentForPayment'])->name('bursar.selectStudentForPayment');
    Route::get('/bursar/payment/{studentId}/details/{sectionId}/{classId}', [BursarController::class, 'paymentDetails'])->name('bursar.payment.details');
    Route::post('/bursar/payment', [BursarController::class, 'processPayment'])->name('bursar.processPayment');

    // Add this route to your web.php file
    Route::get('/bursar/payment/{payment}/receipt', [BursarController::class, 'printReceipt'])->name('bursar.payment.receipt.alt');

    Route::get('/bursary/payments/manage', [BursarController::class, 'managePayments'])->name('payment.manage');

    Route::get('/bursary/payments/{studentId}/history', [BursarController::class, 'viewTransactionHistory'])->name('payment.history');

    // Add these routes to your web.php file, alongside the existing ones
    Route::get('/bursary/payments/{payment}/edit', [BursarController::class, 'editPayment'])->name('payment.edit');
    Route::put('/bursary/payments/{payment}', [BursarController::class, 'updatePayment'])->name('payment.update');


    Route::get('/pins', [PinController::class, 'index'])->name('pins.index');
    Route::get('/pins/create', [PinController::class, 'create'])->name('pins.create');
    Route::post('/pins', [PinController::class, 'store'])->name('pins.store');
    Route::get('/pins/sessions/{section}', [PinController::class, 'getSessions'])->name('pins.sessions');
    Route::get('/pins/terms/{session}', [PinController::class, 'getTerms'])->name('pins.terms');
    Route::patch('/pins/reset/{id}', [PinController::class, 'resetUsage'])->name('pins.reset');

    Route::get('/results/class/{class_id}', [ResultsController::class, 'viewClassResults'])
        ->name('results.class.view');
    Route::get('/pins/issue', [PinController::class, 'issueForm'])->name('pins.issue');
    Route::get('/pins/students/{section}/{class}', [PinController::class, 'getStudents'])->name('pins.students');
    Route::post('/pins/issue-to-students', [PinController::class, 'issueToStudents'])->name('pins.issue.store');

    Route::get('/api/sections/{section}/classes', function ($sectionId) {
        $classes = \App\Models\SchoolClass::where('section_id', $sectionId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        return response()->json(['classes' => $classes]);
    });

    Route::get('/pins/{id}/details', [PinController::class, 'showDetails'])->name('pins.details');

    // Print Issued PINs Route
    Route::get('/pins/print', [PinController::class, 'printIssuedPins'])->name('pins.print');

    // Also add this API route for fetching classes by section (if not already present)
    Route::get('/api/classes/{sectionId}', [SchoolClassController::class, 'getClassesBySection'])
        ->name('api.classes.by.section');

    // Timetable Routes
    Route::get('/timetables', [TimetableController::class, 'index'])->name('timetables.index');
    Route::get('/timetables/create', [TimetableController::class, 'create'])->name('timetables.create');
    Route::post('/timetables', [TimetableController::class, 'store'])->name('timetables.store');
    Route::get('/timetables/sessions/{section}', [TimetableController::class, 'getSessions'])->name('timetables.sessions');
    Route::get('/timetables/terms/{session}', [TimetableController::class, 'getTerms'])->name('timetables.terms');
    Route::get('/timetables/subjects/{section}', [TimetableController::class, 'getSubjects'])->name('timetables.subjects');
    Route::get('/timetables/classes-subjects/{section}', [TimetableController::class, 'getClassesAndSubjects'])->name('timetables.classes-subjects');
    Route::get('/timetables/{timetable}', [TimetableController::class, 'show'])->name('timetables.show');
    Route::get('/timetables/{timetable}/edit', [TimetableController::class, 'edit'])->name('timetables.edit');
    Route::put('/timetables/{timetable}', [TimetableController::class, 'update'])->name('timetables.update');
    Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroy'])->name('timetables.destroy');
    Route::get('/timetables/{timetable}/export', [TimetableController::class, 'export'])->name('timetables.export');

    // Student Timetable Route

    Route::get('/my-timetable', [TimetableController::class, 'myTimetable'])
        ->name('students.timetable');
    // Teacher Timetable Route
    Route::get('/my-teaching-schedule', [TimetableController::class, 'myTeachingSchedule'])
        ->name('teachers.timetable');
    Route::get('/results/class/{class_id}/export', [ResultsController::class, 'exportClassResults'])
        ->name('results.class.export');

    // Admin: View all teachers' teaching schedules
    Route::get('/admin/teaching-schedules', [TimetableController::class, 'allTeachingSchedules'])
        ->name('admin.teaching-schedules')
        ->middleware('auth');

    // List teacher's exams (or all for admins)
    Route::get('/exam-questions', [ExamQuestionController::class, 'index'])
        ->name('exam_questions.index');

    // Create new exam
    Route::get('/exam-questions/create', [ExamQuestionController::class, 'create'])
        ->name('exam_questions.create');
    Route::post('/exam-questions', [ExamQuestionController::class, 'store'])
        ->name('exam_questions.store');

    // View single exam
    Route::get('/exam-questions/{id}', [ExamQuestionController::class, 'show'])
        ->name('exam_questions.show');

    // Edit exam
    Route::get('/exam-questions/{id}/edit', [ExamQuestionController::class, 'edit'])
        ->name('exam_questions.edit');
    Route::put('/exam-questions/{id}', [ExamQuestionController::class, 'update'])
        ->name('exam_questions.update');

    // Delete exam
    Route::delete('/exam-questions/{id}', [ExamQuestionController::class, 'destroy'])
        ->name('exam_questions.destroy');

    // Print exam paper
    Route::get('/exam-questions/{id}/print', [ExamQuestionController::class, 'print'])
        ->name('exam_questions.print');

    // Duplicate exam
    Route::post('/exam-questions/{id}/duplicate', [ExamQuestionController::class, 'duplicate'])
        ->name('exam_questions.duplicate');

    // Admin only: View all exams in the school
    Route::get('/admin/all-exams', [ExamQuestionController::class, 'allExams'])
        ->name('exam_questions.all_exams')
        ->middleware('admin'); // Create this middleware or use role check

    // AJAX Routes — CORRECT PATHS
    Route::get('/ajax/sessions/{sectionId}', [ExamQuestionController::class, 'getSessions'])
        ->name('ajax.sessions');

    Route::get('/ajax/terms/{sessionId}', [ExamQuestionController::class, 'getTerms'])
        ->name('ajax.terms');

    Route::get('/ajax/classes/{sectionId}', [ExamQuestionController::class, 'getClasses'])
        ->name('ajax.classes');

    Route::get('/ajax/subjects/{sectionId}/{classId}', [ExamQuestionController::class, 'getSubjects'])
        ->name('ajax.subjects');


    Route::get('/school-settings', [SchoolSettingController::class, 'index'])
        ->name('settings.school');

    Route::put('/school-settings', [SchoolSettingController::class, 'update'])
        ->name('settings.school.update');


    Route::get('/eclass', [EClassController::class, 'index'])->name('eclass.index');
    Route::get('/eclass/create', [EClassController::class, 'create'])->name('eclass.create');
    Route::post('/eclass', [EClassController::class, 'store'])->name('eclass.store');
    Route::get('/eclass/{id}/join', [EClassController::class, 'join'])->name('eclass.join');
    Route::post('/eclass/{id}/end', [EClassController::class, 'end'])->name('eclass.end');



    // Hostel Management (Admin only)
    Route::prefix('hostels')->name('hostels.')->group(function () {
        Route::get('/add', [HostelController::class, 'create'])->name('add');
        Route::post('/store', [HostelController::class, 'store'])->name('store');

        Route::get('/manage', [HostelController::class, 'index'])->name('manage');

        Route::get('/edit/{id}', [HostelController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [HostelController::class, 'update'])->name('update');

        Route::delete('/delete/{id}', [HostelController::class, 'destroy'])->name('delete');

        Route::get('/allocate', [HostelController::class, 'allocateForm'])->name('allocate');
        // Route::post('/allocate', [HostelController::class, 'allocate'])->name('allocate.store');
    });

    // Hostel Allocation Routes
    Route::get('/hostels/allocate', [HostelController::class, 'allocate'])->name('hostels.allocate');
    Route::post('/hostels/allocate/store', [HostelController::class, 'allocateStore'])->name('hostels.allocate.store');

    // View Students & Deallocate
    Route::get('/hostels/{id}/students', [HostelController::class, 'students'])->name('hostels.students');
    Route::post('/hostels/deallocate/{studentId}', [HostelController::class, 'deallocate'])->name('hostels.deallocate');


    Route::get('/calendar', [EventController::class, 'index'])->name('events.calendar');

    Route::get('/events/fetch', [EventController::class, 'fetch'])->name('events.fetch');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    Route::get('/students/report-cards', [StudentReportCardController::class, 'index'])
        ->name('students.reportcards.index');

    Route::post('/students/report-cards/verify-pin', [StudentReportCardController::class, 'verifyPin'])
        ->name('students.reportcards.verify');

    Route::get('/students/report-cards/view', [StudentReportCardController::class, 'viewReport'])
        ->name('students.reportcards.view');

    Route::get('/get-terms/{sessionId}', [PinController::class, 'getTerms'])
        ->name('get.terms');

    Route::get('/students/issued-pins', [StudentReportCardController::class, 'issuedPins'])
        ->name('students.issuedpins');

    Route::get('/students/report-cards/view', [StudentReportCardController::class, 'showReport'])
        ->name('students.reportcards.show');

    Route::get('/wards/report-cards', [App\Http\Controllers\ParentReportCardController::class, 'selectWard'])
        ->name('parents.wards.reportcards');

    Route::post('/wards/report-cards/verify-pin', [App\Http\Controllers\ParentReportCardController::class, 'verifyPin'])
        ->name('parents.wards.verify');

    Route::get('/wards/report-cards/view', [ParentReportCardController::class, 'showReport'])
        ->name('parents.wards.reportcards.view');

    Route::get('/wards/report-cards/pdf', [ParentReportCardController::class, 'downloadPdf'])
        ->name('parents.wards.reportcards.pdf');

    Route::get('/wards/issued-pins', [ParentReportCardController::class, 'issuedPins'])
        ->name('parents.wards.pins');

    // List all sessions
    Route::get('/counsellor/sessions', [CounsellorController::class, 'index'])
        ->name('counsellor.index');

    // Create new session
    Route::get('/create', [CounsellorController::class, 'create'])->name('counsellor.create');
    Route::post('/create', [CounsellorController::class, 'store'])->name('counsellor.store'); // Better: same URL for GET/POST

    // View single session
    Route::get('/{session}', [CounsellorController::class, 'show'])->name('counsellor.show');

    // Edit session
    Route::get('/{session}/edit', [CounsellorController::class, 'edit'])->name('counsellor.edit');
    Route::put('/{session}', [CounsellorController::class, 'update'])->name('counsellor.update');

    // Optional: Soft delete or cancel (recommended)
    Route::delete('/{session}', [CounsellorController::class, 'destroy'])->name('counsellor.destroy');
});
