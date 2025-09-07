<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    // use \Dimsav\Translatable\Translatable;
//   use SoftDeletes;
    protected $guarded = ['id'];

    public $translatedAttributes = ['name', 'description'];
    protected $appends = ['image_path', 'profit_percent'];


  public function getImagePathAttribute()
{
    if ($this->image && file_exists(public_path('uploads/product_images/' . $this->image))) {
        return asset('uploads/product_images/' . $this->image);
    }
    return asset('uploads/product_images/default.png');
}

    public function getProfitPercentAttribute()
    {
        $profit = $this->sale_price - $this->purchase_price;
        $profit_percent = $profit * 100 / $this->purchase_price;
        return number_format($profit_percent, 2);

    }//end of get profit attribute

    public function category()
    {
        return $this->belongsTo(Category::class)->withTrashed();

    }//end fo category

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'product_order')->withPivot('quantity','sale_price','cost_price');

    }//end of orders
protected static function booted()
{
    static::updating(function ($product) {
        if ($product->isDirty('purchase_price')) {
            PriceHistory::create([
                'product_id' => $product->id,
                'old_price'  => $product->getOriginal('purchase_price'),
                'new_price'  => $product->purchase_price,
                'type'       => 'purchase',
            ]);
        }

        if ($product->isDirty('sale_price')) {
            PriceHistory::create([
                'product_id' => $product->id,
                'old_price'  => $product->getOriginal('sale_price'),
                'new_price'  => $product->sale_price,
                'type'       => 'sale',
            ]);
        }
    });
}



}//end of model
