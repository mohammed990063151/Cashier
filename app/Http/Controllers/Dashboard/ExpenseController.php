<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;

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


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:operational,other',
            'note' => 'nullable|string',
        ]);

        Expense::create($request->except('_token'));
        session()->flash('success', __('تم الإضافة بنجاح'));
        return redirect()->route('dashboard.expenses.index');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'تم حذف المصروف بنجاح 🗑️');
    }
}
