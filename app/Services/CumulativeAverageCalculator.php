<?php

namespace App\Services;

use App\Models\ClassSubjectLimit;
use App\Models\Course;
use App\Models\PrimarySchoolResult;
use App\Models\Result;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for a student's cumulative average over a session.
 *
 * This mirrors the Cumulative Results report exactly (final_obtained scores,
 * the class's subject limit, terms with no results skipped) so the figure the
 * promotion screen ranks and filters on is the same one the school sees on the
 * report.
 */
class CumulativeAverageCalculator
{
    /**
     * @return array{average: float, total: float, term_averages: array<string,float>}
     */
    public function forStudent(int $studentId, int $classId, int $sessionId): array
    {
        $isPrimary = DB::table('primary_result_classes')
            ->where('school_class_id', $classId)
            ->exists();

        $subjects = Course::whereHas('schoolClasses', function ($query) use ($classId) {
            $query->where('school_classes.id', $classId);
        })->orderBy('course_name')->get();

        $resultModel = $isPrimary ? PrimarySchoolResult::class : Result::class;
        $terms = Term::where('session_id', $sessionId)->orderBy('id')->get();

        $results = $resultModel::where('session_id', $sessionId)
            ->whereIn('term_id', $terms->pluck('id'))
            ->where('student_id', $studentId)
            ->whereIn('course_id', $subjects->pluck('id'))
            ->get()
            ->groupBy(['term_id', 'course_id']);

        $cumulativeTotal = 0;
        $cumulativeDivisor = 0;
        $termAverages = [];

        foreach ($terms as $term) {
            $termResults = $results->get($term->id, collect());

            // No records for this term -> excluded from the cumulative entirely
            if ($termResults->isEmpty()) {
                continue;
            }

            if ($isPrimary) {
                $termTotal = 0;
                foreach ($subjects as $subject) {
                    $result = $termResults->get($subject->id);
                    if ($result) {
                        $termTotal += (float) ($result->first()->final_obtained ?? 0);
                    }
                }
                $divisor = $subjects->count() > 0 ? $subjects->count() : 1;
            } else {
                $mapped = $subjects->map(function ($subject) use ($termResults) {
                    $result = $termResults->get($subject->id);

                    return [
                        'course_name'    => $subject->course_name,
                        'final_obtained' => $result ? (float) ($result->first()->final_obtained ?? 0) : 0,
                    ];
                });

                $limitData = $this->applySubjectLimit(collect($mapped), $classId);
                $termTotal = $limitData['adjusted_total'];
                $divisor = $limitData['average_divisor'];
            }

            $termAverages[$term->name] = round($termTotal / $divisor, 2);
            $cumulativeTotal += $termTotal;
            $cumulativeDivisor += $divisor;
        }

        return [
            'average'       => $cumulativeDivisor > 0 ? round($cumulativeTotal / $cumulativeDivisor, 2) : 0.0,
            'total'         => $cumulativeTotal,
            'term_averages' => $termAverages,
        ];
    }

    /**
     * Apply the class's subject limit: keep the best N scored subjects and
     * divide by N (or by the number of scored subjects when no limit is set).
     */
    public function applySubjectLimit($results, int $classId): array
    {
        $limit = ClassSubjectLimit::where('school_class_id', $classId)->first();
        $scored = $results->filter(fn($r) => ($r['final_obtained'] ?? 0) > 0);

        if (!$limit) {
            return [
                'adjusted_total'  => $results->sum('final_obtained'),
                'average_divisor' => $scored->count() > 0 ? $scored->count() : 1,
                'dropped_course'  => null,
            ];
        }

        $minSubjects = (int) $limit->min_subjects;
        $droppedCourse = null;

        if ($scored->count() > $minSubjects) {
            $excessCount = $scored->count() - $minSubjects;
            $sorted = $scored->sortBy('final_obtained');
            $droppedKeys = $sorted->keys()->take($excessCount)->toArray();
            $droppedCourse = $results[$droppedKeys[0]]['course_name'] ?? null;
            $adjustedTotal = $scored->except($droppedKeys)->sum('final_obtained');
        } else {
            $adjustedTotal = $scored->sum('final_obtained');
        }

        return [
            'adjusted_total'  => $adjustedTotal,
            'average_divisor' => $minSubjects > 0 ? $minSubjects : 1,
            'dropped_course'  => $droppedCourse,
        ];
    }
}
