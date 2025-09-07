<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Services\CashService;
use App\Models\CashTransaction;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::latest()->paginate(20);
        return view('expenses.index', compact('expenses'));
    }
    public function edit($id)
    {
        // جلب المصروف حسب المعرف
        $expense = Expense::findOrFail($id);

        // تمرير البيانات إلى صفحة التعديل
        return view('expenses.edit', compact('expense'));
    }

    public function create()
    {
        return view('expenses.create');
    }


  public function store(Request $request, CashService $cashService)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'type' => 'required|in:operational,other',
        'note' => 'nullable|string',
    ]);

    // ✅ التحقق من الرصيد قبل إنشاء المصروف
    $balance = $cashService->getBalance();
    if ($request->amount > $balance) {
        return back()->withErrors([
            'amount' => "المبلغ المطلوب أكبر من الرصيد المتاح في الخزينة ({$balance})"
        ])->withInput();
    }

    // إنشاء المصروف
    $expense = Expense::create($request->except('_token'));

    // ✅ تسجيل الحركة المالية في الكاش مع ربطها بالمصروف
    $transaction = $cashService->record(
        'deduct',
        $expense->amount,
        "مصروف: " . $expense->title,
        $expense->type,
        now(),
        null,
        null,
        null,
        $expense->id // هذا يتخزن في expense_id داخل cash_transactions
    );

    // للتأكد أن الربط صحيح
    if ($transaction->expense_id !== $expense->id) {
        throw new \Exception("لم يتم حفظ معرف المصروف في سجل الحركة المالية");
    }

    session()->flash('success', __('تم الإضافة بنجاح وتم تسجيل الحركة المالية 💰'));
    return redirect()->route('dashboard.expenses.index');
}


    public function update(Request $request, Expense $expense, CashService $cashService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:operational,other',
            'note' => 'nullable|string',
        ]);

        $expense->update($request->except('_token', '_method'));

        // ابحث عن الحركة المالية لهذا المصروف
        $transaction = CashTransaction::where('expense_id',  $expense->id)->first();

        if ($transaction) {
            $cashService->updateTransaction(
                $transaction,
                $expense->amount,
                "مصروف: " . $expense->title,
                $expense->type,
                now()
            );
        }

        session()->flash('success', __('تم التعديل بنجاح وتحديث الحركة المالية 📝'));
        return redirect()->route('dashboard.expenses.index');
    }

    public function destroy(Expense $expense, CashService $cashService)
    {
        // ابحث عن الحركة المالية المرتبطة
        $transaction = CashTransaction::where('expense_id',  $expense->id)->first();

        if ($transaction) {
            $cashService->deleteTransaction($transaction);
        }

        $expense->delete();

        return redirect()->route('dashboard.expenses.index')
            ->with('success', 'تم حذف المصروف واسترجاع أثره المالي 🗑️💵');
    }
    public function restoreExpense($id)
{
    $expense = Expense::withTrashed()->findOrFail($id);
    $expense->restore();

    session()->flash('success', "تم استرجاع المصروف: #{$expense->id}");
    return redirect()->back();
}

}
