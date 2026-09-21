<?php

namespace App\Services;

use App\Models\Product;
use App\Support\SaleUnits;
use Illuminate\Http\Request;

class OrderLineNormalizer
{
    /**
     * @return array<int, array{quantity: int, sale_price: float}>
     */
    public function fromRequest(Request $request): array
    {
        $normalized = [];

        foreach ($request->input('products', []) as $productId => $line) {
            if (! is_array($line)) {
                continue;
            }

            $product = Product::find($productId);
            if (! $product) {
                continue;
            }

            $piecesPerCarton = max(1, (int) ($product->pieces_per_carton ?? 12));
            $converted = SaleUnits::toPieceLine(
                $line,
                $piecesPerCarton,
                $product->sale_mode,
                $product->measure_unit ?? null
            );

            if ($converted['quantity'] > 0) {
                $normalized[(int) $productId] = $converted;
            }
        }

        return $normalized;
    }
}
