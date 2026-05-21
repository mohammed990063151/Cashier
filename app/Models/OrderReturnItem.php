<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturnItem extends Model
{
    protected $fillable = [
        'order_return_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
