<?php

namespace App\Http\Controllers;

use App\Exports\TimetableExport;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Session;
use App\Models\Term;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $query = Timetable::with(['section', 'session', 'term', 'createdBy']);

        if ($request->filter_section) {
            $query->where('section_id', $request->filter_section);
        }
        if ($request->filter_session) {
            $query->where('session_id', $request->filter_session);
        }
        if ($request->filter_term) {
            $query->where('term_id', $request->filter_term);
        }

        $timetables = $query->paginate(10);
        $sections   = Section::all();
        $sessions   = Session::orderByDesc('name')->get(); // school-wide
        $terms      = Term::all();

        return view('timetables_index', compact('timetables', 'sections', 'sessions', 'terms'));
    }

    public function create()
    {
        $sections = Section::all();

        // School-wide sessions — not section-scoped
        $sessions           = Session::orderByDesc('name')->get();
        $currentSession     = $sessions->firstWhere('is_current', true);
        $current_session_id = $currentSession?->id;

        $terms           = $currentSession
            ? Term::where('session_id', $currentSession->id)->get()
            : collect([]);
        $current_term_id = $terms->firstWhere('is_current', true)?->id;

        return view('create_timetable', compact(
            'sections', 'sessions', 'terms',
            'current_session_id', 'current_term_id'
        ));
    }

    /**
     * Returns all school-wide sessions.
     * The $sectionId param is kept for route compatibility but ignored.
     */
    public function getSessions($sectionId)
    {
        $sessions       = Session::orderByDesc('name')->get(['id', 'name', 'is_current']);
        $currentSession = $sessions->firstWhere('is_current', true);

        return response()->json([
            'sessions'           => $sessions,
            'current_session_id' => $currentSession?->id,
        ]);
    }

    public function getTerms($sessionId)
    {
        $terms       = Term::where('session_id', $sessionId)
            ->select('id', 'name', 'is_current')
            ->get();
        $currentTerm = $terms->firstWhere('is_current', true);

        return response()->json([
            'terms'           => $terms,
            'current_term_id' => $currentTerm?->id,
        ]);
    }

    public function getClassesAndSubjects($sectionId)
    {
        $classes  = SchoolClass::where('section_id', $sectionId)->select('id', 'name')->get();
        $subjects = Course::where('section_id', $sectionId)->select('id', 'course_name')->get();

        return response()->json(['classes' => $classes, 'subjects' => $subjects]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id'       => 'required|exists:sections,id',
            'session_id'       => 'required|exists:school_sessions,id',
            'term_id'          => 'required|exists:terms,id',
            'num_periods'      => 'required|integer|min:1|max:12',
            'lesson_duration'  => 'required|integer|min:10|max:120',
            'break_duration'   => 'required|integer|min:5|max:60',
            'break_period'     => 'required|integer|min:1|max:12',
            'has_free_periods' => 'required|boolean',
            'schedule'         => 'required|array',
            'periods_monday'   => 'nullable|integer|min:1|max:12',
            'periods_tuesday'  => 'nullable|integer|min:1|max:12',
            'periods_wednesday'=> 'nullable|integer|min:1|max:12',
            'periods_thursday' => 'nullable|integer|min:1|max:12',
            'periods_friday'   => 'nullable|integer|min:1|max:12',
        ]);

        // Check for duplicate timetable
        $existingTimetable = Timetable::where('section_id', $request->section_id)
            ->where('term_id', $request->term_id)
            ->first();

        if ($existingTimetable) {
            return redirect()->back()
                ->with('error', 'A timetable already exists for this section and term.')
                ->withInput();
        }

        // Validate session exists (school-wide — no section check)
        $session = Session::findOrFail($request->session_id);

        // Validate term belongs to session
        $term = Term::where('id', $request->term_id)
            ->where('session_id', $request->session_id)
            ->firstOrFail();

        $classes  = SchoolClass::where('section_id', $request->section_id)->pluck('id')->toArray();
        $subjects = Course::where('section_id', $request->section_id)->pluck('id')->toArray();

        $dayPeriods = [
            'Monday'    => $request->periods_monday    ?? $request->num_periods,
            'Tuesday'   => $request->periods_tuesday   ?? $request->num_periods,
            'Wednesday' => $request->periods_wednesday ?? $request->num_periods,
            'Thursday'  => $request->periods_thursday  ?? $request->num_periods,
            'Friday'    => $request->periods_friday    ?? $request->num_periods,
        ];

        [$validatedSchedule, $conflictErrors] = $this->buildSchedule(
            $request->schedule, $dayPeriods, $classes, $subjects
        );

        $timetable = Timetable::create([
            'section_id'      => $request->section_id,
            'session_id'      => $request->session_id,
            'term_id'         => $request->term_id,
            'num_periods'     => $request->num_periods,
            'lesson_duration' => $request->lesson_duration,
            'break_duration'  => $request->break_duration,
            'break_period'    => $request->break_period,
            'has_free_periods'=> $request->has_free_periods,
            'day_periods'     => $dayPeriods,
            'schedule'        => $validatedSchedule,
            'has_conflicts'   => !empty($conflictErrors),
            'conflicts'       => !empty($conflictErrors) ? $conflictErrors : null,
            'created_by'      => Auth::id(),
        ]);

        if (!empty($conflictErrors)) {
            return redirect()->route('timetables.index')
                ->with('warning', 'Timetable created with ' . count($conflictErrors) . ' conflict(s).')
                ->with('conflicts', $conflictErrors);
        }

        return redirect()->route('timetables.index')
            ->with('success', 'Master timetable created successfully.');
    }

    public function show($id, Request $request)
    {
        $timetable = Timetable::with(['section', 'session', 'term', 'createdBy'])->findOrFail($id);

        $allClasses   = SchoolClass::where('section_id', $timetable->section_id)->orderBy('name')->get();
        $classesQuery = SchoolClass::where('section_id', $timetable->section_id)->orderBy('name');

        if ($request->has('class_filter') && $request->class_filter != '') {
            $classesQuery->where('id', $request->class_filter);
        }

        $classes  = $classesQuery->get();
        $subjects = Course::where('section_id', $timetable->section_id)->get()->keyBy('id');

        return view('timetables_show', compact('timetable', 'classes', 'subjects', 'allClasses'));
    }

    public function edit($id)
    {
        $timetable = Timetable::findOrFail($id);
        $sections  = Section::all();

        // School-wide sessions
        $sessions = Session::orderByDesc('name')->get();
        $terms    = Term::where('session_id', $timetable->session_id)->get();
        $classes  = SchoolClass::where('section_id', $timetable->section_id)->orderBy('name')->get();
        $subjects = Course::where('section_id', $timetable->section_id)->get();

        return view('timetables_edit', compact(
            'timetable', 'sections', 'sessions', 'terms', 'classes', 'subjects'
        ));
    }

    public function update(Request $request, $id)
    {
        $timetable = Timetable::findOrFail($id);

        $request->validate([
            'section_id'       => 'required|exists:sections,id',
            'session_id'       => 'required|exists:school_sessions,id',
            'term_id'          => 'required|exists:terms,id',
            'num_periods'      => 'required|integer|min:1|max:12',
            'lesson_duration'  => 'required|integer|min:10|max:120',
            'break_duration'   => 'required|integer|min:5|max:60',
            'break_period'     => 'required|integer|min:1|max:12',
            'has_free_periods' => 'required|boolean',
            'schedule'         => 'required|array',
            'periods_monday'   => 'nullable|integer|min:1|max:12',
            'periods_tuesday'  => 'nullable|integer|min:1|max:12',
            'periods_wednesday'=> 'nullable|integer|min:1|max:12',
            'periods_thursday' => 'nullable|integer|min:1|max:12',
            'periods_friday'   => 'nullable|integer|min:1|max:12',
        ]);

        $existingTimetable = Timetable::where('section_id', $request->section_id)
            ->where('term_id', $request->term_id)
            ->where('id', '!=', $id)
            ->first();

        if ($existingTimetable) {
            return redirect()->back()
                ->with('error', 'A timetable already exists for this section and term.')
                ->withInput();
        }

        // Validate session exists (school-wide — no section check)
        $session = Session::findOrFail($request->session_id);

        $term = Term::where('id', $request->term_id)
            ->where('session_id', $request->session_id)
            ->firstOrFail();

        $classes  = SchoolClass::where('section_id', $request->section_id)->pluck('id')->toArray();
        $subjects = Course::where('section_id', $request->section_id)->pluck('id')->toArray();

        $dayPeriods = [
            'Monday'    => $request->periods_monday    ?? $request->num_periods,
            'Tuesday'   => $request->periods_tuesday   ?? $request->num_periods,
            'Wednesday' => $request->periods_wednesday ?? $request->num_periods,
            'Thursday'  => $request->periods_thursday  ?? $request->num_periods,
            'Friday'    => $request->periods_friday    ?? $request->num_periods,
        ];

        [$validatedSchedule, $conflictErrors] = $this->buildSchedule(
            $request->schedule, $dayPeriods, $classes, $subjects
        );

        $timetable->update([
            'section_id'      => $request->section_id,
            'session_id'      => $request->session_id,
            'term_id'         => $request->term_id,
            'num_periods'     => $request->num_periods,
            'lesson_duration' => $request->lesson_duration,
            'break_duration'  => $request->break_duration,
            'break_period'    => $request->break_period,
            'has_free_periods'=> $request->has_free_periods,
            'day_periods'     => $dayPeriods,
            'schedule'        => $validatedSchedule,
            'has_conflicts'   => !empty($conflictErrors),
            'conflicts'       => !empty($conflictErrors) ? $conflictErrors : null,
        ]);

        if (!empty($conflictErrors)) {
            return redirect()->route('timetables.index')
                ->with('warning', 'Timetable updated with ' . count($conflictErrors) . ' conflict(s).')
                ->with('conflicts', $conflictErrors);
        }

        return redirect()->route('timetables.index')
            ->with('success', 'Master timetable updated successfully.');
    }

    public function destroy($id)
    {
        Timetable::findOrFail($id)->delete();
        return redirect()->route('timetables.index')->with('success', 'Timetable deleted successfully.');
    }

    public function export($id)
    {
        $timetable = Timetable::findOrFail($id);
        $classes   = SchoolClass::where('section_id', $timetable->section_id)->orderBy('name')->get();
        $subjects  = Course::where('section_id', $timetable->section_id)->get()->keyBy('id');

        return Excel::download(
            new TimetableExport($timetable, $classes, $subjects),
            'timetable_' . $timetable->section->section_name . '_' . $timetable->term->name . '.xlsx'
        );
    }

    // ── Student timetable ────────────────────────────────────────────────────

    public function myTimetable()
    {
        $user = Auth::user();

        if ($user->user_type != 4) {
            return redirect()->back()->with('error', 'Access denied. This page is only for students.');
        }

        if (!$user->class_id) {
            return redirect()->back()->with('error', 'Your class has not been assigned yet.');
        }

        $schoolClass = SchoolClass::find($user->class_id);
        if (!$schoolClass) {
            return redirect()->back()->with('error', 'Your class information is invalid.');
        }

        // School-wide current session — no section filter
        $currentSession = Session::where('is_current', 1)->first();

        if (!$currentSession) {
            return redirect()->back()->with('error', 'No active session found. Please contact your administrator.');
        }

        $currentTerm = Term::where('session_id', $currentSession->id)
            ->where('is_current', 1)
            ->first();

        if (!$currentTerm) {
            return redirect()->back()->with('error', 'No active term found. Please contact your administrator.');
        }

        $timetable = Timetable::where('section_id', $schoolClass->section_id)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->with(['section', 'session', 'term'])
            ->first();

        if (!$timetable) {
            return redirect()->back()->with('error', 'No timetable has been created for your class yet.');
        }

        $student = (object) [
            'id'          => $user->id,
            'name'        => $user->name,
            'admission_no'=> $user->admission_no,
            'class_id'    => $user->class_id,
            'section_id'  => $schoolClass->section_id,
            'schoolClass' => $user->schoolClass,
            'user'        => $user,
        ];

        return view('students.timetable', compact('timetable', 'student'));
    }

    // ── Teacher schedule ─────────────────────────────────────────────────────

    public function myTeachingSchedule()
    {
        $teacher = Auth::user();

        $allowedRoles = [1, 2, 3, 7, 8, 9, 10];
        if (!in_array($teacher->user_type, $allowedRoles)) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $assignedClassIds = DB::table('class_user')
            ->where('user_id', $teacher->id)
            ->pluck('school_class_id')
            ->toArray();

        if (empty($assignedClassIds)) {
            return redirect()->back()->with('error', 'You have not been assigned to any classes yet.');
        }

        // School-wide current session
        $currentSession = Session::where('is_current', 1)->first();

        if (!$currentSession) {
            return redirect()->back()->with('error', 'No active session found.');
        }

        $currentTerm = Term::where('session_id', $currentSession->id)
            ->where('is_current', 1)
            ->first();

        if (!$currentTerm) {
            return redirect()->back()->with('error', 'No active term found.');
        }

        // Get sections from assigned classes
        $sectionIds = SchoolClass::whereIn('id', $assignedClassIds)
            ->pluck('section_id')
            ->unique()
            ->toArray();

        // Timetables for those sections under the current session/term
        $timetables = Timetable::whereIn('section_id', $sectionIds)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->with(['section', 'session', 'term'])
            ->get();

        $teacherSubjects = DB::table('course_user')
            ->where('user_id', $teacher->id)
            ->pluck('course_id')
            ->toArray();

        $teachingSchedule = $this->extractTeachingSchedule(
            $timetables, $assignedClassIds, $teacherSubjects
        );

        if (empty($teachingSchedule)) {
            return redirect()->back()->with('error', 'No teaching schedule found. You may not have been assigned any subjects yet.');
        }

        return view('teachers.teaching_schedule', compact('teachingSchedule', 'teacher'));
    }

    // ── Admin: all teachers' schedules ───────────────────────────────────────

    public function allTeachingSchedules(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->user_type, [1, 2])) {
            abort(403, 'Unauthorized access.');
        }

        $sections          = Section::all();
        $selectedSectionId = $request->get('section_id');

        $teachersQuery = \App\Models\User::whereIn('user_type', [3, 7, 8, 9, 10])->orderBy('name');

        if ($selectedSectionId) {
            $teachersQuery->whereHas('classes', function ($q) use ($selectedSectionId) {
                $q->where('section_id', $selectedSectionId);
            });
        }

        $teachers           = $teachersQuery->get();
        $selectedTeacherId  = $request->get('teacher_id');

        if ($selectedSectionId && !$selectedTeacherId && $teachers->isNotEmpty()) {
            $selectedTeacherId = $teachers->first()->id;
        }

        $teacher          = null;
        $teachingSchedule = [];

        if ($selectedTeacherId) {
            $teacher = \App\Models\User::find($selectedTeacherId);

            if ($teacher) {
                $assignedClassIds = DB::table('class_user')
                    ->where('user_id', $teacher->id)
                    ->pluck('school_class_id')
                    ->toArray();

                if (!empty($assignedClassIds)) {

                    // School-wide current session
                    $currentSession = Session::where('is_current', 1)->first();

                    if ($currentSession) {
                        $currentTerm = Term::where('session_id', $currentSession->id)
                            ->where('is_current', 1)
                            ->first();

                        if ($currentTerm) {
                            $sectionIds = SchoolClass::whereIn('id', $assignedClassIds)
                                ->pluck('section_id')
                                ->unique()
                                ->toArray();

                            if ($selectedSectionId) {
                                $sectionIds = array_values(array_intersect($sectionIds, [$selectedSectionId]));
                            }

                            $timetables = Timetable::whereIn('section_id', $sectionIds)
                                ->where('session_id', $currentSession->id)
                                ->where('term_id', $currentTerm->id)
                                ->with(['section', 'session', 'term'])
                                ->get();

                            $teacherSubjects = DB::table('course_user')
                                ->where('user_id', $teacher->id)
                                ->pluck('course_id')
                                ->toArray();

                            $teachingSchedule = $this->extractTeachingSchedule(
                                $timetables, $assignedClassIds, $teacherSubjects
                            );
                        }
                    }
                }
            }
        }

        return view('admin.all_teaching_schedules', compact(
            'sections', 'teachers', 'teacher',
            'teachingSchedule', 'selectedSectionId', 'selectedTeacherId'
        ));
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Shared schedule building logic for store() and update().
     */
    private function buildSchedule(array $scheduleInput, array $dayPeriods, array $classes, array $subjects): array
    {
        $validatedSchedule = [];
        $conflictErrors    = [];

        foreach ($scheduleInput as $day => $dayData) {
            if (!in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])) {
                continue;
            }

            $validatedSchedule[$day] = [];
            $maxPeriods = $dayPeriods[$day];

            if (isset($dayData['break'])) {
                $validatedSchedule[$day]['break'] = [];
                foreach ($dayData['break'] as $classId => $hasBreak) {
                    if (in_array($classId, $classes)) {
                        $validatedSchedule[$day]['break'][$classId] = (bool) $hasBreak;
                    }
                }
            }

            foreach ($dayData as $period => $periodData) {
                if ($period === 'break') continue;
                if (!is_numeric($period) || $period < 1 || $period > $maxPeriods) continue;

                $validatedSchedule[$day][$period] = [];
                $periodSubjects = [];

                foreach ($periodData as $classId => $courseId) {
                    if (!in_array($classId, $classes)) {
                        $conflictErrors[] = "Invalid class ID {$classId} for {$day}, Period {$period}";
                        continue;
                    }

                    if ($courseId && $courseId !== 'free' && $courseId !== '') {
                        if (!in_array($courseId, $subjects)) {
                            $conflictErrors[] = "Invalid subject ID {$courseId} for {$day}, Period {$period}";
                            continue;
                        }
                        if (isset($periodSubjects[$courseId])) {
                            $conflictErrors[] = "Conflict: Subject {$courseId} assigned to multiple classes in {$day}, Period {$period}";
                        }
                        $periodSubjects[$courseId] = $classId;
                    }

                    $validatedSchedule[$day][$period][$classId] = $courseId ?: null;
                }
            }
        }

        return [$validatedSchedule, $conflictErrors];
    }

    /**
     * Shared schedule extraction for myTeachingSchedule() and allTeachingSchedules().
     */
    private function extractTeachingSchedule($timetables, array $assignedClassIds, array $teacherSubjects): array
    {
        $teachingSchedule = [];

        foreach ($timetables as $timetable) {
            if (!$timetable->schedule) continue;

            $schedule = is_array($timetable->schedule)
                ? $timetable->schedule
                : json_decode($timetable->schedule, true);

            $classes  = SchoolClass::where('section_id', $timetable->section_id)
                ->whereIn('id', $assignedClassIds)
                ->get()->keyBy('id');

            $subjects = Course::where('section_id', $timetable->section_id)
                ->get()->keyBy('id');

            $startTime = 8 * 60; // 8:00 AM

            foreach ($schedule as $day => $periods) {
                if (!in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])) continue;

                $currentTime   = $startTime;
                $periodCounter = 0;
                $maxPeriods    = is_array($timetable->day_periods) && isset($timetable->day_periods[$day])
                    ? $timetable->day_periods[$day]
                    : $timetable->num_periods;

                for ($p = 1; $p <= $maxPeriods + 1; $p++) {
                    if ($p == $timetable->break_period) {
                        $currentTime += $timetable->break_duration;
                        continue;
                    }

                    $periodCounter++;
                    if ($periodCounter > $maxPeriods) break;
                    if (!isset($periods[$periodCounter])) {
                        $currentTime += $timetable->lesson_duration;
                        continue;
                    }

                    foreach ($periods[$periodCounter] as $classId => $subjectId) {
                        if ($subjectId
                            && in_array($subjectId, $teacherSubjects)
                            && in_array($classId, $assignedClassIds)
                        ) {
                            $subject = $subjects->get($subjectId);
                            $class   = $classes->get($classId);

                            if ($subject && $class) {
                                $teachingSchedule[] = [
                                    'day'        => $day,
                                    'time'       => date('h:i A', mktime(0, $currentTime))
                                                  . ' - '
                                                  . date('h:i A', mktime(0, $currentTime + $timetable->lesson_duration)),
                                    'start_time' => $currentTime,
                                    'subject'    => $subject->course_name,
                                    'class'      => $class->name,
                                    'section'    => $timetable->section->section_name,
                                    'period'     => $periodCounter,
                                    'class_id'   => $classId,
                                    'subject_id' => $subjectId,
                                ];
                            }
                        }
                    }

                    $currentTime += $timetable->lesson_duration;
                }
            }
        }

        return $teachingSchedule;
    }
}