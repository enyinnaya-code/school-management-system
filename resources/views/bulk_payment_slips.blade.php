{{-- resources/views/bulk_payment_slips.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Payment Slips</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            background-color: #f5f5f5;
            padding: 10px;
        }
        .payment-slip {
            max-width: 210mm;
            margin: 0 auto 20px;
            background-color: white;
            padding: 15px 20px;
            page-break-inside: avoid;
            position: relative;
        }
        .payment-slip:last-child {
            page-break-after: avoid;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #000;
        }
        .govt-seal {
            width: 50px;
            height: 50px;
            margin: 0 auto 5px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 2px;
            color: #000;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h2 {
            font-size: 14px;
            color: #000;
            margin-bottom: 2px;
            font-weight: normal;
        }
        .header p {
            font-size: 11px;
            color: #000;
            font-weight: bold;
        }
        .slip-number {
            text-align: right;
            font-size: 10px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 15px;
            margin-bottom: 10px;
            font-size: 11px;
        }
        .info-item {
            display: flex;
            border-bottom: 1px dotted #666;
            padding-bottom: 3px;
        }
        .info-label {
            font-weight: bold;
            color: #000;
            min-width: 100px;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background-color: #e8e8e8;
            padding: 4px 8px;
            margin: 10px 0 8px 0;
            border-left: 4px solid #000;
            text-transform: uppercase;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 11px;
        }
        .salary-table th,
        .salary-table td {
            padding: 5px 8px;
            text-align: left;
            border: 1px solid #000;
        }
        .salary-table th {
            background-color: #d3d3d3;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            font-size: 10px;
        }
        .salary-table .total-row {
            background-color: #e8e8e8;
            font-weight: bold;
            border-top: 2px solid #000;
        }
        .salary-table .net-pay-row {
            background-color: #000;
            color: white;
            font-size: 12px;
            font-weight: bold;
            border: 2px solid #000;
        }
        .text-right {
            text-align: right;
        }
        .signature-section {
            margin-top: 15px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            font-size: 10px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            padding-top: 5px;
            font-weight: bold;
        }
        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 9px;
            color: #333;
            border-top: 1px solid #000;
            padding-top: 8px;
            line-height: 1.4;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            color: rgba(0, 0, 0, 0.03);
            font-weight: bold;
            z-index: 0;
            pointer-events: none;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .payment-slip {
                max-width: 100%;
                margin-bottom: 0;
            }
            .no-print {
                display: none;
            }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px 16px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            z-index: 1000;
        }
        .print-button:hover {
            background-color: #000;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Print All Slips</button>

    @foreach($salaryPayments as $salaryPayment)
        <div class="payment-slip">
            <div class="watermark">CONFIDENTIAL</div>
            
            <!-- Header -->
            <div class="header">
                <div class="govt-seal">SEAL</div>
                <h1>{{ $salaryPayment->section->section_name ?? 'FEDERAL REPUBLIC OF NIGERIA' }}</h1>
                <h2>NAME OF SCHOOL</h2>
                <p>MONTHLY SALARY PAYMENT ADVICE</p>
            </div>

            <div class="slip-number">
                Slip No: {{ $salaryPayment->id ?? 'XXX' }}/{{ $salaryPayment->year }}/{{ str_pad($salaryPayment->month, 2, '0', STR_PAD_LEFT) }}
            </div>

            <!-- Employee Information -->
            <div class="section-title">Employee Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ strtoupper($salaryPayment->employee->name ?? 'N/A') }}</span>
                </div>
             
                <div class="info-item">
                    <span class="info-label">Section:</span>
                    <span class="info-value">{{ $salaryPayment->section->section_name ?? 'N/A' }}</span>
                </div>
               
                <div class="info-item">
                    <span class="info-label">Pay Period:</span>
                    <span class="info-value">{{ date('F Y', mktime(0, 0, 0, $salaryPayment->month, 10, $salaryPayment->year)) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Session:</span>
                    <span class="info-value">{{ $salaryPayment->term->name ?? '' }} {{ $salaryPayment->session->name ?? '' }}</span>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="section-title">Payment Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Bank Name:</span>
                    <span class="info-value">{{ $salaryPayment->bank_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Account Number:</span>
                    <span class="info-value">{{ $salaryPayment->account_number }}</span>
                </div>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <span class="info-label">Remarks:</span>
                    <span class="info-value">{{ $salaryPayment->description }}</span>
                </div>
            </div>

            <!-- Salary Breakdown -->
            <div class="section-title">Salary Breakdown</div>
            <table class="salary-table">
                <thead>
                    <tr>
                        <th style="width: 70%">Particulars</th>
                        <th class="text-right">Amount (₦)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Basic Salary</td>
                        <td class="text-right">{{ number_format($salaryPayment->basic_salary, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Allowances</td>
                        <td class="text-right">{{ number_format($salaryPayment->allowances, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Gross Salary</td>
                        <td class="text-right">{{ number_format($salaryPayment->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Deductions</td>
                        <td class="text-right">({{ number_format($salaryPayment->deductions, 2) }})</td>
                    </tr>
                    <tr class="net-pay-row">
                        <td>NET SALARY PAYABLE</td>
                        <td class="text-right">{{ number_format($salaryPayment->net_pay, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">Employee's Signature</div>
                    <p style="margin-top: 3px; color: #666;">Date: __________</p>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Accounts Officer</div>
                    <p style="margin-top: 3px; color: #666;">Date: __________</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p><strong>OFFICIAL DOCUMENT - NOT NEGOTIABLE</strong></p>
                <p>Processed by: Finance Department  | Processed on: {{ $salaryPayment->processed_at ? $salaryPayment->processed_at->format('d/m/Y H:i') : date('d/m/Y') }}</p>
                <p>This is a computer-generated document. For queries contact: Accounts Department</p>
            </div>
        </div>
    @endforeach
</body>
</html>