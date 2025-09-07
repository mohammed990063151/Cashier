<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleInvoice extends Model
{
    protected $fillable = ['invoice_number','client_id','invoice_date','total_amount'];

    public function items()
    {
        return $this->hasMany(SaleInvoiceItem::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }
}

