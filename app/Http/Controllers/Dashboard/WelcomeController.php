<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Expense;
use App\Models\Cash;
use Carbon\Carbon;

use App\Models\Supplier;
use App\Models\CashTransaction;
use Illuminate\Support\Facades\DB;
class WelcomeController extends Controller
{
     public function index(Request $request)
   {
        // إحصائيات عامة
        $categories_count = Category::count();
        $products_count   = Product::count();
        $clients_count    = Client::count();
        $users_count      = User::whereRoleIs('admin')->count();

        // مبيعات وأرباح
        $total_sales = Order::sum('total_price');
        $total_profit = Order::sum('profit');

        // مصروفات ومشتريات
        $total_expenses = Expense::sum('amount');
        $total_purchases = DB::table('purchase_invoices')->sum('total');

        // العملاء والموردين
        $total_due_clients = Order::sum('remaining');
        $total_due_suppliers = DB::table('suppliers')->sum('balance');

        // بيانات مالية
        $salesOverview = [
            'total_sales' => $total_sales,
        ];
        $profitsOverview = [
            'total_profit' => $total_profit,
        ];
        $expensesOverview = [
            'total_expenses' => $total_expenses,
        ];
        $purchasesOverview = [
            'total_purchases' => $total_purchases,
        ];
        $clientsOverview = [
            'total_due' => $total_due_clients,
        ];
        $suppliersOverview = [
            'total_due' => $total_due_suppliers,
        ];

        // الخزينة
        $cash = Cash::first();
        $cashOverview = [
            'balance' => $cash->balance ?? 0
        ];

        // حركة الخزينة اليومية
        $transactions = CashTransaction::orderBy('transaction_date', 'desc')->get();
        $dates = $transactions->pluck('transaction_date')->unique();

        $dailyAdded = [];
        $dailyDeducted = [];

        foreach ($dates as $date) {
            $dailyAdded[] = CashTransaction::whereDate('transaction_date', $date)
                ->where('type', 'add')
                ->sum('amount');

            $dailyDeducted[] = CashTransaction::whereDate('transaction_date', $date)
                ->where('type', 'deduct')
                ->sum('amount');
        }

        // أفضل المنتجات مبيعًا
        $topProducts = Product::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->take(5)
            ->get();
$startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    $ordersQuery = Order::query();

    if ($startDate && $endDate) {
        $ordersQuery->whereBetween('created_at', [$startDate, $endDate]);
    }

    $filteredSales = $ordersQuery->sum('total_price');
    $filteredProfit = $ordersQuery->sum('profit');

    $currentMonth = Carbon::now()->month;
$lastMonth = Carbon::now()->subMonth()->month;

$currentMonthSales = Order::whereMonth('created_at', $currentMonth)->sum('total_price');
$lastMonthSales = Order::whereMonth('created_at', $lastMonth)->sum('total_price');

$salesGrowth = $lastMonthSales > 0
    ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100
    : 0;

    return view('dashboard.reports.overview', compact(
        'filteredSales',
        'filteredProfit',
        'startDate',
        'endDate',
         'currentMonthSales',
    'lastMonthSales',
    'salesGrowth',
            'categories_count',
            'products_count',
            'clients_count',
            'users_count',
            'salesOverview',
            'profitsOverview',
            'expensesOverview',
            'purchasesOverview',
            'clientsOverview',
            'suppliersOverview',
            'cashOverview',
            'transactions',
            'dates',
            'dailyAdded',
            'dailyDeducted',
            'topProducts'
        ));
}//end of controller


public function trash()
{
    $categories = Category::onlyTrashed()->get();
    $clients = Client::onlyTrashed()->get();
    $expenses = Expense::onlyTrashed()->get();

    return view('dashboard.admin.trash', compact('categories', 'clients', 'expenses'));
}

public function restore($type, $id)
{
    switch ($type) {
        case 'categories':
            $model = Category::withTrashed()->findOrFail($id);
            break;
        case 'clients':
            $model = Client::withTrashed()->findOrFail($id);
            break;
        case 'expenses':
            $model = Expense::withTrashed()->findOrFail($id);
            break;
        default:
            abort(404);
    }

    $model->restore();

    return back()->with('success', "تم استرجاع $type بنجاح");
}

}
