@php
    $fin = app(\App\Services\OrderFinancialService::class);
    $status = $fin->paymentStatus($order);
    $statusLabel = $fin->paymentStatusLabel($status);
    $statusClass = $fin->paymentStatusClass($status);
@endphp

<div class="order-preview-panel">
    <div class="preview-head">
        <div>
            <strong class="preview-order-no">{{ $order->order_number }}</strong>
            <div class="preview-client">{{ $order->client->name }}</div>
            <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
        </div>
        <span class="label {{ $statusClass }}" style="font-size:12px;">{{ $statusLabel }}</span>
    </div>

    @include('dashboard.orders._order_accounting', array_merge(get_defined_vars(), ['order' => $order, 'compact' => true]))

    <div class="preview-totals">
        <div class="preview-row">
            <span>{{ ($hasReturns ?? false) ? 'الصافي الحالي' : 'الإجمالي' }}</span>
            <span class="money money-total">{{ number_format($totalSale, 2) }}</span>
        </div>
        @if($invoiceDiscount > 0)
        <div class="preview-row">
            <span>الخصم</span>
            <span class="money money-discount">-{{ number_format($invoiceDiscount, 2) }}</span>
        </div>
        @endif
        <div class="preview-row">
            <span>بعد الخصم</span>
            <span class="money">{{ number_format($totalAfterDiscount, 2) }}</span>
        </div>
        <div class="preview-row preview-row--paid">
            <span>{{ ($totalRefundedToCustomer ?? 0) > 0 ? 'صافي المدفوع' : 'المدفوع' }}</span>
            <span class="money money-paid">{{ number_format($netPaid ?? $totalPaid, 2) }}</span>
        </div>
        @if(($totalRefundedToCustomer ?? 0) > 0)
        <div class="preview-row">
            <span>مُسترد</span>
            <span class="money text-danger">-{{ number_format($totalRefundedToCustomer, 2) }}</span>
        </div>
        @endif
        <div class="preview-row">
            <span>المتبقي</span>
            <span class="money {{ $remaining > 0 ? 'money-remain-due' : 'money-remain-zero' }}">{{ number_format($remaining, 2) }}</span>
        </div>
    </div>

    <div class="preview-section-title">الأصناف</div>
    <div class="preview-products">
        @foreach($order->products as $product)
            @php
                $breakdown = $fin->productUnitBreakdown($product);
                $lineTotal = \App\Support\SaleUnits::lineMoney($product);
            @endphp
            <div class="preview-product-item">
                <div class="preview-product-top">
                    <strong>{{ $product->name }}</strong>
                    <span class="money">{{ number_format($lineTotal, 2) }}</span>
                </div>
                @php $saleLine = $fin->formatProductSaleLine($product); @endphp
                <div class="preview-product-meta">
                    {{ $saleLine['quantity'] }} — {{ $saleLine['price'] }}
                </div>
                @if(count($breakdown) > 0)
                <div class="preview-units">
                    @foreach($breakdown as $line)
                    <span class="preview-unit-tag">{{ $line['label'] }} ×{{ $line['count'] }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        @endforeach
    </div>


    <div class="preview-actions">
        @if($order->products->count() > 0)
        <button type="button" class="btn btn-warning btn-block btn-sm order-return-btn"
                data-url="{{ route('dashboard.orders.return', $order->id) }}">
            <i class="fa fa-undo"></i> تسجيل مرتجع
        </button>
        @endif
        <button type="button" class="btn btn-default btn-block btn-sm view-order-modal-btn"
                data-order-id="{{ $order->id }}">
            <i class="fa fa-eye"></i> تفاصيل كاملة
        </button>
        <a href="{{ route('dashboard.orders.pdf', $order->id) }}" target="_blank" class="btn btn-primary btn-block">
            <i class="fa fa-print"></i> طباعة PDF
        </a>
    </div>
</div>

<style>
.order-preview-panel { font-size: 13px; }
.preview-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-bottom: 2px solid #3c8dbc;
}
.preview-order-no { font-size: 16px; color: #1e3c72; display: block; }
.preview-client { color: #475569; margin: 2px 0; }
.preview-totals {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 10px;
    margin-bottom: 12px;
}
.preview-row {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px dashed #e8edf3;
}
.preview-row:last-child { border-bottom: none; }
.preview-row--paid {
    background: #e3f2fd;
    margin: 4px -6px;
    padding: 6px !important;
    border-radius: 4px;
    border-bottom: none;
}
.preview-row .money {
    font-weight: 700;
    direction: ltr;
}
.preview-section-title {
    font-weight: 700;
    color: #3c8dbc;
    margin-bottom: 8px;
    font-size: 13px;
}
.preview-products {
    max-height: 280px;
    overflow-y: auto;
    margin-bottom: 12px;
}
.preview-product-item {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}
.preview-product-item:last-child { border-bottom: none; }
.preview-product-top {
    display: flex;
    justify-content: space-between;
    gap: 8px;
}
.preview-product-meta {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}
.preview-units {
    margin-top: 6px;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.preview-unit-tag {
    background: #e0f2fe;
    color: #0369a1;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
}
.preview-actions .btn { margin-bottom: 6px; }
.preview-actions .btn:last-child { margin-bottom: 0; }
.money-total { color: #2e7d32; }
.money-discount { color: #b8860b; }
.money-paid { color: #1b5e20; }
.money-remain-due { color: #c62828; }
.money-remain-zero { color: #2e7d32; }
</style>
