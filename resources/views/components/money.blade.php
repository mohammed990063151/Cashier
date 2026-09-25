@props([
    'amount' => 0,
    'decimals' => 0,
    'block' => false,
])

@php
    $currency = app(\App\Services\CurrencyService::class);
    $amount = (float) $amount;
    $sdg = number_format($amount, $decimals);
    $showUsd = $currency->enabled();
    $usd = $showUsd ? $currency->toUsd($amount) : 0;
@endphp

<span {{ $attributes->class(['money-dual', $block ? 'money-dual-block' : '']) }}>
    <span class="money-sdg">{{ $sdg }} <small>ج.س</small></span>
    @if($showUsd)
        <span class="money-usd" title="{{ $currency->rateLabel() }}">≈ {{ number_format($usd, 2) }} $</span>
    @endif
</span>
