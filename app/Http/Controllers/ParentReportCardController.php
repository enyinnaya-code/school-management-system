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
use App\Models\Section;
use App\Models\Course;
use App\Models\Result;
use App\Models\StudentRemark;
use App\Models\ResultAccessRestriction;
use App\Models\TermSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ResultSheetService;

class ParentReportCardController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // Helper: check if a student is blocked for a given session + term.
    // ──────────────────────────────────────────────────────────────────────────
    private function getBlock(int $studentId, int $sessionId, int $termId): ?ResultAccessRestriction
    {
        return ResultAccessRestriction::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->first();
    }


    public function selectWard()
    {
        $parent = Auth::user();
        $wards  = $parent->students()->with(['class.section'])->get();

        $wardStudentIds = $wards->pluck('id');

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

        $parent  = Auth::user();
        $student = User::findOrFail($request->student_id);

        // ── Parent-ward relationship check ────────────────────────────────────
        if (!$parent->students()->where('student_id', $student->id)->exists()) {
            return back()->with('error', 'You do not have permission to view this ward\'s report.');
        }

        // ── Block check BEFORE pin validation ────────────────────────────────
        $block = $this->getBlock($student->id, $request->session_id, $request->term_id);
        if ($block) {
            $reason = $block->reason ?: 'This ward has been restricted from accessing their result.';
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
            return back()->with('error', 'No PIN issued for this ward in the selected session/term.');
        }

        $pin = Pin::findOrFail($issuedPin->pin_id);

        if ($pin->pin_code !== strtoupper(trim($request->pin))) {
            return back()->with('error', 'Invalid PIN.');
        }

        if ($pin->usage_count >= 5) {
            return back()->with('error', 'This PIN has been used 5 times and is expired.');
        }

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

        // ── Session / expiry guard ────────────────────────────────────────────
        if (!$access || ($access['expires_at'] ?? null) < now()) {
            return redirect()->route('parents.wards.reportcards')
                ->with('error', 'Session expired. Please verify PIN again.');
        }

        $student = User::findOrFail($access['student_id']);
        $parent  = Auth::user();

        // ── Parent-ward relationship guard ────────────────────────────────────
        if (!$parent->students()->where('student_id', $student->id)->exists()) {
            abort(403);
        }

        // ── Block check ───────────────────────────────────────────────────────
        $block = $this->getBlock($student->id, $access['session_id'], $access['term_id']);
        if ($block) {
            session()->forget('parent_verified_report');
            $reason = $block->reason ?: 'This ward has been restricted from accessing their result.';
            return redirect()->route('parents.wards.reportcards')
                ->with('error', 'Access denied: ' . $reason . ' Please contact the school administration.');
        }

        $currentSession = Session::findOrFail($access['session_id']);
        $currentTerm    = Term::findOrFail($access['term_id']);
        $class          = SchoolClass::findOrFail($student->class_id);
        $section        = Section::find($class->section_id);

        $termSettings = TermSetting::where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->first();

        $sheetTemplate = DB::table('result_sheet_templates')
            ->where('is_active', 1)
            ->get()
            ->first(function ($t) use ($class) {
                $classes = json_decode($t->applicable_classes ?? '[]', true);
                return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
            });

        if ($sheetTemplate) {
            return $this->showResultSheet(
                $student, $class, $section, $currentSession, $currentTerm, $sheetTemplate, $termSettings
            );
        }

        $isPrimary = DB::table('primary_result_classes')
            ->where('school_class_id', $class->id)
            ->exists();

        if ($isPrimary) {
            return $this->showPrimaryReport(
                $student, $class, $section, $currentSession, $currentTerm, $termSettings
            );
        }

        return $this->showSecondaryReport(
            $student, $class, $section, $currentSession, $currentTerm, $termSettings
        );
    }


    // ──────────────────────────────────────────────────────────────────────────
    // Download PDF — same data as showReport(), rendered via DomPDF
    // ──────────────────────────────────────────────────────────────────────────
    public function downloadPdf()
    {
        $access = session('parent_verified_report');

        // ── Session / expiry guard ────────────────────────────────────────────
        if (!$access || ($access['expires_at'] ?? null) < now()) {
            return redirect()->route('parents.wards.reportcards')
                ->with('error', 'Session expired. Please verify PIN again.');
        }

        $student = User::findOrFail($access['student_id']);
        $parent  = Auth::user();

        // ── Parent-ward relationship guard ────────────────────────────────────
        if (!$parent->students()->where('student_id', $student->id)->exists()) {
            abort(403);
        }

        // ── Block check ───────────────────────────────────────────────────────
        $block = $this->getBlock($student->id, $access['session_id'], $access['term_id']);
        if ($block) {
            session()->forget('parent_verified_report');
            $reason = $block->reason ?: 'This ward has been restricted from accessing their result.';
            return redirect()->route('parents.wards.reportcards')
                ->with('error', 'Access denied: ' . $reason . ' Please contact the school administration.');
        }

        $currentSession = Session::findOrFail($access['session_id']);
        $currentTerm    = Term::findOrFail($access['term_id']);
        $class          = SchoolClass::findOrFail($student->class_id);
        $section        = Section::find($class->section_id);

        $termSettings = TermSetting::where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->first();

        // ── Build view data (same logic as showReport) ────────────────────────
        $sheetTemplate = DB::table('result_sheet_templates')
            ->where('is_active', 1)
            ->get()
            ->first(function ($t) use ($class) {
                $classes = json_decode($t->applicable_classes ?? '[]', true);
                return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
            });

        if ($sheetTemplate) {
            $viewData = $this->buildResultSheetData(
                $student, $class, $section, $currentSession, $currentTerm, $sheetTemplate, $termSettings
            );
        } else {
            $isPrimary = DB::table('primary_result_classes')
                ->where('school_class_id', $class->id)
                ->exists();

            $viewData = $isPrimary
                ? $this->buildPrimaryData($student, $class, $section, $currentSession, $currentTerm, $termSettings)
                : $this->buildSecondaryData($student, $class, $section, $currentSession, $currentTerm, $termSettings);
        }

        $pdf = Pdf::loadView('students.report_cards.report_card_pdf', $viewData)
            ->setPaper('a4', 'portrait');

        $filename = 'ReportCard_' . strtoupper(str_replace(' ', '_', $student->name))
            . '_' . $currentTerm->name
            . '_' . $currentSession->name
            . '.pdf';

        return $pdf->download($filename);
    }


    // ═══════════════════════════════════════════════════════════════════════════
    // NURSERY — custom result sheet (ratings/checkboxes)
    // ═══════════════════════════════════════════════════════════════════════════
    private function showResultSheet($student, $class, $section, $session, $term, $sheetTemplate, $termSettings)
    {
        $data = $this->buildResultSheetData($student, $class, $section, $session, $term, $sheetTemplate, $termSettings);
        return view('parents.wards.report_card_view', $data);
    }

    private function buildResultSheetData($student, $class, $section, $session, $term, $sheetTemplate, $termSettings)
    {
        $sheetTemplate->rating_columns = json_decode($sheetTemplate->rating_columns ?? '[]');
        $sheetTemplate->footer_fields  = json_decode($sheetTemplate->footer_fields  ?? '{}', true);

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

        $attendanceSummary = $this->getAttendance($student->id, $class->id, $session->id, $term->id);

        return [
            'student'              => $student,
            'class'                => $class,
            'section'              => $section,
            'currentSession'       => $session,
            'currentTerm'          => $term,
            'classTeacher'         => $this->getClassTeacher($class->id),
            'termSettings'         => $termSettings,
            'isNursery'            => true,
            'isPrimary'            => false,
            'sheetTemplate'        => $sheetTemplate,
            'subjects'             => $subjects,
            'ratings'              => $ratings,
            'footerData'           => $footerData,
            'results'              => collect(),
            'overallTotal'         => 0,
            'overallAverage'       => 0,
            'overallGrade'         => '-',
            'formattedPosition'    => '-',
            'totalStudentsInClass' => 0,
            'subjectCount'         => 0,
            'affectiveRatings'     => $this->defaultRatings('affective'),
            'psychomotorRatings'   => $this->defaultRatings('psychomotor'),
            'teacherRemark'        => '',
            'principalRemark'      => '',
            'headmasterRemark'     => '',
            'attendanceSummary'    => $attendanceSummary,
        ];
    }


    // ═══════════════════════════════════════════════════════════════════════════
    // PRIMARY SCHOOL — 1st Half / 2nd Half / Total / Grade
    // ═══════════════════════════════════════════════════════════════════════════
    private function showPrimaryReport($student, $class, $section, $session, $term, $termSettings)
    {
        $data = $this->buildPrimaryData($student, $class, $section, $session, $term, $termSettings);
        return view('parents.wards.report_card_view', $data);
    }

    private function buildPrimaryData($student, $class, $section, $session, $term, $termSettings)
    {
        $allSubjects = Course::whereHas('schoolClasses', function ($q) use ($class) {
            $q->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

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

        $classStudents        = User::where('user_type', 4)->where('class_id', $class->id)->pluck('id');
        $totalStudentsInClass = $classStudents->count();

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

        $remark = StudentRemark::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->first();

        return [
            'student'              => $student,
            'class'                => $class,
            'section'              => $section,
            'currentSession'       => $session,
            'currentTerm'          => $term,
            'classTeacher'         => $this->getClassTeacher($class->id),
            'termSettings'         => $termSettings,
            'results'              => $results,
            'overallTotal'         => $overallTotal,
            'overallAverage'       => $overallAverage,
            'overallGrade'         => $overallGrade,
            'formattedPosition'    => $formattedPosition,
            'totalStudentsInClass' => $totalStudentsInClass,
            'subjectCount'         => $subjectCount,
            'affectiveRatings'     => array_merge($this->defaultRatings('affective'),   $remark?->affective_ratings   ?? []),
            'psychomotorRatings'   => array_merge($this->defaultRatings('psychomotor'), $remark?->psychomotor_ratings ?? []),
            'teacherRemark'        => $remark?->teacher_remark    ?? '',
            'headmasterRemark'     => $remark?->headmaster_remark ?? '',
            'principalRemark'      => '',
            'attendanceSummary'    => $this->getAttendance($student->id, $class->id, $session->id, $term->id),
            'isPrimary'            => true,
            'isNursery'            => false,
            'sheetTemplate'        => null,
            'subjects'             => collect(),
            'ratings'              => [],
            'footerData'           => null,
        ];
    }


    // ═══════════════════════════════════════════════════════════════════════════
    // SECONDARY SCHOOL — 1st Half / 2nd Half / Total / Grade
    // ═══════════════════════════════════════════════════════════════════════════
    private function showSecondaryReport($student, $class, $section, $session, $term, $termSettings)
    {
        $data = $this->buildSecondaryData($student, $class, $section, $session, $term, $termSettings);
        return view('parents.wards.report_card_view', $data);
    }

    private function buildSecondaryData($student, $class, $section, $session, $term, $termSettings)
    {
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

        $classStudents        = User::where('user_type', 4)->where('class_id', $class->id)->pluck('id');
        $totalStudentsInClass = $classStudents->count();

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

        $remark = StudentRemark::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->first();

        return [
            'student'              => $student,
            'class'                => $class,
            'section'              => $section,
            'currentSession'       => $session,
            'currentTerm'          => $term,
            'classTeacher'         => $this->getClassTeacher($class->id),
            'termSettings'         => $termSettings,
            'results'              => $results,
            'overallTotal'         => $overallTotal,
            'overallAverage'       => $overallAverage,
            'overallGrade'         => $overallGrade,
            'formattedPosition'    => $formattedPosition,
            'totalStudentsInClass' => $totalStudentsInClass,
            'subjectCount'         => $subjectCount,
            'affectiveRatings'     => array_merge($this->defaultRatings('affective'),   $remark?->affective_ratings   ?? []),
            'psychomotorRatings'   => array_merge($this->defaultRatings('psychomotor'), $remark?->psychomotor_ratings ?? []),
            'teacherRemark'        => $remark?->teacher_remark   ?? '',
            'principalRemark'      => $remark?->principal_remark ?? '',
            'headmasterRemark'     => '',
            'attendanceSummary'    => $this->getAttendance($student->id, $class->id, $session->id, $term->id),
            'isPrimary'            => false,
            'isNursery'            => false,
            'sheetTemplate'        => null,
            'subjects'             => collect(),
            'ratings'              => [],
            'footerData'           => null,
        ];
    }


    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function getAttendance($studentId, $classId, $sessionId, $termId)
    {
        return \App\Models\StudentAttendance::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->where('session_term', $termId)
            ->selectRaw("
                COUNT(*) as total_days,
                SUM(CASE WHEN attendance = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN attendance = 'Absent'  THEN 1 ELSE 0 END) as absent
            ")
            ->first();
    }

    private function getClassTeacher($classId)
    {
        return User::where('is_form_teacher', true)->where('form_class_id', $classId)->first();
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
            1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th',
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
        $parent     = Auth::user();
        $wards      = $parent->students()->with(['class'])->get();
        $issuedPins = IssuedPin::whereIn('student_id', $wards->pluck('id'))
            ->with(['student', 'session', 'term', 'section', 'schoolClass', 'pin'])
            ->orderByDesc('created_at')
            ->get();

        return view('parents.wards.issued_pins', compact('issuedPins', 'wards'));
    }
}