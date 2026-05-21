@extends('layouts.dashboard.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard_files/css/purchase-invoice.css') }}">
@endpush

@section('content')
<div class="content-wrapper purchase-invoice-page">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> فاتورة شراء جديدة</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
            <li><a href="{{ route('dashboard.purchase-invoices.index') }}">فواتير الشراء</a></li>
            <li class="active">إنشاء</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                @include('dashboard.purchase_invoices._form')
            </div>
        </div>

        <div class="box box-default collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title text-muted"><i class="fa fa-question-circle"></i> كيف يُحسب متوسط سعر الشراء؟</h3>
                <div class="box-tools pull-left">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="box-body text-muted" style="font-size:14px;line-height:1.7;">
                عند إدخال كمية جديدة، يُحدَّث <strong>سعر الشراء للحبة</strong> في المخزون كمتوسط مرجّح:
                (قيمة المخزون القديم + قيمة الشراء الجديد) ÷ إجمالي الحبات.
                مثال: 10 حبات × 100 + شراء 2 عبوة (24 حبة) بسعر 1200 للعبوة → يُخزَّن 24 حبة وتُحدَّث التكلفة للحبة.
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
