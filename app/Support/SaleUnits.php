<?php

namespace App\Support;

use App\Models\Product;

class SaleUnits
{
    public const PIECE = 1;

    public const PACK_3 = 3;

    public const PACK_6 = 6;

    public const DOZEN = 12;

    public const MODE_PIECE_ONLY = 'piece_only';

    public const MODE_BULK_ONLY = 'bulk_only';

    public const MODE_FLEXIBLE = 'flexible';

    public const UNIT_PIECE = 'piece';

    public const UNIT_CARTON = 'carton';

    public const UNIT_KILO = 'kilo';

    public static function normalizeSaleMode(?string $mode): string
    {
        return match ($mode) {
            self::MODE_PIECE_ONLY, self::MODE_BULK_ONLY => $mode,
            default => self::MODE_FLEXIBLE,
        };
    }

    public static function normalizeMeasureUnit(?string $unit): string
    {
        return match ($unit) {
            self::UNIT_CARTON, self::UNIT_KILO => $unit,
            default => self::UNIT_PIECE,
        };
    }

    public static function measureUnitLabel(string $unit): string
    {
        return match (self::normalizeMeasureUnit($unit)) {
            self::UNIT_CARTON => 'كرتونة',
            self::UNIT_KILO => 'كيلو',
            default => 'حبة / قطعة',
        };
    }

    public static function measureUnitShort(string $unit): string
    {
        return match (self::normalizeMeasureUnit($unit)) {
            self::UNIT_CARTON => 'كرتونة',
            self::UNIT_KILO => 'كيلو',
            default => 'حبة',
        };
    }

    public static function saleModeLabel(string $mode): string
    {
        return match (self::normalizeSaleMode($mode)) {
            self::MODE_PIECE_ONLY => 'بالحبة فقط',
            self::MODE_BULK_ONLY => 'بالكرتونة كاملة فقط',
            default => 'مرن: حبة + نصف + كرتونة',
        };
    }

    public static function saleModeShortHint(string $mode): string
    {
        return match (self::normalizeSaleMode($mode)) {
            self::MODE_PIECE_ONLY => 'يُباع بالحبة فقط في الطلبات.',
            self::MODE_BULK_ONLY => 'يُباع بالكرتونة الكاملة فقط.',
            default => 'يُباع بالحبة أو نصف الكرتونة أو الكرتونة الكاملة.',
        };
    }

    public static function resolveSaleModeForMeasure(string $measureUnit, ?string $saleMode = null): string
    {
        $unit = self::normalizeMeasureUnit($measureUnit);

        if ($unit === self::UNIT_KILO) {
            return self::MODE_PIECE_ONLY;
        }

        // الكرتونة: البيع المرن افتراضياً (حبة + نصف + كرتونة كاملة)
        if ($unit === self::UNIT_CARTON) {
            return self::normalizeSaleMode($saleMode ?: self::MODE_FLEXIBLE);
        }

        return self::normalizeSaleMode($saleMode);
    }

    public static function labels(int $piecesPerBulk = 12): array
    {
        $bulkLabel = self::bulkLabel($piecesPerBulk);

        return [
            'piece' => 'حبة',
            'half_carton' => self::halfCartonLabel($piecesPerBulk),
            'pack_3' => '3 قطع',
            'pack_6' => '6 قطع',
            'dozen' => 'دستة (12)',
            'bulk' => $bulkLabel,
            'kilo' => 'كيلو',
        ];
    }

    public static function bulkLabel(int $piecesPerBulk): string
    {
        if ($piecesPerBulk <= 1) {
            return 'حبة';
        }

        return "كرتونة كاملة ({$piecesPerBulk} حبة)";
    }

    public static function halfCartonLabel(int $piecesPerBulk): string
    {
        $half = DecimalMath::div(max(1, $piecesPerBulk), 2);

        return 'نصف كرتونة ('.DecimalMath::display($half).' حبة)';
    }

    public static function halfCartonPieces(int $piecesPerBulk): float
    {
        return DecimalMath::div(max(1, $piecesPerBulk), 2);
    }

