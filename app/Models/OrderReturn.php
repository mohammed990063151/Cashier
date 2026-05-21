<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderReturn extends Model
{
    protected $fillable = [
        'order_id',
        'return_number',
        'items_total',
        'refund_amount',
        'remaining_reduced',
        'notes',
        'return_date',
    ];

    protected $casts = [
        'items_total' => 'float',
        'refund_amount' => 'float',
        'remaining_reduced' => 'float',
        'return_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(CashTransaction::class);
    }
}
