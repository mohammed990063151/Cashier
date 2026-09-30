@php
    $debt = app(\App\Services\BadDebtService::class)->summary();
@endphp
<div class="row debt-status-strip">
    <div class="col-sm-4">
        <div class="alert alert-warning text-center" style="margin-bottom:12px;">
            <strong>ذمم ما زالت مستحقة</strong>
            <div><x-report-money :amount="$debt['active_amount']" :entries="$debt['active_entries']" /></div>
            <small>{{ $debt['active_count'] }} طلب ما زال للتحصيل</small>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="alert alert-danger text-center" style="margin-bottom:12px;">
            <strong>ديون معدومة (هوالك)</strong>
            <div><x-report-money :amount="$debt['bad_amount']" :entries="$debt['bad_entries']" /></div>
            <small>{{ $debt['bad_count'] }} طلب · {{ $debt['bad_clients'] }} عميل</small>
            <div><a href="{{ route('dashboard.bad-debts.index') }}">فتح صفحة الهوالك</a></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="alert alert-info text-center" style="margin-bottom:12px;">
            <strong>ربح المبيعات بعد خصم الهالك</strong>
            <div><x-report-money :amount="$debt['profit_after_bad_debt']" /></div>
            <small>المبيعات بقيت مسجّلة. الهالك خرج من التحصيل وظهر هنا كخسارة.</small>
        </div>
    </div>
</div>
