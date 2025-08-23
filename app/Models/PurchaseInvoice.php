<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $fillable = ['supplier_id', 'total'];

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}

