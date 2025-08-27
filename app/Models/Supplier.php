<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    // use HasFactory;

    protected $fillable = [
        'name',
        'balance',
        'phone',
    'address',
    ];

    /**
     * علاقة المورد مع فواتير الشراء
     */
    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'supplier_id');
    }
}
