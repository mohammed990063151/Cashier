<?php

namespace App\Models;

use App\Support\SaleUnits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'supplier_id',
        'invoice_number',
        'invoice_date',
        'total',
        'paid',
        'remaining',
        'payment_due_at',
        'payment_notes',
        'paid_amount',
        'total_amount',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'payment_due_at' => 'date',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'remaining' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'purchase_invoice_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'purchase_invoice_id');
    }

    public function paymentInstallments(): HasMany
    {
        return $this->hasMany(SupplierPaymentInstallment::class)->orderBy('sort_order');
    }

    public function quantityLabelForItem(PurchaseInvoiceItem $item): string
    {
        $product = $item->product;
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = $product->sale_mode ?? null;
        $unitLabel = $item->purchase_unit_label ?? 'حبة';

        return $unitLabel.' × '.$item->entered_qty.' — '.SaleUnits::formatQuantityLabel(
            (int) $item->quantity,
            $bulk,
            $mode
        );
    }
}

