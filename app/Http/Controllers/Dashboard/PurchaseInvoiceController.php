<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\SupplierPayment;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Setting;
use App\Models\PriceHistory;
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
        $query = PurchaseInvoice::with('supplier', 'items');
        // return $query->items->price;
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


    public function store(Request $request)
    {

        $paid = $request->paid ?? 0;
        if ($paid > 0 && $this->cashService->getBalance() < $paid) {
            return redirect()->back()
                ->withInput()
                ->with('error', '⚠️ الرصيد في الصندوق غير كافٍ لإتمام الدفع المطلوب!');
        }
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
        // return $total   . $paid;
        // ✅ لو المدفوع أكبر من الإجمالي → رجوع بخطأ
        if ($paid > $total) {
            return redirect()->back()
                ->withInput()
                ->with('error', '⚠️ المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة!');
        }

        $remaining = max($total - $paid, 0); // دايماً صفر أو أكثر

        // رقم الفاتورة
        $lastInvoice = PurchaseInvoice::orderBy('id', 'desc')->first();
        if ($lastInvoice && preg_match('/RQI-(\d{5})/', $lastInvoice->invoice_number, $matches)) {
            $lastNumber = (int) $matches[1];
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        $newInvoiceNumber = 'RQI-' . $newNumber;

        // إنشاء الفاتورة
        $invoice = PurchaseInvoice::create([
            'invoice_number' => $newInvoiceNumber,
            'supplier_id'    => $request->supplier_id,
            'total'          => $total,
            'paid'           => $paid,
            'remaining'      => $remaining,
        ]);

        // حفظ العناصر وتحديث المخزون
        foreach ($request->items as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'subtotal'   => $item['quantity'] * $item['price'],
            ]);

            $product = Product::findOrFail($item['product_id']);

            // 🔹 الكمية القديمة والسعر القديم
            $oldQuantity = $product->stock;
            $oldPrice    = $product->purchase_price ?? 0;

            // 🔹 الكمية الجديدة والسعر الجديد
            $newQuantity = $item['quantity'];
            $newPrice    = $item['price'];

            // 🔹 إجمالي الكمية
            $totalQuantity = $oldQuantity + $newQuantity;

            // 🔹 حساب متوسط السعر الجديد
            if ($totalQuantity > 0) {
                $avgPrice = (($oldQuantity * $oldPrice) + ($newQuantity * $newPrice)) / $totalQuantity;
            } else {
                $avgPrice = $newPrice;
            }
            $avgPrice = floor($avgPrice);

            if ($avgPrice != $oldPrice) {
        PriceHistory::create([
            'product_id' => $product->id,
            'old_price'  => $oldPrice,
            'new_price'  => $avgPrice,
            'type'       => 'purchase_invoice', // سبب التغيير
        ]);
    }

            // تحديث المنتج
            $product->update([
                'stock'          => $totalQuantity,
                'purchase_price' => $avgPrice, // ✅ تحديث السعر بالمتوسط
            ]);
        }

        // تسجيل الحركة في الخزنة (خصم المبلغ)
        if ($paid > 0) {
            try {
                $this->cashService->record(
                    'deduct',
                    $paid, // ✅ خصم المدفوع فقط
                    "دفعة نقدية لفاتورة شراء رقم {$invoice->invoice_number}",
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

        // تحديث رصيد المورد
        $supplier = Supplier::findOrFail($request->supplier_id);
        $supplier->update([
            'balance' => $supplier->balance + $remaining, // ✅ فقط المتبقي يضاف لرصيد المورد
        ]);

        // حفظ الدفعة للمورد
        if ($paid > 0) {
            SupplierPayment::create([
                'supplier_id'         => $request->supplier_id,
                'purchase_invoice_id' => $invoice->id,
                'amount'              => $paid,
                'payment_date'        => now()->toDateString(),
                'note'                => "دفعة لفاتورة شراء رقم {$invoice->invoice_number}",
            ]);
        }

        return redirect()->route('dashboard.purchase-invoices.show', $invoice->id)
            ->with('success', 'تم إنشاء الفاتورة بنجاح ✅');
    }


    // عرض صفحة التعديل
    public function edit($id)
    {
        $invoice   = PurchaseInvoice::with('items')->findOrFail($id);
        $suppliers = Supplier::all();
        $products  = Product::all();

        return view('dashboard.purchase_invoices.edit', compact('invoice', 'suppliers', 'products'));
    }

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

        // تحقق إضافي: منع الأسعار أو الكميات السالبة
        foreach ($request->items as $item) {
            if ($item['quantity'] <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '⚠️ الكمية يجب أن تكون أكبر من صفر!');
            }

            if ($item['price'] < 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '⚠️ سعر الشراء لا يمكن أن يكون سالب!');
            }
        }

        $invoice = PurchaseInvoice::findOrFail($id);

        $oldPaid = $invoice->paid;
        $paid    = $request->paid ?? $oldPaid;
        $difference = $paid - $oldPaid; // الفرق بين المدفوع الجديد والقديم

        // التحقق من رصيد الخزينة قبل خصم الفرق إذا الفرق موجب
        if ($difference > 0 && $this->cashService->getBalance() < $difference) {
            return redirect()->back()
                ->withInput()
                ->with('error', '⚠️ الرصيد في الصندوق غير كافٍ لإتمام الدفع الجديد!');
        }

        // 1️⃣ إعادة المخزون القديم قبل حذف العناصر
        foreach ($invoice->items as $oldItem) {
            $product = Product::findOrFail($oldItem->product_id);
            $product->update([
                'stock' => $product->stock - $oldItem->quantity,
            ]);
        }

        // 2️⃣ حذف العناصر القديمة
        $invoice->items()->delete();

        // 3️⃣ حساب الإجمالي والمدفوع
        $total = collect($request->items)->sum(fn($item) => $item['quantity'] * $item['price']);
        if ($paid > $total) {
            return redirect()->back()
                ->withInput()
                ->with('error', '⚠️ المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة!');
        }
        $remaining = max($total - $paid, 0);

        // 4️⃣ تحديث الفاتورة الأساسية
        $invoice->update([
            'supplier_id' => $request->supplier_id,
            'total'       => $total,
            'paid'        => $paid,
            'remaining'   => $remaining,
        ]);

        // 5️⃣ إعادة إدخال العناصر وتحديث المخزون مع متوسط الشراء
        foreach ($request->items as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'subtotal'   => $item['quantity'] * $item['price'],
            ]);

            $product = Product::findOrFail($item['product_id']);

            $oldQuantity = $product->stock;
            $oldPrice    = $product->purchase_price ?? 0;

            $newQuantity = $item['quantity'];
            $newPrice    = $item['price'];

            $totalQuantity = $oldQuantity + $newQuantity;

            $avgPrice = $totalQuantity > 0
                ? floor((($oldQuantity * $oldPrice) + ($newQuantity * $newPrice)) / $totalQuantity)
                : $newPrice;
    if ($avgPrice != $oldPrice) {
        PriceHistory::create([
            'product_id' => $product->id,
            'old_price'  => $oldPrice,
            'new_price'  => $avgPrice,
            'type'       => 'purchase_invoice', // سبب التغيير
        ]);
    }


            $product->update([
                'stock'          => $totalQuantity,
                'purchase_price' => $avgPrice,
            ]);
        }

        // 6️⃣ تحديث رصيد المورد
        $supplier = Supplier::findOrFail($request->supplier_id);
        $totalRemaining = $supplier->purchaseInvoices()->sum('remaining');
        $supplier->update([
            'balance' => $totalRemaining,
        ]);

        // 7️⃣ تعديل الخزينة حسب الفرق
        if ($difference != 0) {
            if ($difference > 0) {
                // خصم الفرق
                $this->cashService->record(
                    'deduct',
                    $difference,
                    "تحديث دفعة لفاتورة شراء رقم {$invoice->invoice_number}",
                    'purchase',
                    now(),
                    null,
                    null,
                    $invoice->id
                );
            } else {
                // إضافة الفرق للخزينة
                $this->cashService->record(
                    'add',
                    abs($difference),
                    "تحديث دفعة لفاتورة شراء رقم {$invoice->invoice_number}",
                    'purchase',
                    now(),
                    null,
                    null,
                    $invoice->id
                );
            }

            // تحديث سجل حركة الدفع إذا موجود
            $cashTransaction = CashTransaction::where('purchase_invoice_id', $invoice->id)->first();
            if ($cashTransaction) {
                $cashTransaction->update([
                    'amount' => $paid,
                ]);
            }
        }

        return redirect()->route('dashboard.purchase-invoices.show', $invoice->id)
            ->with('success', 'تم تعديل الفاتورة بنجاح ✅');
    }





    // عرض فاتورة معينة
    public function show(PurchaseInvoice $purchaseInvoice)
    {
        return view('dashboard.purchase_invoices.show', compact('purchaseInvoice'));
    }


    public function print(PurchaseInvoice $purchaseInvoice)
    {
        $setting = Setting::first();


        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 80], // العرض والارتفاع بالملم
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
            'default_font' => 'tajawal', // أو 'cairo' حسب الخط المثبت
            'isRemoteEnabled' => true,
        ]);
        $html = view('dashboard.purchase_invoices.pdf', compact('purchaseInvoice', 'setting'))->render();
        $mpdf->WriteHTML($html);

        // 'D' للتحميل مباشرة، يمكنك استخدام 'I' للعرض في المتصفح
        return $mpdf->Output("purchase_invoice_{$purchaseInvoice->id}.pdf", 'I');
    }
}
