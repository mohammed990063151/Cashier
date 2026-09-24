<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Services\OrderFinancialService;
use Illuminate\Http\Request;

class ClientHistoryController extends Controller
{
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

        $orders = $query->paginate(20)->withQueryString();

        $rows = $orders->getCollection()->map(function (Order $order) use ($finance) {
            $calc = $finance->calculate($order);
            $statusKey = $finance->paymentStatus($order);

            return [
                'order' => $order,
                'client_name' => $order->client->name ?? '—',
                'total' => $calc['totalAfterDiscount'],
                'paid' => $calc['totalPaid'],
                'remaining' => $calc['remaining'],
                'discount' => $calc['invoiceDiscount'],
                'status' => $statusKey,
                'status_label' => $finance->paymentStatusLabel($statusKey),
                'status_class' => $finance->paymentStatusClass($statusKey),
                'products' => $order->products->map(fn ($p) => $finance->formatProductSaleLine($p))->all(),
            ];
        });

        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('dashboard.clients.history', [
            'orders' => $orders,
            'rows' => $rows,
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
}
