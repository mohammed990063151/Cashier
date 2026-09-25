<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Services\OrderFinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ClientHistoryController extends Controller
{
    /** @var list<string> */
    private const STATUS_ORDER = ['unpaid', 'partial', 'partial_return', 'returned', 'paid'];

    public function index(Request $request, OrderFinancialService $finance)
    {
        $clientId = $request->integer('client_id') ?: null;
        $orderNumber = trim((string) $request->get('order_number', ''));
        $clientName = trim((string) $request->get('client', ''));
        $status = $request->get('status', 'all');
        $unpaidOnly = $request->boolean('unpaid_only');

        $query = Order::with(['client', 'products', 'payments', 'returns'])
            ->orderBy('created_at'); // أقدم أولاً

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($orderNumber !== '') {
            $query->where('order_number', 'like', '%'.$orderNumber.'%');
        }

        if ($clientName !== '') {
            $query->whereHas('client', fn ($q) => $q->where('name', 'like', '%'.$clientName.'%'));
        }

        if ($unpaidOnly) {
            $query->where('remaining', '>', 0);
        }

        $finance->applyPaymentStatusFilter($query, $unpaidOnly ? null : $status);

        $orders = $query->limit(500)->get();

        $rows = $orders->map(function (Order $order) use ($finance) {
            $calc = $finance->calculate($order);
            $statusKey = $finance->paymentStatus($order);

            return [
                'order' => $order,
                'client_name' => $order->client->name ?? '—',
                'total' => (float) $calc['totalAfterDiscount'],
                'paid' => (float) ($calc['netPaid'] ?? $calc['totalPaid']),
                'remaining' => (float) $calc['remaining'],
                'discount' => (float) $calc['invoiceDiscount'],
                'status' => $statusKey,
                'status_label' => $finance->paymentStatusLabel($statusKey),
                'status_class' => $finance->paymentStatusClass($statusKey),
                'products' => $order->products->map(fn ($p) => $finance->formatProductSaleLine($p))->all(),
            ];
        });

        $groups = $this->buildStatusGroups($rows, $finance);

        $grand = [
            'count' => $rows->count(),
            'total' => (float) $rows->sum('total'),
            'paid' => (float) $rows->sum('paid'),
            'remaining' => (float) $rows->sum('remaining'),
        ];

        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('dashboard.clients.history', [
            'groups' => $groups,
            'grand' => $grand,
            'clients' => $clients,
            'filters' => [
                'client_id' => $clientId,
                'order_number' => $orderNumber,
                'client' => $clientName,
                'status' => $status,
                'unpaid_only' => $unpaidOnly,
            ],
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function buildStatusGroups(Collection $rows, OrderFinancialService $finance): Collection
    {
        $byStatus = $rows->groupBy('status');

        return collect(self::STATUS_ORDER)
            ->filter(fn (string $key) => $byStatus->has($key) && $byStatus->get($key)->isNotEmpty())
            ->map(function (string $key) use ($byStatus, $finance) {
                /** @var Collection<int, array<string, mixed>> $items */
                $items = $byStatus->get($key);

                return [
                    'status' => $key,
                    'status_label' => $finance->paymentStatusLabel($key),
                    'status_class' => $finance->paymentStatusClass($key),
                    'count' => $items->count(),
                    'total' => (float) $items->sum('total'),
                    'paid' => (float) $items->sum('paid'),
                    'remaining' => (float) $items->sum('remaining'),
                    'rows' => $items->values(),
                ];
            })
            ->values();
    }
}
