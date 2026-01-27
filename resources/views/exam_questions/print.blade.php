{{-- ============================================ --}}
{{-- FILE: resources/views/exam_questions/print.blade.php --}}
{{-- ============================================ --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->exam_title }} - Print</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .exam-header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .school-name {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .school-address {
            font-size: 11pt;
            color: #333;
            margin-bottom: 10px;
        }

        .exam-title {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 15px;
            text-decoration: underline;
        }

        .exam-info {
            margin: 20px 0;
            border: 2px solid #000;
            padding: 10px;
        }

        .exam-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .exam-info-label {
            font-weight: bold;
            width: 150px;
        }

        .student-info {
            margin: 20px 0;
            border: 2px solid #000;
            padding: 10px;
        }

        .student-info-row {
            display: flex;
            margin-bottom: 10px;
        }

        .student-info-label {
            font-weight: bold;
            width: 150px;
        }

        .student-info-value {
            border-bottom: 1px solid #000;
            flex: 1;
        }

        .instructions {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin: 15px 0;
            page-break-inside: avoid;
        }

        .instructions-title {
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 8px;
        }

        .section {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .section-instructions {
            font-style: italic;
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
        }

        .question {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .question-number {
            font-weight: bold;
            display: inline-block;
            margin-right: 8px;
        }

        .question-text {
            display: inline;
        }

        .question-marks {
            float: right;
            font-weight: bold;
            color: #666;
        }

        .options {
            margin-left: 30px;
            margin-top: 8px;
        }

        .option {
            margin-bottom: 5px;
        }

        .answer-space {
            margin-top: 10px;
            border-bottom: 1px solid #ccc;
            min-height: 30px;
        }

        .answer-space.large {
            min-height: 100px;
        }

        .marking-scheme {
            margin-top: 30px;
            page-break-before: always;
            border: 2px solid #000;
            padding: 15px;
        }

        .marking-scheme-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            color: #d9534f;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .page-break {
                page-break-after: always;
            }
        }

        .print-button-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        @media print {
            .print-button-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    {{-- Print Button --}}
    <div class="print-button-container no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <i class="fas fa-print"></i> Print Exam
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    {{-- Exam Header --}}
    <div class="exam-header">
        @if($exam->school_name)
            <div class="school-name">{{ $exam->school_name }}</div>
        @endif
        @if($exam->school_address)
            <div class="school-address">{{ $exam->school_address }}</div>
        @endif
        <div class="exam-title">{{ $exam->exam_title }}</div>
    </div>

    {{-- Exam Information --}}
    <div class="exam-info">
        <div class="exam-info-row">
            <span class="exam-info-label">Class:</span>
            <span>{{ $exam->schoolClass->name }}</span>
            <span class="exam-info-label">Subject:</span>
            <span>{{ $exam->subject->course_name }}</span>
        </div>
        <div class="exam-info-row">
            <span class="exam-info-label">Exam Type:</span>
            <span>{{ $exam->exam_type }}</span>
            <span class="exam-info-label">Term:</span>
            <span>{{ $exam->term->name }}</span>
        </div>
        <div class="exam-info-row">
            <span class="exam-info-label">Date:</span>
            <span>{{ $exam->formatted_exam_date }}</span>
            <span class="exam-info-label">Duration:</span>
            <span>{{ $exam->duration_formatted }}</span>
        </div>
        <div class="exam-info-row">
            <span class="exam-info-label">Total Marks:</span>
            <span><strong>{{ $exam->total_marks }} marks</strong></span>
        </div>
    </div>

    {{-- Student Information Section --}}
    <div class="student-info">
        <div class="student-info-row">
            <span class="student-info-label">Student Name:</span>
            <span class="student-info-value"></span>
        </div>
        <div class="student-info-row">
            <span class="student-info-label">Admission Number:</span>
            <span class="student-info-value"></span>
        </div>
        <div class="student-info-row">
            <span class="student-info-label">Signature:</span>
            <span class="student-info-value"></span>
        </div>
    </div>

    {{-- General Instructions --}}
    @if($exam->instructions)
        <div class="instructions">
            <div class="instructions-title">INSTRUCTIONS:</div>
            <div>{!! nl2br(e($exam->instructions)) !!}</div>
        </div>
    @endif

    {{-- Exam Sections --}}
    @if($exam->sections)
        @foreach($exam->sections as $sectionIndex => $section)
            <div class="section">
                {{-- Section Title --}}
                @if(isset($section['title']) && $section['title'])
                    <div class="section-title">{{ $section['title'] }}</div>
                @else
                    <div class="section-title">SECTION {{ chr(64 + $sectionIndex) }}</div>
                @endif

                {{-- Section Instructions --}}
                @if(isset($section['instructions']) && $section['instructions'])
                    <div class="section-instructions">
                        {{ $section['instructions'] }}
                    </div>
                @endif

                {{-- Questions --}}
                @if(isset($section['questions']))
                    @foreach($section['questions'] as $questionIndex => $question)
                        <div class="question">
                            <div>
                                <span class="question-number">{{ $questionIndex }}.</span>
                                <span class="question-text">{{ $question['text'] }}</span>
                                @if(isset($question['marks']))
                                    <span class="question-marks">[{{ $question['marks'] }} marks]</span>
                                @endif
                            </div>

                            {{-- Multiple Choice Options --}}
                            @if(isset($question['type']) && $question['type'] == 'multiple_choice' && isset($question['options']))
                                <div class="options">
                                    @foreach($question['options'] as $optionIndex => $option)
                                        @if($option)
                                            <div class="option">
                                                <strong>{{ chr(65 + $optionIndex) }}.</strong> {{ $option }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            {{-- True/False Options --}}
                            @if(isset($question['type']) && $question['type'] == 'true_false')
                                <div class="options">
                                    <div class="option"><strong>A.</strong> True</div>
                                    <div class="option"><strong>B.</strong> False</div>
                                </div>
                            @endif

                            {{-- Answer Space for Essay/Short Answer --}}
                            @if(isset($question['type']))
                                @if($question['type'] == 'essay')
                                    <div class="answer-space large"></div>
                                    <div class="answer-space large"></div>
                                @elseif($question['type'] == 'short' || $question['type'] == 'fill_blank')
                                    <div class="answer-space"></div>
                                @elseif($question['type'] == 'multiple_choice' || $question['type'] == 'true_false')
                                    <div style="margin-top: 8px;">
                                        <strong>Answer:</strong> _____________
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    @endif

    {{-- End of Exam --}}
    <div style="text-align: center; margin-top: 40px; font-weight: bold; font-size: 14pt;">
        - END OF EXAMINATION -
    </div>

    {{-- Marking Scheme (if enabled) --}}
    @if($exam->show_marking_scheme && $exam->marking_scheme)
        <div class="marking-scheme">
            <div class="marking-scheme-title">
                MARKING SCHEME
                <br>
                <small style="font-size: 10pt;">(FOR TEACHER USE ONLY - DO NOT INCLUDE IN STUDENT COPIES)</small>
            </div>
            <div>{!! nl2br(e($exam->marking_scheme)) !!}</div>
        </div>
    @endif

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>