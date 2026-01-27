<?php

namespace App\Http\Controllers;

use App\Models\ExamQuestion;
use App\Models\Section;
use App\Models\Session;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamQuestionController extends Controller
{
    /**
     * Check if a teacher is assigned to a specific class
     * (directly via class_user or indirectly via course_user)
     */
    private function isTeacherAssignedToClass($userId, $classId)
    {
        // Direct assignment through class_user table
        $direct = DB::table('class_user')
            ->where('user_id', $userId)
            ->where('school_class_id', $classId)
            ->exists();

        // Indirect assignment through course_user (specific class or general)
        $viaCourse = DB::table('course_user')
            ->where('user_id', $userId)
            ->where(function ($query) use ($classId) {
                $query->where('class_id', $classId)
                      ->orWhereNull('class_id');
            })
            ->exists();

        return $direct || $viaCourse;
    }

    /**
     * Get all sections the current user is allowed to access
     */
    private function getAllowedSections($userId, $userType)
    {
        if (in_array($userType, [1, 2])) {
            // Admins see all sections
            return Section::orderBy('section_name')->get();
        }

        // Get section IDs from direct class assignments
        $sectionsFromClasses = DB::table('class_user')
            ->join('school_classes', 'class_user.school_class_id', '=', 'school_classes.id')
            ->where('class_user.user_id', $userId)
            ->pluck('school_classes.section_id')
            ->unique();

        // Get section IDs from course assignments (where class_id is not null)
        $sectionsFromCourses = DB::table('course_user')
            ->join('school_classes', 'course_user.class_id', '=', 'school_classes.id')
            ->where('course_user.user_id', $userId)
            ->whereNotNull('course_user.class_id')
            ->pluck('school_classes.section_id')
            ->unique();

        $allowedSectionIds = $sectionsFromClasses->merge($sectionsFromCourses)->unique();

        return Section::whereIn('id', $allowedSectionIds)
                      ->orderBy('section_name')
                      ->get();
    }

    /**
     * Get all classes assigned to a teacher (direct or via courses)
     */
    private function getTeacherClasses($userId)
    {
        $classesFromDirect = DB::table('class_user')
            ->join('school_classes', 'class_user.school_class_id', '=', 'school_classes.id')
            ->where('class_user.user_id', $userId)
            ->select('school_classes.*')
            ->get();

        $classesFromCourses = DB::table('course_user')
            ->join('school_classes', 'course_user.class_id', '=', 'school_classes.id')
            ->where('course_user.user_id', $userId)
            ->whereNotNull('course_user.class_id')
            ->select('school_classes.*')
            ->get();

        return $classesFromDirect->merge($classesFromCourses)->unique('id')->sortBy('name');
    }

    /**
     * Get all subjects (courses) assigned to a teacher
     * Optionally filtered by section and/or class
     */
    private function getTeacherSubjects($userId, $sectionId = null, $classId = null)
    {
        $query = DB::table('course_user')
            ->join('courses', 'course_user.course_id', '=', 'courses.id')
            ->where('course_user.user_id', $userId)
            ->select('courses.*')
            ->distinct();

        if ($sectionId) {
            $query->where('courses.section_id', $sectionId);
        }

        if ($classId) {
            $query->where(function ($q) use ($classId) {
                $q->where('course_user.class_id', $classId)
                  ->orWhereNull('course_user.class_id');
            });
        }

        return $query->orderBy('courses.course_name')->get();
    }

    /**
     * Display a listing of exams (teacher's own or all for admins)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ExamQuestion::with(['section', 'session', 'term', 'schoolClass', 'subject', 'creator']);

        if (!in_array($user->user_type, [1, 2])) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        $exams = $query->orderByDesc('created_at')->paginate(15);

        $sections = $this->getAllowedSections($user->id, $user->user_type);

        if (in_array($user->user_type, [1, 2])) {
            $subjects = Course::orderBy('course_name')->get();
            $classes = SchoolClass::orderBy('name')->get();
        } else {
            $subjects = $this->getTeacherSubjects($user->id);
            $classes = $this->getTeacherClasses($user->id);
        }

        $terms = Term::orderByDesc('created_at')->get();

        return view('exam_questions.index', compact('exams', 'subjects', 'classes', 'terms', 'sections'));
    }

    /**
     * Show the form for creating a new exam
     */
    public function create()
    {
        $user = Auth::user();
        $sections = $this->getAllowedSections($user->id, $user->user_type);

        $examTypes = [
            'Mid-Term Exam',
            'End of Term Exam',
            'Mock Exam',
            'Practice Test',
            'Quiz',
            'Assessment',
            'Other'
        ];

        return view('exam_questions.create', compact('sections', 'examTypes'));
    }

    /**
     * Store a newly created exam in storage
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:courses,id',
            'exam_title' => 'required|string|max:255',
            'exam_type' => 'required|string',
            'exam_date' => 'nullable|date',
            'duration_minutes' => 'nullable|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'instructions' => 'nullable|string',
            'sections' => 'required|array|min:1',
            'status' => 'required|in:draft,published',
            'school_name' => 'nullable|string|max:255',
            'school_address' => 'nullable|string|max:500',
            'show_marking_scheme' => 'boolean',
            'marking_scheme' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Security check for non-admin users
        if (!in_array($user->user_type, [1, 2])) {
            if (!$this->isTeacherAssignedToClass($user->id, $validated['class_id'])) {
                abort(403, 'You are not authorized to create exams for this class.');
            }

            $isAssignedToSubject = DB::table('course_user')
                ->where('user_id', $user->id)
                ->where('course_id', $validated['subject_id'])
                ->exists();

            if (!$isAssignedToSubject) {
                abort(403, 'You are not assigned to teach this subject.');
            }
        }

        $validated['created_by'] = $user->id;
        $validated['show_marking_scheme'] = $request->has('show_marking_scheme');

        $exam = ExamQuestion::create($validated);

        return redirect()->route('exam_questions.show', $exam->id)
            ->with('success', 'Exam created successfully!');
    }

    /**
     * Display the specified exam
     */
    public function show($id)
    {
        $exam = ExamQuestion::with(['section', 'session', 'term', 'schoolClass', 'subject', 'creator'])
            ->findOrFail($id);

        $user = Auth::user();
        if (!in_array($user->user_type, [1, 2]) && $exam->created_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        return view('exam_questions.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified exam
     */
    public function edit($id)
    {
        $exam = ExamQuestion::findOrFail($id);

        $user = Auth::user();
        if (!in_array($user->user_type, [1, 2]) && $exam->created_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        $sections = $this->getAllowedSections($user->id, $user->user_type);
        $sessions = Session::where('section_id', $exam->section_id)->orderByDesc('name')->get();
        $terms = Term::where('session_id', $exam->session_id)->orderBy('name')->get();

        if (in_array($user->user_type, [1, 2])) {
            $classes = SchoolClass::where('section_id', $exam->section_id)->orderBy('name')->get();
            $subjects = Course::where('section_id', $exam->section_id)->orderBy('course_name')->get();
        } else {
            $classes = $this->getTeacherClasses($user->id)
                ->where('section_id', $exam->section_id);
            $subjects = $this->getTeacherSubjects($user->id, $exam->section_id);
        }

        $examTypes = [
            'Mid-Term Exam',
            'End of Term Exam',
            'Mock Exam',
            'Practice Test',
            'Quiz',
            'Assessment',
            'Other'
        ];

        return view('exam_questions.edit', compact('exam', 'sections', 'sessions', 'terms', 'classes', 'subjects', 'examTypes'));
    }

    /**
     * Update the specified exam in storage
     */
    public function update(Request $request, $id)
    {
        $exam = ExamQuestion::findOrFail($id);

        $user = Auth::user();
        if (!in_array($user->user_type, [1, 2]) && $exam->created_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:courses,id',
            'exam_title' => 'required|string|max:255',
            'exam_type' => 'required|string',
            'exam_date' => 'nullable|date',
            'duration_minutes' => 'nullable|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'instructions' => 'nullable|string',
            'sections' => 'required|array|min:1',
            'status' => 'required|in:draft,published,archived',
            'school_name' => 'nullable|string|max:255',
            'school_address' => 'nullable|string|max:500',
            'show_marking_scheme' => 'boolean',
            'marking_scheme' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if (!in_array($user->user_type, [1, 2])) {
            if (!$this->isTeacherAssignedToClass($user->id, $validated['class_id'])) {
                abort(403, 'You are not authorized to update exams for this class.');
            }
        }

        $validated['show_marking_scheme'] = $request->has('show_marking_scheme');

        $exam->update($validated);

        return redirect()->route('exam_questions.show', $exam->id)
            ->with('success', 'Exam updated successfully!');
    }

    /**
     * Remove the specified exam from storage
     */
    public function destroy($id)
    {
        $exam = ExamQuestion::findOrFail($id);

        $user = Auth::user();
        if (!in_array($user->user_type, [1, 2]) && $exam->created_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        $exam->delete();

        return redirect()->route('exam_questions.index')
            ->with('success', 'Exam deleted successfully!');
    }

    /**
     * Print the exam paper
     */
    public function print($id)
    {
        $exam = ExamQuestion::with(['section', 'session', 'term', 'schoolClass', 'subject', 'creator'])
            ->findOrFail($id);

        $user = Auth::user();
        if (!in_array($user->user_type, [1, 2]) && $exam->created_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        return view('exam_questions.print', compact('exam'));
    }

    /**
     * Duplicate an existing exam
     */
    public function duplicate($id)
    {
        $exam = ExamQuestion::findOrFail($id);

        $user = Auth::user();
        if (!in_array($user->user_type, [1, 2]) && $exam->created_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        $newExam = $exam->replicate();
        $newExam->exam_title = $exam->exam_title . ' (Copy)';
        $newExam->status = 'draft';
        $newExam->created_by = $user->id;
        $newExam->save();

        return redirect()->route('exam_questions.edit', $newExam->id)
            ->with('success', 'Exam duplicated successfully! You can now edit it.');
    }

    /**
     * Admin-only: View all exams across the school
     */
    public function allExams(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->user_type, [1, 2])) {
            abort(403, 'Unauthorized access');
        }

        $query = ExamQuestion::with(['section', 'session', 'term', 'schoolClass', 'subject', 'creator']);

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $exams = $query->orderByDesc('created_at')->paginate(20);

        $sections = Section::orderBy('section_name')->get();

        return view('exam_questions.all_exams', compact('exams', 'sections'));
    }

    /**
     * AJAX: Get sessions for a section + return current session ID
     */
    public function getSessions($sectionId)
    {
        $sessions = Session::where('section_id', $sectionId)
            ->orderByDesc('name')
            ->get();

        $currentSession = $sessions->firstWhere('is_current', true);

        return response()->json([
            'sessions' => $sessions,
            'current_session_id' => $currentSession?->id ?? null,
        ]);
    }

    /**
     * AJAX: Get terms for a session + return current term ID
     */
    public function getTerms($sessionId)
    {
        $terms = Term::where('session_id', $sessionId)
            ->orderBy('name')
            ->get();

        $currentTerm = $terms->firstWhere('is_current', true);

        return response()->json([
            'terms' => $terms,
            'current_term_id' => $currentTerm?->id ?? null,
        ]);
    }

    /**
     * AJAX: Get classes for a section (filtered for teacher assignments)
     */
    public function getClasses($sectionId)
    {
        $user = Auth::user();

        $query = SchoolClass::where('section_id', $sectionId);

        if (!in_array($user->user_type, [1, 2])) {
            $query->where(function ($q) use ($user) {
                $q->whereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))
                        ->from('class_user')
                        ->whereColumn('class_user.school_class_id', 'school_classes.id')
                        ->where('class_user.user_id', $user->id);
                })->orWhereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))
                        ->from('course_user')
                        ->whereColumn('course_user.class_id', 'school_classes.id')
                        ->where('course_user.user_id', $user->id);
                });
            });
        }

        $classes = $query->orderBy('name')->get();

        return response()->json(['classes' => $classes]);
    }

    /**
     * AJAX: Get subjects (courses) for a teacher in a section and optional class
     */
    public function getSubjects($sectionId, $classId = null)
    {
        $user = Auth::user();

        if (in_array($user->user_type, [1, 2])) {
            $query = Course::where('section_id', $sectionId);
            if ($classId) {
                $query->where(function ($q) use ($classId) {
                    $q->where('class_id', $classId)->orWhereNull('class_id');
                });
            }
            $subjects = $query->orderBy('course_name')->get();
        } else {
            $subjects = $this->getTeacherSubjects($user->id, $sectionId, $classId);
        }

        return response()->json(['subjects' => $subjects]);
    }
}