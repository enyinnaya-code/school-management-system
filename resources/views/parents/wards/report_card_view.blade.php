@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <div class="main-content pt-5 mt-5">
                <section class="section">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Report Card - {{ strtoupper($student->name) }}</h4>
                                <a href="{{ route('parents.wards.reportcards.pdf') }}" target="_blank"
                                   class="btn btn-success btn-lg">
                                    <i class="fas fa-file-pdf"></i> Download as PDF
                                </a>
                            </div>

                            <div class="card-body p-4">
                                <div class="container"
                                    style="border: 2px solid #000; padding: 20px; font-family: Arial, sans-serif; font-size: 14px;">

                                    {{-- ── Header ───────────────────────────── --}}
                                    <div style="display:table; width:100%; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:10px;">
                                        <div style="display:table-cell; width:120px; text-align:center;">
                                            @php
                                                $settings = school_settings();
                                                $logoPath = $settings && $settings->logo
                                                    ? asset('storage/logos/' . $settings->logo)
                                                    : asset('images/school_management_logo__1_-removebg-preview.png');
                                            @endphp
                                            <img src="{{ $logoPath }}" alt="School Logo"
                                                 style="width:90px; height:90px; object-fit:contain;">
                                        </div>
                                        <div style="display:table-cell; text-align:center; padding:0 10px;">
                                            <div style="font-size:18px; font-weight:bold;">{{ strtoupper(school_name()) }}</div>
                                            <div style="font-size:13px;">{{ $settings->address ?? 'School Address' }}</div>
                                            <div style="font-size:14px; font-weight:bold; margin-top:5px;">
                                                STUDENT'S ACADEMIC REPORT CARD
                                            </div>
                                        </div>
                                        <div style="display:table-cell; width:80px; text-align:center;">
                                            <div style="background-color:#f0f0f0; padding:3px 10px; border-radius:3px; font-weight:bold;">
                                                {{ $class->name }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ── Student Info ─────────────────────── --}}
                                    <div style="text-align:center; padding:8px 0; border-bottom:2px solid #000;">
                                        <div style="font-size:18px; font-weight:bold;">{{ strtoupper($student->name) }}</div>
                                        <div style="font-size:12px;">
                                            <strong>Gender:</strong> {{ ucfirst($student->gender) }} |
                                            <strong>Admission No:</strong> {{ $student->admission_no }}
                                        </div>
                                    </div>

                                    {{-- ── Term Info (4 columns — last column is attendance) ── --}}
                                    <div style="display:table; width:100%; margin:10px 0; border-bottom:1px solid #000; padding-bottom:8px;">
                                        <div style="display:table-cell; width:25%; font-size:12px; vertical-align:top;">
                                            <div><strong>Term:</strong> {{ $currentTerm->name }}</div>
                                            <div><strong>Session:</strong> {{ $currentSession->name }}</div>
                                        </div>
                                        <div style="display:table-cell; width:25%; font-size:12px; vertical-align:top;">
                                            <div><strong>Class:</strong> {{ $class->name }}</div>
                                            <div><strong>Class Teacher:</strong> {{ $classTeacher?->name ?? 'Not Assigned' }}</div>
                                        </div>
                                        <div style="display:table-cell; width:25%; font-size:12px; vertical-align:top;">
                                            <div><strong>No. in Class:</strong> {{ $totalStudentsInClass }}</div>
                                            <div><strong>Position:</strong> <strong>{{ $formattedPosition }}</strong></div>
                                        </div>
                                        <div style="display:table-cell; width:25%; font-size:12px; vertical-align:top; text-align:right;">
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

                                    {{-- ════════════════════════════════════════════════════════════
                                         NURSERY / CUSTOM RESULT SHEET
                                         ════════════════════════════════════════════════════════════ --}}
                                    @if(isset($isNursery) && $isNursery && isset($sheetTemplate))

                                    <div style="margin-top:10px;">
                                        @foreach($subjects as $subject)
                                        <div style="margin-bottom:16px;">
                                            <div style="font-weight:bold; background:#f0f0f0; padding:5px; border:1px solid #000; margin-bottom:4px;">
                                                {{ $subject->name }}
                                            </div>

                                            {{-- Items directly under the subject --}}
                                            @if(count($subject->items))
                                            <table style="width:100%; border-collapse:collapse; font-size:12px; margin-bottom:6px;">
                                                <thead>
                                                    <tr style="background:#f8f8f8;">
                                                        <th style="border:1px solid #000; padding:4px; text-align:left;">Item</th>
                                                        @foreach($sheetTemplate->rating_columns as $col)
                                                            <th style="border:1px solid #000; padding:4px; text-align:center;">{{ $col }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($subject->items as $item)
                                                    <tr>
                                                        <td style="border:1px solid #000; padding:4px;">{{ $item->name }}</td>
                                                        @foreach($sheetTemplate->rating_columns as $col)
                                                            <td style="border:1px solid #000; padding:4px; text-align:center;">
                                                                {{ ($ratings[$item->id] ?? '') === $col ? '✓' : '' }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @endif

                                            {{-- Subcategories --}}
                                            @foreach($subject->subcategories as $sub)
                                            <div style="font-style:italic; font-size:12px; padding:3px 5px; background:#fafafa; border:1px solid #ddd; margin-bottom:3px;">
                                                {{ $sub->name }}
                                            </div>
                                            <table style="width:100%; border-collapse:collapse; font-size:12px; margin-bottom:6px;">
                                                <tbody>
                                                    @foreach($sub->items as $item)
                                                    <tr>
                                                        <td style="border:1px solid #000; padding:4px;">{{ $item->name }}</td>
                                                        @foreach($sheetTemplate->rating_columns as $col)
                                                            <td style="border:1px solid #000; padding:4px; text-align:center; width:40px;">
                                                                {{ ($ratings[$item->id] ?? '') === $col ? '✓' : '' }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @endforeach
                                        </div>
                                        @endforeach

                                        {{-- Nursery template footer fields (remark, reopening date etc.) --}}
                                        @if(isset($sheetTemplate->footer_fields) && count($sheetTemplate->footer_fields))
                                        <div style="margin-top:10px; border-top:1px solid #000; padding-top:8px;">
                                            @foreach($sheetTemplate->footer_fields as $fieldKey => $fieldLabel)
                                            <div style="margin-bottom:8px; font-size:12px;">
                                                <strong>{{ $fieldLabel }}:</strong>
                                                {{ $footerData?->{$fieldKey} ?? '__________________________' }}
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>

                                    @else
                                    {{-- ════════════════════════════════════════════════════════════
                                         PRIMARY & SECONDARY — main content (results + skills)
                                         ════════════════════════════════════════════════════════════ --}}
                                    <div style="display:table; width:100%; margin-top:10px;">

                                        {{-- Left: Academic Results --}}
                                        <div style="display:table-cell; width:65%; vertical-align:top; padding-right:12px;">

                                            @if(isset($isPrimary) && $isPrimary)
                                            {{-- ── PRIMARY table ─────────────────────────────── --}}
                                            <table style="width:100%; border-collapse:collapse;">
                                                <thead>
                                                    <tr style="background:#f0f0f0;">
                                                        <th rowspan="2" style="border:1px solid #000; padding:5px; text-align:left; width:22%; vertical-align:middle;">SUBJECTS</th>
                                                        <th colspan="2" style="border:1px solid #000; padding:4px; font-size:10px; text-align:center;">1st Half (Max 30)</th>
                                                        <th colspan="2" style="border:1px solid #000; padding:4px; font-size:10px; text-align:center;">2nd Half (Max 70)</th>
                                                        <th colspan="2" style="border:1px solid #000; padding:4px; font-size:10px; text-align:center;">Total (Max 100)</th>
                                                        <th rowspan="2" style="border:1px solid #000; padding:4px; font-size:10px; text-align:center; vertical-align:middle;">Remark</th>
                                                    </tr>
                                                    <tr style="background:#f8f8f8;">
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtainable</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtained</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtainable</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtained</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtainable</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtained</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($results as $result)
                                                    <tr>
                                                        <td style="border:1px solid #000; padding:5px; text-align:left;">{{ $result['course_name'] }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['first_half_obtainable'] }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['first_half_obtained'] > 0 ? $result['first_half_obtained'] : '-' }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['second_half_obtainable'] }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['second_half_obtained'] > 0 ? $result['second_half_obtained'] : '-' }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['final_obtainable'] }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;"><strong>{{ $result['final_obtained'] > 0 ? $result['final_obtained'] : '-' }}</strong></td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['teacher_remark'] ?: '-' }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr><td colspan="8" style="text-align:center; padding:10px;">No subjects recorded.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>

                                            @else
                                            {{-- ── SECONDARY table ────────────────────────────── --}}
                                            <table style="width:100%; border-collapse:collapse;">
                                                <thead>
                                                    <tr style="background:#f0f0f0;">
                                                        <th rowspan="2" style="border:1px solid #000; padding:5px; text-align:left; width:22%; vertical-align:middle;">SUBJECTS</th>
                                                        <th colspan="2" style="border:1px solid #000; padding:4px; font-size:10px; text-align:center;">1st Half (Max 30)</th>
                                                        <th colspan="2" style="border:1px solid #000; padding:4px; font-size:10px; text-align:center;">2nd Half (Max 70)</th>
                                                        <th colspan="2" style="border:1px solid #000; padding:4px; font-size:10px; text-align:center;">Total (Max 100)</th>
                                                        <th rowspan="2" style="border:1px solid #000; padding:4px; font-size:10px; text-align:center; vertical-align:middle;">Grade</th>
                                                    </tr>
                                                    <tr style="background:#f8f8f8;">
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtainable</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtained</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtainable</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtained</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtainable</th>
                                                        <th style="border:1px solid #000; padding:3px; font-size:9px; text-align:center;">Obtained</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($results as $result)
                                                    <tr>
                                                        <td style="border:1px solid #000; padding:5px; text-align:left;">{{ $result['course_name'] }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['first_half_obtainable'] }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['first_half_obtained'] > 0 ? $result['first_half_obtained'] : '-' }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['second_half_obtainable'] }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['second_half_obtained'] > 0 ? $result['second_half_obtained'] : '-' }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $result['final_obtainable'] }}</td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;"><strong>{{ $result['final_obtained'] > 0 ? $result['final_obtained'] : '-' }}</strong></td>
                                                        <td style="border:1px solid #000; padding:4px; text-align:center;"><strong>{{ $result['grade'] }}</strong></td>
                                                    </tr>
                                                    @empty
                                                    <tr><td colspan="8" style="text-align:center; padding:10px;">No subjects offered.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                            @endif

                                            {{-- ── Summary ──────────────────────────────────── --}}
                                            <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                                                <tr style="background:#f0f0f0;">
                                                    <td style="padding:5px;"><strong>NO. OF SUBJECTS:</strong></td>
                                                    <td style="padding:5px;">{{ $subjectCount }}</td>
                                                    <td style="padding:5px;"><strong>TOTAL OBTAINABLE:</strong></td>
                                                    <td style="padding:5px;">{{ $subjectCount * 100 }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:5px;"><strong>TOTAL SCORE:</strong></td>
                                                    <td style="padding:5px;"><strong>{{ $overallTotal }}</strong></td>
                                                    <td style="padding:5px;"><strong>AVERAGE:</strong></td>
                                                    <td style="padding:5px;"><strong>{{ $overallAverage }}</strong></td>
                                                </tr>
                                                <tr style="background:#f0f0f0;">
                                                    <td style="padding:5px;"><strong>GRADE:</strong></td>
                                                    <td style="padding:5px;"><strong>{{ $overallGrade }}</strong></td>
                                                    <td style="padding:5px;"><strong>POSITION:</strong></td>
                                                    <td style="padding:5px;"><strong>{{ $formattedPosition }} / {{ $totalStudentsInClass }}</strong></td>
                                                </tr>
                                            </table>

                                            {{-- ── Remarks ──────────────────────────────────── --}}
                                            <div style="margin-top:15px; border-top:1px solid #000; padding-top:10px;">
                                                <div style="margin-bottom:10px; font-size:12px;">
                                                    <strong>CLASS TEACHER'S REMARK:</strong><br>
                                                    {{ $teacherRemark ?: '_________________________________________________' }}
                                                </div>

                                                @if(isset($isPrimary) && $isPrimary)
                                                <div style="font-size:12px;">
                                                    <strong>HEAD MASTER/MISTRESS REMARK:</strong><br>
                                                    {{ $headmasterRemark ?: '_________________________________________________' }}
                                                </div>
                                                @else
                                                <div style="font-size:12px;">
                                                    <strong>PRINCIPAL'S REMARK:</strong><br>
                                                    {{ $principalRemark ?: '_________________________________________________' }}
                                                </div>
                                                @endif
                                            </div>

                                            {{-- ── Signature ─────────────────────────────────── --}}
                                            <div style="margin-top:15px; text-align:center; border:1px solid #000; padding:10px;">
                                                <div style="font-weight:bold; font-size:13px;">
                                                    @if(isset($isPrimary) && $isPrimary)
                                                        HEAD MASTER/MISTRESS SIGNATURE & STAMP
                                                    @else
                                                        PRINCIPAL'S SIGNATURE & STAMP
                                                    @endif
                                                </div>
                                                <div style="height:40px; margin-top:10px; border-bottom:1px solid #000;"></div>
                                            </div>

                                        </div>{{-- /left --}}

                                        {{-- Right: Skills ────────────────────────────────── --}}
                                        <div style="display:table-cell; width:35%; vertical-align:top; border-left:1px solid #000; padding-left:10px;">

                                            {{-- Affective Skills --}}
                                            <div style="margin-bottom:15px;">
                                                <div style="font-weight:bold; background:#f0f0f0; padding:4px; border:1px solid #000; text-align:center;">
                                                    AFFECTIVE SKILLS
                                                </div>
                                                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Punctuality</td><td style="padding:3px; text-align:center; width:30px;">{{ $affectiveRatings['punctuality'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Politeness</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['politeness'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Neatness</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['neatness'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Honesty</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['honesty'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Leadership Skill</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['leadership_skill'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Cooperation</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['cooperation'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Attentiveness</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['attentiveness'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Perseverance</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['perseverance'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Attitude to Work</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['attitude_to_work'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Helping Others</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['helping_other'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Emotional Stability</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['emotional_stability'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Health</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['health'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Speaking/Handwriting</td><td style="padding:3px; text-align:center;">{{ $affectiveRatings['speaking_handwriting'] ?? '-' }}</td></tr>
                                                </table>
                                            </div>

                                            {{-- Psychomotor Skills --}}
                                            <div>
                                                <div style="font-weight:bold; background:#f0f0f0; padding:4px; border:1px solid #000; text-align:center;">
                                                    PSYCHOMOTOR SKILLS
                                                </div>
                                                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Handwriting</td><td style="padding:3px; text-align:center; width:30px;">{{ $psychomotorRatings['handwriting'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Verbal Fluency</td><td style="padding:3px; text-align:center;">{{ $psychomotorRatings['verbal_fluency'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Sports</td><td style="padding:3px; text-align:center;">{{ $psychomotorRatings['sports'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Handling Tools</td><td style="padding:3px; text-align:center;">{{ $psychomotorRatings['handling_tools'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Drawing & Painting</td><td style="padding:3px; text-align:center;">{{ $psychomotorRatings['drawing_painting'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Games</td><td style="padding:3px; text-align:center;">{{ $psychomotorRatings['games'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding:3px; border-right:1px solid #000;">Musical Skills</td><td style="padding:3px; text-align:center;">{{ $psychomotorRatings['musical_skills'] ?? '-' }}</td></tr>
                                                </table>
                                            </div>

                                            {{-- Rating Key --}}
                                            <div style="margin-top:10px; font-size:13px; border:1px solid #000; padding:5px;">
                                                <strong>RATING KEY:</strong><br>
                                                5 - Excellent | 4 - Very Good<br>
                                                3 - Good | 2 - Fair | 1 - Poor
                                            </div>

                                        </div>{{-- /right --}}
                                    </div>{{-- /main-content --}}

                                    {{-- ── Footer: Resumption Date, School Fees & Fees Payable ── --}}
                                    {{-- Data sourced from TermSetting (set by admin in Result Access settings) --}}
                                    <div style="margin-top:20px; padding-top:10px; border-top:2px solid #000;">
                                        <div style="display:table; width:100%; font-size:13px;">
                                            <div style="display:table-cell; width:34%; vertical-align:top; padding-right:8px;">
                                                <strong>Next Term Resumption Date:</strong><br>
                                                <span style="font-size:13px; font-weight:bold;">
                                                    @if($termSettings?->resumption_date)
                                                        {{ \Carbon\Carbon::parse($termSettings->resumption_date)->format('l, d F Y') }}
                                                    @else
                                                        ____________________________
                                                    @endif
                                                </span>
                                            </div>
                                            <div style="display:table-cell; width:33%; vertical-align:top; padding-right:8px; text-align:center;">
                                                <strong>School Fees:</strong><br>
                                                <span style="font-size:13px; font-weight:bold;">
                                                    @if($termSettings?->school_fees)
                                                        &#8358;{{ number_format($termSettings->school_fees, 2) }}
                                                    @else
                                                        ____________________________
                                                    @endif
                                                </span>
                                            </div>
                                            <div style="display:table-cell; width:33%; vertical-align:top; text-align:right;">
                                                <strong>Fees Payable By:</strong><br>
                                                <span style="font-size:13px; font-weight:bold;">
                                                    @if($termSettings?->fees_payable_by)
                                                        {{ \Carbon\Carbon::parse($termSettings->fees_payable_by)->format('l, d F Y') }}
                                                    @else
                                                        ____________________________
                                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        @if($termSettings?->notes)
                                        <div style="margin-top:8px; font-size:12px; font-style:italic; border-top:1px dashed #999; padding-top:6px;">
                                            <strong>Note:</strong> {{ $termSettings->notes }}
                                        </div>
                                        @endif
                                    </div>

                                    @endif {{-- end nursery/primary/secondary split --}}

                                </div>{{-- /container --}}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>
</body>

<style media="print">
    @page { size: A4; margin: 15mm; }
    body, .main-wrapper, #app, .main-content, .section, .col-12 {
        margin: 0 !important; padding: 0 !important; background: white !important;
    }
    body * { visibility: hidden; }
    .card, .card * { visibility: visible; }
    .card {
        position: fixed; left: 0; top: 0;
        width: 100%; max-width: 210mm;
        box-shadow: none !important; border: none !important;
    }
    .card-header .btn { display: none !important; }
    .card-body { padding: 0 !important; }
    .navbar-bg, .main-sidebar, .navbar, .main-footer, .loader { display: none !important; }
    table { page-break-inside: avoid; }
    tr { page-break-inside: avoid; }
</style>