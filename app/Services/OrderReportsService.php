<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderReportsService
{
    /**
     * ملخص مالي للطلبات والخزينة (للتقارير).
     *
     * @return array<string, float|int>
     */
    public static function snapshot(?Carbon $from = null, ?Carbon $to = null): array
    {
        $orderQuery = Order::query();
        if ($from && $to) {
            $orderQuery->whereBetween('created_at', [$from, $to]);
        }

        $netSales = (float) (clone $orderQuery)->sum('total_price');
        $returnsMerchandise = (float) (clone $orderQuery)->sum('total_return');
        $grossSales = round($netSales + $returnsMerchandise, 2);
        $totalRemaining = (float) (clone $orderQuery)->sum('remaining');
        $ordersProfit = (float) (clone $orderQuery)->sum('profit');
        $ordersCount = (int) (clone $orderQuery)->count();

        $returnQuery = OrderReturn::query();
        if ($from && $to) {
            $returnQuery->whereBetween('return_date', [$from->toDateString(), $to->toDateString()]);
        }
        $cashRefunded = (float) $returnQuery->sum('refund_amount');
        $returnsCount = (int) $returnQuery->count();

        $cashQuery = CashTransaction::query();
        if ($from && $to) {
            $cashQuery->whereBetween('transaction_date', [$from, $to]);
        }

        $cashInFromCustomers = (float) (clone $cashQuery)
            ->where('type', 'add')
            ->whereIn('category', ['payment', 'order'])
            ->sum('amount');

        $cashOutReturns = (float) (clone $cashQuery)
            ->where('type', 'deduct')
            ->where('category', 'returns')
            ->sum('amount');

        $cashInTotal = (float) (clone $cashQuery)->where('type', 'add')->sum('amount');
        $cashOutTotal = (float) (clone $cashQuery)->where('type', 'deduct')->sum('amount');

        $totalExpenses = (float) Expense::query()
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->sum('amount');

        $totalPurchasesPaid = (float) PurchaseInvoice::query()
            ->when($from && $to, fn ($q) => $q->whereBetween('invoice_date', [$from, $to]))
            ->sum('paid');

        $totalPurchasesVolume = (float) PurchaseInvoice::query()
            ->when($from && $to, fn ($q) => $q->whereBetween('invoice_date', [$from, $to]))
            ->sum('total');

        return [
            'orders_count' => $ordersCount,
            'gross_sales' => $grossSales,
            'net_sales' => round($netSales, 2),
            'returns_merchandise' => round($returnsMerchandise, 2),
            'cash_refunded' => round($cashRefunded, 2),
            'returns_count' => $returnsCount,
            'total_remaining' => round($totalRemaining, 2),
            'orders_profit' => round($ordersProfit, 2),
            'cash_in_from_customers' => round($cashInFromCustomers, 2),
            'cash_out_returns' => round($cashOutReturns, 2),
            'cash_in_total' => round($cashInTotal, 2),
            'cash_out_total' => round($cashOutTotal, 2),
            'expenses' => round($totalExpenses, 2),
            'purchases_paid' => round($totalPurchasesPaid, 2),
            'purchases_total' => round($totalPurchasesVolume, 2),
            'operating_profit' => round($ordersProfit - $totalExpenses, 2),
        ];
    }

    /**
     * @return array<int, array{date: string, net_sales: float, gross_sales: float}>
     */
    public static function dailySalesSeries(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = Order::query()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total_price) as net_sales')
            ->selectRaw('SUM(total_price + total_return) as gross_sales')
            ->groupBy('date')
            ->orderBy('date');

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return $query->get()->map(fn ($row) => [
            'date' => $row->date,
            'net_sales' => (float) $row->net_sales,
            'gross_sales' => (float) $row->gross_sales,
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function profitLossReport(): array
    {
        $s = self::snapshot();
        $cashBalance = app(CashService::class)->getBalance();

        $totalOutflows = $s['expenses'] + $s['purchases_paid'];
        $netResult = $s['orders_profit'] - $totalOutflows;

        return array_merge($s, [
            'cash_balance' => $cashBalance,
            'total_outflows' => round($totalOutflows, 2),
            'net_result' => round($netResult, 2),
        ]);
    }

    /**
     * @return array<string, float>
     */
    public static function cashCategoryBreakdown(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = CashTransaction::query();
        if ($from && $to) {
            $query->whereBetween('transaction_date', [$from, $to]);
        }

        $rows = (clone $query)
            ->selectRaw('category, type, SUM(amount) as total')
            ->groupBy('category', 'type')
            ->get();

        $breakdown = [];
        foreach ($rows as $row) {
            $key = ($row->category ?: 'other').'_'.$row->type;
            $breakdown[$key] = (float) $row->total;
        }

        return $breakdown;
    }

    /**
     * @return array<int, array{product: string, sales: float, cost: float, profit: float, margin_percent: float}>
     */
    public static function productProfitBreakdown(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = DB::table('product_order')
            ->join('products', 'product_order.product_id', '=', 'products.id')
            ->join('orders', 'product_order.order_id', '=', 'orders.id')
            ->select(
                'products.name as product_name',
                DB::raw('SUM(COALESCE(product_order.line_total, product_order.quantity * product_order.sale_price)) as sales'),
                DB::raw('SUM(product_order.quantity * product_order.cost_price) as cost'),
                DB::raw('SUM(COALESCE(product_order.line_total, product_order.quantity * product_order.sale_price) - product_order.quantity * product_order.cost_price) as profit')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('profit');

        if ($from && $to) {
            $query->whereBetween('orders.created_at', [$from, $to]);
        }

        return $query->get()->map(function ($row) {
            $sales = (float) $row->sales;
            $profit = (float) $row->profit;

            return [
                'product' => $row->product_name,
                'sales' => round($sales, 2),
                'cost' => round((float) $row->cost, 2),
                'profit' => round($profit, 2),
                'margin_percent' => $sales > 0 ? round(($profit / $sales) * 100, 2) : 0,
            ];
        })->all();
    }

    /**
     * @return array{sales: float, cost: float, profit: float}
     */
    public static function ordersProfitTotals(?Carbon $from = null, ?Carbon $to = null): array
    {
        $s = self::snapshot($from, $to);
        $profit = $s['orders_profit'];
        $sales = $s['net_sales'];

        return [
            'sales' => $sales,
            'cost' => round(max(0, $sales - $profit), 2),
            'profit' => $profit,
        ];
    }
}
