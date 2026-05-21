<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderFinancialService;
use App\Services\OrderReportsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function profitLoss()
    {
        $report = OrderReportsService::profitLossReport();

        return view('reports.profit_loss', ['report' => $report]);
    }

    public function salesReport(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;
        $search = $request->search;

        $ordersQuery = Order::with(['client', 'returns'])
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest();

        $orders = $ordersQuery->paginate(50)->withQueryString();
        $snapshot = OrderReportsService::snapshot($from, $to);

        $salesByClient = Order::with('client')
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->selectRaw('client_id, SUM(total_price) as net_total, SUM(total_return) as returned')
            ->groupBy('client_id')
            ->orderByDesc('net_total')
            ->get();

        $fin = app(OrderFinancialService::class);

        return view('reports.sales', compact(
            'orders',
            'snapshot',
            'salesByClient',
            'fin',
            'from',
            'to'
        ));
    }

    public function summary(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;

        $salesQuery = Order::query()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total_price) as net_sales')
            ->selectRaw('SUM(total_price + total_return) as gross_sales')
            ->groupBy('date')
            ->orderBy('date');

        if ($from && $to) {
            $salesQuery->whereBetween('created_at', [$from, $to]);
        }

        $sales = $salesQuery->get();
        $snapshot = OrderReportsService::snapshot($from, $to);

        return view('reports.summary', [
            'sales' => $sales,
            'dates' => $sales->pluck('date'),
            'netTotals' => $sales->pluck('net_sales'),
            'grossTotals' => $sales->pluck('gross_sales'),
            'snapshot' => $snapshot,
            'from' => $request->from,
            'to' => $request->to,
        ]);
    }

    public function detailed(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;

        $orders = Order::with(['client', 'products', 'payments', 'returns'])
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $snapshot = OrderReportsService::snapshot($from, $to);
        $fin = app(OrderFinancialService::class);

        return view('reports.detailed', compact('orders', 'snapshot', 'fin', 'from', 'to'));
    }

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
        $grandTotal = (float) $salesByCategory->sum('total_sales');

        return view('reports.by_category', compact('salesByCategory', 'labels', 'totals', 'grandTotal'));
    }

    public function unpaid()
    {
        $unpaidOrders = Order::with(['client', 'payments'])
            ->where('remaining', '>', 0)
            ->orderByDesc('remaining')
            ->get();

        $totalRemaining = (float) $unpaidOrders->sum('remaining');

        return view('reports.unpaid', compact('unpaidOrders', 'totalRemaining'));
    }

    public function clientsReport()
    {
        $clients = \App\Models\Client::withCount('orders')->get();

        return view('reports.clients', compact('clients'));
    }
}
