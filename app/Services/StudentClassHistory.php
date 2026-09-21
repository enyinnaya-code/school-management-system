<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Answers "which class was this student in during session X?".
 *
 * Results, remarks etc. are stored per session/term but users.class_id only
 * holds the *current* class, so after a promotion an old report card would be
 * built against the new class. Every completed (non-rolled-back) promotion
 * records the class each student was in for the session it closed, so that is
 * the source of truth; when no promotion has touched a session the student is
 * still in the class they were in, so users.class_id is correct.
 */
class StudentClassHistory
{
    public function classIdFor(int $studentId, int $sessionId): ?int
    {
        $classId = $this->promotionRecords($sessionId)
            ->where('pr.student_id', $studentId)
            ->value('pr.original_class_id');

        return $classId !== null
            ? (int) $classId
            : User::where('id', $studentId)->value('class_id');
    }

    /**
     * Ids of the students who were in $classId during $sessionId.
     */
    public function studentIdsInClass(int $classId, int $sessionId): Collection
    {
        $classByStudent = $this->promotionRecords($sessionId)
            ->get(['pr.student_id', 'pr.original_class_id'])
            ->unique('student_id')
            ->pluck('original_class_id', 'student_id');

        $recorded = $classByStudent
            ->filter(fn($recordedClassId) => (int) $recordedClassId === $classId)
            ->keys();

        $unrecorded = User::where('user_type', 4)
            ->where('class_id', $classId)
            ->whereNotIn('id', $classByStudent->keys())
            ->pluck('id');

        return $recorded->merge($unrecorded)->unique()->values();
    }

    /**
     * Records of completed promotions for a session, earliest promotion first
     * so a student's class at the start of the session wins if they were
     * promoted more than once.
     */
    private function promotionRecords(int $sessionId)
    {
        return DB::table('promotion_records as pr')
            ->join('promotions as p', 'p.id', '=', 'pr.promotion_id')
            ->where('p.session_id', $sessionId)
            ->where('p.status', 'completed')
            ->where('pr.is_rolled_back', false)
            ->orderBy('p.id');
    }
}
