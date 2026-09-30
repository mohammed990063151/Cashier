<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            DB::raw('SUM(orders.remaining) as remaining_balance'),
            DB::raw(Schema::hasColumn('orders', 'written_off_amount')
                ? 'SUM(CASE WHEN orders.written_off_at IS NOT NULL THEN orders.written_off_amount ELSE 0 END) as written_off_total'
                : '0 as written_off_total')
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
    $debtRows = Order::query()
        ->whereIn('client_id', $clients->pluck('id'))
        ->where('remaining', '>', 0.009)
        ->get(['client_id', 'remaining', 'usd_rate'])
        ->groupBy('client_id');
    $debtRates = $debtRows->map(fn ($rows) => $rows->pluck('usd_rate'));
    $debtEntries = $debtRows->map(fn ($rows) => $rows->map(fn ($row) => [
        'amount' => (float) $row->remaining,
        'rate' => (float) $row->usd_rate,
    ])->all());

    return view('reports.clients.index', compact('clients', 'debtRates', 'debtEntries'));
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
                'paid' => $calc['netPaid'] ?? $calc['totalPaid'],
                'remaining' => $calc['remaining'],
                'usd_rate' => $order->usd_rate,
                'status' => $finance->paymentStatusLabel($finance->paymentStatus($order)),
                'written_off_amount' => (float) ($order->written_off_amount ?? 0),
                'written_off_note' => $order->written_off_note,
                'created_at' => $order->created_at,
                'is_opening' => (bool) $order->is_opening_balance,
                'opening_details' => $order->opening_details,
                'opening_photo' => $order->opening_photo,
            ];
        })->values();

        $productsFlat = $client->orders->flatMap->products;

        $productsSold = $productsFlat->groupBy('id')->map(function ($items) {
            $first = $items->first();
            $totalQty = $items->sum('pivot.quantity');
            $totalSales = $items->sum(fn ($p) => \App\Support\SaleUnits::lineMoney($p));
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
                'paid' => $calc['netPaid'] ?? $calc['totalPaid'],
                'remaining' => $calc['remaining'],
                'usd_rate' => $order->usd_rate,
                'status' => $finance->paymentStatusLabel($finance->paymentStatus($order)),
                'written_off_amount' => (float) ($order->written_off_amount ?? 0),
                'written_off_note' => $order->written_off_note,
                'payments' => $paidItems,
            ];
        })->values();

        $remainingBalance = (float) $invoices->sum('remaining');
        $writtenOffTotal = (float) $invoices->sum('written_off_amount');

        return view('reports.clients.show', compact(
            'client',
            'invoices',
            'productsSold',
            'statement',
            'remainingBalance',
            'writtenOffTotal'
        ));
    }

    // (اختياري) API endpoint لتحميل بيانات DataTables server-side لو احتجت
}
