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
use App\Models\ResultAccessRestriction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Services\ResultSheetService;

class StudentReportCardController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // Helper: check if the current student is blocked for a session+term.
    // Returns the restriction model (truthy) or null (falsy).
    // ──────────────────────────────────────────────────────────────────────────
    private function getBlock(int $studentId, int $sessionId, int $termId): ?ResultAccessRestriction
    {
        return ResultAccessRestriction::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->first();
    }


    public function index()
    {
        $student  = Auth::user();
        $sessions = Session::orderByDesc('name')->get();
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

        // ── Block check BEFORE pin validation ────────────────────────────────
        // We check here so the student cannot use a valid PIN to bypass the block.
        $block = $this->getBlock($student->id, $request->session_id, $request->term_id);
        if ($block) {
            $reason = $block->reason ?: 'You have been restricted from accessing your result.';
            return back()->with(
                'error',
                'Access denied: ' . $reason . ' Please contact the school administration.'
            );
        }

        // ── PIN existence check ───────────────────────────────────────────────
        $issuedPin = IssuedPin::where('student_id', $student->id)
            ->where('session_id', $request->session_id)
            ->where('term_id', $request->term_id)
            ->first();

        if (!$issuedPin) {
            return back()->with('error', 'No PIN has been issued to you for the selected session and term.');
        }

        $pin = Pin::findOrFail($issuedPin->pin_id);

        if ($pin->pin_code !== strtoupper(trim($request->pin))) {
            return back()->with('error', 'Invalid PIN entered. Please try again.');
        }

        if ($pin->usage_count >= 5) {
            return back()->with('error', 'This PIN has been used 5 times and is no longer valid. Please contact the school administration.');
        }

        $pin->increment('usage_count');

        if ($pin->usage_count == 5) {
            $pin->update(['is_used' => true]);
        }

        session([
            'verified_report_access' => [
                'session_id' => $request->session_id,
                'term_id'    => $request->term_id,
                'expires_at' => now()->addHours(2),
            ]
        ]);

        $class = SchoolClass::find($student->class_id);

        $usesResultSheet = false;
        if ($class) {
            $usesResultSheet = DB::table('result_sheet_templates')
                ->where('term_id', $request->term_id)
                ->where('is_active', 1)
                ->get()
                ->contains(function ($t) use ($class) {
                    $classes = json_decode($t->applicable_classes ?? '[]', true);
                    return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
                });
        }

        if ($usesResultSheet) {
            return redirect()->route('students.reportcards.sheet')
                ->with('success', 'PIN verified successfully! You can now view your report card.');
        }

        return redirect()->route('students.reportcards.show')
            ->with('success', 'PIN verified successfully! You can now view your report card.');
    }


    public function showReport()
    {
        $student = Auth::user();
        $access  = session('verified_report_access');

        // ── Session/expiry guard ──────────────────────────────────────────────
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

        // ── Block check ───────────────────────────────────────────────────────
        // Even if the student passed PIN verification, a block added afterwards
        // (or a block that was set before the session was stored) will deny access.
        $block = $this->getBlock($student->id, $sessionId, $termId);
        if ($block) {
            session()->forget('verified_report_access'); // clear cached access
            $reason = $block->reason ?: 'You have been restricted from accessing your result.';
            return redirect()->route('students.reportcards.index')
                ->with('error', 'Access denied: ' . $reason . ' Please contact the school administration.');
        }

        // ── Issued-pin existence guard ────────────────────────────────────────
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

        $sheetTemplate = DB::table('result_sheet_templates')
            ->where('term_id', $term->id)
            ->where('is_active', 1)
            ->get()
            ->first(function ($t) use ($class) {
                $classes = json_decode($t->applicable_classes ?? '[]', true);
                return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
            });

        if ($sheetTemplate) {
            return $this->showResultSheet($student, $class, $session, $term, $sheetTemplate);
        }

        return $this->showStandardReport($student, $class, $session, $term);
    }


    public function showSheet()
    {
        $student = Auth::user();
        $access  = session('verified_report_access');

        // ── Session/expiry guard ──────────────────────────────────────────────
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

        // ── Block check ───────────────────────────────────────────────────────
        $block = $this->getBlock($student->id, $sessionId, $termId);
        if ($block) {
            session()->forget('verified_report_access');
            $reason = $block->reason ?: 'You have been restricted from accessing your result.';
            return redirect()->route('students.reportcards.index')
                ->with('error', 'Access denied: ' . $reason . ' Please contact the school administration.');
        }

        // ── Issued-pin existence guard ────────────────────────────────────────
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

        $sheetTemplate = DB::table('result_sheet_templates')
            ->where('term_id', $term->id)
            ->where('is_active', 1)
            ->get()
            ->first(function ($t) use ($class) {
                $classes = json_decode($t->applicable_classes ?? '[]', true);
                return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
            });

        if (!$sheetTemplate) {
            return redirect()->route('students.reportcards.show');
        }

        return $this->showResultSheet($student, $class, $session, $term, $sheetTemplate);
    }


    private function showResultSheet($student, $class, $session, $term, $sheetTemplate)
    {
        $sheetTemplate->rating_columns = json_decode($sheetTemplate->rating_columns ?? '[]');
        $sheetTemplate->footer_fields  = json_decode($sheetTemplate->footer_fields ?? '{}', true);

        $service  = new ResultSheetService();
        $subjects = $service->loadTemplateStructure($sheetTemplate->id);

        $allItemIds = collect($subjects)->flatMap(function ($subject) {
            $ids = collect($subject->items)->pluck('id');
            foreach ($subject->subcategories as $sub) {
                $ids = $ids->merge(collect($sub->items)->pluck('id'));
            }
            return $ids;
        });

        $ratings = DB::table('result_sheet_ratings')
            ->where('student_id', $student->id)
            ->where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->whereIn('item_id', $allItemIds)
            ->get(['item_id', 'rating_value'])
            ->mapWithKeys(fn($row) => [(int) $row->item_id => trim($row->rating_value)])
            ->toArray();

        $footerData = DB::table('result_sheet_footer_data')
            ->where('student_id', $student->id)
            ->where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->where('template_id', $sheetTemplate->id)
            ->first();

        $section = \App\Models\Section::find($class->section_id);

        return view('students.report_cards.result_sheet_view', [
            'student'        => $student,
            'class'          => $class,
            'section'        => $section,
            'currentSession' => $session,
            'currentTerm'    => $term,
            'sheetTemplate'  => $sheetTemplate,
            'subjects'       => $subjects,
            'ratings'        => $ratings,
            'footerData'     => $footerData,
        ]);
    }


    /**
     * Standard report — handles BOTH primary and secondary automatically.
     */
    private function showStandardReport($student, $class, $session, $term)
    {
        // ── Detect primary class ──────────────────────────────────────────────
        $isPrimary = DB::table('primary_result_classes')
            ->where('school_class_id', $class->id)
            ->exists();

        $allSubjects = Course::whereHas('schoolClasses', function ($q) use ($class) {
            $q->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

        $classStudents        = User::where('user_type', 4)->where('class_id', $class->id)->pluck('id');
        $totalStudentsInClass = $classStudents->count();

        $classTeacher = User::where('is_form_teacher', true)
            ->where('form_class_id', $class->id)
            ->first();

        $remark = StudentRemark::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->first();

        $affectiveRatings   = array_merge($this->defaultRatings('affective'),   $remark?->affective_ratings   ?? []);
        $psychomotorRatings = array_merge($this->defaultRatings('psychomotor'), $remark?->psychomotor_ratings ?? []);

        $teacherRemark    = $remark?->teacher_remark    ?? '';
        $principalRemark  = $remark?->principal_remark  ?? '';
        $headmasterRemark = $remark?->headmaster_remark ?? '';

        // ══════════════════════════════════════════════════════════════════════
        // PRIMARY PATH
        // ══════════════════════════════════════════════════════════════════════
        if ($isPrimary) {

            $studentPrimaryResults = \App\Models\PrimarySchoolResult::where('student_id', $student->id)
                ->where('session_id', $session->id)
                ->where('term_id', $term->id)
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

            $allStudentTotals = \App\Models\PrimarySchoolResult::where('session_id', $session->id)
                ->where('term_id', $term->id)
                ->whereIn('student_id', $classStudents)
                ->select('student_id', DB::raw('SUM(final_obtained) as total_score'))
                ->groupBy('student_id')
                ->orderByDesc('total_score')
                ->get();

            $studentPosition   = $allStudentTotals->search(fn($item) => $item->student_id == $student->id);
            $studentPosition   = $studentPosition !== false ? $studentPosition + 1 : $totalStudentsInClass;
            $formattedPosition = $studentPosition . $this->getPositionSuffix($studentPosition);

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
                'headmasterRemark'     => $headmasterRemark,
                'principalRemark'      => '',
                'formattedPosition'    => $formattedPosition,
                'totalStudentsInClass' => $totalStudentsInClass,
                'subjectCount'         => $subjectCount,
                'isPrimary'            => true,
            ]);
        }

        // ══════════════════════════════════════════════════════════════════════
        // SECONDARY PATH
        // ══════════════════════════════════════════════════════════════════════

        $studentResults = Result::where('student_id', $student->id)
            ->where('session_id', $session->id)
            ->where('term_id', $term->id)
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

        $studentsScores = Result::where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->whereIn('student_id', $classStudents)
            ->whereIn('course_id', $allSubjects->pluck('id'))
            ->select('student_id', DB::raw('SUM(total) as total_score'))
            ->groupBy('student_id')
            ->orderByDesc('total_score')
            ->get();

        $studentPosition   = $studentsScores->search(fn($item) => $item->student_id == $student->id);
        $studentPosition   = $studentPosition !== false ? $studentPosition + 1 : $totalStudentsInClass;
        $formattedPosition = $studentPosition . $this->getPositionSuffix($studentPosition);

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
            'headmasterRemark'     => '',
            'formattedPosition'    => $formattedPosition,
            'totalStudentsInClass' => $totalStudentsInClass,
            'subjectCount'         => $subjectCount,
            'isPrimary'            => false,
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
        if ($type === 'affective') {
            return [
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
        }

        return [
            'handwriting'      => null,
            'verbal_fluency'   => null,
            'sports'           => null,
            'handling_tools'   => null,
            'drawing_painting' => null,
            'games'            => null,
            'musical_skills'   => null,
        ];
    }


    public function issuedPins()
    {
        $student    = Auth::user();
        $issuedPins = IssuedPin::where('student_id', $student->id)
            ->with(['session', 'term', 'section', 'schoolClass', 'pin'])
            ->orderByDesc('created_at')
            ->get();

        return view('students.report_cards.issued_pins', compact('issuedPins', 'student'));
    }
}