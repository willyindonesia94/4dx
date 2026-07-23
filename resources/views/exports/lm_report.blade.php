<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan LM</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        .bg-blue { background-color: #1f497d; color: #ffffff; }
        .bg-orange { background-color: #e26b0a; color: #ffffff; }
        .bg-green { background-color: #385d22; color: #ffffff; }
        .bg-purple { background-color: #60497a; color: #ffffff; }
        .bg-gray { background-color: #bfbfbf; }
        .bg-light-purple { background-color: #e4dfec; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    @include('exports.lm_report_table')
</body>
</html>
