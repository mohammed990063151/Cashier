<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = ['supplier_id', 'amount', 'payment_date', 'note', 'purchase_invoice_id'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // app/Models/SupplierPayment.php

public function purchase_invoice()
{
    return $this->belongsTo(\App\Models\PurchaseInvoice::class, 'purchase_invoice_id');
}

}

