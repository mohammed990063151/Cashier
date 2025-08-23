<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashTransaction;

class CashTransactionController extends Controller
{
    public function index()
    {
        $transactions = CashTransaction::latest()->paginate(20);

        $cashIn = CashTransaction::where('type','in')->sum('amount');
        $cashOut = CashTransaction::where('type','out')->sum('amount');
        $balance = $cashIn - $cashOut;

        return view('cash_transactions.index', compact('transactions','balance'));
    }
}
