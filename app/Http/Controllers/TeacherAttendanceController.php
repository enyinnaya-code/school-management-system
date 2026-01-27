<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Session;
use App\Models\TeachersAttendance;
use App\Models\Section;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    /**
     * Show the form for creating new attendance record
     */
    public function create()
    {
        // Fetch teachers where user_type = 3
        $teachers = User::where('user_type', 3)->get(['id', 'name']);
        
        // Fetch the current session with its terms
        $currentSession = Session::where('is_current', true)->with('terms')->first();

        return view('teachers_attendance', compact('teachers', 'currentSession'));
    }

    /**
     * Store a newly created attendance record
     */
    public function store(Request $request)
    {
        // Validate form input
        $validated = $request->validate([
            'attendance_date' => 'required|date',
            'attendance_time' => 'required',
            'session_id' => 'required|exists:school_sessions,id',
            'session_term' => 'required|exists:terms,id',
            'teacher_id' => 'required|exists:users,id',
            'attendance' => 'required|in:Present,Absent,Late,On Leave',
        ]);

        // Start a database transaction
        DB::beginTransaction();
        try {
            // Check if an attendance record already exists
            $existingRecord = TeachersAttendance::where([
                'teacher_id' => $request->teacher_id,
                'date' => $request->attendance_date,
                'session_id' => $request->session_id,
                'session_term' => $request->session_term,
            ])->exists();

            if ($existingRecord) {
                $teacher = User::find($request->teacher_id);
                $teacherName = $teacher ? $teacher->name : 'Unknown Teacher (ID: ' . $request->teacher_id . ')';
                DB::rollBack();
                return redirect()->route('attendance.teachers.create')
                    ->with('error', "Attendance for $teacherName on this date has already been recorded.");
            }

            // Create new attendance record
            TeachersAttendance::create([
                'teacher_id' => $request->teacher_id,
                'attendance' => $request->attendance,
                'date' => $request->attendance_date,
                'time' => $request->attendance_time,
                'session_id' => $request->session_id,
                'session_term' => $request->session_term,
            ]);

            DB::commit();

            return redirect()->route('attendance.teachers.create')
                ->with('success', 'Attendance successfully saved for the selected teacher.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('attendance.teachers.create')
                ->with('error', 'Failed to save attendance: ' . $e->getMessage());
        }
    }

    /**
     * Display attendance index/listing
     */
    public function index(Request $request)
    {
        return $this->handleAttendanceView($request, 'teachers_attendance_index');
    }

    /**
     * Display attendance report
     */
    public function report(Request $request)
    {
        return $this->handleAttendanceView($request, 'teachers_attendance_report');
    }

    /**
     * Private method to handle attendance view logic for both index and report
     */
    private function handleAttendanceView(Request $request, $viewName)
    {
        // Fetch all sections
        $sections = Section::all(['id', 'section_name']);
        
        // Initialize variables
        $sectionId = $request->input('section_id');
        $sessionId = $request->input('session_id');
        $termId = $request->input('term_id');
        $period = $request->input('period', 'weekly');
        $inputStart = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $inputEnd = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $attendances = collect(); // Empty collection by default
        $currentSession = null;
        $currentTerm = null;
        $prevWeekStart = null;
        $nextWeekStart = null;

        // Fetch sessions for the selected section
        $sessions = $sectionId ? Session::where('section_id', $sectionId)->get(['id', 'name', 'is_current']) : collect();
        
        // Fetch terms for the selected session
        $terms = $sessionId ? Term::where('session_id', $sessionId)->get(['id', 'name', 'is_current']) : collect();

        // If section is selected but no session/term, default to current session/term
        if ($sectionId && !$sessionId) {
            $currentSession = Session::where('section_id', $sectionId)
                ->where('is_current', true)
                ->first(['id', 'name']);
            $sessionId = $currentSession ? $currentSession->id : null;
            // Re-fetch terms if session now set
            if ($sessionId) {
                $terms = Term::where('session_id', $sessionId)->get(['id', 'name', 'is_current']);
            }
        }

        if ($sessionId && !$termId) {
            $currentTerm = Term::where('session_id', $sessionId)
                ->where('is_current', true)
                ->first(['id', 'name']);
            $termId = $currentTerm ? $currentTerm->id : null;
        }

        // Fetch current session if a session is selected
        if ($sessionId) {
            $currentSession = Session::where('id', $sessionId)->first(['id', 'name']);
        }

        // Calculate month boundaries FIRST for monthly period - THIS IS THE KEY FIX
        $monthStart = null;
        $monthEnd = null;
        if ($period === 'monthly') {
            $baseDate = Carbon::parse($inputStart);
            $monthStart = $baseDate->copy()->startOfMonth();
            $monthEnd = $baseDate->copy()->endOfMonth();
        }

        // Handle view week logic for all periods
        $viewStart = $request->input('view_start');

        if (!$viewStart) {
            $baseDate = Carbon::parse($inputStart);
            switch ($period) {
                case 'weekly':
                    $viewStart = $baseDate->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
                    break;
                case 'monthly':
                    $temp = $monthStart->copy();
                    $firstMonday = $temp->startOfWeek(Carbon::MONDAY);
                    if ($firstMonday->lt($monthStart)) {
                        $firstMonday->addWeek();
                    }
                    $viewStart = $firstMonday->format('Y-m-d');
                    break;
            }
        }

        // Clamp viewStart for monthly to ensure it's within month boundaries
        if ($period === 'monthly' && $viewStart && $monthEnd) {
            $viewCarbon = Carbon::parse($viewStart);
            $weekEnd = $viewCarbon->copy()->endOfWeek(Carbon::SUNDAY);
            
            if ($weekEnd->gt($monthEnd)) {
                $lastSunday = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
                $lastMonday = $lastSunday->copy()->subDays(6);
                if ($lastMonday->lt($monthStart)) {
                    $lastMonday = $monthStart->copy();
                }
                $viewStart = $lastMonday->format('Y-m-d');
            }
        }

        $displayStart = $viewStart;
        $displayEnd = Carbon::parse($displayStart)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
        
        if ($period === 'monthly' && $monthEnd && Carbon::parse($displayEnd)->gt($monthEnd)) {
            $displayEnd = $monthEnd->format('Y-m-d');
        }

        $queryStart = $displayStart;
        $queryEnd = $displayEnd;

        // Calculate prev and next week starts
        $prevWeekStart = Carbon::parse($displayStart)->subWeek()->format('Y-m-d');
        $nextWeekStart = Carbon::parse($displayStart)->addWeek()->format('Y-m-d');

        // Disable navigation for weekly
        if ($period === 'weekly') {
            $prevWeekStart = null;
            $nextWeekStart = null;
        }

        // Adjust navigation buttons for monthly view with proper null checks
        if ($period === 'monthly' && $monthStart && $monthEnd) {
            $prevEnd = Carbon::parse($prevWeekStart)->endOfWeek(Carbon::SUNDAY);
            if ($prevEnd->lt($monthStart)) {
                $prevWeekStart = null;
            }
            
            $nextStartCarbon = Carbon::parse($nextWeekStart);
            if ($nextStartCarbon->gt($monthEnd)) {
                $nextWeekStart = null;
            }
        }

        // Fetch attendance records only if section, session, and term are selected
        if ($sectionId && $sessionId && $termId) {
            $query = TeachersAttendance::with('teacher')
                ->where('session_id', $sessionId)
                ->where('session_term', $termId)
                ->whereBetween('date', [$queryStart, $queryEnd]);

            $attendances = $query->get();
        }

        // Fetch all teachers for display
        $teachers = User::where('user_type', 3)->get(['id', 'name']);

        return view($viewName, compact(
            'sections',
            'sessions',
            'terms',
            'attendances',
            'teachers',
            'currentSession',
            'period',
            'sectionId',
            'sessionId',
            'termId',
            'displayStart',
            'displayEnd',
            'prevWeekStart',
            'nextWeekStart'
        ));
    }
}