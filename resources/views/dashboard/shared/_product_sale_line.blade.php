@php
    $line = app(\App\Services\OrderFinancialService::class)->formatProductSaleLine($product);
@endphp
<strong>{{ $product->name }}</strong>
<span class="text-muted"> — {{ $line['quantity'] }}</span>
<span class="text-success"> ({{ $line['price'] }})</span>
<span class="text-primary"> = {{ number_format($line['line_total'], 2) }} ج.س</span>
