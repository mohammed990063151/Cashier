<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\CashService;
use Illuminate\Http\Request;
use App\Models\PurchaseInvoice;

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
         $request->validate(
        [
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'address'      => 'nullable|string|max:500',
            'balance'      => 'required|numeric|min:0',
        ],
        [
            'required' => 'حقل :attribute مطلوب.',
            'string'   => 'حقل :attribute يجب أن يكون نص.',
            'max'      => 'حقل :attribute يجب ألا يزيد عن :max حرف.',
            'numeric'  => 'حقل :attribute يجب أن يكون رقم.',
            'min'      => 'قيمة :attribute يجب أن تكون على الأقل :min.',
        ],
        [
            'name'         => 'اسم المورد',
            'phone'        => 'رقم الهاتف',
            'address'      => 'العنوان',
            'balance'      => 'الرصيد الابتدائي',
        ]
    );



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
          $request->validate(
        [
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'balance' => 'required|numeric|min:0',
        ],
        [
            'required' => 'حقل :attribute مطلوب.',
            'string'   => 'حقل :attribute يجب أن يكون نص.',
            'max'      => 'حقل :attribute يجب ألا يزيد عن :max حرف.',
            'numeric'  => 'حقل :attribute يجب أن يكون رقم.',
            'min'      => 'قيمة :attribute يجب أن تكون على الأقل :min.',
        ],
        [
            'name'    => 'اسم المورد',
            'phone'   => 'رقم الهاتف',
            'address' => 'العنوان',
            'balance' => 'الرصيد الابتدائي',
        ]
    );

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

    // public function storepayme(Request $request)
    // {
    //     $request->validate([
    //         'supplier_id'  => 'required|exists:suppliers,id',
    //         'amount'       => 'required|numeric|min:0.01',
    //         'payment_date' => 'required|date',
    //     ]);

    //     $supplier = Supplier::findOrFail($request->supplier_id);

    //     // منع دفع مبلغ أكبر من الرصيد
    //     if ($request->amount > $supplier->balance) {
    //         return redirect()->back()->with('errors', 'المبلغ أكبر من رصيد المورد!');
    //     }
    //     // return $request;
    //     // محاولة تسجيل الحركة في الخزنة
    //     if ($request->amount > 0) {
    //         try {
    //             $this->cashService->record(
    //                 'deduct',
    //                 $request->amount, // خصم المدفوع فقط
    //                 "دفعة نقدية للمورد {$supplier->name}",
    //                 'supplier_payment',
    //                 now(),
    //                 null,
    //                 null,
    //                 null,
    //                 null
    //             );
    //         } catch (\Exception $e) {
    //             return redirect()->back()
    //                 ->withInput()
    //                 ->with('error', '⚠️ لا يمكن إتمام العملية: الرصيد في الصندوق غير كافٍ');
    //         }
    //     }

    //     // حفظ الدفعة
    //     SupplierPayment::create([
    //         'supplier_id'  => $request->supplier_id,
    //         'amount'       => $request->amount,
    //         'payment_date' => $request->payment_date,
    //         'note'         => $request->note,
    //     ]);

    //     // تحديث رصيد المورد
    //     $supplier->update([
    //         'balance' => $supplier->balance - $request->amount,
    //     ]);

    //     return redirect()->back()->with('success', 'تم إضافة الدفعة بنجاح');
    // }
    public function storepayme(Request $request)
{
    return 0;
    $request->validate([
        'supplier_id'         => 'required|exists:suppliers,id',
        'purchase_invoice_id' => 'nullable|exists:purchase_invoices,id', // فاتورة اختيارية
        'amount'              => 'required|numeric|min:0.01',
        'payment_date'        => 'required|date',
    ]);

    $supplier = Supplier::findOrFail($request->supplier_id);

    // منع دفع مبلغ أكبر من رصيد المورد
    if ($request->amount > $supplier->balance) {
        return redirect()->back()->withErrors(['amount' => 'المبلغ أكبر من رصيد المورد!'])->withInput();
    }

    // محاولة تسجيل الحركة في الخزنة
    if ($request->amount > 0) {
        try {
            $this->cashService->record(
                'deduct',
                $request->amount,
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
                ->withErrors(['cash' => '⚠️ لا يمكن إتمام العملية: الرصيد في الصندوق غير كافٍ']);
        }
    }

    // حفظ الدفعة
    $payment = SupplierPayment::create([
        'supplier_id'         => $request->supplier_id,
        'purchase_invoice_id' => $request->purchase_invoice_id, // حفظ معرف الفاتورة
        'amount'              => $request->amount,
        'payment_date'        => $request->payment_date,
        'note'                => $request->note,
    ]);

    // خصم المبلغ من الفاتورة إذا تم اختيارها
    if ($request->purchase_invoice_id) {
        $invoice = \App\Models\PurchaseInvoice::find($request->purchase_invoice_id);
        if ($invoice) {
            $invoice->update([
                'remaining_amount' => $invoice->remaining_amount - $request->amount,
            ]);
        }
    }

    // تحديث رصيد المورد
    $supplier->update([
        'balance' => $supplier->balance - $request->amount,
    ]);

    return redirect()->back()->with('success', 'تم إضافة الدفعة بنجاح');
}

public function createPayment($supplierId)
{
    $supplier = Supplier::findOrFail($supplierId);
    $invoices = PurchaseInvoice::where('supplier_id', $supplierId)
        ->where('remaining', '>', 0)
        ->get();

    return view('dashboard.suppliers.add_payment', compact('supplier', 'invoices'));
}

public function storePayment(Request $request, $supplierId)
{
    $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'payment_date' => 'required|date',
        'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
        'note' => 'nullable|string',
    ]);

    $supplier = Supplier::findOrFail($supplierId);

    if ($request->amount > $supplier->balance) {
        return redirect()->back()->withErrors(['amount' => 'المبلغ أكبر من رصيد المورد!'])->withInput();
    }

    // حفظ الدفعة
    SupplierPayment::create([
        'supplier_id' => $supplier->id,
        'purchase_invoice_id' => $request->purchase_invoice_id,
        'amount' => $request->amount,
        'payment_date' => $request->payment_date,
        'note' => $request->note,
    ]);

    // تحديث رصيد المورد
    $supplier->update(['balance' => $supplier->balance - $request->amount]);

    return redirect()->route('dashboard.suppliers.index')->with('success', 'تم إضافة الدفعة بنجاح');
}

    public function updatepayme(Request $request, $id)
{
    // return $request;
    $request->validate([
        'supplier_id'  => 'required|exists:suppliers,id',
        'amount'       => 'required|numeric|min:0.01',
        'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
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
         'purchase_invoice_id' => $request->purchase_invoice_id,
        'note'         => $request->note,
    ]);

    // تحديث رصيد المورد
    $supplier->update([
        'balance' => $supplier->balance - $diff,
    ]);

      return redirect()->route("dashboard.suppliers.payments", $payment->supplier->id)->with('success', 'تم تعديل الدفعة بنجاح');
}


