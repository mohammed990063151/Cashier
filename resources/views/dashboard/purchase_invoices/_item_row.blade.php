@php
    $idx = $index;
    $productId = $item['product_id'] ?? '';
    $unit = $item['purchase_unit'] ?? 'piece';
    $qty = $item['entered_qty'] ?? 1;
    $price = $item['price'] ?? 0;
@endphp
<tr class="purchase-item-row" data-index="{{ $idx }}">
    <td>
        <select name="items[{{ $idx }}][product_id]" class="form-control product-select" required>
            <option value="">— المنتج —</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected((string)$productId === (string)$product->id)>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="items[{{ $idx }}][purchase_unit]" class="form-control unit-select" required>
            <option value="piece">حبة</option>
        </select>
        <small class="text-muted unit-pieces-hint"></small>
    </td>
    <td>
        <input type="number" name="items[{{ $idx }}][entered_qty]" class="form-control entered-qty" min="1" step="1" value="{{ $qty }}" required>
    </td>
    <td>
        <input type="number" name="items[{{ $idx }}][price]" class="form-control unit-price" min="0.01" step="0.01" value="{{ $price }}" required>
    </td>
    <td class="row-subtotal text-bold">0.00</td>
    <td>
        <button type="button" class="btn btn-danger btn-sm remove-purchase-row" title="حذف"><i class="fa fa-times"></i></button>
    </td>
</tr>
