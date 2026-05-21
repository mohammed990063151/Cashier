<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashTransaction;
use App\Services\CashService;

class CashTransactionController extends Controller
{
    public function index(CashService $cashService)
    {
        $transactions = CashTransaction::latest()->orderBy('created_at', 'desc')->paginate(20);

        $cashIn = CashTransaction::where('type', 'add')->sum('amount');
        $cashOut = CashTransaction::where('type', 'deduct')->sum('amount');
        $balance = $cashService->getBalance();

        return view('cash_transactions.index', compact('transactions', 'balance', 'cashIn', 'cashOut'));
    }
}
