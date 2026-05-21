<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'total_price' => 'float',
        'paid_at_sale' => 'float',
        'invoice_discount' => 'float',
        'total_after_discount' => 'float',
        'remaining' => 'float',
        'profit' => 'float',
        'payment_due_at' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_order')
            ->withPivot('quantity', 'sale_price', 'cost_price');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentInstallments()
    {
        return $this->hasMany(OrderPaymentInstallment::class)->orderBy('sort_order')->orderBy('due_at');
    }

    public function unpaidInstallments()
    {
        return $this->paymentInstallments()->whereNull('paid_at');
    }

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class, 'order_id');
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function getTotalAmountAttribute()
    {
        return $this->products->sum(fn ($p) => $p->pivot->quantity * $p->pivot->sale_price);
    }

    /** المدفوع من جدول الدفعات + الدفعة عند البيع */
    public function getTotalPaidAttribute()
    {
        return (float) $this->paid_at_sale + (float) $this->payments->sum('amount');
    }

    public function getPaidAmountAttribute()
    {
        return $this->total_paid;
    }

    public function getRemainingAmountAttribute()
    {
        return max((float) $this->total_after_discount - $this->total_paid, 0);
    }

    public function getTotalProfitAttribute()
    {
        return $this->products->sum(function ($product) {
            return ($product->pivot->sale_price - $product->pivot->cost_price) * $product->pivot->quantity;
        });
    }
}
