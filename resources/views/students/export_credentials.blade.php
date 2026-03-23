<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login Credentials</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            background: #f0f2f5;
            color: #222;
        }

        .screen-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: #1a1a2e;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .screen-toolbar h2 {
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-print {
            background: #27ae60;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-print:hover {
            background: #1e8449;
        }

        .btn-back {
            background: transparent;
            color: #ccc;
            border: 1px solid #555;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: #333;
            color: #fff;
        }

        .page-wrapper {
            max-width: 960px;
            margin: 80px auto 40px;
            padding: 0 20px;
        }

        .document {
            background: #fff;
            padding: 36px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.1);
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 2px solid #1a1a2e;
        }

        .report-header h1 {
            font-size: 20px;
            font-weight: bold;
            color: #1a1a2e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-header p {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #888;
            margin-bottom: 16px;
        }

        .info-box {
            background: #e8f4fd;
            border-left: 4px solid #2980b9;
            padding: 10px 14px;
            margin-bottom: 10px;
            font-size: 11.5px;
            color: #1a5276;
            border-radius: 0 4px 4px 0;
        }

        .warning-box {
            background: #fff8e1;
            border-left: 4px solid #f39c12;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 11.5px;
            color: #7d5a00;
            border-radius: 0 4px 4px 0;
        }

        .class-section {
            margin-bottom: 28px;
        }

        .class-title {
            background: #1a1a2e;
            color: #fff;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 4px 4px 0 0;
        }

        .class-title .count-badge {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #e8eaf6;
        }

        thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #333;
            border: 1px solid #ccc;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        tbody tr:hover {
            background: #eef2ff;
        }

        tbody td {
            padding: 6px 10px;
            font-size: 11px;
            border: 1px solid #ddd;
            color: #333;
            vertical-align: middle;
        }

        .col-sn       { width: 4%;  text-align: center; color: #999; }
        .col-adm      { width: 10%; font-weight: 600; color: #1a1a2e; }
        .col-name     { width: 28%; }
        .col-class    { width: 14%; }
        .col-email    { width: 28%; font-size: 10.5px; color: #555; }
        .col-password { width: 16%; font-family: monospace; color: #c0392b; font-weight: bold; }

        .no-email {
            color: #bbb;
            font-style: italic;
            font-size: 10px;
        }

        .total-summary {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            color: #555;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .doc-footer {
            text-align: center;
            font-size: 10px;
            color: #aaa;
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        /* ── Print styles ── */
        @media print {
            body {
                background: #fff;
                font-size: 11px;
            }

            .screen-toolbar {
                display: none !important;
            }

            .page-wrapper {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }

            .document {
                box-shadow: none;
                border-radius: 0;
                padding: 20px 24px;
            }

            tbody tr:hover {
                background: inherit;
            }

            .class-section {
                page-break-inside: avoid;
            }

            .warning-box,
            .info-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .class-title {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            thead tr {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tbody tr:nth-child(even) {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    {{-- ── Screen toolbar (hidden on print) ── --}}
    <div class="screen-toolbar">
        <h2>&#128196; Student Login Credentials</h2>
        <div class="toolbar-actions">
            <a href="{{ url()->previous() }}" class="btn-back">
                &#8592; Back
            </a>
            <button class="btn-print" onclick="window.print()">
                &#128438; Print / Save as PDF
            </button>
        </div>
    </div>

    {{-- ── Printable document ── --}}
    <div class="page-wrapper">
        <div class="document">

            <div class="report-header">
                <h1>Student Login Credentials</h1>
                <p>Admission Numbers &bull; Names &bull; Classes &bull; Login Email &bull; Default Passwords</p>
            </div>

            <div class="meta-info">
                <span>Generated by: <strong>{{ $generatedBy }}</strong></span>
                <span>Date: <strong>{{ $generatedAt }}</strong></span>
            </div>

            <div class="info-box">
                &#8505; <strong>Login Information:</strong>
                Students log in using their <strong>email address</strong> as their username.
                If a student has no email listed, they may use their
                <strong>admission number</strong> to identify themselves to an administrator
                for account setup. The login URL is: <strong>{{ config('app.url') }}</strong>
            </div>

            <div class="warning-box">
                &#9888; <strong>Confidential:</strong> This document contains login credentials.
                Please distribute securely. The default password shown is <strong>12345</strong>
                — this is the password that will be set if an administrator uses the
                <em>"Reset Password"</em> function. Students who have already changed their
                password will have a different password. Advise all students to change their
                password after first login.
            </div>

            @foreach($groupedByClass as $className => $classStudents)
                <div class="class-section">
                    <div class="class-title">
                        <span>{{ $className }}</span>
                        <span class="count-badge">{{ $classStudents->count() }} student(s)</span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th class="col-sn">#</th>
                                <th class="col-adm">Adm. No</th>
                                <th class="col-name">Student Name</th>
                                <th class="col-class">Class</th>
                                <th class="col-email">Login Email</th>
                                <th class="col-password">Default Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classStudents as $index => $student)
                                <tr>
                                    <td class="col-sn">{{ $index + 1 }}</td>
                                    <td class="col-adm">{{ $student->admission_no ?? 'N/A' }}</td>
                                    <td class="col-name">{{ $student->name }}</td>
                                    <td class="col-class">{{ $student->class->name ?? 'N/A' }}</td>
                                    <td class="col-email">
                                        @if($student->email)
                                            {{ $student->email }}
                                        @else
                                            <span class="no-email">&#8212; no email set &#8212;</span>
                                        @endif
                                    </td>
                                    <td class="col-password">12345</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <div class="total-summary">
                Total Students: {{ $grandTotal }}
            </div>

            <div class="doc-footer">
                This document is auto-generated and intended for administrative use only.
                &mdash; {{ config('app.name') }} &mdash; {{ now()->format('Y') }}
            </div>

        </div>
    </div>

</body>
</html>