    /**
     * @return array<string, array{label: string, multiplier: float|int, step: string}>
     */
    public static function availableForProduct(Product $product): array
    {
        return self::unitsForOrderForm(
            max(1, (int) ($product->pieces_per_carton ?? 12)),
            self::normalizeSaleMode($product->sale_mode ?? null),
            self::normalizeMeasureUnit($product->measure_unit ?? null)
        );
    }

    public static function multiplier(string $unit, int $piecesPerBulk): float
    {
        return match ($unit) {
            'piece', 'kilo' => (float) self::PIECE,
            'half_carton' => self::halfCartonPieces($piecesPerBulk),
            'pack_3' => (float) self::PACK_3,
            'pack_6' => (float) self::PACK_6,
            'dozen' => (float) self::DOZEN,
            'bulk', 'carton' => (float) max(1, $piecesPerBulk),
            default => (float) self::PIECE,
        };
    }

    /**
     * وحدات البيع في الطلب حسب المنتج.
     * للكرتونة (بيع مرن): حبة + نصف كرتونة + كرتونة كاملة — بعدد الحبات الذي تحدده يدوياً.
     *
     * @return array<string, array{label: string, multiplier: float|int, step: string}>
     */
    public static function unitsForOrderForm(int $piecesPerBulk, ?string $saleMode = null, ?string $measureUnit = null): array
    {
        $measure = self::normalizeMeasureUnit($measureUnit);

        if ($measure === self::UNIT_KILO) {
            return [
                'kilo' => [
                    'label' => 'كيلو',
                    'multiplier' => 1,
                    'step' => '0.001',
                ],
            ];
        }

        $mode = self::normalizeSaleMode($saleMode);
        $bulk = max(1, $piecesPerBulk);

        if ($mode === self::MODE_PIECE_ONLY) {
            return [
                'piece' => ['label' => 'حبة', 'multiplier' => self::PIECE, 'step' => '1'],
            ];
        }

        if ($mode === self::MODE_BULK_ONLY) {
            return [
                'bulk' => ['label' => self::bulkLabel($bulk), 'multiplier' => $bulk, 'step' => '1'],
            ];
        }

        // بيع مرن لوحدة كرتونة: الكرتونة أولاً ثم الحبة
        if ($measure === self::UNIT_CARTON && $bulk > 1) {
            return [
                'bulk' => [
                    'label' => self::bulkLabel($bulk),
                    'multiplier' => $bulk,
                    'step' => '1',
                ],
                'half_carton' => [
                    'label' => self::halfCartonLabel($bulk),
                    'multiplier' => self::halfCartonPieces($bulk),
                    'step' => '1',
                ],
                'piece' => ['label' => 'حبة', 'multiplier' => self::PIECE, 'step' => '1'],
            ];
        }

        $units = [
            'piece' => ['label' => 'حبة', 'multiplier' => self::PIECE, 'step' => '1'],
            'pack_3' => ['label' => '3 قطع', 'multiplier' => self::PACK_3, 'step' => '1'],
            'pack_6' => ['label' => '6 قطع', 'multiplier' => self::PACK_6, 'step' => '1'],
        ];

        if ($bulk > 1) {
            $units['half_carton'] = [
                'label' => self::halfCartonLabel($bulk),
                'multiplier' => self::halfCartonPieces($bulk),
                'step' => '1',
            ];
            $units['bulk'] = [
                'label' => self::bulkLabel($bulk),
                'multiplier' => $bulk,
                'step' => '1',
            ];
        } else {
            $units['dozen'] = ['label' => 'دستة (12)', 'multiplier' => self::DOZEN, 'step' => '1'];
        }

        return $units;
    }

