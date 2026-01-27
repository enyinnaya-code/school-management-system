<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\IssuedPin;
use App\Models\Pin;
use App\Models\Session;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Course;
use App\Models\Result;
use App\Models\StudentRemark;
use Barryvdh\DomPDF\Facade\Pdf;

class ParentReportCardController extends Controller
{
    public function selectWard()
    {
        $parent = Auth::user();

        // Get all wards
        $wards = $parent->students()->with(['class.section'])->get();

        // Get all student IDs of wards
        $wardStudentIds = $wards->pluck('id');

        // Get distinct sessions where any ward has an issued PIN
        $sessions = Session::whereHas('issuedPins', function ($query) use ($wardStudentIds) {
            $query->whereIn('student_id', $wardStudentIds);
        })
            ->orderByDesc('name')
            ->get();

        return view('parents.wards.select_ward', compact('wards', 'sessions'));
    }


    public function verifyPin(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id'    => 'required|exists:terms,id',
            'pin'        => 'required|string',
        ]);

        $parent = Auth::user();
        $student = User::findOrFail($request->student_id);

        // Check if this student is actually a ward of the parent
        if (!$parent->students()->where('student_id', $student->id)->exists()) {
            return back()->with('error', 'You do not have permission to view this ward\'s report.');
        }

        $issuedPin = IssuedPin::where('student_id', $student->id)
            ->where('session_id', $request->session_id)
            ->where('term_id', $request->term_id)
            ->first();

        if (!$issuedPin) {
            return back()->with('error', 'No PIN issued for this ward in the selected session/term.');
        }

        $pin = Pin::findOrFail($issuedPin->pin_id);

        if ($pin->pin_code !== strtoupper(trim($request->pin))) {
            return back()->with('error', 'Invalid PIN.');
        }

        if ($pin->usage_count >= 5) {
            return back()->with('error', 'This PIN has been used 5 times and is expired.');
        }

        // Increment usage
        $pin->increment('usage_count');
        if ($pin->usage_count >= 5) {
            $pin->update(['is_used' => true]);
        }

        session([
            'parent_verified_report' => [
                'student_id' => $student->id,
                'session_id' => $request->session_id,
                'term_id'    => $request->term_id,
                'expires_at' => now()->addHours(2),
            ]
        ]);

        return redirect()->route('parents.wards.reportcards.view');
    }

    public function showReport()
    {
        $access = session('parent_verified_report');

        if (!$access || ($access['expires_at'] ?? null) < now()) {
            return redirect()->route('parents.wards.reportcards')
                ->with('error', 'Session expired. Please verify PIN again.');
        }

        $student = User::findOrFail($access['student_id']);
        $parent = Auth::user();

        if (!$parent->students()->where('student_id', $student->id)->exists()) {
            abort(403);
        }

        $currentSession = Session::findOrFail($access['session_id']);
        $currentTerm = Term::findOrFail($access['term_id']);
        $class = SchoolClass::findOrFail($student->class_id);

        // Same result calculation logic (copy from StudentReportCardController)
        $allSubjects = Course::whereHas('schoolClasses', function ($q) use ($class) {
            $q->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

        $studentResults = Result::where('student_id', $student->id)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
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

        $studentPosition = $studentsScores->search(fn($item) => $item->student_id == $student->id);
        $studentPosition = $studentPosition !== false ? $studentPosition + 1 : $totalStudentsInClass;
        $formattedPosition = $studentPosition . $this->getPositionSuffix($studentPosition);

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

        return view('parents.wards.report_card_view', compact(
            'student',
            'class',
            'results',
            'overallTotal',
            'overallAverage',
            'overallGrade',
            'currentSession',
            'currentTerm',
            'classTeacher',
            'affectiveRatings',
            'psychomotorRatings',
            'teacherRemark',
            'principalRemark',
            'formattedPosition',
            'totalStudentsInClass',
            'subjectCount'
        ));
    }

    public function downloadPdf()
    {
        // Same logic as showReport() but return PDF
        // ... (copy showReport logic and end with Pdf::loadView(...) -> stream()
        // Use the same view: parents.wards.report_card_view
    }

    public function issuedPins()
    {
        $parent = Auth::user();
        $wards = $parent->students()->with(['class'])->get();

        $issuedPins = IssuedPin::whereIn('student_id', $wards->pluck('id'))
            ->with(['student', 'session', 'term', 'section', 'schoolClass', 'pin'])
            ->orderByDesc('created_at')
            ->get();

        return view('parents.wards.issued_pins', compact('issuedPins', 'wards'));
    }

    // Helper methods
    private function calculateGrade($total)
    {
        if ($total >= 70) return 'A';
        if ($total >= 60) return 'B';
        if ($total >= 50) return 'C';
        if ($total >= 40) return 'D';
        if ($total >= 30) return 'E';
        return 'F';
    }

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

    private function defaultRatings($type)
    {
        if ($type === 'affective') {
            return [
                'punctuality' => '-',
                'politeness' => '-',
                'neatness' => '-',
                'honesty' => '-',
                'leadership_skill' => '-',
                'cooperation' => '-',
                'attentiveness' => '-',
                'perseverance' => '-',
                'attitude_to_work' => '-',
            ];
        }

        return [
            'handwriting' => '-',
            'verbal_fluency' => '-',
            'sports' => '-',
            'handling_tools' => '-',
            'drawing_painting' => '-',
        ];
    }
}
