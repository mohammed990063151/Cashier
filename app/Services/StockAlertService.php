<?php

namespace App\Services;

use App\Models\Product;
use App\Support\DecimalMath;
use App\Support\SaleUnits;
use Illuminate\Support\Collection;

class StockAlertService
{
    public function threshold(): int
    {
        return max(1, (int) config('cashier.low_stock_threshold', 10));
    }

    /**
     * منتجات نفد مخزونها أو أوشكت على النفاد.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function alerts(int $limit = 20): Collection
    {
        $threshold = $this->threshold();
        $productService = app(ProductService::class);

        return Product::query()
            ->where(function ($q) use ($threshold) {
                $q->where('stock', '<=', 0)
                    ->orWhere(function ($inner) use ($threshold) {
                        $inner->where('stock', '>', 0)
                            ->where('stock', '<=', $threshold);
                    });
            })
            ->orderBy('stock')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function (Product $product) use ($productService, $threshold) {
                $stock = DecimalMath::round((float) $product->stock);
                $level = $stock <= 0 ? 'out' : 'low';

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $stock,
                    'stock_display' => $productService->stockDisplay($product),
                    'level' => $level,
                    'level_label' => $level === 'out' ? 'نفد المخزون' : 'مخزون قليل',
                    'threshold' => $threshold,
                    'measure_unit' => SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null),
                    'url' => route('dashboard.products.edit', $product->id),
                ];
            });
    }

    public function alertsCount(): int
    {
        $threshold = $this->threshold();

        return (int) Product::query()
            ->where(function ($q) use ($threshold) {
                $q->where('stock', '<=', 0)
                    ->orWhere(function ($inner) use ($threshold) {
                        $inner->where('stock', '>', 0)
                            ->where('stock', '<=', $threshold);
                    });
            })
            ->count();
    }

    /**
     * @return array{count: int, threshold: int, alerts: array<int, array<string, mixed>>}
     */
    public function payload(int $limit = 20): array
    {
        $alerts = $this->alerts($limit);

        return [
            'count' => $this->alertsCount(),
            'threshold' => $this->threshold(),
            'alerts' => $alerts->values()->all(),
            'out_count' => $alerts->where('level', 'out')->count(),
            'low_count' => $alerts->where('level', 'low')->count(),
        ];
    }
}
