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
        $sections = Section::select('id', 'section_name')->get();
        $sessions = Session::all();
        $currentSession = Session::where('is_current', true)->first();
        $currentTerm = Term::where('is_current', true)->first();

        return view('students_attendance', compact('sections', 'sessions', 'currentSession', 'currentTerm'));
    }

    public function getSessions(Request $request)
    {
        $sessions = Session::where('section_id', $request->section_id)
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

        $students = $query->get();

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:school_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'class_id' => 'required|exists:school_classes,id',
            'attendance_date' => 'required|date',
            'attendance_time' => 'required',
            'attendances' => 'sometimes|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:Present,Absent,Late,On Leave',
        ]);

        if (empty($request->attendances)) {
            return back()->with('warning', 'No students need attendance marking for this date.');
        }

        $attendanceDate = Carbon::parse($request->attendance_date);
        $dateString = $attendanceDate->format('Y-m-d');

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
                    'student_id' => $attendance['student_id'],
                    'class_id' => $request->class_id,
                    'session_id' => $request->session_id,
                    'session_term' => $request->term_id,
                    'date' => $dateString,
                    'time' => $request->attendance_time,
                    'attendance' => $attendance['status'],
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
        $sectionId = $request->get('section_id');
        $classId = $request->get('class_id');
        $sessionId = $request->get('session_id');
        $termId = $request->get('term_id');
        $period = $request->get('period', 'weekly');
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $sections = Section::select('id', 'section_name')->get();
        $sessions = collect();
        $classes = collect();
        $terms = collect();

        if ($sectionId) {
            $sessions = Session::where('section_id', $sectionId)->get();

            if (!$sessionId) {
                $currentSessionForSection = Session::where('section_id', $sectionId)->where('is_current', true)->first();
                if ($currentSessionForSection) {
                    $sessionId = $currentSessionForSection->id;
                } elseif ($sessions->isNotEmpty()) {
                    $sessionId = $sessions->first()->id;
                }
            }
        }

        if ($sessionId) {
            $terms = Term::where('session_id', $sessionId)->get();

            if (!$termId && $terms->isNotEmpty()) {
                $currentTermForSession = Term::where('is_current', true)->where('session_id', $sessionId)->first();
                if ($currentTermForSession) {
                    $termId = $currentTermForSession->id;
                } else {
                    $termId = $terms->first()->id ?? null;
                }
            }
        }

        if ($sectionId) {
            $classes = SchoolClass::where('section_id', $sectionId)->get();
        }

        $currentSession = Session::where('is_current', true)->first();
        $currentTerm = Term::where('is_current', true)->first();

        $start = Carbon::parse($startDate);
        $displayStart = $startDate;
        $displayEnd = $endDate;
        $prevWeekStart = null;
        $nextWeekStart = null;

        if ($period === 'weekly') {
            $weekStart = $start->copy()->startOfWeek();
            $displayStart = $weekStart->format('Y-m-d');
            $displayEnd = $weekStart->copy()->endOfWeek()->format('Y-m-d');
            $prevWeekStart = $weekStart->copy()->subWeek()->format('Y-m-d');
            $nextWeekStart = $weekStart->copy()->addWeek()->format('Y-m-d');
        } elseif ($period === 'monthly') {
            $monthStart = $start->copy()->startOfMonth();
            $displayStart = $monthStart->format('Y-m-d');
            $displayEnd = $monthStart->copy()->endOfMonth()->format('Y-m-d');
            $prevWeekStart = $monthStart->copy()->subMonth()->format('Y-m-d');
            $nextWeekStart = $monthStart->copy()->addMonth()->format('Y-m-d');
        }

        $students = collect();
        $attendances = collect();

        if ($classId && $sessionId && $termId) {
            $students = User::where('class_id', $classId)
                ->where('user_type', 4)
                ->get();

            // FIXED: Get all attendance records and create a keyed collection for easy lookup
            $attendanceRecords = StudentAttendance::where('class_id', $classId)
                ->where('session_id', $sessionId)
                ->where('session_term', $termId)
                ->whereBetween('date', [$displayStart, $displayEnd])
                ->get();

            // Create a keyed collection for O(1) lookup: "student_id-date" => record
            $attendances = $attendanceRecords->keyBy(function($item) {
                return $item->student_id . '-' . Carbon::parse($item->date)->format('Y-m-d');
            });
        }

        return view('students_attendance_index', compact(
            'sections', 'sessions', 'classes', 'terms', 'currentSession', 'currentTerm',
            'sectionId', 'classId', 'sessionId', 'termId', 'period', 'startDate', 'endDate',
            'students', 'attendances', 'displayStart', 'displayEnd', 'prevWeekStart', 'nextWeekStart'
        ));
    }

    public function report(Request $request)
    {
        $attendances = StudentAttendance::with(['student', 'schoolClass'])
            ->when($request->class_id, function ($query) use ($request) {
                return $query->where('class_id', $request->class_id);
            })
            ->when($request->date_from, function ($query) use ($request) {
                return $query->where('date', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($query) use ($request) {
                return $query->where('date', '<=', $request->date_to);
            })
            ->get();

        return view('attendance.report', compact('attendances'));
    }
}