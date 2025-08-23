<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Revenue;
use App\Models\Expense;
use App\Models\CashTransaction;
use App\Models\Order;
use App\Models\Client;

class ReportController extends Controller
{
    public function profitLoss()
    {
        // الإيرادات
        $revenues = Revenue::sum('amount');

        // المصروفات
        $expenses = Expense::sum('amount');

        // الأرباح = الإيرادات - المصروفات
        $profit = $revenues - $expenses;

        // رصيد الصندوق
        $cashIn = CashTransaction::where('type','in')->sum('amount');
        $cashOut = CashTransaction::where('type','out')->sum('amount');
        $cashBalance = $cashIn - $cashOut;

        return view('reports.profit_loss', compact('revenues','expenses','profit','cashBalance'));
    }

      public function salesReport()
    {
        $orders = Order::where('status','completed')->latest()->paginate(20);
        $totalSales = $orders->sum('total_price');
        return view('reports.sales', compact('orders','totalSales'));
    }

    // تقرير الأرباح والخسائر
    // public function profitLoss()
    // {
    //     $salesTotal = Order::where('status', 'completed')->sum('total_price');
    //     $expensesTotal = Expense::sum('amount');
    //     $netProfit = $salesTotal - $expensesTotal;
    //     return view('reports.profit_loss', compact('salesTotal','expensesTotal','netProfit'));
    // }

    // تقرير العملاء
    public function clientsReport()
    {
        $clients = Client::withCount('orders')->get();
        return view('reports.clients', compact('clients'));
    }
}
