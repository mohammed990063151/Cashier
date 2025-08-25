
<style>
/* Reset styles */
/* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Print area / Card */
#print-area {
    font-family: 'Roboto', sans-serif;
    direction: rtl;
    padding: 15px;
    background-color: #f7f7f7;
    width: 95%; /* كامل تقريبًا مع الحافة */
    max-width: 800px; /* الحد الأقصى */
    margin: 10px auto;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

/* Header */
.invoice-header {
    text-align: center;
    margin-bottom: 15px;
}

.invoice-header .invoice-logo {
   width: 55%;
    max-width: 117px;
    height: auto;
    margin-bottom: -37px;
    object-fit: contain;
}

.invoice-header h2 {
    font-size: 1.8rem; /* حجم نسبي */
    margin-bottom: 5px;
}

.invoice-header h4 {
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.invoice-header p {
    font-size: 0.9rem;
    margin-bottom: 4px;
}

/* Table */
.invoice-table {
    width: 100%;
    margin-bottom: 20px;
    border-collapse: collapse;
    table-layout: auto; /* يجعل الجدول مرن */
}

.invoice-table th, .invoice-table td {
    padding: 8px 10px;
    text-align: center;
    border: 1px solid #ddd;
    font-size: 0.85rem;
}

.invoice-table th {
    background-color: #2980b9;
    color: #fff;
}

/* Total */
.invoice-total {
    text-align: left;
    font-size: 1rem;
    margin-bottom: 15px;
    font-weight: 600;
}

.invoice-total span {
    font-size: 1.1rem;
    color: #e74c3c;
}

/* Notes */
.notes {
    font-size: 0.85rem;
    margin-bottom: 15px;
    background-color: #ecf0f1;
    padding: 10px;
    border-radius: 5px;
    border-left: 4px solid #3498db;
}

/* Signature */
.signature {
    text-align: left;
    margin-top: 15px;
}

.signature p {
    font-size: 0.9rem;
}

.signature-box {
    width: 40%;
    max-width: 200px;
    height: 40px;
    border: 2px dashed #7f8c8d;
    margin-top: 5px;
    border-radius: 5px;
}

/* Button */
.print-btn {
    display: inline-block;
    background-color: #3498db;
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

.print-btn:hover {
    background-color: #2980b9;
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .invoice-header h2 {
        font-size: 1.4rem;
    }
    .invoice-header h4 {
        font-size: 1rem;
    }
    .invoice-header p,
    .invoice-table th,
    .invoice-table td,
    .invoice-total,
    .notes,
    .signature p {
        font-size: 0.75rem;
    }
    .signature-box {
        width: 60%;
        height: 35px;
    }
    .invoice-header .invoice-logo {
        width: 50%;
        max-width: 120px;
    }
}


</style>

<div id="print-area">
    <div class="invoice-header">
        <img src="{{ asset('dashboard_files/img/logoatabi.jpg') }}" alt="الشعار" class="invoice-logo"
        {{-- <img src="{{ public_path('dashboard_files/img/logoatabi.jpg') }}" --}}
     alt="الشعار"
     width="100">
     {{-- height="auto"> --}}
        <h2>أبو الطاهر</h2>
        <h4>أفاميا</h4>
        <p>التاريخ: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</p>
        <p>رقم الإيصال: {{ $order->order_number }}</p>
        <p>العميل: {{ $order->client->name  }}</p>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>اسم الصنف</th>
                <th>الكمية</th>
                <th>السعر</th>
                 <th>المجموع</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->pivot->quantity }}</td>
                    <td>{{ number_format($product->sale_price) }}</td>
                    <td>{{ number_format($product->pivot->quantity * $product->sale_price, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="invoice-total">
        جملة المبلغ: <span>{{ number_format($order->total_price, 0) }}  جنية </span>
    </div>

    <!-- ✅ الملاحظات أو الشروط -->
    <div class="notes">
        <p><strong>ملاحظة:</strong> يرجى التأكد من فحص البضاعة جيدًا قبل المغادرة. لا تقبل المرتجعات بعد 24 ساعة من الاستلام.</p>
    </div>

    <!-- ✅ التوقيع -->
    <div class="signature">
        <p>توقيع المستلم:</p>
        <div class="signature-box"></div>
    </div>
</div>
<button class="btn btn-primary print-btn"
        onclick="window.location.href='{{ route('dashboard.orders.pdf', $order->id) }}'">
    <i class="fa fa-scannd "></i> طباعة PDF
</button>
