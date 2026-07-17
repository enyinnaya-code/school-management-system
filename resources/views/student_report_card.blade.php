<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - {{ strtoupper($student->name) }}</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #000; margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; }
        .no-border td, .no-border th { border: none; }
    </style>
</head>
<body>

    @php
        $settings = school_settings();
        $logoPath = $settings && $settings->logo
            ? public_path('storage/logos/' . $settings->logo)
            : public_path('images/school_management_logo__1_-removebg-preview.png');
    @endphp

    <div style="border: 2px solid #000; padding: 20px;">

        {{-- ── Header ───────────────────────────────────────────── --}}
        <table class="no-border" style="margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 10px;">
            <tr>
                <td style="width: 120px; text-align: center;">
                    <img src="{{ $logoPath }}" alt="School Logo" style="width: 90px; height: 90px;">
                </td>
                <td style="text-align: center;">
                    <div style="font-size: 18px; font-weight: bold;">{{ strtoupper(school_name()) }}</div>
                    <div style="font-size: 13px;">{{ $settings->address ?? 'School Address' }}</div>
                    <div style="font-size: 14px; font-weight: bold; margin-top: 5px;">
                        STUDENT'S ACADEMIC REPORT CARD
                    </div>
                </td>
                <td style="width: 80px; text-align: center;">
                    <div style="background-color: #f0f0f0; padding: 3px 10px; border-radius: 3px; font-weight: bold;">
                        {{ $class->name }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- ── Student Info ─────────────────────────────────────── --}}
        <div style="text-align: center; padding: 8px 0; border-bottom: 2px solid #000;">
            <div style="font-size: 18px; font-weight: bold;">{{ strtoupper($student->name) }}</div>
            <div style="font-size: 12px;">
                <strong>Gender:</strong> {{ ucfirst($student->gender) }} |
                <strong>Admission No:</strong> {{ $student->admission_no }}
            </div>
        </div>

        {{-- ── Term Info ─────────────────────────────────────────── --}}
        <table class="no-border" style="margin: 10px 0; border-bottom: 1px solid #000; padding-bottom: 8px; font-size: 12px;">
            <tr>
                <td style="width: 25%; vertical-align: top;">
                    <div><strong>Term:</strong> {{ $currentTerm->name }}</div>
                    <div><strong>Session:</strong> {{ $currentSession->name }}</div>
                </td>
                <td style="width: 25%; vertical-align: top;">
                    <div><strong>Class:</strong> {{ $class->name }}</div>
                    <div><strong>Class Teacher:</strong> {{ $classTeacher?->name ?? 'Not Assigned' }}</div>
                </td>
                <td style="width: 25%; vertical-align: top;">
                    <div><strong>No. in Class:</strong> {{ $totalStudentsInClass }}</div>
                    <div><strong>Position:</strong> <strong>{{ $formattedPosition }}</strong></div>
                </td>
                <td style="width: 25%; vertical-align: top; text-align: right;">
                    <div>
                        <strong>Times Present:</strong>
                        {{ $attendanceSummary->present ?? '-' }} / {{ $attendanceSummary->total_days ?? '-' }}
                    </div>
                    <div><strong>Times Absent:</strong> {{ $attendanceSummary->absent ?? '-' }}</div>
                </td>
            </tr>
        </table>

        {{-- ── Main Content: results (left) + skills (right) ───────
             Real <table> two-column layout — dompdf renders this
             far more reliably than display:table-cell divs. ──────── --}}
        <table class="no-border" style="margin-top: 10px;">
            <tr>
                <td style="width: 65%; vertical-align: top; padding-right: 12px;">

                    {{-- Results table --}}
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f0f0f0;">
                                <th rowspan="2" style="border: 1px solid #000; padding: 5px; text-align:left; width:22%; vertical-align:middle;">SUBJECTS</th>
                                <th colspan="2" style="border: 1px solid #000; padding: 4px; font-size:10px; text-align:center;">1st Half (Max 30)</th>
                                <th colspan="2" style="border: 1px solid #000; padding: 4px; font-size:10px; text-align:center;">2nd Half (Max 70)</th>
                                <th colspan="2" style="border: 1px solid #000; padding: 4px; font-size:10px; text-align:center;">Total (Max 100)</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 4px; font-size:10px; text-align:center; vertical-align:middle;">Grade</th>
                            </tr>
                            <tr style="background-color: #f8f8f8;">
                                <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obt.</th>
                                <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obt.</th>
                                <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obt.</th>
                                <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obt.</th>
                                <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obt.</th>
                                <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obt.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $result)
                            <tr>
                                <td style="border: 1px solid #000; padding: 5px; text-align: left;">{{ $result['course_name'] }}</td>
                                <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $result['first_half_obtainable'] }}</td>
                                <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $result['first_half_obtained'] > 0 ? $result['first_half_obtained'] : '-' }}</td>
                                <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $result['second_half_obtainable'] }}</td>
                                <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $result['second_half_obtained'] > 0 ? $result['second_half_obtained'] : '-' }}</td>
                                <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $result['final_obtainable'] }}</td>
                                <td style="border: 1px solid #000; padding: 4px; text-align: center;"><strong>{{ $result['final_obtained'] > 0 ? $result['final_obtained'] : '-' }}</strong></td>
                                <td style="border: 1px solid #000; padding: 4px; text-align: center;"><strong>{{ $result['grade'] ?: '-' }}</strong></td>
                            </tr>
                            @empty
                            <tr><td colspan="8" style="text-align:center; padding: 10px;">No subjects recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Summary --}}
                    <table style="margin-top: 10px;">
                        <tr style="background-color:#f0f0f0;">
                            <td style="padding: 5px; border: 1px solid #000;"><strong>NO. OF SUBJECTS:</strong></td>
                            <td style="padding: 5px; border: 1px solid #000;">{{ $subjectCount }}</td>
                            <td style="padding: 5px; border: 1px solid #000;"><strong>TOTAL OBTAINABLE:</strong></td>
                            <td style="padding: 5px; border: 1px solid #000;">{{ $subjectCount * 100 }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px; border: 1px solid #000;"><strong>TOTAL SCORE:</strong></td>
                            <td style="padding: 5px; border: 1px solid #000;"><strong>{{ $overallTotal }}</strong></td>
                            <td style="padding: 5px; border: 1px solid #000;"><strong>AVERAGE:</strong></td>
                            <td style="padding: 5px; border: 1px solid #000;"><strong>{{ $overallAverage }}</strong></td>
                        </tr>
                        <tr style="background-color:#f0f0f0;">
                            <td style="padding: 5px; border: 1px solid #000;"><strong>GRADE:</strong></td>
                            <td style="padding: 5px; border: 1px solid #000;"><strong>{{ $overallGrade }}</strong></td>
                            <td style="padding: 5px; border: 1px solid #000;"><strong>POSITION:</strong></td>
                            <td style="padding: 5px; border: 1px solid #000;"><strong>{{ $formattedPosition }} / {{ $totalStudentsInClass }}</strong></td>
                        </tr>
                    </table>

                    {{-- Remarks --}}
                    <div style="margin-top: 15px; border-top: 1px solid #000; padding-top: 10px;">
                        <div style="margin-bottom: 10px; font-size: 12px;">
                            <strong>CLASS TEACHER'S REMARK:</strong><br>
                            {{ $teacherRemark ?: '_________________________________________________' }}
                        </div>
                        @if(isset($isPrimary) && $isPrimary)
                        <div style="font-size: 12px;">
                            <strong>HEAD MASTER/MISTRESS REMARK:</strong><br>
                            {{ $headmasterRemark ?: '_________________________________________________' }}
                        </div>
                        @else
                        <div style="font-size: 12px;">
                            <strong>PRINCIPAL'S REMARK:</strong><br>
                            {{ $principalRemark ?: '_________________________________________________' }}
                        </div>
                        @endif
                    </div>

                    {{-- Signature --}}
                    <div style="margin-top: 15px; text-align: center; border: 1px solid #000; padding: 10px;">
                        <div style="font-weight: bold; font-size: 13px;">
                            @if(isset($isPrimary) && $isPrimary)
                                HEAD MASTER/MISTRESS SIGNATURE & STAMP
                            @else
                                PRINCIPAL'S SIGNATURE & STAMP
                            @endif
                        </div>
                        <div style="height: 40px; margin-top: 10px; border-bottom: 1px solid #000;"></div>
                    </div>

                </td>

                <td style="width: 35%; vertical-align: top; border-left: 1px solid #000; padding-left: 10px;">

                    {{-- Affective Skills --}}
                    <div style="font-weight: bold; background-color: #f0f0f0; padding: 4px; border: 1px solid #000; text-align: center;">
                        AFFECTIVE SKILLS
                    </div>
                    <table style="font-size: 12px; margin-bottom: 15px;">
                        @php
                            $affectiveLabels = [
                                'punctuality' => 'Punctuality', 'politeness' => 'Politeness', 'neatness' => 'Neatness',
                                'honesty' => 'Honesty', 'leadership_skill' => 'Leadership Skill', 'cooperation' => 'Cooperation',
                                'attentiveness' => 'Attentiveness', 'perseverance' => 'Perseverance', 'attitude_to_work' => 'Attitude to Work',
                                'helping_other' => 'Helping Others', 'emotional_stability' => 'Emotional Stability',
                                'health' => 'Health', 'speaking_handwriting' => 'Speaking/Handwriting',
                            ];
                        @endphp
                        @foreach($affectiveLabels as $key => $label)
                        <tr>
                            <td style="padding: 3px; border-right: 1px solid #000; border-bottom: 1px solid #eee;">{{ $label }}</td>
                            <td style="padding: 3px; text-align: center; width:30px; border-bottom: 1px solid #eee;">{{ $affectiveRatings[$key] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </table>

                    {{-- Psychomotor Skills --}}
                    <div style="font-weight: bold; background-color: #f0f0f0; padding: 4px; border: 1px solid #000; text-align: center;">
                        PSYCHOMOTOR SKILLS
                    </div>
                    <table style="font-size: 12px;">
                        @php
                            $psychoLabels = [
                                'handwriting' => 'Handwriting', 'verbal_fluency' => 'Verbal Fluency', 'sports' => 'Sports',
                                'handling_tools' => 'Handling Tools', 'drawing_painting' => 'Drawing & Painting',
                                'games' => 'Games', 'musical_skills' => 'Musical Skills',
                            ];
                        @endphp
                        @foreach($psychoLabels as $key => $label)
                        <tr>
                            <td style="padding: 3px; border-right: 1px solid #000; border-bottom: 1px solid #eee;">{{ $label }}</td>
                            <td style="padding: 3px; text-align: center; width:30px; border-bottom: 1px solid #eee;">{{ $psychomotorRatings[$key] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </table>

                    {{-- Rating Key --}}
                    <div style="margin-top: 10px; font-size: 12px; border: 1px solid #000; padding: 5px;">
                        <strong>RATING KEY:</strong><br>
                        5 - Excellent | 4 - Very Good<br>
                        3 - Good | 2 - Fair | 1 - Poor
                    </div>

                </td>
            </tr>
        </table>

        {{-- ── Footer: Resumption Date & Fees Payable By ────────── --}}
        <table class="no-border" style="margin-top: 20px; padding-top: 10px; border-top: 2px solid #000; font-size: 13px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <strong>Next Term Resumption Date:</strong><br>
                    <span style="font-weight: bold;">
                        @if($termSettings?->resumption_date)
                            {{ \Carbon\Carbon::parse($termSettings->resumption_date)->format('l, d F Y') }}
                        @else
                            ____________________________
                        @endif
                    </span>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <strong>Next Term Fees Payable By:</strong><br>
                    <span style="font-weight: bold;">
                        @if($termSettings?->fees_payable_by)
                            {{ \Carbon\Carbon::parse($termSettings->fees_payable_by)->format('l, d F Y') }}
                        @else
                            ____________________________
                        @endif
                    </span>
                </td>
            </tr>
        </table>

        @if($termSettings?->notes)
        <div style="margin-top: 8px; font-size: 12px; font-style: italic; border-top: 1px dashed #999; padding-top: 6px;">
            <strong>Note:</strong> {{ $termSettings->notes }}
        </div>
        @endif

    </div>{{-- /outer border box --}}

    {{-- ══════════════════════════════════════════════════════════
         PAGE 2 — CUMULATIVE RESULT (Third Term only)
         ══════════════════════════════════════════════════════════ --}}
    @if($showCumulative ?? false)
    <div style="page-break-before: always;"></div>
    <div style="border: 2px solid #000; padding: 20px;">

        <table class="no-border" style="margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 10px;">
            <tr>
                <td style="width: 120px; text-align: center;">
                    <img src="{{ $logoPath }}" alt="School Logo" style="width: 90px; height: 90px;">
                </td>
                <td style="text-align: center;">
                    <div style="font-size: 18px; font-weight: bold;">{{ strtoupper(school_name()) }}</div>
                    <div style="font-size: 13px;">{{ $settings->address ?? 'School Address' }}</div>
                    <div style="font-size: 14px; font-weight: bold; margin-top: 5px;">
                        CUMULATIVE ACADEMIC REPORT — {{ $currentSession->name }}
                    </div>
                </td>
                <td style="width: 80px; text-align: center;">
                    <div style="background-color: #f0f0f0; padding: 3px 10px; border-radius: 3px; font-weight: bold;">
                        {{ $class->name }}
                    </div>
                </td>
            </tr>
        </table>

        <div style="text-align: center; padding: 8px 0; border-bottom: 2px solid #000; margin-bottom: 10px;">
            <div style="font-size: 18px; font-weight: bold;">{{ strtoupper($student->name) }}</div>
            <div style="font-size: 12px;">
                <strong>Admission No:</strong> {{ $student->admission_no }} |
                <strong>Class:</strong> {{ $class->name }} |
                <strong>No. in Class:</strong> {{ $totalStudentsInClass }}
            </div>
        </div>

        <table>
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="border:1px solid #000; padding:6px; text-align:left;">SUBJECTS</th>
                    @foreach($cumulative['term_names'] as $termName)
                    <th style="border:1px solid #000; padding:5px; font-size:11px;">{{ $termName }}</th>
                    @endforeach
                    <th style="border:1px solid #000; padding:5px; font-size:11px;">Total</th>
                    <th style="border:1px solid #000; padding:5px; font-size:11px;">Average</th>
                    <th style="border:1px solid #000; padding:5px; font-size:11px;">Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cumulative['subjects'] as $row)
                <tr>
                    <td style="border:1px solid #000; padding:6px; text-align:left;">{{ $row['course_name'] }}</td>
                    @foreach($cumulative['term_names'] as $termName)
                    <td style="border:1px solid #000; padding:5px; text-align:center;">{{ $row['term_scores'][$termName] ?? '-' }}</td>
                    @endforeach
                    <td style="border:1px solid #000; padding:5px; text-align:center;"><strong>{{ $row['subject_total'] }}</strong></td>
                    <td style="border:1px solid #000; padding:5px; text-align:center;">{{ $row['subject_average'] }}</td>
                    <td style="border:1px solid #000; padding:5px; text-align:center;"><strong>{{ $row['subject_grade'] }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="margin-top: 12px;">
            <tr style="background-color:#f0f0f0;">
                <td style="padding: 6px; border: 1px solid #000;"><strong>CUMULATIVE TOTAL:</strong></td>
                <td style="padding: 6px; border: 1px solid #000;">{{ $cumulative['overall_total'] }}</td>
                <td style="padding: 6px; border: 1px solid #000;"><strong>CUMULATIVE AVERAGE:</strong></td>
                <td style="padding: 6px; border: 1px solid #000;">{{ $cumulative['overall_average'] }}</td>
            </tr>
            <tr>
                <td style="padding: 6px; border: 1px solid #000;"><strong>CUMULATIVE GRADE:</strong></td>
                <td style="padding: 6px; border: 1px solid #000;"><strong>{{ $cumulative['overall_grade'] }}</strong></td>
                <td style="padding: 6px; border: 1px solid #000;"><strong>CUMULATIVE POSITION:</strong></td>
                <td style="padding: 6px; border: 1px solid #000;"><strong>{{ $cumulative['formatted_position'] }} / {{ $cumulative['total_students'] }}</strong></td>
            </tr>
        </table>

        <div style="margin-top: 20px; text-align: center; font-size: 11px; color: #555;">
            This cumulative result combines {{ implode(', ', $cumulative['term_names']) }} for {{ $currentSession->name }}.
        </div>

    </div>
    @endif

</body>
</html>