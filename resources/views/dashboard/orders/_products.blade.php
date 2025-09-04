<!-- filepath: resources/views/pdf/order-invoice.blade.php -->

    <style>

        .invoice-container {
            width: 100%;
            padding: 2px;
        }

        /* الهيدر: أزرق رئيسي وحدود خفيفة، وكل المحتوى في الوسط */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            background: #f7faff;
            border-radius: 6px;
            overflow: hidden;
        }

        .header-table td {
            border: 1px solid #e3e6ea !important;
            padding: 4px 0 4px 0;
            vertical-align: middle;
            font-size: 11px;
            text-align: center !important;
        }

        .header-logo {
            width: 38px;
            max-width: 38px;
            border-radius: 4px;
            display: block;
            margin: 0 auto 2px auto;
        }

        .header-title {
            font-size: 13px;
            font-weight: bold;
            color: #337ab7;
            letter-spacing: 1px;
            font-family: 'Cairo', 'Tajawal', Arial, sans-serif;
            margin-bottom: 1px;
        }

        .header-subtitle {
            font-size: 11px;
            font-weight: bold;
            color: #f39c12;
            font-family: 'Cairo', 'Tajawal', Arial, sans-serif;
            margin-bottom: 1px;
        }

        .header-info {
            font-size: 10px;
            color: #337ab7;
            font-family: 'Tajawal', 'Cairo', Arial, sans-serif;
            margin-bottom: 1px;
        }

        .header-sep {
            border-bottom: 1px dashed #337ab7;
            margin: 2px 0 3px 0;
        }

        /* جدول المنتجات */
        table.products {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            background: #fff;
            border-radius: 4px;
            overflow: hidden;
        }

        table.products th,
        table.products td {
            border: 1px solid #e3e6ea;
            padding: 2px 1px;
            text-align: center;
            font-size: 9px;
            font-family: 'Tajawal', 'Cairo', Arial, sans-serif;
        }

        table.products th {
            background: #337ab7;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
        }

        .total-row th,
        .total-row td {
            background: #f7faff;
            font-weight: bold;
            font-size: 10px;
            border-top: 2px solid #f39c12;
            color: #337ab7;
        }

        /* الملاحظات */
        .notes {
            background: #fffbe6;
            padding: 4px 5px;
            margin-top: 5px;
            border-radius: 4px;
            font-size: 9px;
            color: #f39c12;
            border: 1px solid #f9e0a8;
            font-family: 'Tajawal', 'Cairo', Arial, sans-serif;
            text-align: center;
        }

        /* التوقيع */
        .signature {
            margin-top: 7px;
            font-size: 9px;
            color: #337ab7;
            font-family: 'Tajawal', 'Cairo', Arial, sans-serif;
            text-align: center;
        }

        .signature-box {
            width: 60px;
            height: 16px;
            border: 1px dashed #337ab7;
            margin: 2px auto 0 auto;
            background: #f7faff;
        }

        /* خطوط Google للطباعة PDF */
        @font-face {
            font-family: 'Tajawal';
            font-style: normal;
            font-weight: 400;
            src: url('https://fonts.gstatic.com/s/tajawal/v8/Iura6YBj_oCad4k1nzSBC45I.woff2') format('woff2');
        }

        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: 700;
            src: url('https://fonts.gstatic.com/s/cairo/v20/SLXGc1nY6HkvalIhTQ.woff2') format('woff2');
        }
        .print-btn {
    display: inline-block;
    background-color: #337ab7;
    color: white;
    font-size: 1rem;
    padding: 10px 20px;
    text-align: center;
    border: none;
    cursor: pointer;
    border-radius: 6px;
    width: 100%;
    margin-top: 20px;
}

    </style>

    <div class="invoice-container">
        <!-- رأس الفاتورة داخل جدول عمودين متقابلين بخطوط خفيفة وملونة وكل المحتوى في الوسط -->
        <table class="header-table">
            <tr>
                <td colspan="2">
                    <img src="{{ $setting && $setting->logo ? asset('storage/'.$setting->logo) : asset('default-logo.png') }}" class="header-logo" alt="الشعار">
                </td>
            </tr>
            <tr>
                <td colspan="2" class="header-title">
                    {{ $setting->name }}
                </td>
            </tr>
            <tr>
                <td colspan="2" class="header-subtitle">
                    فاتورة مبيعات
                </td>
            </tr>
            <tr>
                <td class="header-info">
                    التاريخ: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}
                </td>
                <td class="header-info">
                    رقم الإيصال: {{ $order->order_number }}
                </td>
            </tr>
            <tr>
                <td colspan="2" class="header-info">
                    العميل: {{ $order->client->name  }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="header-sep"></div>
                </td>
            </tr>
        </table>

        <!-- جدول المنتجات مع صف الإجمالي -->
        <table class="products">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->pivot->quantity }}</td>
                    <td>{{ number_format($product->pivot->sale_price, 2) }}</td>
                    <td>{{ number_format($product->pivot->quantity * $product->pivot->sale_price, 2) }}</td>
                </tr>
                @endforeach
                <!-- صف الإجمالي -->
                <tr class="total-row">
                    <th colspan="2" style="text-align:right;">اجمالي المبلغ</th>
                    <td colspan="2" style="color: green">{{ number_format($order->total_price, 2) }} ج.س</td>
                </tr>
                <tr class="total-row">
                    <th colspan="2" style="text-align:right;">المدفوع منه</th>
                    <td colspan="2">{{ number_format($order->discount, 2) }} ج.س</td>
                </tr>
                <tr class="total-row">
                    <th colspan="2" style="text-align:right;">المتبقي</th>
                    <td colspan="2" style="color: red">{{ number_format($order->remaining, 2) }} ج.س</td>
                </tr>
            </tbody>
        </table>

        <!-- الملاحظات -->
        <div class="notes">
            <p><strong>ملاحظة:</strong>
               لا تُقبل المرتجعات بعد 24 ساعة من الاستلام. تعتبر هذه الفاتورة مستنداً رسمياً إلا بتوقيع المسؤول.

            </p>
        </div>

        <!-- التوقيع -->
        <div class="signature">
            <p>توقيع المستلم:</p>
            <div class="signature-box"></div>
        </div>
    </div>
    <button class="btn btn-primary print-btn" onclick="window.location.href='{{ route('dashboard.orders.pdf', $order->id) }}'">
    <i class="fa fa-scannd "></i> طباعة PDF
</button>



