<?php

namespace App\Support;

use App\Models\Product;
use InvalidArgumentException;

class PurchaseLineNormalizer
{
    /**
     * @param  array<string, mixed>  $item
     * @return array{purchase_unit: string, entered_qty: int, quantity: int, price: float, subtotal: float, unit_label: string}
     */
    public static function normalize(array $item, Product $product): array
    {
        $unit = (string) ($item['purchase_unit'] ?? 'piece');
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);
        $allowed = array_keys(SaleUnits::unitsForOrderForm($bulk, $mode));

        if (! in_array($unit, $allowed, true)) {
            throw new InvalidArgumentException("وحدة الشراء غير مسموحة للمنتج «{$product->name}».");
        }

        $enteredQty = max(1, (int) ($item['entered_qty'] ?? $item['quantity'] ?? 1));
        $unitPrice = max(0, (float) ($item['price'] ?? 0));
        $multiplier = SaleUnits::multiplier($unit, $bulk);
        $pieces = $enteredQty * $multiplier;
        $subtotal = round($enteredQty * $unitPrice, 2);
        $labels = SaleUnits::unitsForOrderForm($bulk, $mode);

        return [
            'purchase_unit' => $unit,
            'entered_qty' => $enteredQty,
            'quantity' => $pieces,
            'price' => $unitPrice,
            'subtotal' => $subtotal,
            'unit_label' => $labels[$unit]['label'] ?? $unit,
        ];
    }
}