public function getSupplierInvoices($supplierId)
{
    $invoices = \App\Models\PurchaseInvoice::where('supplier_id', $supplierId)
        ->where('remaining_amount', '>', 0)
        ->get(['id', 'invoice_number', 'remaining_amount']); // إعادة فقط الحقول اللازمة

    return response()->json($invoices);
}

public function show_payments($supplier)
{
    // return 0;
    
//    $supplier = Supplier::findOrFail($supplier);
//    return $supplier;
    $invoices = SupplierPayment::with('purchase_invoice')->where('supplier_id', $supplier)
        ->get();
        $supplier = Supplier::findOrFail($supplier);
// return $invoices;
    return view('dashboard.suppliers.payments', compact( 'supplier','invoices'));
}

 public function edit_payment($paymentId)
    {
        // جلب الدفعة مع المورد والفاتورة
        $payment = SupplierPayment::with('supplier', 'purchase_invoice')->findOrFail($paymentId);
        // return $payment;
        // جلب جميع فواتير المورد المفتوحة لتحديدها في select
        $invoices = PurchaseInvoice::where('supplier_id', $payment->supplier_id)
            ->where('remaining', '>', 0)
            ->get();
// return $invoices;
        return view('dashboard.suppliers.edit_payment', compact('payment', 'invoices'));
    }


}
