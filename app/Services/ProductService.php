<?php

namespace App\Services;

use App\Models\Product;
use App\Support\SaleUnits;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductService
{
    /**
     * @return Collection<int, Product>
     */
    public function listForIndex(Request $request): Collection
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
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function preparePayload(array $data): array
    {
        $mode = SaleUnits::normalizeSaleMode($data['sale_mode'] ?? null);

        return [
            'category_id' => (int) $data['category_id'],
            'name' => trim((string) $data['name']),
            'description' => $data['description'] ?? null,
            'purchase_price' => (float) $data['purchase_price'],
            'sale_price' => (float) $data['sale_price'],
            'stock' => (int) $data['stock'],
            'sale_mode' => $mode,
            'pieces_per_carton' => $this->resolvePiecesPerCarton($mode, $data['pieces_per_carton'] ?? 12),
        ];
    }

    public function resolvePiecesPerCarton(string $mode, mixed $value): int
    {
        if ($mode === SaleUnits::MODE_PIECE_ONLY) {
            return 1;
        }

        if ($mode === SaleUnits::MODE_BULK_ONLY) {
            return max(2, (int) $value);
        }

        return max(1, (int) $value);
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

    public function stockBadgeClass(int $stock): string
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
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);

        if ($mode === SaleUnits::MODE_PIECE_ONLY) {
            return '—';
        }

        return $bulk.' حبة / عبوة';
    }
}
