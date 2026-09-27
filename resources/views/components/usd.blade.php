@props(['amount' => 0, 'rate' => null])

@php
    $hints = app(\App\Services\CurrencyService::class)->hints((float) $amount, $rate !== null && $rate !== '' ? (float) $rate : null);
@endphp

@foreach($hints as $hint)
    <span class="{{ $hint['class'] }}" title="{{ $hint['title'] }}">{{ $hint['text'] }}</span>
@endforeach
