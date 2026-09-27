@props(['amount' => 0, 'rate' => null, 'entries' => null])

@php
    $sdg = (float) $amount;
    $currency = app(\App\Services\CurrencyService::class);
    $book = is_array($entries) ? $currency->debtChange($entries) : null;
@endphp
<span class="report-money">
    <strong>{{ number_format($sdg, 2) }} ج.س</strong>
    @if($book)
        <span class="fx-compare">
            <span class="money-usd money-usd-then">يومها {{ $currency->formatUsd($book['then_usd']) }} · 1 $ = {{ $book['receipt_label'] }}</span>
            <span class="money-usd">الآن {{ $currency->formatUsd($book['now_usd']) }} · 1 $ = {{ number_format($book['today_rate'], 0) }}</span>
        </span>
    @else
        <x-usd :amount="$sdg" :rate="$rate" />
    @endif
</span>
