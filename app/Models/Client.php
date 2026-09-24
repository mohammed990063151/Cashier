<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
      use SoftDeletes;
    protected $dates = ['deleted_at'];
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
        $finance = app(\App\Services\OrderFinancialService::class);
        $orders = $this->relationLoaded('orders')
            ? $this->orders
            : $this->orders()->with(['products', 'payments', 'returns'])->get();

        return $orders->sum(fn ($order) => (float) $finance->calculate($order)['remaining']);
    }



}//end of model
