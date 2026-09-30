<?php

namespace App\Models;

use App\Support\DecimalMath;
use App\Support\SaleUnits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceItem extends Model
{
    protected $table = 'purchase_invoice_items';

    protected $fillable = [
        'purchase_invoice_id',
        'product_id',
        'purchase_unit',
        'entered_qty',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'entered_qty' => 'float',
        'quantity' => 'float',
        'price' => 'decimal:3',
        'subtotal' => 'decimal:3',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function getPurchaseUnitLabelAttribute(): string
    {
        $product = $this->relationLoaded('product') ? $this->product : $this->product()->first();
        if (! $product) {
            return $this->purchase_unit ?? 'حبة';
        }

        $units = SaleUnits::unitsForPurchaseForm(
            max(1, (int) ($product->pieces_per_carton ?? 12)),
            $product->measure_unit ?? null
        );

        return $units[$this->purchase_unit]['label'] ?? ($product->measure_unit === 'kilo' ? 'كيلو' : 'حبة');
    }

    /** الكمية الداخلة للمخزون بوحدة الإدخال: كرتونة أو حبة أو كيلو. */
    public function receivedStockLabel(): string
    {
        $product = $this->relationLoaded('product') ? $this->product : $this->product()->first();
        $measure = SaleUnits::normalizeMeasureUnit($product?->measure_unit ?? null);
        $unit = (string) ($this->purchase_unit ?? '');
        $entered = $this->entered_qty;

        if ($entered === null || (float) $entered <= 0) {
            $base = (float) $this->quantity;
            if ($measure === SaleUnits::UNIT_KILO) {
                return DecimalMath::display($base).' كيلو';
            }
            if ($measure === SaleUnits::UNIT_CARTON && $product) {
                $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));

                return DecimalMath::display($bulk > 0 ? $base / $bulk : $base).' كرتونة';
            }

            return DecimalMath::display($base).' حبة';
        }

        $label = match ($unit) {
            'bulk' => 'كرتونة',
            'half_carton' => 'نصف كرتونة',
            'kilo' => 'كيلو',
            'piece' => 'حبة',
            default => match ($measure) {
                SaleUnits::UNIT_KILO => 'كيلو',
                SaleUnits::UNIT_CARTON => 'كرتونة',
                default => 'حبة',
            },
        };

        return DecimalMath::display((float) $entered).' '.$label;
    }
}
