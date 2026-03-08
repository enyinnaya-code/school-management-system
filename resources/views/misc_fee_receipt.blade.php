{{-- resources/views/misc_fee_receipt.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $payment->receipt_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            width: 76mm;
            margin: 0 auto;
            padding: 3mm 2mm;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            display: block;
            margin: 0 auto 2px;
        }

        .school-name {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .school-address {
            font-size: 7pt;
            margin-top: 1px;
        }

        /* ── Dividers ── */
        .dashed  { border-top: 1px dashed #000; margin: 3px 0; }
        .solid   { border-top: 1px solid  #000; margin: 3px 0; }
        .double  { border-top: 3px double #000; margin: 3px 0; }

        /* ── Receipt title ── */
        .receipt-title {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 2px;
            padding: 2px 0;
        }

        /* ── Two-column rows ── */
        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2px;
            font-size: 7.5pt;
        }

        .row .lbl {
            font-weight: bold;
            flex: 0 0 42%;
        }

        .row .val {
            flex: 0 0 56%;
            text-align: right;
            word-break: break-word;
        }

        /* ── Full-width label + value ── */
        .block-lbl {
            font-weight: bold;
            font-size: 7.5pt;
        }

        .block-val {
            font-size: 7.5pt;
            padding-left: 6px;
            margin-bottom: 2px;
        }

        /* ── Amount box ── */
        .amount-box {
            border: 2px solid #000;
            text-align: center;
            padding: 4px 2px;
            margin: 4px 0;
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* ── Summary table ── */
        .summary {
            border: 1px solid #000;
            padding: 3px 4px;
            margin: 3px 0;
            font-size: 7.5pt;
        }

        .summary .row {
            margin-bottom: 1px;
        }

        .balance-row {
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
            font-weight: bold;
            border-top: 1px dashed #000;
            padding-top: 2px;
            margin-top: 2px;
        }

        /* ── Cashier line ── */
        .cashier-line {
            font-size: 7pt;
            margin-top: 3px;
            text-align: center;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            font-size: 7pt;
            margin-top: 5px;
            line-height: 1.4;
        }

        .footer .thank-you {
            font-size: 8pt;
            font-weight: bold;
        }

        .footer .note {
            font-style: italic;
        }

        .footer .timestamp {
            font-size: 6.5pt;
            margin-top: 2px;
            color: #333;
        }

        @media print {
            html, body { width: 80mm; }
            .no-print  { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- ── HEADER ── --}}
    <div class="header">
        {{-- @if(school_logo())
            <img src="{{ school_logo() }}" alt="Logo" class="logo">
        @endif --}}
        <div class="school-name">{{ school_name() ?? 'SCHOOL NAME' }}</div>
        {{-- @if(school_address())
            <div class="school-address">{{ school_address() }}</div>
        @endif --}}
        {{-- @if(school_phone())
            <div class="school-address">Tel: {{ school_phone() }}</div>
        @endif --}}
    </div>

    <div class="dashed"></div>
    <div class="receipt-title">MISC FEE RECEIPT</div>
    <div class="dashed"></div>

    {{-- ── RECEIPT META ── --}}
    <div class="row">
        <span class="lbl">Receipt #:</span>
        <span class="val">{{ $payment->receipt_number }}</span>
    </div>
    <div class="row">
        <span class="lbl">Date:</span>
        <span class="val">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</span>
    </div>
    <div class="row">
        <span class="lbl">Time:</span>
        <span class="val">{{ $payment->created_at->format('h:i A') }}</span>
    </div>

    <div class="dashed"></div>

    {{-- ── STUDENT INFO ── --}}
    <div class="block-lbl">Student:</div>
    <div class="block-val">{{ $student->name }}</div>

    <div class="row">
        <span class="lbl">Adm. No:</span>
        <span class="val">{{ $student->admission_no ?? 'N/A' }}</span>
    </div>
    <div class="row">
        <span class="lbl">Class:</span>
        <span class="val">{{ $class->name ?? 'N/A' }}</span>
    </div>
    <div class="row">
        <span class="lbl">Section:</span>
        <span class="val">{{ $section->section_name ?? 'N/A' }}</span>
    </div>

    <div class="dashed"></div>

    {{-- ── SESSION / TERM ── --}}
    <div class="row">
        <span class="lbl">Session:</span>
        <span class="val">{{ $session->name ?? 'N/A' }}</span>
    </div>
    <div class="row">
        <span class="lbl">Term:</span>
        <span class="val">{{ $currentTerm->name ?? 'N/A' }}</span>
    </div>

    <div class="dashed"></div>

    {{-- ── FEE DETAILS ── --}}
    <div class="row">
        <span class="lbl">Fee Type:</span>
        <span class="val">{{ $payment->miscFeeType->name ?? 'N/A' }}</span>
    </div>
    <div class="row">
        <span class="lbl">Status:</span>
        <span class="val">{{ ucfirst($payment->status) }}</span>
    </div>

    <div class="solid"></div>

    {{-- ── AMOUNT BOX ── --}}
    <div class="amount-box">
        AMT PAID: &#x20A6;{{ number_format($payment->amount_paid, 2) }}
    </div>

    {{-- ── SUMMARY ── --}}
    <div class="summary">
        <div class="row">
            <span class="lbl">Total Due:</span>
            <span class="val">&#x20A6;{{ number_format($totalDue, 2) }}</span>
        </div>
        <div class="row">
            <span class="lbl">Amt Paid:</span>
            <span class="val">&#x20A6;{{ number_format($payment->amount_paid, 2) }}</span>
        </div>
        <div class="balance-row">
            <span>Balance:</span>
            <span>&#x20A6;{{ number_format($balance, 2) }}</span>
        </div>
        @if($session && $currentTerm)
        <div style="font-size:6.5pt; text-align:center; margin-top:2px; color:#333;">
            {{ $currentTerm->name }}, {{ $session->name }}
        </div>
        @endif
    </div>

    {{-- ── CASHIER ── --}}
    @if($payment->paidBy)
    <div class="cashier-line">
        Received by: {{ $payment->paidBy->name }}
    </div>
    @endif

    <div class="double"></div>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        <div class="thank-you">Thank you!</div>
        <div class="note">Keep this receipt for your records.</div>
        <div class="note">Computer-generated — no signature required.</div>
        <div class="timestamp">{{ now()->format('d/m/Y h:i A') }}</div>
    </div>

</body>
</html>