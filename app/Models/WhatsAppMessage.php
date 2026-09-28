<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'order_id',
        'recipient',
        'phone',
        'phone_normalized',
        'message',
        'status',
        'reason',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
