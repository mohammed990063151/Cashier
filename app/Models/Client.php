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



}//end of model
