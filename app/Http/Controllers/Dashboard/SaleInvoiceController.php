<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\SaleInvoice;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


use App\Models\Client;

use Illuminate\Http\Request;

class SaleInvoiceController extends Controller
{
    public function index()
    {
        $invoices = SaleInvoice::with('client')->latest()->paginate(20);
        return view('dashboard.sale_invoices.index', compact('invoices'));
    }

    public function create()
    {
        $clients = Client::all();
        return view('sale_invoices.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'items.*.product_name' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function() use ($request) {
            $invoice = SaleInvoice::create([
                'invoice_number' => 'INV-'.time(),
                'client_id' => $request->client_id,
                'invoice_date' => $request->invoice_date,
                'total_amount' => 0,
            ]);

            $total = 0;
            foreach($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $invoice->update(['total_amount' => $total]);
        });

        return redirect()->route('sale_invoices.index')->with('success','تم حفظ الفاتورة بنجاح');
    }

    public function show(SaleInvoice $saleInvoice)
    {
        return view('sale_invoices.show', compact('saleInvoice'));
    }

    public function print(SaleInvoice $saleInvoice)
    {
        $pdf = PDF::loadView('sale_invoices.print', compact('saleInvoice'));
        return $pdf->download("فاتورة-{$saleInvoice->invoice_number}.pdf");
    }
}

