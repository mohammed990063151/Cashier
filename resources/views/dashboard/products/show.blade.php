@extends('layouts.dashboard.app')

@section('content')

@include('dashboard.products._products_styles')

@php
    $productService = app(\App\Services\ProductService::class);
    $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
@endphp

<div class="content-wrapper">
    <section class="content-header">
        <h1>عرض منتج</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li><a href="{{ route('dashboard.products.index') }}">المنتجات</a></li>
            <li class="active">{{ $product->name }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary text-center">
                    <div class="box-body">
                        <img src="{{ $product->image_path }}" class="img-thumbnail" style="max-width:100%;max-height:220px;" alt="{{ $product->name }}"
                             onerror="this.onerror=null;this.src='{{ \App\Models\Product::defaultImageUrl() }}';">
                        <h3 style="margin-top:12px;">{{ $product->name }}</h3>
                        <p class="text-muted">{{ $product->category->name ?? '—' }}</p>
                        <span class="label {{ $productService->saleModeBadgeClass($product->sale_mode ?? 'flexible') }}">
                            {{ \App\Support\SaleUnits::saleModeLabel($product->sale_mode ?? 'flexible') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="box box-primary product-show-card">
                    <div class="box-header with-border">
                        <h3 class="box-title">تفاصيل المنتج</h3>
                        <div class="box-tools pull-left">
                            @if (auth()->user()->hasPermission('update_products'))
                            <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                                <i class="fa fa-pencil"></i> تعديل
                            </a>
                            @endif
                            <a href="{{ route('dashboard.products.index') }}" class="btn btn-default btn-sm">رجوع</a>
                        </div>
                    </div>
                    <div class="box-body">
                        @if($product->description)
                        <p><strong>الوصف:</strong> {!! nl2br(e(strip_tags($product->description))) !!}</p>
                        <hr>
                        @endif

                        <div class="info-row"><span>وحدة القياس</span>
                            <span class="label {{ $productService->measureUnitBadgeClass($product->measure_unit ?? 'piece') }}">
                                {{ \App\Support\SaleUnits::measureUnitLabel($product->measure_unit ?? 'piece') }}
                            </span>
                        </div>
                        <div class="info-row"><span>سعر الشراء</span><strong>{{ $productService->priceDisplay($product, 'purchase') }} ج.س</strong></div>
                        <div class="info-row"><span>سعر البيع</span><strong class="text-success">{{ $productService->priceDisplay($product, 'sale') }} ج.س</strong></div>
                        @php
                            $measure = \App\Support\SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
                            $entry = \App\Support\SaleUnits::toEntryValues($product);
                        @endphp
                        @if($measure === \App\Support\SaleUnits::UNIT_CARTON)
                        <div class="info-row"><span>سعر الحبة (محسوب)</span><strong>{{ \App\Support\DecimalMath::display($product->sale_price) }} ج.س</strong></div>
                        @endif
                        <div class="info-row"><span>نسبة الربح</span><strong>{{ $product->profit_percent }}%</strong></div>
                        <div class="info-row">
                            <span>المخزون</span>
                            <span class="label {{ $productService->stockBadgeClass((float) $product->stock) }}">{{ $productService->stockDisplay($product) }}</span>
                        </div>
                        <div class="info-row"><span>حبات العبوة</span><strong>{{ $productService->cartonSummary($product) }}</strong></div>
                        <div class="info-row">
                            <span>طريقة البيع في الطلبات</span>
                            <strong>{{ \App\Support\SaleUnits::saleModeShortHint($product->sale_mode ?? 'flexible') }}</strong>
                        </div>
                        <div class="info-row"><span>عدد الطلبات</span><strong>{{ $product->orders()->count() }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection
