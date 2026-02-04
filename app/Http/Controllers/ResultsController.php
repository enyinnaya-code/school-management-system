<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Course;
use App\Models\Result;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Session;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClassResultsExport;
use App\Models\StudentRemark;
use App\Exports\MasterListExport;


class ResultsController extends Controller
{
    /**
     * Check if teacher is assigned to a specific class
     * Checks both class_user and course_user tables
     */
    private function isTeacherAssignedToClass($userId, $classId)
    {
        // Check direct class assignment
        $isAssignedToClass = DB::table('class_user')
            ->where('user_id', $userId)
            ->where('school_class_id', $classId)
            ->exists();

        // Check course assignment
        $isAssignedToCourse = DB::table('course_user')
            ->where('user_id', $userId)
            ->where(function ($query) use ($classId) {
                $query->where('class_id', $classId)
                    ->orWhereNull('class_id');
            })
            ->exists();

        return $isAssignedToClass || $isAssignedToCourse;
    }

    private function getAllowedSections()
    {
        $user = Auth::user();

        // Admins & Super Admins see all
        if (in_array($user->user_type, [1, 2])) {
            return Section::all();
        }

        // Get section IDs where teacher has class assignments (via class_user)
        $sectionsFromClassUser = DB::table('class_user')
            ->join('school_classes', 'class_user.school_class_id', '=', 'school_classes.id')
            ->where('class_user.user_id', $user->id)
            ->pluck('school_classes.section_id');

        // Get section IDs where teacher has course assignments (via course_user)
        $sectionsFromCourseUser = DB::table('course_user')
            ->join('school_classes', 'course_user.class_id', '=', 'school_classes.id')
            ->where('course_user.user_id', $user->id)
            ->whereNotNull('course_user.class_id')
            ->pluck('school_classes.section_id');

        // Merge and get unique section IDs
        $allowedSectionIds = $sectionsFromClassUser->merge($sectionsFromCourseUser)->unique();

        // Return sections
        return Section::whereIn('id', $allowedSectionIds)->get();
    }

    public function uploadForm()
    {
        $sections = $this->getAllowedSections();
        return view('students_result', compact('sections'));
    }

    public function uploadFormResult()
    {
        $sections = $this->getAllowedSections();
        return view('upload_result', compact('sections'));
    }

