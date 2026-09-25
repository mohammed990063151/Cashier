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
        أدخل الكمية المرتجعة <strong>بنفس وحدة البيع</strong> (حبة / كرتونة / كيلو). لا تخلط الوحدات.
    </p>

    @foreach($order->products as $product)
        @php
            $soldPieces = (float) $product->pivot->quantity;
            $piecePrice = (float) $product->pivot->sale_price;
            $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
            $mode = $product->sale_mode ?? 'flexible';
            $measure = $product->measure_unit ?? 'piece';
            $units = \App\Support\SaleUnits::unitsForOrderForm($bulk, $mode, $measure);
            $soldLabel = \App\Support\SaleUnits::formatQuantityLabel($soldPieces, $bulk, $mode, $measure);
            $breakdown = $fin->productUnitBreakdown($product);
        @endphp
        <div class="return-product-card" data-product-id="{{ $product->id }}" data-max-pieces="{{ $soldPieces }}">
            <div class="return-product-title">
                <strong>{{ $product->name }}</strong>
                <span class="label label-default">مباع: {{ $soldLabel }}</span>
            </div>
            @if(count($breakdown))
            <div class="return-sold-units">
                @foreach($breakdown as $b)
                    <span>{{ $b['label'] }} × {{ \App\Support\DecimalMath::display($b['count']) }}</span>
                @endforeach
            </div>
            @endif

            <div class="return-unit-grid">
                @foreach($units as $unitKey => $unitMeta)
                    @php
                        $unitPrice = \App\Support\SaleUnits::unitPriceForForm($unitKey, $piecePrice, $bulk);
                        $multiplier = (float) $unitMeta['multiplier'];
                        $maxUnit = $multiplier > 0 ? floor($soldPieces / $multiplier + 1e-9) : 0;
                        if ($unitKey === 'kilo') {
                            $maxUnit = $soldPieces;
                        }
                    @endphp
                    <div class="return-unit-block">
                        <label>{{ $unitMeta['label'] }}</label>
                        <small class="text-muted">سعر الوحدة: {{ \App\Support\DecimalMath::display($unitPrice) }} ج.س</small>
                        <input type="number"
                               name="lines[{{ $product->id }}][{{ $unitKey }}][qty]"
                               class="form-control return-qty-input"
                               min="0"
                               max="{{ $maxUnit }}"
                               step="{{ $unitMeta['step'] ?? '1' }}"
                               value="0"
                               data-price="{{ $unitPrice }}"
                               data-multiplier="{{ $multiplier }}"
                               data-product="{{ $product->id }}">
                        <input type="hidden" name="lines[{{ $product->id }}][{{ $unitKey }}][price]" value="{{ $unitPrice }}">
                        <small class="text-muted">الحد الأقصى: {{ \App\Support\DecimalMath::display($maxUnit) }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

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
        <div id="preview-unit-error" class="text-danger" style="display:none;margin-top:6px;"></div>
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
.return-product-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 12px;
    background: #fff;
}
.return-product-title {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 8px;
}
.return-sold-units {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 10px;
    font-size: 12px;
    color: #64748b;
}
.return-sold-units span {
    background: #f1f5f9;
    border-radius: 6px;
    padding: 3px 8px;
}
.return-unit-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
}
.return-unit-block label {
    display: block;
    font-weight: 700;
    margin-bottom: 2px;
}
.return-unit-block small { display: block; margin: 2px 0 4px; }
.return-unit-block .form-control {
    min-height: 42px;
    text-align: center;
    font-size: 16px;
}
.return-preview-box {
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 12px;
    font-size: 13px;
}
.return-preview-box div { display: flex; justify-content: space-between; padding: 3px 0; }
@media (max-width: 767px) {
    .return-unit-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function() {
    var remaining = {{ (float) $remaining }};
    var totalPaid = {{ (float) $totalPaid }};
    var totalSale = {{ (float) $totalSale }};
    var invoiceDiscount = {{ (float) ($invoiceDiscount ?? 0) }};
    var alreadyRefunded = {{ (float) ($alreadyRefundedCash ?? 0) }};
    var box = document.getElementById('return-preview-box');
    var errEl = document.getElementById('preview-unit-error');

    function updatePreview() {
        var itemsTotal = 0;
        var unitError = '';

        document.querySelectorAll('.return-product-card').forEach(function(card) {
            var maxPieces = parseFloat(card.getAttribute('data-max-pieces')) || 0;
            var usedPieces = 0;
            card.querySelectorAll('.return-qty-input').forEach(function(input) {
                var qty = parseFloat(input.value) || 0;
                var price = parseFloat(input.dataset.price) || 0;
                var mult = parseFloat(input.dataset.multiplier) || 1;
                itemsTotal += qty * price;
                usedPieces += qty * mult;
            });
            usedPieces = Math.round(usedPieces * 1000) / 1000;
            if (usedPieces > maxPieces + 0.0005) {
                unitError = 'كمية المرتجع أكبر من المباعة في أحد الأصناف.';
                card.style.borderColor = '#e74c3c';
            } else {
                card.style.borderColor = '#e2e8f0';
            }
        });

        itemsTotal = Math.round(itemsTotal * 100) / 100;
        var remainingReduced = Math.min(itemsTotal, remaining);
        var newTotalSale = Math.max(0, totalSale - itemsTotal);
        var newAfterDiscount = Math.max(0, newTotalSale - invoiceDiscount);
        var stillOwed = Math.max(0, Math.round((totalPaid - newAfterDiscount) * 100) / 100);
        var refund = Math.max(0, Math.round((stillOwed - alreadyRefunded) * 100) / 100);

        if (itemsTotal > 0 || unitError) {
            box.style.display = 'block';
            document.getElementById('preview-items-total').textContent = itemsTotal.toFixed(2);
            document.getElementById('preview-remaining').textContent = remainingReduced.toFixed(2);
            document.getElementById('preview-refund').textContent = refund.toFixed(2);
            if (unitError) {
                errEl.style.display = 'block';
                errEl.textContent = unitError;
            } else {
                errEl.style.display = 'none';
            }
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
