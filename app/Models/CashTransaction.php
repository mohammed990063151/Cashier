<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = [
        'type', 'amount', 'description', 'transaction_date', 'category','order_id','payment_id', 'purchase_invoice_id'
    ];

    public function order()
{
    return $this->belongsTo(Order::class);
}
 public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

}
