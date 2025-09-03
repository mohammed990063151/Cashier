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
use App\Models\CashTransaction;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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
        $total_due_clients = Client::sum('balance_due');
        $total_due_suppliers = DB::table('suppliers')->sum('balance_due');

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

        return view('dashboard.reports.overview', compact(
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
    }
}
