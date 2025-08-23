<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleInvoiceItem extends Model
{
    protected $fillable = ['sale_invoice_id','product_id','product_name','quantity','unit_price','subtotal'];

    public function invoice()
    {
        return $this->belongsTo(SaleInvoice::class);
    }
}

