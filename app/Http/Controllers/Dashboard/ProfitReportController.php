<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderFinancialService;
use App\Services\OrderReportsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfitReportController extends Controller
{
    public function detailed(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;

        $orders = Order::with(['client', 'products', 'returns'])
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->latest()
            ->get();

        $totals = OrderReportsService::ordersProfitTotals($from, $to);
        $fin = app(OrderFinancialService::class);

        return view('reports.profit.profit_detailed', compact('orders', 'totals', 'fin', 'from', 'to'));
    }

    public function summary(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;

        $totals = OrderReportsService::ordersProfitTotals($from, $to);
        $snapshot = OrderReportsService::snapshot($from, $to);

        return view('reports.profit.profit_summary', [
            'totalSales' => $totals['sales'],
            'totalCost' => $totals['cost'],
            'totalProfit' => $totals['profit'],
            'snapshot' => $snapshot,
            'from' => $request->from,
            'to' => $request->to,
        ]);
    }

    public function productRatio(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;

        $productProfits = OrderReportsService::productProfitBreakdown($from, $to);

        return view('reports.profit.profit_ratio', compact('productProfits', 'from', 'to'));
    }
}
