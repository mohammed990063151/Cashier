<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PriceHistory extends Model
{
    protected $fillable = ['product_id','old_price','new_price','type'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
