<?php

namespace App\Exports;

use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Course;
use App\Models\Result;
use App\Models\Session;
use App\Models\Term;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassResultsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $classId;
    protected $sessionId;
    protected $termId;
    protected $user;

    public function __construct($classId, $sessionId, $termId)
    {
        $this->classId = $classId;
        $this->sessionId = $sessionId;
        $this->termId = $termId;
        $this->user = Auth::user(); // Get current user
    }

    public function collection()
    {
        $students = User::where('user_type', 4)
            ->where('class_id', $this->classId)
            ->orderBy('name')
            ->get();

        $subjects = $this->getSubjects(); // Use restricted subjects

        $results = Result::where('session_id', $this->sessionId)
            ->where('term_id', $this->termId)
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('course_id', $subjects->pluck('id'))
            ->get()
            ->groupBy(['student_id', 'course_id']);

        return $students->map(function ($student) use ($subjects, $results) {
            $row = [
                'S/N' => '',
                'Name' => $student->name,
                'Adm No' => $student->admission_no,
            ];

            foreach ($subjects as $subject) {
                $result = $results[$student->id][$subject->id][0] ?? null;
                $total = $result?->total ?? 0;
                $grade = $result?->grade ?? 'F';

                $row["{$subject->course_name} CA1"] = $result?->first_ca ?? '';
                $row["{$subject->course_name} CA2"] = $result?->second_ca ?? '';
                $row["{$subject->course_name} Mid"] = $result?->mid_term_test ?? '';
                $row["{$subject->course_name} Exam"] = $result?->examination ?? '';
                $row["{$subject->course_name} Total (Grade)"] = $total ? "$total ($grade)" : '';
            }

            // Optional: Add overall average/grade if needed
            return $row;
        });
    }

    public function headings(): array
    {
        $class = SchoolClass::find($this->classId);
        $session = Session::find($this->sessionId);
        $term = Term::find($this->termId);

        $subjects = $this->getSubjects(); // Use restricted subjects

        $headings = ['S/N', 'Student Name', 'Adm No'];

        foreach ($subjects as $subject) {
            $headings[] = "{$subject->course_name} CA1";
            $headings[] = "{$subject->course_name} CA2";
            $headings[] = "{$subject->course_name} Mid";
            $headings[] = "{$subject->course_name} Exam";
            $headings[] = "{$subject->course_name} Total (Grade)";
        }

        return [
            ["Class Results: {$class->name}"],
            ["Session: {$session->name} — Term: {$term->name}"],
            [], // empty row for spacing
            $headings
        ];
    }

    public function map($row): array
    {
        return array_values($row);
    }

    // New private method to get subjects (same logic as in controller)
    private function getSubjects()
    {
        $query = Course::orderBy('course_name');

        if (!in_array($this->user->user_type, [1, 2])) { // Not admin or super admin
            $query->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('course_user')
                    ->whereColumn('course_user.course_id', 'courses.id')
                    ->where('course_user.user_id', $this->user->id)
                    ->where(function ($sub) {
                        $sub->where('class_id', $this->classId)
                            ->orWhereNull('class_id'); // School-wide assignments
                    });
            });
        }

        return $query->get();
    }
}