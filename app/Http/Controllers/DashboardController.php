<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Session;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Course;
use App\Models\Book;
use App\Models\Event;
use App\Models\TeachersAttendance;
use App\Models\Test;
use App\Models\StudentsExam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        // Get current session and term
        $currentSession = Session::where('is_current', 1)->first();
        $currentTerm = $currentSession ? Term::where('session_id', $currentSession->id)
            ->where('is_current', 1)
            ->first() : null;

        // Basic counts
        $totalStudents = User::where('user_type', 4)->count();
        $totalTeachers = User::where('user_type', 3)->count();
        
        // Staff counts
        $totalBursars = User::where('user_type', 6)->count();
        $totalPrincipals = User::where('user_type', 7)->count();
        $totalVicePrincipals = User::where('user_type', 8)->count();
        $totalDeans = User::where('user_type', 9)->count();
        $totalCounsellors = User::where('user_type', 10)->count();
        
        $totalStaff = $totalTeachers + $totalBursars + $totalPrincipals + 
                     $totalVicePrincipals + $totalDeans + $totalCounsellors;
        
        $suspendedAccounts = User::where('is_active', 0)->count();
        $totalClasses = SchoolClass::count();
        $totalCourses = DB::table('courses')->count();
        $libraryBooks = DB::table('books')->count();

        // Upcoming events (events with start_date >= today)
        $upcomingEvents = DB::table('events')
            ->where('start_date', '>=', Carbon::today())
            ->orderBy('start_date', 'asc')
            ->count();

        // Get next 5 upcoming events for display
        $nextEvents = DB::table('events')
            ->where('start_date', '>=', Carbon::today())
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        // Teacher Attendance Statistics (Current Week)
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        
        $attendanceStats = collect();
        $attendanceRate = 0;
        
        if ($currentSession && $currentTerm) {
            $attendanceStats = TeachersAttendance::where('session_id', $currentSession->id)
                ->where('session_term', $currentTerm->id)
                ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                ->select('attendance', DB::raw('count(*) as total'))
                ->groupBy('attendance')
                ->get()
                ->keyBy('attendance');

            $totalRecords = $attendanceStats->sum('total');
            $presentCount = $attendanceStats->get('Present')->total ?? 0;
            $attendanceRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;
        }

        // Teacher Attendance by Day (Last 7 Days) for Chart
        $last7Days = [];
        $attendanceByDay = [];
        $absentByDay = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last7Days[] = $date->format('D, M j');
            
            if ($currentSession && $currentTerm) {
                $dayStats = TeachersAttendance::where('session_id', $currentSession->id)
                    ->where('session_term', $currentTerm->id)
                    ->where('date', $date->format('Y-m-d'))
                    ->select('attendance', DB::raw('count(*) as total'))
                    ->groupBy('attendance')
                    ->get()
                    ->keyBy('attendance');
                
                $present = $dayStats->get('Present')->total ?? 0;
                $absent = ($dayStats->get('Absent')->total ?? 0) + 
                         ($dayStats->get('Late')->total ?? 0) + 
                         ($dayStats->get('On Leave')->total ?? 0);
                
                $attendanceByDay[] = $present;
                $absentByDay[] = $absent;
            } else {
                $attendanceByDay[] = 0;
                $absentByDay[] = 0;
            }
        }

        // Class Distribution Chart Data
        $classDistribution = SchoolClass::withCount(['students' => function($query) {
            $query->where('user_type', 4);
        }])->get();
        
        $classNames = $classDistribution->pluck('name')->toArray();
        $studentCounts = $classDistribution->pluck('students_count')->toArray();

        // Recent Activity - Latest Teacher Attendances
        $recentAttendances = collect();
        if ($currentSession && $currentTerm) {
            $recentAttendances = TeachersAttendance::with('teacher')
                ->where('session_id', $currentSession->id)
                ->where('session_term', $currentTerm->id)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->limit(10)
                ->get();
        }

        // Monthly attendance summary
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        
        $monthlyAttendance = collect();
        if ($currentSession && $currentTerm) {
            $monthlyAttendance = TeachersAttendance::where('session_id', $currentSession->id)
                ->where('session_term', $currentTerm->id)
                ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                ->select('attendance', DB::raw('count(*) as total'))
                ->groupBy('attendance')
                ->get()
                ->keyBy('attendance');
        }

        return view('admin_dashboard', compact(
            'currentSession',
            'currentTerm',
            'totalStudents',
            'totalTeachers',
            'totalStaff',
            'suspendedAccounts',
            'totalClasses',
            'totalCourses',
            'libraryBooks',
            'upcomingEvents',
            'nextEvents',
            'attendanceRate',
            'attendanceStats',
            'last7Days',
            'attendanceByDay',
            'absentByDay',
            'classNames',
            'studentCounts',
            'recentAttendances',
            'monthlyAttendance',
            'totalBursars',
            'totalPrincipals',
            'totalVicePrincipals',
            'totalDeans',
            'totalCounsellors'
        ));
    }

    public function index()
    {
        $totalTests = Test::count();
        $submittedTests = Test::where('is_submitted', 1)->count();
        $approvedTests = Test::where('is_approved', 1)->count();
        $notSubmittedTests = Test::where('is_submitted', 0)->count();
        $testsTaken = Test::where('is_started', 1)->count();
        $submittedNotApprovedTests = Test::where('is_submitted', 1)
            ->where('is_approved', 0)
            ->count();
        $scheduledTests = Test::whereNotNull('scheduled_date')
            ->where('scheduled_date', '!=', '')
            ->count();

        $approvedNotScheduledTests = Test::where('is_approved', 1)
            ->where(function ($query) {
                $query->whereNull('scheduled_date')
                    ->orWhere('scheduled_date', '');
            })
            ->count();

        $passedTests = StudentsExam::where('user_id', Auth::id())
            ->where('is_passed', 1)
            ->count();

        $totalSubmissions = StudentsExam::where('user_id', Auth::id())
            ->where('is_submited', 1)
            ->count();
        $passedCount = StudentsExam::where('user_id', Auth::id())
            ->where('is_submited', 1)
            ->where('is_passed', 1)
            ->count();
        $failedCount = $totalSubmissions - $passedCount;

        $passPercentage = $totalSubmissions > 0 ? round(($passedCount / $totalSubmissions) * 100, 1) : 0;
        $failPercentage = $totalSubmissions > 0 ? round(($failedCount / $totalSubmissions) * 100, 1) : 0;

        $totalStudents = User::where('user_type', 4)->count();
        $totalTeachers = User::where('user_type', 3)->count();
        $totalAdmins = User::where('user_type', 2)->count();
        $suspendedUsers = User::where('is_active', 0)->count();

        $studentTestsTaken = 0;
        $availableTests = 0;

        if (Auth::check() && Auth::user()->user_type == 4) {
            $studentClassId = Auth::user()->class_id;
            $studentTestsTaken = StudentsExam::where('user_id', Auth::id())->count();
            $availableTests = Test::where('is_started', 0)
                ->where('class_id', $studentClassId)
                ->count();
        }

        $lastFiveExams = StudentsExam::with('test')
            ->where('user_id', Auth::id())
            ->where('is_submited', 1)
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->reverse();

        $examLabels = [];
        $scores = [];

        foreach ($lastFiveExams as $exam) {
            $examLabels[] = $exam->test->test_name ?? 'Test';
            $scores[] = $exam->test_total_score ?? 0;
        }

        $failedTestsCount = DB::table('students_exams')
            ->where('user_id', Auth::id())
            ->where('is_submited', 1)
            ->where('is_passed', 0)
            ->count();

        if (Auth::check() && Auth::user()->user_type == 4) {
            $latestExamRecords = DB::table('students_exams')
                ->where('user_id', Auth::id())
                ->where('is_submited', 1)
                ->orderByDesc('id')
                ->take(5)
                ->get();
            $submittedTestIds = $latestExamRecords->pluck('test_id')->toArray();
            $tests = Test::with('schoolClass')
                ->whereIn('id', $submittedTestIds)
                ->get()
                ->sortBy(function ($test) use ($submittedTestIds) {
                    return array_search($test->id, $submittedTestIds);
                });
            $studentTestData = $latestExamRecords->keyBy('test_id');
        } else {
            $tests = collect();
            $studentTestData = collect();
        }

        $submittedCount = DB::table('students_exams')
            ->where('user_id', Auth::id())
            ->where('is_submited', 1)
            ->count();

        $myCreatedTests = Test::where('created_by', Auth::id())->count();
        $mySubmittedTests = Test::where('created_by', Auth::id())
            ->where('is_submitted', 1)
            ->count();
        $myApprovedTests = Test::where('created_by', Auth::id())
            ->where('is_approved', 1)
            ->count();
        $myNotSubmittedTests = Test::where('created_by', Auth::id())
            ->where('is_submitted', 0)
            ->count();

        $recentClassTests = DB::table('students_exams as se')
            ->join('tests as t', 'se.test_id', '=', 't.id')
            ->join('school_classes as sc', 't.class_id', '=', 'sc.id')
            ->where('se.is_submited', 1)
            ->select(
                'se.test_id',
                't.test_name',
                'sc.name',
                DB::raw("SUM(CASE WHEN se.is_passed = 1 THEN 1 ELSE 0 END) as pass_count"),
                DB::raw("SUM(CASE WHEN se.is_passed = 0 THEN 1 ELSE 0 END) as fail_count"),
                DB::raw("MAX(se.id) as latest_submission_id")
            )
            ->groupBy('se.test_id', 't.test_name', 'sc.name')
            ->orderByDesc('latest_submission_id')
            ->limit(5)
            ->get();

        $testLabels = [];
        $passCounts = [];
        $failCounts = [];

        foreach ($recentClassTests as $test) {
            $testLabels[] = "{$test->test_name} ({$test->name})";
            $passCounts[] = (int) $test->pass_count;
            $failCounts[] = (int) $test->fail_count;
        }

        return view('dashboard', compact(
            'myNotSubmittedTests',
            'myCreatedTests',
            'myApprovedTests',
            'mySubmittedTests',
            'submittedCount',
            'failedTestsCount',
            'examLabels',
            'scores',
            'totalTests',
            'submittedTests',
            'approvedTests',
            'notSubmittedTests',
            'testsTaken',
            'submittedNotApprovedTests',
            'scheduledTests',
            'approvedNotScheduledTests',
            'totalStudents',
            'totalTeachers',
            'totalAdmins',
            'suspendedUsers',
            'studentTestsTaken',
            'availableTests',
            'passedTests',
            'passPercentage',
            'failPercentage',
            'totalSubmissions',
            'tests',
            'studentTestData',
            'testLabels',
            'passCounts',
            'failCounts'
        ));
    }

    public function redirectToDashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        switch (Auth::user()->user_type) {
            case 1: // superAdmin
            case 7: // principal
            case 8: // viceprincipal
            case 9: // dean of studies
            case 10: // Guidance counsellor
                return redirect()->route('admins.dashboard');
            case 2: // Admin
                return redirect()->route('admins.dashboard');
            case 3: // Teacher
                return redirect()->route('teachers.dashboard');
            case 4: // Student
                return redirect()->route('students.dashboard');
            case 5: // Parent
                return redirect()->route('parents.dashboard');
            case 6: // Accountant/Bursar
                return redirect()->route('bursar.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }
}