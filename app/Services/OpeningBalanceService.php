<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\UploadedFile;

class OpeningBalanceService
{
    public function create(Client $client, float $amount, float $alreadyPaid = 0, ?string $reference = null, ?string $details = null, ?string $date = null, ?UploadedFile $photo = null): Order
    {
        $amount = round(max(0, $amount), 2);
        $alreadyPaid = round(min(max(0, $alreadyPaid), $amount), 2);
        $photoPath = $photo?->store('opening-invoices', 'public_uploads');

        $order = Order::create([
            'client_id' => $client->id,
            'order_number' => $this->nextNumber(),
            'total_price' => $amount,
            'paid_at_sale' => $alreadyPaid,
            'invoice_discount' => 0,
            'total_after_discount' => $amount,
            'remaining' => round($amount - $alreadyPaid, 2),
            'profit' => 0,
            'usd_rate' => app(CurrencyService::class)->rate() ?: null,
            'is_opening_balance' => true,
            'opening_reference' => $reference !== null && trim($reference) !== '' ? trim($reference) : null,
            'opening_details' => $details !== null && trim($details) !== '' ? trim($details) : null,
            'opening_photo' => $photoPath,
            'opening_date' => $date ?: now()->toDateString(),
        ]);

        if ($alreadyPaid > 0.009) {
            $order->payments()->create([
                'amount' => $alreadyPaid,
                'method' => 'prior_balance',
                'notes' => 'مدفوع قبل إدخال الحساب إلى النظام',
            ]);
        }

        return $order;
    }

    protected function nextNumber(): string
    {
        do {
            $number = 'OLD-'.mt_rand(10000, 99999);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
