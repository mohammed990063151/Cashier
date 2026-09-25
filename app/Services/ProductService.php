<?php

namespace App\Services;

use App\Models\Product;
use App\Support\DecimalMath;
use App\Support\SaleUnits;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ProductService
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function listForIndex(Request $request): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('sale_mode'), function ($query) use ($request) {
                $query->where('sale_mode', $request->sale_mode);
            })
            ->when($request->filled('measure_unit'), function ($query) use ($request) {
                $query->where('measure_unit', $request->measure_unit);
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function preparePayload(array $data): array
    {
        $normalized = SaleUnits::normalizeProductEntry($data);

        return [
            'category_id' => (int) $data['category_id'],
            'name' => trim((string) $data['name']),
            'description' => $data['description'] ?? null,
            'purchase_price' => $normalized['purchase_price'],
            'sale_price' => $normalized['sale_price'],
            'stock' => $normalized['stock'],
            'sale_mode' => $normalized['sale_mode'],
            'measure_unit' => $normalized['measure_unit'],
            'pieces_per_carton' => $normalized['pieces_per_carton'],
        ];
    }

    public function canDelete(Product $product): bool
    {
        return ! $product->orders()->exists();
    }

    public function saleModeBadgeClass(string $mode): string
    {
        return match (SaleUnits::normalizeSaleMode($mode)) {
            SaleUnits::MODE_PIECE_ONLY => 'label-info',
            SaleUnits::MODE_BULK_ONLY => 'label-warning',
            default => 'label-primary',
        };
    }

    public function measureUnitBadgeClass(string $unit): string
    {
        return match (SaleUnits::normalizeMeasureUnit($unit)) {
            SaleUnits::UNIT_CARTON => 'label-warning',
            SaleUnits::UNIT_KILO => 'label-success',
            default => 'label-info',
        };
    }

    public function stockBadgeClass(float|int $stock): string
    {
        if ($stock <= 0) {
            return 'label-danger';
        }

        if ($stock <= 10) {
            return 'label-warning';
        }

        return 'label-success';
    }

    public function cartonSummary(Product $product): string
    {
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
        if ($measure === SaleUnits::UNIT_KILO) {
            return 'بالكيلو';
        }

        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);

        if ($mode === SaleUnits::MODE_PIECE_ONLY && $measure === SaleUnits::UNIT_PIECE) {
            return '—';
        }

        return $bulk.' حبة / كرتونة';
    }

    public function stockDisplay(Product $product): string
    {
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
        $mode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);
        $stock = DecimalMath::round($product->stock);
        $unit = SaleUnits::measureUnitShort($measure);
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));

        if ($measure === SaleUnits::UNIT_CARTON || ($mode === SaleUnits::MODE_BULK_ONLY && $bulk > 1)) {
            $cartons = $bulk > 0 ? DecimalMath::div($stock, $bulk) : 0;

            return DecimalMath::display($cartons).' كرتونة ('.DecimalMath::display($stock).' حبة)';
        }

        return DecimalMath::display($stock).' '.$unit;
    }

    public function priceDisplay(Product $product, string $type = 'sale'): string
    {
        $piece = (float) ($type === 'purchase' ? $product->purchase_price : $product->sale_price);
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);

        if ($measure === SaleUnits::UNIT_KILO) {
            $value = $piece;
            $text = DecimalMath::display($value).' / كيلو';
        } elseif (
            $mode === SaleUnits::MODE_BULK_ONLY
            || ($measure === SaleUnits::UNIT_CARTON && $bulk > 1 && $mode !== SaleUnits::MODE_PIECE_ONLY)
        ) {
            $value = DecimalMath::money($piece * $bulk);
            $text = DecimalMath::moneyDisplay($value).' / كرتونة';
        } else {
            $value = DecimalMath::money($piece);
            $text = DecimalMath::moneyDisplay($value).' / حبة';
        }

        $currency = app(CurrencyService::class);
        if ($currency->enabled()) {
            $text .= ' ≈ '.$currency->formatUsd($currency->toUsd($value));
        }

        return $text;
    }

    /**
     * سعر العرض في قائمة إضافة الطلب حسب طريقة البيع.
     * كرتونة فقط / وحدة كرتونة → سعر الكرتونة. حبة فقط → سعر الحبة.
     */
    public function orderListPriceDisplay(Product $product): string
    {
        $piece = (float) $product->sale_price;
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
        $currency = app(CurrencyService::class);

        if ($measure === SaleUnits::UNIT_KILO) {
            $value = $piece;
            $text = DecimalMath::display($value).' / كيلو';
        } elseif (
            $mode === SaleUnits::MODE_BULK_ONLY
            || ($measure === SaleUnits::UNIT_CARTON && $bulk > 1 && $mode !== SaleUnits::MODE_PIECE_ONLY)
        ) {
            $value = DecimalMath::money($piece * $bulk);
            $text = DecimalMath::moneyDisplay($value).' / كرتونة';
        } else {
            $value = DecimalMath::money($piece);
            $text = DecimalMath::moneyDisplay($value).' / حبة';
        }

        if ($currency->enabled()) {
            $text .= ' ≈ '.$currency->formatUsd($currency->toUsd($value));
        }

        return $text;
    }
}
