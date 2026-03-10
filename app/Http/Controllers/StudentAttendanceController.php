<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Session;
use App\Models\Term;
use App\Models\StudentAttendance;
use Carbon\Carbon;

class StudentAttendanceController extends Controller
{
    public function create()
    {
        $user           = Auth::user();
        $currentSession = Session::where('is_current', true)->first();
        $currentTerm    = $currentSession
            ? Term::where('session_id', $currentSession->id)->where('is_current', true)->first()
            : null;
        $sessions       = Session::orderByDesc('name')->get();

        $isFormTeacher  = $user->user_type == 3 && $user->is_form_teacher && $user->form_class_id;

        if ($isFormTeacher) {
            $formClass = SchoolClass::find($user->form_class_id);
            $sections  = Section::where('id', $formClass->section_id)->get();
        } else {
            $sections  = Section::select('id', 'section_name')->get();
        }

        return view('students_attendance', compact(
            'sections',
            'sessions',
            'currentSession',
            'currentTerm',
            'isFormTeacher',
            'user'
        ));
    }

    /**
     * Returns all school-wide sessions.
     * section_id param accepted but ignored — kept for AJAX backward compat.
     */
    public function getSessions(Request $request)
    {
        $sessions = Session::orderByDesc('name')
            ->select('id', 'name', 'is_current')
            ->get();

        return response()->json($sessions);
    }

    public function getTerms(Request $request)
    {
        $terms = Term::where('session_id', $request->session_id)
            ->select('id', 'name', 'is_current')
            ->get();

        return response()->json($terms);
    }

    public function getClasses(Request $request)
    {
        $classes = SchoolClass::where('section_id', $request->section_id)
            ->select('id', 'name')
            ->get();

        return response()->json($classes);
    }

