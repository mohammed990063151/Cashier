@props(['entries' => [], 'rateLabel' => 'استلام', 'invert' => false])

@php
    $fx = app(\App\Services\CurrencyService::class)->debtChange($entries);
    $kind = $fx['kind'] ?? 'flat';
    if ($fx && $invert && $kind === 'loss') {
        $kind = 'gain';
    } elseif ($fx && $invert && $kind === 'gain') {
        $kind = 'loss';
    }
@endphp

@if($fx)
<div class="debt-fx">
    <div class="debt-fx-rates">{{ $rateLabel }} {{ $fx['receipt_label'] }} · اليوم {{ number_format($fx['today_rate'], 0) }}</div>
    <div class="debt-fx-usd">كان {{ app(\App\Services\CurrencyService::class)->formatUsd($fx['then_usd']) }} · الآن {{ app(\App\Services\CurrencyService::class)->formatUsd($fx['now_usd']) }}</div>
    @if($kind === 'loss')
        <div class="debt-fx-result is-loss">خسارة {{ app(\App\Services\CurrencyService::class)->formatUsd(abs($fx['loss_usd'])) }}</div>
    @elseif($kind === 'gain')
        <div class="debt-fx-result is-gain">ربح {{ app(\App\Services\CurrencyService::class)->formatUsd(abs($fx['loss_usd'])) }}</div>
    @else
        <div class="debt-fx-result">لا فرق</div>
    @endif
</div>
@endif
