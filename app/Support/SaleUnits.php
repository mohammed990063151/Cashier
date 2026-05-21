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

    public static function normalizeSaleMode(?string $mode): string
    {
        return match ($mode) {
            self::MODE_PIECE_ONLY, self::MODE_BULK_ONLY => $mode,
            default => self::MODE_FLEXIBLE,
        };
    }

    public static function saleModeLabel(string $mode): string
    {
        return match (self::normalizeSaleMode($mode)) {
            self::MODE_PIECE_ONLY => 'بالحبة فقط',
            self::MODE_BULK_ONLY => 'بالعبوة/كرتون فقط',
            default => 'بيع مرن (حبة + وحدات)',
        };
    }

    public static function saleModeShortHint(string $mode): string
    {
        return match (self::normalizeSaleMode($mode)) {
            self::MODE_PIECE_ONLY => 'يُباع ويُسعَّر بالحبة في الطلبات.',
            self::MODE_BULK_ONLY => 'يُباع بالعبوة أو الكرتون الكامل فقط.',
            default => 'يدعم الحبة، 3، 6، الدستة، والعبوة.',
        };
    }

    public static function labels(int $piecesPerBulk = 12): array
    {
        $bulkLabel = self::bulkLabel($piecesPerBulk);

        return [
            'piece' => 'حبة',
            'pack_3' => '3 قطع',
            'pack_6' => '6 قطع',
            'dozen' => 'دستة (12)',
            'bulk' => $bulkLabel,
        ];
    }

    public static function bulkLabel(int $piecesPerBulk): string
    {
        if ($piecesPerBulk <= 1) {
            return 'حبة';
        }

        return "عبوة ({$piecesPerBulk} حبة)";
    }

    /**
     * @return array<string, array{label: string, multiplier: int}>
     */
    public static function availableForProduct(Product $product): array
    {
        return self::unitsForOrderForm(
            max(1, (int) ($product->pieces_per_carton ?? 12)),
            self::normalizeSaleMode($product->sale_mode ?? null)
        );
    }

    public static function multiplier(string $unit, int $piecesPerBulk): int
    {
        return match ($unit) {
            'piece' => self::PIECE,
            'pack_3' => self::PACK_3,
            'pack_6' => self::PACK_6,
            'dozen' => self::DOZEN,
            'bulk', 'carton' => max(1, $piecesPerBulk),
            default => self::PIECE,
        };
    }

    /**
     * @return array<string, array{label: string, multiplier: int}>
     */
    public static function unitsForOrderForm(int $piecesPerBulk, ?string $saleMode = null): array
    {
        $mode = self::normalizeSaleMode($saleMode);
        $bulk = max(1, $piecesPerBulk);

        if ($mode === self::MODE_PIECE_ONLY) {
            return [
                'piece' => ['label' => 'حبة', 'multiplier' => self::PIECE],
            ];
        }

        if ($mode === self::MODE_BULK_ONLY) {
            return [
                'bulk' => ['label' => self::bulkLabel($bulk), 'multiplier' => $bulk],
            ];
        }

        $units = [
            'piece' => ['label' => 'حبة', 'multiplier' => self::PIECE],
            'pack_3' => ['label' => '3 قطع', 'multiplier' => self::PACK_3],
            'pack_6' => ['label' => '6 قطع', 'multiplier' => self::PACK_6],
        ];

        if ($bulk === self::DOZEN) {
            $units['dozen'] = ['label' => 'دستة (12)', 'multiplier' => self::DOZEN];
        } elseif ($bulk > 1) {
            if ($bulk % self::DOZEN === 0 && $bulk > self::DOZEN) {
                $units['dozen'] = ['label' => 'دستة (12)', 'multiplier' => self::DOZEN];
            }
            $units['bulk'] = ['label' => 'عبوة ('.$bulk.')', 'multiplier' => $bulk];
        } else {
            $units['dozen'] = ['label' => 'دستة (12)', 'multiplier' => self::DOZEN];
        }

        return $units;
    }

    public static function masterUnitKey(?string $saleMode): string
    {
        return self::normalizeSaleMode($saleMode) === self::MODE_BULK_ONLY ? 'bulk' : 'piece';
    }

    public static function formatQuantityLabel(int $pieces, int $piecesPerBulk = 12, ?string $saleMode = null): string
    {
        if ($pieces <= 0) {
            return '0 حبة';
        }

        $mode = self::normalizeSaleMode($saleMode);
        $bulkSize = max(1, $piecesPerBulk);

        if ($mode === self::MODE_PIECE_ONLY) {
            return $pieces.' حبة';
        }

        if ($mode === self::MODE_BULK_ONLY && $bulkSize > 1) {
            $bulks = intdiv($pieces, $bulkSize);
            $rest = $pieces % $bulkSize;
            $label = $bulks.' '.self::bulkLabel($bulkSize);
            if ($rest > 0) {
                $label .= ' + '.$rest.' حبة';
            }

            return $label.' — '.$pieces.' حبة';
        }

        return self::formatFlexibleQuantityLabel($pieces, $bulkSize);
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
                $parts[] = $bulks.' عبوة';
                $remaining -= $bulks * $bulkSize;
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
     * @return array<int, array{label: string, count: int, pieces: int}>
     */
    public static function breakdownLines(int $pieces, int $piecesPerBulk = 12, ?string $saleMode = null): array
    {
        if ($pieces <= 0) {
            return [];
        }

        $mode = self::normalizeSaleMode($saleMode);
        $bulkSize = max(1, $piecesPerBulk);

        if ($mode === self::MODE_PIECE_ONLY) {
            return [['label' => 'حبة', 'count' => $pieces, 'pieces' => $pieces]];
        }

        if ($mode === self::MODE_BULK_ONLY && $bulkSize > 1) {
            $bulks = intdiv($pieces, $bulkSize);
            $lines = [];
            if ($bulks > 0) {
                $lines[] = ['label' => self::bulkLabel($bulkSize), 'count' => $bulks, 'pieces' => $bulks * $bulkSize];
            }
            $rest = $pieces % $bulkSize;
            if ($rest > 0) {
                $lines[] = ['label' => 'حبة', 'count' => $rest, 'pieces' => $rest];
            }

            return $lines;
        }

        return self::breakdownLinesFlexible($pieces, $bulkSize);
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
                $lines[] = ['label' => 'عبوة ('.$bulkSize.' حبة)', 'count' => $count, 'pieces' => $count * $bulkSize];
                $remaining -= $count * $bulkSize;
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
        $mode = self::normalizeSaleMode($product->sale_mode ?? null);

        return $mode === self::MODE_BULK_ONLY ? 'سعر العبوة' : 'سعر الحبة';
    }

    public static function unitPriceForForm(string $unitKey, float $piecePrice, int $bulkSize): float
    {
        return round($piecePrice * self::multiplier($unitKey, $bulkSize), 2);
    }

    /**
     * @return array{qty: int, price: float}
     */
    public static function initialUnitInput(Product $product, string $unitKey): array
    {
        $pieces = 0;
        $piecePrice = (float) $product->pivot->sale_price;
        $bulkSize = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = self::normalizeSaleMode($product->sale_mode ?? null);
        $storedPieces = (int) ($product->pivot->quantity ?? 0);

        if ($mode === self::MODE_BULK_ONLY && $unitKey === 'bulk') {
            return [
                'qty' => $bulkSize > 0 ? intdiv($storedPieces, $bulkSize) : 0,
                'price' => self::unitPriceForForm('bulk', $piecePrice, $bulkSize),
            ];
        }

        if ($unitKey === 'piece') {
            return [
                'qty' => $storedPieces,
                'price' => $piecePrice,
            ];
        }

        return ['qty' => 0, 'price' => self::unitPriceForForm($unitKey, $piecePrice, $bulkSize)];
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array{quantity: int, sale_price: float}
     */
    public static function toPieceLine(array $line, int $piecesPerBulk, ?string $saleMode = null): array
    {
        $mode = self::normalizeSaleMode($saleMode);
        $allowedUnits = array_keys(self::unitsForOrderForm($piecesPerBulk, $mode));
        $totalPieces = 0;
        $lineTotal = 0.0;

        foreach ($allowedUnits as $unit) {
            $qty = max(0, (int) ($line[$unit]['qty'] ?? 0));
            $price = max(0, (float) ($line[$unit]['price'] ?? 0));

            if ($qty === 0) {
                continue;
            }

            $multiplier = self::multiplier($unit, $piecesPerBulk);
            $totalPieces += $qty * $multiplier;
            $lineTotal += $qty * $price;
        }

        if ($totalPieces < 1) {
            return ['quantity' => 0, 'sale_price' => 0];
        }

        return [
            'quantity' => $totalPieces,
            'sale_price' => round($lineTotal / $totalPieces, 4),
        ];
    }
}
