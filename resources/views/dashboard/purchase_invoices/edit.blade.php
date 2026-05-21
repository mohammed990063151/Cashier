@extends('layouts.dashboard.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard_files/css/purchase-invoice.css') }}">
@endpush

@section('content')
<div class="content-wrapper purchase-invoice-page">
    <section class="content-header">
        <h1><i class="fa fa-edit"></i> تعديل فاتورة {{ $purchaseInvoice->invoice_number }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
            <li><a href="{{ route('dashboard.purchase-invoices.index') }}">فواتير الشراء</a></li>
            <li class="active">تعديل</li>
        </ol>
    </section>

    <section class="content">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="box box-warning">
            <div class="box-body">
                @include('dashboard.purchase_invoices._form')
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
window.purchaseProductsCatalog = @json($productsCatalog);
window.quickProductStoreUrl = @json(route('dashboard.products.quick-store'));
window.csrfToken = @json(csrf_token());
</script>
<script src="{{ asset('dashboard_files/js/custom/purchase-invoice.js') }}?v=2"></script>
@endpush
