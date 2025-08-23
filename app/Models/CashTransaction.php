<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = [
        'type', 'amount', 'description', 'transaction_date', 'category'
    ];
}
