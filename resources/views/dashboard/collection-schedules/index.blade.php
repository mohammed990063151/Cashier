@extends('layouts.dashboard.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard_files/css/client-groups.css') }}">
<style>
.collection-page .stat-card {
    border-radius: 10px;
    padding: 16px;
    color: #fff;
    margin-bottom: 14px;
    text-align: center;
}
.collection-page .stat-card h4 { margin: 0; font-size: 30px; font-weight: 800; }
.collection-page .stat-card p { margin: 6px 0 0; font-size: 14px; }
</style>
@endpush

@section('content')
<div class="content-wrapper collection-page">
    <section class="content-header">
        <h1 style="font-size:26px;"><i class="fa fa-calendar-check-o"></i> جدولة تحصيل الذمم</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}">لوحة التحكم</a></li>
            <li class="active">جدولة السداد</li>
        </ol>
    </section>

    <section class="content">
        @if(session('success'))<div class="alert alert-success alert-dismissible" style="font-size:16px;"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible" style="font-size:16px;"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>@endif

        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card" style="background:#e74c3c;"><h4>{{ $summary['overdue'] }}</h4><p>متأخر</p></div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card" style="background:#f39c12;"><h4>{{ $summary['due_today'] }}</h4><p>مستحق اليوم</p></div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card" style="background:#3498db;"><h4>{{ $summary['due_soon'] }}</h4><p>قريب ({{ $dueSoonDays }} يوم)</p></div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card" style="background:#7f8c8d;"><h4>{{ $summary['no_date'] }}</h4><p>بدون تقسيط</p></div>
            </div>
        </div>

        <div class="box box-solid" style="border-radius:12px;">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size:18px;">
                    إجمالي الذمم المفتوحة:
                    <span class="text-danger" style="font-weight:800;">{{ number_format($summary['total_remaining'], 2) }} ج.س</span>
                </h3>
                <div class="box-tools">
                    <a href="{{ route('dashboard.payments.index') }}" class="btn btn-success"><i class="fa fa-money"></i> صفحة المدفوعات</a>
                </div>
            </div>
            <div class="box-body" style="padding:20px;">
                @php
                    $st = $filters['schedule_status'] ?? 'all';
                    $sort = $filters['sort'] ?? 'due_asc';
                    $scheduleExtra = view('dashboard.collection-schedules._filter_extra', compact('st', 'sort', 'filters'))->render();
                @endphp

                @include('dashboard.partials._client_order_filter', [
                    'filterAction' => route('dashboard.collection-schedules.index'),
                    'filters' => $filters,
                    'clients' => $clients,
                    'clientOrders' => $clientOrders,
                    'showPaymentStatus' => false,
                    'extraFields' => $scheduleExtra,
                ])

                @include('dashboard.partials._client_groups', [
                    'clientGroups' => $clientGroups,
                    'filters' => $filters,
                    'rowPartial' => 'dashboard.partials._order_schedule_row',
                ])
            </div>
        </div>
    </section>
</div>

@include('dashboard.collection-schedules._installment_modal')
@endsection

@push('scripts')
<script>
window.collectionInstallmentUrl = @json(url('/dashboard/collection-schedules/__ORDER__/installments'));
window.paymentsClientOrdersUrl = @json(route('dashboard.payments.client-orders', ['client' => '__CLIENT__']));
</script>
<script src="{{ asset('dashboard_files/js/custom/collection-schedule.js') }}"></script>
<script src="{{ asset('dashboard_files/js/custom/payments-page.js') }}"></script>
<script>
$(function() {
    $('.client-orders-collapse').on('show.bs.collapse hide.bs.collapse', function() {
        var $btn = $('[data-target="#' + this.id + '"]');
        var count = $(this).find('.order-detail-panel').length;
        $btn.find('.btn-text-show').text($(this).hasClass('in') ? 'إخفاء الطلبات' : 'عرض الطلبات (' + count + ')');
    });
});
</script>
@endpush
