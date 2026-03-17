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
use App\Services\ResultSheetService;
use Illuminate\Support\Facades\Log;


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
        $class   = SchoolClass::findOrFail($student->class_id);
        $section = Section::find($class->section_id);
        $user    = Auth::user();

        if (!in_array($user->user_type, [1, 2])) {
            if (!$this->isTeacherAssignedToClass($user->id, $class->id)) {
                abort(403, 'You are not assigned to this class.');
            }
        }

        $currentSession = Session::where('is_current', true)->first();
        $currentTerm    = $currentSession?->terms()->where('is_current', true)->first();

        if (!$currentSession || !$currentTerm) {
            return redirect()->back()->with('error', 'No current academic session or term is set.');
        }

        // ── NURSERY: Custom result sheet template ─────────────────────────────
        $sheetTemplate = DB::table('result_sheet_templates')
            ->where('is_active', 1)
            ->get()
            ->first(function ($t) use ($class) {
                $classes = json_decode($t->applicable_classes ?? '[]', true);
                return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
            });

        if ($sheetTemplate) {
            $sheetTemplate->rating_columns = json_decode($sheetTemplate->rating_columns ?? '[]');
            $sheetTemplate->footer_fields  = json_decode($sheetTemplate->footer_fields ?? '{}', true);

            $service    = new ResultSheetService();
            $subjects   = $service->loadTemplateStructure($sheetTemplate->id);
            $allItemIds = collect($subjects)->flatMap(function ($subject) {
                $ids = collect($subject->items)->pluck('id');
                foreach ($subject->subcategories as $sub) {
                    $ids = $ids->merge(collect($sub->items)->pluck('id'));
                }
                return $ids;
            });

            $existingRatings = DB::table('result_sheet_ratings')
                ->where('student_id', $studentId)
                ->where('session_id', $currentSession->id)
                ->where('term_id', $currentTerm->id)
                ->whereIn('item_id', $allItemIds)
                ->pluck('rating_value', 'item_id');

            $footerData = DB::table('result_sheet_footer_data')
                ->where('student_id', $student->id)
                ->where('session_id', $currentSession->id)
                ->where('term_id', $currentTerm->id)
                ->where('template_id', $sheetTemplate->id)
                ->first();

            return view('student_result_sheet', compact(
                'student',
                'class',
                'section',
                'sheetTemplate',
                'subjects',
                'existingRatings',
                'currentSession',
                'currentTerm',
                'footerData'
            ));
        }

        // ── PRIMARY: Primary school cognitive ability grid ────────────────────
        $isPrimaryClass = DB::table('primary_result_classes')
            ->where('school_class_id', $class->id)
            ->exists();

        if ($isPrimaryClass) {
            $subjects = Course::whereHas('schoolClasses', function ($q) use ($class) {
                $q->where('school_classes.id', $class->id);
            })->orderBy('course_name')->get(['id', 'course_name']);

            $existingResults = \App\Models\PrimarySchoolResult::where('student_id', $studentId)
                ->where('session_id', $currentSession->id)
                ->where('term_id', $currentTerm->id)
                ->get()
                ->keyBy('course_id');

            $termlyScores = $this->calculateTermlyScores(
                $studentId,
                $currentSession->id,
                $currentTerm->id
            );

            $remark = StudentRemark::where('student_id', $student->id)
                ->where('class_id', $class->id)
                ->where('session_id', $currentSession->id)
                ->where('term_id', $currentTerm->id)
                ->first();

            $affectiveRatings = array_merge(
                $this->defaultRatings('affective'),
                $remark?->affective_ratings ?? []
            );

            return view('primary_student_result_upload', compact(
                'student',
                'class',
                'section',
                'subjects',
                'existingResults',
                'termlyScores',
                'affectiveRatings',
                'currentSession',
                'currentTerm'
            ));
        }

        // ── OTHER (Secondary/JS/SS): Standard numeric result upload ───────────
        $subjectsQuery = Course::whereHas('schoolClasses', function ($q) use ($class) {
            $q->where('school_classes.id', $class->id);
        })->orderBy('course_name');

        if (!in_array($user->user_type, [1, 2])) {
            $subjectsQuery->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('course_user')
                    ->whereColumn('course_user.course_id', 'courses.id')
                    ->where('course_user.user_id', $user->id);
            });
        }

        $subjects        = $subjectsQuery->get(['id', 'course_name']);
        $existingResults = Result::where('student_id', $studentId)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->get()
            ->keyBy('course_id');

        $sheetTemplate = null;

        return view('student_result_upload', compact(
            'student',
            'class',
            'section',
            'subjects',
            'existingResults',
            'currentSession',
            'currentTerm',
            'sheetTemplate'
        ));
    }

    private function calculateTermlyScores($studentId, $sessionId, $currentTermId)
    {
        $session = Session::find($sessionId);
        $terms   = $session?->terms()->orderBy('name')->get() ?? collect();

        $scores     = [];
        $cumulative = 0;

        foreach ($terms as $term) {
            $termTotal = \App\Models\PrimarySchoolResult::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('term_id', $term->id)
                ->sum('final_obtained');

            $scores[$term->id] = [
                'name'       => $term->name,
                'total'      => $termTotal,
                'is_current' => $term->id == $currentTermId,
            ];

            $cumulative += $termTotal;
        }

        return [
            'terms'      => $scores,
            'cumulative' => $cumulative,
        ];
    }



    public function saveStudentSheetRatings(Request $request, $studentId, $templateId)
    {
        $request->validate([
            'session_id'     => 'required|exists:school_sessions,id',
            'term_id'        => 'required|exists:terms,id',
            'ratings'        => 'nullable|array',
            'remark'         => 'nullable|string|max:500',
            'reopening_date' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($request, $templateId, $studentId) {

            // Save checkbox ratings
            foreach ($request->input('ratings', []) as $itemId => $value) {
                DB::table('result_sheet_ratings')->updateOrInsert(
                    [
                        'item_id'    => $itemId,
                        'student_id' => $studentId,
                        'session_id' => $request->session_id,
                        'term_id'    => $request->term_id,
                    ],
                    [
                        'template_id'  => $templateId,
                        'rating_value' => $value ?: null,
                        'rated_by'     => Auth::id(),
                        'updated_at'   => now(),
                        'created_at'   => now(),
                    ]
                );
            }

            // Save footer fields
            DB::table('result_sheet_footer_data')->updateOrInsert(
                [
                    'student_id'  => $studentId,
                    'session_id'  => $request->session_id,
                    'term_id'     => $request->term_id,
                    'template_id' => $templateId,
                ],
                [
                    'remark'         => $request->input('remark'),
                    'reopening_date' => $request->input('reopening_date'),
                    'updated_at'     => now(),
                    'created_at'     => now(),
                ]
            );
        });

        return back()->with('success', 'Ratings saved successfully.');
    }

    public function saveStudentResults(Request $request, $studentId)
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class   = SchoolClass::findOrFail($student->class_id);
        $user    = Auth::user();

        if (!in_array($user->user_type, [1, 2])) {
            if (!$this->isTeacherAssignedToClass($user->id, $class->id)) {
                abort(403, 'You are not assigned to this class.');
            }
        }

        $currentSession = Session::where('is_current', true)->first();
        $currentTerm    = $currentSession?->terms()->where('is_current', true)->first();

        if (!$currentSession || !$currentTerm) {
            return redirect()->back()->with('error', 'Cannot save results: No current academic session or term is set.');
        }

        $request->validate([
            'results'                          => 'nullable|array',
            'results.*.first_half_obtained'    => 'nullable|numeric|min:0|max:30',
            'results.*.second_half_obtained'   => 'nullable|numeric|min:0|max:70',
            'results.*.final_obtained'         => 'nullable|numeric|min:0|max:100',
            'results.*.comment'                => 'nullable|string|max:500',
            // Hidden obtainable fields (overridden server-side anyway)
            'results.*.first_half_obtainable'  => 'nullable|numeric',
            'results.*.second_half_obtainable' => 'nullable|numeric',
            'results.*.final_obtainable'       => 'nullable|numeric',
        ]);

        foreach ($request->input('results', []) as $course_id => $data) {

            $firstObtained  = isset($data['first_half_obtained'])  && $data['first_half_obtained']  !== ''
                ? (float) $data['first_half_obtained']  : null;
            $secondObtained = isset($data['second_half_obtained']) && $data['second_half_obtained'] !== ''
                ? (float) $data['second_half_obtained'] : null;

            // Skip entirely if both halves are blank
            if ($firstObtained === null && $secondObtained === null) {
                continue;
            }

            // Auto-calculate final = first + second
            $finalObtained = round(($firstObtained ?? 0) + ($secondObtained ?? 0), 2);
            $finalObtained = min($finalObtained, 100);

            // Grade is based on the final total out of 100
            $grade = $this->calculateGrade($finalObtained);

            Result::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'course_id'  => $course_id,
                    'session_id' => $currentSession->id,
                    'term_id'    => $currentTerm->id,
                ],
                [
                    // Fixed obtainable values — always server-side
                    'first_half_obtainable'  => 30,
                    'second_half_obtainable' => 70,
                    'final_obtainable'       => 100,

                    // Obtained values
                    'first_half_obtained'    => $firstObtained,
                    'second_half_obtained'   => $secondObtained,
                    'final_obtained'         => $finalObtained,

                    // total column kept in sync for any existing queries that use it
                    'total'                  => $finalObtained,
                    'grade'                  => $grade,

                    'comment'                => $data['comment'] ?? null,
                    'uploaded_by'            => Auth::id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Results saved successfully.');
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
        // On POST, redirect to GET with all params in the URL
        if ($request->isMethod('post')) {
            $request->validate([
                'section_id' => 'required|exists:sections,id',
                'class_id'   => 'required|exists:school_classes,id',
            ]);

            return redirect()->route('results.selectClassForPrint', $request->only([
                'section_id',
                'class_id',
                'session_id',
                'term_id'
            ]));
        }

        // Guard: if params are missing on GET, go back to selection screen
        if (!$request->input('section_id') || !$request->input('class_id')) {
            return redirect()->route('results.print');
        }

        $class   = SchoolClass::findOrFail($request->class_id);
        $section = Section::find($class->section_id);
        $user    = Auth::user();

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

        // ── Detect if this class uses a custom skill sheet template ──────────
        $usesResultSheet = false;
        $sheetTemplate   = null;

        // Check against ALL active templates (not term-scoped)
        // because the print list page shows students regardless of term
        $allActiveTemplates = DB::table('result_sheet_templates')
            ->where('is_active', 1)
            ->get();

        foreach ($allActiveTemplates as $t) {
            $classes = json_decode($t->applicable_classes ?? '[]', true);
            if (in_array($class->id, $classes) || in_array((string) $class->id, $classes)) {
                $usesResultSheet = true;
                $sheetTemplate   = $t;
                break;
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        // Fetch all sessions
        $sessions = Session::orderByDesc('name')->get();

        // Get selected session or default to current
        $selectedSessionId = $request->input('session_id');
        if (!$selectedSessionId) {
            $currentSession    = Session::where('is_current', true)->first();
            $selectedSessionId = $currentSession?->id;
        }
        $selectedSession = Session::find($selectedSessionId);

        // Get terms for selected session
        $terms = $selectedSession
            ? $selectedSession->terms()->orderBy('name')->get()
            : collect();

        // Get selected term or default to current
        $selectedTermId = $request->input('term_id');
        if (!$selectedTermId && $selectedSession) {
            $currentTerm    = $terms->where('is_current', true)->first();
            $selectedTermId = $currentTerm?->id ?? $terms->first()?->id;
        }
        $selectedTerm = Term::find($selectedTermId);

        // Fetch students
        $students = User::where('user_type', 4)
            ->where('class_id', $request->class_id)
            ->select(
                'id',
                'name',
                'email',
                'admission_no',
                'dob',
                'phone',
                'guardian_name',
                'guardian_phone',
                'guardian_email',
                'guardian_address',
                'address',
                'class_id',
                'gender'
            )
            ->paginate(10);

        return view('print_class_result', compact(
            'students',
            'class',
            'section',
            'sessions',
            'selectedSession',
            'terms',
            'selectedTerm',
            'usesResultSheet',   // ← tells the view which mode to use
            'sheetTemplate'      // ← available if needed in the view
        ));
    }


    public function printStudentSheet(Request $request, $studentId)
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class   = SchoolClass::findOrFail($student->class_id);
        $section = Section::find($class->section_id);

        // Respect session/term passed from the filter
        $currentSession = $request->input('session_id')
            ? Session::findOrFail($request->input('session_id'))
            : Session::where('is_current', true)->firstOrFail();

        $selectedTerm = $request->input('term_id')
            ? Term::findOrFail($request->input('term_id'))
            : $currentSession->terms()->where('is_current', true)->first()
            ?? $currentSession->terms()->orderBy('name')->first();

        abort_if(!$selectedTerm, 404, 'No term found.');

        // Find active template for this class
        $sheetTemplate = DB::table('result_sheet_templates')
            ->where('is_active', 1)
            ->get()
            ->first(function ($t) use ($class) {
                $classes = json_decode($t->applicable_classes ?? '[]', true);
                return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
            });

        abort_if(!$sheetTemplate, 404, 'No active skill sheet template for this class.');

        $sheetTemplate->rating_columns = json_decode($sheetTemplate->rating_columns ?? '[]');
        $sheetTemplate->footer_fields  = json_decode($sheetTemplate->footer_fields ?? '{}', true);

        $service  = new ResultSheetService();
        $subjects = $service->loadTemplateStructure($sheetTemplate->id);

        // Collect all item IDs
        $allItemIds = collect($subjects)->flatMap(function ($subject) {
            $ids = collect($subject->items)->pluck('id');
            foreach ($subject->subcategories as $sub) {
                $ids = $ids->merge(collect($sub->items)->pluck('id'));
            }
            return $ids;
        });

        // Fetch ratings for THIS student, THIS session, THIS term only
        // In printStudentSheet — fix the ratings fetch
        $ratings = DB::table('result_sheet_ratings')
            ->where('student_id', $studentId)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $selectedTerm->id)
            ->whereIn('item_id', $allItemIds)
            ->get(['item_id', 'rating_value'])
            ->mapWithKeys(function ($row) {
                // Cast item_id to int so $ratings[$item->id] always matches
                return [(int) $row->item_id => trim($row->rating_value)];
            })
            ->toArray();


        $footerData = DB::table('result_sheet_footer_data')
            ->where('student_id', $studentId)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $selectedTerm->id)
            ->where('template_id', $sheetTemplate->id)
            ->first();

        return view('result_sheet_student_print', compact(
            'student',
            'class',
            'section',
            'sheetTemplate',
            'subjects',
            'ratings',
            'currentSession',
            'selectedTerm',
            'footerData'   // ← add this
        ));

        return view('result_sheet_student_print', compact(
            'student',
            'class',
            'section',
            'sheetTemplate',
            'subjects',
            'ratings',
            'currentSession',
            'selectedTerm'
        ));
    }


    public function printStudent($studentId, Request $request, $action = 'stream')
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class   = SchoolClass::findOrFail($student->class_id);
        $section = Section::find($class->section_id);

        $sessionId = $request->query('session_id');
        $termId    = $request->query('term_id');

        $currentSession = $sessionId
            ? Session::findOrFail($sessionId)
            : Session::where('is_current', true)->first();

        $currentTerm = $termId
            ? Term::findOrFail($termId)
            : $currentSession?->terms()->where('is_current', true)->first();

        if (!$currentSession || !$currentTerm) {
            abort(404, 'No current session or term is set.');
        }

        // ── Determine class type ──────────────────────────────────────────────────
        $isPrimary = DB::table('primary_result_classes')
            ->where('school_class_id', $class->id)
            ->exists();

        // ── Shared: class teacher, remarks, ratings ───────────────────────────────
        $classTeacher = User::where('is_form_teacher', true)
            ->where('form_class_id', $class->id)
            ->first();

        $remark = StudentRemark::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->first();

        $defaultAffective = [
            'punctuality'          => null,
            'politeness'           => null,
            'neatness'             => null,
            'honesty'              => null,
            'leadership_skill'     => null,
            'cooperation'          => null,
            'attentiveness'        => null,
            'perseverance'         => null,
            'attitude_to_work'     => null,
            'helping_other'        => null,
            'emotional_stability'  => null,
            'health'               => null,
            'speaking_handwriting' => null,
        ];

        $defaultPsychomotor = [
            'handwriting'      => null,
            'verbal_fluency'   => null,
            'sports'           => null,
            'handling_tools'   => null,
            'drawing_painting' => null,
            'games'            => null,
            'musical_skills'   => null,
        ];

        $affectiveRatings   = array_merge($defaultAffective,   $remark?->affective_ratings   ?? []);
        $psychomotorRatings = array_merge($defaultPsychomotor, $remark?->psychomotor_ratings ?? []);

        $teacherRemark    = $remark?->teacher_remark    ?? '';
        $principalRemark  = $remark?->principal_remark  ?? '';
        $headmasterRemark = $remark?->headmaster_remark ?? '';

        // ── Watermark flag ────────────────────────────────────────────────────────
        $isTeacherOrAdmin = in_array(Auth::user()->user_type, [1, 2, 3]);
        $showWatermark    = $isTeacherOrAdmin;

        // ── Total students in class ───────────────────────────────────────────────
        $classStudentIds      = User::where('user_type', 4)->where('class_id', $class->id)->pluck('id');
        $totalStudentsInClass = $classStudentIds->count();

        // ══════════════════════════════════════════════════════════════════════════
        // PRIMARY SCHOOL PATH
        // ══════════════════════════════════════════════════════════════════════════
        if ($isPrimary) {

            $allSubjects = Course::whereHas('schoolClasses', function ($q) use ($class) {
                $q->where('school_classes.id', $class->id);
            })->orderBy('course_name')->get();

            $studentPrimaryResults = \App\Models\PrimarySchoolResult::where('student_id', $studentId)
                ->where('session_id', $currentSession->id)
                ->where('term_id', $currentTerm->id)
                ->get()
                ->keyBy('course_id');

            $results = $allSubjects->map(function ($subject) use ($studentPrimaryResults) {
                $r = $studentPrimaryResults->get($subject->id);
                return [
                    'course_name'            => $subject->course_name,
                    'first_half_obtainable'  => $r?->first_half_obtainable  ?? 30,
                    'first_half_obtained'    => $r?->first_half_obtained    ?? 0,
                    'second_half_obtainable' => $r?->second_half_obtainable ?? 70,
                    'second_half_obtained'   => $r?->second_half_obtained   ?? 0,
                    'final_obtainable'       => $r?->final_obtainable       ?? 100,
                    'final_obtained'         => $r?->final_obtained         ?? 0,
                    'teacher_remark'         => $r?->teacher_remark         ?? '',
                ];
            });

            $overallTotal   = $results->sum('final_obtained');
            $subjectCount   = $allSubjects->count();
            $overallAverage = $subjectCount > 0 ? round($overallTotal / $subjectCount, 2) : 0;
            $overallGrade   = $this->calculateGrade($overallAverage);

            $allStudentTotals = \App\Models\PrimarySchoolResult::where('session_id', $currentSession->id)
                ->where('term_id', $currentTerm->id)
                ->whereIn('student_id', $classStudentIds)
                ->select('student_id', DB::raw('SUM(final_obtained) as total_score'))
                ->groupBy('student_id')
                ->orderByDesc('total_score')
                ->get();

            $studentPosition   = $allStudentTotals->search(fn($item) => $item->student_id == $studentId);
            $studentPosition   = $studentPosition !== false ? $studentPosition + 1 : $totalStudentsInClass;
            $formattedPosition = $studentPosition . $this->getPositionSuffix($studentPosition);

            // ↓ FIXED: uses 'student_report_card' (same view), isPrimary = true
            $pdf = Pdf::loadView('student_report_card', [
                'student'              => $student,
                'class'                => $class,
                'section'              => $section,
                'results'              => $results,
                'overallTotal'         => $overallTotal,
                'overallAverage'       => $overallAverage,
                'overallGrade'         => $overallGrade,
                'currentSession'       => $currentSession,
                'currentTerm'          => $currentTerm,
                'classTeacher'         => $classTeacher,
                'affectiveRatings'     => $affectiveRatings,
                'psychomotorRatings'   => $psychomotorRatings,
                'teacherRemark'        => $teacherRemark,
                'headmasterRemark'     => $headmasterRemark, // ← primary uses this
                'principalRemark'      => '',                // ← not used for primary
                'formattedPosition'    => $formattedPosition,
                'totalStudentsInClass' => $totalStudentsInClass,
                'subjectCount'         => $subjectCount,
                'showWatermark'        => $showWatermark,
                'isPrimary'            => true,              // ← correct
            ])->setPaper('a4', 'portrait');

            $filename = strtoupper($student->name) . '_Primary_Report_Card_' . $currentTerm->name . '.pdf';

            if ($action === 'download' && $isTeacherOrAdmin) {
                abort(403, 'Download not allowed for preview mode.');
            }

            return $action === 'download' ? $pdf->download($filename) : $pdf->stream($filename);
        }

        // ══════════════════════════════════════════════════════════════════════════
        // SECONDARY SCHOOL PATH
        // ══════════════════════════════════════════════════════════════════════════

        $allSubjects = Course::whereHas('schoolClasses', function ($q) use ($class) {
            $q->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

        $studentResults = Result::where('student_id', $studentId)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->get()
            ->keyBy('course_id');

        $results = $allSubjects->map(function ($subject) use ($studentResults) {
            $result = $studentResults->get($subject->id);
            return [
                'course_name'            => $subject->course_name,
                'first_half_obtainable'  => $result?->first_half_obtainable  ?? 30,
                'first_half_obtained'    => $result?->first_half_obtained    ?? 0,
                'second_half_obtainable' => $result?->second_half_obtainable ?? 70,
                'second_half_obtained'   => $result?->second_half_obtained   ?? 0,
                'final_obtainable'       => $result?->final_obtainable       ?? 100,
                'final_obtained'         => $result?->final_obtained         ?? 0,
                'total'                  => $result?->total                  ?? 0,
                'grade'                  => $result?->grade                  ?? '-',
            ];
        });

        $overallTotal   = $results->sum('final_obtained');
        $subjectCount   = $allSubjects->count();
        $overallAverage = $subjectCount > 0 ? round($overallTotal / $subjectCount, 2) : 0;
        $overallGrade   = $this->calculateGrade($overallAverage);

        $studentsScores = Result::where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->whereIn('student_id', $classStudentIds)
            ->whereIn('course_id', $allSubjects->pluck('id'))
            ->select('student_id', DB::raw('SUM(total) as total_score'))
            ->groupBy('student_id')
            ->orderByDesc('total_score')
            ->get();

        $studentPosition   = $studentsScores->search(fn($item) => $item->student_id == $studentId);
        $studentPosition   = $studentPosition !== false ? $studentPosition + 1 : $totalStudentsInClass;
        $formattedPosition = $studentPosition . $this->getPositionSuffix($studentPosition);

        // ↓ FIXED: isPrimary = false, principalRemark = actual value
        $pdf = Pdf::loadView('student_report_card', [
            'student'              => $student,
            'class'                => $class,
            'section'              => $section,
            'results'              => $results,
            'overallTotal'         => $overallTotal,
            'overallAverage'       => $overallAverage,
            'overallGrade'         => $overallGrade,
            'currentSession'       => $currentSession,
            'currentTerm'          => $currentTerm,
            'classTeacher'         => $classTeacher,
            'affectiveRatings'     => $affectiveRatings,
            'psychomotorRatings'   => $psychomotorRatings,
            'teacherRemark'        => $teacherRemark,
            'principalRemark'      => $principalRemark,  // ← secondary uses this
            'headmasterRemark'     => '',                // ← not used for secondary
            'formattedPosition'    => $formattedPosition,
            'totalStudentsInClass' => $totalStudentsInClass,
            'subjectCount'         => $subjectCount,
            'showWatermark'        => $showWatermark,
            'isPrimary'            => false,             // ← correct
        ])->setPaper('a4', 'portrait');

        $filename = strtoupper($student->name) . '_Report_Card_' . $currentTerm->name . '.pdf';

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
                // --- original ---
                'punctuality'          => null,
                'politeness'           => null,
                'neatness'             => null,
                'honesty'              => null,
                'leadership_skill'     => null,
                'cooperation'          => null,
                'attentiveness'        => null,
                'perseverance'         => null,
                'attitude_to_work'     => null,
                // --- newly added ---
                'helping_other'        => null,
                'emotional_stability'  => null,
                'health'               => null,
                'speaking_handwriting' => null,
            ];
        }

        // psychomotor
        return [
            // --- original ---
            'handwriting'    => null,
            'verbal_fluency' => null,
            'sports'         => null,
            'handling_tools' => null,
            'drawing_painting' => null,
            // --- newly added ---
            'games'          => null,
            'musical_skills' => null,
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

        // Determine if this is a Primary class
        $isPrimary = DB::table('primary_result_classes')
            ->where('school_class_id', $class->id)
            ->exists();

        // Validation
        $request->validate([
            'affective.punctuality'          => 'nullable|integer|between:1,5',
            'affective.politeness'           => 'nullable|integer|between:1,5',
            'affective.neatness'             => 'nullable|integer|between:1,5',
            'affective.honesty'              => 'nullable|integer|between:1,5',
            'affective.leadership_skill'     => 'nullable|integer|between:1,5',
            'affective.cooperation'          => 'nullable|integer|between:1,5',
            'affective.attentiveness'        => 'nullable|integer|between:1,5',
            'affective.perseverance'         => 'nullable|integer|between:1,5',
            'affective.attitude_to_work'     => 'nullable|integer|between:1,5',
            'affective.helping_other'        => 'nullable|integer|between:1,5',
            'affective.emotional_stability'  => 'nullable|integer|between:1,5',
            'affective.health'               => 'nullable|integer|between:1,5',
            'affective.speaking_handwriting' => 'nullable|integer|between:1,5',
            'psychomotor.handwriting'        => 'nullable|integer|between:1,5',
            'psychomotor.verbal_fluency'     => 'nullable|integer|between:1,5',
            'psychomotor.sports'             => 'nullable|integer|between:1,5',
            'psychomotor.handling_tools'     => 'nullable|integer|between:1,5',
            'psychomotor.drawing_painting'   => 'nullable|integer|between:1,5',
            'psychomotor.games'              => 'nullable|integer|between:1,5',
            'psychomotor.musical_skills'     => 'nullable|integer|between:1,5',
            'teacher_remark'                 => 'nullable|string|max:1000',
            'principal_remark'               => 'nullable|string|max:1000',
            'headmaster_remark'              => 'nullable|string|max:1000',
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

        // Determine which remark columns to save based on class type.
        // No role restriction — whoever can access this page can save.
        if ($isPrimary) {
            // Primary: save headmaster_remark, leave principal_remark as-is
            $principalRemark  = StudentRemark::where([
                'student_id' => $student->id,
                'class_id'   => $class->id,
                'session_id' => $currentSession->id,
                'term_id'    => $currentTerm->id,
            ])->value('principal_remark'); // preserve existing

            $headmasterRemark = $request->input('headmaster_remark');
        } else {
            // Secondary: save principal_remark, leave headmaster_remark as-is
            $principalRemark  = $request->input('principal_remark');

            $headmasterRemark = StudentRemark::where([
                'student_id' => $student->id,
                'class_id'   => $class->id,
                'session_id' => $currentSession->id,
                'term_id'    => $currentTerm->id,
            ])->value('headmaster_remark'); // preserve existing
        }

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
                'teacher_remark'      => $request->input('teacher_remark'),
                'principal_remark'    => $principalRemark,
                'headmaster_remark'   => $headmasterRemark,
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


    public function savePrimaryStudentResults(Request $request, $studentId)
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        $class   = SchoolClass::findOrFail($student->class_id);
        $user    = Auth::user();

        // Authorization
        if (!in_array($user->user_type, [1, 2])) {
            if (!$this->isTeacherAssignedToClass($user->id, $class->id)) {
                abort(403, 'You are not assigned to this class.');
            }
        }

        $currentSession = Session::where('is_current', true)->first();
        $currentTerm    = $currentSession?->terms()->where('is_current', true)->first();

        if (!$currentSession || !$currentTerm) {
            return redirect()->back()->with('error', 'No current academic session or term is set.');
        }

        // Validation — obtained values must not exceed the fixed obtainable maximums
        $request->validate([
            'results'                          => 'nullable|array',
            'results.*.first_half_obtained'    => 'nullable|numeric|min:0|max:30',
            'results.*.second_half_obtained'   => 'nullable|numeric|min:0|max:70',
            'results.*.teacher_remark'         => 'nullable|string|max:255',

            // Hidden obtainable fields submitted from the form (we will override them anyway)
            'results.*.first_half_obtainable'  => 'nullable|numeric',
            'results.*.second_half_obtainable' => 'nullable|numeric',
            'results.*.final_obtainable'       => 'nullable|numeric',
            'results.*.final_obtained'         => 'nullable|numeric|min:0|max:100',
        ]);

        foreach ($request->input('results', []) as $courseId => $data) {

            $firstObtained  = isset($data['first_half_obtained'])  && $data['first_half_obtained']  !== ''
                ? (float) $data['first_half_obtained']  : null;
            $secondObtained = isset($data['second_half_obtained']) && $data['second_half_obtained'] !== ''
                ? (float) $data['second_half_obtained'] : null;

            // Skip entirely if both halves are blank
            if ($firstObtained === null && $secondObtained === null) {
                continue;
            }

            // Auto-calculate final obtained = first + second (using 0 for any null half)
            $finalObtained = round(($firstObtained ?? 0) + ($secondObtained ?? 0), 2);

            // Clamp to 100 just in case
            $finalObtained = min($finalObtained, 100);

            \App\Models\PrimarySchoolResult::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'course_id'  => $courseId,
                    'session_id' => $currentSession->id,
                    'term_id'    => $currentTerm->id,
                ],
                [
                    // Fixed obtainable values — always stored server-side, not from form input
                    'first_half_obtainable'  => 30,
                    'second_half_obtainable' => 70,
                    'final_obtainable'       => 100,

                    // Obtained values
                    'first_half_obtained'    => $firstObtained,
                    'second_half_obtained'   => $secondObtained,
                    'final_obtained'         => $finalObtained,

                    // class_average removed — column no longer used
                    'class_average'          => null,

                    'teacher_remark'         => $data['teacher_remark'] ?? null,
                    'uploaded_by'            => Auth::id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Primary school results saved successfully.');
    }
}
