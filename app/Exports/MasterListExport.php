<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Course;
use App\Models\Result;
use App\Models\SchoolClass;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterListExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $classId;
    protected $sessionId;
    protected $termId;
    protected $subjects;
    protected $results;

    public function __construct($classId, $sessionId, $termId)
    {
        $this->classId = $classId;
        $this->sessionId = $sessionId;
        $this->termId = $termId;

        // Get all subjects offered by this class
        $class = SchoolClass::findOrFail($classId);
        $this->subjects = Course::whereHas('schoolClasses', function ($query) use ($class) {
            $query->where('school_classes.id', $class->id);
        })->orderBy('course_name')->get();

        // Get all results for this class, session, and term
        $students = User::where('user_type', 4)
            ->where('class_id', $classId)
            ->pluck('id');

        $this->results = Result::where('session_id', $sessionId)
            ->where('term_id', $termId)
            ->whereIn('student_id', $students)
            ->whereIn('course_id', $this->subjects->pluck('id'))
            ->get()
            ->groupBy('student_id');
    }

    public function collection()
    {
        $students = User::where('user_type', 4)
            ->where('class_id', $this->classId)
            ->orderBy('name')
            ->get();

        // Calculate totals and positions
        $studentSummaries = $students->map(function ($student) {
            $studentResults = $this->results->get($student->id, collect());

            $totalScore = 0;
            $subjectsWithScores = 0;

            foreach ($this->subjects as $subject) {
                $result = $studentResults->firstWhere('course_id', $subject->id);
                if ($result && $result->total > 0) {
                    $totalScore += $result->total;
                    $subjectsWithScores++;
                }
            }

            $average = $this->subjects->count() > 0 
                ? round($totalScore / $this->subjects->count(), 2) 
                : 0;
            
            $grade = $this->calculateGrade($average);

            return [
                'student' => $student,
                'total_score' => $totalScore,
                'average' => $average,
                'grade' => $grade,
                'student_results' => $studentResults,
            ];
        });

        // Sort by total score and assign positions
        $sortedStudents = $studentSummaries->sortByDesc('total_score')->values();
        
        return $sortedStudents->map(function ($item, $index) {
            $item['position'] = $index + 1;
            return $item;
        });
    }

    public function headings(): array
    {
        $headings = ['Position', 'Admission No', 'Student Name'];
        
        // Add subject headings
        foreach ($this->subjects as $subject) {
            $headings[] = $subject->course_name;
        }
        
        $headings[] = 'Total';
        $headings[] = 'Average';
        $headings[] = 'Grade';
        
        return $headings;
    }

    public function map($row): array
    {
        $data = [
            $row['position'],
            $row['student']->admission_no,
            strtoupper($row['student']->name),
        ];

        // Add scores for each subject
        foreach ($this->subjects as $subject) {
            $result = $row['student_results']->firstWhere('course_id', $subject->id);
            $total = $result?->total ?? 0;
            $data[] = $total > 0 ? $total : '-';
        }

        // Add totals
        $data[] = $row['total_score'];
        $data[] = $row['average'];
        $data[] = $row['grade'];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (header)
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Master List';
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
}