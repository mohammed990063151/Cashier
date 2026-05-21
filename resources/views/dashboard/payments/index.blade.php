@extends('layouts.dashboard.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard_files/css/client-groups.css') }}">
@endpush

@section('content')
<div class="content-wrapper payments-page">
    <section class="content-header">
        <h1 style="font-size:26px;"><i class="fa fa-money"></i> المدفوعات والتحصيل</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}">لوحة التحكم</a></li>
            <li class="active">المدفوعات</li>
        </ol>
    </section>

    <section class="content">
        @if(session('success'))<div class="alert alert-success alert-dismissible" style="font-size:16px;"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible" style="font-size:16px;"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>@endif

        <div class="box box-solid" style="border-radius:12px;border:none;box-shadow:0 2px 12px rgba(0,0,0,.06);">
            <div class="box-body" style="padding:20px;">
                @include('dashboard.partials._client_order_filter', [
                    'filterAction' => route('dashboard.payments.index'),
                    'filters' => $filters,
                    'clients' => $clients,
                    'clientOrders' => $clientOrders,
                    'showPaymentStatus' => true,
                    'paymentStatus' => $paymentStatus,
                    'paymentFilterPartial' => 'dashboard.payments._payment_status_filter',
                ])

                @include('dashboard.partials._client_groups', [
                    'clientGroups' => $clientGroups,
                    'filters' => $filters,
                    'rowPartial' => 'dashboard.partials._order_payment_row',
                ])
            </div>
        </div>
    </section>
</div>

@include('dashboard.payments._modals')
@endsection

@push('scripts')
<script>
window.paymentsClientOrdersUrl = @json(route('dashboard.payments.client-orders', ['client' => '__CLIENT__']));
window.paymentLogUrl = @json(route('dashboard.orders.payment-log', ['order' => '__ORDER__']));
</script>
<script src="{{ asset('dashboard_files/js/custom/payments-page.js') }}?v=3"></script>
<script>
$(function() {
    $('.client-orders-collapse').on('show.bs.collapse hide.bs.collapse', function() {
        var $btn = $('[data-target="#' + this.id + '"]');
        var count = $(this).find('.order-detail-panel').length;
        if ($(this).hasClass('in')) {
            $btn.find('.btn-text-show').text('إخفاء الطلبات');
        } else {
            $btn.find('.btn-text-show').text('عرض الطلبات (' + count + ')');
        }
    });
});
</script>
@endpush