    /**
     * وحدات الشراء حسب نوع المنتج (حبة / كرتونة / كيلو)
     * غير مقيدة بـ sale_mode — يمكن شراء كرتونة حتى لو البيع بالحبة.
     *
     * @return array<string, array{label: string, multiplier: float|int, step: string}>
     */
    public static function unitsForPurchaseForm(int $piecesPerBulk, ?string $measureUnit = null): array
    {
        $measure = self::normalizeMeasureUnit($measureUnit);
        $bulk = max(1, $piecesPerBulk);

        if ($measure === self::UNIT_KILO) {
            return [
                'kilo' => [
                    'label' => 'كيلو',
                    'multiplier' => 1,
                    'step' => '0.001',
                ],
            ];
        }

        if ($measure === self::UNIT_CARTON || $bulk > 1) {
            $bulk = max(2, $bulk);

            return [
                'bulk' => [
                    'label' => self::bulkLabel($bulk),
                    'multiplier' => $bulk,
                    'step' => '1',
                ],
                'half_carton' => [
                    'label' => self::halfCartonLabel($bulk),
                    'multiplier' => self::halfCartonPieces($bulk),
                    'step' => '1',
                ],
                'piece' => ['label' => 'حبة', 'multiplier' => self::PIECE, 'step' => '1'],
            ];
        }

        return [
            'piece' => ['label' => 'حبة', 'multiplier' => self::PIECE, 'step' => '1'],
        ];
    }

    public static function masterUnitKey(?string $saleMode, ?string $measureUnit = null): string
    {
        $measure = self::normalizeMeasureUnit($measureUnit);
        if ($measure === self::UNIT_KILO) {
            return 'kilo';
        }

        $mode = self::normalizeSaleMode($saleMode);
        if ($mode === self::MODE_BULK_ONLY || ($measure === self::UNIT_CARTON && $mode !== self::MODE_PIECE_ONLY)) {
            return 'bulk';
        }

        return 'piece';
    }

    public static function formatQuantityLabel(float|int $pieces, int $piecesPerBulk = 12, ?string $saleMode = null, ?string $measureUnit = null): string
    {
        $qty = DecimalMath::round($pieces);
        if ($qty <= 0) {
            return self::normalizeMeasureUnit($measureUnit) === self::UNIT_KILO ? '0 كيلو' : '0 حبة';
        }

        $measure = self::normalizeMeasureUnit($measureUnit);
        if ($measure === self::UNIT_KILO) {
            return DecimalMath::display($qty).' كيلو';
        }

        $mode = self::normalizeSaleMode($saleMode);
        $bulkSize = max(1, $piecesPerBulk);
        $intPieces = (int) round($qty);

        if ($measure === self::UNIT_CARTON && $bulkSize > 1) {
            return self::formatFlexibleQuantityLabel($intPieces, $bulkSize);
        }

        if ($mode === self::MODE_PIECE_ONLY) {
            return DecimalMath::display($qty).' حبة';
        }

        if ($mode === self::MODE_BULK_ONLY && $bulkSize > 1) {
            $bulks = intdiv($intPieces, $bulkSize);
            $rest = $intPieces % $bulkSize;
            $label = $bulks.' '.self::bulkLabel($bulkSize);
            if ($rest > 0) {
                $label .= ' + '.$rest.' حبة';
            }

            return $label.' — '.$intPieces.' حبة';
        }

        return self::formatFlexibleQuantityLabel($intPieces, $bulkSize);
    }

    public static function formatPiecesLabel(int $pieces, int $piecesPerBulk = 12): string
    {
        return self::formatFlexibleQuantityLabel($pieces, $piecesPerBulk);
    }

