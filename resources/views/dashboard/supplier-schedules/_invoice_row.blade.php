@php
    $schedule = app(\App\Services\SupplierPaymentScheduleService::class);
    $nextInstallment = $invoice->paymentInstallments->whereNull('paid_at')->sortBy('due_at')->first();
    $status = $schedule->scheduleStatus(
        $nextInstallment?->due_at ?? $invoice->payment_due_at,
        (float) $invoice->remaining <= 0
    );
@endphp
<div class="order-detail-panel" style="margin-bottom:12px;">
    <div class="order-detail-head">
        <div>
            <span class="order-number-badge">{{ $invoice->invoice_number }}</span>
            <span class="label {{ $schedule->scheduleStatusClass($status) }}">{{ $schedule->scheduleStatusLabel($status) }}</span>
        </div>
        <div class="order-detail-remaining is-due">
            {{ number_format($invoice->remaining, 2) }} <small>ج.س متبقي</small>
        </div>
    </div>
    <div class="order-detail-stats">
        <span><label>الإجمالي</label> {{ number_format($invoice->total, 2) }}</span>
        <span><label>مدفوع</label> {{ number_format($invoice->paid, 2) }}</span>
        <span><label>تاريخ الفاتورة</label> {{ optional($invoice->invoice_date)->format('d/m/Y') ?? '—' }}</span>
    </div>
    @if($invoice->paymentInstallments->whereNull('paid_at')->count())
    <ul class="list-unstyled" style="font-size:13px;margin:8px 0;">
        @foreach($invoice->paymentInstallments->whereNull('paid_at') as $inst)
        <li style="margin-bottom:6px;">
            <i class="fa fa-calendar-o"></i>
            {{ number_format($inst->amount, 2) }} ج.س — {{ $inst->due_at->format('d/m/Y') }}
            <form method="POST" action="{{ route('dashboard.supplier-installments.paid', $inst) }}" style="display:inline;" onsubmit="return confirm('تسجيل سداد هذا القسط من الخزينة؟');">
                @csrf
                <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-check"></i> سداد</button>
            </form>
        </li>
        @endforeach
    </ul>
    @endif
    <div class="order-detail-actions">
        <a href="{{ route('dashboard.purchase-invoices.show', $invoice) }}" class="btn btn-default btn-sm"><i class="fa fa-eye"></i> الفاتورة</a>
        <button type="button" class="btn btn-primary btn-sm btn-schedule-installments"
                data-invoice-id="{{ $invoice->id }}"
                data-invoice-number="{{ $invoice->invoice_number }}"
                data-remaining="{{ $invoice->remaining }}"
                data-action="{{ route('dashboard.supplier-schedules.installments', $invoice) }}">
            <i class="fa fa-calendar"></i> جدولة أقساط
        </button>
    </div>
</div>
