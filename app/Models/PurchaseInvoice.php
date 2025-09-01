<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $fillable = ['supplier_id', 'total','remaining','paid','paid_amount','total_amount'];

    // public function items()
    // {
    //     return $this->hasMany(PurchaseInvoiceItem::class);
    // }
   public function items()
{
    return $this->hasMany(PurchaseInvoiceItem::class);
}


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function payments()
{
    return $this->hasMany(SupplierPayment::class, 'purchase_invoice_id');
}

}

