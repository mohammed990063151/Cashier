<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPaymentInstallment extends Model
{
    protected $fillable = [
        'purchase_invoice_id',
        'amount',
        'due_at',
        'paid_at',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'due_at' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }
}
