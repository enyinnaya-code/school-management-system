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

        // Apply filters
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
        $sections = Section::all();
        $sessions = Session::all();
        $terms = Term::all();

        return view('timetables_index', compact('timetables', 'sections', 'sessions', 'terms'));
    }

    public function create()
    {
        $sections = Section::all();
        $sessions = collect([]);
        $terms = collect([]);
        $current_session_id = null;
        $current_term_id = null;

        return view('create_timetable', compact('sections', 'sessions', 'terms', 'current_session_id', 'current_term_id'));
    }

    public function getSessions($sectionId)
    {
        $sessions = Session::where('section_id', $sectionId)
            ->select('id', 'name', 'is_current')
            ->get();

        $currentSession = $sessions->firstWhere('is_current', true);

        return response()->json([
            'sessions' => $sessions,
            'current_session_id' => $currentSession ? $currentSession->id : null
        ]);
    }

    public function getTerms($sessionId)
    {
        $terms = Term::where('session_id', $sessionId)
            ->select('id', 'name', 'is_current')
            ->get();

        $currentTerm = $terms->firstWhere('is_current', true);

        return response()->json([
            'terms' => $terms,
            'current_term_id' => $currentTerm ? $currentTerm->id : null
        ]);
    }

    public function getClassesAndSubjects($sectionId)
    {
        $classes = SchoolClass::where('section_id', $sectionId)
            ->select('id', 'name')
            ->get();

        $subjects = Course::where('section_id', $sectionId)
            ->select('id', 'course_name')
            ->get();

        return response()->json([
            'classes' => $classes,
            'subjects' => $subjects
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'num_periods' => 'required|integer|min:1|max:12',
            'lesson_duration' => 'required|integer|min:10|max:120',
            'break_duration' => 'required|integer|min:5|max:60',
            'break_period' => 'required|integer|min:1|max:12',
            'has_free_periods' => 'required|boolean',
            'schedule' => 'required|array',
            'periods_monday' => 'nullable|integer|min:1|max:12',
            'periods_tuesday' => 'nullable|integer|min:1|max:12',
            'periods_wednesday' => 'nullable|integer|min:1|max:12',
            'periods_thursday' => 'nullable|integer|min:1|max:12',
            'periods_friday' => 'nullable|integer|min:1|max:12',
        ]);

        // Check if timetable already exists for this section and term
        $existingTimetable = Timetable::where('section_id', $request->section_id)
            ->where('term_id', $request->term_id)
            ->first();

        if ($existingTimetable) {
            return redirect()->back()
                ->with('error', 'A timetable already exists for this section and term. Please edit the existing timetable or choose a different term.')
                ->withInput();
        }

        // Validate session belongs to section
        $session = Session::where('id', $request->session_id)
            ->where('section_id', $request->section_id)
            ->firstOrFail();

        // Validate term belongs to session
        $term = Term::where('id', $request->term_id)
            ->where('session_id', $request->session_id)
            ->firstOrFail();

        // Get all valid classes and subjects for this section
        $classes = SchoolClass::where('section_id', $request->section_id)
            ->pluck('id')
            ->toArray();
        $subjects = Course::where('section_id', $request->section_id)
            ->pluck('id')
            ->toArray();

        // Build day-specific periods configuration
        $dayPeriods = [
            'Monday' => $request->periods_monday ?? $request->num_periods,
            'Tuesday' => $request->periods_tuesday ?? $request->num_periods,
            'Wednesday' => $request->periods_wednesday ?? $request->num_periods,
            'Thursday' => $request->periods_thursday ?? $request->num_periods,
            'Friday' => $request->periods_friday ?? $request->num_periods,
        ];

        // Validate and restructure schedule data
        $validatedSchedule = [];
        $conflictErrors = [];

        foreach ($request->schedule as $day => $dayData) {
            if (!in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])) {
                continue;
            }

            $validatedSchedule[$day] = [];
            $maxPeriods = $dayPeriods[$day];

            // Process break data if present
            if (isset($dayData['break'])) {
                $validatedSchedule[$day]['break'] = [];
                foreach ($dayData['break'] as $classId => $hasBreak) {
                    if (in_array($classId, $classes)) {
                        $validatedSchedule[$day]['break'][$classId] = (bool) $hasBreak;
                    }
                }
            }

            // Process period data
            foreach ($dayData as $period => $periodData) {
                if ($period === 'break') {
                    continue;
                }

                if (!is_numeric($period) || $period < 1 || $period > $maxPeriods) {
                    continue;
                }

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
                            $conflictErrors[] = "Conflict detected: Subject ID {$courseId} is assigned to multiple classes in {$day}, Period {$period}";
                        }
                        $periodSubjects[$courseId] = $classId;
                    }

                    $validatedSchedule[$day][$period][$classId] = $courseId ?: null;
                }
            }
        }

        if (!empty($conflictErrors)) {
            session()->flash('warning', 'Timetable created with conflicts. Please review and resolve them.');
            session()->flash('conflicts', $conflictErrors);
        }

        // Create the timetable
        $timetable = Timetable::create([
            'section_id' => $request->section_id,
            'session_id' => $request->session_id,
            'term_id' => $request->term_id,
            'num_periods' => $request->num_periods,
            'lesson_duration' => $request->lesson_duration,
            'break_duration' => $request->break_duration,
            'break_period' => $request->break_period,
            'has_free_periods' => $request->has_free_periods,
            'day_periods' => $dayPeriods,
            'schedule' => $validatedSchedule,
            'has_conflicts' => !empty($conflictErrors),
            'conflicts' => !empty($conflictErrors) ? $conflictErrors : null,
            'created_by' => Auth::id(),
        ]);

        if (!empty($conflictErrors)) {
            return redirect()->route('timetables.index')
                ->with('warning', 'Master timetable created with ' . count($conflictErrors) . ' conflict(s). Please review and resolve them.')
                ->with('conflicts', $conflictErrors);
        }

        return redirect()->route('timetables.index')->with('success', 'Master timetable created successfully with no conflicts.');
    }

    public function show($id, Request $request)
    {
        $timetable = Timetable::with(['section', 'session', 'term', 'createdBy'])->findOrFail($id);

        // Get all classes for the filter dropdown
        $allClasses = SchoolClass::where('section_id', $timetable->section_id)
            ->orderBy('name')
            ->get();

        // Get classes for display (filtered or all)
        $classesQuery = SchoolClass::where('section_id', $timetable->section_id)
            ->orderBy('name');

        // Apply filter if class_filter is provided
        if ($request->has('class_filter') && $request->class_filter != '') {
            $classesQuery->where('id', $request->class_filter);
        }

        $classes = $classesQuery->get();

        // Get subjects
        $subjects = Course::where('section_id', $timetable->section_id)
            ->get()
            ->keyBy('id');

        return view('timetables_show', compact('timetable', 'classes', 'subjects', 'allClasses'));
    }

    public function edit($id)
    {
        $timetable = Timetable::findOrFail($id);
        $sections = Section::all();
        $sessions = Session::where('section_id', $timetable->section_id)->get();
        $terms = Term::where('session_id', $timetable->session_id)->get();
        $classes = SchoolClass::where('section_id', $timetable->section_id)->orderBy('name')->get();
        $subjects = Course::where('section_id', $timetable->section_id)->get();

        return view('timetables_edit', compact('timetable', 'sections', 'sessions', 'terms', 'classes', 'subjects'));
    }

    public function update(Request $request, $id)
    {
        $timetable = Timetable::findOrFail($id);

        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'num_periods' => 'required|integer|min:1|max:12',
            'lesson_duration' => 'required|integer|min:10|max:120',
            'break_duration' => 'required|integer|min:5|max:60',
            'break_period' => 'required|integer|min:1|max:12',
            'has_free_periods' => 'required|boolean',
            'schedule' => 'required|array',
            'periods_monday' => 'nullable|integer|min:1|max:12',
            'periods_tuesday' => 'nullable|integer|min:1|max:12',
            'periods_wednesday' => 'nullable|integer|min:1|max:12',
            'periods_thursday' => 'nullable|integer|min:1|max:12',
            'periods_friday' => 'nullable|integer|min:1|max:12',
        ]);

        // Check if another timetable exists for this section and term (excluding current one)
        $existingTimetable = Timetable::where('section_id', $request->section_id)
            ->where('term_id', $request->term_id)
            ->where('id', '!=', $id)
            ->first();

        if ($existingTimetable) {
            return redirect()->back()
                ->with('error', 'A timetable already exists for this section and term. Please choose a different term.')
                ->withInput();
        }

        // Validate session and term
        $session = Session::where('id', $request->session_id)
            ->where('section_id', $request->section_id)
            ->firstOrFail();

        $term = Term::where('id', $request->term_id)
            ->where('session_id', $request->session_id)
            ->firstOrFail();

        $classes = SchoolClass::where('section_id', $request->section_id)
            ->pluck('id')
            ->toArray();
        $subjects = Course::where('section_id', $request->section_id)
            ->pluck('id')
            ->toArray();

        $dayPeriods = [
            'Monday' => $request->periods_monday ?? $request->num_periods,
            'Tuesday' => $request->periods_tuesday ?? $request->num_periods,
            'Wednesday' => $request->periods_wednesday ?? $request->num_periods,
            'Thursday' => $request->periods_thursday ?? $request->num_periods,
            'Friday' => $request->periods_friday ?? $request->num_periods,
        ];

        $validatedSchedule = [];
        $conflictErrors = [];

        foreach ($request->schedule as $day => $dayData) {
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
                if ($period === 'break') {
                    continue;
                }

                if (!is_numeric($period) || $period < 1 || $period > $maxPeriods) {
                    continue;
                }

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
                            $conflictErrors[] = "Conflict detected: Subject ID {$courseId} is assigned to multiple classes in {$day}, Period {$period}";
                        }
                        $periodSubjects[$courseId] = $classId;
                    }

                    $validatedSchedule[$day][$period][$classId] = $courseId ?: null;
                }
            }
        }

        if (!empty($conflictErrors)) {
            $timetable->update([
                'section_id' => $request->section_id,
                'session_id' => $request->session_id,
                'term_id' => $request->term_id,
                'num_periods' => $request->num_periods,
                'lesson_duration' => $request->lesson_duration,
                'break_duration' => $request->break_duration,
                'break_period' => $request->break_period,
                'has_free_periods' => $request->has_free_periods,
                'day_periods' => $dayPeriods,
                'schedule' => $validatedSchedule,
                'has_conflicts' => true,
                'conflicts' => $conflictErrors,
            ]);

            return redirect()->route('timetables.index')
                ->with('warning', 'Master timetable updated with ' . count($conflictErrors) . ' conflict(s). Please review and resolve them.')
                ->with('conflicts', $conflictErrors);
        }

        $timetable->update([
            'section_id' => $request->section_id,
            'session_id' => $request->session_id,
            'term_id' => $request->term_id,
            'num_periods' => $request->num_periods,
            'lesson_duration' => $request->lesson_duration,
            'break_duration' => $request->break_duration,
            'break_period' => $request->break_period,
            'has_free_periods' => $request->has_free_periods,
            'day_periods' => $dayPeriods,
            'schedule' => $validatedSchedule,
            'has_conflicts' => false,
            'conflicts' => null,
        ]);

        return redirect()->route('timetables.index')->with('success', 'Master timetable updated successfully with no conflicts.');
    }

    public function destroy($id)
    {
        $timetable = Timetable::findOrFail($id);
        $timetable->delete();

        return redirect()->route('timetables.index')->with('success', 'Timetable deleted successfully.');
    }

    public function export($id)
    {
        $timetable = Timetable::findOrFail($id);
        $classes = SchoolClass::where('section_id', $timetable->section_id)
            ->orderBy('name')
            ->get();
        $subjects = Course::where('section_id', $timetable->section_id)
            ->get()
            ->keyBy('id');

        return Excel::download(new TimetableExport($timetable, $classes, $subjects), 'timetable_' . $timetable->section->section_name . '_' . $timetable->term->name . '.xlsx');
    }


    /**
     * Display student's timetable
     */
    public function myTimetable()
    {
        $user = Auth::user();

        // Check if user is a student (user_type = 4)
        if ($user->user_type != 4) {
            return redirect()->back()->with('error', 'Access denied. This page is only for students.');
        }

        // Check if student has class assigned
        if (!$user->class_id) {
            return redirect()->back()->with('error', 'Your class has not been assigned yet. Please contact your administrator.');
        }

        // Get the section_id from the user's class
        $schoolClass = \App\Models\SchoolClass::find($user->class_id);

        if (!$schoolClass) {
            return redirect()->back()->with('error', 'Your class information is invalid. Please contact your administrator.');
        }

        $sectionId = $schoolClass->section_id;

        // Get current session for the student's section where is_current = 1
        $currentSession = \App\Models\Session::where('section_id', $sectionId)
            ->where('is_current', 1)
            ->first();

        if (!$currentSession) {
            return redirect()->back()->with('error', 'No active session found for your section. Please contact your administrator.');
        }

        // Get current term for the session where is_current = 1
        $currentTerm = \App\Models\Term::where('session_id', $currentSession->id)
            ->where('is_current', 1)
            ->first();

        if (!$currentTerm) {
            return redirect()->back()->with('error', 'No active term found for the current session. Please contact your administrator.');
        }

        // Get timetable for student's section and current term
        $timetable = Timetable::where('section_id', $sectionId)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->with(['section', 'session', 'term'])
            ->first();

        if (!$timetable) {
            return redirect()->back()->with('error', 'No timetable has been created for your class yet. Please contact your administrator.');
        }

        // Prepare student data for the view
        $student = (object) [
            'id' => $user->id,
            'name' => $user->name,
            'admission_no' => $user->admission_no,
            'class_id' => $user->class_id,
            'section_id' => $sectionId,
            'schoolClass' => $user->schoolClass, // Relationship to SchoolClass
            'user' => $user
        ];

        return view('students.timetable', compact('timetable', 'student'));
    }


    /**
     * Display teacher's teaching schedule
     */
    public function myTeachingSchedule()
    {
        $teacher = Auth::user();

        // Check if user is a teacher (user_type = 3)
        $allowedTeachingRoles = [1, 2, 3, 7, 8, 9, 10]; 

        if (!in_array($teacher->user_type, $allowedTeachingRoles)) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        // Get classes assigned to this teacher from class_user table
        $assignedClassIds = DB::table('class_user')
            ->where('user_id', $teacher->id)
            ->pluck('school_class_id')
            ->toArray();

        if (empty($assignedClassIds)) {
            return redirect()->back()->with('error', 'You have not been assigned to any classes yet. Please contact your administrator.');
        }

        // Get section_ids from the assigned classes
        $sectionIds = \App\Models\SchoolClass::whereIn('id', $assignedClassIds)
            ->pluck('section_id')
            ->unique()
            ->toArray();

        // Get current sessions for these sections where is_current = 1
        $currentSessions = \App\Models\Session::whereIn('section_id', $sectionIds)
            ->where('is_current', 1)
            ->get()
            ->keyBy('section_id');

        if ($currentSessions->isEmpty()) {
            return redirect()->back()->with('error', 'No active session found for your assigned classes. Please contact your administrator.');
        }

        // Get current terms for these sessions where is_current = 1
        $sessionIds = $currentSessions->pluck('id')->toArray();
        $currentTerms = \App\Models\Term::whereIn('session_id', $sessionIds)
            ->where('is_current', 1)
            ->get()
            ->keyBy('session_id');

        if ($currentTerms->isEmpty()) {
            return redirect()->back()->with('error', 'No active term found for the current sessions. Please contact your administrator.');
        }

        // Get all timetables for current sessions and terms
        $timetables = Timetable::whereIn('session_id', $sessionIds)
            ->whereIn('term_id', $currentTerms->pluck('id')->toArray())
            ->with(['section', 'session', 'term'])
            ->get();

        // Get subjects taught by this teacher from course_user pivot table
        $teacherSubjects = DB::table('course_user')
            ->where('user_id', $teacher->id)
            ->pluck('course_id')
            ->toArray();

        $teachingSchedule = [];

        foreach ($timetables as $timetable) {
            if (!$timetable->schedule) {
                continue;
            }

            $schedule = is_array($timetable->schedule) ? $timetable->schedule : json_decode($timetable->schedule, true);

            // Get class information
            $classes = \App\Models\SchoolClass::where('section_id', $timetable->section_id)
                ->whereIn('id', $assignedClassIds) // Only get classes assigned to this teacher
                ->get()
                ->keyBy('id');

            // Get subjects information
            $subjects = \App\Models\Course::where('section_id', $timetable->section_id)
                ->get()
                ->keyBy('id');

            // Calculate time slots
            $startTime = 8 * 60; // 8:00 AM in minutes

            // Loop through each day
            foreach ($schedule as $day => $periods) {
                if (!in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])) {
                    continue;
                }

                $currentTime = $startTime; // Reset for each day
                $periodCounter = 0;

                // Get max periods for the day
                $maxPeriods = is_array($timetable->day_periods) && isset($timetable->day_periods[$day])
                    ? $timetable->day_periods[$day]
                    : $timetable->num_periods;

                // Loop through periods
                for ($p = 1; $p <= $maxPeriods + 1; $p++) {
                    // Check if it's break period
                    if ($p == $timetable->break_period) {
                        $currentTime += $timetable->break_duration;
                        continue;
                    }

                    $periodCounter++;

                    if ($periodCounter > $maxPeriods) {
                        break;
                    }

                    // Check if this period exists in schedule
                    if (!isset($periods[$periodCounter])) {
                        $currentTime += $timetable->lesson_duration;
                        continue;
                    }

                    // Loop through classes in this period
                    foreach ($periods[$periodCounter] as $classId => $subjectId) {
                        // Check if this is a class assigned to the teacher AND subject taught by them
                        if ($subjectId && in_array($subjectId, $teacherSubjects) && in_array($classId, $assignedClassIds)) {
                            $subject = $subjects->get($subjectId);
                            $class = $classes->get($classId);

                            if ($subject && $class) {
                                $startTimeFormatted = date('h:i A', mktime(0, $currentTime));
                                $endTimeFormatted = date('h:i A', mktime(0, $currentTime + $timetable->lesson_duration));

                                $teachingSchedule[] = [
                                    'day' => $day,
                                    'time' => $startTimeFormatted . ' - ' . $endTimeFormatted,
                                    'subject' => $subject->course_name,
                                    'class' => $class->name,
                                    'section' => $timetable->section->section_name,
                                    'period' => $periodCounter,
                                    'class_id' => $classId,
                                    'subject_id' => $subjectId,
                                ];
                            }
                        }
                    }

                    $currentTime += $timetable->lesson_duration;
                }
            }
        }

        if (empty($teachingSchedule)) {
            return redirect()->back()->with('error', 'No teaching schedule found. You may not have been assigned any subjects yet.');
        }

        return view('teachers.teaching_schedule', compact('teachingSchedule', 'teacher'));
    }


    /**
 * Display all teachers' teaching schedules (Admin view)
 */
