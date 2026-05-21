<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    protected $fillable = [
        'payment_id',
        'filename',
        'sort_order',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('uploads/payment_receipts/'.$this->filename);
    }
}
