<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BadDebtService
{
    public function __construct(private OrderFinancialService $financial) {}

    public function writeOff(Order $order, ?string $note = null): Order
    {
        $order->loadMissing(['products', 'payments', 'returns']);

        if ($order->written_off_at) {
            return $order;
        }

        $remaining = (float) $this->financial->calculate($order)['remaining'];
        if ($remaining <= 0.009) {
            throw ValidationException::withMessages([
                'order' => 'لا يوجد متبقي على هذا الطلب لنقله إلى الديون المعدومة.',
            ]);
        }

        $order->written_off_at = now();
        $order->written_off_amount = round($remaining, 2);
        $order->written_off_note = $note !== null && trim($note) !== '' ? mb_substr(trim($note), 0, 500) : null;
        $order->save();

        return $this->financial->syncOrderTotals($order);
    }

    public function writeOffClient(Client $client, ?string $note = null): int
    {
        $orders = $client->orders()
            ->whereNull('written_off_at')
            ->where('remaining', '>', 0.009)
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $this->writeOff($order, $note);
            $count++;
        }

        return $count;
    }

    public function restore(Order $order): Order
    {
        $order->written_off_at = null;
        $order->written_off_amount = null;
        $order->written_off_note = null;
        $order->save();

        return $this->financial->syncOrderTotals($order);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $empty = [
            'bad_amount' => 0.0,
            'bad_count' => 0,
            'bad_clients' => 0,
            'bad_entries' => [],
            'active_amount' => 0.0,
            'active_count' => 0,
            'active_entries' => [],
            'orders_profit' => 0.0,
            'profit_after_bad_debt' => 0.0,
        ];

        if (! Schema::hasColumn('orders', 'written_off_at')) {
            return $empty;
        }

        $bad = Order::query()
            ->whereNotNull('written_off_at')
            ->get(['written_off_amount', 'usd_rate', 'client_id']);

        $active = Order::query()
            ->whereNull('written_off_at')
            ->where('remaining', '>', 0.009)
            ->get(['remaining', 'usd_rate']);

        $badAmount = round((float) $bad->sum('written_off_amount'), 2);
        $ordersProfit = round((float) Order::query()->withoutOpening()->sum('profit'), 2);

        return [
            'bad_amount' => $badAmount,
            'bad_count' => $bad->count(),
            'bad_clients' => $bad->pluck('client_id')->unique()->filter()->count(),
            'bad_entries' => $bad->map(fn ($row) => [
                'amount' => (float) $row->written_off_amount,
                'rate' => (float) ($row->usd_rate ?? 0),
            ])->all(),
            'active_amount' => round((float) $active->sum('remaining'), 2),
            'active_count' => $active->count(),
            'active_entries' => $active->map(fn ($row) => [
                'amount' => (float) $row->remaining,
                'rate' => (float) ($row->usd_rate ?? 0),
            ])->all(),
            'orders_profit' => $ordersProfit,
            'profit_after_bad_debt' => round($ordersProfit - $badAmount, 2),
        ];
    }
}
