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
use App\Models\TermSetting;
use App\Models\ClassSubjectLimit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Services\ResultSheetService;

class StudentReportCardController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // Helper: check if the current student is blocked for a session+term.
    // ──────────────────────────────────────────────────────────────────────────
    private function getBlock(int $studentId, int $sessionId, int $termId): ?ResultAccessRestriction
    {
        return ResultAccessRestriction::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->first();
    }


    // ──────────────────────────────────────────────────────────────────────────
    // Helper: apply custom subject limit logic for secondary classes.
    //
    // Returns:
    //   'adjusted_total'  — total after dropping lowest if scored count > min
    //   'average_divisor' — always min_subjects when a limit exists
    //   'dropped_course'  — course_name of dropped subject, or null
    //
    // Rules:
    //   1. Only subjects where final_obtained > 0 are considered "scored".
    //   2. If scored count > min → drop the ONE subject with the lowest score.
    //   3. adjusted_total = sum of kept subjects only.
    //   4. average_divisor = min_subjects (always, even if < min scored).
    //   5. If no limit configured → standard behaviour (sum all / count scored).
    // ──────────────────────────────────────────────────────────────────────────
    private function applySubjectLimit($results, int $classId): array
    {
        $limit = ClassSubjectLimit::where('school_class_id', $classId)->first();

        if (!$limit) {
            $scored  = $results->filter(fn($r) => ($r['final_obtained'] ?? 0) > 0);
            $divisor = $scored->count() > 0 ? $scored->count() : 1;
            return [
                'adjusted_total'  => $results->sum('final_obtained'),
                'average_divisor' => $divisor,
                'dropped_course'  => null,
            ];
        }

        $minSubjects   = (int) $limit->min_subjects;
        $scored        = $results->filter(fn($r) => ($r['final_obtained'] ?? 0) > 0);
        $droppedCourse = null;

        if ($scored->count() > $minSubjects) {
            $excessCount   = $scored->count() - $minSubjects;          // ← how many to drop
            $sorted        = $scored->sortBy('final_obtained');
            $droppedKeys   = $sorted->keys()->take($excessCount)->toArray();
            $droppedCourse = $results[$droppedKeys[0]]['course_name'] ?? null;
            $adjustedTotal = $scored->except($droppedKeys)->sum('final_obtained');
        } else {
            $adjustedTotal = $scored->sum('final_obtained');
        }

        return [
            'adjusted_total'  => $adjustedTotal,
            'average_divisor' => $minSubjects,
            'dropped_course'  => $droppedCourse,
        ];
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

        $block = $this->getBlock($student->id, $sessionId, $termId);
        if ($block) {
            session()->forget('verified_report_access');
            $reason = $block->reason ?: 'You have been restricted from accessing your result.';
            return redirect()->route('students.reportcards.index')
                ->with('error', 'Access denied: ' . $reason . ' Please contact the school administration.');
        }

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

        $termSettings = TermSetting::where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->first();

        // ── Check if this class has a result sheet template (term-name match first, fallback to any) ──
        $sheetTemplate = DB::table('result_sheet_templates')
            ->where('is_active', 1)
            ->get()
            ->first(function ($t) use ($class, $term) {
                $classes    = json_decode($t->applicable_classes ?? '[]', true);
                $classMatch = in_array($class->id, $classes) || in_array((string) $class->id, $classes);
                $termMatch  = !empty($t->term_name) && $t->term_name === $term->name;
                return $classMatch && $termMatch;
            });

        // Fallback: any active template for this class regardless of term
        if (!$sheetTemplate) {
            $sheetTemplate = DB::table('result_sheet_templates')
                ->where('is_active', 1)
                ->get()
                ->first(function ($t) use ($class) {
                    $classes = json_decode($t->applicable_classes ?? '[]', true);
                    return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
                });
        }

        // ── If class has a template, ALWAYS render the result sheet ──────────────
        // regardless of whether the class is also in primary_result_classes
        if ($sheetTemplate) {
            return $this->showResultSheet($student, $class, $session, $term, $sheetTemplate);
        }

        return $this->showStandardReport($student, $class, $session, $term, $termSettings);
    }


    public function showSheet()
    {
        $student = Auth::user();
        $access  = session('verified_report_access');

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

        $block = $this->getBlock($student->id, $sessionId, $termId);
        if ($block) {
            session()->forget('verified_report_access');
            $reason = $block->reason ?: 'You have been restricted from accessing your result.';
            return redirect()->route('students.reportcards.index')
                ->with('error', 'Access denied: ' . $reason . ' Please contact the school administration.');
        }

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

        // ── Check template (term-name match first, then fallback) ────────────────
        $sheetTemplate = DB::table('result_sheet_templates')
            ->where('is_active', 1)
            ->get()
            ->first(function ($t) use ($class, $term) {
                $classes    = json_decode($t->applicable_classes ?? '[]', true);
                $classMatch = in_array($class->id, $classes) || in_array((string) $class->id, $classes);
                $termMatch  = !empty($t->term_name) && $t->term_name === $term->name;
                return $classMatch && $termMatch;
            });

        if (!$sheetTemplate) {
            $sheetTemplate = DB::table('result_sheet_templates')
                ->where('is_active', 1)
                ->get()
                ->first(function ($t) use ($class) {
                    $classes = json_decode($t->applicable_classes ?? '[]', true);
                    return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
                });
        }

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
    private function showStandardReport($student, $class, $session, $term, $termSettings)
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

        // ── Attendance summary ────────────────────────────────────────────────
        $attendanceSummary = \App\Models\StudentAttendance::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('session_id', $session->id)
            ->where('session_term', $term->id)
            ->selectRaw("
                COUNT(*) as total_days,
                SUM(CASE WHEN attendance = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN attendance = 'Absent'  THEN 1 ELSE 0 END) as absent
            ")
            ->first();

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
                $r             = $studentPrimaryResults->get($subject->id);
                $finalObtained = $r?->final_obtained ?? 0;
                return [
                    'course_name'            => $subject->course_name,
                    'first_half_obtainable'  => $r?->first_half_obtainable  ?? 30,
                    'first_half_obtained'    => $r?->first_half_obtained    ?? 0,
                    'second_half_obtainable' => $r?->second_half_obtainable ?? 70,
                    'second_half_obtained'   => $r?->second_half_obtained   ?? 0,
                    'final_obtainable'       => $r?->final_obtainable       ?? 100,
                    'final_obtained'         => $finalObtained,
                    'grade'                  => $finalObtained > 0 ? $this->calculateGrade($finalObtained) : '-',
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
                'attendanceSummary'    => $attendanceSummary,
                'termSettings'         => $termSettings,
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

        // ── Apply custom subject limit (drop lowest if scored count > min) ────
        $limitData      = $this->applySubjectLimit($results, $class->id);
        $overallTotal   = $limitData['adjusted_total'];
        $averageDivisor = $limitData['average_divisor'];
        $droppedCourse  = $limitData['dropped_course'];

        $overallAverage = $averageDivisor > 0 ? round($overallTotal / $averageDivisor, 2) : 0;
        $overallGrade   = $this->calculateGrade($overallAverage);
        $subjectCount   = $averageDivisor;

        // ── Position ranking: apply same drop-lowest logic per classmate ──────
        $allClassResults = Result::where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->whereIn('student_id', $classStudents)
            ->whereIn('course_id', $allSubjects->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $studentRankTotals = $classStudents->map(function ($sid) use ($allClassResults, $class) {
            $sResults = $allClassResults->get($sid, collect())->map(fn($r) => [
                'course_name'    => '',
                'final_obtained' => (float) ($r->final_obtained ?? 0),
            ]);
            $ld = $this->applySubjectLimit(collect($sResults), $class->id);
            return ['student_id' => $sid, 'adjusted_total' => $ld['adjusted_total']];
        })->sortByDesc('adjusted_total')->values();

        $studentPosition   = $studentRankTotals->search(fn($item) => $item['student_id'] == $student->id);
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
            'attendanceSummary'    => $attendanceSummary,
            'termSettings'         => $termSettings,
            'droppedCourse'        => $droppedCourse,
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
