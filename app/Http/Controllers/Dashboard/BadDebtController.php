<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Services\BadDebtService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BadDebtController extends Controller
{
    public function __construct(private BadDebtService $badDebts) {}

    public function index()
    {
        abort_unless(auth()->user()->hasPermission('read_orders'), 403);

        $orders = Order::with('client')
            ->whereNotNull('written_off_at')
            ->orderByDesc('written_off_at')
            ->get();

        $summary = $this->badDebts->summary();

        return view('dashboard.bad_debts.index', compact('orders', 'summary'));
    }

    public function store(Request $request, Order $order)
    {
        abort_unless(auth()->user()->hasPermission('update_orders'), 403);

        $data = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $this->badDebts->writeOff($order, $data['note'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first('order'));
        }

        return back()->with('success', 'نُقل الطلب '.$order->order_number.' إلى الديون المعدومة، وخرج من التحصيل.');
    }

    public function storeClient(Request $request, Client $client)
    {
        abort_unless(auth()->user()->hasPermission('update_orders'), 403);

        $data = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $count = $this->badDebts->writeOffClient($client, $data['note'] ?? null);

        if ($count === 0) {
            return back()->with('error', 'لا يوجد متبقي على هذا العميل.');
        }

        return back()->with('success', 'نُقل '.$count.' طلب للعميل '.$client->name.' إلى الديون المعدومة.');
    }

    public function restore(Order $order)
    {
        abort_unless(auth()->user()->hasPermission('update_orders'), 403);

        $this->badDebts->restore($order);

        return back()->with('success', 'أُعيد الطلب '.$order->order_number.' إلى الذمم المستحقة.');
    }
}
