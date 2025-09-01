<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Product;
use App\Models\Category;

class PurchaseReportController extends Controller
{
    public function index()
    {
        $invoices = PurchaseInvoice::with(['supplier','items.product'])->get();
        return view('reports.purchases.index', compact('invoices'));
    }

    public function detailed()
    {
        $invoices = PurchaseInvoice::with(['supplier','items.product'])->get();
        return view('reports.purchases.detailed', compact('invoices'));
    }

    public function summary()
    {
        $invoices = PurchaseInvoice::with('supplier')->get();
        return view('reports.purchases.summary', compact('invoices'));
    }

    public function byCategory()
    {
        $items = PurchaseInvoiceItem::with(['invoice.supplier','product.category'])->get();
        return view('reports.purchases.byCategory', compact('items'));
    }

    public function unpaid()
    {
        $invoices = PurchaseInvoice::with('supplier')->where('remaining','>',0)->get();
        return view('reports.purchases.unpaid', compact('invoices'));
    }

    public function invoiceDetails(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier','items.product','payments']);
        return view('reports.purchases.invoice_details', compact('invoice'));
    }
}
