@php
    $filterAction = $filterAction ?? route('dashboard.payments.index');
    $filters = $filters ?? request()->only(['search', 'client_id', 'order_id', 'payment_status', 'schedule_status', 'due_from', 'due_to', 'sort']);
    $clientOrders = $clientOrders ?? [];
    $showPaymentStatus = $showPaymentStatus ?? true;
    $showScheduleStatus = $showScheduleStatus ?? false;
    $extraFields = $extraFields ?? '';
@endphp

<form method="GET" action="{{ $filterAction }}" class="client-order-filter-panel">
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <label class="filter-label"><i class="fa fa-user"></i> العميل</label>
            <select name="client_id" id="filterClientId" class="form-control input-lg">
                <option value="">— كل العملاء —</option>
                @foreach($clients ?? [] as $client)
                    <option value="{{ $client->id }}" {{ ($filters['client_id'] ?? '') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-sm-12">
            <label class="filter-label"><i class="fa fa-file-text-o"></i> الطلب (اختياري)</label>
            <select name="order_id" id="filterOrderId" class="form-control input-lg">
                <option value="">— كل طلبات العميل —</option>
                @foreach($clientOrders as $ord)
                    <option value="{{ $ord['id'] }}" {{ ($filters['order_id'] ?? '') == $ord['id'] ? 'selected' : '' }}
                            data-remaining="{{ $ord['remaining'] }}">
                        {{ $ord['order_number'] }} — متبقي {{ number_format($ord['remaining'], 2) }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">اتركه فارغاً لعرض كل طلبات العميل المفتوحة</small>
        </div>
        <div class="col-md-4 col-sm-12">
            <label class="filter-label"><i class="fa fa-search"></i> بحث سريع</label>
            <input type="text" name="search" class="form-control input-lg" placeholder="رقم الطلب أو اسم العميل"
                   value="{{ $filters['search'] ?? '' }}">
        </div>
    </div>
    @if($showPaymentStatus)
    <div class="row" style="margin-top:12px;">
        <div class="col-md-12">
            @include($paymentFilterPartial ?? 'dashboard.orders._payment_filter', [
                'filters' => $filters,
                'paymentStatus' => $paymentStatus ?? ($filters['payment_status'] ?? 'all'),
            ])
        </div>
    </div>
    @endif
    {!! $extraFields !!}
    <div class="row" style="margin-top:14px;">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-filter"></i> عرض النتائج</button>
            <a href="{{ $filterAction }}" class="btn btn-default btn-lg">إعادة تعيين</a>
        </div>
    </div>
</form>

@once
@push('styles')
<style>
.client-order-filter-panel {
    background: #f0f4f8;
    border: 2px solid #d2dae2;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}
.client-order-filter-panel .filter-label {
    font-size: 15px;
    font-weight: 700;
    color: #2c3e50;
    display: block;
    margin-bottom: 8px;
}
</style>
@endpush
@endonce