    public static function formatFlexibleQuantityLabel(int $pieces, int $piecesPerBulk = 12): string
    {
        if ($pieces <= 0) {
            return '0 حبة';
        }

        $parts = [];
        $remaining = $pieces;
        $bulkSize = max(1, $piecesPerBulk);

        if ($bulkSize > 1 && $bulkSize !== self::DOZEN) {
            $bulks = intdiv($remaining, $bulkSize);
            if ($bulks > 0) {
                $parts[] = $bulks.' كرتونة';
                $remaining -= $bulks * $bulkSize;
            }

            $half = (int) floor($bulkSize / 2);
            if ($half > 0 && $remaining >= $half) {
                $halves = intdiv($remaining, $half);
                // فقط نصف واحد منطقي لكل كرتونة متبقية جزئياً
                if ($halves > 0 && ($bulkSize % 2 === 0 || $remaining === $half)) {
                    $parts[] = '1 نصف كرتونة';
                    $remaining -= $half;
                }
            }
        }

        $dozens = intdiv($remaining, self::DOZEN);
        if ($dozens > 0) {
            $parts[] = $dozens.' دستة';
            $remaining -= $dozens * self::DOZEN;
        }

        $six = intdiv($remaining, self::PACK_6);
        if ($six > 0) {
            $parts[] = $six.' ×6';
            $remaining -= $six * self::PACK_6;
        }

        $three = intdiv($remaining, self::PACK_3);
        if ($three > 0) {
            $parts[] = $three.' ×3';
            $remaining -= $three * self::PACK_3;
        }

        if ($remaining > 0) {
            $parts[] = $remaining.' حبة';
        }

        return implode(' + ', $parts).' — '.$pieces.' حبة';
    }

    /**
     * @return array<int, array{label: string, count: float|int, pieces: float|int}>
     */
    public static function breakdownLines(float|int $pieces, int $piecesPerBulk = 12, ?string $saleMode = null, ?string $measureUnit = null): array
    {
        $qty = DecimalMath::round($pieces);
        if ($qty <= 0) {
            return [];
        }

        $measure = self::normalizeMeasureUnit($measureUnit);
        if ($measure === self::UNIT_KILO) {
            return [['label' => 'كيلو', 'count' => $qty, 'pieces' => $qty]];
        }

        $mode = self::normalizeSaleMode($saleMode);
        $bulkSize = max(1, $piecesPerBulk);
        $intPieces = (int) round($qty);

        if ($mode === self::MODE_PIECE_ONLY) {
            return [['label' => 'حبة', 'count' => $intPieces, 'pieces' => $intPieces]];
        }

        if (($mode === self::MODE_BULK_ONLY) && $bulkSize > 1) {
            $bulks = intdiv($intPieces, $bulkSize);
            $lines = [];
            if ($bulks > 0) {
                $lines[] = ['label' => self::bulkLabel($bulkSize), 'count' => $bulks, 'pieces' => $bulks * $bulkSize];
            }
            $rest = $intPieces % $bulkSize;
            if ($rest > 0) {
                $lines[] = ['label' => 'حبة', 'count' => $rest, 'pieces' => $rest];
            }

            return $lines;
        }

        return self::breakdownLinesFlexible($intPieces, $bulkSize);
    }

    /**
     * @return array<int, array{label: string, count: int, pieces: int}>
     */
    protected static function breakdownLinesFlexible(int $pieces, int $bulkSize): array
    {
        $lines = [];
        $remaining = $pieces;

        if ($bulkSize > 1 && $bulkSize !== self::DOZEN) {
            $count = intdiv($remaining, $bulkSize);
            if ($count > 0) {
                $lines[] = ['label' => 'كرتونة كاملة ('.$bulkSize.' حبة)', 'count' => $count, 'pieces' => $count * $bulkSize];
                $remaining -= $count * $bulkSize;
            }

            $half = (int) floor($bulkSize / 2);
            if ($half > 0 && $remaining >= $half) {
                $lines[] = ['label' => 'نصف كرتونة ('.$half.' حبة)', 'count' => 1, 'pieces' => $half];
                $remaining -= $half;
            }
        }

        if ($bulkSize === self::DOZEN || $remaining >= self::DOZEN) {
            $count = intdiv($remaining, self::DOZEN);
            if ($count > 0) {
                $lines[] = ['label' => 'دستة (12 حبة)', 'count' => $count, 'pieces' => $count * self::DOZEN];
                $remaining -= $count * self::DOZEN;
            }
        }

        $six = intdiv($remaining, self::PACK_6);
        if ($six > 0) {
            $lines[] = ['label' => '6 قطع', 'count' => $six, 'pieces' => $six * self::PACK_6];
            $remaining -= $six * self::PACK_6;
        }

        $three = intdiv($remaining, self::PACK_3);
        if ($three > 0) {
            $lines[] = ['label' => '3 قطع', 'count' => $three, 'pieces' => $three * self::PACK_3];
            $remaining -= $three * self::PACK_3;
        }

        if ($remaining > 0) {
            $lines[] = ['label' => 'حبة', 'count' => $remaining, 'pieces' => $remaining];
        }

        return $lines;
    }

