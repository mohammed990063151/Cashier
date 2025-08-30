<h4>مدفوعات الطلب رقم #{{ $order->order_number }}</h4>
<p>اسم العميل: {{ $order->client->name }}</p>
<p>المتبقي: <strong style="color: red">{{ number_format($remaining, 2) }}</strong></p>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>المبلغ</th>
            <th>طريقة الدفع</th>
            <th>ملاحظات</th>
            <th>تاريخ الدفع</th>
        </tr>
    </thead>
    <tbody>
        @forelse($order->payments as $payment)
        <tr>
            <td>{{ number_format($payment->amount, 2) }}</td>
            <td>{{ $payment->method ?? '-' }}</td>
            <td>{{ $payment->notes ?? '-' }}</td>
            <td>{{ $payment->created_at->format('d-m-Y') }}</td>
        </tr>

                @empty
        <tr>
            <td colspan="4" class="text-center">لا توجد مدفوعات</td>
        </tr>
        @endforelse
        <tr>
            <td colspan="4" class="text-right" ><strong >الإجمالي المدفوع
            :<strong style="color: green;"> {{ number_format($totalPaid, 2) }}</strong></strong></td>
        </tr>
    </tbody>
</table>
