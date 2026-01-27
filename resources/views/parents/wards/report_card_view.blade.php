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
                                <a href="{{ route('parents.wards.reportcards.pdf') }}" target="_blank" class="btn btn-success btn-lg">
                                    <i class="fas fa-file-pdf"></i> Download as PDF
                                </a>
                            </div>
                            <div class="card-body p-4">
                                <!-- Full Report Card HTML -->
                                <div class="container"
                                    style="border: 2px solid #000; padding: 20px; font-family: Arial, sans-serif; font-size: 14px;">
                                    
                                    <!-- Header -->
                                    <div class="header"
                                        style="display: table; width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
                                        <div class="header-left"
                                            style="display: table-cell; width: 120px; text-align: center;">
                                            @php
                                                $settings = school_settings();
                                                $logoPath = $settings && $settings->logo
                                                    ? asset('storage/logos/' . $settings->logo)
                                                    : asset('images/school_management_logo__1_-removebg-preview.png');
                                            @endphp
                                            <img src="{{ $logoPath }}" alt="School Logo"
                                                style="width: 90px; height: 90px; object-fit: contain;">
                                        </div>
                                        <div class="header-center"
                                            style="display: table-cell; text-align: center; padding: 0 10px;">
                                            <div style="font-size: 18px; font-weight: bold;">
                                                {{ strtoupper(school_name()) }}
                                            </div>
                                            <div style="font-style: italic; font-size: 12px;">
                                                Motto: Excellence Personified
                                            </div>
                                            <div style="font-size: 13px;">
                                                {{ $settings->address ?? 'School Address' }}
                                            </div>
                                            <div style="font-size: 14px; font-weight: bold; margin-top: 5px;">
                                                STUDENT'S ACADEMIC REPORT CARD
                                            </div>
                                        </div>
                                        <div class="header-right"
                                            style="display: table-cell; width: 80px; text-align: center;">
                                            <div style="background-color: #f0f0f0; padding: 3px 10px; border-radius: 3px; font-weight: bold;">
                                                {{ $class->name }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Student Name & Info -->
                                    <div style="text-align: center; padding: 8px 0; border-bottom: 2px solid #000;">
                                        <div style="font-size: 18px; font-weight: bold;">
                                            {{ strtoupper($student->name) }}
                                        </div>
                                        <div style="font-size: 12px;">
                                            <strong>Gender:</strong> {{ ucfirst($student->gender) }} |
                                            <strong>Admission No:</strong> {{ $student->admission_no }}
                                        </div>
                                    </div>

                                    <!-- Term Info -->
                                    <div style="display: table; width: 100%; margin: 10px 0; border-bottom: 1px solid #000; padding-bottom: 8px;">
                                        <div style="display: table-cell; width: 33.33%; font-size: 12px;">
                                            <div><strong>Term:</strong> {{ $currentTerm->name }}</div>
                                            <div><strong>Session:</strong> {{ $currentSession->name }}</div>
                                        </div>
                                        <div style="display: table-cell; width: 33.33%; font-size: 12px; text-align: center;">
                                            <div><strong>Class:</strong> {{ $class->name }}</div>
                                            <div><strong>Class Teacher:</strong> {{ $classTeacher?->name ?? 'Not Assigned' }}</div>
                                        </div>
                                        <div style="display: table-cell; width: 33.33%; font-size: 12px; text-align: right;">
                                            <div><strong>No. in Class:</strong> {{ $totalStudentsInClass }}</div>
                                            <div><strong>Position:</strong> <strong>{{ $formattedPosition }}</strong></div>
                                        </div>
                                    </div>

                                    <!-- Main Content: Results + Skills -->
                                    <div style="display: table; width: 100%; margin-top: 10px;">
                                        <!-- Academic Results -->
                                        <div style="display: table-cell; width: 65%; vertical-align: top; padding-right: 12px;">
                                            <table style="width: 100%; border-collapse: collapse;">
                                                <thead>
                                                    <tr style="background-color: #f0f0f0;">
                                                        <th style="border: 1px solid #000; padding: 5px; width: 25%;">SUBJECTS</th>
                                                        <th style="border: 1px solid #000; padding: 5px;">1st CA (10)</th>
                                                        <th style="border: 1px solid #000; padding: 5px;">2nd CA (10)</th>
                                                        <th style="border: 1px solid #000; padding: 5px;">Mid Term (10)</th>
                                                        <th style="border: 1px solid #000; padding: 5px;">Exam (70)</th>
                                                        <th style="border: 1px solid #000; padding: 5px;">TOTAL</th>
                                                        <th style="border: 1px solid #000; padding: 5px;">GRADE</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($results as $result)
                                                    <tr>
                                                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">
                                                            {{ $result['course_name'] }}
                                                        </td>
                                                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">
                                                            {{ $result['first_ca'] > 0 ? $result['first_ca'] : '-' }}
                                                        </td>
                                                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">
                                                            {{ $result['second_ca'] > 0 ? $result['second_ca'] : '-' }}
                                                        </td>
                                                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">
                                                            {{ $result['mid_term_test'] > 0 ? $result['mid_term_test'] : '-' }}
                                                        </td>
                                                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">
                                                            {{ $result['examination'] > 0 ? $result['examination'] : '-' }}
                                                        </td>
                                                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">
                                                            <strong>{{ $result['total'] > 0 ? $result['total'] : '-' }}</strong>
                                                        </td>
                                                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">
                                                            <strong>{{ $result['grade'] }}</strong>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="7" style="text-align:center; padding: 10px;">
                                                            No subjects offered.
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>

                                            <!-- Summary Table -->
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

                                            <!-- Remarks -->
                                            <div style="margin-top: 15px; border-top: 1px solid #000; padding-top: 10px;">
                                                <div style="margin-bottom: 10px;">
                                                    <strong>CLASS TEACHER'S REMARK:</strong><br>
                                                    {{ $teacherRemark ?: '_________________________________________________' }}
                                                </div>
                                                <div>
                                                    <strong>PRINCIPAL'S REMARK:</strong><br>
                                                    {{ $principalRemark ?: '_________________________________________________' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Skills Section -->
                                        <div style="display: table-cell; width: 35%; vertical-align: top; border-left: 1px solid #000; padding-left: 10px;">
                                            <div style="margin-bottom: 15px;">
                                                <div style="font-weight: bold; background-color: #f0f0f0; padding: 4px; border: 1px solid #000; text-align: center;">
                                                    AFFECTIVE SKILLS
                                                </div>
                                                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Punctuality</td><td style="padding: 3px; text-align: center;">{{ $affectiveRatings['punctuality'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Politeness</td><td style="padding: 3px; text-align: center;">{{ $affectiveRatings['politeness'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Neatness</td><td style="padding: 3px; text-align: center;">{{ $affectiveRatings['neatness'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Honesty</td><td style="padding: 3px; text-align: center;">{{ $affectiveRatings['honesty'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Leadership Skill</td><td style="padding: 3px; text-align: center;">{{ $affectiveRatings['leadership_skill'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Cooperation</td><td style="padding: 3px; text-align: center;">{{ $affectiveRatings['cooperation'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Attentiveness</td><td style="padding: 3px; text-align: center;">{{ $affectiveRatings['attentiveness'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Perseverance</td><td style="padding: 3px; text-align: center;">{{ $affectiveRatings['perseverance'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Attitude to Work</td><td style="padding: 3px; text-align: center;">{{ $affectiveRatings['attitude_to_work'] ?? '-' }}</td></tr>
                                                </table>
                                            </div>

                                            <div>
                                                <div style="font-weight: bold; background-color: #f0f0f0; padding: 4px; border: 1px solid #000; text-align: center;">
                                                    PSYCHOMOTOR SKILLS
                                                </div>
                                                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Handwriting</td><td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['handwriting'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Verbal Fluency</td><td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['verbal_fluency'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Sports</td><td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['sports'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Handling Tools</td><td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['handling_tools'] ?? '-' }}</td></tr>
                                                    <tr><td style="padding: 3px; border-right: 1px solid #000;">Drawing & Painting</td><td style="padding: 3px; text-align: center;">{{ $psychomotorRatings['drawing_painting'] ?? '-' }}</td></tr>
                                                </table>
                                            </div>

                                            <div style="margin-top: 10px; font-size: 13px; border: 1px solid #000; padding: 5px;">
                                                <strong>RATING KEY:</strong><br>
                                                5 - Excellent | 4 - Very Good<br>
                                                3 - Good | 2 - Fair | 1 - Poor
                                            </div>

                                            <div style="margin-top: 15px; text-align: center; border: 1px solid #000; padding: 10px;">
                                                <div style="font-weight: bold; font-size: 13px;">PRINCIPAL'S SIGNATURE & STAMP</div>
                                                <div style="height: 40px; margin-top: 10px; border-bottom: 1px solid #000;"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #000; font-size: 13px; text-align: center;">
                                        <strong>Next Term Begins:</strong> ____________________ |
                                        <strong>Next Term Fees Payable By:</strong> ____________________
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>
</body>

<!-- Print & PDF Styles -->
<style media="print">
    @page {
        size: A4;
        margin: 15mm;
    }

    body, .main-wrapper, #app, .main-content, .section, .col-12 {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    body * {
        visibility: hidden;
    }

    .card, .card * {
        visibility: visible;
    }

    .card {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        max-width: 210mm;
        box-shadow: none !important;
        border: none !important;
    }

    .card-header .btn {
        display: none !important;
    }

    .card-body {
        padding: 0 !important;
    }

    .navbar-bg, .main-sidebar, .navbar, .main-footer, .loader {
        display: none !important;
    }

    table { page-break-inside: avoid; }
    tr { page-break-inside: avoid; }
</style>