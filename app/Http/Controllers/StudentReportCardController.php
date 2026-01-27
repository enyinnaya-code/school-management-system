<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IssuedPin;
use App\Models\Pin;
use App\Models\Session;
use App\Models\Term;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Course;
use App\Models\Result;
use App\Models\StudentRemark;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class StudentReportCardController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Get sessions where the student has an issued PIN
        $sessions = Session::whereHas('issuedPins', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })->orderByDesc('name')->get();

        return view('students.report_cards.index', compact('sessions', 'student'));
    }

    public function verifyPin(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:school_sessions,id',
            'term_id'    => 'required|exists:terms,id',
            'pin'        => 'required|string|max:255',
        ]);

        $student = Auth::user();

        // Find the issued PIN record for this student + session + term
        $issuedPin = IssuedPin::where('student_id', $student->id)
            ->where('session_id', $request->session_id)
            ->where('term_id', $request->term_id)
            ->first();

        if (!$issuedPin) {
            return back()->with('error', 'No PIN has been issued to you for the selected session and term.');
        }

        $pin = Pin::findOrFail($issuedPin->pin_id);

        // Validate the PIN code
        if ($pin->pin_code !== strtoupper(trim($request->pin))) {
            return back()->with('error', 'Invalid PIN entered. Please try again.');
        }

        // Check if maximum usage (5) has been reached
        if ($pin->usage_count >= 5) {
            return back()->with('error', 'This PIN has been used 5 times and is no longer valid. Please contact the school administration.');
        }

        // Increment usage count
        $pin->increment('usage_count');

        // Optional: Mark as fully used when it reaches 5
        if ($pin->usage_count == 5) {
            $pin->update(['is_used' => true]);
        }

        // Grant access by storing in session
        session([
            'verified_report_access' => [
                'session_id' => $request->session_id,
                'term_id'    => $request->term_id,
                'expires_at' => now()->addHours(2), // optional timeout
            ]
        ]);

        return redirect()->route('students.reportcards.show')
            ->with('success', 'PIN verified successfully! You can now view your report card.');
    }


    public function showReport()
    {
        $student = Auth::user();

        $access = session('verified_report_access');

        // Security checks
        if (
            !$access ||
            !isset($access['session_id'], $access['term_id']) ||
            ($access['expires_at'] ?? null) < now()
        ) {
            return redirect()->route('students.reportcards.index')
                ->with('error', 'Unauthorized access. Please verify your PIN again.');
        }

        $sessionId = $access['session_id'];
        $termId    = $access['term_id'];

        // Final check: does student actually have issued PIN for this combo?
        $hasValidPin = IssuedPin::where('student_id', $student->id)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->exists();

        if (!$hasValidPin) {
            session()->forget('verified_report_access');
            return redirect()->route('students.reportcards.index')
                ->with('error', 'Access denied. Invalid session or term.');
        }

        $session = Session::findOrFail($sessionId);
        $term    = Term::findOrFail($termId);
        $class   = SchoolClass::findOrFail($student->class_id);

        // === SAME RESULT CALCULATION LOGIC AS BEFORE ===
        $allSubjects = Course::whereHas('schoolClasses', function ($q) use ($class) {
            $q->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

        $studentResults = Result::where('student_id', $student->id)
            ->where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->get()
            ->keyBy('course_id');

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

        $overallTotal   = $results->sum('total');
        $subjectCount   = $allSubjects->count();
        $overallAverage = $subjectCount > 0 ? round($overallTotal / $subjectCount, 2) : 0;
        $overallGrade   = $this->calculateGrade($overallAverage);

        $classStudents = User::where('user_type', 4)
            ->where('class_id', $class->id)
            ->pluck('id');

        $totalStudentsInClass = $classStudents->count();

        $studentsScores = Result::where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->whereIn('student_id', $classStudents)
            ->whereIn('course_id', $allSubjects->pluck('id'))
            ->select('student_id', DB::raw('SUM(total) as total_score'))
            ->groupBy('student_id')
            ->orderByDesc('total_score')
            ->get();

        $studentPosition = $studentsScores->search(fn($item) => $item->student_id == $student->id);
        $studentPosition = $studentPosition !== false ? $studentPosition + 1 : $totalStudentsInClass;
        $formattedPosition = $studentPosition . $this->getPositionSuffix($studentPosition);

        $classTeacher = User::where('is_form_teacher', true)
            ->where('form_class_id', $class->id)
            ->first();

        $remark = StudentRemark::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->first();

        $affectiveRatings   = array_merge($this->defaultRatings('affective'),   $remark?->affective_ratings ?? []);
        $psychomotorRatings = array_merge($this->defaultRatings('psychomotor'), $remark?->psychomotor_ratings ?? []);

        $teacherRemark   = $remark?->teacher_remark ?? '';
        $principalRemark = $remark?->principal_remark ?? '';

        return view('students.report_cards.report_card_view', [
            'student'              => $student,
            'class'                => $class,
            'results'              => $results,
            'overallTotal'         => $overallTotal,
            'overallAverage'       => $overallAverage,
            'overallGrade'         => $overallGrade,
            'currentSession'       => $session,
            'currentTerm'          => $term,
            'classTeacher'         => $classTeacher,
            'affectiveRatings'     => $affectiveRatings,
            'psychomotorRatings'   => $psychomotorRatings,
            'teacherRemark'        => $teacherRemark,
            'principalRemark'      => $principalRemark,
            'formattedPosition'    => $formattedPosition,
            'totalStudentsInClass' => $totalStudentsInClass,
            'subjectCount'         => $subjectCount,
        ]);
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

    private function getPositionSuffix($position)
    {
        if ($position % 100 >= 11 && $position % 100 <= 13) return 'th';
        return match ($position % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }

    private function defaultRatings($type)
    {
        return $type === 'affective' ? [
            'punctuality' => null,
            'politeness' => null,
            'neatness' => null,
            'honesty' => null,
            'leadership_skill' => null,
            'cooperation' => null,
            'attentiveness' => null,
            'perseverance' => null,
            'attitude_to_work' => null,
        ] : [
            'handwriting' => null,
            'verbal_fluency' => null,
            'sports' => null,
            'handling_tools' => null,
            'drawing_painting' => null,
        ];
    }


    public function issuedPins()
    {
        $student = Auth::user();

        $issuedPins = IssuedPin::where('student_id', $student->id)
            ->with(['session', 'term', 'section', 'schoolClass', 'pin'])
            ->orderByDesc('created_at')
            ->get();

        return view('students.report_cards.issued_pins', compact('issuedPins', 'student'));
    }
}
