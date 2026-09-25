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
            ->withPivot('quantity', 'sale_price', 'cost_price', 'line_total');
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
        return $this->products->sum(fn ($p) => \App\Support\SaleUnits::lineMoney($p));
    }

    /** إجمالي ما دفعه العميل (بدون خصم الاستردادات) */
    public function getTotalPaidAttribute()
    {
        $paymentsTotal = (float) $this->payments->sum('amount');
        if ($paymentsTotal > 0) {
            return $paymentsTotal;
        }

        return (float) ($this->paid_at_sale ?? 0);
    }

    /** صافي المدفوع بعد خصم المبالغ المستردة نقداً */
    public function getNetPaidAttribute()
    {
        $refunded = 0.0;
        if ($this->relationLoaded('returns')) {
            $refunded = (float) $this->returns->sum('refund_amount');
        } else {
            $refunded = (float) $this->returns()->sum('refund_amount');
        }

        return max(0, round($this->total_paid - $refunded, 2));
    }

    public function getPaidAmountAttribute()
    {
        return $this->net_paid;
    }

    public function getRemainingAmountAttribute()
    {
        $afterDiscount = (float) ($this->total_after_discount ?? $this->total_price ?? 0);

        return max($afterDiscount - $this->net_paid, 0);
    }

    public function getTotalProfitAttribute()
    {
        return $this->products->sum(function ($product) {
            return ($product->pivot->sale_price - $product->pivot->cost_price) * $product->pivot->quantity;
        });
    }
}