public function allTeachingSchedules(Request $request)
{
    $user = Auth::user();

    // Only admins can access this
    if (!in_array($user->user_type, [1, 2])) {
        abort(403, 'Unauthorized access.');
    }

    // Get all sections
    $sections = Section::all();
    $selectedSectionId = $request->get('section_id');

    // Get teachers based on section filter
    $teachersQuery = \App\Models\User::whereIn('user_type', [3, 7, 8, 9, 10])
        ->orderBy('name');

    // Filter teachers by section if selected
    if ($selectedSectionId) {
        $teachersQuery->whereHas('classes', function($query) use ($selectedSectionId) {
            $query->where('section_id', $selectedSectionId);
        });
    }

    $teachers = $teachersQuery->get();

    // Get selected teacher from request
    $selectedTeacherId = $request->get('teacher_id');
    
    // Auto-select first teacher if section is selected but no teacher is selected
    if ($selectedSectionId && !$selectedTeacherId && $teachers->isNotEmpty()) {
        $selectedTeacherId = $teachers->first()->id;
    }

    $teacher = null;
    $teachingSchedule = [];

    if ($selectedTeacherId) {
        $teacher = \App\Models\User::find($selectedTeacherId);

        if ($teacher) {
            // Get classes assigned to this teacher from class_user table
            $assignedClassIds = DB::table('class_user')
                ->where('user_id', $teacher->id)
                ->pluck('school_class_id')
                ->toArray();

            if (!empty($assignedClassIds)) {
                // Get section_ids from the assigned classes
                $sectionIds = \App\Models\SchoolClass::whereIn('id', $assignedClassIds)
                    ->pluck('section_id')
                    ->unique()
                    ->toArray();

                // If section filter is applied, only get that section's data
                if ($selectedSectionId) {
                    $sectionIds = array_intersect($sectionIds, [$selectedSectionId]);
                }

                // Get current sessions for these sections where is_current = 1
                $currentSessions = \App\Models\Session::whereIn('section_id', $sectionIds)
                    ->where('is_current', 1)
                    ->get()
                    ->keyBy('section_id');

                if ($currentSessions->isNotEmpty()) {
                    // Get current terms for these sessions where is_current = 1
                    $sessionIds = $currentSessions->pluck('id')->toArray();
                    $currentTerms = \App\Models\Term::whereIn('session_id', $sessionIds)
                        ->where('is_current', 1)
                        ->get()
                        ->keyBy('session_id');

                    if ($currentTerms->isNotEmpty()) {
                        // Get all timetables for current sessions and terms
                        $timetables = Timetable::whereIn('session_id', $sessionIds)
                            ->whereIn('term_id', $currentTerms->pluck('id')->toArray())
                            ->with(['section', 'session', 'term'])
                            ->get();

                        // Get subjects taught by this teacher from course_user pivot table
                        $teacherSubjects = DB::table('course_user')
                            ->where('user_id', $teacher->id)
                            ->pluck('course_id')
                            ->toArray();

                        foreach ($timetables as $timetable) {
                            if (!$timetable->schedule) {
                                continue;
                            }

                            $schedule = is_array($timetable->schedule) ? $timetable->schedule : json_decode($timetable->schedule, true);

                            // Get class information
                            $classes = \App\Models\SchoolClass::where('section_id', $timetable->section_id)
                                ->whereIn('id', $assignedClassIds)
                                ->get()
                                ->keyBy('id');

                            // Get subjects information
                            $subjects = \App\Models\Course::where('section_id', $timetable->section_id)
                                ->get()
                                ->keyBy('id');

                            // Calculate time slots
                            $startTime = 8 * 60; // 8:00 AM in minutes

                            // Loop through each day
                            foreach ($schedule as $day => $periods) {
                                if (!in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])) {
                                    continue;
                                }

                                $currentTime = $startTime;
                                $periodCounter = 0;

                                // Get max periods for the day
                                $maxPeriods = is_array($timetable->day_periods) && isset($timetable->day_periods[$day])
                                    ? $timetable->day_periods[$day]
                                    : $timetable->num_periods;

                                // Loop through periods
                                for ($p = 1; $p <= $maxPeriods + 1; $p++) {
                                    // Check if it's break period
                                    if ($p == $timetable->break_period) {
                                        $currentTime += $timetable->break_duration;
                                        continue;
                                    }

                                    $periodCounter++;

                                    if ($periodCounter > $maxPeriods) {
                                        break;
                                    }

                                    // Check if this period exists in schedule
                                    if (!isset($periods[$periodCounter])) {
                                        $currentTime += $timetable->lesson_duration;
                                        continue;
                                    }

                                    // Loop through classes in this period
                                    foreach ($periods[$periodCounter] as $classId => $subjectId) {
                                        // Check if this is a class assigned to the teacher AND subject taught by them
                                        if ($subjectId && in_array($subjectId, $teacherSubjects) && in_array($classId, $assignedClassIds)) {
                                            $subject = $subjects->get($subjectId);
                                            $class = $classes->get($classId);

                                            if ($subject && $class) {
                                                $startTimeFormatted = date('h:i A', mktime(0, $currentTime));
                                                $endTimeFormatted = date('h:i A', mktime(0, $currentTime + $timetable->lesson_duration));

                                                $teachingSchedule[] = [
                                                    'day' => $day,
                                                    'time' => $startTimeFormatted . ' - ' . $endTimeFormatted,
                                                    'start_time' => $currentTime,
                                                    'subject' => $subject->course_name,
                                                    'class' => $class->name,
                                                    'section' => $timetable->section->section_name,
                                                    'period' => $periodCounter,
                                                    'class_id' => $classId,
                                                    'subject_id' => $subjectId,
                                                ];
                                            }
                                        }
                                    }

                                    $currentTime += $timetable->lesson_duration;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return view('admin.all_teaching_schedules', compact('sections', 'teachers', 'teacher', 'teachingSchedule', 'selectedSectionId', 'selectedTeacherId'));
}
}


