<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'amount',
        'method',
        'bank_receipt',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction()
    {
        return $this->hasOne(CashTransaction::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class)->orderBy('sort_order');
    }

    public function getBankReceiptUrlAttribute(): ?string
    {
        $urls = $this->bank_receipt_urls;

        return $urls[0] ?? null;
    }

    /**
     * @return list<string>
     */
    public function getBankReceiptUrlsAttribute(): array
    {
        if ($this->relationLoaded('receipts')) {
            if ($this->receipts->isNotEmpty()) {
                return $this->receipts->map(fn (PaymentReceipt $r) => $r->url)->all();
            }
        } elseif ($this->receipts()->exists()) {
            return $this->receipts()->get()->map(fn (PaymentReceipt $r) => $r->url)->all();
        }

        if ($this->bank_receipt) {
            return [asset('uploads/payment_receipts/'.$this->bank_receipt)];
        }

        return [];
    }

    public function hasBankReceipts(): bool
    {
        return count($this->bank_receipt_urls) > 0;
    }
}
