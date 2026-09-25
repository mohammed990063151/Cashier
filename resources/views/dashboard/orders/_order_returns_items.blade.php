@if($order->relationLoaded('returns') && $order->returns->isNotEmpty())
<div class="order-returns-detail">
    <h5 class="od-section-title"><i class="fa fa-undo"></i> تفاصيل المرتجعات</h5>
    @foreach($order->returns->sortByDesc('return_date') as $ret)
    <div class="ord-ret-card">
        <div class="ord-ret-head">
            <strong>{{ $ret->return_number }}</strong>
            <span>{{ $ret->return_date->format('d/m/Y') }}</span>
        </div>
        @if($ret->relationLoaded('items') && $ret->items->isNotEmpty())
        <table class="table table-condensed table-bordered" style="margin:8px 0 0;font-size:12px;">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th class="text-center">الكمية</th>
                    <th class="text-center">السعر</th>
                    <th class="text-center">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ret->items as $item)
                @php
                    $p = $item->product;
                    $qtyLabel = $p
                        ? \App\Support\SaleUnits::formatQuantityLabel(
                            (float) $item->quantity,
                            max(1, (int) ($p->pieces_per_carton ?? 12)),
                            $p->sale_mode ?? null,
                            $p->measure_unit ?? null
                        )
                        : (string) $item->quantity;
                @endphp
                <tr>
                    <td>{{ $p->name ?? '—' }}</td>
                    <td class="text-center">{{ $qtyLabel }}</td>
                    <td class="text-center">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        <div class="ord-ret-foot">
            <span>قيمة المرتجع: <strong>{{ number_format($ret->items_total, 2) }}</strong> ج.س</span>
            @if($ret->refund_amount > 0)
            <span class="text-danger">مُسترد من الخزينة: <strong>{{ number_format($ret->refund_amount, 2) }}</strong> ج.س</span>
            @else
            <span class="text-muted">خصم من المتبقي — دون حركة نقدية</span>
            @endif
            @if($ret->remaining_reduced > 0 && $ret->refund_amount <= 0)
            <span>خُصم من المتبقي: {{ number_format($ret->remaining_reduced, 2) }} ج.س</span>
            @endif
        </div>
        @if($ret->notes)
        <div class="text-muted" style="font-size:11px;margin-top:4px;">{{ $ret->notes }}</div>
        @endif
    </div>
    @endforeach
</div>
<style>
.ord-ret-card {
    border: 1px solid #fcd34d;
    background: #fffbeb;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 10px;
}
.ord-ret-head {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
}
.ord-ret-foot {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 16px;
    margin-top: 8px;
    font-size: 12px;
}
</style>
@endif
