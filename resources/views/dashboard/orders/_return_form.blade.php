@php
    $fin = app(\App\Services\OrderFinancialService::class);
@endphp

<form action="{{ route('dashboard.orders.return.store', $order->id) }}" method="post" id="order-return-form">
    @csrf

    <div class="return-form-head">
        <div>
            <strong>طلب {{ $order->order_number }}</strong>
            <div class="text-muted">{{ $order->client->name }}</div>
        </div>
        <div class="text-left">
            <small class="text-muted">المدفوع</small>
            <div class="money money-paid">{{ number_format($totalPaid, 2) }} ج.س</div>
            <small class="text-muted">المتبقي</small>
            <div class="money {{ $remaining > 0 ? 'money-remain-due' : 'money-remain-zero' }}">{{ number_format($remaining, 2) }} ج.س</div>
        </div>
    </div>

    @if((float) ($order->total_return ?? 0) > 0)
    <div class="alert alert-info" style="padding:8px 12px;font-size:12px;">
        <i class="fa fa-info-circle"></i>
        مرتجعات سابقة (بضاعة): <strong>{{ number_format($order->total_return, 2) }} ج.س</strong>
        @if(($alreadyRefundedCash ?? 0) > 0)
        — مُسترد من الخزينة سابقاً: <strong>{{ number_format($alreadyRefundedCash, 2) }} ج.س</strong>
        @endif
    </div>
    @endif

    <p class="text-muted" style="font-size:12px;margin-bottom:10px;">
        حدد الكميات المرتجعة. يُخصم قيمة المرتجع من المتبقي أولاً، ثم يُسحب من الخزينة ما يُستحق إرجاعه للعميل من المدفوعات.
    </p>

    <table class="table table-bordered table-condensed return-items-table">
        <thead>
            <tr>
                <th>المنتج</th>
                <th class="text-center">المباع</th>
                <th class="text-center" style="width:100px;">كمية المرتجع</th>
                <th class="text-center">السعر</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td class="text-center">{{ $product->pivot->quantity }}</td>
                <td>
                    <input type="number"
                           name="lines[{{ $product->id }}]"
                           class="form-control input-sm text-center return-qty-input"
                           min="0"
                           max="{{ $product->pivot->quantity }}"
                           value="{{ old('lines.'.$product->id, 0) }}"
                           data-price="{{ $product->pivot->sale_price }}">
                </td>
                <td class="text-center">{{ number_format($product->pivot->sale_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="form-group">
        <label>تاريخ المرتجع</label>
        <input type="date" name="return_date" class="form-control" value="{{ old('return_date', now()->toDateString()) }}">
    </div>

    <div class="form-group">
        <label>ملاحظات (اختياري)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="سبب الإرجاع أو تفاصيل إضافية">{{ old('notes') }}</textarea>
    </div>

    <div class="return-preview-box" id="return-preview-box" style="display:none;">
        <div><span>قيمة المرتجع:</span> <strong id="preview-items-total">0.00</strong> ج.س</div>
        <div><span>يُخصم من المتبقي:</span> <strong id="preview-remaining">0.00</strong> ج.س</div>
        <div><span>يُسحب من الخزينة للعميل:</span> <strong id="preview-refund" class="text-danger">0.00</strong> ج.س</div>
    </div>

    <button type="submit" class="btn btn-warning btn-block" id="return-submit-btn">
        <i class="fa fa-undo"></i> تأكيد المرتجع وتسجيله في الخزينة
    </button>
</form>

<style>
.return-form-head {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
}
.return-items-table { font-size: 12px; margin-bottom: 12px; }
.return-preview-box {
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 12px;
    font-size: 13px;
}
.return-preview-box div { display: flex; justify-content: space-between; padding: 3px 0; }
</style>

<script>
(function() {
    var remaining = {{ (float) $remaining }};
    var totalPaid = {{ (float) $totalPaid }};
    var totalSale = {{ (float) $totalSale }};
    var invoiceDiscount = {{ (float) ($invoiceDiscount ?? 0) }};
    var alreadyRefunded = {{ (float) ($alreadyRefundedCash ?? 0) }};
    var box = document.getElementById('return-preview-box');

    function updatePreview() {
        var itemsTotal = 0;
        document.querySelectorAll('.return-qty-input').forEach(function(input) {
            var qty = parseInt(input.value, 10) || 0;
            var price = parseFloat(input.dataset.price) || 0;
            itemsTotal += qty * price;
        });
        itemsTotal = Math.round(itemsTotal * 100) / 100;
        var remainingReduced = Math.min(itemsTotal, remaining);
        var newTotalSale = Math.max(0, totalSale - itemsTotal);
        var newAfterDiscount = Math.max(0, newTotalSale - invoiceDiscount);
        var stillOwed = Math.max(0, Math.round((totalPaid - newAfterDiscount) * 100) / 100);
        var refund = Math.max(0, Math.round((stillOwed - alreadyRefunded) * 100) / 100);

        if (itemsTotal > 0) {
            box.style.display = 'block';
            document.getElementById('preview-items-total').textContent = itemsTotal.toFixed(2);
            document.getElementById('preview-remaining').textContent = remainingReduced.toFixed(2);
            document.getElementById('preview-refund').textContent = refund.toFixed(2);
        } else {
            box.style.display = 'none';
        }
    }

    document.querySelectorAll('.return-qty-input').forEach(function(input) {
        input.addEventListener('input', updatePreview);
    });
    updatePreview();
})();
</script>
