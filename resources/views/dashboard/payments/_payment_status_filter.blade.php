@php
    $filterBase = request()->except('payment_status', 'page');
    if (isset($filters) && is_array($filters)) {
        $filterBase = array_merge($filterBase, array_filter($filters, fn ($v, $k) => $k !== 'payment_status' && $v !== '' && $v !== null, ARRAY_FILTER_USE_BOTH));
    }
    $current = $paymentStatus ?? 'due';
@endphp
<div class="payment-status-filter btn-group btn-group-lg" role="group" style="display:flex;flex-wrap:wrap;gap:6px;">
    <a href="{{ route('dashboard.payments.index', $filterBase) }}"
       class="btn {{ $current === 'due' ? 'btn-primary' : 'btn-default' }}">مستحق التحصيل</a>
    <a href="{{ route('dashboard.payments.index', array_merge($filterBase, ['payment_status' => 'partial'])) }}"
       class="btn {{ $current === 'partial' ? 'btn-warning' : 'btn-default' }}">دفع جزئي</a>
    <a href="{{ route('dashboard.payments.index', array_merge($filterBase, ['payment_status' => 'unpaid'])) }}"
       class="btn {{ $current === 'unpaid' ? 'btn-danger' : 'btn-default' }}">متبقي</a>
    <a href="{{ route('dashboard.payments.index', array_merge($filterBase, ['payment_status' => 'paid'])) }}"
       class="btn {{ $current === 'paid' ? 'btn-success' : 'btn-default' }}">مدفوع بالكامل</a>
    <a href="{{ route('dashboard.payments.index', array_merge($filterBase, ['payment_status' => 'all'])) }}"
       class="btn {{ $current === 'all' ? 'btn-default active' : 'btn-default' }}" style="{{ $current === 'all' ? 'border:2px solid #3c8dbc;' : '' }}">الكل</a>
</div>
