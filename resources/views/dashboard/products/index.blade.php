@extends('layouts.dashboard.app')

@section('content')

@include('dashboard.products._products_styles')

<div class="content-wrapper">

    <section class="content-header">
        <h1>المنتجات <small>{{ $products->total() }}</small></h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">المنتجات</li>
        </ol>
    </section>

    <section class="content">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
        @endif

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cubes"></i> قائمة المنتجات</h3>
                <div class="box-tools pull-left">
                    @if (auth()->user()->hasPermission('create_products'))
                    <a href="{{ route('dashboard.products.create') }}" class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> إضافة منتج
                    </a>
                    @endif
                </div>
            </div>

            <div class="box-body">
                <form action="{{ route('dashboard.products.index') }}" method="get" class="products-toolbar mobile-filter-panel">
                    <div class="row">
                        <div class="col-md-4 col-sm-12" style="margin-bottom:8px;">
                            <label class="visible-xs">اسم المنتج</label>
                            <input type="text" name="search" class="form-control"
                                   placeholder="بحث باسم المنتج" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 col-sm-6" style="margin-bottom:8px;">
                            <label class="visible-xs">القسم</label>
                            <select name="category_id" class="form-control">
                                <option value="">كل الأقسام</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6" style="margin-bottom:8px;">
                            <label class="visible-xs">طريقة البيع</label>
                            <select name="sale_mode" class="form-control">
                                <option value="">كل طرق البيع</option>
                                <option value="piece_only" {{ request('sale_mode') === 'piece_only' ? 'selected' : '' }}>بالحبة فقط</option>
                                <option value="bulk_only" {{ request('sale_mode') === 'bulk_only' ? 'selected' : '' }}>بالعبوة فقط</option>
                                <option value="flexible" {{ request('sale_mode') === 'flexible' ? 'selected' : '' }}>بيع مرن</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-12" style="margin-bottom:8px;">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> بحث</button>
                        </div>
                    </div>
                </form>

                @if ($products->count() > 0)
                @php $productService = app(\App\Services\ProductService::class); @endphp

                {{-- بطاقات الهاتف --}}
                <div class="products-phone-list visible-xs">
                    @foreach ($products as $product)
                    <article class="phone-product-card">
                        <div class="phone-product-head">
                            <img src="{{ $product->image_path }}" class="phone-product-img" alt="{{ $product->name }}"
                                 onerror="this.onerror=null;this.src='{{ \App\Models\Product::defaultImageUrl() }}';">
                            <div class="phone-product-title">
                                <strong>{{ $product->name }}</strong>
                                <span>{{ $product->category->name ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="phone-product-meta">
                            <div class="phone-meta-row">
                                <span>سعر الشراء</span>
                                <strong class="money">{{ $productService->priceDisplay($product, 'purchase') }}</strong>
                            </div>
                            <div class="phone-meta-row">
                                <span>سعر البيع</span>
                                <strong class="money text-success">{{ $productService->priceDisplay($product, 'sale') }}</strong>
                            </div>
                            <div class="phone-meta-row">
                                <span>الربح</span>
                                <strong>{{ $product->profit_percent }}%</strong>
                            </div>
                            <div class="phone-meta-row">
                                <span>المخزون</span>
                                <span class="label {{ $productService->stockBadgeClass((float) $product->stock) }}">
                                    {{ $productService->stockDisplay($product) }}
                                </span>
                            </div>
                            <div class="phone-meta-row">
                                <span>البيع</span>
                                <span>
                                    <span class="label {{ $productService->measureUnitBadgeClass($product->measure_unit ?? 'piece') }}">
                                        {{ \App\Support\SaleUnits::measureUnitLabel($product->measure_unit ?? 'piece') }}
                                    </span>
                                    <span class="label {{ $productService->saleModeBadgeClass($product->sale_mode ?? 'flexible') }}">
                                        {{ \App\Support\SaleUnits::saleModeLabel($product->sale_mode ?? 'flexible') }}
                                    </span>
                                </span>
                            </div>
                            <div class="phone-meta-row">
                                <span>العبوة</span>
                                <strong>{{ $productService->cartonSummary($product) }}</strong>
                            </div>
                        </div>

                        <div class="phone-action-bar">
                            <a href="{{ route('dashboard.products.show', $product->id) }}" class="btn btn-default">
                                <i class="fa fa-eye"></i> عرض
                            </a>
                            @if (auth()->user()->hasPermission('update_products'))
                            <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-warning">
                                <i class="fa fa-pencil"></i> تعديل
                            </a>
                            @endif
                            @if (auth()->user()->hasPermission('delete_products'))
                            <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="post" class="delete-form">
                                @csrf
                                @method('delete')
                                <button type="button" class="btn btn-danger btn-block delete-product-btn">
                                    <i class="fa fa-trash"></i> حذف
                                </button>
                            </form>
                            @endif
                        </div>
                    </article>
                    @endforeach
                </div>

                {{-- جدول سطح المكتب --}}
                <div class="table-responsive hidden-xs">
                    <table class="table table-bordered table-hover products-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المنتج</th>
                                <th>القسم</th>
                                <th>سعر الشراء</th>
                                <th>سعر البيع</th>
                                <th>الربح %</th>
                                <th>المخزون</th>
                                <th>الوحدة / البيع</th>
                                <th>العبوة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $index => $product)
                            <tr>
                                <td>{{ $products->firstItem() + $index }}</td>
                                <td class="col-name">
                                    <img src="{{ $product->image_path }}" class="product-thumb" alt="{{ $product->name }}"
                                         onerror="this.onerror=null;this.src='{{ \App\Models\Product::defaultImageUrl() }}';">
                                    {{ $product->name }}
                                </td>
                                <td>{{ $product->category->name ?? '—' }}</td>
                                <td><span class="money">{{ $productService->priceDisplay($product, 'purchase') }}</span></td>
                                <td><span class="money text-success">{{ $productService->priceDisplay($product, 'sale') }}</span></td>
                                <td>{{ $product->profit_percent }}%</td>
                                <td>
                                    <span class="label {{ $productService->stockBadgeClass((float) $product->stock) }}">
                                        {{ $productService->stockDisplay($product) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="label {{ $productService->measureUnitBadgeClass($product->measure_unit ?? 'piece') }}">
                                        {{ \App\Support\SaleUnits::measureUnitLabel($product->measure_unit ?? 'piece') }}
                                    </span>
                                    <div style="margin-top:4px;">
                                        <span class="label {{ $productService->saleModeBadgeClass($product->sale_mode ?? 'flexible') }}">
                                            {{ \App\Support\SaleUnits::saleModeLabel($product->sale_mode ?? 'flexible') }}
                                        </span>
                                    </div>
                                </td>
                                <td><small>{{ $productService->cartonSummary($product) }}</small></td>
                                <td>
                                    <div class="products-actions">
                                        <a href="{{ route('dashboard.products.show', $product->id) }}" class="btn btn-default btn-sm" title="عرض">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if (auth()->user()->hasPermission('update_products'))
                                        <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-warning btn-sm" title="تعديل">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_products'))
                                        <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="post" class="delete-form" style="display:inline;">
                                            @csrf
                                            @method('delete')
                                            <button type="button" class="btn btn-danger btn-sm delete-product-btn" title="حذف">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="products-pagination text-center">
                    {{ $products->links() }}
                </div>
                @else
                <div class="alert alert-info text-center" style="margin:0;">
                    <i class="fa fa-info-circle"></i> لا توجد منتجات مطابقة.
                    @if (auth()->user()->hasPermission('create_products'))
                    <a href="{{ route('dashboard.products.create') }}">أضف منتجاً جديداً</a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('body').on('click', '.delete-product-btn', function(e) {
        e.preventDefault();
        var form = $(this).closest('.delete-form');
        Swal.fire({
            title: 'حذف المنتج؟',
            text: 'لن تتمكن من التراجع إذا كان المنتج غير مرتبط بطلبات.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'إلغاء',
            confirmButtonText: 'نعم، احذف'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush

@endsection
