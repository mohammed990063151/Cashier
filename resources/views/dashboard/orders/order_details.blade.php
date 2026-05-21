@php
    $financial = app(\App\Services\OrderFinancialService::class);
    $payStatus = $financial->paymentStatus($order);
    $payLabel = $financial->paymentStatusLabel($payStatus);
    $payClass = $financial->paymentStatusClass($payStatus);
@endphp

<div class="order-details-modern">
    <div class="od-header">
        <div class="od-header-main">
            <h4 class="od-order-no">{{ $order->order_number }}</h4>
            <p class="od-meta">{{ $order->client->name }} — {{ $order->created_at->format('d-m-Y H:i') }}</p>
        </div>
        <span class="label od-status-badge {{ $payClass }}">{{ $payLabel }}</span>
    </div>

    @include('dashboard.orders._order_accounting', array_merge(get_defined_vars(), ['order' => $order]))

    <div class="od-stats-grid">
        <div class="od-stat">
            <span class="od-stat-label">{{ ($hasReturns ?? false) ? 'الصافي الحالي' : 'الإجمالي' }}</span>
            <strong class="od-stat-value text-success">{{ number_format($totalSale, 2) }}</strong>
        </div>
        @if($hasReturns ?? false)
        <div class="od-stat">
            <span class="od-stat-label">الأصلي قبل المرتجع</span>
            <strong class="od-stat-value">{{ number_format($originalTotalSale, 2) }}</strong>
        </div>
        @endif
        @if($invoiceDiscount > 0)
        <div class="od-stat">
            <span class="od-stat-label">الخصم</span>
            <strong class="od-stat-value text-warning">{{ number_format($invoiceDiscount, 2) }}</strong>
        </div>
        @endif
        <div class="od-stat">
            <span class="od-stat-label">بعد الخصم</span>
            <strong class="od-stat-value">{{ number_format($totalAfterDiscount, 2) }}</strong>
        </div>
        <div class="od-stat od-stat--highlight">
            <span class="od-stat-label">المدفوع</span>
            <strong class="od-stat-value text-primary">{{ number_format($totalPaid, 2) }}</strong>
        </div>
        <div class="od-stat">
            <span class="od-stat-label">المتبقي</span>
            <strong class="od-stat-value {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($remaining, 2) }}</strong>
        </div>
        <div class="od-stat">
            <span class="od-stat-label">الربح</span>
            <strong class="od-stat-value text-info">{{ number_format($profitAfterDiscount, 2) }}</strong>
        </div>
    </div>

    <h5 class="od-section-title"><i class="fa fa-boxes"></i> المنتجات</h5>

    @foreach($order->products as $product)
        @php
            $breakdown = $financial->productUnitBreakdown($product);
            $lineTotal = $product->pivot->quantity * $product->pivot->sale_price;
        @endphp
        <div class="od-product-card">
            <div class="od-product-head">
                <strong>{{ $product->name }}</strong>
                <span class="od-product-total">{{ number_format($lineTotal, 2) }} ج.س</span>
            </div>
            @php $saleLine = $financial->formatProductSaleLine($product); @endphp
            <div class="od-product-summary">
                <span class="od-pieces-total">{{ $saleLine['quantity'] }}</span>
                <span class="od-piece-price">{{ $saleLine['price'] }}</span>
            </div>
            @if(count($breakdown) > 0)
            <div class="od-unit-breakdown">
                @foreach($breakdown as $line)
                <div class="od-unit-chip">
                    <span class="od-unit-chip-label">{{ $line['label'] }}</span>
                    <span class="od-unit-chip-qty">× {{ $line['count'] }}</span>
                    <span class="od-unit-chip-pieces">({{ $line['pieces'] }} حبة)</span>
                    <span class="od-unit-chip-sub">{{ number_format($line['line_total'], 2) }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    @endforeach

    @include('dashboard.orders._order_returns_items', ['order' => $order])

    <h5 class="od-section-title"><i class="fa fa-wallet"></i> الدفع</h5>
    <ul class="od-payments-list">
        @if($paidAtSale > 0)
        <li>
            <span>دفعة عند البيع</span>
            <strong>{{ number_format($paidAtSale, 2) }} ج.س</strong>
        </li>
        @endif
        @foreach($order->payments->where('method', '!=', 'cash_at_sale') as $payment)
        <li>
            <span>{{ $payment->created_at->format('Y-m-d') }} — {{ $payment->method ?? 'دفعة' }}</span>
            <strong>{{ number_format($payment->amount, 2) }} ج.س</strong>
        </li>
        @endforeach
        @if($totalPaid <= 0)
        <li class="od-empty">لا توجد دفعات مسجّلة</li>
        @endif
    </ul>

    <a href="{{ route('dashboard.orders.pdf', $order->id) }}" target="_blank" class="btn btn-primary btn-sm od-print-btn">
        <i class="fa fa-print"></i> طباعة إيصال
    </a>
</div>

<style>
.order-details-modern {
    --od-primary: #1e3a5f;
    --od-accent: #0ea5e9;
    --od-border: #e2e8f0;
    --od-radius: 10px;
    padding: 0;
}
.od-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--od-primary);
}
.od-order-no {
    margin: 0 0 4px;
    font-size: 18px;
    font-weight: 700;
    color: var(--od-primary);
}
.od-meta {
    margin: 0;
    font-size: 12px;
    color: #64748b;
}
.od-status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.od-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 8px;
    margin-bottom: 16px;
}
.od-stat {
    background: #f8fafc;
    border: 1px solid var(--od-border);
    border-radius: var(--od-radius);
    padding: 8px 10px;
    text-align: center;
}
.od-stat--highlight {
    background: linear-gradient(180deg, #e0f2fe 0%, #f0f9ff 100%);
    border-color: #7dd3fc;
}
.od-stat-label {
    display: block;
    font-size: 11px;
    color: #64748b;
    margin-bottom: 4px;
}
.od-stat-value {
    font-size: 15px;
    display: block;
}
.od-section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--od-primary);
    margin: 12px 0 10px;
}
.od-product-card {
    background: #fff;
    border: 1px solid var(--od-border);
    border-radius: var(--od-radius);
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 1px 4px rgba(30, 58, 95, 0.06);
}
.od-product-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.od-product-head strong {
    color: var(--od-primary);
    font-size: 15px;
}
.od-product-total {
    font-weight: 700;
    color: #10b981;
    font-size: 15px;
}
.od-product-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 16px;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 10px;
}
.od-pieces-total {
    background: #e0f2fe;
    color: #0369a1;
    padding: 2px 10px;
    border-radius: 12px;
    font-weight: 600;
}
.od-unit-breakdown {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.od-unit-chip {
    display: grid;
    grid-template-columns: 1fr auto auto auto;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
    font-size: 13px;
}
.od-unit-chip-label {
    font-weight: 600;
    color: var(--od-primary);
}
.od-unit-chip-qty {
    color: #475569;
    font-weight: 600;
}
.od-unit-chip-pieces {
    font-size: 11px;
    color: #94a3b8;
}
.od-unit-chip-sub {
    font-weight: 700;
    color: #10b981;
    text-align: left;
    direction: ltr;
}
.od-payments-list {
    list-style: none;
    padding: 0;
    margin: 0 0 14px;
    border: 1px solid var(--od-border);
    border-radius: var(--od-radius);
    overflow: hidden;
}
.od-payments-list li {
    display: flex;
    justify-content: space-between;
    padding: 10px 12px;
    border-bottom: 1px solid var(--od-border);
    font-size: 13px;
}
.od-payments-list li:last-child {
    border-bottom: none;
}
.od-payments-list li.od-empty {
    justify-content: center;
    color: #94a3b8;
}
.od-print-btn {
    border-radius: 8px;
}
</style>
