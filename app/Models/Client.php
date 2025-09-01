<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $guarded = [];

    protected $casts = [
        'phone' => 'array'
    ];

    public function getNameAttribute($value)
    {
        return ucfirst($value);

    }//end of get name attribute

    public function orders()
    {
        return $this->hasMany(Order::class);

    }//end of orders
    public function saleInvoices()
{
    return $this->hasMany(SaleInvoice::class);
}

 public function getRemainingBalanceAttribute()
    {
        // eager loaded relations better: orders, orders.products, orders.payments
        $orders = $this->orders ?? $this->orders()->with(['products','payments'])->get();

        return $orders->sum(function($order){
            $total = $order->products->sum(fn($p) => $p->pivot->quantity * $p->pivot->sale_price);
            $paid  = $order->payments->sum('amount') + $order->discount ;
            // لو عندك عمود remaining في order ممكن تستخدمه بدل الحساب:
            return $order->remaining ?? ($total - $paid);
            // return ($total - $paid);
        });
    }



}//end of model
