<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet" />
    <title>تقرير أداء الفنادق</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding: 20px;
            direction: rtl;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        th {
            background-color: #f8f9fa;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .date {
            text-align: left;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>تقرير أداء الفنادق</h2>
        <p class="date">التاريخ: {{ $date }}</p>
    </div>

    <div class="summary">
        <h3>ملخص التقرير</h3>
        <p>إجمالي الفنادق: {{ $summary['total_hotels'] }}</p>
        <p>إجمالي العقود: {{ $summary['total_contracts'] }}</p>
        <p>متوسط التقييم: {{ number_format($summary['average_rating'], 1) }} نجوم</p>
        <p>إجمالي المصروفات: {{ number_format($summary['total_spent'], 2) }} ريال</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>اسم الفندق</th>
                <th>التقييم</th>
                <th>التقييم المتوسط</th>
                <th>الخدمات الفرعية</th>
                <th>عدد العقود</th>
                <th>إجمالي المصروفات</th>
                <th>معلومات التواصل</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hotels as $hotel)
                <tr>
                    <td>{{ $hotel['name'] }}</td>
                    <td>{{ $hotel['stars'] }} نجوم</td>
                    <td>{{ $hotel['average_rating'] ?: 0 }}</td>
                    <td>{{ is_array($hotel['sub_services']) ? implode(', ', $hotel['sub_services']) : '' }}</td>
                    <td>{{ $hotel['contracts_count'] }}</td>
                    <td>{{ number_format($hotel['total_spent'], 2) }} ريال</td>
                    <td>
                        {{ $hotel['email'] }}<br>
                        {{ $hotel['phone'] }}<br>
                        {{ $hotel['address'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
