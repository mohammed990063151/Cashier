<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use PDF; // لو تستعمل dompdf
use App\Services\CashService;

class PurchaseInvoiceController extends Controller
{

    protected $cashService;

    public function __construct(CashService $cashService)
    {
        $this->cashService = $cashService;
    }

    public function index(Request $request)
    {
        $query = PurchaseInvoice::with('supplier');

        if ($request->search) {
            $query->whereHas('supplier', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->orWhere('id', $request->search); // للبحث برقم الفاتورة
        }

        $purchaseInvoices = $query->latest()->paginate(10);

        return view('dashboard.purchase_invoices.index', compact('purchaseInvoices'));
    }

    // عرض نموذج إضافة فاتورة شراء
    public function create()
    {
        $suppliers = Supplier::all();
        $products  = Product::all();
        return view('dashboard.purchase_invoices.create', compact('suppliers', 'products'));
    }

    // حفظ الفاتورة
    // public function store(Request $request)
    // {
    //     // التحقق من صحة البيانات
    //     $request->validate([
    //         'supplier_id' => 'required|exists:suppliers,id',
    //         'items'       => 'required|array',
    //         'items.*.product_id' => 'required|exists:products,id',
    //         'items.*.quantity'   => 'required|numeric|min:1',
    //         'items.*.price'      => 'required|numeric|min:0',
    //     ]);

    //     // حساب المجموع الكلي للفاتورة
    //     $total = collect($request->items)->sum(function ($item) {
    //         return $item['quantity'] * $item['price'];
    //     });

    //     // إنشاء الفاتورة
    //     $invoice = PurchaseInvoice::create([
    //         'supplier_id' => $request->supplier_id,
    //         'total'       => $total,
    //     ]);

    //     // حفظ عناصر الفاتورة مع حساب subtotal لكل عنصر
    //     foreach ($request->items as $item) {
    //         $invoice->items()->create([
    //             'product_id' => $item['product_id'],
    //             'quantity'   => $item['quantity'],
    //             'price'      => $item['price'],
    //             'subtotal'   => $item['quantity'] * $item['price'], // ✅ السطر المضاف
    //         ]);
    //     }
    //     // return  $total;

    //     if ($total > 0) {
    //         try {
    //             $this->cashService->record(
    //                 'deduct',
    //                 $total,
    //                 "دفعة نقدية فاتورة شراء رقم {$invoice->id}",
    //                 'purchase',
    //                 now(),
    //                 null, // order_id
    //                 null, // payment_id
    //                 $invoice->id // purchase_invoice_id
    //             );
    //         } catch (\Exception $e) {
    //            return $e;
    //             return redirect()->back()
    //                 ->with('error', "لا يمكن إتمام العملية: الرصيد في الصندوق غير كافٍ")
    //                 ->withInput();
    //         }
    //     }



    //     // إعادة التوجيه مع رسالة النجاح
    //     return redirect()->route('dashboard.purchase-invoices.show', $invoice->id)
    //         ->with('success', 'تم إنشاء الفاتورة بنجاح');
    // }

    public function store(Request $request)
    {
       
        $request->validate([
            'supplier_id'         => 'required|exists:suppliers,id',
            'items'               => 'required|array',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|numeric|min:1',
            'items.*.price'       => 'required|numeric|min:0',
            'paid'                => 'nullable|numeric|min:0', // المدفوع لازم >= 0
        ]);

        // حساب الإجمالي
        $total = $request->total ?? 0;
        $paid = $request->paid ?? 0;

        // ✅ لو المدفوع أكبر من الإجمالي → رجوع بخطأ
        if ($paid > $total) {
            return redirect()->back()
                ->withInput()
                ->with('error', '⚠️ المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة!');
        }

        $remaining = max($total - $paid, 0); // دايماً صفر أو أكثر

 
        // إنشاء الفاتورة
        $invoice = PurchaseInvoice::create([
            'supplier_id' => $request->supplier_id,
            'total'       => $total,
            'paid'        => $paid,
            'remaining'   => $remaining,
        ]);
// return $invoice;
        // حفظ العناصر وتحديث المخزون
        foreach ($request->items as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'subtotal'   => $item['quantity'] * $item['price'],
            ]);

            $product = Product::findOrFail($item['product_id']);
            $product->update([
                'stock' => $product->stock + $item['quantity'], // ✅ زيادة الكمية
            ]);
        }

