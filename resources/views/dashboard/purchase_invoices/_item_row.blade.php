@php
    $idx = $index;
    $productId = $item['product_id'] ?? '';
    $unit = $item['purchase_unit'] ?? 'piece';
    $qty = $item['entered_qty'] ?? 1;
    $price = $item['price'] ?? 0;
    $selectedProduct = collect($products ?? [])->firstWhere('id', (int) $productId);
    $purchaseUnits = $selectedProduct
        ? \App\Support\SaleUnits::unitsForPurchaseForm(
            max(1, (int) ($selectedProduct->pieces_per_carton ?? 12)),
            $selectedProduct->measure_unit ?? 'piece'
        )
        : [
            'piece' => ['label' => 'حبة', 'multiplier' => 1, 'step' => '1'],
            'half_carton' => ['label' => 'نصف كرتونة', 'multiplier' => 6, 'step' => '1'],
            'bulk' => ['label' => 'كرتونة', 'multiplier' => 12, 'step' => '0.001'],
            'kilo' => ['label' => 'كيلو', 'multiplier' => 1, 'step' => '0.001'],
        ];
@endphp
<tr class="purchase-item-row" data-index="{{ $idx }}">
    <td>
        <select name="items[{{ $idx }}][product_id]" class="form-control product-select" required>
            <option value="">— المنتج —</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}"
                    data-measure="{{ $product->measure_unit ?? 'piece' }}"
                    data-bulk="{{ max(1, (int) ($product->pieces_per_carton ?? 12)) }}"
                    data-sale-mode="{{ $product->sale_mode ?? 'flexible' }}"
                    @selected((string)$productId === (string)$product->id)>
                    {{ $product->name }}
                    @if(($product->measure_unit ?? '') === 'carton') (كرتونة) @elseif(($product->measure_unit ?? '') === 'kilo') (كيلو) @endif
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="items[{{ $idx }}][purchase_unit]" class="form-control unit-select" required>
            @foreach($purchaseUnits as $key => $u)
                <option value="{{ $key }}" data-multiplier="{{ $u['multiplier'] }}" @selected($unit === $key)>
                    {{ $u['label'] }}
                </option>
            @endforeach
        </select>
        <small class="text-muted unit-pieces-hint"></small>
    </td>
    <td>
        <input type="number" name="items[{{ $idx }}][entered_qty]" class="form-control entered-qty" min="0.001" step="0.001" value="{{ $qty }}" required>
    </td>
    <td>
        <input type="number" name="items[{{ $idx }}][price]" class="form-control unit-price" min="0" step="0.001" value="{{ $price }}" required>
    </td>
    <td class="row-subtotal text-bold">0.00</td>
    <td>
        <button type="button" class="btn btn-danger btn-sm remove-purchase-row" title="حذف"><i class="fa fa-times"></i></button>
    </td>
</tr>
