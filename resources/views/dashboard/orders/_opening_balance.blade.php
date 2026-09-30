@if($order->is_opening_balance)
<div class="opening-balance-card">
    <strong><i class="fa fa-history"></i> حساب قديم خارج النظام</strong>
    <div class="opening-balance-meta">
        @if($order->opening_date)
            <span>تاريخ الفاتورة: {{ $order->opening_date->format('d/m/Y') }}</span>
        @endif
        @if($order->opening_reference)
            <span>رقمها: {{ $order->opening_reference }}</span>
        @endif
    </div>
    @if($order->opening_details)
        <p style="margin:8px 0 0;white-space:pre-wrap;">{{ $order->opening_details }}</p>
    @endif
    @if($order->opening_photo)
        <a href="{{ asset($order->opening_photo) }}" target="_blank">
            <img src="{{ asset($order->opening_photo) }}" alt="فاتورة قديمة" class="opening-balance-photo">
        </a>
    @endif
</div>
<style>
.opening-balance-card {
    border: 1px solid #fcd34d;
    background: #fffbeb;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 12px;
}
.opening-balance-meta { display: flex; flex-wrap: wrap; gap: 8px 14px; font-size: 12px; color: #92400e; margin-top: 6px; }
.opening-balance-photo { display: block; max-width: 100%; max-height: 280px; margin-top: 8px; border-radius: 8px; }
</style>
@endif
