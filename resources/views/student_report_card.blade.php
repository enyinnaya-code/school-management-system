<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - {{ strtoupper($student->name) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            padding: 12px;
            font-size: 11px;
        }
        .container { border: 2px solid #000; padding: 12px; }

        /* ── Header ── */
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 8px; overflow: hidden; }
        .header-center { text-align: center; }
        .school-name { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .school-address { font-size: 9px; margin-bottom: 4px; }
        .report-title { font-size: 11px; font-weight: bold; }
        .class-badge { float: right; background-color: #f0f0f0; padding: 3px 10px; border-radius: 3px; font-weight: bold; margin-top: -30px; }

        /* ── Student name ── */
        .student-name-section { text-align: center; padding: 6px 0; border-bottom: 2px solid #000; }
        .student-name { font-size: 15px; font-weight: bold; margin-bottom: 2px; }
        .student-basic-info { font-size: 10px; }

        /* ── Term info — 4 equal float columns ── */
        .term-info-section { overflow: hidden; margin: 8px 0; border-bottom: 1px solid #000; padding-bottom: 6px; }
        .term-info-col { float: left; width: 25%; font-size: 10px; padding-right: 4px; }
        .info-item { margin-bottom: 2px; }
        .info-label { font-weight: bold; }

        /* ── Main content — left 63%, right 35% via float ── */
        .main-content { overflow: hidden; margin-top: 8px; }
        .left-section { float: left; width: 63%; padding-right: 8px; }
        .right-section { float: right; width: 35%; border-left: 1px solid #000; padding-left: 8px; }

        /* ── Tables ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #000; padding: 4px 2px; text-align: center; font-size: 9px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .subject-name { text-align: left; padding-left: 4px; }
        .rotate-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            font-size: 8px;
            padding: 3px 2px;
            white-space: nowrap;
        }
        .no-score-row { background-color: #fffbe6; }
        .summary-table td { padding: 4px 5px; }

        /* ── Skills ── */
        .skills-section { margin-bottom: 8px; }
        .section-title { font-weight: bold; font-size: 10px; background-color: #f0f0f0; padding: 3px; border: 1px solid #000; text-align: center; margin-bottom: 2px; }
        .skills-table td { padding: 2px 4px; font-size: 9px; }
        .skill-name { text-align: left; border-right: 1px solid #000; }
        .skill-rating { text-align: center; width: 28px; font-weight: bold; }

        /* ── Remarks ── */
        .remarks-section { margin-top: 8px; border-top: 1px solid #000; padding-top: 6px; }
        .remark-item { margin-bottom: 6px; font-size: 10px; }
        .remark-label { font-weight: bold; }

        /* ── Clearfix ── */
        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>
    <div class="container">

        {{-- ── Header ── --}}
        <div class="header clearfix">
            <div class="header-center">
                <div class="school-name">{{ strtoupper(school_name()) }}</div>
                <div class="school-address">{{ $settings->address ?? 'School Address' }}</div>
                <div class="report-title">«« STUDENT'S ACADEMIC REPORT CARD »»</div>
            </div>
            <div class="class-badge">{{ $class->name }}</div>
        </div>

        {{-- ── Student Name ── --}}
        <div class="student-name-section">
            <div class="student-name">{{ strtoupper($student->name) }}</div>
            <div class="student-basic-info">
                <strong>Gender:</strong> {{ ucfirst($student->gender) }} |
                <strong>Admission No:</strong> {{ $student->admission_no }}
            </div>
        </div>

        {{-- ── Term Info ── --}}
        <div class="term-info-section clearfix">
            <div class="term-info-col">
                <div class="info-item"><span class="info-label">Term:</span> {{ $currentTerm->name }}</div>
                <div class="info-item"><span class="info-label">Session:</span> {{ $currentSession->name }}</div>
            </div>
            <div class="term-info-col">
                <div class="info-item"><span class="info-label">Class:</span> {{ $class->name }}</div>
                <div class="info-item"><span class="info-label">Class Teacher:</span> {{ $classTeacher?->name ?? 'Not Assigned' }}</div>
            </div>
            <div class="term-info-col">
                <div class="info-item"><span class="info-label">No. in Class:</span> {{ $totalStudentsInClass }}</div>
                <div class="info-item"><span class="info-label">Position:</span> <strong>{{ $formattedPosition }}</strong></div>
            </div>
            <div class="term-info-col">
                <div class="info-item">
                    <span class="info-label">Times Present:</span>
                    {{ $attendanceSummary->present ?? '-' }} / {{ $attendanceSummary->total_days ?? '-' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Times Absent:</span>
                    {{ $attendanceSummary->absent ?? '-' }}
                </div>
            </div>
        </div>

        {{-- ── Main Content ── --}}
        <div class="main-content clearfix">

            {{-- Left: Results --}}
            <div class="left-section">

                @if(isset($isPrimary) && $isPrimary)
                {{-- ══════════════════════════════════════════════
                     PRIMARY SCHOOL
                     ══════════════════════════════════════════════ --}}
                <table>
                    <thead>
                        <tr>
                            <th style="width:30%; text-align:left; padding-left:4px;">SUBJECTS</th>
                            <th class="rotate-text">1st Half Obtainable</th>
                            <th class="rotate-text">1st Half Obtained</th>
                            <th class="rotate-text">2nd Half Obtainable</th>
                            <th class="rotate-text">2nd Half Obtained</th>
                            <th class="rotate-text">Total Obtainable</th>
                            <th class="rotate-text">Total Obtained</th>
                            <th class="rotate-text">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                        @php
                            $hasScore = ($result['first_half_obtained'] > 0 || $result['second_half_obtained'] > 0 || $result['final_obtained'] > 0);
                            $grade    = $result['grade'] ?? null;
                        @endphp
                        <tr class="{{ !$hasScore ? 'no-score-row' : '' }}">
                            <td class="subject-name">{{ $result['course_name'] }}</td>
                            <td>{{ $result['first_half_obtainable'] }}</td>
                            <td>{{ $result['first_half_obtained'] > 0 ? $result['first_half_obtained'] : '-' }}</td>
                            <td>{{ $result['second_half_obtainable'] }}</td>
                            <td>{{ $result['second_half_obtained'] > 0 ? $result['second_half_obtained'] : '-' }}</td>
                            <td>{{ $result['final_obtainable'] }}</td>
                            <td><strong>{{ $result['final_obtained'] > 0 ? $result['final_obtained'] : '-' }}</strong></td>
                            <td><strong>{{ $hasScore ? ($grade ?: '-') : '-' }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" style="text-align:center;">No subjects recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <table class="summary-table">
                    <tr style="background-color:#f0f0f0; font-weight:bold;">
                        <td>NO. OF SUBJECTS:</td>
                        <td>{{ $subjectCount }}</td>
                        <td>TOTAL OBTAINABLE:</td>
                        <td>{{ $subjectCount * 100 }}</td>
                    </tr>
                    <tr>
                        <td><strong>TOTAL SCORE:</strong></td>
                        <td><strong>{{ $overallTotal }}</strong></td>
                        <td><strong>AVERAGE:</strong></td>
                        <td><strong>{{ $overallAverage }}</strong></td>
                    </tr>
                    <tr style="background-color:#f0f0f0;">
                        <td><strong>GRADE:</strong></td>
                        <td><strong>{{ $overallGrade }}</strong></td>
                        <td><strong>POSITION:</strong></td>
                        <td><strong>{{ $formattedPosition }} / {{ $totalStudentsInClass }}</strong></td>
                    </tr>
                </table>

                @else
                {{-- ══════════════════════════════════════════════
                     SECONDARY SCHOOL
                     ══════════════════════════════════════════════ --}}
                <table>
                    <thead>
                        <tr>
                            <th style="width:30%; text-align:left; padding-left:4px;">SUBJECTS</th>
                            <th class="rotate-text">1st Half Obtainable</th>
                            <th class="rotate-text">1st Half Obtained</th>
                            <th class="rotate-text">2nd Half Obtainable</th>
                            <th class="rotate-text">2nd Half Obtained</th>
                            <th class="rotate-text">Total Obtainable</th>
                            <th class="rotate-text">Total Obtained</th>
                            <th class="rotate-text">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                        @php
                            $hasScore = ($result['first_half_obtained'] > 0 || $result['second_half_obtained'] > 0 || $result['final_obtained'] > 0);
                        @endphp
                        <tr class="{{ !$hasScore ? 'no-score-row' : '' }}">
                            <td class="subject-name">{{ $result['course_name'] }}</td>
                            <td>{{ $result['first_half_obtainable'] }}</td>
                            <td>{{ $result['first_half_obtained'] > 0 ? $result['first_half_obtained'] : '-' }}</td>
                            <td>{{ $result['second_half_obtainable'] }}</td>
                            <td>{{ $result['second_half_obtained'] > 0 ? $result['second_half_obtained'] : '-' }}</td>
                            <td>{{ $result['final_obtainable'] }}</td>
                            <td><strong>{{ $result['final_obtained'] > 0 ? $result['final_obtained'] : '-' }}</strong></td>
                            <td><strong>{{ $result['grade'] }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" style="text-align:center;">No subjects offered in this class.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <table class="summary-table">
                    <tr style="background-color:#f0f0f0; font-weight:bold;">
                        <td>NO. OF SUBJECTS:</td>
                        <td>{{ $subjectCount }}</td>
                        <td>TOTAL OBTAINABLE:</td>
                        <td>{{ $subjectCount * 100 }}</td>
                    </tr>
                    <tr>
                        <td><strong>TOTAL SCORE:</strong></td>
                        <td><strong>{{ $overallTotal }}</strong></td>
                        <td><strong>AVERAGE:</strong></td>
                        <td><strong>{{ $overallAverage }}</strong></td>
                    </tr>
                    <tr style="background-color:#f0f0f0;">
                        <td><strong>GRADE:</strong></td>
                        <td><strong>{{ $overallGrade }}</strong></td>
                        <td><strong>POSITION:</strong></td>
                        <td><strong>{{ $formattedPosition }} / {{ $totalStudentsInClass }}</strong></td>
                    </tr>
                </table>
                @endif

                {{-- ── Remarks ── --}}
                <div class="remarks-section">
                    <div class="remark-item">
                        <span class="remark-label">CLASS TEACHER'S REMARK:</span><br>
                        {{ $teacherRemark ?: '_________________________________________________' }}
                    </div>
                    @if(isset($isPrimary) && $isPrimary)
                    <div class="remark-item">
                        <span class="remark-label">HEAD MASTER/MISTRESS REMARK:</span><br>
                        {{ $headmasterRemark ?: '_________________________________________________' }}
                    </div>
                    @else
                    <div class="remark-item">
                        <span class="remark-label">PRINCIPAL'S REMARK:</span><br>
                        {{ $principalRemark ?: '_________________________________________________' }}
                    </div>
                    @endif
                </div>

            </div>{{-- /left-section --}}

            {{-- Right: Skills --}}
            <div class="right-section">

                <div class="skills-section">
                    <div class="section-title">AFFECTIVE SKILLS</div>
                    <table class="skills-table">
                        <tr><td class="skill-name">Punctuality</td><td class="skill-rating">{{ $affectiveRatings['punctuality'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Politeness</td><td class="skill-rating">{{ $affectiveRatings['politeness'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Neatness</td><td class="skill-rating">{{ $affectiveRatings['neatness'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Honesty</td><td class="skill-rating">{{ $affectiveRatings['honesty'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Leadership Skill</td><td class="skill-rating">{{ $affectiveRatings['leadership_skill'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Cooperation</td><td class="skill-rating">{{ $affectiveRatings['cooperation'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Attentiveness</td><td class="skill-rating">{{ $affectiveRatings['attentiveness'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Perseverance</td><td class="skill-rating">{{ $affectiveRatings['perseverance'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Attitude to Work</td><td class="skill-rating">{{ $affectiveRatings['attitude_to_work'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Helping Others</td><td class="skill-rating">{{ $affectiveRatings['helping_other'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Emotional Stability</td><td class="skill-rating">{{ $affectiveRatings['emotional_stability'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Health</td><td class="skill-rating">{{ $affectiveRatings['health'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Speaking/Handwriting</td><td class="skill-rating">{{ $affectiveRatings['speaking_handwriting'] ?? '-' }}</td></tr>
                    </table>
                </div>

                <div class="skills-section">
                    <div class="section-title">PSYCHOMOTOR SKILLS</div>
                    <table class="skills-table">
                        <tr><td class="skill-name">Handwriting</td><td class="skill-rating">{{ $psychomotorRatings['handwriting'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Verbal Fluency</td><td class="skill-rating">{{ $psychomotorRatings['verbal_fluency'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Sports</td><td class="skill-rating">{{ $psychomotorRatings['sports'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Handling Tools</td><td class="skill-rating">{{ $psychomotorRatings['handling_tools'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Drawing &amp; Painting</td><td class="skill-rating">{{ $psychomotorRatings['drawing_painting'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Games</td><td class="skill-rating">{{ $psychomotorRatings['games'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Musical Skills</td><td class="skill-rating">{{ $psychomotorRatings['musical_skills'] ?? '-' }}</td></tr>
                    </table>
                </div>

                <div style="margin-top:8px; font-size:8px; border:1px solid #000; padding:4px;">
                    <strong>RATING KEY:</strong><br>
                    5 - Excellent | 4 - Very Good<br>
                    3 - Good | 2 - Fair | 1 - Poor
                </div>

            </div>{{-- /right-section --}}

        </div>{{-- /main-content --}}

        {{-- ── Watermark ── --}}
        @if(isset($showWatermark) && $showWatermark)
        <div style="
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px; font-weight: bold;
            color: rgba(255,0,0,0.15); pointer-events: none;
            z-index: 999; white-space: nowrap;
            text-transform: uppercase; letter-spacing: 10px;
        ">PREVIEW ONLY</div>
        <div style="
            position: absolute; bottom: 30px; left: 0; right: 0;
            text-align: center; font-size: 18px; color: red;
            font-weight: bold; z-index: 999;
        ">This is a preview copy – not for official use or distribution</div>
        @endif

    </div>
</body>
</html>