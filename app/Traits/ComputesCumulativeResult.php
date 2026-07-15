<?php

namespace App\Traits;

use App\Models\Course;
use App\Models\PrimarySchoolResult;
use App\Models\Result;
use App\Models\Term;
use App\Models\User;

/**
 * Computes a full-session cumulative result (First + Second + Third Term combined)
 * for a single student, subject-by-subject, plus overall totals/average/grade/position.
 *
 * Used whenever the currently viewed term is "Third Term".
 *
 * Requires the consuming class to already define:
 *   applySubjectLimit($results, $classId)
 *   calculateGrade($total)
 *   getPositionSuffix($position)
 */
trait ComputesCumulativeResult
{
    protected function computeCumulativeResult($student, $class, $session, bool $isPrimary): array
    {
        $terms = Term::where('session_id', $session->id)->orderBy('name')->get();

        $allSubjects = Course::whereHas('schoolClasses', function ($q) use ($class) {
            $q->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

        $classStudentIds = User::where('user_type', 4)
            ->where('class_id', $class->id)
            ->pluck('id');

        // ── Overall total/average: drop-lowest applied PER TERM, then combined ──
        $studentTermTotals = $this->studentTermTotalsAcrossTerms(
            $student->id,
            $class,
            $session,
            $terms,
            $allSubjects,
            $isPrimary
        );

        $cumulativeTotal   = array_sum(array_column($studentTermTotals, 'adjusted_total'));
        $cumulativeDivisor = array_sum(array_column($studentTermTotals, 'divisor'));
        $cumulativeAverage = $cumulativeDivisor > 0 ? round($cumulativeTotal / $cumulativeDivisor, 2) : 0;
        $cumulativeGrade   = $this->calculateGrade($cumulativeAverage);

        // ── Subject-by-subject: RAW score per term per subject, no drop-lowest ──
        // (drop-lowest is a whole-total rule, not per-subject — the subject table
        //  should show every subject's actual score across the three terms)
        $rawByTerm = [];
        foreach ($terms as $term) {
            $rawByTerm[$term->id] = $this->rawSubjectScores(
                $student->id,
                $term->id,
                $session->id,
                $allSubjects,
                $isPrimary
            );
        }

        $subjectRows = $allSubjects->map(function ($subject) use ($terms, $rawByTerm) {
            $termScores   = [];
            $subjectTotal = 0;

            foreach ($terms as $term) {
                $score = (float) ($rawByTerm[$term->id][$subject->id] ?? 0);
                $termScores[$term->name] = $score > 0 ? $score : null;
                $subjectTotal += $score;
            }

            $termsCount     = max($terms->count(), 1);
            $subjectAverage = round($subjectTotal / $termsCount, 2);

            return [
                'course_name'     => $subject->course_name,
                'term_scores'     => $termScores,
                'subject_total'   => $subjectTotal,
                'subject_average' => $subjectAverage,
                'subject_grade'   => $subjectTotal > 0 ? $this->calculateGrade($subjectAverage) : '-',
            ];
        });

        // ── Position: same per-term adjusted-total calc, for every classmate ──
        $rankTotals = $classStudentIds->map(function ($sid) use ($class, $session, $terms, $allSubjects, $isPrimary) {
            $totals = $this->studentTermTotalsAcrossTerms($sid, $class, $session, $terms, $allSubjects, $isPrimary);
            return [
                'student_id' => $sid,
                'total'      => array_sum(array_column($totals, 'adjusted_total')),
            ];
        })->sortByDesc('total')->values();

        $position = $rankTotals->search(fn($item) => $item['student_id'] == $student->id);
        $position = $position !== false ? $position + 1 : $classStudentIds->count();
        $formattedPosition = $position . $this->getPositionSuffix($position);

        return [
            'term_names'         => $terms->pluck('name')->toArray(),
            'subjects'           => $subjectRows,
            'overall_total'      => $cumulativeTotal,
            'overall_average'    => $cumulativeAverage,
            'overall_grade'      => $cumulativeGrade,
            'position'           => $position,
            'formatted_position' => $formattedPosition,
            'total_students'     => $classStudentIds->count(),
            'per_term_totals'    => $studentTermTotals,
        ];
    }

    /**
     * One student's adjusted total + divisor for every term in the session.
     * Secondary: drop-lowest via applySubjectLimit(). Primary: raw sum, divisor = subject count.
     *
     * @return array<string, array{adjusted_total: float, divisor: int}> keyed by term name
     */
    private function studentTermTotalsAcrossTerms($studentId, $class, $session, $terms, $allSubjects, bool $isPrimary): array
    {
        $out = [];

        foreach ($terms as $term) {
            if ($isPrimary) {
                $results = PrimarySchoolResult::where('student_id', $studentId)
                    ->where('session_id', $session->id)
                    ->where('term_id', $term->id)
                    ->whereIn('course_id', $allSubjects->pluck('id'))
                    ->get()
                    ->keyBy('course_id');

                $mapped = $allSubjects->map(fn($s) => [
                    'course_name'    => $s->course_name,
                    'final_obtained' => (float) ($results->get($s->id)->final_obtained ?? 0),
                ]);

                $out[$term->name] = [
                    'adjusted_total' => $mapped->sum('final_obtained'),
                    'divisor'        => $allSubjects->count() > 0 ? $allSubjects->count() : 1,
                ];
            } else {
                $results = Result::where('student_id', $studentId)
                    ->where('session_id', $session->id)
                    ->where('term_id', $term->id)
                    ->whereIn('course_id', $allSubjects->pluck('id'))
                    ->get()
                    ->keyBy('course_id');

                $mapped = $allSubjects->map(fn($s) => [
                    'course_name'    => $s->course_name,
                    'final_obtained' => (float) ($results->get($s->id)->final_obtained ?? 0),
                ]);

                $limitData = $this->applySubjectLimit(collect($mapped), $class->id);

                $out[$term->name] = [
                    'adjusted_total' => $limitData['adjusted_total'],
                    'divisor'        => $limitData['average_divisor'],
                ];
            }
        }

        return $out;
    }

    /**
     * Raw (non-adjusted) final_obtained per subject for one student/term.
     *
     * @return array<int, float> keyed by course_id
     */
    private function rawSubjectScores($studentId, $termId, $sessionId, $allSubjects, bool $isPrimary): array
    {
        $query = $isPrimary
            ? PrimarySchoolResult::query()
            : Result::query();

        return $query->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->whereIn('course_id', $allSubjects->pluck('id'))
            ->pluck('final_obtained', 'course_id')
            ->toArray();
    }
}