@php
    $fin = app(\App\Services\OrderFinancialService::class);
    $schedule = app(\App\Services\CollectionScheduleService::class);
    $data = $fin->calculate($order);
    $status = $fin->paymentStatus($order);
    $collectNow = $schedule->collectableInstallmentNow($order);
@endphp

<div class="order-detail-panel">
    <div class="order-detail-head">
        <div>
            <span class="order-number-badge">{{ $order->order_number }}</span>
            <span class="label {{ $fin->paymentStatusClass($status) }}" style="margin-right:8px;font-size:13px;">
                {{ $fin->paymentStatusLabel($status) }}
            </span>
        </div>
        <div class="order-detail-remaining {{ $data['remaining'] > 0 ? 'is-due' : 'is-paid' }}">
            {{ number_format($data['remaining'], 2) }} <small>ج.س متبقي</small>
        </div>
    </div>

    <div class="order-detail-stats">
        <span><label>الإجمالي</label> {{ number_format($data['totalSale'], 2) }}</span>
        <span><label>مدفوع</label> <strong class="text-primary">{{ number_format($data['totalPaid'], 2) }}</strong></span>
        <span><label>بعد الخصم</label> {{ number_format($data['totalAfterDiscount'], 2) }}</span>
    </div>

    <div class="order-detail-actions">
        @if($collectNow && $data['remaining'] > 0)
        <button type="button" class="btn btn-warning btn-collect-today collect-today-btn"
                data-toggle="modal" data-target="#paymentModal"
                data-order-id="{{ $order->id }}"
                data-order-number="{{ $order->order_number }}"
                data-client-name="{{ $order->client->name }}"
                data-amount="{{ $collectNow['amount'] }}"
                data-remaining="{{ $data['remaining'] }}"
                data-label="{{ $collectNow['label'] }}">
            <i class="fa fa-bolt"></i> تحصيل {{ $collectNow['label'] }} ({{ number_format($collectNow['amount'], 2) }})
        </button>
        @endif
        @if($data['remaining'] > 0)
        <button type="button" class="btn btn-success add-payment-btn"
                data-toggle="modal" data-target="#paymentModal"
                data-order-id="{{ $order->id }}"
                data-order-number="{{ $order->order_number }}"
                data-client-name="{{ $order->client->name }}"
                data-remaining="{{ $data['remaining'] }}">
            <i class="fa fa-money"></i> دفعة
        </button>
        @endif
        <a href="{{ route('dashboard.collection-schedules.index', ['client_id' => $order->client_id, 'order_id' => $order->id]) }}" class="btn btn-primary">
            <i class="fa fa-calendar"></i> أقساط
        </a>
        <button type="button" class="btn btn-info btn-view-payment-log" data-order-id="{{ $order->id }}">
            <i class="fa fa-list"></i> السجل
        </button>
    </div>
</div>
