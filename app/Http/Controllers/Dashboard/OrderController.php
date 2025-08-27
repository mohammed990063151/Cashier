<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use Barryvdh\DomPDF\Facade\Mpdf;
use Mpdf\Mpdf;
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
    ->orWhere('order_number', 'like', '%' . $search . '%') // البحث برقم الطلب
    ->paginate(5);

        return view('dashboard.orders.index', compact('orders'));

    }//end of index

    public function products(Order $order)
    {
        $products = $order->products;
        return view('dashboard.orders._products', compact('order', 'products'));

    }//end of products



// public function generatePdf($orderId)
// {
//     $order = Order::with('products')->findOrFail($orderId);
//     $products = $order->products;

//     $pdf = Pdf::loadView('pdf.order-invoice', compact('order', 'products'))
//         ->setPaper('a4', 'portrait')
//         ->setOptions([
//             'defaultFont' => 'dejavusans',
//             'isHtml5ParserEnabled' => true,
//             'isRemoteEnabled' => true,
//         ]);

//     return $pdf->stream('invoice-' . $order->id . '.pdf');
// }
public function generatePdf($orderId)
{
    $order = Order::with('products')->findOrFail($orderId);
    $products = $order->products;

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'dejavusans', // خط يدعم العربية
        'margin_left' => 10,
        'margin_right' => 10,
    ]);

    $html = view('pdf.order-invoice', compact('order', 'products'))->render();

    $mpdf->WriteHTML($html);
    // return $mpdf->Output("invoice-{$order->id}.pdf", 'I'); // عرض في المتصفح
     return $mpdf->Output("invoice-{$order->id}.pdf", 'D');
}

    public function destroy(Order $order)
    {
        foreach ($order->products as $product) {

            $product->update([
                'stock' => $product->stock + $product->pivot->quantity
            ]);

        }//end of for each

        $order->delete();
        session()->flash('success', __('تم الحذف بنجاح'));
        return redirect()->route('dashboard.orders.index');

    }//end of order

//     public function showAjax(Order $order)
// {
//     return view('dashboard.orders.order_details', compact('order'));
// }

}//end of controller
