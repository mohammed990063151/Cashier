<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CashService;
use App\Services\CollectionScheduleService;
use App\Services\OrderFinancialService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function __construct(
        protected CashService $cashService,
        protected OrderFinancialService $orderFinancial,
        protected CollectionScheduleService $collectionSchedule,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with(['client', 'payments.receipts', 'products', 'paymentInstallments']);
        $paymentStatus = $request->payment_status;
        $logOrderId = $request->filled('log_order') ? (int) $request->log_order : null;

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('order_id')) {
            $query->where('id', $request->order_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($paymentStatus === null || $paymentStatus === '') {
            $query->where(function ($q) use ($logOrderId) {
                $q->where('remaining', '>', 0.009);
                if ($logOrderId) {
                    $q->orWhere('id', $logOrderId);
                }
            });
            $paymentStatus = 'due';
        } else {
            $this->orderFinancial->applyPaymentStatusFilter($query, $paymentStatus);
        }

        $orders = $query->orderByDesc('created_at')->get();

        if ($orders->isNotEmpty() && $orders->count() <= 30) {
            $repaired = $this->collectionSchedule->repairOrdersCollection(
                $orders,
                $this->cashService,
                $this->orderFinancial
            );
            if ($repaired > 0) {
                session()->flash('success', "تم مزامنة {$repaired} طلب — خُصم المبالغ المسدّدة من المتبقي.");
                $orders = $query->orderByDesc('created_at')->get();
            }
        }

        $clientGroups = $this->collectionSchedule->paginateClientGroups($orders, 8);

        $clients = Client::query()
            ->whereHas('orders', fn ($q) => $q->where('remaining', '>', 0))
            ->orderBy('name')
            ->get(['id', 'name']);

        $clientOrders = $request->filled('client_id')
            ? $this->collectionSchedule->clientOpenOrders((int) $request->client_id)
            : [];

        $selectedClient = $request->filled('client_id')
            ? Client::find($request->client_id)
            : null;

        $filters = $request->only(['search', 'client_id', 'order_id', 'payment_status']);

        return view('dashboard.payments.index', compact(
            'clientGroups',
            'paymentStatus',
            'clients',
            'clientOrders',
            'selectedClient',
            'filters'
        ));
    }

    public function clientOrders(Client $client)
    {
        return response()->json([
            'orders' => $this->collectionSchedule->clientOpenOrders($client->id),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank',
            'notes' => 'nullable|string|max:500',
            'bank_receipts' => 'required_if:method,bank|nullable|array|min:1',
            'bank_receipts.*' => 'image|mimes:jpeg,jpg,png,webp|max:4096',
        ], [
            'bank_receipts.required_if' => 'أرفق صورة واحدة على الأقل لإشعار التحويل البنكي.',
            'bank_receipts.min' => 'أرفق صورة واحدة على الأقل لإشعار التحويل البنكي.',
        ]);

        $order = Order::with(['payments', 'paymentInstallments', 'products'])->findOrFail($request->order_id);
        $maxAllowed = $this->paymentService->maxAllowedAmount($order);

        if ($request->amount > $maxAllowed + 0.02) {
            return redirect()->back()->with('error', "المبلغ أكبر من المتبقي ({$maxAllowed})")->withInput();
        }

        $data = $request->only(['order_id', 'amount', 'method', 'notes']);

        try {
            $payment = Payment::create($data);

            if ($request->method === 'bank') {
                $this->paymentService->attachReceiptFiles($payment, $request->file('bank_receipts', []));
            }

            $order->load('client');
            $this->cashService->record(
                'add',
                (float) $request->amount,
                CashService::orderPaymentDescription($order, (float) $request->amount),
                'payment',
                now(),
                $order->id,
                $payment->id
            );

            $order = $this->orderFinancial->syncOrderTotals($order->fresh(['products', 'payments', 'paymentInstallments']));
            $this->collectionSchedule->resyncInstallmentsFromPayments($order);

            $msg = 'تم إضافة الدفعة وتسجيلها في الخزينة.';
            if ($order->remaining <= 0.009) {
                $msg .= ' الطلب مسدد بالكامل.';
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            if (isset($payment)) {
                $this->paymentService->deleteAllReceipts($payment);
                $payment->delete();
            }

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    protected function redirectBackToPayments(Order $order, string $message, string $type = 'success')
    {
        $params = array_filter([
            'log_order' => $order->id,
            'client_id' => request('client_id'),
            'order_id' => request('order_id'),
            'search' => request('search'),
            'payment_status' => request('payment_status'),
        ], fn ($v) => $v !== null && $v !== '');

        session()->flash('open_log_order', $order->id);

        return redirect()->route('dashboard.payments.index', $params)->with($type, $message);
    }

    public function update(Request $request, Payment $payment)
    {
        $rules = [
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'bank_receipts' => 'nullable|array',
            'bank_receipts.*' => 'image|mimes:jpeg,jpg,png,webp|max:4096',
        ];

        if ($payment->method !== 'cash_at_sale') {
            $rules['method'] = 'required|in:cash,bank';
        }

        $request->validate($rules);

        $request->merge([
            'log_order' => $request->input('log_order', $payment->order_id),
        ]);

        try {
            $order = $this->paymentService->updatePayment(
                $payment,
                $request->only(['amount', 'method', 'notes']),
                $request->file('bank_receipts', [])
            );

            $msg = 'تم التعديل — المتبقي: '.number_format($order->remaining, 2).' ج.س';

            return $this->redirectAfterPaymentChange($order, $msg);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, Payment $payment)
    {
        try {
            $order = $this->paymentService->deletePayment($payment);

            return $this->redirectAfterPaymentChange(
                $order,
                'تم الحذف — المتبقي: '.number_format($order->remaining, 2).' ج.س'
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function paymentLog(Order $order)
    {
        $order->load(['payments.receipts', 'client']);
        $summary = $this->orderFinancial->calculate($order);

        return view('dashboard.payments._payment_log_body', compact('order', 'summary'));
    }

    public function showPayments(Order $order)
    {
        return redirect()->route('dashboard.payments.index', array_filter([
            'log_order' => $order->id,
            'client_id' => request('client_id'),
            'order_id' => request('order_id'),
            'search' => request('search'),
            'payment_status' => request('payment_status'),
        ]));
    }

    protected function redirectAfterPaymentChange(Order $order, string $message): \Illuminate\Http\RedirectResponse
    {
        $params = array_filter([
            'log_order' => $order->id,
            'client_id' => request('client_id'),
            'order_id' => request('order_id'),
            'search' => request('search'),
            'payment_status' => request('payment_status'),
        ]);

        session()->flash('open_log_order', $order->id);

        return redirect()->route('dashboard.payments.index', $params)->with('success', $message);
    }

    protected function storeBankReceipt($file): string
    {
        $dir = public_path('uploads/payment_receipts');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $file->hashName();
        $file->move($dir, $filename);

        return $filename;
    }

    protected function deleteBankReceiptFile(?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path('uploads/payment_receipts/'.$filename);
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
