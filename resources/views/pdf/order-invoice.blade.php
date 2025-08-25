<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة رقم {{ $order->id }}</title>
    <style>
        /* إعدادات الخطوط ودعم العربية */
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url('{{ public_path('dashboard_files/fonts/Cairo-VariableFont_slnt,wght.ttf') }}') format('truetype');
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #2c3e50;
        }

        /* حاوية الفاتورة */
        .invoice-container {
            width: 95%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #f9f9f9;
        }

        /* رأس الفاتورة */
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .invoice-header img {
            width: 100px;
            margin-bottom: 10px;
        }

        .invoice-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .invoice-header h4 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #555;
        }

        .invoice-header p {
            font-size: 14px;
            margin: 3px 0;
        }

        /* الجدول */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: center;
        }

        table th {
            background: #2980b9;
            color: white;
            font-size: 14px;
        }

        table td {
            font-size: 13px;
        }

        /* الإجمالي */
        .total {
            text-align: left;
            font-size: 16px;
            font-weight: bold;
            margin-top: 15px;
        }

        .total span {
            color: #e74c3c;
        }

        /* الملاحظات */
        .notes {
            background: #ecf0f1;
            border-right: 5px solid #3498db;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 13px;
        }

        /* التوقيع */
        .signature {
            margin-top: 30px;
        }

        .signature p {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .signature-box {
            width: 200px;
            height: 50px;
            border: 2px dashed #7f8c8d;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <!-- رأس الفاتورة -->
    <div class="invoice-header">
        <img src="{{ public_path('dashboard_files/img/logoatabi.jpg') }}"
     alt="الشعار"
     width="200"
     height="auto">
        <h2>أبو الطاهر</h2>
        <h4>فاتورة مبيعات</h4>
        <p>التاريخ: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</p>
        <p>رقم الإيصال: {{ $order->order_number }}</p>
       <p>العميل: {{ $order->client->name  }}</p>
    </div>

    <!-- جدول المنتجات -->
    <table>
        <thead>
            <tr>
                <th>اسم الصنف</th>
                <th>الكمية</th>
                <th>السعر</th>
                  <th>المجموعة</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->pivot->quantity }}</td>
                     <td>{{ number_format($product->sale_price, 2) }}</td>
                    <td>{{ number_format($product->pivot->quantity * $product->sale_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- الإجمالي -->
    <div class="total">
        جملة المبلغ: <span>{{ number_format($order->total_price, 2) }} جنية </span>
    </div>

    <!-- الملاحظات -->
    <div class="notes">
        <p><strong>ملاحظة:</strong> يرجى التأكد من فحص البضاعة جيدًا قبل المغادرة. لا تقبل المرتجعات بعد 24 ساعة من الاستلام.</p>
    </div>

    <!-- التوقيع -->
    <div class="signature">
        <p>توقيع المستلم:</p>
        <div class="signature-box"></div>
    </div>
</div>

</body>
</html>
