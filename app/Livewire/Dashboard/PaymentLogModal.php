<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Services\OrderFinancialService;
use Livewire\Attributes\On;
use Livewire\Component;

class PaymentLogModal extends Component
{
    public bool $show = false;

    public ?int $orderId = null;

    public function mount(): void
    {
        if ($logOrder = session()->pull('open_log_order')) {
            $this->open((int) $logOrder);
        } elseif (request()->filled('log_order')) {
            $this->open((int) request('log_order'));
        }
    }

    #[On('open-payment-log')]
    public function open(int|array $orderId): void
    {
        if (is_array($orderId)) {
            $orderId = (int) ($orderId['orderId'] ?? $orderId['order_id'] ?? 0);
        }

        if ($orderId < 1) {
            return;
        }

        $this->orderId = $orderId;
        $this->show = true;
    }

    #[On('close-payment-log')]
    public function close(): void
    {
        $this->show = false;
        $this->orderId = null;
    }

    public function render()
    {
        $order = null;
        $summary = [];

        if ($this->orderId) {
            $order = Order::with(['payments', 'client'])->find($this->orderId);
            if ($order) {
                $summary = app(OrderFinancialService::class)->calculate($order);
            }
        }

        return view('livewire.dashboard.payment-log-modal', [
            'order' => $order,
            'summary' => $summary,
        ]);
    }
}