        // تسجيل الحركة في الخزنة (خصم المبلغ)
        if ($paid > 0) {
            try {
                $this->cashService->record(
                    'deduct',
                    $paid, // ✅ خصم المدفوع فقط
                    "دفعة نقدية لفاتورة شراء رقم {$invoice->id}",
                    'purchase',
                    now(),
                    null,
                    null,
                    $invoice->id
                );
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '⚠️ لا يمكن إتمام العملية: الرصيد في الصندوق غير كافٍ');
            }
        }


        $supplier = Supplier::findOrFail($request->supplier_id);
        $supplier->update([
            'balance' => $supplier->balance + $remaining, // ✅ فقط المتبقي يضاف لرصيد المورد
        ]);


        return redirect()->route('dashboard.purchase-invoices.show', $invoice->id)
            ->with('success', 'تم إنشاء الفاتورة بنجاح');
    }

    // عرض صفحة التعديل
    public function edit($id)
    {
        $invoice   = PurchaseInvoice::with('items')->findOrFail($id);
        $suppliers = Supplier::all();
        $products  = Product::all();

        return view('dashboard.purchase_invoices.edit', compact('invoice', 'suppliers', 'products'));
    }

    // تحديث الفاتورة
    // public function update(Request $request, $id)
    // {

    //     $request->validate([
    //         'supplier_id' => 'required|exists:suppliers,id',
    //         'items'       => 'required|array',
    //         'items.*.product_id' => 'required|exists:products,id',
    //         'items.*.quantity'   => 'required|numeric|min:1',
    //         'items.*.price'      => 'required|numeric|min:0',
    //     ]);

    //     $invoice = PurchaseInvoice::findOrFail($id);

    //     // حساب الإجمالي الجديد
    //     $total = collect($request->items)->sum(function ($item) {
    //         return $item['quantity'] * $item['price'];
    //     });

    //     // تحديث الفاتورة
    //     $invoice->update([
    //         'supplier_id' => $request->supplier_id,
    //         'total'       => $total,
    //     ]);

    //     // حذف العناصر القديمة وإعادة إدخالها (ممكن لاحقاً نعمل update بدل الحذف)
    //     $invoice->items()->delete();

    //     foreach ($request->items as $item) {
    //         $invoice->items()->create([
    //             'product_id' => $item['product_id'],
    //             'quantity'   => $item['quantity'],
    //             'price'      => $item['price'],
    //             'subtotal'   => $item['quantity'] * $item['price'],
    //         ]);
    //     }


    //     $this->updateCashAmount($invoice->id, $total);
    //     return redirect()->route('dashboard.purchase-invoices.show', $invoice->id)
    //         ->with('success', 'تم تعديل الفاتورة بنجاح');
    // }
    public function update(Request $request, $id)
{
    $request->validate([
        'supplier_id'         => 'required|exists:suppliers,id',
        'items'               => 'required|array',
        'items.*.product_id'  => 'required|exists:products,id',
        'items.*.quantity'    => 'required|numeric|min:1',
        'items.*.price'       => 'required|numeric|min:0',
        'paid'                => 'nullable|numeric|min:0',
    ]);

    $invoice = PurchaseInvoice::findOrFail($id);

    // ✅ رجّع المخزون قبل ما نحذف العناصر
    foreach ($invoice->items as $oldItem) {
        $product = Product::findOrFail($oldItem->product_id);
        $product->update([
            'stock' => $product->stock - $oldItem->quantity,
        ]);
    }

    // احذف العناصر القديمة
    $invoice->items()->delete();

    // احسب الإجمالي والمدفوع
    $total = collect($request->items)->sum(fn($item) => $item['quantity'] * $item['price']);
    $paid = $request->paid ?? $invoice->paid; // لو ما بعث paid خليه زي القديم
    if ($paid > $total) {
        return redirect()->back()
            ->withInput()
            ->with('error', '⚠️ المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة!');
    }
    $remaining = max($total - $paid, 0);

    // ✅ تحديث الفاتورة
    $invoice->update([
        'supplier_id' => $request->supplier_id,
        'total'       => $total,
        'paid'        => $paid,
        'remaining'   => $remaining,
    ]);

    // إعادة إدخال العناصر + تحديث المخزون
    foreach ($request->items as $item) {
        $invoice->items()->create([
            'product_id' => $item['product_id'],
            'quantity'   => $item['quantity'],
            'price'      => $item['price'],
            'subtotal'   => $item['quantity'] * $item['price'],
        ]);

        $product = Product::findOrFail($item['product_id']);
        $product->update([
            'stock' => $product->stock + $item['quantity'],
        ]);
    }

    // ✅ تحديث رصيد المورد
  $supplier = Supplier::findOrFail($request->supplier_id);

// احسب رصيد المورد من كل فواتيره (المتبقي فقط)
$totalRemaining = $supplier->purchaseInvoices()->sum('remaining');

$supplier->update([
    'balance' => $totalRemaining,
]);

    // (اختياري) تحديث حركة الخزنة
    $this->updateCashAmount($invoice->id, $paid);

    return redirect()->route('dashboard.purchase-invoices.show', $invoice->id)
        ->with('success', 'تم تعديل الفاتورة بنجاح');
}

    protected function updateCashAmount($purchaseInvoiceId, $newAmount)
    {
        $cashTransaction = CashTransaction::where('purchase_invoice_id', $purchaseInvoiceId)->first();
        if ($cashTransaction) {
            $cashTransaction->update([
                'amount' => $newAmount,
            ]);
        }
    }

    // عرض فاتورة معينة
    public function show(PurchaseInvoice $purchaseInvoice)
    {
        return view('dashboard.purchase_invoices.show', compact('purchaseInvoice'));
    }

    // طباعة PDF
    // public function print(PurchaseInvoice $purchaseInvoice)
    // {
    //     $pdf = PDF::loadView('dashboard.purchase_invoices.pdf', compact('purchaseInvoice'));
    //     return $pdf->download("purchase_invoice_{$purchaseInvoice->id}.pdf");
    // }



    public function print(PurchaseInvoice $purchaseInvoice)
    {
        $html = view('dashboard.purchase_invoices.pdf', compact('purchaseInvoice'))->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'default_font' => 'cairo'
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output("purchase_invoice_{$purchaseInvoice->id}.pdf", 'D');
    }
}
