<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPaymentInstallment;
use App\Models\Payment;
use App\Services\CashService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CollectionScheduleService
{
    public function dueSoonDays(): int
    {
        return max(1, (int) config('collection.due_soon_days', 7));
    }

    /**
     * @return Builder<Order>
     */
    public function baseQuery(): Builder
    {
        return Order::query()
            ->with(['client', 'paymentInstallments'])
            ->where('remaining', '>', 0);
    }

    /**
     * @return Builder<OrderPaymentInstallment>
     */
    protected function unpaidInstallmentQuery(): Builder
    {
        return OrderPaymentInstallment::query()
            ->whereNull('paid_at')
            ->whereHas('order', fn ($q) => $q->where('remaining', '>', 0));
    }

    /**
     * @return Builder<Order>
     */
    public function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('order_id')) {
            $query->where('id', $request->order_id);
        }

        $today = Carbon::today();
        $soonEnd = $today->copy()->addDays($this->dueSoonDays());
        $status = $request->input('schedule_status', 'all');

        if ($request->filled('due_from') || $request->filled('due_to')) {
            $from = $request->due_from;
            $to = $request->due_to;
            $query->whereHas('paymentInstallments', function ($q) use ($from, $to) {
                $q->whereNull('paid_at');
                if ($from) {
                    $q->whereDate('due_at', '>=', $from);
                }
                if ($to) {
                    $q->whereDate('due_at', '<=', $to);
                }
            });
        }

        match ($status) {
            'no_date' => $query->whereDoesntHave('paymentInstallments', fn ($q) => $q->whereNull('paid_at')),
            'overdue' => $query->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')->whereDate('due_at', '<', $today)),
            'due_today' => $query->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')->whereDate('due_at', $today)),
            'due_soon' => $query->whereHas('paymentInstallments', function ($q) use ($today, $soonEnd) {
                $q->whereNull('paid_at')->whereBetween('due_at', [$today->copy()->addDay(), $soonEnd]);
            }),
            'upcoming' => $query->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')->whereDate('due_at', '>', $soonEnd)),
            'alert' => $query->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')->whereDate('due_at', '<=', $soonEnd)),
            'has_installments' => $query->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')),
            default => null,
        };

        return match ($request->input('sort', 'due_asc')) {
            'due_desc' => $query->orderByDesc(
                OrderPaymentInstallment::select('due_at')
                    ->whereColumn('order_id', 'orders.id')
                    ->whereNull('paid_at')
                    ->orderBy('due_at')
                    ->limit(1)
            ),
            'remaining_desc' => $query->orderByDesc('remaining'),
            'remaining_asc' => $query->orderBy('remaining'),
            'client' => $query->orderBy(
                \App\Models\Client::select('name')->whereColumn('clients.id', 'orders.client_id'),
                'asc'
            ),
            default => $query->orderBy(
                OrderPaymentInstallment::select('due_at')
                    ->whereColumn('order_id', 'orders.id')
                    ->whereNull('paid_at')
                    ->orderBy('due_at')
                    ->limit(1)
            ),
        };
    }

    public function scheduleStatus(?Carbon $dueAt, bool $isPaid = false): string
    {
        if ($isPaid) {
            return 'paid';
        }

        if (! $dueAt) {
            return 'no_date';
        }

        $today = Carbon::today();
        $due = $dueAt->copy()->startOfDay();

        if ($due->lt($today)) {
            return 'overdue';
        }

        if ($due->equalTo($today)) {
            return 'due_today';
        }

        if ($due->lte($today->copy()->addDays($this->dueSoonDays()))) {
            return 'due_soon';
        }

        return 'upcoming';
    }

    public function scheduleStatusLabel(string $status): string
    {
        return match ($status) {
            'overdue' => 'متأخر',
            'due_today' => 'مستحق اليوم',
            'due_soon' => 'قريب السداد',
            'upcoming' => 'لاحقاً',
            'no_date' => 'بدون أقساط',
            'paid' => 'مسدد',
            default => '—',
        };
    }

    public function scheduleStatusClass(string $status): string
    {
        return match ($status) {
            'overdue' => 'label-danger',
            'due_today' => 'label-warning',
            'due_soon' => 'label-info',
            'upcoming' => 'label-primary',
            'no_date' => 'label-default',
            'paid' => 'label-success',
            default => 'label-default',
        };
    }

    /**
     * @return Collection<int, array{installment: OrderPaymentInstallment, order: Order, status: string}>
     */
    public function dashboardAlerts(int $limit = 15): Collection
    {
        $soonEnd = Carbon::today()->addDays($this->dueSoonDays());

        return $this->unpaidInstallmentQuery()
            ->with(['order.client'])
            ->whereDate('due_at', '<=', $soonEnd)
            ->orderBy('due_at')
            ->limit($limit)
            ->get()
            ->map(fn ($inst) => [
                'installment' => $inst,
                'order' => $inst->order,
                'status' => $this->scheduleStatus($inst->due_at),
            ]);
    }

    public function dashboardAlertsCount(): int
    {
        $soonEnd = Carbon::today()->addDays($this->dueSoonDays());

        return $this->unpaidInstallmentQuery()
            ->whereDate('due_at', '<=', $soonEnd)
            ->count();
    }

    /**
     * @return Collection<int, array{installment: OrderPaymentInstallment, order: Order, status: string}>
     */
    public function dueTodayAlerts(): Collection
    {
        return $this->unpaidInstallmentQuery()
            ->with(['order.client'])
            ->whereDate('due_at', Carbon::today())
            ->orderBy('amount')
            ->get()
            ->map(fn ($inst) => [
                'installment' => $inst,
                'order' => $inst->order,
                'status' => 'due_today',
            ]);
    }

    /**
     * @return array{overdue: int, due_today: int, due_soon: int, no_date: int, total_remaining: float, with_installments: int}
     */
    public function summaryCounts(): array
    {
        $today = Carbon::today();
        $soonEnd = $today->copy()->addDays($this->dueSoonDays());
        $unpaid = $this->unpaidInstallmentQuery();

        $ordersWithRemaining = Order::where('remaining', '>', 0);

        return [
            'overdue' => (clone $unpaid)->whereDate('due_at', '<', $today)->count(),
            'due_today' => (clone $unpaid)->whereDate('due_at', $today)->count(),
            'due_soon' => (clone $unpaid)->whereBetween('due_at', [$today->copy()->addDay(), $soonEnd])->count(),
            'no_date' => (clone $ordersWithRemaining)->whereDoesntHave('paymentInstallments', fn ($q) => $q->whereNull('paid_at'))->count(),
            'with_installments' => (clone $ordersWithRemaining)->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at'))->count(),
            'total_remaining' => (float) (clone $ordersWithRemaining)->sum('remaining'),
        ];
    }

    /**
     * @param  array<int, array{amount: mixed, due_at: mixed, notes?: mixed}>  $rows
     */
    public function saveInstallments(Order $order, array $rows): void
    {
        $order->refresh();
        $remaining = (float) $order->remaining;

        if ($remaining <= 0) {
            throw new \InvalidArgumentException('الطلب مسدد بالكامل.');
        }

        $total = 0;
        $normalized = [];

        foreach ($rows as $i => $row) {
            $amount = round((float) ($row['amount'] ?? 0), 2);
            $dueAt = $row['due_at'] ?? null;

            if ($amount <= 0 || ! $dueAt) {
                continue;
            }

            $total += $amount;
            $normalized[] = [
                'amount' => $amount,
                'due_at' => Carbon::parse($dueAt)->toDateString(),
                'notes' => $row['notes'] ?? null,
                'sort_order' => $i,
            ];
        }

        if (empty($normalized)) {
            throw new \InvalidArgumentException('أضف قسطاً واحداً على الأقل.');
        }

        if (abs($total - $remaining) > 0.02) {
            throw new \InvalidArgumentException(
                "مجموع الأقساط ({$total}) يجب أن يساوي المتبقي ({$remaining})."
            );
        }

        DB::transaction(function () use ($order, $normalized) {
            $order->paymentInstallments()->whereNull('paid_at')->delete();

            foreach ($normalized as $row) {
                $order->paymentInstallments()->create($row);
            }

            $this->syncOrderDueDateFromInstallments($order->fresh());
        });
    }

    public function syncOrderDueDateFromInstallments(Order $order): void
    {
        $next = $order->paymentInstallments()
            ->whereNull('paid_at')
            ->orderBy('due_at')
            ->first();

        $order->update([
            'payment_due_at' => $next?->due_at,
        ]);
    }

    public function allocatePaymentToInstallments(Order $order, float $paymentAmount): void
    {
        if ($paymentAmount <= 0) {
            return;
        }

        $left = $paymentAmount;
        $installments = $order->paymentInstallments()
            ->whereNull('paid_at')
            ->orderBy('due_at')
            ->orderBy('sort_order')
            ->get();

        foreach ($installments as $installment) {
            if ($left < $installment->amount - 0.009) {
                break;
            }

            $installment->update(['paid_at' => now()]);
            $left -= $installment->amount;
        }

        $this->syncOrderDueDateFromInstallments($order->fresh());
    }

    /** إعادة مطابقة الأقساط المسددة مع مجموع الدفعات الفعلية. */
    public function resyncInstallmentsFromPayments(Order $order): void
    {
        $order->loadMissing(['payments', 'paymentInstallments']);

        if ($order->paymentInstallments->isEmpty()) {
            return;
        }

        $order->paymentInstallments()->update(['paid_at' => null]);

        $totalPayments = (float) $order->payments->sum('amount');

        if ($totalPayments > 0) {
            $this->allocatePaymentToInstallments($order->fresh(['paymentInstallments']), $totalPayments);
        } else {
            $this->syncOrderDueDateFromInstallments($order->fresh());
        }
    }

    public function orderScheduleSummary(Order $order): array
    {
        $unpaid = $order->paymentInstallments->whereNull('paid_at');
        $next = $unpaid->sortBy('due_at')->first();

        return [
            'installments_count' => $unpaid->count(),
            'scheduled_total' => (float) $unpaid->sum('amount'),
            'next_due' => $next?->due_at,
            'next_status' => $next ? $this->scheduleStatus($next->due_at) : 'no_date',
        ];
    }

    /**
     * أول قسط مستحق اليوم أو متأخر للتحصيل المباشر.
     *
     * @return array{installment: OrderPaymentInstallment, amount: float, label: string, status: string}|null
     */
    public function collectableInstallmentNow(Order $order): ?array
    {
        $today = Carbon::today();

        $installment = $order->paymentInstallments()
            ->whereNull('paid_at')
            ->whereDate('due_at', '<=', $today)
            ->orderBy('due_at')
            ->first();

        if (! $installment) {
            return null;
        }

        $status = $this->scheduleStatus($installment->due_at);
        $label = $status === 'due_today'
            ? 'مستحق اليوم'
            : 'متأخر — '.$installment->due_at->format('d/m/Y');

        return [
            'installment' => $installment,
            'amount' => (float) $installment->amount,
            'label' => $label,
            'status' => $status,
        ];
    }

    /**
     * @return array<int, array{id: int, order_number: string, remaining: float}>
     */
    /**
     * تسجيل تحصيل قسط: دفعة فعلية + خزينة + تحديث المتبقي.
     */
    public function collectInstallment(OrderPaymentInstallment $installment, CashService $cashService, OrderFinancialService $orderFinancial): Order
    {
        if ($installment->paid_at) {
            throw new \InvalidArgumentException('القسط مسدد مسبقاً.');
        }

        $order = $installment->order()->with(['products', 'payments', 'paymentInstallments'])->firstOrFail();

        if ($order->remaining <= 0) {
            throw new \InvalidArgumentException('لا يوجد متبقي على هذا الطلب.');
        }

        $amount = (float) $installment->amount;

        if ($amount > $order->remaining + 0.02) {
            $amount = (float) $order->remaining;
        }

        return DB::transaction(function () use ($installment, $order, $amount, $cashService, $orderFinancial) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => 'cash',
                'notes' => 'تحصيل قسط — موعد '.$installment->due_at->format('d/m/Y'),
            ]);

            $installment->update(['paid_at' => now()]);

            $order = $orderFinancial->syncOrderTotals($order->fresh(['products', 'payments', 'paymentInstallments']));

            $cashService->record(
                'add',
                $amount,
                CashService::orderPaymentDescription($order, $amount).' — قسط',
                'payment',
                now(),
                $order->id,
                $payment->id
            );

            $this->syncOrderDueDateFromInstallments($order->fresh());

            return $order;
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Order>  $orders
     */
    public function paginateClientGroups(Collection $orders, int $perPage = 8): LengthAwarePaginator
    {
        $groups = $orders->groupBy('client_id')->map(function (Collection $clientOrders) {
            $client = $clientOrders->first()->client;

            return [
                'client' => $client,
                'orders' => $clientOrders->sortByDesc('created_at')->values(),
                'orders_count' => $clientOrders->count(),
                'total_remaining' => (float) $clientOrders->sum(fn ($o) => app(OrderFinancialService::class)->calculate($o)['remaining']),
            ];
        })->sortBy(fn ($g) => $g['client']->name)->values();

        $page = max(1, (int) request('page', 1));
        $items = $groups->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $groups->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * إصلاح أقساط مُعلَّمة مسددة دون دفعة مسجّلة (بيانات قديمة).
     */
    public function repairMissingInstallmentPayments(
        Order $order,
        CashService $cashService,
        OrderFinancialService $orderFinancial
    ): bool {
        $order->loadMissing(['paymentInstallments', 'payments', 'products']);
        $repaired = false;

        foreach ($order->paymentInstallments->whereNotNull('paid_at') as $installment) {
            $hasPayment = Payment::where('order_id', $order->id)
                ->where('amount', $installment->amount)
                ->where('notes', 'like', '%تحصيل قسط%')
                ->exists();

            if ($hasPayment) {
                continue;
            }

            DB::transaction(function () use ($installment, $order, $cashService, $orderFinancial, &$repaired) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'amount' => $installment->amount,
                    'method' => 'cash',
                    'notes' => 'تحصيل قسط — موعد '.$installment->due_at->format('d/m/Y').' (إصلاح تلقائي)',
                ]);

                $cashService->record(
                    'add',
                    $installment->amount,
                    CashService::orderPaymentDescription($order, (float) $installment->amount).' — إصلاح قسط',
                    'payment',
                    $installment->paid_at ?? now(),
                    $order->id,
                    $payment->id
                );

                $orderFinancial->syncOrderTotals($order->fresh(['products', 'payments', 'paymentInstallments']));
                $repaired = true;
            });
        }

        if ($repaired) {
            $this->syncOrderDueDateFromInstallments($order->fresh());
        }

        return $repaired;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Order>  $orders
     */
    public function repairOrdersCollection(Collection $orders, CashService $cash, OrderFinancialService $fin): int
    {
        $count = 0;
        foreach ($orders as $order) {
            if ($this->repairMissingInstallmentPayments($order, $cash, $fin)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array<int, array{id: int, order_number: string, remaining: float}>
     */
    public function clientOpenOrders(int $clientId): array
    {
        return Order::query()
            ->where('client_id', $clientId)
            ->where('remaining', '>', 0)
            ->orderByDesc('created_at')
            ->get(['id', 'order_number', 'remaining'])
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'remaining' => (float) $o->remaining,
            ])
            ->values()
            ->all();
    }
}
