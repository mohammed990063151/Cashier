{{-- يتطلب: $order + حقول calculate/summary (totalSale, totalPaid, hasReturns, ...) --}}
@php
    $fin = $fin ?? app(\App\Services\OrderFinancialService::class);
    if (! isset($hasReturns)) {
        $calc = $fin->calculate($order);
        extract($calc);
    }
@endphp

@if($hasReturns ?? false)
<div class="order-accounting-returns {{ $compact ?? false ? 'order-accounting-returns--compact' : '' }}">
    <div class="oar-title"><i class="fa fa-undo"></i> ملخص المرتجعات والحساب</div>

    <table class="oar-table">
        <tr>
            <td>إجمالي البيع الأصلي (قبل المرتجع)</td>
            <td class="oar-num">{{ number_format($originalTotalSale, 2) }} ج.س</td>
        </tr>
        @if(($invoiceDiscount ?? 0) > 0)
        <tr>
            <td>خصم الفاتورة</td>
            <td class="oar-num text-warning">-{{ number_format($invoiceDiscount, 2) }} ج.س</td>
        </tr>
        <tr>
            <td>الصافي الأصلي بعد الخصم</td>
            <td class="oar-num">{{ number_format($originalTotalAfterDiscount, 2) }} ج.س</td>
        </tr>
        @endif
        <tr class="oar-highlight">
            <td>قيمة المرتجعات (بضاعة)</td>
            <td class="oar-num text-danger">-{{ number_format($totalReturnedMerchandise, 2) }} ج.س</td>
        </tr>
        <tr>
            <td>صافي البيع الحالي (بعد المرتجع)</td>
            <td class="oar-num">{{ number_format($totalSale, 2) }} ج.س</td>
        </tr>
        <tr>
            <td>بعد الخصم (حالياً)</td>
            <td class="oar-num">{{ number_format($totalAfterDiscount, 2) }} ج.س</td>
        </tr>
        <tr>
            <td>إجمالي المدفوع من العميل</td>
            <td class="oar-num text-success">{{ number_format($totalPaid, 2) }} ج.س</td>
        </tr>
        @if(($totalRefundedToCustomer ?? 0) > 0)
        <tr class="oar-highlight">
            <td>مُسترد للعميل من الخزينة</td>
            <td class="oar-num text-danger">-{{ number_format($totalRefundedToCustomer, 2) }} ج.س</td>
        </tr>
        @endif
        <tr>
            <td>المتبقي على العميل</td>
            <td class="oar-num {{ ($remaining ?? 0) > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($remaining ?? 0, 2) }} ج.س</td>
        </tr>
    </table>

    @if($order->relationLoaded('returns') && $order->returns->isNotEmpty())
    <div class="oar-returns-list">
        <strong>سجل المرتجعات:</strong>
        @foreach($order->returns->sortByDesc('return_date') as $ret)
        <div class="oar-return-item">
            <span>{{ $ret->return_number }}</span>
            <span class="text-muted">{{ $ret->return_date->format('d/m/Y') }}</span>
            <span>بضاعة: {{ number_format($ret->items_total, 2) }}</span>
            @if($ret->refund_amount > 0)
            <span class="text-danger">مُسترد: {{ number_format($ret->refund_amount, 2) }}</span>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

<style>
.order-accounting-returns {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 14px;
    font-size: 13px;
}
.order-accounting-returns--compact { padding: 8px 10px; font-size: 12px; margin-bottom: 10px; }
.oar-title { font-weight: 700; color: #0369a1; margin-bottom: 8px; }
.oar-table { width: 100%; border-collapse: collapse; }
.oar-table td { padding: 5px 4px; border-bottom: 1px dashed #e0f2fe; }
.oar-table tr:last-child td { border-bottom: none; }
.oar-num { font-weight: 700; text-align: left; direction: ltr; white-space: nowrap; }
.oar-highlight td { background: #e0f2fe; }
.oar-returns-list { margin-top: 10px; padding-top: 8px; border-top: 1px solid #bae6fd; }
.oar-return-item {
    display: flex; flex-wrap: wrap; gap: 8px 12px;
    padding: 4px 0; font-size: 12px;
}
</style>
@endif
