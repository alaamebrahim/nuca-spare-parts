<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', config('app.name'))</title>
    <style>
        @media screen {
            html {
                background: #f1f1f1;
            }

            .page-container {
                min-height: calc(297mm - 35px);
            }
        }

        .page {
            max-width: 1080px;
            margin: 0 auto;
            height: 100%;
        }

        .header {
            width: 90%;
            margin: 0 auto 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .page-container {
            background: #ffffff;
            border-radius: 5px;
            padding: 15px;
            width: 100%;
        }

        table {
            border-spacing: 0;
            text-align: center;
            width: 92%;
            border: 1px solid #888888 !important;
            margin: 0 auto;
            font-weight: bold;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .body table th,
        .body table td {
            border: 1px solid #888888 !important;
            padding: 5px;
            word-wrap: break-word;
        }

        .body table th {
            background: #f5f5f5;
        }

        .print-btn-container {
            width: 200px;
            padding: 10px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .print-btn {
            background: #e2e8f0;
            padding: 8px 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 1cm 0.5cm;
            }

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
                padding: 5px !important;
                width: 100% !important;
            }

            .print-btn-container {
                display: none;
            }

            table {
                width: 100% !important;
                font-size: 9pt;
            }

            td, th {
                font-size: 8pt;
                padding: 3px !important;
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
</div>
<div class="page">
    <div class="page-container">
        <div class="header">
            <div style="line-height: 1.4em; text-align: center;">
                <h4 style="margin: 4px 0;">وزارة الاسكان والمرافق والمجتمعات العمرانية الجديدة</h4>
                <h4 style="margin: 4px 0;">هيئة المجتمعات العمرانية الجديدة</h4>
            </div>

            <div style="flex-grow: 2; text-align: center;">
                <h3 style="margin: 8px 0;">@yield('report_title')</h3>
                <h6 style="color: #333333; margin: 4px 0;">وقت الطباعة<br>{{ now()->format('Y-m-d H:i') }}</h6>
                @yield('report_head')
            </div>

            <div style="text-align: center;">
                <img src="{{ asset('img/logo.png') }}" alt="الشعار" style="width: 100px;"/>
            </div>
        </div>
        <div class="body">
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
