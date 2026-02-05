<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use App\Models\Result;
use App\Models\Course;
use App\Models\Session;
use App\Models\Term;
use App\Models\StudentAttendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class StudentController extends Controller
{

    public function dashboard()
    {
        if (Auth::user()->user_type != 4) {
            abort(403, 'Unauthorized access.');
        }

        $student = User::with(['class.section', 'hostel'])->findOrFail(Auth::id());

        // Get current session and term
        $currentSession = \App\Models\Session::where('is_current', true)->first();
        $currentTerm = \App\Models\Term::where('is_current', true)->first();

        // Calculate attendance statistics for current term
        $attendanceStats = null;
        if ($student->class_id && $currentSession && $currentTerm) {
            $totalDays = StudentAttendance::where('student_id', $student->id)
                ->where('class_id', $student->class_id)
                ->where('session_id', $currentSession->id)
                ->where('session_term', $currentTerm->id)
                ->count();

            $presentDays = StudentAttendance::where('student_id', $student->id)
                ->where('class_id', $student->class_id)
                ->where('session_id', $currentSession->id)
                ->where('session_term', $currentTerm->id)
                ->where('attendance', 'Present')
                ->count();

            $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

            $attendanceStats = [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $totalDays - $presentDays,
                'attendance_rate' => $attendanceRate
            ];
        }

        // Get recent grades (last 5)
        $recentGrades = Result::with(['course'])
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate average grade (only if currentTerm exists)
        $allGrades = collect();
        if ($currentTerm) {
            $allGrades = Result::where('student_id', $student->id)
                ->where('term_id', $currentTerm->id)
                ->get();
        } else {
            // If no current term, get all results for the student
            $allGrades = Result::where('student_id', $student->id)->get();
        }

        $averageScore = $allGrades->count() > 0 ? round($allGrades->avg('total'), 1) : 0;

        // Determine letter grade
        $letterGrade = 'N/A';
        if ($averageScore >= 90) $letterGrade = 'A+';
        elseif ($averageScore >= 85) $letterGrade = 'A';
        elseif ($averageScore >= 80) $letterGrade = 'A-';
        elseif ($averageScore >= 75) $letterGrade = 'B+';
        elseif ($averageScore >= 70) $letterGrade = 'B';
        elseif ($averageScore >= 65) $letterGrade = 'C+';
        elseif ($averageScore >= 60) $letterGrade = 'C';
        elseif ($averageScore >= 55) $letterGrade = 'D';
        elseif ($averageScore > 0) $letterGrade = 'F';

        // Chart data for grade trend
        $chartLabels = $recentGrades->pluck('course.course_name')->reverse()->toArray();
        $chartData = $recentGrades->pluck('total')->reverse()->toArray();

        // Get subjects enrolled in
        $enrolledSubjects = collect();
        if ($student->class && $student->class->section) {
            $enrolledSubjects = Course::where('section_id', $student->class->section->id)->get();
        }

        return view('student_dashboard', compact(
            'student',
            'currentSession',
            'currentTerm',
            'attendanceStats',
            'recentGrades',
            'averageScore',
            'letterGrade',
            'chartLabels',
            'chartData',
            'enrolledSubjects'
        ));
    }

    public function create()
    {
        $sections = Section::all();
        return view('add_student', compact('sections'));
    }

    public function getClasses($section_id)
    {
        $classes = SchoolClass::where('section_id', $section_id)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json(['classes' => $classes]);
    }

    public function index()
    {
        if (!in_array(Auth::user()->user_type, [1, 2, 3, 7, 8, 9, 10])) {
            abort(403, 'Unauthorized access.');
        }

        $sections = Section::all();
        $selectedSection = request('filter_section');

        // Load classes: if section filtered, only those in the section; otherwise all
        $classes = $selectedSection
            ? SchoolClass::where('section_id', $selectedSection)->orderBy('name', 'asc')->get()
            : SchoolClass::orderBy('name', 'asc')->get();

        // Base query for students
        $query = User::where('user_type', 4)->with('class.section')->orderBy('id', 'desc');

        if ($filterName = request('filter_name')) {
            $query->where('name', 'like', "%{$filterName}%");
        }

        // Fix: Filter students by section through their class's section_id
        if ($selectedSection) {
            $query->whereHas('class', function ($q) use ($selectedSection) {
                $q->where('section_id', $selectedSection);
            });
        }

        if ($filterClass = request('filter_class')) {
            $query->where('class_id', $filterClass);
        }

        if ($filterGender = request('filter_gender')) {
            $query->where('gender', $filterGender);
        }

        if ($filterDateAdded = request('filter_date_added')) {
            $query->whereDate('created_at', $filterDateAdded);
        }

        $students = $query->paginate(20);

        return view('manage_students', compact('students', 'classes', 'sections'));
    }

    public function suspend(User $student)
    {
        $student->is_active = 0;
        $student->save();
        return redirect()->route('students.index')->with('success', 'Student suspended.');
    }

    public function activate(User $student)
    {
        $student->is_active = 1;
        $student->save();
        return redirect()->route('students.index')->with('success', 'Student activated.');
    }

    public function resetPassword(User $student)
    {
        $student->password = bcrypt('123456');
        $student->save();
        return redirect()->route('students.index')->with('success', 'Password reset to 123456.');
    }

    public function destroy(User $student)
    {
        if ($student->is_active) {
            return redirect()->route('students.index')->with('error', 'Active students cannot be deleted. Please suspend them first.');
        }

        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    /**
     * Helper: Normalize student email to end with @sms.com
     */
    private function normalizeStudentEmail($email)
    {
        $email = trim(strtolower($email));

        // If empty, return null (optional field)
        if (empty($email)) {
            return null;
        }

        // Remove any whitespace
        $email = preg_replace('/\s+/', '', $email);

        // If already ends with @sms.com, return as-is
        if (str_ends_with($email, '@sms.com')) {
            return $email;
        }

        // If contains @gmail.com, replace it
        if (str_contains($email, '@gmail.com')) {
            return str_replace('@gmail.com', '@sms.com', $email);
        }

        // If no @ at all, or invalid domain, append @sms.com
        if (!str_contains($email, '@')) {
            return $email . '@sms.com';
        }

        // Otherwise, replace everything after @ with sms.com
        return preg_replace('/@.+$/', '@sms.com', $email);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'dob' => 'required|date',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|string|in:Male,Female',
            'address' => 'nullable|string',

            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email',
            'guardian_address' => 'nullable|string',

            'section_id' => 'required|exists:sections,id',
            'class_id' => 'required|exists:school_classes,id',
        ]);

        // Normalize email
        $normalizedEmail = $this->normalizeStudentEmail($validated['email']);

        // Ensure email is unique (after normalization)
        if ($normalizedEmail) {
            $exists = User::where('email', $normalizedEmail)->exists();
            if ($exists) {
                return back()->withErrors(['email' => 'This email (after normalization) is already taken.'])->withInput();
            }
        }

        // Generate admission number
        $lastAdmission = User::whereNotNull('admission_no')
            ->orderByRaw("LPAD(admission_no, 4, '0') DESC")
            ->first();

        $newNumber = $lastAdmission && is_numeric($lastAdmission->admission_no)
            ? (int)$lastAdmission->admission_no + 1
            : 1;

        $newAdmissionNo = str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        while (User::where('admission_no', $newAdmissionNo)->exists()) {
            $newNumber++;
            $newAdmissionNo = str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        }

        User::create([
            'name' => strtoupper($validated['student_name']),
            'email' => $normalizedEmail,
            'admission_no' => $newAdmissionNo,
            'dob' => $validated['dob'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'guardian_name' => $validated['guardian_name'],
            'guardian_phone' => $validated['guardian_phone'],
            'guardian_email' => $validated['guardian_email'],
            'guardian_address' => $validated['guardian_address'],
            'section' => $validated['section_id'],
            'class_id' => $validated['class_id'],
            'user_type' => 4,
            'password' => bcrypt('student123'),
        ]);

        return redirect()->route('students.create')->with('success', 'Student added successfully.');
    }

    public function profile($id)
    {
        $user = User::with([
            'class.section',
            'classes.section',
            'courses.section',
            'students.class.section'
        ])->findOrFail($id);

        // Optional: Add authorization if needed (e.g., only allow viewing own profile or by admins/teachers)
        // if (!in_array(Auth::user()->user_type, [1, 2, 3]) && Auth::id() != $id) {
        //     abort(403);
        // }

        return view('profile', compact('user'));
    }

    public function performance($id, Request $request)
    {
        if (!in_array(Auth::user()->user_type, [1, 2, 3, 7, 8, 9, 10])) {
            abort(403, 'Unauthorized access.');
        }

        $student = User::where('user_type', 4)->with(['class.section'])->findOrFail($id);

        $subjects = collect();
        if ($student->class && $student->class->section) {
            $subjects = Course::where('section_id', $student->class->section->id)->get();
        }

        // Get all sessions and terms for dropdowns
        $sessions = \App\Models\Session::orderBy('id', 'desc')->get();
        $terms = Term::with('session')->orderBy('id', 'desc')->get();

        $resultsQuery = Result::with(['course', 'student', 'uploader'])
            ->where('student_id', $id);

        // Filter by session
        if ($sessionId = $request->get('session_id')) {
            $resultsQuery->where('session_id', $sessionId);
        }

        // Filter by term
        if ($termId = $request->get('term_id')) {
            $resultsQuery->where('term_id', $termId);
        }

        // Filter by subject
        if ($subjectId = $request->get('subject_id')) {
            $resultsQuery->where('course_id', $subjectId);
        }

        $results = $resultsQuery->orderBy('course_id')->get();

        $totalScores = $results->sum('total');
        $average = $results->count() > 0 ? $totalScores / $results->count() : 0;

        $chartData = [];
        $chartLabels = [];
        if ($results->isNotEmpty()) {
            $grouped = $results->groupBy(function ($result) {
                return $result->created_at->format('Y-m');
            })->map->avg('total');

            $chartLabels = $grouped->keys()->toArray();
            $chartData = $grouped->values()->toArray();
        }

        return view('student_performance', compact(
            'student',
            'results',
            'average',
            'sessionId',
            'termId',
            'subjectId',
            'subjects',
            'sessions',
            'terms',
            'chartLabels',
            'chartData'
        ));
    }

    public function edit($id)
    {
        if (!in_array(Auth::user()->user_type, [1, 2, 3, 7, 8, 9, 10])) {
            abort(403, 'Unauthorized access.');
        }

        $student = User::where('user_type', 4)->findOrFail($id);
        $classes = SchoolClass::all();
        $sections = Section::all();

        $studentClass = SchoolClass::find($student->class_id);
        $studentSectionId = $studentClass?->section_id;

        return view('edit_student', compact('student', 'classes', 'sections', 'studentSectionId'));
    }

    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->user_type, [1, 2, 3, 7, 8, 9, 10])) {
            abort(403, 'Unauthorized access.');
        }

        $student = User::where('user_type', 4)->findOrFail($id);

        $validated = $request->validate([
            'student_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'dob' => 'required|date',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:Male,Female',
            'address' => 'nullable|string',

            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email',
            'guardian_address' => 'nullable|string',

            'section_id' => 'required|exists:sections,id',
            'class_id' => 'required|exists:school_classes,id',
        ]);

        // Normalize email
        $normalizedEmail = $this->normalizeStudentEmail($validated['email']);

        // Check uniqueness (excluding current student)
        if ($normalizedEmail && $normalizedEmail !== $student->email) {
            $exists = User::where('email', $normalizedEmail)->where('id', '!=', $id)->exists();
            if ($exists) {
                return back()->withErrors(['email' => 'This email (after normalization) is already taken.'])->withInput();
            }
        }

        $student->update([
            'name' => strtoupper($validated['student_name']),
            'email' => $normalizedEmail,
            'dob' => $validated['dob'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'guardian_name' => $validated['guardian_name'],
            'guardian_phone' => $validated['guardian_phone'],
            'guardian_email' => $validated['guardian_email'],
            'guardian_address' => $validated['guardian_address'],
            'section' => $validated['section_id'],
            'class_id' => $validated['class_id'],
        ]);

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }


    public function myStudents()
    {
        // if (Auth::user()->user_type != 3) {
        //     abort(403, 'Unauthorized access.');
        // }

        $teacher = Auth::user();

        // Load teacher's assigned classes (with section for display)
        $assignedClasses = $teacher->classes()->with('section')->get();

        $assignedClassIds = $assignedClasses->pluck('id')->toArray();

        // Base query: only students in teacher's assigned classes
        $query = User::where('user_type', 4)
            ->whereIn('class_id', $assignedClassIds);

        // Apply class filter if selected
        if ($filterClass = request('filter_class')) {
            $query->where('class_id', $filterClass);
        }

        $students = $query->with('class.section')
            ->orderBy('name')
            ->paginate(20);

        return view('teachers.my_students', compact('students', 'assignedClasses'));
    }

    /**
     * Show the class promotion form
     */
    public function promoteForm()
    {
        $sections = Section::all();
        $sessions = Session::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();

        // Get current session and term
        $currentSession = Session::where('is_current', 1)->first();
        $currentTerm = Term::where('is_current', 1)->first();

        return view('students.promote', compact('sections', 'sessions', 'terms', 'currentSession', 'currentTerm'));
    }

    /**
     * Get third term ID for a given session
     */
    public function getThirdTerm($sessionId)
    {
        // Find the third term for this session
        // Assuming term naming convention includes "Third" or position = 3
        $thirdTerm = Term::where('session_id', $sessionId)
            ->where(function ($query) {
                $query->where('name', 'LIKE', '%Third%')
                    ->orWhere('name', 'LIKE', '%3rd%')
                    ->orWhere('name', 'LIKE', '%3%');
            })
            ->first();

        if (!$thirdTerm) {
            // Alternative: get the term with the highest ID (last term) for the session
            $thirdTerm = Term::where('session_id', $sessionId)
                ->orderBy('id', 'desc')
                ->first();
        }

        return response()->json([
            'term_id' => $thirdTerm ? $thirdTerm->id : null,
            'term_name' => $thirdTerm ? $thirdTerm->name : null
        ]);
    }

    /**
     * Get students for promotion preview with cumulative average calculation
     * Cumulative Average = (Term 1 Average + Term 2 Average + Term 3 Average) / Number of Terms
     */
    public function getPromotionPreview(Request $request)
    {
        // Log incoming request for debugging
        Log::info('Promotion Preview Request:', $request->all());

        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'current_class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|integer',
            'term_id' => 'required|integer',
        ]);

        $sessionId = $request->session_id;
        $termId = $request->term_id;

        // Verify session and term exist
        $session = Session::find($sessionId);
        $term = Term::find($termId);

        if (!$session) {
            return response()->json([
                'error' => 'Invalid session selected.'
            ], 400);
        }

        if (!$term) {
            return response()->json([
                'error' => 'Invalid term selected.'
            ], 400);
        }

        // Verify that the selected class belongs to the selected section
        $currentClass = SchoolClass::where('id', $request->current_class_id)
            ->where('section_id', $request->section_id)
            ->first();

        if (!$currentClass) {
            return response()->json([
                'error' => 'The selected class does not belong to the selected section.'
            ], 400);
        }

        $students = User::where('user_type', 4)
            ->where('class_id', $request->current_class_id)
            ->where('is_active', 1)
            ->with(['class.section'])
            ->get()
            ->map(function ($student) use ($sessionId) {
                // Get all terms for the session
                $sessionTerms = Term::where('session_id', $sessionId)
                    ->orderBy('id')
                    ->get();

                $termAverages = [];

                foreach ($sessionTerms as $term) {
                    // Get the student's class for this term from class_user table
                    $studentClass = DB::table('class_user')
                        ->where('user_id', $student->id)
                        ->first();

                    if (!$studentClass) {
                        $classId = $student->class_id;
                    } else {
                        $classId = $studentClass->school_class_id;
                    }

                    // Get total number of subjects for this class from class_course table
                    $subjectCount = DB::table('class_course')
                        ->where('school_class_id', $classId)
                        ->count();

                    if ($subjectCount == 0) {
                        Log::warning("No subjects found for student {$student->id} in class {$classId} for term {$term->id}");
                        continue;
                    }

                    // Get sum of total scores for this student in this term
                    $totalScores = Result::where('student_id', $student->id)
                        ->where('session_id', $sessionId)
                        ->where('term_id', $term->id)
                        ->sum('total');

                    // Check if student has any results for this term
                    $resultsCount = Result::where('student_id', $student->id)
                        ->where('session_id', $sessionId)
                        ->where('term_id', $term->id)
                        ->count();

                    if ($resultsCount > 0) {
                        // Term Average = Total Scores / Subject Count
                        // Make sure this is already a percentage (0-100)
                        $termAverage = ($totalScores / $subjectCount);
                        $termAverages[] = round($termAverage, 2);

                        Log::info("Student {$student->id} ({$student->name}) - Term {$term->name}: Total={$totalScores}, Subjects={$subjectCount}, Average={$termAverage}%");
                    }
                }

                // Calculate cumulative average as average of all term averages
                $cumulativeAverage = 0;
                if (count($termAverages) > 0) {
                    $cumulativeAverage = round(array_sum($termAverages) / count($termAverages), 2);
                }

                Log::info("Student {$student->id} ({$student->name}) - Final Cumulative Average: {$cumulativeAverage}%");
                Log::info("Term Averages: " . json_encode($termAverages));

                return [
                    'id' => $student->id,
                    'admission_number' => $student->admission_no,
                    'admission_no' => $student->admission_no,
                    'first_name' => $student->name,
                    'middle_name' => '',
                    'last_name' => '',
                    'name' => $student->name,
                    'school_class' => $student->class,
                    'class' => $student->class,
                    'cumulative_average' => $cumulativeAverage,
                    'terms_calculated' => count($termAverages),
                    'term_averages' => $termAverages
                ];
            });

        // Get all classes in the same section for "next class" options
        $nextClasses = SchoolClass::where('section_id', $request->section_id)
            ->where('id', '!=', $request->current_class_id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'students' => $students,
            'current_class' => $currentClass,
            'next_classes' => $nextClasses,
            'total_students' => $students->count()
        ]);
    }

    /**
     * Process class promotion with cumulative average-based or bulk promotion
     */
    public function processPromotion(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sessions,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
            'current_class_id' => 'required|exists:school_classes,id',
            'promotion_type' => 'required|in:bulk,performance',
            'promotion_config' => 'required|json',
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:users,id',
            'students.*.next_class_id' => 'nullable|exists:school_classes,id',
        ]);

        DB::beginTransaction();
        try {
            $promotedCount = 0;
            $repeatingCount = 0;
            $config = json_decode($request->promotion_config, true);

            foreach ($request->students as $studentData) {
                $student = User::where('user_type', 4)
                    ->where('id', $studentData['student_id'])
                    ->where('is_active', 1)
                    ->first();

                if ($student) {
                    if (!empty($studentData['next_class_id'])) {
                        // Promote student
                        $student->class_id = $studentData['next_class_id'];
                        $student->save();
                        $promotedCount++;
                    } else {
                        // Student repeats (stays in same class)
                        $repeatingCount++;
                    }
                }
            }

            DB::commit();

            $message = "Successfully processed promotion: {$promotedCount} student(s) promoted";
            if ($repeatingCount > 0) {
                $message .= ", {$repeatingCount} student(s) repeating current class";
            }

            return redirect()->route('students.promote')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error promoting students: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get promotion preview for multiple classes
     * Supports selecting multiple source classes (e.g., JSS1A, JSS1B, JSS1C)
     */
    public function getPromotionPreviewMultiple(Request $request)
    {
        Log::info('Multiple Class Promotion Preview Request:', $request->all());

        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'class_ids' => 'required|array',
            'class_ids.*' => 'required|exists:school_classes,id',
            'session_id' => 'required|integer',
            'term_id' => 'required|integer',
        ]);

        $sessionId = $request->session_id;
        $termId = $request->term_id;
        $classIds = $request->class_ids;

        // Verify session and term exist
        $session = Session::find($sessionId);
        $term = Term::find($termId);

        if (!$session) {
            return response()->json(['error' => 'Invalid session selected.'], 400);
        }

        if (!$term) {
            return response()->json(['error' => 'Invalid term selected.'], 400);
        }

        // Verify all selected classes belong to the selected section
        $validClasses = SchoolClass::where('section_id', $request->section_id)
            ->whereIn('id', $classIds)
            ->count();

        if ($validClasses !== count($classIds)) {
            return response()->json([
                'error' => 'Some selected classes do not belong to the selected section.'
            ], 400);
        }

        // Get students from all selected classes
        $students = User::where('user_type', 4)
            ->whereIn('class_id', $classIds)
            ->where('is_active', 1)
            ->with(['class.section'])
            ->get()
            ->map(function ($student) use ($sessionId) {
                // Get all terms for the session
                $sessionTerms = Term::where('session_id', $sessionId)
                    ->orderBy('id')
                    ->get();

                $termAverages = [];

                foreach ($sessionTerms as $term) {
                    // Get student's class for this term
                    $studentClass = DB::table('class_user')
                        ->where('user_id', $student->id)
                        ->first();

                    $classId = $studentClass ? $studentClass->school_class_id : $student->class_id;

                    // Get total number of subjects for this class
                    $subjectCount = DB::table('class_course')
                        ->where('school_class_id', $classId)
                        ->count();

                    if ($subjectCount == 0) {
                        Log::warning("No subjects found for student {$student->id} in class {$classId} for term {$term->id}");
                        continue;
                    }

                    // Get sum of total scores for this student in this term
                    $totalScores = Result::where('student_id', $student->id)
                        ->where('session_id', $sessionId)
                        ->where('term_id', $term->id)
                        ->sum('total');

                    // Check if student has any results for this term
                    $resultsCount = Result::where('student_id', $student->id)
                        ->where('session_id', $sessionId)
                        ->where('term_id', $term->id)
                        ->count();

                    if ($resultsCount > 0) {
                        $termAverage = ($totalScores / $subjectCount);
                        $termAverages[] = round($termAverage, 2);

                        Log::info("Student {$student->id} ({$student->name}) - Term {$term->name}: Total={$totalScores}, Subjects={$subjectCount}, Average={$termAverage}%");
                    }
                }

                // Calculate cumulative average
                $cumulativeAverage = 0;
                if (count($termAverages) > 0) {
                    $cumulativeAverage = round(array_sum($termAverages) / count($termAverages), 2);
                }

                Log::info("Student {$student->id} ({$student->name}) - Cumulative Average: {$cumulativeAverage}%");

                return [
                    'id' => $student->id,
                    'admission_number' => $student->admission_no,
                    'admission_no' => $student->admission_no,
                    'first_name' => $student->name,
                    'middle_name' => '',
                    'last_name' => '',
                    'name' => $student->name,
                    'school_class' => $student->class,
                    'class' => $student->class,
                    'cumulative_average' => $cumulativeAverage,
                    'terms_calculated' => count($termAverages),
                    'term_averages' => $termAverages
                ];
            });

        // Get potential destination classes (all classes in same section except source classes)
        $nextClasses = SchoolClass::where('section_id', $request->section_id)
            ->whereNotIn('id', $classIds)
            ->orderBy('name')
            ->get();

        // Also get current enrollment counts for destination classes
        $classEnrollments = User::where('user_type', 4)
            ->where('is_active', 1)
            ->whereIn('class_id', $nextClasses->pluck('id'))
            ->select('class_id', DB::raw('count(*) as student_count'))
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        $nextClasses = $nextClasses->map(function ($class) use ($classEnrollments) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'current_enrollment' => $classEnrollments->get($class->id)?->student_count ?? 0
            ];
        });

        return response()->json([
            'students' => $students,
            'source_classes' => SchoolClass::whereIn('id', $classIds)->get(),
            'next_classes' => $nextClasses,
            'total_students' => $students->count()
        ]);
    }

    /**
     * Process promotion for multiple classes with capacity management and tracking
     */
    public function processPromotionEnhanced(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer',
            'term_id' => 'required|integer',
            'section_id' => 'required|exists:sections,id',
            'current_class_ids' => 'required|json',
            'promotion_type' => 'required|in:bulk,performance',
            'promotion_config' => 'required|json',
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:users,id',
            'students.*.next_class_id' => 'nullable|exists:school_classes,id',
        ]);

        DB::beginTransaction();
        try {
            $config = json_decode($request->promotion_config, true);
            $currentClassIds = json_decode($request->current_class_ids, true);

            // Generate unique batch ID
            $promotionBatchId = 'PROMO_' . date('Y') . '_' . str_pad(
                DB::table('promotions')->whereYear('created_at', date('Y'))->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            // Get source class names
            $sourceClasses = SchoolClass::whereIn('id', $currentClassIds)->pluck('name')->toArray();
            $sourceClassNames = implode(', ', $sourceClasses);

            // Create main promotion record
            $promotionId = DB::table('promotions')->insertGetId([
                'promotion_batch_id' => $promotionBatchId,
                'session_id' => $request->session_id,
                'term_id' => $request->term_id,
                'section_id' => $request->section_id,
                'source_class_ids' => json_encode($currentClassIds),
                'source_class_names' => $sourceClassNames,
                'promotion_type' => $request->promotion_type,
                'promotion_config' => $request->promotion_config,
                'total_students' => count($request->students),
                'promoted_count' => 0, // Will update after processing
                'repeating_count' => 0, // Will update after processing
                'destination_classes_count' => count($config['destination_classes'] ?? []),
                'status' => 'pending',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Track capacity usage
            $capacityUsed = [];
            $capacityRecords = [];

            if (isset($config['capacities'])) {
                foreach ($config['capacities'] as $classId => $capacity) {
                    $capacityUsed[$classId] = 0;

                    // Get initial enrollment
                    $initialEnrollment = User::where('user_type', 4)
                        ->where('class_id', $classId)
                        ->where('is_active', 1)
                        ->count();

                    $className = SchoolClass::find($classId)->name ?? 'Unknown';

                    $capacityRecords[$classId] = [
                        'promotion_id' => $promotionId,
                        'class_id' => $classId,
                        'class_name' => $className,
                        'max_capacity' => $capacity['max'],
                        'students_assigned' => 0,
                        'available_slots' => $capacity['max'],
                        'initial_enrollment' => $initialEnrollment,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            $promotedCount = 0;
            $repeatingCount = 0;
            $promotionRecords = [];

            // Process each student
            foreach ($request->students as $studentData) {
                $student = User::where('user_type', 4)
                    ->where('id', $studentData['student_id'])
                    ->where('is_active', 1)
                    ->first();

                if (!$student) {
                    continue;
                }

                // Get student's current class info
                $originalClass = SchoolClass::find($student->class_id);

                // Get student's cumulative average
                $cumulativeAverage = $this->calculateCumulativeAverage(
                    $student->id,
                    $request->session_id
                );

                $promotionStatus = 'repeating';
                $newClassId = null;
                $newClassName = null;
                $promotionReason = 'No available slots or did not meet criteria';

                if (!empty($studentData['next_class_id'])) {
                    $nextClassId = $studentData['next_class_id'];

                    // Verify capacity
                    $canPromote = true;
                    if (isset($config['capacities'][$nextClassId])) {
                        $maxCapacity = $config['capacities'][$nextClassId]['max'];

                        if (!isset($capacityUsed[$nextClassId])) {
                            $capacityUsed[$nextClassId] = 0;
                        }

                        if ($capacityUsed[$nextClassId] >= $maxCapacity) {
                            $canPromote = false;
                            $promotionReason = 'Class capacity exceeded';
                            Log::warning("Capacity exceeded for class {$nextClassId}, student {$student->id} will repeat");
                        }
                    }

                    if ($canPromote) {
                        // Update student's class
                        $student->class_id = $nextClassId;
                        $student->save();

                        $newClass = SchoolClass::find($nextClassId);
                        $newClassId = $nextClassId;
                        $newClassName = $newClass->name ?? 'Unknown';
                        $promotionStatus = 'promoted';
                        $promotionReason = $request->promotion_type === 'performance'
                            ? "Met performance criteria (Avg: {$cumulativeAverage}%)"
                            : 'Bulk promotion';

                        // Update capacity tracking
                        if (isset($capacityUsed[$nextClassId])) {
                            $capacityUsed[$nextClassId]++;
                            $capacityRecords[$nextClassId]['students_assigned']++;
                            $capacityRecords[$nextClassId]['available_slots']--;
                        }

                        $promotedCount++;

                        Log::info("Student {$student->id} ({$student->name}) promoted to class {$nextClassId}");
                    } else {
                        $repeatingCount++;
                    }
                } else {
                    $repeatingCount++;
                }

                // Create promotion record for this student
                $promotionRecords[] = [
                    'promotion_id' => $promotionId,
                    'student_id' => $student->id,
                    'original_class_id' => $originalClass->id,
                    'original_class_name' => $originalClass->name,
                    'new_class_id' => $newClassId,
                    'new_class_name' => $newClassName,
                    'cumulative_average' => $cumulativeAverage,
                    'term_averages' => json_encode($this->getTermAverages($student->id, $request->session_id)),
                    'promotion_status' => $promotionStatus,
                    'promotion_reason' => $promotionReason,
                    'is_rolled_back' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Batch insert promotion records
            if (!empty($promotionRecords)) {
                DB::table('promotion_records')->insert($promotionRecords);
            }

            // Insert capacity records
            if (!empty($capacityRecords)) {
                DB::table('promotion_class_capacities')->insert(array_values($capacityRecords));
            }

            // Update main promotion record with final counts
            DB::table('promotions')->where('id', $promotionId)->update([
                'promoted_count' => $promotedCount,
                'repeating_count' => $repeatingCount,
                'status' => 'completed',
                'updated_at' => now(),
            ]);

            DB::commit();

            // Build success message
            $message = "Successfully completed promotion batch {$promotionBatchId}: " .
                "{$promotedCount} student(s) promoted, " .
                "{$repeatingCount} student(s) repeating current class";

            Log::info("Promotion completed: {$message}");

            return redirect()->route('students.promote')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Promotion error: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Mark promotion as failed if it was created
            if (isset($promotionId)) {
                DB::table('promotions')->where('id', $promotionId)->update([
                    'status' => 'failed',
                    'notes' => 'Error: ' . $e->getMessage(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->back()
                ->with('error', 'Error promoting students: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Calculate cumulative average for a student
     */
    private function calculateCumulativeAverage($studentId, $sessionId)
    {
        $sessionTerms = Term::where('session_id', $sessionId)->orderBy('id')->get();
        $termAverages = [];

        foreach ($sessionTerms as $term) {
            $studentClass = DB::table('class_user')
                ->where('user_id', $studentId)
                ->first();

            $classId = $studentClass ? $studentClass->school_class_id : User::find($studentId)->class_id;

            $subjectCount = DB::table('class_course')
                ->where('school_class_id', $classId)
                ->count();

            if ($subjectCount == 0) {
                continue;
            }

            $totalScores = Result::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('term_id', $term->id)
                ->sum('total');

            $resultsCount = Result::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('term_id', $term->id)
                ->count();

            if ($resultsCount > 0) {
                $termAverages[] = round($totalScores / $subjectCount, 2);
            }
        }

        return count($termAverages) > 0
            ? round(array_sum($termAverages) / count($termAverages), 2)
            : 0;
    }

    /**
     * Get term averages for a student
     */
    private function getTermAverages($studentId, $sessionId)
    {
        $sessionTerms = Term::where('session_id', $sessionId)->orderBy('id')->get();
        $termAverages = [];

        foreach ($sessionTerms as $term) {
            $studentClass = DB::table('class_user')
                ->where('user_id', $studentId)
                ->first();

            $classId = $studentClass ? $studentClass->school_class_id : User::find($studentId)->class_id;

            $subjectCount = DB::table('class_course')
                ->where('school_class_id', $classId)
                ->count();

            if ($subjectCount == 0) {
                continue;
            }

            $totalScores = Result::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('term_id', $term->id)
                ->sum('total');

            $resultsCount = Result::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('term_id', $term->id)
                ->count();

            if ($resultsCount > 0) {
                $termAverages[$term->name] = round($totalScores / $subjectCount, 2);
            }
        }

        return $termAverages;
    }

    /**
     * View promotion history
     */
    /**
     * View promotion history
     */
    public function promotionHistory()
{
    $promotions = DB::table('promotions as p')
        // FIXED: Changed 'sessions' to 'school_sessions'
        ->leftJoin('school_sessions as s', 'p.session_id', '=', 's.id')
        ->leftJoin('terms as t', 'p.term_id', '=', 't.id')
        ->leftJoin('sections as sec', 'p.section_id', '=', 'sec.id')
        ->leftJoin('users as u', 'p.processed_by', '=', 'u.id')
        ->leftJoin('users as rb_user', 'p.rolled_back_by', '=', 'rb_user.id')
        ->select(
            'p.id',
            'p.promotion_batch_id',
            'p.session_id',
            DB::raw('COALESCE(s.name, CONCAT("Unknown Session (ID: ", p.session_id, ")")) as session_name'),
            'p.term_id',
            DB::raw('COALESCE(t.name, CONCAT("Unknown Term (ID: ", p.term_id, ")")) as term_name'),
            'p.section_id',
            DB::raw('COALESCE(sec.section_name, CONCAT("Unknown Section (ID: ", p.section_id, ")")) as section_name'),
            'p.source_class_ids',
            'p.source_class_names',
            'p.promotion_type',
            'p.promotion_config',
            'p.total_students',
            'p.promoted_count',
            'p.repeating_count',
            'p.destination_classes_count',
            'p.status',
            'p.processed_by',
            DB::raw('COALESCE(u.name, CONCAT("Unknown User (ID: ", p.processed_by, ")")) as processed_by_name'),
            'p.processed_at',
            'p.rolled_back_by',
            DB::raw('COALESCE(rb_user.name, NULL) as rolled_back_by_name'),
            'p.rolled_back_at',
            'p.rollback_reason',
            'p.notes',
            'p.created_at',
            'p.updated_at',
            DB::raw('CASE WHEN p.total_students > 0 THEN ROUND((p.promoted_count / p.total_students) * 100, 1) ELSE 0 END as promotion_rate')
        )
        ->orderBy('p.processed_at', 'desc')
        ->paginate(20);

    return view('students.promotion_history', compact('promotions'));
}




    /**
     * View specific promotion details
     */
    public function viewPromotionDetails($promotionId)
{
    $promotion = DB::table('promotions as p')
        // FIXED: Changed 'sessions' to 'school_sessions'
        ->join('school_sessions as s', 'p.session_id', '=', 's.id')
        ->join('terms as t', 'p.term_id', '=', 't.id')
        ->join('sections as sec', 'p.section_id', '=', 'sec.id')
        ->join('users as u', 'p.processed_by', '=', 'u.id')
        ->leftJoin('users as rb_user', 'p.rolled_back_by', '=', 'rb_user.id')
        ->where('p.id', $promotionId)
        ->select(
            'p.*',
            's.name as session_name',
            't.name as term_name',
            'sec.section_name',
            'u.name as processed_by_name',
            'rb_user.name as rolled_back_by_name',
            DB::raw('ROUND((p.promoted_count / p.total_students) * 100, 2) as promotion_rate')
        )
        ->first();

    if (!$promotion) {
        abort(404, 'Promotion not found');
    }

    $students = DB::table('promotion_records as pr')
        ->join('promotions as p', 'pr.promotion_id', '=', 'p.id')
        // FIXED: Changed 'sessions' to 'school_sessions'
        ->join('school_sessions as s', 'p.session_id', '=', 's.id')
        ->join('terms as t', 'p.term_id', '=', 't.id')
        ->join('users as u', 'pr.student_id', '=', 'u.id')
        ->join('users as processor', 'p.processed_by', '=', 'processor.id')
        ->where('pr.promotion_id', $promotionId)
        ->select(
            'pr.*',
            'p.promotion_batch_id',
            'p.session_id',
            's.name as session_name',
            'p.term_id',
            't.name as term_name',
            'u.admission_no',
            'u.name as student_name',
            'u.gender',
            'p.promotion_type',
            'p.status as promotion_batch_status',
            'p.processed_at',
            'p.processed_by',
            'processor.name as processed_by_name'
        )
        ->orderBy('u.name')
        ->get();

    $capacities = DB::table('promotion_class_capacities as pcc')
        ->join('promotions as p', 'pcc.promotion_id', '=', 'p.id')
        // FIXED: Changed 'sessions' to 'school_sessions'
        ->join('school_sessions as s', 'p.session_id', '=', 's.id')
        ->where('pcc.promotion_id', $promotionId)
        ->select(
            'pcc.*',
            'p.promotion_batch_id',
            'p.session_id',
            's.name as session_name',
            'p.processed_at',
            'p.status as promotion_status',
            DB::raw('ROUND((pcc.students_assigned / pcc.max_capacity) * 100, 2) as utilization_percentage'),
            DB::raw('(pcc.initial_enrollment + pcc.students_assigned) as final_enrollment')
        )
        ->get();

    return view('students.promotion_details', compact('promotion', 'students', 'capacities'));
}


    /**
     * Rollback a promotion
     */
    public function rollbackPromotion(Request $request, $promotionId)
    {
        $request->validate([
            'rollback_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $promotion = DB::table('promotions')->where('id', $promotionId)->first();

            if (!$promotion) {
                throw new \Exception('Promotion not found');
            }

            if ($promotion->status === 'rolled_back') {
                throw new \Exception('This promotion has already been rolled back');
            }

            // Get all promotion records
            $records = DB::table('promotion_records')
                ->where('promotion_id', $promotionId)
                ->where('is_rolled_back', false)
                ->get();

            $rolledBackCount = 0;

            foreach ($records as $record) {
                // Only rollback students who were actually promoted
                if ($record->promotion_status === 'promoted' && $record->new_class_id) {
                    // Restore student to original class
                    User::where('id', $record->student_id)
                        ->update(['class_id' => $record->original_class_id]);

                    // Mark record as rolled back
                    DB::table('promotion_records')
                        ->where('id', $record->id)
                        ->update([
                            'is_rolled_back' => true,
                            'rolled_back_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $rolledBackCount++;
                }
            }

            // Update main promotion record
            DB::table('promotions')->where('id', $promotionId)->update([
                'status' => 'rolled_back',
                'rolled_back_by' => Auth::id(),
                'rolled_back_at' => now(),
                'rollback_reason' => $request->rollback_reason,
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info("Promotion {$promotion->promotion_batch_id} rolled back by " . Auth::user()->name);

            return redirect()->route('students.promotion.history')
                ->with('success', "Promotion rolled back successfully. {$rolledBackCount} students restored to their original classes.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Rollback error: " . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error rolling back promotion: ' . $e->getMessage());
        }
    }
}
