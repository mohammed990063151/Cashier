<?php

namespace App\Support;

use App\Models\Product;
use InvalidArgumentException;

class PurchaseLineNormalizer
{
    /**
     * @param  array<string, mixed>  $item
     * @return array{purchase_unit: string, entered_qty: float, quantity: float, price: float, subtotal: float, unit_label: string}
     */
    public static function normalize(array $item, Product $product): array
    {
        $unit = (string) ($item['purchase_unit'] ?? 'piece');
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
        $allowed = array_keys(SaleUnits::unitsForOrderForm($bulk, $mode, $measure));

        if ($measure === SaleUnits::UNIT_KILO) {
            $unit = 'kilo';
        }

        if (! in_array($unit, $allowed, true)) {
            throw new InvalidArgumentException("وحدة الشراء غير مسموحة للمنتج «{$product->name}».");
        }

        $enteredQty = max(0.001, DecimalMath::round($item['entered_qty'] ?? $item['quantity'] ?? 1));
        $unitPrice = max(0, DecimalMath::round($item['price'] ?? 0));
        $multiplier = SaleUnits::multiplier($unit, $bulk);
        $pieces = DecimalMath::mul($enteredQty, $multiplier);
        $subtotal = DecimalMath::mul($enteredQty, $unitPrice);
        $labels = SaleUnits::unitsForOrderForm($bulk, $mode, $measure);

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
