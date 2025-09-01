<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierReportController extends Controller
{

    //   public function index()
    // {
    //     $suppliers = Supplier::with(['purchaseInvoices.items.product', 'payments'])->get();

    //     return view('reports.suppliers.index', compact('suppliers'));
    // }
     public function index()
    {
        $suppliers = Supplier::with(['purchaseInvoices.items.product', 'payments'])->get();

        return view('reports.suppliers.index', compact('suppliers'));
    }

    public function invoiceDetails($invoiceId)
    {
        $invoice = \App\Models\PurchaseInvoice::with(['items.product', 'supplier', 'payments'])->findOrFail($invoiceId);

        // مجموع المدفوعات للفواتير
        $totalPaid = $invoice->paid + $invoice->payments->sum('amount');

        return view('reports.suppliers.invoice_details', compact('invoice', 'totalPaid'));
    }


    public function show(Supplier $supplier)
    {
        // الفواتير
        $invoices = $supplier->purchaseInvoices()->with('items.product')->get();

        // المنتجات المشتراه (من خلال العناصر items)
        $products = $invoices->flatMap->items->groupBy('product_id')->map(function ($group) {
            return [
                'product_name' => $group->first()->product->name ?? 'غير معروف',
                'quantity' => $group->sum('quantity'),
                'total' => $group->sum('subtotal'),
            ];
        });

        // كشف الحساب (الدفعات)
        $payments = $supplier->payments()->get();

        return view('reports.suppliers.show', compact('supplier', 'invoices', 'products', 'payments'));
    }
}
