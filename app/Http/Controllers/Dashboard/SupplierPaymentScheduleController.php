<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\SupplierPaymentInstallment;
use App\Services\CashService;
use App\Services\SupplierPaymentScheduleService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SupplierPaymentScheduleController extends Controller
{
    public function __construct(
        protected SupplierPaymentScheduleService $schedule,
        protected CashService $cashService
    ) {}

    public function index(Request $request)
    {
        $query = $this->schedule->baseQuery();
        $this->schedule->applyFilters($query, $request);

        $invoices = $query->get();
        $supplierGroups = $this->schedule->paginateSupplierGroups($invoices, 8);
        $summary = $this->schedule->summaryCounts();
        $dueSoonDays = $this->schedule->dueSoonDays();

        $suppliers = Supplier::query()
            ->whereHas('purchaseInvoices', fn ($q) => $q->where('remaining', '>', 0))
            ->orderBy('name')
            ->get(['id', 'name']);

        $filters = $request->only(['search', 'supplier_id', 'schedule_status']);

        return view('dashboard.supplier-schedules.index', compact(
            'supplierGroups',
            'summary',
            'suppliers',
            'filters',
            'dueSoonDays'
        ));
    }

    public function storeInstallments(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $request->validate([
            'installments' => 'required|array|min:1',
            'installments.*.amount' => 'required|numeric|min:0.01',
            'installments.*.due_at' => 'required|date',
            'installments.*.notes' => 'nullable|string|max:255',
        ], [
            'installments.required' => 'أضف قسطاً واحداً على الأقل.',
        ]);

        try {
            $this->schedule->saveInstallments($purchaseInvoice, $request->installments);

            return redirect()
                ->route('dashboard.supplier-schedules.index', ['supplier_id' => $purchaseInvoice->supplier_id])
                ->with('success', 'تم حفظ جدولة السداد للمورد.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function markInstallmentPaid(SupplierPaymentInstallment $installment)
    {
        try {
            $invoice = $this->schedule->payInstallment($installment, $this->cashService);

            return redirect()
                ->route('dashboard.supplier-schedules.index', ['supplier_id' => $invoice->supplier_id])
                ->with('success', 'تم سداد القسط وتحديث الفاتورة — المتبقي: '.number_format($invoice->remaining, 2).' ج.س');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
