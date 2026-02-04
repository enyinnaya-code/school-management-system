<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Transcript - {{ $student->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
        }

        /* Header Section - Elegant and Professional */
        .transcript-header {
            display: table;
            width: 100%;
            border: 2px solid #000;
            border-bottom: 3px double #000;
            padding: 8px;
            margin-bottom: 8px;
        }

        .header-left {
            display: table-cell;
            width: 80px;
            text-align: center;
            vertical-align: middle;
            border-right: 1px solid #ccc;
            padding-right: 8px;
        }

        .header-left img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 3px;
        }

        .header-center {
            display: table-cell;
            text-align: center;
            padding: 0 10px;
            vertical-align: middle;
        }

        .header-center .school-name {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
            display: inline-block;
        }

        .header-center .motto {
            font-style: italic;
            font-size: 11px;
            margin: 3px 0;
            color: #333;
        }

        .header-center .address {
            font-size: 10px;
            margin-bottom: 5px;
            color: #555;
        }

        .header-center .document-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
            letter-spacing: 1px;
            text-decoration: underline;
        }

        .header-right {
            display: table-cell;
            width: 70px;
            text-align: center;
            vertical-align: middle;
            border-left: 1px solid #ccc;
            padding-left: 8px;
        }

        .header-right .class-badge {
            border: 2px solid #000;
            padding: 6px;
            font-weight: bold;
            font-size: 13px;
        }

        /* Student Info Section */
        .student-info {
            padding: 8px;
            border: 1px solid #000;
            margin-bottom: 8px;
            background: #fafafa;
        }

        .student-info .student-name {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 4px;
            text-align: center;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .student-info .student-details {
            font-size: 11px;
            text-align: center;
            margin-bottom: 5px;
        }

        .student-info table {
            width: 100%;
            margin-top: 4px;
        }

        .student-info td {
            padding: 3px;
            text-align: left;
            font-size: 11px;
        }

        /* Session Block */
        .session-block {
            margin-bottom: 8px;
            page-break-inside: avoid;
            border: 2px solid #000;
            padding: 6px;
        }

        .session-title {
            border-bottom: 3px double #000;
            padding: 4px 0;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .term-block {
            margin-bottom: 6px;
            border: 1px solid #ccc;
            padding: 5px;
        }

        .term-title {
            border-bottom: 2px solid #000;
            padding: 4px 0;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: center;
            text-transform: uppercase;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        table th {
            background: #f5f5f5;
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        table td {
            padding: 3px 4px;
            border: 1px solid #999;
            font-size: 10px;
        }

        table tr:nth-child(even) {
            background: #fafafa;
        }

        .summary-row {
            background: #f0f0f0 !important;
            font-weight: bold;
            border-top: 2px solid #000 !important;
        }

        .summary-row td {
            border: 1px solid #000 !important;
            padding: 4px !important;
        }

        /* Session Summary Table */
        .session-summary {
            margin-top: 6px;
            background: #f8f8f8;
            border: 2px solid #000;
        }

        .session-summary td {
            padding: 5px;
            font-size: 11px;
            border: 1px solid #000;
        }

        /* Overall Summary */
        .overall-summary {
            border: 3px double #000;
            padding: 8px;
            margin-top: 8px;
            background: #f9f9f9;
        }

        .overall-summary h3 {
            margin-bottom: 6px;
            font-size: 13px;
            text-align: center;
            text-decoration: underline;
            text-underline-offset: 2px;
            letter-spacing: 0.5px;
        }

        .overall-summary table {
            width: 100%;
            border: 2px solid #000;
        }

        .overall-summary td {
            padding: 6px;
            border: 1px solid #000;
            font-size: 11px;
            font-weight: bold;
        }

        /* Grade Legend */
        .grade-legend {
            margin-top: 8px;
            padding: 8px;
            background: #fafafa;
            border: 1px solid #000;
        }

        .grade-legend h4 {
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: bold;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .grade-legend p {
            font-size: 10px;
            margin: 2px 0;
            line-height: 1.4;
        }

        /* Footer */
        .footer {
            margin-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #555;
            border-top: 2px solid #000;
            padding-top: 6px;
        }

        .footer p {
            margin: 2px 0;
        }

        .footer .signature-section {
            margin-top: 10px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 4px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin: 15px auto 3px;
        }

        /* Print Optimizations */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    @php
        $settings = school_settings();
        $logoPath = $settings && $settings->logo
            ? asset('storage/logos/' . $settings->logo)
            : asset('images/school_management_logo__1_-removebg-preview.png');
    @endphp

    <!-- Header - Professional Style -->
    <div class="transcript-header">
        <div class="header-left">
            <img src="{{ $logoPath }}" alt="School Logo">
        </div>
        <div class="header-center">
            <div class="school-name">{{ strtoupper(school_name()) }}</div>
            <div class="motto">"{{ $settings->motto ?? 'Excellence Personified' }}"</div>
            <div class="address">{{ $settings->address ?? 'School Address' }}</div>
            <div class="document-title">ACADEMIC TRANSCRIPT</div>
        </div>
        <div class="header-right">
            <div class="class-badge">{{ $class->name }}</div>
        </div>
    </div>

    <!-- Student Information -->
    <div class="student-info">
        <div class="student-name">{{ strtoupper($student->name) }}</div>
        <div class="student-details">
            <strong>Gender:</strong> {{ ucfirst($student->gender) }} | 
            <strong>Admission Number:</strong> {{ $student->admission_no }}
        </div>
        <table>
            <tr>
                <td style="width: 50%;"><strong>Date of Birth:</strong> {{ $student->dob }}</td>
                <td><strong>Current Class:</strong> {{ $class->name }}</td>
            </tr>
            <tr>
                <td><strong>Section:</strong> {{ $section->section_name }}</td>
                <td><strong>Date Generated:</strong> {{ now()->format('F d, Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- Academic Records by Session -->
    @foreach($transcriptData as $sessionData)
    <div class="session-block">
        <div class="session-title">{{ $sessionData['session']->name }} Academic Session</div>
        
        @foreach($sessionData['terms'] as $termData)
        <div class="term-block">
            <div class="term-title">{{ $termData['term']->name }}</div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">Subject</th>
                        <th style="text-align: center; width: 10%;">1st CA</th>
                        <th style="text-align: center; width: 10%;">2nd CA</th>
                        <th style="text-align: center; width: 10%;">Test</th>
                        <th style="text-align: center; width: 10%;">Exam</th>
                        <th style="text-align: center; width: 10%;">Total</th>
                        <th style="text-align: center; width: 10%;">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($termData['results'] as $result)
                    <tr>
                        <td>{{ $result->course->course_name }}</td>
                        <td style="text-align: center;">{{ $result->first_ca }}</td>
                        <td style="text-align: center;">{{ $result->second_ca }}</td>
                        <td style="text-align: center;">{{ $result->mid_term_test }}</td>
                        <td style="text-align: center;">{{ $result->examination }}</td>
                        <td style="text-align: center;"><strong>{{ $result->total }}</strong></td>
                        <td style="text-align: center;"><strong>{{ $result->grade }}</strong></td>
                    </tr>
                    @endforeach
                    <tr class="summary-row">
                        <td colspan="4"><strong>{{ $termData['term']->name }} Summary</strong></td>
                        <td style="text-align: center;"><strong>Total:</strong></td>
                        <td style="text-align: center;"><strong>{{ $termData['total'] }}</strong></td>
                        <td style="text-align: center;"><strong>{{ $termData['grade'] }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach
        
        <table class="session-summary">
            <tr class="summary-row">
                <td style="width: 40%;"><strong>Session Total:</strong> {{ $sessionData['session_total'] }}</td>
                <td style="text-align: center; width: 30%;"><strong>Session Average:</strong> {{ $sessionData['session_average'] }}</td>
                <td style="text-align: center; width: 30%;"><strong>Session Grade:</strong> {{ $sessionData['session_grade'] }}</td>
            </tr>
        </table>
    </div>
    @endforeach

    <!-- Overall Summary -->
    <div class="overall-summary">
        <h3>OVERALL ACADEMIC PERFORMANCE SUMMARY</h3>
        <table>
            <tr>
                <td style="width: 33.33%; text-align: center;"><strong>Cumulative Total:</strong><br>{{ $grandTotal }}</td>
                <td style="text-align: center; width: 33.33%; border-left: 2px solid #000; border-right: 2px solid #000;"><strong>Overall Average:</strong><br>{{ $overallAverage }}</td>
                <td style="text-align: center; width: 33.33%;"><strong>Final Grade:</strong><br>{{ $overallGrade }}</td>
            </tr>
        </table>
    </div>

    <!-- Grading Legend -->
    <div class="grade-legend">
        <h4>GRADING SCALE AND INTERPRETATION:</h4>
        <p><strong>Grade A (70-100):</strong> Excellent Performance | <strong>Grade B (60-69):</strong> Very Good Performance</p>
        <p><strong>Grade C (50-59):</strong> Good Performance | <strong>Grade D (45-49):</strong> Pass</p>
        <p><strong>Grade E (40-44):</strong> Marginal Pass | <strong>Grade F (0-39):</strong> Fail</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>{{ strtoupper(school_name()) }}</strong></p>
        <p>This is an official academic transcript issued on {{ now()->format('F d, Y') }}</p>
        <p>For verification or authentication, please contact the school administration.</p>
        <p style="margin-top: 5px;">{{ $settings->phone ?? 'School Phone' }} | {{ $settings->email ?? 'School Email' }}</p>
        
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong style="font-size: 10px;">Principal's Signature</strong><br>
                <span style="font-size: 9px;">Date: _______________</span>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong style="font-size: 10px;">School Seal/Stamp</strong><br>
                <span style="font-size: 9px;">&nbsp;</span>
            </div>
        </div>
    </div>
</body>
</html>