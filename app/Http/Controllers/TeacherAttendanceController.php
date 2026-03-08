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
        $teachers = User::where('user_type', 3)->get(['id', 'name']);

        // School-wide current session
        $currentSession = Session::where('is_current', true)->with('terms')->first();

        return view('teachers_attendance', compact('teachers', 'currentSession'));
    }

    /**
     * Store a newly created attendance record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'attendance_date' => 'required|date',
            'attendance_time' => 'required',
            'session_id'      => 'required|exists:school_sessions,id',
            'session_term'    => 'required|exists:terms,id',
            'teacher_id'      => 'required|exists:users,id',
            'attendance'      => 'required|in:Present,Absent,Late,On Leave',
        ]);

        DB::beginTransaction();
        try {
            $existingRecord = TeachersAttendance::where([
                'teacher_id'   => $request->teacher_id,
                'date'         => $request->attendance_date,
                'session_id'   => $request->session_id,
                'session_term' => $request->session_term,
            ])->exists();

            if ($existingRecord) {
                $teacher     = User::find($request->teacher_id);
                $teacherName = $teacher ? $teacher->name : 'Unknown Teacher (ID: ' . $request->teacher_id . ')';
                DB::rollBack();
                return redirect()->route('attendance.teachers.create')
                    ->with('error', "Attendance for $teacherName on this date has already been recorded.");
            }

            TeachersAttendance::create([
                'teacher_id'   => $request->teacher_id,
                'attendance'   => $request->attendance,
                'date'         => $request->attendance_date,
                'time'         => $request->attendance_time,
                'session_id'   => $request->session_id,
                'session_term' => $request->session_term,
            ]);

            DB::commit();

            return redirect()->route('attendance.teachers.create')
                ->with('success', 'Attendance successfully saved.');
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
     * Shared attendance view logic for both index and report.
     */
    private function handleAttendanceView(Request $request, $viewName)
    {
        // ── No section filter needed — sessions are school-wide ──
        $sessionId = $request->input('session_id');
        $termId    = $request->input('term_id');
        $period    = $request->input('period', 'weekly');
        $inputStart = $request->input('start_date', Carbon::today()->format('Y-m-d'));

        // All school-wide sessions for the dropdown
        $sessions = Session::orderByDesc('name')->get(['id', 'name', 'is_current']);

        // Default to current session if none selected
        if (!$sessionId) {
            $currentSession = $sessions->firstWhere('is_current', true);
            $sessionId      = $currentSession?->id;
        } else {
            $currentSession = Session::find($sessionId, ['id', 'name']);
        }

        // Terms for selected session
        $terms = $sessionId
            ? Term::where('session_id', $sessionId)->get(['id', 'name', 'is_current'])
            : collect();

        // Default to current term if none selected
        $currentTerm = null;
        if ($sessionId && !$termId) {
            $currentTerm = $terms->firstWhere('is_current', true);
            $termId      = $currentTerm?->id;
        }
        if ($sessionId && $termId && !$currentTerm) {
            $currentTerm = $terms->firstWhere('id', $termId);
        }

        // ── Date/week calculation ────────────────────────────────
        $monthStart = null;
        $monthEnd   = null;
        if ($period === 'monthly') {
            $baseDate   = Carbon::parse($inputStart);
            $monthStart = $baseDate->copy()->startOfMonth();
            $monthEnd   = $baseDate->copy()->endOfMonth();
        }

        $viewStart = $request->input('view_start');
        if (!$viewStart) {
            $baseDate = Carbon::parse($inputStart);
            if ($period === 'weekly') {
                $viewStart = $baseDate->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            } elseif ($period === 'monthly') {
                $firstMonday = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
                if ($firstMonday->lt($monthStart)) $firstMonday->addWeek();
                $viewStart = $firstMonday->format('Y-m-d');
            }
        }

        // Clamp viewStart for monthly
        if ($period === 'monthly' && $viewStart && $monthEnd) {
            $viewCarbon = Carbon::parse($viewStart);
            $weekEnd    = $viewCarbon->copy()->endOfWeek(Carbon::SUNDAY);
            if ($weekEnd->gt($monthEnd)) {
                $lastMonday = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY)->subDays(6);
                if ($lastMonday->lt($monthStart)) $lastMonday = $monthStart->copy();
                $viewStart = $lastMonday->format('Y-m-d');
            }
        }

        $displayStart = $viewStart;
        $displayEnd   = Carbon::parse($displayStart)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        if ($period === 'monthly' && $monthEnd && Carbon::parse($displayEnd)->gt($monthEnd)) {
            $displayEnd = $monthEnd->format('Y-m-d');
        }

        $prevWeekStart = Carbon::parse($displayStart)->subWeek()->format('Y-m-d');
        $nextWeekStart = Carbon::parse($displayStart)->addWeek()->format('Y-m-d');

        if ($period === 'weekly') {
            $prevWeekStart = null;
            $nextWeekStart = null;
        }

        if ($period === 'monthly' && $monthStart && $monthEnd) {
            if (Carbon::parse($prevWeekStart)->endOfWeek(Carbon::SUNDAY)->lt($monthStart)) {
                $prevWeekStart = null;
            }
            if (Carbon::parse($nextWeekStart)->gt($monthEnd)) {
                $nextWeekStart = null;
            }
        }

        // ── Fetch attendance records ─────────────────────────────
        $attendances = collect();

        if ($sessionId && $termId) {
            $attendances = TeachersAttendance::with('teacher')
                ->where('session_id', $sessionId)
                ->where('session_term', $termId)
                ->whereBetween('date', [$displayStart, $displayEnd])
                ->get();
        }

        $teachers = User::where('user_type', 3)->get(['id', 'name']);

        // Note: $sections and $sectionId are passed as null/empty so any blade
        // that still references them doesn't error out
        $sections  = collect();
        $sectionId = null;

        return view($viewName, compact(
            'sections',
            'sessions',
            'terms',
            'attendances',
            'teachers',
            'currentSession',
            'currentTerm',
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