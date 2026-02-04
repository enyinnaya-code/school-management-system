<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Issued PINs</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .print-container {
            width: 287mm;
            margin: 0 auto;
            background: white;
        }

        .pin-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr); /* 8 columns */
            gap: 1mm;
            padding: 1mm;
        }

        .pin-card {
            border: 0.5px dashed #666;
            padding: 1.5mm;
            background: white;
            page-break-inside: avoid;
            position: relative;
            height: 35mm;
            font-size: 7px;
            overflow: hidden;
            display: flex;
            flex-direction: row;
            gap: 2mm;
        }

        /* Left side - PIN Code (Rectangular) */
        .pin-section {
            flex: 0 0 55%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 2px;
            padding: 2mm 1mm;
        }

        .pin-label {
            font-size: 6px;
            color: #666;
            margin-bottom: 1mm;
            font-weight: bold;
        }

        .pin-value {
            font-size: 11px;
            font-weight: bold;
            color: #c0392b;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
            word-break: break-all;
            line-height: 1.2;
            text-align: center;
        }

        /* Right side - Details */
        .details-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 0.5mm 0;
        }

        .student-name {
            font-weight: bold;
            color: #27ae60;
            font-size: 8px;
            margin-bottom: 1mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-line {
            margin: 0.5mm 0;
            line-height: 1.2;
            font-size: 7px;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        .value {
            color: #000;
        }

        .divider {
            border-top: 0.5px solid #ddd;
            margin: 1mm 0;
        }

        /* Print-specific styles */
        @media print {
            body {
                background: white;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Screen-only styles */
        @media screen {
            .controls {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
            }

            .btn {
                background: #3498db;
                color: white;
                border: none;
                padding: 12px 24px;
                font-size: 16px;
                border-radius: 5px;
                cursor: pointer;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                transition: all 0.3s;
            }

            .btn:hover {
                background: #2980b9;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }
        }
    </style>
</head>
<body>
    <!-- Print Button Only -->
    <div class="controls no-print">
        <button class="btn" onclick="window.print()">Print</button>
    </div>

    <div class="print-container">
        <div class="pin-grid">
            @foreach($issuedPins as $index => $issuedPin)
                <div class="pin-card">
                    <!-- Left: PIN Code (Rectangular) -->
                    <div class="pin-section">
                        <div class="pin-label">PIN</div>
                        <div class="pin-value">{{ $issuedPin->pin->pin_code }}</div>
                    </div>

                    <!-- Right: Details -->
                    <div class="details-section">
                        <!-- Student Name -->
                        <div class="student-name" title="{{ $issuedPin->student->name }}">
                            {{ $issuedPin->student->name }}
                        </div>

                        <div class="divider"></div>

                        <!-- Essential Info -->
                        <div class="info-line">
                            <span class="label">Class:</span> 
                            <span class="value">{{ $issuedPin->schoolClass->name }}</span>
                        </div>
                        
                        <div class="info-line">
                            <span class="label">Session:</span> 
                            <span class="value">{{ $issuedPin->session->name }}</span>
                        </div>
                        
                        <div class="info-line">
                            <span class="label">Term:</span> 
                            <span class="value">{{ $issuedPin->term->name }}</span>
                        </div>

                        <div class="info-line">
                            <span class="label">Uses:</span> 
                            <span class="value">{{ $issuedPin->pin->usage_count }}/5</span>
                        </div>
                    </div>
                </div>

                <!-- Page break after every 40 cards (8 columns × 5 rows) -->
                @if(($index + 1) % 40 == 0 && !$loop->last)
                    </div></div>
                    <div style="page-break-after: always;"></div>
                    <div class="print-container"><div class="pin-grid">
                @endif
            @endforeach
        </div>
    </div>
</body>
</html>