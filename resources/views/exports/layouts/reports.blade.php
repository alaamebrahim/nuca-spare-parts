<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', config('app.name'))</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5cm 0.75cm;
        }

        @media screen {
            html {
                background: #f1f1f1;
            }

            .page-container {
                min-height: calc(210mm - 35px);
            }
        }

        .page {
            max-width: 297mm;
            width: 100%;
            margin: 0 auto;
        }

        .header {
            width: 96%;
            margin: 0 auto 12px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .page-container {
            background: #ffffff;
            border-radius: 5px;
            padding: 12px 8px;
            width: 100%;
            box-sizing: border-box;
        }

        .report-table {
            border-spacing: 0;
            text-align: center;
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 10px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #888888;
            padding: 4px 3px;
            word-wrap: break-word;
            overflow-wrap: anywhere;
            vertical-align: top;
            line-height: 1.35;
        }

        .report-table th {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 9px;
        }

        .report-table td {
            font-weight: normal;
        }

        .print-btn-container {
            width: 280px;
            padding: 10px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .print-btn {
            background: #e2e8f0;
            padding: 8px 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .print-hint {
            font-size: 12px;
            color: #555;
            text-align: center;
        }

        @media print {
            html, body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .page {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
            }

            .page-container {
                padding: 0 !important;
                width: 100% !important;
                border-radius: 0;
            }

            .header {
                width: 100%;
                margin-bottom: 8px;
            }

            .header h3 {
                font-size: 14pt;
                margin: 4px 0;
            }

            .header h4 {
                font-size: 10pt;
                margin: 2px 0;
            }

            .header h6 {
                font-size: 8pt;
            }

            .header img {
                width: 70px !important;
            }

            .print-btn-container {
                display: none;
            }

            .report-table {
                width: 100% !important;
                font-size: 7pt;
            }

            .report-table th {
                font-size: 7pt;
            }

            .report-table td,
            .report-table th {
                padding: 2px !important;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
    @yield('extra_styles')
</head>
<body>
<div class="print-btn-container">
    <button onclick="window.print()" class="print-btn">طباعة / حفظ PDF</button>
    <span class="print-hint">اختر الاتجاه الأفقي (Landscape) في إعدادات الطباعة</span>
</div>
<div class="page">
    <div class="page-container">
        <div class="header">
            <div style="line-height: 1.3em; text-align: center; flex: 1;">
                <h4 style="margin: 2px 0;">وزارة الاسكان والمرافق والمجتمعات العمرانية الجديدة</h4>
                <h4 style="margin: 2px 0;">هيئة المجتمعات العمرانية الجديدة</h4>
            </div>

            <div style="flex: 2; text-align: center;">
                <h3 style="margin: 6px 0;">@yield('report_title')</h3>
                <h6 style="color: #333333; margin: 2px 0;">وقت الطباعة<br>{{ now()->format('Y-m-d H:i') }}</h6>
                @yield('report_head')
            </div>

            <div style="text-align: center; flex: 1;">
                <img src="{{ asset('img/logo.png') }}" alt="الشعار" style="width: 80px;"/>
            </div>
        </div>
        <div class="body">
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
