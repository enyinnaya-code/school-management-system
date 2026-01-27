{{-- resources/views/pdf/fee_prospectus.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Prospectus</title>
    <style>
        @page {
            margin: 20mm 15mm;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 25px;
            padding-bottom: 0px;
          
        }
        
        
        .school-info p { 
            margin: 2px 0;
            font-size: 1rem;
        }
        
        .document-title {
            text-align: center;
            font-size: 1rem;
            font-weight: bold;
            /* margin: 20px 0 25px; */
            text-transform: uppercase;
            letter-spacing: 2px;
            /* border-bottom: 1px solid #000; */
            padding-bottom: 8px;
        }
        
        .prospectus-details { 
            margin-bottom: 2px;
            line-height: 1;
        }
        
        .prospectus-details table {
            width: auto;
            max-width: min-content;
            border: none;
            margin: 0;
            border-collapse: collapse;
        }
        
        .prospectus-details td {
            border: none;
            padding: 6px 4px 6px 0;
            font-size: 1rem;
            white-space: nowrap;
        }
        
        .prospectus-details td:first-child {
            font-weight: bold;
            padding-right: 20px;
        }
        
        .prospectus-details td:last-child {
            font-weight: normal;
            padding-left: 10px;
        }
        
        .fee-table { 
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0 20px;
            table-layout: auto;
        }
        
        .fee-table th, 
        .fee-table td { 
            border: 1px solid #000;
            padding: 8px 15px;
            text-align: left;
            vertical-align: middle;
        }
        
        .fee-table th { 
            background-color: #fff;
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
        }
        
        .fee-table td {
            font-size: 11pt;
        }
        
        .amount-column {
            text-align: right;
            font-family: 'Times New Roman', Times, serif;
            font-weight: normal;
            width: 30%;
        }
        
        .item-column {
            width: 70%;
        }
        
        .total-row td {
            background-color: #fff;
            font-weight: bold;
            font-size: 12pt;
            border-top: 2px solid #000;
            padding: 12px;
        }
        
        .total-amount { 
            font-size: 1rem;
            font-weight: bold;
            text-align: right;
           
            padding: 2px 30px;
          
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 9pt;
            color: #000;
            font-style: italic;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        /* Print Specific */
        @media print {
            @page {
                size: A4 portrait;
                margin: 20mm 15mm;
            }
            
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Logo and School Info -->
    <div class="header" style="margin-top: 2rem">
        {{-- @if(school_logo())
        <div class="logo-container">
            <img src="{{ school_logo() }}" alt="{{ school_name() ?? 'School Logo' }}" class="logo">
        </div>
        @endif --}}
        <div class="school-info">
            <h3>{{ school_name() ?? 'SCHOOL NAME' }}</h3>
        </div>
    </div>

    <!-- Document Title -->
    <h4 class="document-title">Fee Prospectus</h4>

    <!-- Prospectus Details -->
    <div class="prospectus-details" style="padding: 0 2rem">
        <table>
            <tr>
                <td>Section/Arm:</td>
                <td>{{ $prospectus->section->section_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Class:</td>
                <td>{{ $prospectus->schoolClass->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Term:</td>
                <td>{{ $prospectus->term->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Academic Session:</td>
                <td>{{ $prospectus->term->session->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Fee Items Table -->
    <table class="fee-table" style="padding: 0 2rem">
        <thead>
            <tr>
                <th class="item-column">Fee Item</th>
                <th class="amount-column">Amount (#)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prospectus->items as $item)
            <tr>
                <td class="item-column">{{ $item['item'] }}</td>
                <td class="amount-column">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td class="item-column" style="text-align: right;">TOTAL</td>
                <td class="amount-column">{{ number_format($prospectus->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Total Amount Highlight -->
    <div class="total-amount">
        TOTAL AMOUNT: #{{ number_format($prospectus->total_amount, 2) }}
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document and requires no signature.</p>
        <p>Generated on: {{ now()->format('d/m/Y h:i A') }}</p>
    </div>
</body>
</html>