<?php

namespace App\Services;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentInstallment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SupplierPaymentScheduleService
{
    public function dueSoonDays(): int
    {
        return max(1, (int) config('supplier_collection.due_soon_days', 7));
    }

    /**
     * @return Builder<PurchaseInvoice>
     */
    public function baseQuery(): Builder
    {
        return PurchaseInvoice::query()
            ->with(['supplier', 'paymentInstallments'])
            ->where('remaining', '>', 0);
    }

    /**
     * @return Builder<PurchaseInvoice>
     */
    public function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $today = Carbon::today();
        $soonEnd = $today->copy()->addDays($this->dueSoonDays());
        $status = $request->input('schedule_status', 'all');

        match ($status) {
            'no_date' => $query->whereDoesntHave('paymentInstallments', fn ($q) => $q->whereNull('paid_at')),
            'overdue' => $query->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')->whereDate('due_at', '<', $today)),
            'due_today' => $query->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')->whereDate('due_at', $today)),
            'due_soon' => $query->whereHas('paymentInstallments', function ($q) use ($today, $soonEnd) {
                $q->whereNull('paid_at')->whereBetween('due_at', [$today->copy()->addDay(), $soonEnd]);
            }),
            'alert' => $query->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')->whereDate('due_at', '<=', $soonEnd)),
            'has_installments' => $query->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')),
            default => null,
        };

        return $query->orderByDesc('created_at');
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
        if ($dueAt->lt($today)) {
            return 'overdue';
        }
        if ($dueAt->isSameDay($today)) {
            return 'due_today';
        }
        if ($dueAt->lte($today->copy()->addDays($this->dueSoonDays()))) {
            return 'due_soon';
        }

        return 'upcoming';
    }

    public function scheduleStatusLabel(string $status): string
    {
        return match ($status) {
            'overdue' => 'متأخر',
            'due_today' => 'مستحق اليوم',
            'due_soon' => 'قريب',
            'upcoming' => 'لاحقاً',
            'paid' => 'مسدد',
            default => 'بدون جدولة',
        };
    }

    public function scheduleStatusClass(string $status): string
    {
        return match ($status) {
            'overdue' => 'label-danger',
            'due_today' => 'label-warning',
            'due_soon' => 'label-info',
            'upcoming' => 'label-default',
            'paid' => 'label-success',
            default => 'label-default',
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function saveInstallments(PurchaseInvoice $invoice, array $rows): void
    {
        $invoice->refresh();
        $remaining = (float) $invoice->remaining;

        if ($remaining <= 0) {
            throw new InvalidArgumentException('الفاتورة مسددة بالكامل.');
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

        if ($normalized === []) {
            throw new InvalidArgumentException('أضف قسطاً واحداً على الأقل بمبلغ وتاريخ.');
        }

        if (abs($total - $remaining) > 0.02) {
            throw new InvalidArgumentException(
                "مجموع الأقساط ({$total}) يجب أن يساوي المتبقي ({$remaining})."
            );
        }

        DB::transaction(function () use ($invoice, $normalized) {
            $invoice->paymentInstallments()->whereNull('paid_at')->delete();

            foreach ($normalized as $row) {
                $invoice->paymentInstallments()->create($row);
            }

            $firstDue = collect($normalized)->min('due_at');
            $invoice->update(['payment_due_at' => $firstDue]);
        });
    }

    public function payInstallment(
        SupplierPaymentInstallment $installment,
        CashService $cashService
    ): PurchaseInvoice {
        if ($installment->paid_at) {
            throw new InvalidArgumentException('هذا القسط مسدد مسبقاً.');
        }

        return DB::transaction(function () use ($installment, $cashService) {
            $invoice = $installment->purchaseInvoice()->with('supplier')->firstOrFail();
            $amount = (float) $installment->amount;

            if ($cashService->getBalance() < $amount) {
                throw new InvalidArgumentException('رصيد الخزينة غير كافٍ.');
            }

            $supplierPayment = SupplierPayment::create([
                'supplier_id' => $invoice->supplier_id,
                'purchase_invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_date' => now()->toDateString(),
                'note' => 'سداد قسط — موعد '.$installment->due_at->format('d/m/Y'),
            ]);

            $cashService->record(
                'deduct',
                $amount,
                "سداد قسط مورد — فاتورة {$invoice->invoice_number}",
                'supplier_payment',
                now(),
                null,
                null,
                $invoice->id,
                null,
                $supplierPayment->id
            );

            $newPaid = round((float) $invoice->paid + $amount, 2);
            $newRemaining = max((float) $invoice->total - $newPaid, 0);

            $invoice->update([
                'paid' => $newPaid,
                'remaining' => $newRemaining,
            ]);

            $installment->update(['paid_at' => now()]);

            if ($newRemaining <= 0.009) {
                $invoice->update(['payment_due_at' => null, 'remaining' => 0]);
                $invoice->paymentInstallments()->whereNull('paid_at')->delete();
            } else {
                $next = $invoice->paymentInstallments()->whereNull('paid_at')->orderBy('due_at')->value('due_at');
                $invoice->update(['payment_due_at' => $next]);
            }

            $invoice->supplier->update([
                'balance' => (float) PurchaseInvoice::where('supplier_id', $invoice->supplier_id)->sum('remaining'),
            ]);

            return $invoice->fresh(['supplier', 'paymentInstallments', 'items.product']);
        });
    }

    /**
     * @param  Collection<int, PurchaseInvoice>  $invoices
     */
    public function paginateSupplierGroups(Collection $invoices, int $perPage = 8): LengthAwarePaginator
    {
        $groups = $invoices->groupBy('supplier_id')->map(function (Collection $supplierInvoices) {
            $supplier = $supplierInvoices->first()->supplier;

            return [
                'supplier' => $supplier,
                'invoices' => $supplierInvoices->sortByDesc('created_at')->values(),
                'invoices_count' => $supplierInvoices->count(),
                'total_remaining' => (float) $supplierInvoices->sum('remaining'),
            ];
        })->sortBy(fn ($g) => $g['supplier']->name)->values();

        $page = max(1, (int) request('page', 1));

        return new LengthAwarePaginator(
            $groups->forPage($page, $perPage)->values(),
            $groups->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * @return array<string, int|float>
     */
    public function summaryCounts(): array
    {
        $today = Carbon::today();
        $soonEnd = $today->copy()->addDays($this->dueSoonDays());
        $base = $this->baseQuery();

        return [
            'total_remaining' => (float) (clone $base)->sum('remaining'),
            'overdue' => (clone $base)->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')->whereDate('due_at', '<', $today))->count(),
            'due_today' => (clone $base)->whereHas('paymentInstallments', fn ($q) => $q->whereNull('paid_at')->whereDate('due_at', $today))->count(),
            'due_soon' => (clone $base)->whereHas('paymentInstallments', function ($q) use ($today, $soonEnd) {
                $q->whereNull('paid_at')->whereBetween('due_at', [$today->copy()->addDay(), $soonEnd]);
            })->count(),
            'no_date' => (clone $base)->whereDoesntHave('paymentInstallments', fn ($q) => $q->whereNull('paid_at'))->count(),
        ];
    }
}
