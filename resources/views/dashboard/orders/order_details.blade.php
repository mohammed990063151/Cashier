<div class="p-3">

    <!-- معلومات أساسية -->
    <h4 class="mb-3 text-primary">معلومات الطلب</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <tr>
                <th>رقم الطلب</th>
                <td>{{ $order->order_number }}</td>
            </tr>
            <tr>
                <th>اسم العميل</th>
                <td>{{ $order->client->name }}</td>
            </tr>
            <tr>
                <th>إجمالي الشراء</th>
                <td>{{ number_format($totalPurchase, 2) }} ج.س</td>
            </tr>
            <tr>
                <th>إجمالي البيع</th>
                <td>{{ number_format($totalSale, 2) }} ج.س</td>
            </tr>
            <tr>
                <th>إجمالي الربح</th>
                <td class="text-success">{{ number_format($profit, 2) }} ج.س</td>
            </tr>
            <tr>
                <th>نسبة المكسب</th>
                <td class="text-info">{{ number_format($profitPercentage, 2) }}%</td>
            </tr>
        </table>
    </div>

    <!-- تفاصيل المنتجات -->
    <h4 class="mt-4 text-primary">تفاصيل المنتجات</h4>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>المنتج</th>
                    <th>سعر الشراء</th>
                    <th>سعر البيع</th>
                    <th>الكمية</th>
                    <th>الإجمالي</th>
                    <th>الربح</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->products as $index => $product)
                    @php
                        $totalProductPurchase = $product->pivot->cost_price * $product->pivot->quantity;
                        $totalProductSale = $product->pivot->sale_price * $product->pivot->quantity;
                        $productProfit = $totalProductSale - $totalProductPurchase;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ number_format($product->pivot->cost_price, 2) }}</td>
                        <td>{{ number_format($product->pivot->sale_price, 2) }}</td>
                        <td>{{ $product->pivot->quantity }}</td>
                        <td>{{ number_format($totalProductSale, 2) }}</td>
                        <td class="text-success">{{ number_format($productProfit, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- تفاصيل الدفع -->
    <h4 class="mt-4 text-primary">تفاصيل الدفع</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>المبلغ المدفوع</th>
                    <th>طريقة الدفع</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $paid = $order->payments->sum('amount');
                    $discountAmount = $order->tax_amount ?? 0;
                    $totalAfterDiscount = max($totalSale - $discountAmount, 0);
                    $profitAfterDiscount = $totalAfterDiscount - $totalPurchase;
                    $discountPercentage = $totalSale > 0 ? ($discountAmount / $totalSale) * 100 : 0;
                    $remaining = max($totalAfterDiscount - $paid - $order->discount, 0);
                @endphp

                @forelse($order->payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                        <td>{{ number_format($payment->amount, 2) }} ج.س</td>
                        <td>{{ $payment->method ?? 'غير محدد' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">لا توجد مدفوعات مسجلة</td>
                    </tr>
                @endforelse

                <tr class="bg-light">
                    <th>إجمالي المبيعات</th>
                    <td colspan="2">{{ number_format($totalSale, 2) }} ج.س</td>
                </tr>

                @if($discountAmount > 0)
                <tr class="bg-danger text-white">
                    <th>الخصم</th>
                    <td colspan="2">- {{ number_format($discountAmount, 2) }} ج.س ({{ number_format($discountPercentage, 2) }}%)</td>
                </tr>
                @endif

                <tr class="bg-success text-white">
                    <th>الإجمالي بعد الخصم</th>
                    <td colspan="2">{{ number_format($totalAfterDiscount, 2) }} ج.س</td>
                </tr>

                <tr class="bg-light">
                    <th>إجمالي المدفوع</th>
                    <td colspan="2">{{ number_format($order->discount, 2) }} ج.س</td>
                </tr>

                <tr class="bg-info text-white">
                    <th>الربح بعد الخصم</th>
                    <td colspan="2">{{ number_format($profitAfterDiscount, 2) }} ج.س</td>
                </tr>

                <tr class="bg-warning">
                    <th>المتبقي</th>
                    <td colspan="2">{{ number_format($remaining, 2) }} ج.س</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
