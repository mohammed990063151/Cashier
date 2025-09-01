<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\CashService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    protected $cashService;

    public function __construct(CashService $cashService)
    {
        $this->cashService = $cashService;
    }
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);
        return view('dashboard.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('dashboard.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'balance' => 'nullable|numeric',
        ]);



        Supplier::create($request->all());

        return redirect()->route('dashboard.suppliers.index')
            ->with('success', 'تم إنشاء المورد بنجاح');
    }

    public function edit(Supplier $supplier)
    {
        return view('dashboard.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'balance' => 'nullable|numeric',
        ]);

        $supplier->update($request->all());

        return redirect()->route('dashboard.suppliers.index')
            ->with('success', 'تم تحديث المورد بنجاح');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('dashboard.suppliers.index')
            ->with('success', 'تم حذف المورد بنجاح');
    }

    // public function storepayme(Request $request)
    //     {
    //         $request->validate([
    //             'supplier_id'  => 'required|exists:suppliers,id',
    //             'amount'       => 'required|numeric|min:0.01',
    //             'payment_date' => 'required|date',
    //         ]);

    //         $supplier = Supplier::findOrFail($request->supplier_id);

    //         // منع دفع مبلغ أكبر من الرصيد
    //         if ($request->amount > $supplier->balance) {
    //             return redirect()->back()->with('error', 'المبلغ أكبر من رصيد المورد!');
    //         }

    //         // حفظ الدفعة
    //         SupplierPayment::create([
    //             'supplier_id'  => $supplier->id,
    //             'amount'       => $request->amount,
    //             'payment_date' => $request->payment_date,
    //             'note'         => $request->note,
    //         ]);

    //         // تحديث رصيد المورد
    //         $supplier->update([
    //             'balance' => $supplier->balance - $request->amount,
    //         ]);

    //         return redirect()->back()->with('success', 'تم إضافة الدفعة بنجاح');
    //     }

    public function storepayme(Request $request)
    {
        $request->validate([
            'supplier_id'  => 'required|exists:suppliers,id',
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);

        // منع دفع مبلغ أكبر من الرصيد
        if ($request->amount > $supplier->balance) {
            return redirect()->back()->with('errors', 'المبلغ أكبر من رصيد المورد!');
        }
        // return $request;
        // محاولة تسجيل الحركة في الخزنة
        if ($request->amount > 0) {
            try {
                $this->cashService->record(
                    'deduct',
                    $request->amount, // خصم المدفوع فقط
                    "دفعة نقدية للمورد {$supplier->name}",
                    'supplier_payment',
                    now(),
                    null,
                    null,
                    null,
                    null
                );
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '⚠️ لا يمكن إتمام العملية: الرصيد في الصندوق غير كافٍ');
            }
        }

        // حفظ الدفعة
        SupplierPayment::create([
            'supplier_id'  => $request->supplier_id,
            'amount'       => $request->amount,
            'payment_date' => $request->payment_date,
            'note'         => $request->note,
        ]);

        // تحديث رصيد المورد
        $supplier->update([
            'balance' => $supplier->balance - $request->amount,
        ]);

        return redirect()->back()->with('success', 'تم إضافة الدفعة بنجاح');
    }
    public function updatepayme(Request $request, $id)
{
    $request->validate([
        'supplier_id'  => 'required|exists:suppliers,id',
        'amount'       => 'required|numeric|min:0.01',
        'payment_date' => 'required|date',
    ]);

    $payment = SupplierPayment::findOrFail($id);
    $supplier = Supplier::findOrFail($request->supplier_id);

    // احتساب الفرق لتحديث رصيد المورد
    $diff = $request->amount - $payment->amount;
    if ($diff > $supplier->balance) {
        return redirect()->back()->withErrors(['amount' => 'المبلغ أكبر من رصيد المورد!']);
    }

    // تحديث الخزنة (خصم/إضافة الفرق)
    if ($diff != 0) {
        try {
            $type = $diff > 0 ? 'deduct' : 'add';
            $this->cashService->record(
                $type,
                abs($diff),
                "تعديل دفعة المورد {$supplier->name}",
                'supplier_payment',
                now()
            );
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['cash' => '⚠️ لا يمكن إتمام العملية: الرصيد في الصندوق غير كافٍ']);
        }
    }

    // تحديث الدفعة
    $payment->update([
        'amount'       => $request->amount,
        'payment_date' => $request->payment_date,
        'note'         => $request->note,
    ]);

    // تحديث رصيد المورد
    $supplier->update([
        'balance' => $supplier->balance - $diff,
    ]);

    return redirect()->back()->with('success', 'تم تعديل الدفعة بنجاح');
}

}
