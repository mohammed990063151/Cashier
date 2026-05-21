<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Services\CashService;
use App\Services\OrderReportsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;

        $transactions = CashTransaction::query()
            ->when($from && $to, fn ($q) => $q->whereBetween('transaction_date', [$from, $to]))
            ->with(['order.client', 'payment.order', 'orderReturn.order.client'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $totalAdded = (float) $transactions->where('type', 'add')->sum('amount');
        $totalDeducted = (float) $transactions->where('type', 'deduct')->sum('amount');
        $netFiltered = $totalAdded - $totalDeducted;
        $totalReturnsOut = (float) $transactions->where('category', 'returns')->where('type', 'deduct')->sum('amount');
        $cashBalance = app(CashService::class)->getBalance();

        $dates = $transactions->pluck('transaction_date')->unique()->sort()->values();
        $dailyAdded = [];
        $dailyDeducted = [];

        foreach ($dates as $date) {
            $day = $transactions->where('transaction_date', $date);
            $dailyAdded[] = (float) $day->where('type', 'add')->sum('amount');
            $dailyDeducted[] = (float) $day->where('type', 'deduct')->sum('amount');
        }

        $cashSnapshot = OrderReportsService::snapshot($from, $to);

        return view('reports.cash.cash', compact(
            'transactions',
            'totalAdded',
            'totalDeducted',
            'netFiltered',
            'totalReturnsOut',
            'cashBalance',
            'dates',
            'dailyAdded',
            'dailyDeducted',
            'cashSnapshot',
            'from',
            'to'
        ));
    }
}
