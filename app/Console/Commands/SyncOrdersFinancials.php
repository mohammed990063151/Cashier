<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderFinancialService;
use Illuminate\Console\Command;

class SyncOrdersFinancials extends Command
{
    protected $signature = 'orders:sync-financials';

    protected $description = 'Sync order totals, fix legacy payment fields, and register missing sale payments';

    public function handle(OrderFinancialService $financial): int
    {
        $count = 0;

        Order::with(['products', 'payments'])->chunkById(100, function ($orders) use ($financial, &$count) {
            foreach ($orders as $order) {
                $before = $order->only(['paid_at_sale', 'invoice_discount', 'remaining']);
                $financial->syncOrderTotals($order);
                $financial->recordInitialPayment($order->fresh(), (float) $order->paid_at_sale);
                $count++;
                $this->line("Order #{$order->order_number} synced");
            }
        });

        $this->info("Synced {$count} orders.");

        return self::SUCCESS;
    }
}
