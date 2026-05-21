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

    public function storePayment(Request $request, $supplierId)
    {
        // return $request  ;
        $request->validate([
            // 'supplier_id'          => 'required|exists:suppliers,id',
            'amount'               => 'required|numeric|min:0.01',
            'purchase_invoice_id'  => 'required|exists:purchase_invoices,id',
            'payment_date'         => 'required|date',
        ], [
            // 'supplier_id.required'         => 'الرجاء اختيار المورد.',
            // 'supplier_id.exists'           => 'المورد المختار غير موجود.',

            'amount.required'              => 'الرجاء إدخال المبلغ.',
            'amount.numeric'               => 'المبلغ يجب أن يكون رقمًا.',
            'amount.min'                   => 'المبلغ لا يمكن أن يكون أقل من 0.01.',

            'purchase_invoice_id.required' => 'الرجاء اختيار الفاتورة.',
            'purchase_invoice_id.exists'   => 'الفاتورة المختارة غير موجودة.',

            'payment_date.required'        => 'الرجاء إدخال تاريخ الدفع.',
            'payment_date.date'            => 'تاريخ الدفع غير صالح.',
        ]);

        $supplier = Supplier::findOrFail($supplierId);
        $amount = round((float) $request->amount, 2);
        $invoice = PurchaseInvoice::findOrFail($request->purchase_invoice_id);

        if ($amount > (float) $invoice->remaining + 0.02) {
            return redirect()->back()->with('error', '⚠️ المبلغ المدخل أكبر من المتبقي في الفاتورة!');
        }

        if ($amount > (float) $supplier->balance + 0.02) {
            return redirect()->back()->withErrors(['amount' => 'المبلغ أكبر من رصيد المورد!'])->withInput();
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $supplier, $invoice, $amount) {
                $payment = SupplierPayment::create([
                    'supplier_id' => $supplier->id,
                    'purchase_invoice_id' => $invoice->id,
                    'amount' => $amount,
                    'payment_date' => $request->payment_date,
                    'note' => $request->note,
                ]);

                $this->cashService->record(
                    'deduct',
                    $amount,
                    "دفعة نقدية للمورد {$supplier->name} — فاتورة {$invoice->invoice_number}",
                    'supplier_payment',
                    $request->payment_date,
                    null,
                    null,
                    $invoice->id,
                    null,
                    $payment->id
                );

                $newPaid = round((float) $invoice->paid + $amount, 2);
                $newRemaining = max((float) $invoice->total - $newPaid, 0);

                $invoice->update([
                    'paid' => $newPaid,
                    'remaining' => $newRemaining,
                ]);

                $supplier->update([
                    'balance' => max(0, (float) $supplier->balance - $amount),
                ]);
            });
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '⚠️ لا يمكن إتمام العملية: '.$e->getMessage());
        }

        return redirect()->route('dashboard.suppliers.index')
            ->with('success', 'تم إضافة الدفعة وتسجيلها في الخزينة بنجاح');
    }
    //     public function storepayme(Request $request)
    // {
    //     return 0;
    //     $request->validate([
    //         'supplier_id'         => 'required|exists:suppliers,id',
    //         'purchase_invoice_id' => 'nullable|exists:purchase_invoices,id', // فاتورة اختيارية
    //         'amount'              => 'required|numeric|min:0.01',
    //         'payment_date'        => 'required|date',
    //     ]);

    //     $supplier = Supplier::findOrFail($request->supplier_id);

    //     // منع دفع مبلغ أكبر من رصيد المورد
    //     if ($request->amount > $supplier->balance) {
    //         return redirect()->back()->withErrors(['amount' => 'المبلغ أكبر من رصيد المورد!'])->withInput();
    //     }

    //     // محاولة تسجيل الحركة في الخزنة
    //     if ($request->amount > 0) {
    //         try {
    //             $this->cashService->record(
    //                 'deduct',
    //                 $request->amount,
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
    //                 ->withErrors(['error' => '⚠️ لا يمكن إتمام العملية: الرصيد في الصندوق غير كافٍ']);
    //         }
    //     }

    //     // حفظ الدفعة
    //     $payment = SupplierPayment::create([
    //         'supplier_id'         => $request->supplier_id,
    //         'purchase_invoice_id' => $request->purchase_invoice_id, // حفظ معرف الفاتورة
    //         'amount'              => $request->amount,
    //         'payment_date'        => $request->payment_date,
    //         'note'                => $request->note,
    //     ]);

    //     // خصم المبلغ من الفاتورة إذا تم اختيارها
    //     if ($request->purchase_invoice_id) {
    //         $invoice = \App\Models\PurchaseInvoice::find($request->purchase_invoice_id);
    //         if ($invoice) {
    //             $invoice->update([
    //                 'remaining_amount' => $invoice->remaining_amount - $request->amount,
    //             ]);
    //         }
    //     }

    //     // تحديث رصيد المورد
    //     $supplier->update([
    //         'balance' => $supplier->balance - $request->amount,
    //     ]);

    //     return redirect()->back()->with('success', 'تم إضافة الدفعة بنجاح');
    // }

    public function createPayment($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $invoices = PurchaseInvoice::where('supplier_id', $supplierId)
            ->where('remaining', '>', 0)
            ->get();

        return view('dashboard.suppliers.add_payment', compact('supplier', 'invoices'));
    }

    // public function storePayment(Request $request, $supplierId)
    // {
    //     $request->validate([
    //         'amount' => 'required|numeric|min:0.01',
    //         'payment_date' => 'required|date',
    //         'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
    //         'note' => 'nullable|string',
    //     ]);

    //     $supplier = Supplier::findOrFail($supplierId);

    //     if ($request->amount > $supplier->balance) {
    //         return redirect()->back()->withErrors(['amount' => 'المبلغ أكبر من رصيد المورد!'])->withInput();
    //     }

    //     // حفظ الدفعة
    //     SupplierPayment::create([
    //         'supplier_id' => $supplier->id,
    //         'purchase_invoice_id' => $request->purchase_invoice_id,
    //         'amount' => $request->amount,
    //         'payment_date' => $request->payment_date,
    //         'note' => $request->note,
    //     ]);

    //     // تحديث رصيد المورد
    //     $supplier->update(['balance' => $supplier->balance - $request->amount]);

    //     return redirect()->route('dashboard.suppliers.index')->with('success', 'تم إضافة الدفعة بنجاح');
    // }

    public function updatepayme(Request $request, $id)
    {
        // return $request;
          $request->validate([
        'supplier_id'          => 'required|exists:suppliers,id',
        'amount'               => 'required|numeric|min:0.01',
        'purchase_invoice_id'  => 'required|exists:purchase_invoices,id',
        'payment_date'         => 'required|date',
    ], [
        'supplier_id.required'         => 'الرجاء اختيار المورد.',
        'supplier_id.exists'           => 'المورد المختار غير موجود.',
        'amount.required'              => 'الرجاء إدخال المبلغ.',
        'amount.numeric'               => 'المبلغ يجب أن يكون رقمًا.',
        'amount.min'                   => 'المبلغ لا يمكن أن يكون أقل من 0.01.',
        'purchase_invoice_id.required' => 'الرجاء اختيار الفاتورة.',
        'purchase_invoice_id.exists'   => 'الفاتورة المختارة غير موجودة.',
        'payment_date.required'        => 'الرجاء إدخال تاريخ الدفع.',
        'payment_date.date'            => 'تاريخ الدفع غير صالح.',
    ]);

    $payment = SupplierPayment::with('transaction')->findOrFail($id);
    $supplier = Supplier::findOrFail($request->supplier_id);
    $newAmount = round((float) $request->amount, 2);
    $oldAmount = round((float) $payment->amount, 2);
    $diff = round($newAmount - $oldAmount, 2);
    $newInvoice = PurchaseInvoice::findOrFail($request->purchase_invoice_id);

    $maxOnInvoice = (float) $newInvoice->remaining;
    if ((int) $payment->purchase_invoice_id === (int) $newInvoice->id) {
        $maxOnInvoice += $oldAmount;
    }

    if ($newAmount > $maxOnInvoice + 0.02) {
        return redirect()->back()->withErrors(['amount' => '⚠️ المبلغ المدخل أكبر من المتبقي في الفاتورة!']);
    }

    if ($diff > 0 && $diff > (float) $supplier->balance + 0.02) {
        return redirect()->back()->withErrors(['amount' => 'المبلغ أكبر من رصيد المورد!']);
    }

    try {
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $payment, $supplier, $newInvoice, $newAmount, $oldAmount, $diff) {
            if ($payment->purchase_invoice_id) {
                $oldInvoice = PurchaseInvoice::find($payment->purchase_invoice_id);
                if ($oldInvoice) {
                    $revertedPaid = max(0, (float) $oldInvoice->paid - $oldAmount);
                    $oldInvoice->update([
                        'paid' => $revertedPaid,
                        'remaining' => max((float) $oldInvoice->total - $revertedPaid, 0),
                    ]);
                }
            }

            $newPaid = round((float) $newInvoice->paid + $newAmount, 2);
            $newInvoice->update([
                'paid' => $newPaid,
                'remaining' => max((float) $newInvoice->total - $newPaid, 0),
            ]);

            if ($transaction = $payment->transaction) {
                $this->cashService->updateTransaction(
                    $transaction,
                    $newAmount,
                    "تعديل دفعة المورد {$supplier->name} — فاتورة {$newInvoice->invoice_number}",
                    'supplier_payment',
                    $request->payment_date
                );
            } elseif ($newAmount > 0) {
                $this->cashService->record(
                    'deduct',
                    $newAmount,
                    "دفعة المورد {$supplier->name} — فاتورة {$newInvoice->invoice_number}",
                    'supplier_payment',
                    $request->payment_date,
                    null,
                    null,
                    $newInvoice->id,
                    null,
                    $payment->id
                );
            }

            $payment->update([
                'amount' => $newAmount,
                'payment_date' => $request->payment_date,
                'purchase_invoice_id' => $request->purchase_invoice_id,
                'note' => $request->note,
            ]);

            $supplier->update([
                'balance' => max(0, (float) $supplier->balance - $diff),
            ]);
        });
    } catch (\Throwable $e) {
        return redirect()->back()->withErrors(['cash' => '⚠️ لا يمكن إتمام العملية: '.$e->getMessage()]);
    }

    return redirect()->route('dashboard.suppliers.payments', $payment->supplier->id)
        ->with('success', 'تم تعديل الدفعة وتحديث الخزينة بنجاح');
    }


    public function getSupplierInvoices($supplierId)
    {
        $invoices = \App\Models\PurchaseInvoice::where('supplier_id', $supplierId)
            ->where('remaining_amount', '>', 0)
            ->get(['id', 'invoice_number', 'remaining_amount']); // إعادة فقط الحقول اللازمة

        return response()->json($invoices);
    }

public function show_payments($supplierId)
{
    $supplier = Supplier::findOrFail($supplierId);

    // جلب الدفعات المرتبطة بالمورد
    $allPayments = SupplierPayment::with('purchase_invoice')
        ->where('supplier_id', $supplierId)
        ->get();

    // تجميع الدفعات بحسب الفاتورة
    $payments = $allPayments->groupBy(function($item) {
        return $item->purchase_invoice_id ?? 'no_invoice';
    });

    // تصفية المجموعات بحيث تتجاهل الفواتير المكتملة
    $payments = $payments->filter(function($groupedPayments, $invoiceId) {
        if ($invoiceId === 'no_invoice') return true; // احتفظ بالدفعات بدون فاتورة
        $remaining = $groupedPayments->first()->purchase_invoice->remaining ?? 0;
        return $remaining > 0; // فقط الفواتير التي لديها متبقي > 0
    });

    return view('dashboard.suppliers.payments', compact('supplier', 'payments'));
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
