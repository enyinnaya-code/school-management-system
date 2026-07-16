<div class="container"
    style="border: 2px solid #000; padding: 20px; font-family: Arial, sans-serif; font-size: 14px;">

    {{-- ── Header ───────────────────────────────────────────── --}}
    <div style="display: table; width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
        <div style="display: table-cell; width: 120px; text-align: center;">
            @php
                $settings = school_settings();
                $logoPath = $settings && $settings->logo
                    ? asset('storage/logos/' . $settings->logo)
                    : asset('images/school_management_logo__1_-removebg-preview.png');

                /**
                 * ────────────────────────────────────────────────────────
                 *  AUTO-GENERATED REMARKS
                 *  Used only when $teacherRemark / $headmasterRemark /
                 *  $principalRemark are empty/null.
                 *
                 *  Grade scale:
                 *  A 70–100 Excellent | B 60–69 Very Good | C 50–59 Good
                 *  D 45–49 Pass | E 40–44 Below Average | F 0–39 Fail
                 * ────────────────────────────────────────────────────────
                 */
                $firstName = $student->name ? explode(' ', trim($student->name))[0] : 'The student';

                // Pools of remarks per grade so it doesn't feel robotic if printed for a whole class.
                $teacherRemarkPool = [
                    'A' => [
                        "{$firstName} has performed excellently this term. Keep up the outstanding work!",
                        "An excellent result. {$firstName} shows great commitment to academic work.",
                        "Outstanding performance. {$firstName} is a role model to peers.",
                    ],
                    'B' => [
                        "{$firstName} put in a very good performance this term.",
                        "Very good result. With a little more effort, {$firstName} can attain excellence.",
                        "A commendable performance. Keep pushing, {$firstName}.",
                    ],
                    'C' => [
                        "{$firstName} had a good term overall, but there is room for improvement.",
                        "A fairly good result. {$firstName} needs to put in more effort.",
                        "Good effort shown. {$firstName} should aim higher next term.",
                    ],
                    'D' => [
                        "{$firstName} managed a pass this term but must work harder.",
                        "This is a fair result. {$firstName} needs to be more serious with studies.",
                        "{$firstName} can do better with increased effort and attention in class.",
                    ],
                    'E' => [
                        "{$firstName}'s performance this term is below average and needs urgent attention.",
                        "{$firstName} must put in a lot more effort to improve academic performance.",
                        "Below average result. {$firstName} requires close monitoring and extra study time.",
                    ],
                    'F' => [
                        "{$firstName} performed poorly this term and needs serious academic support.",
                        "This is an unsatisfactory result. Urgent intervention is required for {$firstName}.",
                        "{$firstName} must dedicate significantly more time and effort to studies.",
                    ],
                ];

                $headRemarkPool = [
                    'A' => [
                        "Excellent performance. Keep it up.",
                        "A brilliant result. Well done.",
                        "Excellent! Continue in this direction.",
                    ],
                    'B' => [
                        "Very good performance. Well done.",
                        "Good result, aim for excellence next term.",
                        "A commendable result overall.",
                    ],
                    'C' => [
                        "Good result, more effort is encouraged.",
                        "Satisfactory performance. Can do better.",
                        "Fair result, more focus is needed.",
                    ],
                    'D' => [
                        "A pass. Much more effort is required.",
                        "Needs to work harder next term.",
                        "More diligence is required going forward.",
                    ],
                    'E' => [
                        "Below average. Needs close supervision.",
                        "Performance needs urgent improvement.",
                        "More effort and support are required.",
                    ],
                    'F' => [
                        "Unsatisfactory. Requires urgent attention.",
                        "Serious improvement is needed next term.",
                        "Needs immediate academic intervention.",
                    ],
                ];

                $gradeKey = isset($overallGrade) ? strtoupper(trim($overallGrade)) : null;

                // Pick deterministically per student (so re-rendering the same report gives the same remark)
                // rather than randomly on every page load.
                $seed = isset($student->id) ? (int) $student->id : 0;

                $generatedTeacherRemark = 'No remark available.';
                $generatedHeadRemark = 'No remark available.';

                if ($gradeKey && isset($teacherRemarkPool[$gradeKey])) {
                    $pool = $teacherRemarkPool[$gradeKey];
                    $generatedTeacherRemark = $pool[$seed % count($pool)];

                    $headPool = $headRemarkPool[$gradeKey];
                    $generatedHeadRemark = $headPool[$seed % count($headPool)];
                } elseif (isset($overallAverage)) {
                    // Fallback if $overallGrade isn't set but $overallAverage is — derive grade from score.
                    $avg = (float) $overallAverage;
                    $gradeKey = $avg >= 70 ? 'A'
                        : ($avg >= 60 ? 'B'
                        : ($avg >= 50 ? 'C'
                        : ($avg >= 45 ? 'D'
                        : ($avg >= 40 ? 'E' : 'F'))));

                    $pool = $teacherRemarkPool[$gradeKey];
                    $generatedTeacherRemark = $pool[$seed % count($pool)];

                    $headPool = $headRemarkPool[$gradeKey];
                    $generatedHeadRemark = $headPool[$seed % count($headPool)];
                }

                // Principal's remark reuses the same pool as headmaster/mistress (same tone/length).
                $generatedPrincipalRemark = $generatedHeadRemark;
            @endphp
            <img src="{{ $logoPath }}" alt="School Logo"
                style="width: 90px; height: 90px; object-fit: contain;">
        </div>
        <div style="display: table-cell; text-align: center; padding: 0 10px;">
            <div style="font-size: 18px; font-weight: bold;">{{ strtoupper(school_name()) }}</div>
            <div style="font-size: 13px;">{{ $settings->address ?? 'School Address' }}</div>
            <div style="font-size: 14px; font-weight: bold; margin-top: 5px;">
                STUDENT'S ACADEMIC REPORT CARD
            </div>
        </div>
        <div style="display: table-cell; width: 80px; text-align: center;">
            <div style="background-color: #f0f0f0; padding: 3px 10px; border-radius: 3px; font-weight: bold;">
                {{ $class->name }}
            </div>
        </div>
    </div>

    {{-- ── Student Info ─────────────────────────────────────── --}}
    <div style="text-align: center; padding: 8px 0; border-bottom: 2px solid #000;">
        <div style="font-size: 18px; font-weight: bold;">{{ strtoupper($student->name) }}</div>
        <div style="font-size: 12px;">
            <strong>Gender:</strong> {{ ucfirst($student->gender) }} |
            <strong>Admission No:</strong> {{ $student->admission_no }}
        </div>
    </div>

    {{-- ── Term Info (4 columns — last column is attendance) ── --}}
    <div style="display: table; width: 100%; margin: 10px 0; border-bottom: 1px solid #000; padding-bottom: 8px;">
        <div style="display: table-cell; width: 25%; font-size: 12px; vertical-align: top;">
            <div><strong>Term:</strong> {{ $currentTerm->name }}</div>
            <div><strong>Session:</strong> {{ $currentSession->name }}</div>
        </div>
        <div style="display: table-cell; width: 25%; font-size: 12px; vertical-align: top;">
            <div><strong>Class:</strong> {{ $class->name }}</div>
            <div><strong>Class Teacher:</strong> {{ $classTeacher?->name ?? 'Not Assigned' }}</div>
        </div>
        <div style="display: table-cell; width: 25%; font-size: 12px; vertical-align: top;">
            <div><strong>No. in Class:</strong> {{ $totalStudentsInClass }}</div>
            <div><strong>Position:</strong> <strong>{{ $formattedPosition }}</strong></div>
        </div>
        <div style="display: table-cell; width: 25%; font-size: 12px; vertical-align: top; text-align: right;">
            <div>
                <strong>Times Present:</strong>
                {{ $attendanceSummary->present ?? '-' }} / {{ $attendanceSummary->total_days ?? '-' }}
            </div>
            <div>
                <strong>Times Absent:</strong>
                {{ $attendanceSummary->absent ?? '-' }}
            </div>
        </div>
    </div>

    {{-- ── Main Content ─────────────────────────────────────── --}}
    <div style="display: table; width: 100%; table-layout: fixed; margin-top: 10px;">

        {{-- Left: Results --}}
        <div style="display: table-cell; width: 65%; vertical-align: top; padding-right: 12px;">

            @if(isset($isPrimary) && $isPrimary)
            {{-- ════════════════════════════════════════════════════
                 PRIMARY: 1st Half / 2nd Half / Total / Grade
                 ════════════════════════════════════════════════════ --}}
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
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtainable</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtained</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtainable</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtained</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtainable</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtained</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">
                            {{ $result['course_name'] }}
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                            {{ $result['first_half_obtainable'] }}
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                            {{ $result['first_half_obtained'] > 0 ? $result['first_half_obtained'] : '-' }}
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                            {{ $result['second_half_obtainable'] }}
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                            {{ $result['second_half_obtained'] > 0 ? $result['second_half_obtained'] : '-' }}
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                            {{ $result['final_obtainable'] }}
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                            <strong>{{ $result['final_obtained'] > 0 ? $result['final_obtained'] : '-' }}</strong>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                            <strong>{{ $result['grade'] ?: '-' }}</strong>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 10px;">No subjects recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @else
            {{-- ════════════════════════════════════════════════════
                 SECONDARY: 1st Half / 2nd Half / Total / Grade
                 ════════════════════════════════════════════════════ --}}
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
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtainable</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtained</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtainable</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtained</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtainable</th>
                        <th style="border: 1px solid #000; padding: 3px; font-size:9px; text-align:center;">Obtained</th>
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
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;"><strong>{{ $result['grade'] }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 10px;">No subjects offered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @endif

            {{-- ── Summary ─────────────────────────────────────── --}}
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <tr style="background-color:#f0f0f0;">
                    <td style="padding: 5px;"><strong>NO. OF SUBJECTS:</strong></td>
                    <td style="padding: 5px;">{{ $subjectCount }}</td>
                    <td style="padding: 5px;"><strong>TOTAL OBTAINABLE:</strong></td>
                    <td style="padding: 5px;">{{ $subjectCount * 100 }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><strong>TOTAL SCORE:</strong></td>
                    <td style="padding: 5px;"><strong>{{ $overallTotal }}</strong></td>
                    <td style="padding: 5px;"><strong>AVERAGE:</strong></td>
                    <td style="padding: 5px;"><strong>{{ $overallAverage }}</strong></td>
                </tr>
                <tr style="background-color:#f0f0f0;">
                    <td style="padding: 5px;"><strong>GRADE:</strong></td>
                    <td style="padding: 5px;"><strong>{{ $overallGrade }}</strong></td>
                    <td style="padding: 5px;"><strong>POSITION:</strong></td>
                    <td style="padding: 5px;"><strong>{{ $formattedPosition }} / {{ $totalStudentsInClass }}</strong></td>
                </tr>
            </table>

            {{-- ── Remarks ──────────────────────────────────────── --}}
            <div style="margin-top: 15px; border-top: 1px solid #000; padding-top: 10px;">
                <div style="margin-bottom: 10px; font-size: 12px;">
                    <strong>CLASS TEACHER'S REMARK:</strong><br>
                    {{ $teacherRemark ?: $generatedTeacherRemark }}
                </div>

                @if(isset($isPrimary) && $isPrimary)
                <div style="font-size: 12px;">
                    <strong>HEAD MASTER/MISTRESS REMARK:</strong><br>
                    {{ $headmasterRemark ?: $generatedHeadRemark }}
                </div>
                @else
                <div style="font-size: 12px;">
                    <strong>PRINCIPAL'S REMARK:</strong><br>
                    {{ $principalRemark ?: $generatedPrincipalRemark }}
                </div>
                @endif
            </div>

            {{-- ── Signature block ── --}}
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

        </div>{{-- /left --}}

        {{-- Right: Skills ───────────────────────────────────────── --}}
        <div style="display: table-cell; width: 35%; vertical-align: top; border-left: 1px solid #000; padding-left: 10px;">

            {{-- Affective Skills --}}
            <div style="margin-bottom: 15px;">
                <div style="font-weight: bold; background-color: #f0f0f0; padding: 4px; border: 1px solid #000; text-align: center;">
                    AFFECTIVE SKILLS
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Punctuality</td>
                        <td style="padding: 3px; text-align: center; width:30px;">{{ $affectiveRatings['punctuality'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Politeness</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['politeness'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Neatness</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['neatness'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Honesty</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['honesty'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Leadership Skill</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['leadership_skill'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Cooperation</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['cooperation'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Attentiveness</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['attentiveness'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Perseverance</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['perseverance'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Attitude to Work</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['attitude_to_work'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Helping Others</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['helping_other'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Emotional Stability</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['emotional_stability'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Health</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['health'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Speaking/Handwriting</td>
                        <td style="padding: 3px; text-align: center;">{{ $affectiveRatings['speaking_handwriting'] ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            {{-- Psychomotor Skills --}}
            <div>
                <div style="font-weight: bold; background-color: #f0f0f0; padding: 4px; border: 1px solid #000; text-align: center;">
                    PSYCHOMOTOR SKILLS
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Handwriting</td>
                        <td style="padding: 3px; text-align: center; width:30px;">{{ $psychomotorRatings['handwriting'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Verbal Fluency</td>
                        <td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['verbal_fluency'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Sports</td>
                        <td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['sports'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Handling Tools</td>
                        <td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['handling_tools'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Drawing & Painting</td>
                        <td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['drawing_painting'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Games</td>
                        <td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['games'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border-right: 1px solid #000;">Musical Skills</td>
                        <td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['musical_skills'] ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            {{-- Rating Key --}}
            <div style="margin-top: 10px; font-size: 13px; border: 1px solid #000; padding: 5px;">
                <strong>RATING KEY:</strong><br>
                5 - Excellent | 4 - Very Good<br>
                3 - Good | 2 - Fair | 1 - Poor
            </div>

        </div>{{-- /right --}}
    </div>{{-- /main-content --}}

    {{-- ── Footer: Resumption Date & Fees Payable By ────────── --}}
    <div style="margin-top: 20px; padding-top: 10px; border-top: 2px solid #000;">
        <div style="display: table; width: 100%; font-size: 13px;">
            <div style="display: table-cell; width: 50%; vertical-align: top;">
                <strong>Next Term Resumption Date:</strong><br>
                <span style="font-size: 13px; font-weight: bold;">
                    @if($termSettings?->resumption_date)
                        {{ \Carbon\Carbon::parse($termSettings->resumption_date)->format('l, d F Y') }}
                    @else
                        ____________________________
                    @endif
                </span>
            </div>
            <div style="display: table-cell; width: 50%; vertical-align: top; text-align: right;">
                <strong>Next Term Fees Payable By:</strong><br>
                <span style="font-size: 13px; font-weight: bold;">
                    @if($termSettings?->fees_payable_by)
                        {{ \Carbon\Carbon::parse($termSettings->fees_payable_by)->format('l, d F Y') }}
                    @else
                        ____________________________
                    @endif
                </span>
            </div>
        </div>

        @if($termSettings?->notes)
        <div style="margin-top: 8px; font-size: 12px; font-style: italic; border-top: 1px dashed #999; padding-top: 6px;">
            <strong>Note:</strong> {{ $termSettings->notes }}
        </div>
        @endif
    </div>

</div>{{-- /container --}}

{{-- ══════════════════════════════════════════════════════════
     PAGE 2 — CUMULATIVE RESULT (Third Term only)
     ══════════════════════════════════════════════════════════ --}}
@if($showCumulative ?? false)
<div class="cumulative-page" style="border: 2px solid #000; padding: 20px; font-family: Arial, sans-serif; font-size: 14px; margin-top: 25px;">

    {{-- Mini header repeating student/session context on the new page --}}
    <div style="display: table; width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
        <div style="display: table-cell; width: 120px; text-align: center;">
            <img src="{{ $logoPath }}" alt="School Logo"
                style="width: 90px; height: 90px; object-fit: contain;">
        </div>
        <div style="display: table-cell; text-align: center; padding: 0 10px;">
            <div style="font-size: 18px; font-weight: bold;">{{ strtoupper(school_name()) }}</div>
            <div style="font-size: 13px;">{{ $settings->address ?? 'School Address' }}</div>
            <div style="font-size: 14px; font-weight: bold; margin-top: 5px;">
                CUMULATIVE ACADEMIC REPORT — {{ $currentSession->name }}
            </div>
        </div>
        <div style="display: table-cell; width: 80px; text-align: center;">
            <div style="background-color: #f0f0f0; padding: 3px 10px; border-radius: 3px; font-weight: bold;">
                {{ $class->name }}
            </div>
        </div>
    </div>

    <div style="text-align: center; padding: 8px 0; border-bottom: 2px solid #000; margin-bottom: 10px;">
        <div style="font-size: 18px; font-weight: bold;">{{ strtoupper($student->name) }}</div>
        <div style="font-size: 12px;">
            <strong>Admission No:</strong> {{ $student->admission_no }} |
            <strong>Class:</strong> {{ $class->name }} |
            <strong>No. in Class:</strong> {{ $totalStudentsInClass }}
        </div>
    </div>

    {{-- Subject-by-subject cumulative table --}}
    <table style="width: 100%; border-collapse: collapse;">
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
                <td style="border:1px solid #000; padding:5px; text-align:center;">
                    {{ $row['term_scores'][$termName] ?? '-' }}
                </td>
                @endforeach
                <td style="border:1px solid #000; padding:5px; text-align:center;"><strong>{{ $row['subject_total'] }}</strong></td>
                <td style="border:1px solid #000; padding:5px; text-align:center;">{{ $row['subject_average'] }}</td>
                <td style="border:1px solid #000; padding:5px; text-align:center;"><strong>{{ $row['subject_grade'] }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Cumulative summary --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 12px;">
        <tr style="background-color:#f0f0f0;">
            <td style="padding: 6px;"><strong>CUMULATIVE TOTAL:</strong></td>
            <td style="padding: 6px;">{{ $cumulative['overall_total'] }}</td>
            <td style="padding: 6px;"><strong>CUMULATIVE AVERAGE:</strong></td>
            <td style="padding: 6px;">{{ $cumulative['overall_average'] }}</td>
        </tr>
        <tr>
            <td style="padding: 6px;"><strong>CUMULATIVE GRADE:</strong></td>
            <td style="padding: 6px;"><strong>{{ $cumulative['overall_grade'] }}</strong></td>
            <td style="padding: 6px;"><strong>CUMULATIVE POSITION:</strong></td>
            <td style="padding: 6px;"><strong>{{ $cumulative['formatted_position'] }} / {{ $cumulative['total_students'] }}</strong></td>
        </tr>
    </table>

    <div style="margin-top: 20px; text-align: center; font-size: 11px; color: #555;">
        This cumulative result combines {{ implode(', ', $cumulative['term_names']) }} for {{ $currentSession->name }}.
    </div>

</div>{{-- /cumulative-page --}}
@endif