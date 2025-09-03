<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use Barryvdh\DomPDF\Facade\Mpdf;
use Mpdf\Mpdf;
use App\Services\CashService;
use App\Models\CashTransaction;
class OrderController extends Controller
{



public function show($id)
{
    $order = \App\Models\Order::with(['products', 'client', 'payments'])->findOrFail($id);

    // حساب إجمالي الشراء: كمية كل منتج × سعر الشراء من جدول products
    $totalPurchase = $order->products->sum(function ($product) {
        return $product->pivot->quantity * $product->purchase_price;
    });

    // حساب إجمالي البيع: كمية كل منتج × سعر البيع من جدول products
    $totalSale = $order->products->sum(function ($product) {
        return $product->pivot->quantity * $product->pivot->sale_price;
    });

    // حساب الربح والمكسب بالنسبة المئوية
    $profit = $totalSale - $totalPurchase;
    $profitPercentage = $totalPurchase > 0 ? ($profit / $totalPurchase) * 100 : 0;

    return view('dashboard.orders.order_details', compact(
        'order', 'totalPurchase', 'totalSale', 'profit', 'profitPercentage'
    ));
}

    public function index(Request $request)
    {
       $search = $request->search;

    $orders = Order::whereHas('client', function ($q) use ($search) {
        $q->where('name', 'like', '%' . $search . '%');
    })
    ->orWhere('order_number', 'like', '%' . $search . '%')
     ->orderBy('created_at', 'desc')
    ->paginate(10);

        return view('dashboard.orders.index', compact('orders'));

    }//end of index

    public function products(Order $order)
    {
        $products = $order->products;
        return view('dashboard.orders._products', compact('order', 'products'));

    }//end of products
public function generatePdf($orderId)
{
    $order = Order::with('products')->findOrFail($orderId);
    $products = $order->products;

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [80, 80], // مقاس الورق 80mm * 80mm
        'default_font' => 'dejavusans',
        'margin_left' => 2,
        'margin_right' => 2,
        'margin_top' => 2,
        'margin_bottom' => 2,
    ]);

    $html = view('pdf.order-invoice', compact('order', 'products'))->render();

    $mpdf->WriteHTML($html);
    return $mpdf->Output("receipt-{$order->id}.pdf", 'I'); // عرض مباشرة
}

  public function destroy(Order $order , CashService $cashService)
{
    // إعادة المنتجات إلى المخزون
    foreach ($order->products as $product) {
        $product->update([
            'stock' => $product->stock + $product->pivot->quantity
        ]);
    }

    // تحديث بيانات الطلب قبل الحذف
    $order->update([
        'total_return' => $order->total_price,
    ]);

    // Soft Delete
    $order->delete();

      // تحديث/حذف حركة الخزينة المرتبطة بالطلب
    $transaction = CashTransaction::where('order_id', $order->id)->first();
    if ($transaction) {
        $cashService->deleteTransaction($transaction);
    }
    session()->flash('success', "تم حذف الطلب (#{$order->order_number}) مؤقتاً.");
    return redirect()->route('dashboard.orders.index');
}
public function softdelet()
{
    // return 0;
    $orders = Order::onlyTrashed()->with('client')->paginate(10);

    return view('dashboard.orders.trashed', compact('orders'));
}


public function restore($id , CashService $cashService)
{
    $order = Order::withTrashed()->findOrFail($id);

    // استرجاع الطلب نفسه
    $order->restore();

    // استرجاع المنتجات المرتبطة
    foreach ($order->products as $product) {
        $product->update([
            'stock' => $product->stock - $product->pivot->quantity
        ]);
    }

    // إعادة القيم الأصلية إذا أحببت
    $order->update([
        'total_return' => 0,
        'remaining' => $order->remaining,
        'profit' => $order->profit, // حسب الحاجة
    ]);

     // تحديث أو إعادة تسجيل حركة الخزينة المرتبطة بالطلب
    $transaction = CashTransaction::where('order_id', $order->id)->first();
    if ($transaction) {
        $cashService->updateTransaction(
            $transaction,
            $order->discount,
            "استرجاع الدفعيات على الطلب رقم #{$order->order_number}   من العميل {$order->client->name}",
            'discount',
            now()
        );
    } elseif ($order->discount > 0) {
        $cashService->record(
            'add',
            $order->discount,
            "استرجاع الدفعيات على الطلب رقم #{$order->order_number} من العميل {$order->client->name}",
            'discount',
            now(),
            $order->id
        );
    }

    session()->flash('success', "تم استرجاع الطلب بالكامل: #{$order->order_number}");
    return redirect()->route('dashboard.orders.index');
}


}//end of controller
