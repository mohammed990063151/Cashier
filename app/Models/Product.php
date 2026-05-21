<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    public const DEFAULT_IMAGE = 'default.svg';

    public const LEGACY_DEFAULT_IMAGE = 'default.png';

    // use \Dimsav\Translatable\Translatable;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'purchase_price',
        'sale_price',
        'stock',
        'pieces_per_carton',
        'sale_mode',
        'image',
    ];

    public $translatedAttributes = ['name', 'description'];
    protected $appends = ['image_path', 'profit_percent'];


    public static function defaultImageUrl(): string
    {
        $uploaded = public_path('uploads/product_images/'.self::DEFAULT_IMAGE);
        if (file_exists($uploaded)) {
            return asset('uploads/product_images/'.self::DEFAULT_IMAGE);
        }

        return asset('images/product-default.svg');
    }

    public function getImagePathAttribute(): string
    {
        $directory = public_path('uploads/product_images/');
        $filename = $this->image;

        if ($filename && ! $this->usesDefaultImage()) {
            if (file_exists($directory.$filename)) {
                return asset('uploads/product_images/'.$filename);
            }
        }

        return self::defaultImageUrl();
    }

    public function usesDefaultImage(): bool
    {
        return in_array($this->image, [null, '', self::DEFAULT_IMAGE, self::LEGACY_DEFAULT_IMAGE], true);
    }

    public function getProfitPercentAttribute()
    {
        $purchase = (float) $this->purchase_price;
        if ($purchase <= 0) {
            return '0.00';
        }

        $profit = (float) $this->sale_price - $purchase;

        return number_format(($profit * 100) / $purchase, 2);
    }

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
