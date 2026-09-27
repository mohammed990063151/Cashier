@php
    $installmentReminders = collect($collectionAlerts ?? []);
@endphp
@if($installmentReminders->isNotEmpty())
@php $schedule = app(\App\Services\CollectionScheduleService::class); @endphp
<div class="collection-due-banner">
    <div class="container-fluid">
        <i class="fa fa-bell"></i>
        <strong>أقساط خلال {{ $schedule->dueSoonDays() }} أيام:</strong>
        @foreach($installmentReminders->take(4) as $item)
            @php
                $dueDays = $schedule->daysUntilDue($item['installment']->due_at);
            @endphp
            <span class="collection-due-item">
                {{ $item['order']->client->name }} — {{ $item['order']->order_number }}:
                <strong>{{ number_format($item['installment']->amount, 2) }} ج.س</strong>
                ({{ $schedule->dueWhenLabel($dueDays) }})
            </span>@if(!$loop->last)<span class="text-muted"> | </span>@endif
        @endforeach
        <a href="{{ route('dashboard.collection-schedules.index', ['schedule_status' => 'alert']) }}" class="btn btn-sm btn-warning" style="margin-top:2px;float:left;font-weight:700;">
            <i class="fa fa-money"></i> تحصيل الآن
        </a>
    </div>
</div>
<style>
.collection-due-banner {
    background: linear-gradient(90deg, #f39c12 0%, #e67e22 100%);
    color: #fff;
    padding: 10px 20px;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
    position: relative;
    z-index: 1030;
}
.collection-due-banner .fa-bell { margin-left: 8px; animation: bell-pulse 1.5s infinite; }
@keyframes bell-pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
.collection-due-item { margin: 0 6px; }
</style>
@endif
