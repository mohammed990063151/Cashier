<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientReportController extends Controller
{
    // قائمة العملاء مع المتبقي (index)
    public function index(Request $request)
    {
        // return 0;
        // يمكن تطبيق بحث على الاسم أو الهاتف
        // $query = Client::query();

        // if ($request->filled('q')) {
        //     $q = $request->q;
        //     $query->where('name', 'like', "%{$q}%")
        //           ->orWhereJsonContains('phone', $q);
        // }

        // // جلب العملاء مع الحسابات المسبقة لتقليل الاستعلامات
        // $clients = $query->with(['orders.products','orders.payments'])->distinct()->get();

        // return view('reports.clients.index', compact('clients'));

           $query = Client::query()
        ->leftJoin('orders', 'orders.client_id', '=', 'clients.id')
        ->select(
            'clients.id',
            'clients.name',
            'clients.phone',
            DB::raw('COUNT(orders.id) as orders_count'),
            DB::raw('SUM(orders.remaining) as remaining_balance')
        )
        ->groupBy('clients.id', 'clients.name', 'clients.phone');

    // بحث
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function($sub) use ($q) {
            $sub->where('clients.name', 'like', "%{$q}%")
                ->orWhereJsonContains('clients.phone', $q);
        });
    }

    $clients = $query->distinct()->get();
     return view('reports.clients.index', compact('clients'));
    }

    // صفحة تفاصيل العميل: فواتيره - المنتجات المباعة - كشف الحساب
    public function show(Request $request, Client $client)
    {
        $finance = app(\App\Services\OrderFinancialService::class);
        $client->load(['orders.products', 'orders.payments', 'orders.returns']);

        $invoices = $client->orders->sortBy('created_at')->map(function ($order) use ($finance) {
            $calc = $finance->calculate($order);

            return (object) [
                'id' => $order->id,
                'order_number' => $order->order_number ?? $order->id,
                'total' => $calc['totalAfterDiscount'],
                'paid' => $calc['totalPaid'],
                'remaining' => $calc['remaining'],
                'status' => $finance->paymentStatusLabel($finance->paymentStatus($order)),
                'created_at' => $order->created_at,
            ];
        })->values();

        $productsFlat = $client->orders->flatMap->products;

        $productsSold = $productsFlat->groupBy('id')->map(function ($items) {
            $first = $items->first();
            $totalQty = $items->sum('pivot.quantity');
            $totalSales = $items->sum(fn ($p) => $p->pivot->quantity * $p->pivot->sale_price);
            $bulk = max(1, (int) ($first->pieces_per_carton ?? 12));
            $qtyLabel = \App\Support\SaleUnits::formatQuantityLabel(
                $totalQty,
                $bulk,
                $first->sale_mode ?? null,
                $first->measure_unit ?? null
            );

            return (object) [
                'product_id' => $first->id,
                'product_name' => $first->name,
                'quantity' => $totalQty,
                'quantity_label' => $qtyLabel,
                'total_sales' => $totalSales,
            ];
        })->values();

        $statement = $client->orders->sortBy('created_at')->map(function ($order) use ($finance) {
            $calc = $finance->calculate($order);
            $paidItems = $order->payments->map(fn ($pay) => [
                'payment_id' => $pay->id,
                'amount' => $pay->amount,
                'date' => $pay->created_at,
                'method' => $pay->method,
            ]);

            return (object) [
                'order_id' => $order->id,
                'order_number' => $order->order_number ?? $order->id,
                'date' => $order->created_at,
                'total' => $calc['totalAfterDiscount'],
                'paid' => $calc['totalPaid'],
                'remaining' => $calc['remaining'],
                'status' => $finance->paymentStatusLabel($finance->paymentStatus($order)),
                'payments' => $paidItems,
            ];
        })->values();

        return view('reports.clients.show', compact('client', 'invoices', 'productsSold', 'statement'));
    }

    // (اختياري) API endpoint لتحميل بيانات DataTables server-side لو احتجت
}
