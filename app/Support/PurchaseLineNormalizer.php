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
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
        $labels = SaleUnits::unitsForPurchaseForm($bulk, $measure);
        $allowed = array_keys($labels);

        if ($measure === SaleUnits::UNIT_KILO) {
            $unit = 'kilo';
        }

        // قبول مرادفات قديمة
        if ($unit === 'carton') {
            $unit = 'bulk';
        }

        if (! in_array($unit, $allowed, true)) {
            throw new InvalidArgumentException("وحدة الشراء غير مسموحة للمنتج «{$product->name}». المتاح: ".implode('، ', array_column($labels, 'label')));
        }

        $enteredQty = max(0.001, DecimalMath::round($item['entered_qty'] ?? $item['quantity'] ?? 1));
        $unitPrice = max(0, DecimalMath::round($item['price'] ?? 0));
        $multiplier = SaleUnits::multiplier($unit, $bulk);
        $baseQty = DecimalMath::mul($enteredQty, $multiplier);
        $subtotal = DecimalMath::mul($enteredQty, $unitPrice);

        return [
            'purchase_unit' => $unit,
            'entered_qty' => $enteredQty,
            'quantity' => $baseQty, // أساس المخزون: حبة أو كيلو
            'price' => $unitPrice,
            'subtotal' => $subtotal,
            'unit_label' => $labels[$unit]['label'] ?? $unit,
        ];
    }
}
