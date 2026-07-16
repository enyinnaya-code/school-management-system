<!DOCTYPE html>
<html lang="en">
<head>
    @include('includes.head')
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #f2f2f2;
        }

        .print-toolbar {
            padding: 15px;
            text-align: center;
            background: #fff;
            border-bottom: 1px solid #ddd;
        }
        .print-toolbar button {
            padding: 8px 20px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            color: #fff;
            background: #28a745;
        }
        .print-toolbar button.secondary {
            background: #6c757d;
        }

        .print-page-wrapper {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .print-page {
            width: 100%;
            max-width: 210mm;
            background: #fff;
            padding: 20px;
        }

        @media print {
            .print-toolbar {
                display: none !important;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }
            .print-page-wrapper {
                padding: 0 !important;
                display: block !important;
            }
            .print-page {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 auto !important;
            }
            @page {
                size: A4;
                margin: 15mm;
            }
            .container {
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            .cumulative-page {
                page-break-before: always;
                page-break-inside: avoid;
                margin-top: 0 !important;
            }
            table {
                page-break-inside: avoid;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        <button class="secondary" onclick="window.close()">Close</button>
    </div>

    <div class="print-page-wrapper">
        <div class="print-page">
            @yield('content')
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>