@php
    $fin = app(\App\Services\OrderFinancialService::class);
    $schedule = app(\App\Services\CollectionScheduleService::class);
    $finData = $fin->calculate($order);
    $orderSummary = $schedule->orderScheduleSummary($order);
    $collectNow = $schedule->collectableInstallmentNow($order);
    $unpaid = $order->paymentInstallments->whereNull('paid_at');
    $paidInst = $order->paymentInstallments->whereNotNull('paid_at');
@endphp

<div class="order-detail-panel schedule-panel">
    <div class="order-detail-head">
        <div>
            <span class="order-number-badge">{{ $order->order_number }}</span>
            <span class="label {{ $schedule->scheduleStatusClass($orderSummary['next_status']) }}">
                {{ $schedule->scheduleStatusLabel($orderSummary['next_status']) }}
            </span>
        </div>
        <div class="order-detail-remaining {{ $finData['remaining'] > 0 ? 'is-due' : 'is-paid' }}">
            {{ number_format($finData['remaining'], 2) }} <small>ج.س متبقي</small>
        </div>
    </div>

    @if($order->paymentInstallments->isNotEmpty())
    <table class="table table-bordered table-condensed installment-mini-table">
        <thead>
            <tr>
                <th>قسط</th>
                <th>المبلغ</th>
                <th>الموعد</th>
                <th>الحالة</th>
                <th>إجراء</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->paymentInstallments as $idx => $inst)
            @php $ist = $schedule->scheduleStatus($inst->due_at, $inst->isPaid()); @endphp
            <tr class="{{ $inst->isPaid() ? 'success' : '' }}">
                <td>{{ $idx + 1 }}</td>
                <td><strong>{{ number_format($inst->amount, 2) }}</strong></td>
                <td>{{ $inst->due_at->format('d/m/Y') }}</td>
                <td><span class="label {{ $schedule->scheduleStatusClass($ist) }}">{{ $schedule->scheduleStatusLabel($ist) }}</span></td>
                <td>
                    @if(!$inst->isPaid())
                    <form method="POST" action="{{ route('dashboard.collection-installments.paid', $inst) }}" style="display:inline;"
                          onsubmit="return confirm('تسجيل تحصيل {{ number_format($inst->amount, 2) }} ج.س وخصمها من المتبقي؟');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-check"></i> تحصيل
                        </button>
                    </form>
                    @else
                    <small class="text-success"><i class="fa fa-check"></i> {{ $inst->paid_at->format('d/m/Y') }}</small>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p class="text-muted small" style="margin:6px 0;">
        مجدول (غير مسدد): {{ number_format($unpaid->sum('amount'), 2) }} —
        محصّل عبر الأقساط: {{ number_format($paidInst->sum('amount'), 2) }} ج.س
    </p>
    @else
    <p class="text-warning" style="margin:8px 0;"><i class="fa fa-info-circle"></i> لم يُقسّم بعد — اضغط «تقسيط» من رأس بطاقة العميل أو أدناه.</p>
    @endif

    <div class="order-detail-actions">
        @if($collectNow)
        <a href="{{ route('dashboard.payments.index', ['client_id' => $order->client_id, 'order_id' => $order->id, 'collect' => 1]) }}"
           class="btn btn-warning">
            <i class="fa fa-bolt"></i> تحصيل {{ $collectNow['label'] }}
        </a>
        @endif
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#installmentModal"
                data-order-id="{{ $order->id }}"
                data-order-number="{{ $order->order_number }}"
                data-client-name="{{ $order->client->name }}"
                data-remaining="{{ $order->remaining }}">
            <i class="fa fa-calendar-plus-o"></i> تقسيط
        </button>
        <a href="{{ route('dashboard.payments.index', ['client_id' => $order->client_id, 'order_id' => $order->id]) }}" class="btn btn-success">
            <i class="fa fa-money"></i> مدفوعات
        </a>
    </div>
</div>
