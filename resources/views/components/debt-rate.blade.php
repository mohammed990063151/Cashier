@props(['rate' => null, 'rates' => [], 'remaining' => 1])

@php
    $left = (float) ($remaining ?? 0);
    $list = collect($rates ?? []);
    if ($rate !== null && $rate !== '') {
        $list->push($rate);
    }
    $list = $list->map(fn ($r) => (float) $r)->filter(fn ($r) => $r > 0)->unique()->sort()->values();
    $today = app(\App\Services\CurrencyService::class)->rate();
    $then = $list->isEmpty()
        ? '—'
        : ($list->count() === 1
            ? number_format($list[0], 0)
            : number_format($list->first(), 0).'–'.number_format($list->last(), 0));
@endphp

@if($left > 0.009 && ($list->isNotEmpty() || $today > 0))
    <span class="debt-rate" title="سعر الدولار يوم أخذ البضاعة، وسعر اليوم">أخذها {{ $then }} · اليوم {{ $today > 0 ? number_format($today, 0) : '—' }}</span>
@endif
