<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_invoice_id', 'product_id', 'quantity', 'price'];
    protected $table = 'purchase_invoice_items';

    // protected $fillable = ['purchase_invoice_id', 'product_id', 'quantity', 'price'];

    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
