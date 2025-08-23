<?php

namespace App\Services;

use App\Models\SaleInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Product;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Expense;
use App\Models\CashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsService
{
    protected $startDate;
    protected $endDate;

    public function __construct($filter = 'today')
    {
        // تحديد نطاق التاريخ حسب الفلتر
        switch ($filter) {
            case 'yesterday':
                $this->startDate = Carbon::yesterday()->startOfDay();
                $this->endDate   = Carbon::yesterday()->endOfDay();
                break;
            case 'last7days':
                $this->startDate = Carbon::now()->subDays(7)->startOfDay();
                $this->endDate   = Carbon::now()->endOfDay();
                break;
            case 'this_month':
                $this->startDate = Carbon::now()->startOfMonth();
                $this->endDate   = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth();
                $this->endDate   = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                // سيتم ضبطه لاحقاً
                break;
            default:
                $this->startDate = Carbon::today()->startOfDay();
                $this->endDate   = Carbon::today()->endOfDay();
                break;
        }
    }

    public function setCustomRange($from, $to)
    {
        $this->startDate = Carbon::parse($from)->startOfDay();
        $this->endDate   = Carbon::parse($to)->endOfDay();
    }

    // ======== بيانات المبيعات ========
    public function salesOverview()
    {
        $sales = SaleInvoice::whereBetween('invoice_date', [$this->startDate, $this->endDate]);

        $totalSales      = $sales->sum('total_amount');
        $totalReturns    = $sales->sum('total_return') ?? 0;
        $netSales        = $totalSales - $totalReturns;
        $totalTax        = $sales->sum('tax_amount') ?? 0;
        $totalPaid       = $sales->sum('paid_amount') ?? 0;
        $remaining       = $netSales - $totalPaid;

        // المنتجات الأكثر مبيعاً
        $topProducts = DB::table('sale_invoice_items')
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(quantity*price) as total_amount'))
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->get();

        return compact(
            'totalSales', 'totalReturns', 'netSales', 'totalTax', 'totalPaid', 'remaining', 'topProducts'
        );
    }

    // ======== بيانات الأرباح ========
    public function profitsOverview()
    {
        $profitQuery = SaleInvoice::whereBetween('invoice_date', [$this->startDate, $this->endDate]);

        $totalProfit     = $profitQuery->sum('profit') ?? 0;
        $expenses        = Expense::whereBetween('created_at', [$this->startDate, $this->endDate])->sum('amount') ?? 0;
        $profitAfterCost = $totalProfit - $expenses;
        $profitPercent   = $totalProfit > 0 ? ($profitAfterCost / $totalProfit * 100) : 0;

        return compact('totalProfit', 'expenses', 'profitAfterCost', 'profitPercent');
    }

    // ======== بيانات العملاء ========
    public function clientsOverview()
    {
        $clients = Client::all();
        $overview = [];
        foreach ($clients as $client) {
            $totalInvoices  = $client->saleInvoices()->whereBetween('invoice_date', [$this->startDate, $this->endDate])->sum('total_amount');
            $paid           = $client->saleInvoices()->whereBetween('invoice_date', [$this->startDate, $this->endDate])->sum('paid_amount');
            $remaining      = $totalInvoices - $paid;
            $overview[]     = [
                'client'    => $client->name,
                'total'     => $totalInvoices,
                'paid'      => $paid,
                'remaining' => $remaining,
            ];
        }
        return $overview;
    }

    // ======== بيانات الموردين ========
    public function suppliersOverview()
    {
        $suppliers = Supplier::all();
        $overview = [];
        foreach ($suppliers as $supplier) {
            $totalPurchases  = $supplier->purchaseInvoices()->whereBetween('invoice_date', [$this->startDate, $this->endDate])->sum('total_amount');
            $paid            = $supplier->purchaseInvoices()->whereBetween('invoice_date', [$this->startDate, $this->endDate])->sum('paid_amount');
            $remaining       = $totalPurchases - $paid;
            $overview[] = [
                'supplier' => $supplier->name,
                'total'    => $totalPurchases,
                'paid'     => $paid,
                'remaining'=> $remaining,
            ];
        }
        return $overview;
    }

    // ======== بيانات المشتريات ========
    public function purchasesOverview()
    {
        $purchases = PurchaseInvoice::whereBetween('invoice_date', [$this->startDate, $this->endDate]);
        $totalPurchases = $purchases->sum('total');
        $totalPaid      = $purchases->sum('paid_amount');
        $remaining      = $totalPurchases - $totalPaid;

        return compact('totalPurchases', 'totalPaid', 'remaining');
    }

    // ======== بيانات المصروفات ========
    public function expensesOverview()
    {
        $totalExpenses = Expense::whereBetween('created_at', [$this->startDate, $this->endDate])->sum('amount');
        return compact('totalExpenses');
    }

    // ======== بيانات الخزينة ========
    public function cashOverview()
    {
        $totalCashIn  = CashTransaction::whereBetween('transaction_date', [$this->startDate, $this->endDate])
                        ->where('type', 'in')->sum('amount');
        $totalCashOut = CashTransaction::whereBetween('transaction_date', [$this->startDate, $this->endDate])
                        ->where('type', 'out')->sum('amount');
        return compact('totalCashIn', 'totalCashOut');
    }
}
