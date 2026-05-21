@php
    $filterBase = request()->except('payment_status', 'page');
    if (isset($filters) && is_array($filters)) {
        $filterBase = array_merge($filterBase, array_filter($filters, fn ($v, $k) => $k !== 'payment_status' && $v !== '' && $v !== null, ARRAY_FILTER_USE_BOTH));
    }
@endphp
<div class="payment-status-filter btn-group btn-group-lg" role="group" style="display:flex;flex-wrap:wrap;gap:6px;">
    <a href="{{ route(request()->route()->getName(), array_merge($filterBase, ['payment_status' => 'all'])) }}"
       class="btn {{ ($paymentStatus ?? 'all') === 'all' || empty($paymentStatus) ? 'btn-primary' : 'btn-default' }}">الكل</a>
    <a href="{{ route(request()->route()->getName(), array_merge($filterBase, ['payment_status' => 'paid'])) }}"
       class="btn {{ ($paymentStatus ?? '') === 'paid' ? 'btn-success' : 'btn-default' }}">مدفوع</a>
    <a href="{{ route(request()->route()->getName(), array_merge($filterBase, ['payment_status' => 'partial'])) }}"
       class="btn {{ ($paymentStatus ?? '') === 'partial' ? 'btn-warning' : 'btn-default' }}">دفع جزئي</a>
    <a href="{{ route(request()->route()->getName(), array_merge($filterBase, ['payment_status' => 'unpaid'])) }}"
       class="btn {{ ($paymentStatus ?? '') === 'unpaid' ? 'btn-danger' : 'btn-default' }}">متبقي</a>
    <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->except('payment_status'), ['payment_status' => 'returned'])) }}"
       class="btn {{ ($paymentStatus ?? '') === 'returned' ? 'btn-info' : 'btn-default' }}">مسترجع</a>
    <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->except('payment_status'), ['payment_status' => 'partial_return'])) }}"
       class="btn {{ ($paymentStatus ?? '') === 'partial_return' ? 'btn-primary' : 'btn-default' }}">مرتجع جزئي</a>
</div>
