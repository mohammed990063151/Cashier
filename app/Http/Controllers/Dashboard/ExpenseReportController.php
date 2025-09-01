<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        // تقرير مفصل
        $detailed = Expense::when($from && $to, function($q) use ($from, $to) {
            $q->whereBetween('created_at', [$from, $to]);
        })->get();

        // تقرير مجمل حسب النوع
        $types = Expense::select('type')->distinct()->pluck('type');
        $typesTotals = $types->map(function($type) use ($from,$to){
            return Expense::where('type', $type)
                ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from,$to]))
                ->sum('amount');
        });

        $summary = $detailed->sum('amount');

        return view('reports.expenses.expenses', compact('detailed','summary','types','typesTotals'));
    }
}
