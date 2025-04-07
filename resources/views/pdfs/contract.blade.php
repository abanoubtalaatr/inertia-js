<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet" />
    <title>عقد خدمات</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 30px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #1a237e;
            font-size: 24px;
            margin: 0;
        }

        .contract-info {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-item {
            margin-bottom: 10px;
        }

        .info-label {
            color: #1a237e;
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th {
            background-color: #1a237e;
            color: white;
            padding: 12px;
            text-align: right;
            font-weight: bold;
        }

        td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .totals {
            margin-top: 30px;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .grand-total {
            border-top: 2px solid #1a237e;
            margin-top: 10px;
            padding-top: 10px;
            font-weight: bold;
            color: #1a237e;
        }

        .bank-info {
            margin-top: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .bank-info h3 {
            color: #1a237e;
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .signature {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .signature-box {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
        }

        .qr-code {
            text-align: left;
            margin-top: 30px;
        }
    </style>
</head>

<body dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
    style="font-family: 'Cairo', sans-serif; margin: 20px; padding: 0; color: #333;">
    <div class="logo">
        <img src="{{ public_path('/dashboard-assets/img/logos/logo.svg') }}" height="60" alt="Logo">
    </div>

    <div class="header">
        <h1>عقد خدمات</h1>
        <p>رقم العقد: {{ $contract->contract_number }}</p>
    </div>

    <div class="contract-info">
        <div class="info-item">
            <span class="info-label">الفندق:</span>
            <span>{{ $contract->hotel->name }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">تاريخ البداية:</span>
            <span>{{ $contract->start_date->format('Y-m-d') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">تاريخ الانتهاء:</span>
            <span>{{ $contract->end_date->format('Y-m-d') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">المستوى:</span>
            <span>{{ $contract->level }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="40%">الخدمة</th>
                <th width="35%">المزود</th>
                <th width="20%">السعر</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contract->services as $index => $service)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ optional($service->service)->name ?? 'غير متوفر' }}</td>
                    <td>{{ optional($service->provider)->name ?? 'غير متوفر' }}</td>
                    <td>{{ number_format($service->price ?? 0, 2) }} ريال</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-item">
            <span>إجمالي الخدمات:</span>
            <span>{{ number_format($contract->total_amount ?? 0, 2) }} ريال</span>
        </div>
        <div class="total-item">
            <span>العمولة:</span>
            <span>{{ number_format($contract->commission ?? 0, 2) }} ريال</span>
        </div>
        <div class="total-item">
            <span>ضريبة القيمة المضافة:</span>
            <span>{{ number_format($contract->vat ?? 0, 2) }} ريال</span>
        </div>
        <div class="total-item grand-total">
            <span>الإجمالي النهائي:</span>
            <span>{{ number_format($contract->grand_total ?? 0, 2) }} ريال</span>
        </div>
    </div>

    @if ($contract->bank)
        <div class="bank-info">
            <h3>معلومات الحساب البنكي</h3>
            <div class="info-item">
                <span class="info-label">البنك:</span>
                <span>{{ $contract->bank->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">صاحب الحساب:</span>
                <span>{{ $contract->bank_account_holder_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">رقم الحساب:</span>
                <span>{{ $contract->bank_account_number }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">الآيبان:</span>
                <span>{{ $contract->iban }}</span>
            </div>
        </div>
    @endif

    <div class="signature">
        <div class="signature-box">
            <p>توقيع الفندق</p>
            <div style="margin-top: 40px;">_________________</div>
        </div>

    </div>


</body>

</html>
