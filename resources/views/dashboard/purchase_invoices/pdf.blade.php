<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة شراء رقم {{ $purchaseInvoice->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #2c3e50;
        }

        .invoice-box {
            max-width: 1000px;
            margin: auto;
            padding: 25px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px #ccc;
            background: #fdfdfd;
        }

        /* جداول عامة */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* جدول الهيدر */
        .header-table td {
            vertical-align: top;
            padding: 5px 10px;
            border: none; /* شفاف */
        }

        .company-info {
            text-align: right;
            font-size: 15px;
        }

        .invoice-info {
            text-align: left;
            font-size: 15px;
        }

        /* جدول المنتجات */
        .items th, .items td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .items th {
            background: #3498db;
            color: #fff;
        }

        /* الإجماليات */
        .summary {
            margin-top: 20px;
            width: 50%;
            float: left;
            font-size: 14px;
        }

        /* الملاحظات */
        .notes {
            clear: both;
            margin-top: 40px;
            font-size: 13px;
            background: #fcfcfc;
            border-right: 5px solid #2980b9;
            padding: 10px;
            border-radius: 4px;
        }

        /* جدول التوقيع */
        .signature-table {
            margin-top: 60px;
            border-top: 1px solid #000;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding-top: 30px; /* مساحة للتوقيع اليدوي */
            border: none; /* شفاف */
        }

        @media print {
            .invoice-box {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

<div class="invoice-box">

    <!-- الهيدر -->
    <table class="header-table">
        <tr>
            <!-- بيانات الشركة -->
            <td class="company-info">
                <h2>شركة أبو الطاهر للتوريدات</h2>
                <p>السجل التجاري: 1234567890</p>
                <p>الرقم الضريبي: 9876543210</p>
                <p>العنوان: الخرطوم - السوق العربي</p>
                <p>الهاتف: 0999999999</p>
                <p>البريد الإلكتروني: info@abotaher.com</p>
            </td>
            <!-- بيانات الفاتورة -->
            <td class="invoice-info">
                <p><strong>رقم الفاتورة:</strong> {{ $purchaseInvoice->id }}</p>
                <p><strong>التاريخ:</strong> {{ $purchaseInvoice->created_at->format('Y-m-d') }}</p>
                <p><strong>الوقت:</strong> {{ $purchaseInvoice->created_at->format('H:i') }}</p>
                <p><strong>العميل:</strong> {{ $purchaseInvoice->supplier->name ?? 'غير معروف' }}</p>
                <p><strong>طريقة الدفع:</strong> {{ $purchaseInvoice->payment_method ?? 'نقداً' }}</p>
            </td>
        </tr>
    </table>

    <!-- جدول المنتجات -->
    <table class="items" style="margin-top:20px;">
        <thead>
        <tr>
            <th>#</th>
            <th>اسم المنتج</th>
            <th>الكمية</th>
            <th>سعر الوحدة</th>
            <th>الإجمالي</th>
        </tr>
        </thead>
        <tbody>
        @foreach($purchaseInvoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? 'غير معروف' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- الإجماليات -->
    <div class="summary">
        <p><strong>الإجمالي الكلي:</strong> {{ number_format($purchaseInvoice->total, 2) }} ج.س</p>
    </div>

    <!-- الملاحظات -->
    <div class="notes">
        <p><strong>ملاحظات:</strong> يرجى التأكد من فحص البضاعة قبل المغادرة. لا تُقبل المرتجعات بعد 24 ساعة من الاستلام. هذه الفاتورة لا تعتبر مستنداً رسمياً إلا بتوقيع المسؤول.</p>
    </div>

    <!-- التوقيعات -->
    <table class="signature-table">
        <tr>
            <td>توقيع المورد</td>
            <td>توقيع المستلم</td>
        </tr>
    </table>

</div>

</body>
</html>
