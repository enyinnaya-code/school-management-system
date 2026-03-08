{{-- ============================================ --}}
{{-- FILE: resources/views/exam_questions/print.blade.php --}}
{{-- ============================================ --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->exam_title }} - Print</title>
    <style>
        @page {
            size: A4;
            margin: 7mm 10mm 7mm 10mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            line-height: 1.1;
            color: #000;
            background: #fff;
        }

        /* Toolbar (screen only) */
        .print-button-container {
            position: fixed;
            top: 10px; right: 10px;
            z-index: 1000;
            display: flex;
            gap: 6px;
        }
        .print-button-container button {
            padding: 5px 14px;
            font-size: 13px;
            cursor: pointer;
            border: 1px solid #555;
            border-radius: 3px;
            background: #fff;
        }
        @media print {
            .print-button-container { display: none !important; }
        }

        /* Header */
        .exam-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }
        .school-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .school-address { font-size: 12px; }
        .exam-title {
            font-size: 15px;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 2px;
        }

        /* Exam info box */
        .exam-info {
            border: 1px solid #000;
            padding: 2px 5px;
            margin-bottom: 3px;
        }
        .exam-info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0 14px;
            line-height: 1.2;
        }
        .exam-info-label { font-weight: bold; white-space: nowrap; }

        /* Student name */
        .student-info {
            border: 1px solid #000;
            padding: 2px 5px;
            margin-bottom: 3px;
            display: flex;
            align-items: flex-end;
            gap: 6px;
        }
        .student-info-label { font-weight: bold; font-size: 14px; white-space: nowrap; }
        .student-info-value { border-bottom: 1px solid #000; flex: 1; }

        /* Instructions */
        .instructions {
            border: 1px solid #000;
            padding: 2px 5px;
            margin-bottom: 3px;
            line-height: 1.15;
            font-size: 13px;
        }
        .instructions-title { font-weight: bold; font-size: 14px; }

        /* Section */
        .section { margin-top: 4px; }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1.5px solid #000;
            padding-bottom: 1px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .section-instructions {
            font-style: italic;
            font-size: 13px;
            margin-bottom: 2px;
            line-height: 1.1;
        }

        /* Question */
        .question {
            display: flex;
            align-items: flex-start;
            gap: 3px;
            margin-bottom: 2px;
            page-break-inside: avoid;
        }
        .question-number {
            font-weight: bold;
            white-space: nowrap;
            min-width: 22px;
            flex-shrink: 0;
            font-size: 14px;
            line-height: 1.1;
        }
        .question-body { flex: 1; }
        .question-text { font-size: 14px; line-height: 1.1; }
        .question-marks { font-size: 12px; white-space: nowrap; flex-shrink: 0; line-height: 1.1; }

        /* Multiple choice 2-column */
        .options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 10px;
            margin-top: 1px;
            margin-left: 22px;
            font-size: 13px;
            line-height: 1.1;
        }
        .option { display: flex; gap: 2px; }

        /* True/False inline */
        .tf-options {
            display: flex;
            gap: 20px;
            margin-left: 22px;
            margin-top: 1px;
            font-size: 13px;
            line-height: 1.1;
        }

        /* Answer spaces */
        .answer-space {
            border-bottom: 1px solid #aaa;
            height: 10px;
            margin-bottom: 3px;
            margin-left: 22px;
        }
        .answer-inline {
            margin-left: 22px;
            font-size: 13px;
            margin-top: 1px;
        }

        /* End of exam */
        .end-of-exam {
            text-align: center;
            margin-top: 5px;
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 2px;
        }

        /* Marking scheme */
        .marking-scheme {
            margin-top: 6px;
            page-break-before: always;
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 13px;
        }
        .marking-scheme-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    {{-- Toolbar --}}
    <div class="print-button-container">
        <button onclick="window.print()">🖨 Print</button>
        <button onclick="window.close()">✕ Close</button>
    </div>

    {{-- Header --}}
    <div class="exam-header">
        @if($exam->school_name)
            <div class="school-name">{{ $exam->school_name }}</div>
        @endif
        @if($exam->school_address)
            <div class="school-address">{{ $exam->school_address }}</div>
        @endif
        <div class="exam-title">{{ $exam->exam_title }}</div>
    </div>

    {{-- Exam Info --}}
    <div class="exam-info">
        <div class="exam-info-row">
            <span class="exam-info-label">Class:</span><span>{{ $exam->schoolClass->name }}</span>
            <span class="exam-info-label">Subject:</span><span>{{ $exam->subject->course_name }}</span>
            <span class="exam-info-label">Term:</span><span>{{ $exam->term->name }}</span>
            <span class="exam-info-label">Type:</span><span>{{ $exam->exam_type }}</span>
        </div>
        <div class="exam-info-row">
            @if($exam->exam_date)
                <span class="exam-info-label">Date:</span><span>{{ $exam->formatted_exam_date }}</span>
            @endif
            @if($exam->duration_minutes)
                <span class="exam-info-label">Duration:</span><span>{{ $exam->duration_formatted }}</span>
            @endif
            <span class="exam-info-label">Total Marks:</span><span><strong>{{ $exam->total_marks }}</strong></span>
        </div>
    </div>

    {{-- Student Name only (no admission no., no signature) --}}
    <div class="student-info">
        <span class="student-info-label">Student Name:</span>
        <span class="student-info-value"></span>
    </div>

    {{-- Instructions --}}
    @if($exam->instructions)
        <div class="instructions">
            <span class="instructions-title">INSTRUCTIONS: </span>{!! nl2br(e($exam->instructions)) !!}
        </div>
    @endif

    {{-- Sections & Questions --}}
    @if($exam->sections)
        @php $globalQ = 0; @endphp
        @foreach($exam->sections as $sectionIndex => $section)
            <div class="section">

                <div class="section-title">
                    @if(isset($section['title']) && $section['title'])
                        {{ $section['title'] }}
                    @else
                        Section {{ chr(64 + $sectionIndex) }}
                    @endif
                </div>

                @if(isset($section['instructions']) && $section['instructions'])
                    <div class="section-instructions">{{ $section['instructions'] }}</div>
                @endif

                @if(isset($section['questions']))
                    @foreach($section['questions'] as $questionIndex => $question)
                        @php
                            $globalQ++;
                            $type    = $question['type'] ?? 'essay';
                            $options = $question['options'] ?? [];
                            $marks   = $question['marks'] ?? null;
                        @endphp

                        <div class="question">
                            <span class="question-number">{{ $globalQ }}.</span>
                            <div class="question-body">
                                <span class="question-text">{{ $question['text'] }}</span>

                                @if($type === 'multiple_choice' && count(array_filter($options)) > 0)
                                    <div class="options">
                                        @foreach($options as $oIdx => $opt)
                                            @if($opt)
                                                <div class="option"><strong>{{ chr(65 + $oIdx) }}.</strong>&nbsp;{{ $opt }}</div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="answer-inline"><strong>Ans:</strong> _______</div>

                                @elseif($type === 'true_false')
                                    <div class="tf-options">
                                        <span><strong>A.</strong> True</span>
                                        <span><strong>B.</strong> False</span>
                                    </div>
                                    <div class="answer-inline"><strong>Ans:</strong> _______</div>

                                @elseif($type === 'essay')
                                    @for($l = 0; $l < 4; $l++)<div class="answer-space"></div>@endfor

                                @elseif($type === 'short' || $type === 'fill_blank')
                                    @for($l = 0; $l < 2; $l++)<div class="answer-space"></div>@endfor
                                @endif

                            </div>
                            @if($marks)
                                <span class="question-marks">({{ $marks }}mk{{ $marks > 1 ? 's' : '' }})</span>
                            @endif
                        </div>

                    @endforeach
                @endif
            </div>
        @endforeach
    @endif

    <div class="end-of-exam">— END OF EXAMINATION —</div>

    @if($exam->show_marking_scheme && $exam->marking_scheme)
        <div class="marking-scheme">
            <div class="marking-scheme-title">
                MARKING SCHEME<br>
                <small style="font-size:11px;">(FOR TEACHER USE ONLY — DO NOT INCLUDE IN STUDENT COPIES)</small>
            </div>
            {!! nl2br(e($exam->marking_scheme)) !!}
        </div>
    @endif

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>