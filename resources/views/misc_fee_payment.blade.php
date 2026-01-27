<!DOCTYPE html>
<html>
<head>
    <title>Fee Receipt - {{ $payment->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: #fff;
            font-size: 12px;
            line-height: 1.4;
            font-weight: bold;
        }
        
        .receipt-container {
            width: 100%;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            margin: 0 auto 8px;
        }
        
        .school-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        .school-address {
            font-size: 9px;
            margin-bottom: 5px;
            line-height: 1.3;
        }
        
        .receipt-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
            letter-spacing: 1px;
        }
        
        .receipt-number {
            font-size: 11px;
            margin-top: 5px;
            font-weight: bold;
        }
        
        .details-section {
            margin: 10px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .detail-row {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .detail-label {
            font-weight: bold;
            display: inline-block;
        }
        
        .detail-value {
            display: inline;
        }
        
        .amount-section {
            text-align: center;
            margin: 12px 0;
            padding: 10px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        
        .amount-label {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .amount-value {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .footer {
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .signature-section {
            margin-top: 20px;
            font-size: 10px;
        }
        
        .signature-box {
            text-align: center;
            margin: 15px 0 8px 0;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 60%;
            margin: 0 auto 3px auto;
        }
        
        .signature-label {
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .print-date {
            text-align: center;
            font-size: 9px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #000;
        }
        
        .thank-you {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        @media print {
            body {
                padding: 0;
                width: 80mm;
            }
            
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('images/school-logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="school-name">{{ config('app.school_name', 'SCHOOL NAME') }}</div>
            <div class="school-address">
                {{ config('app.school_address', 'School Address, City, State') }}<br>
                Tel: {{ config('app.school_phone', '000-000-0000') }}
            </div>
            <div class="receipt-title">FEE RECEIPT</div>
            <div class="receipt-number">No: {{ $payment->receipt_number }}</div>
        </div>
        
        <div class="details-section">
            <div class="detail-row">
                <span class="detail-label">Student:</span>
                <span class="detail-value">{{ $payment->student->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Adm No:</span>
                <span class="detail-value">{{ $payment->student->admission_no }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Fee Type:</span>
                <span class="detail-value">{{ $payment->miscFeeType->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ $payment->payment_date->format('d/m/Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Method:</span>
                <span class="detail-value">{{ $payment->payment_method ?? 'Cash' }}</span>
            </div>
            
            @if($payment->paidBy)
            <div class="detail-row">
                <span class="detail-label">Received From:</span>
                <span class="detail-value">{{ $payment->paidBy->name }}</span>
            </div>
            @endif
        </div>
        
        <div class="amount-section">
            <div class="amount-label">AMOUNT PAID</div>
            <div class="amount-value">N{{ number_format($payment->amount_paid, 2) }}</div>
        </div>
        
        <div class="footer">
            
            <div class="print-date">
                {{ now()->format('d/m/Y h:i A') }}
            </div>
        </div>
    </div>
</body>
</html>