@php
    $expandAll = ($clientGroups->count() === 1) || !empty($filters['client_id'] ?? null) || !empty($filters['order_id'] ?? null);
    $rowPartial = $rowPartial ?? 'dashboard.partials._order_payment_row';
@endphp

@forelse($clientGroups as $group)
@php
    $client = $group['client'];
    $collapseId = 'client-orders-'.$client->id;
    $isOpen = $expandAll || $group['orders_count'] === 1;
@endphp
<div class="client-group-card">
    <div class="client-group-summary">
        <div class="client-group-info">
            <div class="client-avatar"><i class="fa fa-user"></i></div>
            <div>
                <h3 class="client-group-name">{{ $client->name }}</h3>
                <p class="client-group-meta">
                    <span class="badge bg-blue">{{ $group['orders_count'] }} طلب</span>
                    @if($group['orders_count'] > 1)
                        <span class="text-muted"> — اضغط لعرض تفاصيل كل طلب</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="client-group-totals">
            <div class="total-remaining-box">
                <small>إجمالي المتبقي</small>
                <strong>{{ number_format($group['total_remaining'], 2) }}</strong>
                <span>ج.س</span>
            </div>
            <button type="button" class="btn btn-primary btn-lg btn-toggle-orders {{ $isOpen ? '' : 'collapsed' }}"
                    data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="{{ $isOpen ? 'true' : 'false' }}">
                <i class="fa fa-chevron-down"></i>
                <span class="btn-text-show">{{ $isOpen ? 'إخفاء الطلبات' : 'عرض الطلبات ('.$group['orders_count'].')' }}</span>
            </button>
        </div>
    </div>

    <div id="{{ $collapseId }}" class="collapse client-orders-collapse {{ $isOpen ? 'in' : '' }}">
        <div class="client-orders-inner">
            @foreach($group['orders'] as $order)
                @include($rowPartial, ['order' => $order])
            @endforeach
        </div>
    </div>
</div>
@empty
<div class="empty-state-box">
    <i class="fa fa-inbox fa-3x"></i>
    <p>لا توجد نتائج. غيّر الفلتر أو اختر عميلاً.</p>
</div>
@endforelse

@if(isset($clientGroups) && method_exists($clientGroups, 'links'))
<div class="text-center" style="margin-top:16px;">{{ $clientGroups->links() }}</div>
@endif
