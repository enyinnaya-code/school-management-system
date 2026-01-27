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
            padding: 20px; 
            font-size: 11px;
        }
        .container { border: 2px solid #000; padding: 15px; }
        .header { display: table; width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
        .header-left { display: table-cell; width: 120px; text-align: center; vertical-align: middle; }
        .logo { width: 90px; height: 90px; object-fit: contain; }
        .header-center { display: table-cell; text-align: center; vertical-align: middle; padding: 0 10px; }
        .school-name { font-size: 18px; font-weight: bold; margin-bottom: 3px; }
        .school-motto { font-style: italic; font-size: 10px; margin-bottom: 2px; }
        .school-address { font-size: 9px; margin-bottom: 5px; }
        .report-title { font-size: 11px; font-weight: bold; margin-top: 5px; }
        .header-right { display: table-cell; width: 80px; text-align: center; vertical-align: middle; }
        .student-photo { width: 70px; height: 80px; border: 1px solid #000; background-color: #f0f0f0; }
        .class-badge { display: inline-block; background-color: #f0f0f0; padding: 3px 10px; border-radius: 3px; font-weight: bold; margin-top: 5px; }

        .student-name-section { text-align: center; padding: 8px 0; border-bottom: 2px solid #000; }
        .student-name { font-size: 16px; font-weight: bold; margin-bottom: 3px; }
        .student-basic-info { font-size: 10px; }

        .term-info-section { display: table; width: 100%; margin: 10px 0; border-bottom: 1px solid #000; padding-bottom: 8px; }
        .term-info-left, .term-info-center, .term-info-right { display: table-cell; width: 33.33%; vertical-align: top; font-size: 10px; }
        .info-item { margin-bottom: 3px; }
        .info-label { font-weight: bold; }

        .main-content { display: table; width: 100%; margin-top: 10px; }
        .left-section { display: table-cell; width: 65%; vertical-align: top; padding-right: 10px; }
        .right-section { display: table-cell; width: 35%; vertical-align: top; border-left: 1px solid #000; padding-left: 10px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px 3px; text-align: center; font-size: 9px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .subject-name { text-align: left; padding-left: 5px; }
        .rotate-text { 
            writing-mode: vertical-lr; 
            text-orientation: mixed;
            font-size: 8px; 
            padding: 3px 2px; 
            white-space: nowrap;
        }
        .no-score-row { background-color: #fff9e62b; }

        .summary-table td { padding: 5px; }
        .skills-section { margin-bottom: 10px; }
        .section-title { font-weight: bold; font-size: 10px; background-color: #f0f0f0; padding: 4px; border: 1px solid #000; text-align: center; margin-bottom: 3px; }
        .skills-table td { padding: 3px 5px; font-size: 9px; }
        .skill-name { text-align: left; border-right: 1px solid #000; }
        .skill-rating { text-align: center; width: 30px; font-weight: bold; }

        .remarks-section { margin-top: 10px; border-top: 1px solid #000; padding-top: 10px; }
        .remark-item { margin-bottom: 8px; font-size: 10px; }
        .remark-label { font-weight: bold; }

        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #000; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            {{-- <div class="header-left">
                @php
                    $settings = school_settings();
                    $logoPath = $settings && $settings->logo 
                        ? public_path('storage/logos/' . $settings->logo)
                        : public_path('images/school_management_logo__1_-removebg-preview.png');
                @endphp
                <img src="{{ $logoPath }}" alt="School Logo" class="logo">
            </div> --}}
            <div class="header-center">
                <div class="school-name">{{ strtoupper(school_name()) }}</div>
                {{-- <div class="school-motto">Motto: Excellence Personified</div> --}}
                <div class="school-address">
                    {{ $settings->address ?? 'School Address' }}<br>
                    
                </div>
                <div class="report-title">«« STUDENT'S ACADEMIC REPORT CARD PREVIEW»»</div>
            </div>
            <div class="header-right">
                <div class="class-badge">{{ $class->name }}</div>
            </div>
        </div>

        <!-- Student Name -->
        <div class="student-name-section">
            <div class="student-name">{{ strtoupper($student->name) }}</div>
            <div class="student-basic-info">
                <strong>Gender:</strong> {{ ucfirst($student->gender) }} | 
                <strong>Admission No:</strong> {{ $student->admission_no }}
            </div>
        </div>

        <!-- Term & Class Info -->
        <div class="term-info-section">
            <div class="term-info-left">
                <div class="info-item"><span class="info-label">Term:</span> {{ $currentTerm->name }}</div>
                <div class="info-item"><span class="info-label">Session:</span> {{ $currentSession->name }}</div>
            </div>
            <div class="term-info-center">
                <div class="info-item"><span class="info-label">Class:</span> {{ $class->name }}</div>
                <div class="info-item"><span class="info-label">Class Teacher:</span> {{ $classTeacher?->name ?? 'Not Assigned' }}</div>
            </div>
            <div class="term-info-right">
                <div class="info-item"><span class="info-label">No. in Class:</span> {{ $totalStudentsInClass }}</div>
                <div class="info-item"><span class="info-label">Position:</span> <strong>{{ $formattedPosition }}</strong></div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Left: Academic Results -->
            <div class="left-section">
                <table>
                    <thead>
                        <tr>
                            <th style="width:25%;">SUBJECTS</th>
                            <th class="rotate-text">1st CA (10)</th>
                            <th class="rotate-text">2nd CA (10)</th>
                            <th class="rotate-text">Mid Term (10)</th>
                            <th class="rotate-text">Exam (70)</th>
                            <th class="rotate-text">TOTAL</th>
                            <th class="rotate-text">GRADE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                        <tr class="{{ $result['total'] == 0 ? 'no-score-row' : '' }}">
                            <td class="subject-name">{{ $result['course_name'] }}</td>
                            <td>{{ $result['first_ca'] > 0 ? $result['first_ca'] : '-' }}</td>
                            <td>{{ $result['second_ca'] > 0 ? $result['second_ca'] : '-' }}</td>
                            <td>{{ $result['mid_term_test'] > 0 ? $result['mid_term_test'] : '-' }}</td>
                            <td>{{ $result['examination'] > 0 ? $result['examination'] : '-' }}</td>
                            <td><strong>{{ $result['total'] > 0 ? $result['total'] : '-' }}</strong></td>
                            <td><strong>{{ $result['grade'] }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center;">No subjects offered in this class.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Summary -->
                <table class="summary-table">
                    <tr style="background-color:#f0f0f0;font-weight:bold;">
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

                {{-- <!-- Remarks -->
                <div class="remarks-section">
                    <div class="remark-item">
                        <span class="remark-label">CLASS TEACHER'S REMARK:</span><br>
                        {{ $teacherRemark ?: '_________________________________________________' }}
                    </div>
                    <div class="remark-item">
                        <span class="remark-label">PRINCIPAL'S REMARK:</span><br>
                        {{ $principalRemark ?: '_________________________________________________' }}
                    </div>
                </div> --}}
            </div>

            <!-- Right: Skills -->
            <div class="right-section">
                <!-- Affective Skills -->
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
                    </table>
                </div>

                <!-- Psychomotor Skills -->
                <div class="skills-section">
                    <div class="section-title">PSYCHOMOTOR SKILLS</div>
                    <table class="skills-table">
                        <tr><td class="skill-name">Handwriting</td><td class="skill-rating">{{ $psychomotorRatings['handwriting'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Verbal Fluency</td><td class="skill-rating">{{ $psychomotorRatings['verbal_fluency'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Sports</td><td class="skill-rating">{{ $psychomotorRatings['sports'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Handling Tools</td><td class="skill-rating">{{ $psychomotorRatings['handling_tools'] ?? '-' }}</td></tr>
                        <tr><td class="skill-name">Drawing & Painting</td><td class="skill-rating">{{ $psychomotorRatings['drawing_painting'] ?? '-' }}</td></tr>
                    </table>
                </div>

                <!-- Rating Key -->
                <div style="margin-top: 10px; font-size: 8px; border: 1px solid #000; padding: 5px;">
                    <strong>RATING KEY:</strong><br>
                    5 - Excellent | 4 - Very Good<br>
                    3 - Good | 2 - Fair | 1 - Poor
                </div>

                <!-- Signature -->
                {{-- <div style="margin-top: 15px; text-align: center; border: 1px solid #000; padding: 10px;">
                    <div style="font-weight: bold; font-size: 9px;">PRINCIPAL'S SIGNATURE & STAMP</div>
                    <div style="height: 40px; margin-top: 10px;"></div>
                </div> --}}
            </div>
        </div>

        <!-- WATERMARK FOR TEACHERS/ADMINS ONLY -->
        @if(isset($showWatermark) && $showWatermark)
        <div style="
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: rgba(255, 0, 0, 0.15);
            pointer-events: none;
            z-index: 999;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 10px;
        ">
            PREVIEW ONLY
        </div>

        <div style="
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 18px;
            color: red;
            font-weight: bold;
            z-index: 999;
        ">
            This is a preview copy – not for official use or distribution
        </div>
        @endif

        {{-- <div class="footer">
            <strong>Next Term Begins:</strong> ____________________ | 
            <strong>Next Term Fees Payable By:</strong> ____________________
        </div> --}}
    </div>
</body>
</html>