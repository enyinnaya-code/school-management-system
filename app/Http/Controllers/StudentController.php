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

        return response()->json($classes);
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

        $resultsQuery = Result::with(['course', 'student', 'uploader'])
            ->where('student_id', $id);

        if ($term = $request->get('term')) {
            $resultsQuery->where('term', $term);
        }

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
            'term',
            'subjectId',
            'subjects',
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
            'email' => 'required|email',
            'gender' => 'required|in:Male,Female',
            'class_id' => 'required|exists:school_classes,id',
        ]);

        // Normalize email
        $normalizedEmail = $this->normalizeStudentEmail($validated['email']);

        // Check uniqueness (excluding current student)
        if ($normalizedEmail !== $student->email) {
            $exists = User::where('email', $normalizedEmail)->where('id', '!=', $id)->exists();
            if ($exists) {
                return back()->withErrors(['email' => 'This email (after normalization) is already taken.'])->withInput();
            }
        }

        $student->update([
            'name' => strtoupper($validated['student_name']),
            'email' => $normalizedEmail,
            'gender' => $validated['gender'],
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
}
