<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseInvoiceRequest;
use App\Http\Requests\UpdatePurchaseInvoiceRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Setting;
use App\Models\Supplier;
use App\Services\PurchaseInvoiceService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Mpdf\Mpdf;

class PurchaseInvoiceController extends Controller
{
    public function __construct(
        protected PurchaseInvoiceService $purchaseService
    ) {}

    public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['supplier', 'items.product']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%'.$request->search.'%')
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', '%'.$request->search.'%'));
            });
        }

        $purchaseInvoices = $query->latest()->paginate(10);

        return view('dashboard.purchase_invoices.index', compact('purchaseInvoices'));
    }

    public function create()
    {
        return view('dashboard.purchase_invoices.create', $this->formData());
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(?PurchaseInvoice $purchaseInvoice = null): array
    {
        $products = Product::orderBy('name')->get();

        return [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'productsCatalog' => $this->productsCatalog($products),
            'purchaseInvoice' => $purchaseInvoice,
        ];
    }

    public function store(StorePurchaseInvoiceRequest $request)
    {
        try {
            $invoice = $this->purchaseService->create(
                $request->only(['supplier_id', 'invoice_date', 'paid', 'payment_due_at', 'payment_notes']),
                $request->items,
                $request->installments
            );

            return redirect()
                ->route('dashboard.purchase-invoices.show', $invoice->id)
                ->with('success', 'تم إنشاء فاتورة الشراء بنجاح.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load(['items.product', 'paymentInstallments']);

        return view('dashboard.purchase_invoices.edit', $this->formData($purchaseInvoice));
    }

    public function update(UpdatePurchaseInvoiceRequest $request, PurchaseInvoice $purchaseInvoice)
    {
        try {
            $invoice = $this->purchaseService->update(
                $purchaseInvoice,
                $request->only(['supplier_id', 'invoice_date', 'paid', 'payment_due_at', 'payment_notes']),
                $request->items
            );

            return redirect()
                ->route('dashboard.purchase-invoices.show', $invoice->id)
                ->with('success', 'تم تعديل فاتورة الشراء بنجاح.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load(['supplier', 'items.product', 'payments', 'paymentInstallments']);

        return view('dashboard.purchase_invoices.show', compact('purchaseInvoice'));
    }

    public function print(PurchaseInvoice $purchaseInvoice)
    {
        $setting = Setting::first();
        $purchaseInvoice->load(['supplier', 'items.product', 'paymentInstallments']);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'default_font' => 'dejavusans',
            'isRemoteEnabled' => true,
            'shrink_tables_to_fit' => 1,
        ]);

        $html = view('dashboard.purchase_invoices.pdf', compact('purchaseInvoice', 'setting'))->render();
        $mpdf->WriteHTML($html);

        return $mpdf->Output("purchase_invoice_{$purchaseInvoice->id}.pdf", 'I');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     * @return array<int, array<string, mixed>>
     */
    protected function productsCatalog($products): array
    {
        return $products->map(function (Product $p) {
            $entry = \App\Support\SaleUnits::toEntryValues($p);

            return [
                'id' => $p->id,
                'name' => $p->name,
                // سعر الشراء بوحدة القياس المعتمدة للمنتج (كرتونة/حبة/كيلو)
                'purchase_price' => (float) $entry['purchase_price'],
                'pieces_per_carton' => max(1, (int) ($p->pieces_per_carton ?? 12)),
                'sale_mode' => $p->sale_mode,
                'measure_unit' => $p->measure_unit ?? 'piece',
            ];
        })->values()->all();
    }
}
