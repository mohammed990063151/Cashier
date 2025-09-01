<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashTransaction;
use App\Models\CashSetting;

class CashReportController extends Controller
{
   public function index(Request $request)
{
    $from = $request->get('from');
    $to   = $request->get('to');

    $transactions = CashTransaction::when($from && $to, fn($q)=>$q->whereBetween('transaction_date', [$from,$to]))
        ->with(['order','payment'])
        ->orderBy('transaction_date','asc')
        ->get();

    $totalAdded = $transactions->whereIn('type', ['add_sales','add_client_payments'])->sum('amount');
    $totalDeducted = $transactions->whereIn('type', ['deduct_purchases','deduct_supplier_payments','deduct_expenses'])->sum('amount');

    // بيانات مجملة يومية لكل نوع
    $dates = $transactions->pluck('transaction_date')->unique()->sort();
    $dailyAdded = [];
    $dailyDeducted = [];

    foreach($dates as $date){
        $dailyAdded[] = $transactions->where('transaction_date', $date)
                                     ->whereIn('type', ['add_sales','add_client_payments'])
                                     ->sum('amount');
        $dailyDeducted[] = $transactions->where('transaction_date', $date)
                                        ->whereIn('type', ['deduct_purchases','deduct_supplier_payments','deduct_expenses'])
                                        ->sum('amount');
    }

    return view('reports.cash.cash', compact('transactions','totalAdded','totalDeducted','dates','dailyAdded','dailyDeducted'));
}

}
