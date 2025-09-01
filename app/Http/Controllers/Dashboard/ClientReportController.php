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
        // يمكن تطبيق بحث على الاسم أو الهاتف
        $query = Client::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('name', 'like', "%{$q}%")
                  ->orWhereJsonContains('phone', $q);
        }

        // جلب العملاء مع الحسابات المسبقة لتقليل الاستعلامات
        $clients = $query->with(['orders.products','orders.payments'])->get();

        return view('reports.clients.index', compact('clients'));
    }

    // صفحة تفاصيل العميل: فواتيره - المنتجات المباعة - كشف الحساب
    public function show(Request $request, Client $client)
    {
        // eager load orders, products, payments
       $client->load(['orders.products', 'orders.payments']);
// return $d ;
        // فواتير العميل (مع مجموعات لكل فاتورة)
        $invoices = $client->orders->map(function($order){
            $total = $order->products->sum(fn($p) => $p->pivot->quantity * $p->pivot->sale_price);
            $paid  = $order->payments->sum('amount') + $order->discount;
            return (object)[
                'id' => $order->id,
                'order_number' => $order->order_number  ?? $order->id,
                'total' =>  $order->total_price ?? $total,
                'paid' => $paid,
                'remaining' => $order->remaining ?? $total - $paid,
                'created_at' => $order->created_at,
            ];
        });
// return $invoices;
        // منتجات مباعة للعميل: تجميع عبر الـ orders -> products
        $productsFlat = $client->orders->flatMap->products;

        $productsSold = $productsFlat->groupBy('id')->map(function($items) {
            $first = $items->first();
            $totalQty = $items->sum('pivot.quantity');
            $totalSales = $items->sum(fn($p) => $p->pivot->quantity * $p->pivot->sale_price);
            return (object)[
                'product_id' => $first->id,
                'product_name' => $first->name,
                'quantity' => $totalQty,
                'total_sales' => $totalSales,
            ];
        })->values();

        // كشف الحساب بالتفصيل (يمكن تضمين تواريخ، مدفوعات متكررة...)
        $statement = $client->orders->map(function($order){
            $total = $order->products->sum(fn($p) => $p->pivot->quantity * $p->pivot->sale_price);
              $paid  = $order->payments->sum('amount') + $order->discount;
            $paidItems = $order->payments->map(fn($pay) => [
                'payment_id' => $pay->id,
                'amount' => $pay->amount,
                'date' => $pay->created_at,
            ]);
            return (object)[
                'order_id' => $order->id,
                'order_number' => $order->order_number ?? $order->id,
                'date' => $order->created_at,
                'total' => $order->total_price ??  $total,
                'paid' => $paid ?? $order->payments->sum('amount'),
                'remaining' =>$order->remaining ??  $total - $order->payments->sum('amount'),
                'payments' => $paidItems,
            ];
        });

        // إجمالي المتبقي (من accessor)
        $remainingBalance = $client->remaining_balance;

        return view('reports.clients.show', compact(
            'client','invoices','productsSold','statement','remainingBalance'
        ));
    }

    // (اختياري) API endpoint لتحميل بيانات DataTables server-side لو احتجت
}
