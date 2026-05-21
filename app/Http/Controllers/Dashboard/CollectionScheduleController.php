<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderPaymentInstallment;
use App\Services\CashService;
use App\Services\CollectionScheduleService;
use App\Services\OrderFinancialService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CollectionScheduleController extends Controller
{
    public function __construct(
        protected CollectionScheduleService $schedule,
        protected OrderFinancialService $orderFinancial,
        protected CashService $cashService
    ) {}

    public function index(Request $request)
    {
        $query = $this->schedule->applyFilters($this->schedule->baseQuery(), $request);

        $orders = $query->orderByDesc('created_at')->get();

        if ($orders->isNotEmpty() && $orders->count() <= 30) {
            $repaired = $this->schedule->repairOrdersCollection($orders, $this->cashService, $this->orderFinancial);
            if ($repaired > 0) {
                session()->flash('success', "تم مزامنة {$repaired} طلب — خُصم المبالغ من الأقساط المسدّدة سابقاً.");
                $orders = $query->orderByDesc('created_at')->get();
            }
        }

        $clientGroups = $this->schedule->paginateClientGroups($orders, 8);

        $clients = Client::query()
            ->whereHas('orders', fn ($q) => $q->where('remaining', '>', 0))
            ->orderBy('name')
            ->get(['id', 'name']);

        $summary = $this->schedule->summaryCounts();
        $dueSoonDays = $this->schedule->dueSoonDays();

        $filters = $request->only([
            'search', 'client_id', 'order_id', 'schedule_status', 'due_from', 'due_to', 'sort',
        ]);

        $clientOrders = $request->filled('client_id')
            ? $this->schedule->clientOpenOrders((int) $request->client_id)
            : [];

        $selectedClient = $request->filled('client_id')
            ? Client::find($request->client_id)
            : null;

        return view('dashboard.collection-schedules.index', compact(
            'clientGroups',
            'clients',
            'summary',
            'dueSoonDays',
            'filters',
            'clientOrders',
            'selectedClient'
        ));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->remaining <= 0) {
            return back()->with('error', 'هذا الطلب مسدد بالكامل.');
        }

        $request->validate([
            'collection_notes' => 'nullable|string|max:1000',
        ]);

        $order->update([
            'collection_notes' => $request->collection_notes,
        ]);

        return back()->with('success', "تم حفظ ملاحظات الطلب #{$order->order_number}");
    }

    public function storeInstallments(Request $request, Order $order)
    {
        if ($order->remaining <= 0) {
            return back()->with('error', 'الطلب مسدد بالكامل.');
        }

        $request->validate([
            'installments' => 'required|array|min:1',
            'installments.*.amount' => 'required|numeric|min:0.01',
            'installments.*.due_at' => 'required|date',
            'installments.*.notes' => 'nullable|string|max:255',
        ]);

        try {
            $this->schedule->saveInstallments($order, $request->installments);

            return back()->with('success', "تم حفظ تقسيط الطلب #{$order->order_number}");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function markInstallmentPaid(OrderPaymentInstallment $installment)
    {
        try {
            $order = $this->schedule->collectInstallment(
                $installment,
                $this->cashService,
                $this->orderFinancial
            );

            return back()->with(
                'success',
                "تم تحصيل القسط وتسجيل الدفعة — المتبقي على الطلب #{$order->order_number}: ".number_format($order->remaining, 2).' ج.س'
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل تحصيل القسط: '.$e->getMessage());
        }
    }
}
