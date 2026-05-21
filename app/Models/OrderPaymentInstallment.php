<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPaymentInstallment extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'due_at',
        'paid_at',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'float',
        'due_at' => 'date',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }
}