    public function getStudents(Request $request)
    {
        $query = User::where('class_id', $request->class_id)
            ->where('user_type', 4)
            ->select('id', 'name');

        if ($request->session_id && $request->term_id && $request->attendance_date) {
            $date = Carbon::parse($request->attendance_date)->format('Y-m-d');
            $query->whereDoesntHave('studentAttendances', function ($q) use ($request, $date) {
                $q->where('session_id', $request->session_id)
                    ->where('session_term', $request->term_id)
                    ->where('date', $date)
                    ->where('class_id', $request->class_id);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_id'                   => 'required|exists:school_sessions,id',
            'term_id'                      => 'required|exists:terms,id',
            'class_id'                     => 'required|exists:school_classes,id',
            'attendance_date'              => 'required|date',
            'attendance_time'              => 'required',
            'attendances'                  => 'sometimes|array',
            'attendances.*.student_id'     => 'required|exists:users,id',
            'attendances.*.status'         => 'required|in:Present,Absent,Late,On Leave',
        ]);

        if (empty($request->attendances)) {
            return back()->with('warning', 'No students need attendance marking for this date.');
        }

        $dateString   = Carbon::parse($request->attendance_date)->format('Y-m-d');
        $createdCount = 0;

        foreach ($request->attendances as $attendance) {
            $exists = StudentAttendance::where('student_id', $attendance['student_id'])
                ->where('date', $dateString)
                ->where('session_id', $request->session_id)
                ->where('session_term', $request->term_id)
                ->where('class_id', $request->class_id)
                ->exists();

            if (!$exists) {
                StudentAttendance::create([
                    'student_id'   => $attendance['student_id'],
                    'class_id'     => $request->class_id,
                    'session_id'   => $request->session_id,
                    'session_term' => $request->term_id,
                    'date'         => $dateString,
                    'time'         => $request->attendance_time,
                    'attendance'   => $attendance['status'],
                ]);
                $createdCount++;
            }
        }

        $message = $createdCount > 0
            ? "Attendance marked successfully for {$createdCount} student(s)."
            : "No new attendance records were created (all already marked).";

        return back()->with('success', $message);
    }

    public function index(Request $request)
    {
        $user      = Auth::user();
        $sectionId = $request->get('section_id');
        $classId   = $request->get('class_id');
        $sessionId = $request->get('session_id');
        $termId    = $request->get('term_id');
        $period    = $request->get('period', 'weekly');
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate   = $request->get('end_date',   Carbon::today()->format('Y-m-d'));

        $sections = Section::select('id', 'section_name')->get();

        // School-wide sessions — not section-scoped
        $sessions = Session::orderByDesc('name')->get();

        // Default to current session if none selected
        if (!$sessionId) {
            $currentSession = $sessions->firstWhere('is_current', true);
            $sessionId      = $currentSession?->id;
        }

        // Terms for selected session
        $terms = $sessionId
            ? Term::where('session_id', $sessionId)->get()
            : collect();

        // Default to current term if none selected
        if (!$termId && $terms->isNotEmpty()) {
            $currentTerm = $terms->firstWhere('is_current', true);
            $termId      = $currentTerm?->id ?? $terms->first()->id;
        }

        // ── Form Teacher: lock them to their form class ──
        $isFormTeacher = $user->user_type == 3 && $user->is_form_teacher && $user->form_class_id;

        if ($isFormTeacher) {
            $formClass = SchoolClass::find($user->form_class_id);
            $classId   = $classId ?: $user->form_class_id;
            $sectionId = $sectionId ?: $formClass?->section_id;
        }

        // Classes for selected section
        $classes = $sectionId
            ? SchoolClass::where('section_id', $sectionId)->get()
            : collect();

        $currentSession = Session::where('is_current', true)->first();
        $currentTerm    = $currentSession
            ? Term::where('session_id', $currentSession->id)->where('is_current', true)->first()
            : null;

        // Date range calculation
        $start        = Carbon::parse($startDate);
        $displayStart = $startDate;
        $displayEnd   = $endDate;
        $prevWeekStart = null;
        $nextWeekStart = null;

        if ($period === 'weekly') {
            $weekStart     = $start->copy()->startOfWeek();
            $displayStart  = $weekStart->format('Y-m-d');
            $displayEnd    = $weekStart->copy()->endOfWeek()->format('Y-m-d');
            $prevWeekStart = $weekStart->copy()->subWeek()->format('Y-m-d');
            $nextWeekStart = $weekStart->copy()->addWeek()->format('Y-m-d');
        } elseif ($period === 'monthly') {
            $monthStart    = $start->copy()->startOfMonth();
            $displayStart  = $monthStart->format('Y-m-d');
            $displayEnd    = $monthStart->copy()->endOfMonth()->format('Y-m-d');
            $prevWeekStart = $monthStart->copy()->subMonth()->format('Y-m-d');
            $nextWeekStart = $monthStart->copy()->addMonth()->format('Y-m-d');
        }

        $students    = collect();
        $attendances = collect();

        if ($classId && $sessionId && $termId) {
            // Security: form teachers can only view their own form class
            if ($isFormTeacher && $classId != $user->form_class_id) {
                abort(403, 'You can only view attendance for your form class.');
            }

            $students = User::where('class_id', $classId)
                ->where('user_type', 4)
                ->get();

            $attendanceRecords = StudentAttendance::where('class_id', $classId)
                ->where('session_id', $sessionId)
                ->where('session_term', $termId)
                ->whereBetween('date', [$displayStart, $displayEnd])
                ->get();

            // Keyed for O(1) lookup: "student_id-date" => record
            $attendances = $attendanceRecords->keyBy(function ($item) {
                return $item->student_id . '-' . Carbon::parse($item->date)->format('Y-m-d');
            });
        }

        return view('students_attendance_index', compact(
            'sections',
            'sessions',
            'classes',
            'terms',
            'currentSession',
            'currentTerm',
            'sectionId',
            'classId',
            'sessionId',
            'termId',
            'period',
            'startDate',
            'endDate',
            'students',
            'attendances',
            'displayStart',
            'displayEnd',
            'prevWeekStart',
            'nextWeekStart',
            'isFormTeacher'  // ← pass this so the view can hide the section/class selectors
        ));
    }

    public function report(Request $request)
    {
        $attendances = StudentAttendance::with(['student', 'schoolClass'])
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->date_from,  fn($q) => $q->where('date', '>=', $request->date_from))
            ->when($request->date_to,    fn($q) => $q->where('date', '<=', $request->date_to))
            ->get();

        return view('attendance.report', compact('attendances'));
    }
}
