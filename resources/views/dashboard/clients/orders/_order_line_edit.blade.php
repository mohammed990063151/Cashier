@php
    use App\Support\DecimalMath;
    use App\Support\SaleUnits;
    $bulkSize = max(1, (int) ($product->pieces_per_carton ?? 12));
    $piecePrice = (float) $product->pivot->sale_price;
    $qtyStored = (float) $product->pivot->quantity;
    $lineTotal = DecimalMath::mul($qtyStored, $piecePrice);
    $saleMode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);
    $measureUnit = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
    $units = SaleUnits::unitsForOrderForm($bulkSize, $saleMode, $measureUnit);
    $masterUnit = SaleUnits::masterUnitKey($saleMode, $measureUnit);
    $qtyHint = SaleUnits::formatQuantityLabel($qtyStored, $bulkSize, $saleMode, $measureUnit);
@endphp
<tr class="order-item" data-id="{{ $product->id }}" data-bulk-size="{{ $bulkSize }}" data-sale-mode="{{ $saleMode }}" data-measure-unit="{{ $measureUnit }}">
    <td>
        <strong>{{ $product->name }}</strong>
        <div class="text-muted total-pieces-hint" style="font-size:12px;">{{ $qtyHint }}</div>
    </td>
    <td colspan="2">
        <div class="order-unit-grid">
            @foreach ($units as $unitKey => $unit)
                @php
                    $initial = SaleUnits::initialUnitInput($product, $unitKey);
                    $qty = $initial['qty'];
                    $price = $initial['price'];
                    $step = $unit['step'] ?? '1';
                    $hint = $unitKey === 'kilo' ? 'كيلو' : ($unit['multiplier'].' حبة');
                @endphp
                <div class="order-unit-block" data-unit="{{ $unitKey }}" data-multiplier="{{ $unit['multiplier'] }}">
                    <div class="order-unit-title">{{ $unit['label'] }}</div>
                    <div class="row" style="margin:0 -5px;">
                        <div class="col-xs-6" style="padding:0 5px;">
                            <label class="order-unit-label">الكمية</label>
                            <input type="number" min="0" step="{{ $step }}" value="{{ $qty }}"
                                name="products[{{ $product->id }}][{{ $unitKey }}][qty]"
                                class="form-control input-sm unit-qty">
                        </div>
                        <div class="col-xs-6" style="padding:0 5px;">
                            <label class="order-unit-label">السعر</label>
                            <input type="number" min="0" step="0.001" value="{{ $price }}"
                                name="products[{{ $product->id }}][{{ $unitKey }}][price]"
                                class="form-control input-sm unit-price{{ $unitKey === $masterUnit ? ' unit-price-master' : '' }}">
                        </div>
                    </div>
                    <small class="text-muted unit-hint">= {{ $hint }}</small>
                </div>
            @endforeach
        </div>
    </td>
    <td>
        <span class="product-price" style="color:#01941f;font-weight:bold;">{{ DecimalMath::display($lineTotal) }}</span>
        <input type="hidden" name="products[{{ $product->id }}][total_price]" value="{{ $lineTotal }}">
    </td>
    <td>
        <button type="button" class="btn btn-danger btn-sm remove-product-btn" data-id="{{ $product->id }}">
            <span class="fa fa-trash"></span>
        </button>
    </td>
</tr>
