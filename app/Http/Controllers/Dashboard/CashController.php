<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;

use App\Models\Cash;
use App\Models\CashTransaction;
use App\Models\CashSetting;
use Illuminate\Http\Request;

class CashController extends Controller
{
    public function index()
    {
        $cash = Cash::firstOrCreate(['id' => 1], ['balance' => 0]);
        $transactions = CashTransaction::latest()->orderBy('created_at', 'desc')->paginate(10);
        return view('dashboard.cash.index', compact('cash', 'transactions'));
    }

    public function storeTransaction(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:add,deduct',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
            'category' => 'nullable|string'
        ]);

        $cash = Cash::first();
        if ($data['type'] === 'add') {
            $cash->balance += $data['amount'];
        } else {
            if ($cash->balance < $data['amount']) {
                return back()->withErrors(['balance' => 'الرصيد غير كافٍ.']);
            }
            $cash->balance -= $data['amount'];
        }
        $cash->save();

        CashTransaction::create($data);

        return redirect()->route('dashboard.cash.index')->with('success', 'تمت العملية بنجاح');
    }

    public function settings()
    {
        $settings = CashSetting::firstOrCreate(['id' => 1]);
        return view('dashboard.cash.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $settings = CashSetting::first();
        $settings->update([
            'add_sales' => $request->has('add_sales'),
            'add_client_payments' => $request->has('add_client_payments'),
            'deduct_purchases' => $request->has('deduct_purchases'),
            'deduct_supplier_payments' => $request->has('deduct_supplier_payments'),
            'deduct_expenses' => $request->has('deduct_expenses')
        ]);

        return back()->with('success', 'تم تحديث الإعدادات');
    }

    public function filterTransactions(Request $request)
    {
        $query = CashTransaction::query();

        if ($request->category && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $transactions = $query->latest()->paginate(10);
        $cash = Cash::first();

        return view('dashboard.cash.index', compact('cash', 'transactions'));
    }
}
