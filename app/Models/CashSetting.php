<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSetting extends Model
{
    protected $fillable = [
        'add_sales',
        'add_client_payments',
        'deduct_purchases',
        'deduct_supplier_payments',
        'deduct_expenses'
    ];
}