    public static function priceLabelForProduct(Product $product): string
    {
        $measure = self::normalizeMeasureUnit($product->measure_unit ?? null);

        return match ($measure) {
            self::UNIT_KILO => 'سعر الكيلو',
            self::UNIT_CARTON => 'سعر الكرتونة',
            default => self::normalizeSaleMode($product->sale_mode ?? null) === self::MODE_BULK_ONLY
                ? 'سعر العبوة'
                : 'سعر الحبة',
        };
    }

    public static function unitPriceForForm(string $unitKey, float $piecePrice, int $bulkSize): float
    {
        $price = DecimalMath::mul($piecePrice, self::multiplier($unitKey, $bulkSize));
        if ($unitKey === 'kilo') {
            return DecimalMath::round($price);
        }

        return DecimalMath::money($price);
    }

    /**
     * Convert form entry values into stored base-unit prices/stock.
     * Base unit: piece for piece/carton products, kilo for kilo products.
     *
     * @param  array<string, mixed>  $data
     * @return array{measure_unit: string, sale_mode: string, pieces_per_carton: int, purchase_price: float, sale_price: float, stock: float}
     */
    public static function normalizeProductEntry(array $data): array
    {
        $measure = self::normalizeMeasureUnit($data['measure_unit'] ?? null);
        $saleMode = self::resolveSaleModeForMeasure($measure, $data['sale_mode'] ?? null);
        $bulk = max(1, (int) ($data['pieces_per_carton'] ?? 12));

        if ($measure === self::UNIT_KILO) {
            $bulk = 1;
            $saleMode = self::MODE_PIECE_ONLY;
        } elseif ($measure === self::UNIT_CARTON) {
            $bulk = max(2, $bulk);
        } elseif ($saleMode === self::MODE_PIECE_ONLY) {
            $bulk = 1;
        } elseif ($saleMode === self::MODE_BULK_ONLY) {
            $bulk = max(2, $bulk);
        }

        $purchase = DecimalMath::round($data['purchase_price'] ?? 0);
        $sale = DecimalMath::round($data['sale_price'] ?? 0);
        $stock = DecimalMath::round($data['stock'] ?? 0);

        // الحبة/الكرتونة: أسعار صحيحة بدون كسور (تجنّب أخطاء الحسابات)
        if ($measure !== self::UNIT_KILO) {
            $purchase = DecimalMath::money($purchase);
            $sale = DecimalMath::money($sale);
            if ($measure === self::UNIT_CARTON) {
                $stock = DecimalMath::money($stock); // عدد كراتين صحيح
            } else {
                $stock = DecimalMath::money($stock); // عدد حبات صحيح
            }
        }

        // Carton entry: prices & stock are per carton → convert to per-piece base.
        // نستخدم دقة أعلى داخلياً ثم نعرض الكرتونة كرقم صحيح.
        if ($measure === self::UNIT_CARTON && $bulk > 1) {
            $purchase = $bulk > 0 ? ($purchase / $bulk) : 0;
            $sale = $bulk > 0 ? ($sale / $bulk) : 0;
            $stock = DecimalMath::mul($stock, $bulk);
            // احفظ بسعر الحبة بدقة كافية لاسترجاع سعر الكرتونة صحيحاً
            $purchase = round($purchase, 6);
            $sale = round($sale, 6);
        }

        return [
            'measure_unit' => $measure,
            'sale_mode' => $saleMode,
            'pieces_per_carton' => $bulk,
            'purchase_price' => $purchase,
            'sale_price' => $sale,
            'stock' => max(0, $stock),
        ];
    }

