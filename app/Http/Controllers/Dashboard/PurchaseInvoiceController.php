<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PDF; // لو تستعمل dompdf

class PurchaseInvoiceController extends Controller
{
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
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        $total = \collect($request->items)->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        $invoice = PurchaseInvoice::create([
            'supplier_id' => $request->supplier_id,
            'total'       => $total,
        ]);


        foreach ($request->items as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
            ]);
        }

        return redirect()->route('purchase-invoices.show', $invoice->id)
            ->with('success', 'تم إنشاء الفاتورة بنجاح');
    }

    // عرض فاتورة معينة
    public function show(PurchaseInvoice $purchaseInvoice)
    {
        return view('purchase_invoices.show', compact('purchaseInvoice'));
    }

    // طباعة PDF
    public function print(PurchaseInvoice $purchaseInvoice)
    {
        $pdf = PDF::loadView('purchase_invoices.pdf', compact('purchaseInvoice'));
        return $pdf->download("purchase_invoice_{$purchaseInvoice->id}.pdf");
    }
}
