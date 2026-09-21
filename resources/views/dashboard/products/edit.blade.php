@extends('layouts.dashboard.app')

@section('content')

@include('dashboard.products._products_styles')

<div class="content-wrapper">
    <section class="content-header">
        <h1>تعديل منتج</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li><a href="{{ route('dashboard.products.index') }}">المنتجات</a></li>
            <li class="active">{{ $product->name }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pencil"></i> {{ $product->name }}</h3>
                <div class="box-tools pull-left">
                    <a href="{{ route('dashboard.products.show', $product->id) }}" class="btn btn-default btn-sm">
                        <i class="fa fa-eye"></i> عرض
                    </a>
                </div>
            </div>
            <div class="box-body">
                @include('partials._errors')
                <form action="{{ route('dashboard.products.update', $product->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    @include('dashboard.products._form', ['categories' => $categories, 'product' => $product])
                </form>
            </div>
        </div>
    </section>
</div>

@include('dashboard.products._sale_mode_script')
@include('dashboard.products._image_capture_script')
@endsection
