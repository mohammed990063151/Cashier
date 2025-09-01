<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Order extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);

    }//end of user

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_order')->withPivot('quantity','sale_price');
}


public function payments()
{
    return $this->hasMany(Payment::class);
}

public function transactions()
{
    return $this->hasMany(CashTransaction::class, 'order_id');
}

public function getTotalAmountAttribute()
    {
        return $this->products->sum(fn($p) => $p->pivot->quantity * $p->pivot->sale_price);
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

}//end of model