    /**
     * Convert stored base values into display/entry values for the selected measure unit.
     *
     * @return array{purchase_price: float, sale_price: float, stock: float}
     */
    public static function toEntryValues(Product $product, ?string $measureUnit = null): array
    {
        $measure = self::normalizeMeasureUnit($measureUnit ?? $product->measure_unit ?? null);
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $purchase = (float) $product->purchase_price;
        $sale = (float) $product->sale_price;
        $stock = DecimalMath::round($product->stock);

        if ($measure === self::UNIT_CARTON && $bulk > 1) {
            return [
                'purchase_price' => DecimalMath::money($purchase * $bulk),
                'sale_price' => DecimalMath::money($sale * $bulk),
                'stock' => DecimalMath::money($bulk > 0 ? $stock / $bulk : 0),
            ];
        }

        if ($measure === self::UNIT_KILO) {
            return [
                'purchase_price' => DecimalMath::round($purchase),
                'sale_price' => DecimalMath::round($sale),
                'stock' => $stock,
            ];
        }

        return [
            'purchase_price' => DecimalMath::money($purchase),
            'sale_price' => DecimalMath::money($sale),
            'stock' => DecimalMath::money($stock),
        ];
    }

    /**
     * @return array{qty: float|int, price: float}
     */
    public static function initialUnitInput(Product $product, string $unitKey): array
    {
        $piecePrice = (float) $product->pivot->sale_price;
        $bulkSize = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = self::normalizeSaleMode($product->sale_mode ?? null);
        $measure = self::normalizeMeasureUnit($product->measure_unit ?? null);
        $storedPieces = DecimalMath::round($product->pivot->quantity ?? 0);

        if ($measure === self::UNIT_KILO && $unitKey === 'kilo') {
            return [
                'qty' => $storedPieces,
                'price' => $piecePrice,
            ];
        }

        // منتجات الكرتونة / بالعبوة فقط: الكمية المحفوظة تُعرض بكرتونة
        if (
            $unitKey === 'bulk'
            && $bulkSize > 1
            && (
                $mode === self::MODE_BULK_ONLY
                || ($measure === self::UNIT_CARTON && $mode !== self::MODE_PIECE_ONLY)
            )
        ) {
            return [
                'qty' => DecimalMath::div($storedPieces, $bulkSize),
                'price' => self::unitPriceForForm('bulk', $piecePrice, $bulkSize),
            ];
        }

        if ($unitKey === 'piece' && ! (
            $mode === self::MODE_BULK_ONLY
            || ($measure === self::UNIT_CARTON && $mode !== self::MODE_PIECE_ONLY && $bulkSize > 1)
        )) {
            return [
                'qty' => $storedPieces,
                'price' => DecimalMath::money($piecePrice),
            ];
        }

        return ['qty' => 0, 'price' => self::unitPriceForForm($unitKey, $piecePrice, $bulkSize)];
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array{quantity: float, sale_price: float}
     */
    public static function toPieceLine(array $line, int $piecesPerBulk, ?string $saleMode = null, ?string $measureUnit = null): array
    {
        $allowedUnits = array_keys(self::unitsForOrderForm($piecesPerBulk, $saleMode, $measureUnit));
        $totalPieces = 0.0;
        $lineTotal = 0.0;

        foreach ($allowedUnits as $unit) {
            $qty = max(0, DecimalMath::round($line[$unit]['qty'] ?? 0));
            $price = max(0, DecimalMath::round($line[$unit]['price'] ?? 0));

            if ($qty <= 0) {
                continue;
            }

            $multiplier = self::multiplier($unit, $piecesPerBulk);
            $totalPieces = DecimalMath::add($totalPieces, DecimalMath::mul($qty, $multiplier));
            $lineTotal = DecimalMath::add($lineTotal, DecimalMath::mul($qty, $price));
        }

        if ($totalPieces <= 0) {
            return ['quantity' => 0, 'sale_price' => 0];
        }

        return [
            'quantity' => $totalPieces,
            'sale_price' => DecimalMath::div($lineTotal, $totalPieces),
        ];
    }
}
