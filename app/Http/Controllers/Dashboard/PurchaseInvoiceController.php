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
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        // حساب المجموع الكلي للفاتورة
        $total = collect($request->items)->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        // إنشاء الفاتورة
        $invoice = PurchaseInvoice::create([
            'supplier_id' => $request->supplier_id,
            'total'       => $total,
        ]);

        // حفظ عناصر الفاتورة مع حساب subtotal لكل عنصر
        foreach ($request->items as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'subtotal'   => $item['quantity'] * $item['price'], // ✅ السطر المضاف
            ]);
        }
        // return  $total;

        if ($total > 0) {
            try {
                $this->cashService->record(
                    'deduct',
                    $total,
                    "دفعة نقدية فاتورة شراء رقم {$invoice->id}",
                    'purchase',
                    now(),
                    null, // order_id
                    null, // payment_id
                    $invoice->id // purchase_invoice_id
                );
            } catch (\Exception $e) {
                // بدلاً من ظهور الاستثناء، نعيد توجيه المستخدم برسالة صديقة
                return redirect()->back()
                    ->with('error', "لا يمكن إتمام العملية: الرصيد في الصندوق غير كافٍ")
                    ->withInput();
            }
        }



        // إعادة التوجيه مع رسالة النجاح
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
    public function update(Request $request, $id)
    {

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        $invoice = PurchaseInvoice::findOrFail($id);

        // حساب الإجمالي الجديد
        $total = collect($request->items)->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        // تحديث الفاتورة
        $invoice->update([
            'supplier_id' => $request->supplier_id,
            'total'       => $total,
        ]);

        // حذف العناصر القديمة وإعادة إدخالها (ممكن لاحقاً نعمل update بدل الحذف)
        $invoice->items()->delete();

        foreach ($request->items as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'subtotal'   => $item['quantity'] * $item['price'],
            ]);
        }


       $this->updateCashAmount($invoice->id, $total);
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
