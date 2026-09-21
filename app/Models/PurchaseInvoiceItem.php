<?php

namespace App\Models;

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
        return $this->belongsTo(Product::class);
    }

    public function getPurchaseUnitLabelAttribute(): string
    {
        $product = $this->relationLoaded('product') ? $this->product : $this->product()->first();
        if (! $product) {
            return $this->purchase_unit ?? 'حبة';
        }

        $units = SaleUnits::unitsForOrderForm(
            max(1, (int) ($product->pieces_per_carton ?? 12)),
            $product->sale_mode,
            $product->measure_unit ?? null
        );

        return $units[$this->purchase_unit]['label'] ?? ($product->measure_unit === 'kilo' ? 'كيلو' : 'حبة');
    }
}
