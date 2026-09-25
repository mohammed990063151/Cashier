<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    protected $fillable = [
        'sdg_per_usd',
        'rate_date',
        'note',
        'user_id',
    ];

    protected $casts = [
        'sdg_per_usd' => 'float',
        'rate_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
