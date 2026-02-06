{{-- resources/views/payment_receipt.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $student->admission_no }}</title>
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
            font-family: 'Courier New', 'Arial', monospace;
            font-size: 9px;
            width: 74mm;
            margin: 0 auto;
            padding: 3mm;
            line-height: 1.2;
            color: #000;
            font-weight: bold;
        }
        
        .receipt-container {
            width: 100%;
        }
        
        /* Header with Logo and School Name */
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        
        .logo-container {
            margin-bottom: 2px;
        }
        
        .logo {
            width: 35px;
            height: 35px;
            object-fit: contain;
            display: inline-block;
        }
        
        .school-name {
            font-size: 11px;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .school-subtitle {
            font-size: 8px;
            color: #333;
            margin-top: 1px;
        }
        
        /* Receipt Title */
        .receipt-title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin: 4px 0;
            letter-spacing: 1px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
        }
        
        /* Content Section */
        .content {
            margin: 4px 0;
        }
        
        .info-row {
            width: 100%;
            margin-bottom: 4px;
            overflow: hidden;
            clear: both;
        }
        
        .label {
            font-weight: bold;
            float: left;
            width: 42%;
            clear: left;
        }
        
        .value {
            float: right;
            width: 56%;
            text-align: right;
            word-wrap: break-word;
            clear: right;
        }
        
        .full-width {
            width: 100%;
            margin-bottom: 4px;
            clear: both;
        }
        
        .full-width .label {
            width: 100%;
            margin-bottom: 2px;
            float: none;
        }
        
        .full-width .value {
            width: 100%;
            text-align: left;
            padding-left: 8px;
            float: none;
        }
        
        /* Separator Lines */
        .separator {
            border-bottom: 1px dashed #000;
            margin: 4px 0;
            clear: both;
        }
        
        .separator-solid {
            border-bottom: 1px solid #000;
            margin: 4px 0;
            clear: both;
        }
        
        /* Payment Summary */
        .payment-summary {
            border: 1px solid #000;
            padding: 4px;
            margin: 4px 0;
            clear: both;
        }
        
        .amount-paid {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin: 4px 0;
            padding: 4px;
            border: 2px solid #000;
            clear: both;
        }
        
        .balance-info {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px dashed #000;
            clear: both;
        }
        
        /* Footer */
        .footer {
            margin-top: 6px;
            text-align: center;
            font-size: 8px;
            padding-top: 4px;
            border-top: 1px solid #000;
            clear: both;
        }
        
        .footer-note {
            margin: 2px 0;
            font-style: italic;
        }
        
        .timestamp {
            font-size: 7px;
            color: #666;
            margin-top: 3px;
        }
        
        /* Highlight for payment term if different from current */
        .payment-term-highlight {
            background-color: #f0f0f0;
            padding: 3px;
            margin: 3px 0;
            border: 1px solid #ccc;
        }
        
        /* Print Specific */
        @media print {
            body {
                padding: 2mm;
            }
            
            .no-print {
                display: none !important;
            }
            
            .payment-term-highlight {
                background-color: #e0e0e0;
            }
        }
        
        /* Utilities */
        .text-center {
            text-align: center;
        }
        
        .text-bold {
            font-weight: bold;
        }
        
        .mt-1 {
            margin-top: 3px;
        }
        
        .mb-1 {
            margin-bottom: 3px;
        }
        
        /* Clear fix utility */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header with Logo and School Info -->
        <div class="header">
            @if(school_logo())
            <div class="logo-container">
                <img src="{{ school_logo() }}" alt="{{ school_name() ?? 'School Logo' }}" class="logo">
            </div>
            @endif
            <div class="school-name">{{ school_name() ?? 'SCHOOL NAME' }}</div>
            <div class="school-subtitle">Fee Payment Receipt</div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">PAYMENT RECEIPT</div>

        <!-- Receipt Details -->
        <div class="content">
            <div class="info-row">
                <span class="label">Receipt No:</span>
                <span class="value">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
                <div style="clear:both;"></div>
            </div>
            
            <div class="info-row">
                <span class="label">Date:</span>
                <span class="value">{{ $payment->created_at->format('d/m/Y') }}</span>
                <div style="clear:both;"></div>
            </div>
            
            <div class="info-row">
                <span class="label">Time:</span>
                <span class="value">{{ $payment->created_at->format('h:i A') }}</span>
                <div style="clear:both;"></div>
            </div>
        </div>

        <div class="separator"></div>

        <!-- Student Information -->
        <div class="content">
            <div class="full-width">
                <div class="label">Student Name:</div>
                <div class="value">{{ $student->name }}</div>
            </div>
            
            <div class="info-row">
                <span class="label">Admission No:</span>
                <span class="value">{{ $student->admission_no }}</span>
                <div style="clear:both;"></div>
            </div>
            
            <div class="info-row">
                <span class="label">Class:</span>
                <span class="value">{{ $class->name }}</span>
                <div style="clear:both;"></div>
            </div>
            
            <div class="info-row">
                <span class="label">Section:</span>
                <span class="value">{{ $section->section_name }}</span>
                <div style="clear:both;"></div>
            </div>
        </div>

        <div class="separator"></div>

        <!-- Session/Term Information - UPDATED SECTION -->
        <div class="content">
            {{-- Show the term this payment is FOR (from the payment record) --}}
            @php
                $paymentTerm = $payment->term;
                $paymentSession = $payment->session;
                $isCurrentTerm = ($payment->term_id == $currentTerm->id && $payment->session_id == $session->id);
            @endphp
            
            <div class="info-row">
                <span class="label">Session:</span>
                <span class="value">{{ $paymentSession->name }}</span>
                <div style="clear:both;"></div>
            </div>
            
            <div class="info-row">
                <span class="label">Term:</span>
                <span class="value">{{ $paymentTerm->name }}</span>
                <div style="clear:both;"></div>
            </div>
            
            {{-- Indicate if payment is for a previous term --}}
            @if(!$isCurrentTerm)
            <div class="payment-term-highlight">
                <div style="text-align: center; font-size: 8px;">
                    ** PAYMENT FOR PREVIOUS TERM **
                </div>
            </div>
            @endif
        </div>

        <div class="separator-solid"></div>

        <!-- Payment Amount - Highlighted -->
        <div class="amount-paid">
            AMOUNT PAID: N{{ number_format($payment->amount, 2) }}
        </div>

        <!-- Payment Details -->
        <div class="content">
            <div class="info-row">
                <span class="label">Payment Type:</span>
                <span class="value">{{ $payment->payment_type }}</span>
                <div style="clear:both;"></div>
            </div>
            
            @if($payment->description)
            <div class="full-width mt-1">
                <div class="label">Description:</div>
                <div class="value">{{ $payment->description }}</div>
            </div>
            @endif
        </div>

        <!-- Payment Summary Box - UPDATED SECTION -->
        <div class="payment-summary">
            <div class="info-row">
                <span class="label">Total Due:</span>
                <span class="value">N{{ number_format($totalDue, 2) }}</span>
                <div style="clear:both;"></div>
            </div>
            
            <div class="info-row">
                <span class="label">Amount Paid:</span>
                <span class="value">N{{ number_format($totalDue - $balance, 2) }}</span>
                <div style="clear:both;"></div>
            </div>
            
            <div class="balance-info">
                <div class="info-row">
                    <span class="label">Balance:</span>
                    <span class="value text-bold">N{{ number_format($balance, 2) }}</span>
                    <div style="clear:both;"></div>
                </div>
            </div>
            
            {{-- Show note about term balance --}}
            <div style="margin-top: 3px; padding-top: 3px; border-top: 1px dashed #000;">
                <div style="font-size: 7px; text-align: center;">
                    Balance for {{ $paymentTerm->name }}, {{ $paymentSession->name }}
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="separator"></div>
            <div class="footer-note">Thank you for your payment!</div>
            <div class="footer-note">This is a computer-generated receipt</div>
            <div class="timestamp">
                Printed: {{ now()->format('d/m/Y h:i A') }}
            </div>
        </div>
    </div>

    <!-- Auto-print script -->
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>