    public function selectClass(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'class_id' => 'required|exists:school_classes,id',
        ]);

        $sections = Section::all();
        $class = SchoolClass::findOrFail($request->class_id);
        $user = Auth::user();

        // Security check for non-admin users
        if (!in_array($user->user_type, [1, 2])) {
            if (!$this->isTeacherAssignedToClass($user->id, $request->class_id)) {
                return redirect()->back()->with('error', 'You are not assigned to this class.');
            }
        }

        $students = User::where('user_type', 4)
            ->where('class_id', $request->class_id)
            ->select('id', 'name', 'email', 'admission_no', 'dob', 'phone', 'guardian_name', 'guardian_phone', 'guardian_email', 'guardian_address', 'address', 'class_id', 'gender')
            ->paginate(10);

        return view('upload_result', compact('students', 'class', 'sections'));
    }

    public function studentResultUpload($studentId)
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class = SchoolClass::findOrFail($student->class_id);
        $section = Section::find($class->section_id);
        $user = Auth::user();

        // Security check for non-admin users
        if (!in_array($user->user_type, [1, 2])) {
            if (!$this->isTeacherAssignedToClass($user->id, $class->id)) {
                abort(403, 'You are not assigned to this class.');
            }
        }

        $currentSession = Session::where('is_current', true)->first();
        $currentTerm = $currentSession?->terms()->where('is_current', true)->first();

        if (!$currentSession || !$currentTerm) {
            return redirect()->back()->with('error', 'No current academic session or term is set. Please ask the admin to set a current session/term.');
        }

        $subjectsQuery = Course::orderBy('course_name');

        if (!in_array($user->user_type, [1, 2])) {
            $subjectsQuery->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('course_user')
                    ->whereColumn('course_user.course_id', 'courses.id')
                    ->where('course_user.user_id', $user->id);
            });
        }

        $subjects = $subjectsQuery->get(['id', 'course_name']);

        $existingResults = Result::where('student_id', $studentId)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->get()
            ->keyBy('course_id');

        return view('student_result_upload', compact(
            'student',
            'class',
            'section',
            'subjects',
            'existingResults',
            'currentSession',
            'currentTerm'
        ));
    }

    public function saveStudentResults(Request $request, $studentId)
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class = SchoolClass::findOrFail($student->class_id);
        $user = Auth::user();

        // Security check for non-admin users
        if (!in_array($user->user_type, [1, 2])) {
            if (!$this->isTeacherAssignedToClass($user->id, $class->id)) {
                abort(403, 'You are not assigned to this class.');
            }
        }

        $currentSession = Session::where('is_current', true)->first();
        $currentTerm = $currentSession?->terms()->where('is_current', true)->first();

        if (!$currentSession || !$currentTerm) {
            return redirect()->back()->with('error', 'Cannot save results: No current academic session or term is set.');
        }

        $request->validate([
            'results' => 'required|array',
            'results.*.first_ca' => 'required|numeric|min:0',
            'results.*.second_ca' => 'required|numeric|min:0',
            'results.*.mid_term_test' => 'required|numeric|min:0',
            'results.*.examination' => 'required|numeric|min:0',
            'results.*.comment' => 'nullable|string|max:500',
        ]);

        foreach ($request->results as $course_id => $data) {
            $total = $data['first_ca'] + $data['second_ca'] + $data['mid_term_test'] + $data['examination'];
            $grade = $this->calculateGrade($total);

            Result::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'course_id' => $course_id,
                    'session_id' => $currentSession->id,
                    'term_id' => $currentTerm->id,
                ],
                [
                    'first_ca' => $data['first_ca'],
                    'second_ca' => $data['second_ca'],
                    'mid_term_test' => $data['mid_term_test'],
                    'examination' => $data['examination'],
                    'total' => $total,
                    'grade' => $grade,
                    'comment' => $data['comment'] ?? null,
                    'uploaded_by' => Auth::id(),
                    'session_id' => $currentSession->id,
                    'term_id' => $currentTerm->id,
                ]
            );
        }

        return redirect()->back()->with('success', 'Results saved successfully (updated where already existing).');
    }

    private function calculateGrade($total)
    {
        if ($total >= 70) return 'A';
        if ($total >= 60) return 'B';
        if ($total >= 50) return 'C';
        if ($total >= 45) return 'D';
        if ($total >= 40) return 'E';
        return 'F';
    }

    public function getClassesBySection($sectionId)
    {
        $user = Auth::user();
        $classesQuery = SchoolClass::where('section_id', $sectionId);

        // Filter classes for teachers
        if (!in_array($user->user_type, [1, 2])) {
            $classesQuery->where(function ($query) use ($user) {
                $query->whereExists(function ($subQ) use ($user) {
                    $subQ->select(DB::raw(1))
                        ->from('class_user')
                        ->whereColumn('class_user.school_class_id', 'school_classes.id')
                        ->where('class_user.user_id', $user->id);
                })
                    ->orWhereExists(function ($subQ) use ($user) {
                        $subQ->select(DB::raw(1))
                            ->from('course_user')
                            ->whereColumn('course_user.class_id', 'school_classes.id')
                            ->where('course_user.user_id', $user->id);
                    });
            });
        }

        $classes = $classesQuery->get(['id', 'name']);
        return response()->json($classes);
    }

    public function printForm()
    {
        $user = Auth::user();

        // If user is a Form Teacher, skip selection and go straight to their class
        if ($user->user_type == 3 && $user->is_form_teacher && $user->form_class_id) {
            $formClass = SchoolClass::find($user->form_class_id);

            if ($formClass) {
                // Redirect directly to the print page for their form class
                return redirect()->route('results.selectClassForPrint', [
                    'section_id' => $formClass->section_id,
                    'class_id'   => $formClass->id
                ]);
            }
        }

        // For Admins (and fallback for others): show section/class selection
        $sections = Section::all();

        return view('print_result_select_section_class', compact('sections'));
    }


    public function masterList(Request $request, $classId)
    {
        $class = SchoolClass::findOrFail($classId);
        $section = Section::find($class->section_id);
        $user = Auth::user();

        // Security check for non-admin users
        if (!in_array($user->user_type, [1, 2])) {
            $allowed = $this->isTeacherAssignedToClass($user->id, $classId);

            if (!$allowed && $user->is_form_teacher && $user->form_class_id == $classId) {
                $allowed = true;
            }

            if (!$allowed) {
                abort(403, 'You are not authorized to view the master list for this class.');
            }
        }

        // Fetch all sessions
        $sessions = Session::orderByDesc('name')->get();

        // Get selected session or default to current
        $selectedSessionId = $request->input('session_id');
        if (!$selectedSessionId) {
            $currentSession = Session::where('is_current', true)->first();
            $selectedSessionId = $currentSession?->id;
        }
        $selectedSession = Session::find($selectedSessionId);

        if (!$selectedSession) {
            return redirect()->back()->with('error', 'Selected session not found.');
        }

        // Get terms for selected session
        $terms = $selectedSession->terms()->orderBy('name')->get();

        // Get selected term or default to current
        $selectedTermId = $request->input('term_id');
        if (!$selectedTermId) {
            $currentTerm = $terms->where('is_current', true)->first();
            $selectedTermId = $currentTerm?->id ?? $terms->first()?->id;
        }
        $selectedTerm = Term::find($selectedTermId);

        if (!$selectedTerm) {
            return redirect()->back()->with('error', 'Selected term not found.');
        }

        // Get all subjects offered by this class
        $subjects = Course::whereHas('schoolClasses', function ($query) use ($class) {
            $query->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

        // Fetch all students in the class
        $students = User::where('user_type', 4)
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get();

        // Fetch all results for this class, session, and term
        $results = Result::where('session_id', $selectedSession->id)
            ->where('term_id', $selectedTerm->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('course_id', $subjects->pluck('id'))
            ->get()
            ->groupBy('student_id');

        // Calculate totals and positions for each student
        $studentSummaries = $students->map(function ($student) use ($results, $subjects) {
            $studentResults = $results->get($student->id, collect());

            $totalScore = 0;
            $subjectsWithScores = 0;

            foreach ($subjects as $subject) {
                $result = $studentResults->firstWhere('course_id', $subject->id);
                if ($result && $result->total > 0) {
                    $totalScore += $result->total;
                    $subjectsWithScores++;
                }
            }

            $average = $subjects->count() > 0 ? round($totalScore / $subjects->count(), 2) : 0;
            $grade = $this->calculateGrade($average);

            return [
                'student' => $student,
                'total_score' => $totalScore,
                'average' => $average,
                'grade' => $grade,
            ];
        });

        // Sort by total score descending to determine positions
        $sortedStudents = $studentSummaries->sortByDesc('total_score')->values();

        // Assign positions
        $sortedStudents = $sortedStudents->map(function ($item, $index) {
            $item['position'] = $index + 1;
            $item['formatted_position'] = ($index + 1) . $this->getPositionSuffix($index + 1);
            return $item;
        });

        return view('results.master_list', compact(
            'class',
            'section',
            'subjects',
            'students',
            'results',
            'sortedStudents',
            'sessions',
            'selectedSession',
            'terms',
            'selectedTerm'
        ));
    }


    // Add this method to your ResultsController class

    public function exportMasterList($classId)
    {
        $sessionId = request('session_id');
        $termId = request('term_id');

        $class = SchoolClass::findOrFail($classId);
        $session = Session::findOrFail($sessionId);
        $term = Term::findOrFail($termId);

        // Security check for non-admin users
        $user = Auth::user();
        if (!in_array($user->user_type, [1, 2])) {
            $allowed = $this->isTeacherAssignedToClass($user->id, $classId);

            if (!$allowed && $user->is_form_teacher && $user->form_class_id == $classId) {
                $allowed = true;
            }

            if (!$allowed) {
                abort(403, 'You are not authorized to export the master list for this class.');
            }
        }

        $filename = sprintf(
            'MasterList_%s_%s_%s.xlsx',
            str_replace(['/', '\\', ' '], '_', $class->name),
            str_replace(['/', '\\', ' '], '_', $session->name),
            str_replace(['/', '\\', ' '], '_', $term->name)
        );

        return Excel::download(
            new MasterListExport($classId, $sessionId, $termId),
            $filename
        );
    }


    public function selectClassForPrint(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'class_id' => 'required|exists:school_classes,id',
        ]);

        $class = SchoolClass::findOrFail($request->class_id);
        $section = Section::find($class->section_id);
        $user = Auth::user();

        // Security check for non-admin users
        if (!in_array($user->user_type, [1, 2])) {
            $allowed = $this->isTeacherAssignedToClass($user->id, $request->class_id);

            if (!$allowed && $user->is_form_teacher && $user->form_class_id == $request->class_id) {
                $allowed = true;
            }

            if (!$allowed) {
                return redirect()->back()->with('error', 'You are not authorized to print results for this class.');
            }
        }

        // Fetch all sessions
        $sessions = Session::orderByDesc('name')->get();

        // Get selected session or default to current
        $selectedSessionId = $request->input('session_id');
        if (!$selectedSessionId) {
            $currentSession = Session::where('is_current', true)->first();
            $selectedSessionId = $currentSession?->id;
        }
        $selectedSession = Session::find($selectedSessionId);

        // Get terms for selected session
        $terms = $selectedSession ? $selectedSession->terms()->orderBy('name')->get() : collect();

        // Get selected term or default to current
        $selectedTermId = $request->input('term_id');
        if (!$selectedTermId && $selectedSession) {
            $currentTerm = $terms->where('is_current', true)->first();
            $selectedTermId = $currentTerm?->id ?? $terms->first()?->id;
        }
        $selectedTerm = Term::find($selectedTermId);

        // Fetch students
        $students = User::where('user_type', 4)
            ->where('class_id', $request->class_id)
            ->select('id', 'name', 'email', 'admission_no', 'dob', 'phone', 'guardian_name', 'guardian_phone', 'guardian_email', 'guardian_address', 'address', 'class_id', 'gender')
            ->paginate(10);

        return view('print_class_result', compact(
            'students',
            'class',
            'section',
            'sessions',
            'selectedSession',
            'terms',
            'selectedTerm'
        ));
    }


    public function printStudent($studentId, Request $request, $action = 'stream')
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class = SchoolClass::findOrFail($student->class_id);
        $section = Section::find($class->section_id);

        $sessionId = $request->query('session_id');
        $termId    = $request->query('term_id');

        $currentSession = $sessionId ? Session::findOrFail($sessionId) : Session::where('is_current', true)->first();
        $currentTerm    = $termId ? Term::findOrFail($termId) : ($currentSession?->terms()->where('is_current', true)->first());

        if (!$currentSession || !$currentTerm) {
            abort(404, 'No current session or term is set.');
        }

        // Get all subjects for the class
        $allSubjects = Course::whereHas('schoolClasses', function ($q) use ($class) {
            $q->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

        // Get student's results
        $studentResults = Result::where('student_id', $studentId)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->get()
            ->keyBy('course_id');

        // Build results array (THIS IS CRITICAL — DO NOT SKIP)
        $results = $allSubjects->map(function ($subject) use ($studentResults) {
            $result = $studentResults->get($subject->id);

            return [
                'course_name'   => $subject->course_name,
                'first_ca'      => $result?->first_ca ?? 0,
                'second_ca'     => $result?->second_ca ?? 0,
                'mid_term_test' => $result?->mid_term_test ?? 0,
                'examination'   => $result?->examination ?? 0,
                'total'         => $result?->total ?? 0,
                'grade'         => $result?->grade ?? '-',
            ];
        });

        // Calculations
        $overallTotal   = $results->sum('total');
        $subjectCount   = $allSubjects->count();
        $overallAverage = $subjectCount > 0 ? round($overallTotal / $subjectCount, 2) : 0;
        $overallGrade   = $this->calculateGrade($overallAverage);

        // Position
        $classStudents = User::where('user_type', 4)->where('class_id', $class->id)->pluck('id');
        $totalStudentsInClass = $classStudents->count();

        $studentsScores = Result::where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->whereIn('student_id', $classStudents)
            ->whereIn('course_id', $allSubjects->pluck('id'))
            ->select('student_id', DB::raw('SUM(total) as total_score'))
            ->groupBy('student_id')
            ->orderByDesc('total_score')
            ->get();

        $studentPosition = $studentsScores->search(fn($item) => $item->student_id == $studentId);
        $studentPosition = $studentPosition !== false ? $studentPosition + 1 : $totalStudentsInClass;
        $formattedPosition = $studentPosition . $this->getPositionSuffix($studentPosition);

        // Teacher & Remarks
        $classTeacher = User::where('is_form_teacher', true)->where('form_class_id', $class->id)->first();

        $remark = StudentRemark::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->first();

        $affectiveRatings   = array_merge($this->defaultRatings('affective'),   $remark?->affective_ratings ?? []);
        $psychomotorRatings = array_merge($this->defaultRatings('psychomotor'), $remark?->psychomotor_ratings ?? []);

        $teacherRemark   = $remark?->teacher_remark ?? '';
        $principalRemark = $remark?->principal_remark ?? '';

        // Watermark for teachers/admins
        $isTeacherOrAdmin = in_array(Auth::user()->user_type, [1, 2, 3]); // admin, superadmin, teacher
        $showWatermark = $isTeacherOrAdmin;

        // Generate PDF
        $pdf = Pdf::loadView('student_report_card', [
            'student'              => $student,
            'class'                => $class,
            'section'              => $section,
            'results'              => $results,                    // ← MUST be included
            'overallTotal'         => $overallTotal,
            'overallAverage'       => $overallAverage,
            'overallGrade'         => $overallGrade,
            'currentSession'       => $currentSession,
            'currentTerm'          => $currentTerm,
            'classTeacher'         => $classTeacher,
            'affectiveRatings'     => $affectiveRatings,
            'psychomotorRatings'   => $psychomotorRatings,
            'teacherRemark'        => $teacherRemark,
            'principalRemark'      => $principalRemark,
            'formattedPosition'    => $formattedPosition,
            'totalStudentsInClass' => $totalStudentsInClass,
            'subjectCount'         => $subjectCount,
            'showWatermark'        => $showWatermark,              // ← for watermark
        ])->setPaper('a4', 'portrait');

        $filename = strtoupper($student->name) . '_Report_Card_' . $currentTerm->name . '.pdf';

        // Block download for teachers
        if ($action === 'download' && $isTeacherOrAdmin) {
            abort(403, 'Download not allowed for preview mode.');
        }

        return $action === 'download' ? $pdf->download($filename) : $pdf->stream($filename);
    }

    // Add this helper method to the controller
    private function getPositionSuffix($position)
    {
        if ($position % 100 >= 11 && $position % 100 <= 13) {
            return 'th';
        }

        switch ($position % 10) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
            default:
                return 'th';
        }
    }


    public function viewClassResults(Request $request, $classId)
    {
        $class = SchoolClass::findOrFail($classId);
        $section = Section::find($class->section_id);
        $user = Auth::user();

        // Security check for non-admin users
        if (!in_array($user->user_type, [1, 2])) {
            if (!$this->isTeacherAssignedToClass($user->id, $classId)) {
                abort(403, 'You are not authorized to view results for this class.');
            }
        }

        $sessions = Session::orderByDesc('name')->get();

        $selectedSessionId = $request->input('session_id');
        if (!$selectedSessionId) {
            $currentSession = Session::where('is_current', true)->first();
            $selectedSessionId = $currentSession?->id;
        }

        $selectedSession = Session::find($selectedSessionId);
        if (!$selectedSession) {
            return redirect()->back()->with('error', 'Selected session not found.');
        }

        $terms = $selectedSession->terms()->orderBy('name')->get();

        $selectedTermId = $request->input('term_id');
        if (!$selectedTermId) {
            $currentTerm = $terms->where('is_current', true)->first();
            $selectedTermId = $currentTerm?->id ?? $terms->first()?->id;
        }

        $selectedTerm = Term::find($selectedTermId);
        if (!$selectedTerm) {
            return redirect()->back()->with('error', 'Selected term not found.');
        }

        $students = User::where('user_type', 4)
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get();

        $subjectsQuery = Course::orderBy('course_name');

        if (!in_array($user->user_type, [1, 2])) {
            $subjectsQuery->whereExists(function ($query) use ($user, $classId) {
                $query->select(DB::raw(1))
                    ->from('course_user')
                    ->whereColumn('course_user.course_id', 'courses.id')
                    ->where('course_user.user_id', $user->id)
                    ->where(function ($q) use ($classId) {
                        $q->where('class_id', $classId)
                            ->orWhereNull('class_id');
                    });
            });
        }

        $subjects = $subjectsQuery->get();

        $resultsMatrix = Result::where('session_id', $selectedSession->id)
            ->where('term_id', $selectedTerm->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('course_id', $subjects->pluck('id'))
            ->get()
            ->groupBy(['student_id', 'course_id']);

        return view('class_results_view', compact(
            'class',
            'section',
            'students',
            'subjects',
            'resultsMatrix',
            'sessions',
            'selectedSession',
            'terms',
            'selectedTerm',
            'user'
        ));
    }

    public function exportClassResults($classId)
    {
        $sessionId = request('session_id');
        $termId = request('term_id');

        $class = SchoolClass::findOrFail($classId);
        $session = Session::findOrFail($sessionId);
        $term = Term::findOrFail($termId);

        $filename = sprintf(
            '%s_%s_%s_Results.xlsx',
            str_replace(['/', '\\', ' '], '_', $class->name),
            str_replace(['/', '\\', ' '], '_', $session->name),
            str_replace(['/', '\\', ' '], '_', $term->name)
        );

        return Excel::download(
            new ClassResultsExport($classId, $sessionId, $termId),
            $filename
        );
    }


    private function defaultRatings($type)
    {
        if ($type === 'affective') {
            return [
                'punctuality'       => null,
                'politeness'        => null,
                'neatness'          => null,
                'honesty'           => null,
                'leadership_skill'  => null,
                'cooperation'       => null,
                'attentiveness'     => null,
                'perseverance'      => null,
                'attitude_to_work'  => null,
            ];
        }

        // psychomotor
        return [
            'handwriting'       => null,
            'verbal_fluency'    => null,
            'sports'            => null,
            'handling_tools'    => null,
            'drawing_painting'  => null,
        ];
    }


    public function editRemarks($studentId)
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class   = SchoolClass::findOrFail($student->class_id);
        $section = Section::find($class->section_id);

        $currentSession = Session::where('is_current', true)->first();
        $currentTerm    = $currentSession?->terms()->where('is_current', true)->first();

        if (!$currentSession || !$currentTerm) {
            return redirect()->back()->with('error', 'No current academic session or term is set.');
        }

        // Get or create the remark record
        $remark = StudentRemark::firstOrCreate(
            [
                'student_id' => $student->id,
                'class_id'   => $class->id,
                'session_id' => $currentSession->id,
                'term_id'    => $currentTerm->id,
            ],
            [
                'affective_ratings'   => $this->defaultRatings('affective'),
                'psychomotor_ratings' => $this->defaultRatings('psychomotor'),
                'updated_by'          => Auth::id(),
            ]
        );

        // Ensure all keys exist even if partially saved before
        $remark->affective_ratings   = array_merge($this->defaultRatings('affective'),   $remark->affective_ratings ?? []);
        $remark->psychomotor_ratings = array_merge($this->defaultRatings('psychomotor'), $remark->psychomotor_ratings ?? []);

        return view('results.student_remarks', compact(
            'student',
            'class',
            'section',
            'remark',
            'currentSession',
            'currentTerm'
        ));
    }

    public function updateRemarks(Request $request, $studentId)
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class   = SchoolClass::findOrFail($student->class_id);

        $currentSession = Session::where('is_current', true)->first();
        $currentTerm    = $currentSession?->terms()->where('is_current', true)->first();

        if (!$currentSession || !$currentTerm) {
            return redirect()->back()->with('error', 'No current academic session or term is set.');
        }

        // Validation: each rating must be 1–5 or null
        $request->validate([
            'affective.punctuality'       => 'nullable|integer|between:1,5',
            'affective.politeness'        => 'nullable|integer|between:1,5',
            'affective.neatness'          => 'nullable|integer|between:1,5',
            'affective.honesty'           => 'nullable|integer|between:1,5',
            'affective.leadership_skill'  => 'nullable|integer|between:1,5',
            'affective.cooperation'       => 'nullable|integer|between:1,5',
            'affective.attentiveness'     => 'nullable|integer|between:1,5',
            'affective.perseverance'      => 'nullable|integer|between:1,5',
            'affective.attitude_to_work'  => 'nullable|integer|between:1,5',

            'psychomotor.handwriting'       => 'nullable|integer|between:1,5',
            'psychomotor.verbal_fluency'    => 'nullable|integer|between:1,5',
            'psychomotor.sports'            => 'nullable|integer|between:1,5',
            'psychomotor.handling_tools'    => 'nullable|integer|between:1,5',
            'psychomotor.drawing_painting'  => 'nullable|integer|between:1,5',

            'teacher_remark'    => 'nullable|string|max:1000',
            'principal_remark'  => 'nullable|string|max:1000',
        ]);

        // Extract and clean ratings (only allowed keys)
        $affectiveRatings = array_intersect_key(
            $request->input('affective', []),
            $this->defaultRatings('affective')
        );

        $psychomotorRatings = array_intersect_key(
            $request->input('psychomotor', []),
            $this->defaultRatings('psychomotor')
        );

        // Update or create the record
        StudentRemark::updateOrCreate(
            [
                'student_id' => $student->id,
                'class_id'   => $class->id,
                'session_id' => $currentSession->id,
                'term_id'    => $currentTerm->id,
            ],
            [
                'affective_ratings'   => $affectiveRatings,
                'psychomotor_ratings' => $psychomotorRatings,
                'teacher_remark'      => $request->teacher_remark,
                'principal_remark'    => in_array(Auth::user()->user_type, [1, 2])
                    ? $request->principal_remark
                    : ($remark->principal_remark ?? null), // only admins can change principal remark
                'updated_by'          => Auth::id(),
            ]
        );

        return redirect()->back()->with('success', 'Affective & Psychomotor skills and remarks saved successfully.');
    }

    public function cumulativeResults(Request $request, $classId)
    {
        $class = SchoolClass::findOrFail($classId);
        $section = Section::find($class->section_id);
        $user = Auth::user();

        // Security check for non-admin users
        if (!in_array($user->user_type, [1, 2])) {
            $allowed = $this->isTeacherAssignedToClass($user->id, $classId);
            if (!$allowed && $user->is_form_teacher && $user->form_class_id == $classId) {
                $allowed = true;
            }
            if (!$allowed) {
                abort(403, 'You are not authorized to view cumulative results for this class.');
            }
        }

        // Fetch all sessions
        $sessions = Session::orderByDesc('name')->get();

        // Get selected session or default to current
        $selectedSessionId = $request->input('session_id');
        if (!$selectedSessionId) {
            $currentSession = Session::where('is_current', true)->first();
            $selectedSessionId = $currentSession?->id;
        }
        $selectedSession = Session::find($selectedSessionId);

        if (!$selectedSession) {
            return redirect()->back()->with('error', 'Selected session not found.');
        }

        // Get all terms for the selected session
        $terms = $selectedSession->terms()->orderBy('name')->get();

        if ($terms->isEmpty()) {
            return redirect()->back()->with('error', 'No terms found for this session.');
        }

        // Get all subjects offered by this class
        $subjects = Course::whereHas('schoolClasses', function ($query) use ($class) {
            $query->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

        // Fetch all students in the class
        $students = User::where('user_type', 4)
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get();

        // Fetch results for all terms in the session
        $allResults = Result::where('session_id', $selectedSession->id)
            ->whereIn('term_id', $terms->pluck('id'))
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('course_id', $subjects->pluck('id'))
            ->get()
            ->groupBy(['student_id', 'term_id', 'course_id']);

        // Calculate cumulative data for each student
        $cumulativeData = $students->map(function ($student) use ($allResults, $subjects, $terms) {
            $studentResults = $allResults->get($student->id, collect());

            $termTotals = [];
            $cumulativeTotal = 0;

            foreach ($terms as $term) {
                $termResults = $studentResults->get($term->id, collect());
                $termTotal = 0;

                foreach ($subjects as $subject) {
                    $result = $termResults->get($subject->id);
                    if ($result) {
                        $termTotal += $result->first()->total;
                    }
                }

                $termTotals[$term->id] = $termTotal;
                $cumulativeTotal += $termTotal;
            }

            $totalPossible = $subjects->count() * $terms->count() * 100;
            $cumulativeAverage = $totalPossible > 0 ? round(($cumulativeTotal / $totalPossible) * 100, 2) : 0;
            $grade = $this->calculateGrade($cumulativeAverage);

            return [
                'student' => $student,
                'term_totals' => $termTotals,
                'cumulative_total' => $cumulativeTotal,
                'cumulative_average' => $cumulativeAverage,
                'grade' => $grade,
            ];
        });

        // Sort by cumulative total descending
        $sortedStudents = $cumulativeData->sortByDesc('cumulative_total')->values();

        // Assign positions
        $sortedStudents = $sortedStudents->map(function ($item, $index) {
            $item['position'] = $index + 1;
            $item['formatted_position'] = ($index + 1) . $this->getPositionSuffix($index + 1);
            return $item;
        });

        return view('results.cumulative_results', compact(
            'class',
            'section',
            'students',
            'subjects',
            'sortedStudents',
            'sessions',
            'selectedSession',
            'terms',
            'allResults'
        ));
    }

    public function printTranscript($studentId, $action = 'stream')
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class = SchoolClass::findOrFail($student->class_id);
        $section = Section::find($class->section_id);

        // Get all sessions the student has results for
        $sessions = Session::whereHas('results', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->orderBy('name')->get();

        // Get all subjects the student has taken
        $subjects = Course::whereHas('results', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->orderBy('course_name')->get();

        // Fetch all results for this student
        $allResults = Result::where('student_id', $studentId)
            ->with(['session', 'term', 'course'])
            ->get()
            ->groupBy(['session_id', 'term_id']);

        // Calculate overall statistics
        $transcriptData = [];
        $grandTotal = 0;
        $totalSubjectsAcrossSessions = 0;

        foreach ($sessions as $session) {
            $sessionResults = $allResults->get($session->id, collect());
            $terms = Term::where('session_id', $session->id)->orderBy('name')->get();

            $sessionData = [
                'session' => $session,
                'terms' => []
            ];

            $sessionTotal = 0;
            $sessionSubjectCount = 0;

            foreach ($terms as $term) {
                $termResults = $sessionResults->get($term->id, collect());

                if ($termResults->isNotEmpty()) {
                    $termTotal = $termResults->sum('total');
                    $termAverage = $termResults->count() > 0 ? round($termTotal / $termResults->count(), 2) : 0;

                    $sessionData['terms'][] = [
                        'term' => $term,
                        'results' => $termResults,
                        'total' => $termTotal,
                        'average' => $termAverage,
                        'grade' => $this->calculateGrade($termAverage)
                    ];

                    $sessionTotal += $termTotal;
                    $sessionSubjectCount += $termResults->count();
                }
            }

            $sessionData['session_total'] = $sessionTotal;
            $sessionData['session_average'] = $sessionSubjectCount > 0 ? round($sessionTotal / $sessionSubjectCount, 2) : 0;
            $sessionData['session_grade'] = $this->calculateGrade($sessionData['session_average']);

            $transcriptData[] = $sessionData;
            $grandTotal += $sessionTotal;
            $totalSubjectsAcrossSessions += $sessionSubjectCount;
        }

        $overallAverage = $totalSubjectsAcrossSessions > 0 ? round($grandTotal / $totalSubjectsAcrossSessions, 2) : 0;
        $overallGrade = $this->calculateGrade($overallAverage);

        // Generate PDF
        $pdf = Pdf::loadView('results.student_transcript', [
            'student' => $student,
            'class' => $class,
            'section' => $section,
            'transcriptData' => $transcriptData,
            'subjects' => $subjects,
            'grandTotal' => $grandTotal,
            'overallAverage' => $overallAverage,
            'overallGrade' => $overallGrade,
        ])->setPaper('a4', 'portrait');

        $filename = strtoupper($student->name) . '_Transcript.pdf';

        return $action === 'download' ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
