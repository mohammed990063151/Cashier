<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة شراء رقم {{ $purchaseInvoice->invoice_number }}</title>
    <style>
        @page { size: 80mm 80mm; margin: 0; }
        body {
            font-family: 'Tajawal', 'Cairo', Arial, sans-serif;
            direction: rtl;
            font-size: 10px;
            margin: 0;
            padding: 2px;
            background: #fff;
            color: #222;
        }
        .invoice-container { width: 100%; padding: 2px; }

        /* جدول الهيدر */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            background: #f7faff;
            border-radius: 6px;
        }
        .header-table td {
            border: 1px solid #e3e6ea;
            padding: 4px;
            text-align: center;
            font-size: 11px;
        }
        .header-logo { width: 38px; margin: 0 auto 2px auto; }
        .header-title { font-size: 13px; font-weight: bold; color: #337ab7; margin-bottom: 1px; }
        .header-subtitle { font-size: 11px; font-weight: bold; color: #f39c12; margin-bottom: 1px; }
        .header-info { font-size: 10px; color: #337ab7; margin-bottom: 1px; }
        .header-sep { border-bottom: 1px dashed #337ab7; margin: 2px 0 3px 0; }

        /* جدول المنتجات */
        table.products { width: 100%; border-collapse: collapse; margin: 5px 0; }
        table.products th, table.products td {
            border: 1px solid #e3e6ea;
            padding: 2px;
            text-align: center;
            font-size: 9px;
        }
        table.products th { background: #337ab7; color: #fff; font-weight: bold; font-size: 10px; }
        .total-row th, .total-row td { background: #f7faff; font-weight: bold; border-top: 2px solid #f39c12; color: #337ab7; }

        /* الملاحظات */
        .notes { background: #fffbe6; padding: 4px 5px; margin-top: 5px; border-radius: 4px; font-size: 9px; color: #f39c12; border: 1px solid #f9e0a8; text-align: center; }

        /* التوقيع */
        .signature { margin-top: 7px; font-size: 9px; color: #337ab7; text-align: center; }
        .signature-box { width: 60px; height: 16px; border: 1px dashed #337ab7; margin: 2px auto 0 auto; background: #f7faff; }
    </style>
</head>
<body>
<div class="invoice-container">

    <!-- رأس الفاتورة -->
    <table class="header-table">
        <tr>
            <td colspan="2"> <img src="{{ $setting && $setting->logo ? public_path('storage/'.$setting->logo) : public_path('logo.png') }}" class="header-logo" alt="الشعار">
</td>
        </tr>
        <tr>
            <td colspan="2" class="header-title"> {{ $setting->name }}</td>
        </tr>
        <tr>
            <td colspan="2" class="header-subtitle">فاتورة شراء</td>
        </tr>
        <tr>
            <td class="header-info">التاريخ: {{ $purchaseInvoice->created_at->format('d/m/Y') }}</td>
            <td class="header-info">رقم الفاتورة: {{ $purchaseInvoice->invoice_number }}</td>
        </tr>
        <tr>
            <td colspan="2" class="header-info">المورد: {{ $purchaseInvoice->supplier->name ?? 'غير معروف' }}</td>
        </tr>
        <tr><td colspan="2"><div class="header-sep"></div></td></tr>
    </table>

    <!-- جدول المنتجات -->
    <table class="products">
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
        @foreach ($purchaseInvoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? 'غير معروف' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <th colspan="3">الإجمالي</th>
            <td colspan="2" style="color:green">{{ number_format($purchaseInvoice->total, 2) }} ج.س</td>
        </tr>
        <tr class="total-row">
            <th colspan="3">المدفوع</th>
            <td colspan="2">{{ number_format($purchaseInvoice->paid, 2) }} ج.س</td>
        </tr>
        <tr class="total-row">
            <th colspan="3">المتبقي</th>
            <td colspan="2" style="color: red">{{ number_format($purchaseInvoice->remaining, 2) }} ج.س</td>
        </tr>
        </tbody>
    </table>

    <!-- الملاحظات -->
    <div class="notes">
        <p>يرجى التأكد من فحص البضاعة قبل المغادرة. لا تُقبل المرتجعات بعد 24 ساعة من الاستلام. تعتبر هذه الفاتورة مستنداً رسمياً عند توقيع المسؤول.</p>
    </div>

    <!-- التوقيع -->
<table style="width: 100%; border-collapse: collapse; margin-top: 7px;">
    <tr>
        <!-- توقيع المورد -->
        <td style="width: 50%; text-align: center; vertical-align: middle; font-size: 9px; font-family: 'Tajawal', 'Cairo', Arial, sans-serif; color: #337ab7;">
            <p style="margin: 0 0 3px 0;">توقيع المورد:</p>
            <table style="width: 80px; height: 30px; border: 1px dashed #337ab7; margin: 0 auto;">
                <tr><td></td></tr>
            </table>
        </td>

        <!-- توقيع المستلم -->
        <td style="width: 50%; text-align: center; vertical-align: middle; font-size: 9px; font-family: 'Tajawal', 'Cairo', Arial, sans-serif; color: #337ab7;">
            <p style="margin: 0 0 3px 0;">توقيع المستلم:</p>
            <table style="width: 80px; height: 30px; border: 1px dashed #337ab7; margin: 0 auto;">
                <tr><td></td></tr>
            </table>
        </td>
    </tr>
</table>


</div>
</body>
</html>




{{-- <!DOCTYPE html>
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
                <br />
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
         <p><strong>الإجمالي المدفوع:</strong> {{ number_format($purchaseInvoice->paid, 2) }} ج.س</p>
          <p><strong>الإجمالي المتبقي:</strong> {{ number_format($purchaseInvoice->remaining, 2) }} ج.س</p>
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
</html> --}}
