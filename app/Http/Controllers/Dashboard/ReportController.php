<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseInvoice;
use App\Models\Expense;
use App\Models\CashTransaction;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function profitLoss()
    {
        $e = CashTransaction::where('type', 'add')->where('category', 'direct')->sum('amount');
        // الإيرادات
        $b = Order::sum('total_price');
        $revenues = $e + $b;

        // المصروفات
        $c = CashTransaction::where('type', 'deduct')->where('category', 'direct')->sum('amount');

        $v = PurchaseInvoice::sum('total');
        $d = Expense::sum('amount');
        $expenses = $v + $d;
        // الأرباح = الإيرادات - المصروفات
        $profit = $revenues - $expenses;

        // رصيد الصندوق
        $cashIn = CashTransaction::where('type', 'add')->sum('amount');
        $cashOut = CashTransaction::where('type', 'deduct')->sum('amount');
        $cashBalance = $cashIn - $cashOut;

        return view('reports.profit_loss', compact('revenues', 'expenses', 'profit', 'cashBalance'));
    }

    public function salesReport(Request $request)
{
    $search = $request->search;

    $orders = Order::with('client')
        ->when($search, function ($q) use ($search) {
            $q->whereHas('client', function ($qq) use ($search) {
                $qq->where('name', 'like', "%$search%");
            });
        })
        ->latest()
        ->paginate(50);

    $totalSales = $orders->sum('total_price');

    // تجميع المبيعات حسب العميل (للكارت + الرسم البياني)
    $salesByClient = Order::with('client')
        ->selectRaw('client_id, SUM(total_price) as total')
        ->groupBy('client_id')
        ->get();

    return view('reports.sales', compact('orders', 'totalSales', 'salesByClient'));
}


   public function summary()
    {
       $sales = \App\Models\Order::selectRaw('DATE(created_at) as date, SUM(total_price) as total_sales')
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();

    $dates = $sales->pluck('date');
    $totals = $sales->pluck('total_sales');

    $totalSales = $sales->sum('total_sales');
    $totalOrders = \App\Models\Order::count();

        return view('reports.summary', compact('sales', 'dates', 'totals', 'totalSales', 'totalOrders'));
    }
    // تقرير المبيعات المفصل
    public function detailed()
    {
        $orders = Order::with(['client', 'products'])->latest()->paginate(50);

        return view('reports.detailed', compact('orders'));
    }

    // تقرير المبيعات حسب التصنيف
    public function byCategory()
    {
        $salesByCategory = DB::table('product_order')
        ->join('products', 'product_order.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->select(
            'categories.name as category_name',
            DB::raw('SUM(product_order.quantity * product_order.sale_price) as total_sales')
        )
        ->groupBy('categories.id', 'categories.name')
        ->orderByDesc('total_sales')
        ->get();

    $labels = $salesByCategory->pluck('category_name');
    $totals = $salesByCategory->pluck('total_sales');

    // return view('reports.by_category', compact('salesByCategory', 'labels', 'totals'))

        return view('reports.by_category', compact('salesByCategory', 'labels', 'totals'));
    }

    // تقرير الفواتير غير المسددة
    public function unpaid()
    {
        $unpaidOrders = Order::with('client', 'payments')
        ->where('remaining', '>', 0)
            ->withSum('payments', 'amount') // تحسب مجموع المدفوعات لكل طلب
            ->get()
            ->filter(fn($order) => $order->payments_sum_amount < $order->total_price);
        // return $unpaidOrders;
        return view('reports.unpaid', compact('unpaidOrders'));
    }
    // تقرير العملاء
    public function clientsReport()
    {
        $clients = Client::withCount('orders')->get();
        return view('reports.clients', compact('clients'));
    }
}
