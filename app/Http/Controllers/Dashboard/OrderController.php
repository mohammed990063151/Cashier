<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use Barryvdh\DomPDF\Facade\Mpdf;
use Mpdf\Mpdf;
use App\Services\CashService;
use App\Models\CashTransaction;

class OrderController extends Controller
{

   protected $cashService;

    public function __construct(CashService $cashService)
    {
        $this->cashService = $cashService;
    }


    // public function show($id)
    // {
    //     $order = \App\Models\Order::with(['products', 'client', 'payments'])->findOrFail($id);

    //     // حساب إجمالي الشراء: كمية كل منتج × سعر الشراء من جدول products
    //     $totalPurchase = $order->products->sum(function ($product) {
    //         return $product->pivot->quantity * $product->pivot->cost_price;
    //     });

    //     // حساب إجمالي البيع: كمية كل منتج × سعر البيع من جدول products
    //     $totalSale = $order->products->sum(function ($product) {
    //         return $product->pivot->quantity * $product->pivot->sale_price;
    //     });

    //     // حساب الربح والمكسب بالنسبة المئوية
    //     $profit = $totalSale - $totalPurchase;
    //     $profitPercentage = $totalPurchase > 0 ? ($profit / $totalPurchase) * 100 : 0;

    //     return view('dashboard.orders.order_details', compact(
    //         'order',
    //         'totalPurchase',
    //         'totalSale',
    //         'profit',
    //         'profitPercentage'
    //     ));
    // }
public function show($id)
{
    $order = \App\Models\Order::with(['products', 'client', 'payments'])->findOrFail($id);

    // إجمالي الشراء
    $totalPurchase = $order->products->sum(function ($product) {
        return $product->pivot->quantity * $product->pivot->cost_price;
    });

    // إجمالي البيع
    $totalSale = $order->products->sum(function ($product) {
        return $product->pivot->quantity * $product->pivot->sale_price;
    });

    // الخصم من الطلب
    $discount = $order->tax_amount ?? 0;

    // الإجمالي بعد الخصم
    $totalAfterDiscount = max($totalSale - $discount, 0);

    // الربح قبل الخصم
    $profit = $totalSale - $totalPurchase;

    // الربح بعد الخصم
    $profitAfterDiscount = $totalAfterDiscount - $totalPurchase;

    // نسبة الربح (على الشراء)
    $profitPercentage = $totalPurchase > 0 ? ($profitAfterDiscount / $totalPurchase) * 100 : 0;

    return view('dashboard.orders.order_details', compact(
        'order',
        'totalPurchase',
        'totalSale',
        'discount',
        'totalAfterDiscount',
        'profit',
        'profitAfterDiscount',
        'profitPercentage'
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
    } //end of index

    public function products(Order $order)
    {
        $products = $order->products;
        return view('dashboard.orders._products', compact('order', 'products'));
    } //end of products
    public function generatePdf($orderId)
    {
        $order = Order::with('products')->findOrFail($orderId);
        $products = $order->products;
        $setting = Setting::first();
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 80], // مقاس الورق 80mm * 80mm
            'default_font' => 'dejavusans',
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
            'isRemoteEnabled' => true,
        ]);

        $html = view('pdf.order-invoice', compact('order', 'products', 'setting'))->render();

        $mpdf->WriteHTML($html);
        return $mpdf->Output("receipt-{$order->id}.pdf", 'I'); // عرض مباشرة
    }

    //   public function destroy(Order $order , CashService $cashService)
    // {
    //     // إعادة المنتجات إلى المخزون
    //     foreach ($order->products as $product) {
    //         $product->update([
    //             'stock' => $product->stock + $product->pivot->quantity
    //         ]);
    //     }

    //     // تحديث بيانات الطلب قبل الحذف
    //     $order->update([
    //         'total_return' => $order->total_price,
    //     ]);

    //     // Soft Delete
    //     $order->delete();

    //       // تحديث/حذف حركة الخزينة المرتبطة بالطلب
    //     $transaction = CashTransaction::where('order_id', $order->id)->first();
    //     if ($transaction) {
    //         $cashService->deleteTransaction($transaction);
    //     }
    //     session()->flash('success', "تم حذف الطلب (#{$order->order_number}) مؤقتاً.");
    //     return redirect()->route('dashboard.orders.index');
    // }
    public function destroy(Order $order, CashService $cashService)
    {
        // return 0;
        // البحث عن حركة الخزينة المرتبطة بالطلب
        $transaction = CashTransaction::where('order_id', $order->id)->first();

        // التحقق من أن الرصيد في الخزينة يكفي لإرجاع المبلغ إذا كان هناك حركة
        if ($transaction && $cashService->getBalance() < $transaction->amount) {
            return redirect()->back()->with('error', '⚠️ الرصيد في الصندوق غير كافٍ لاسترجاع مبلغ الطلب!');
        }

        // إعادة المنتجات إلى المخزون
        foreach ($order->products as $product) {
            $product->update([
                'stock' => $product->stock + $product->pivot->quantity
            ]);
        }

        // تحديث بيانات الطلب قبل الحذف (مثلاً لتسجيل الإجمالي المسترجع)
        $order->update([
            'total_return' => $order->total_price,
        ]);

        // Soft Delete
        $order->delete();

        // حذف حركة الخزينة المرتبطة بالطلب
        if ($transaction) {
            $cashService->deleteTransaction($transaction);
        }

        session()->flash('success', "تم حذف الطلب (#{$order->order_number}) مؤقتاً.");
        return redirect()->route('dashboard.orders.index');
    }

   public function softdelet()
{
    $orders = Order::onlyTrashed()->with('client')->paginate(10);

    // رصيد الخزينة الحالي
    $balance = $this->cashService->getBalance();

    // تحقق: إذا فيه أي طلب مدفوع أكبر من رصيد الخزينة
    $hasProblem = $orders->contains(function ($order) use ($balance) {
        return $order->paid > $balance;
    });

    if ($hasProblem) {
        session()->flash('error', '⚠️ يوجد طلب مدفوع أكبر من رصيد الخزينة الحالي!');
    }

    return view('dashboard.orders.trashed', compact('orders'));
}



    public function restore($id, CashService $cashService)
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
