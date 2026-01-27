<?php

namespace App\Exports;

use App\Models\Timetable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TimetableExport implements FromArray, WithHeadings, WithStyles
{
    protected $timetable;
    protected $classes;
    protected $subjects;

    public function __construct(Timetable $timetable, $classes, $subjects)
    {
        $this->timetable = $timetable;
        $this->classes = $classes;
        $this->subjects = $subjects;
    }

    public function array(): array
    {
        $data = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        // Fallback to a default number of periods if day_periods is null or not an array
        $maxPeriods = is_array($this->timetable->day_periods) ? max($this->timetable->day_periods) : ($this->timetable->num_periods ?? 6);
        $breakPeriod = $this->timetable->break_period ?? 0; // Fallback to 0 if null

        foreach ($days as $day) {
            foreach ($this->classes as $class) {
                $row = [
                    'Day' => $day,
                    'Class' => $class->name,
                ];

                $periodsForDay = is_array($this->timetable->day_periods) && isset($this->timetable->day_periods[$day]) ? $this->timetable->day_periods[$day] : ($this->timetable->num_periods ?? 6);
                $periodCounter = 0;

                for ($p = 1; $p <= $maxPeriods + 1; $p++) {
                    if ($p == $breakPeriod) {
                        $row['Break'] = (isset($this->timetable->schedule[$day]['break'][$class->id]) && $this->timetable->schedule[$day]['break'][$class->id]) ? 'Break' : '';
                    } else {
                        $periodCounter++;
                        if ($periodCounter <= $periodsForDay) {
                            $courseId = $this->timetable->schedule[$day][$periodCounter][$class->id] ?? null;
                            $row['Period ' . $periodCounter] = $courseId == 'free' ? 'Free Period' : ($courseId && isset($this->subjects[$courseId]) ? ($this->subjects[$courseId]->course_name ?? 'Unknown') : '');
                        } else {
                            $row['Period ' . $periodCounter] = '';
                        }
                    }
                }

                $data[] = $row;
            }
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = ['Day', 'Class'];
        
        // Fallback to a default number of periods if day_periods is null or not an array
        $maxPeriods = is_array($this->timetable->day_periods) ? max($this->timetable->day_periods) : ($this->timetable->num_periods ?? 6);
        $startTime = 8 * 60; // 8:00 AM
        $currentTime = $startTime;
        $periodCounter = 0;

        for ($p = 1; $p <= $maxPeriods + 1; $p++) {
            if ($p == $this->timetable->break_period) {
                $headings[] = 'Break (' . date('h:i A', mktime(0, $currentTime)) . '-' . date('h:i A', mktime(0, $currentTime + ($this->timetable->break_duration ?? 30))) . ')';
                $currentTime += ($this->timetable->break_duration ?? 30);
            } else {
                if ($periodCounter < $maxPeriods) {
                    $periodCounter++;
                    $headings[] = 'Period ' . $periodCounter . ' (' . date('h:i A', mktime(0, $currentTime)) . '-' . date('h:i A', mktime(0, $currentTime + ($this->timetable->lesson_duration ?? 45))) . ')';
                    $currentTime += ($this->timetable->lesson_duration ?? 45);
                }
            }
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFCCCCCC']]],
            'A' => ['font' => ['bold' => true]],
            'B' => ['font' => ['bold' => true]],
        ];
    }
}