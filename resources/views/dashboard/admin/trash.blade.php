@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>سلة المحذوفات</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">سلة المحذوفات</li>
        </ol>
    </section>

    <section class="content">

        {{-- Categories --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">التصنيفات المحذوفة <small>{{ $categories->count() }}</small></h3>
            </div>

            <div class="box-body">
                @if($categories->count() > 0)
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>عدد المنتجات</th>
                                <th>تاريخ الحذف</th>
                                <th>الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $index => $category)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->products->count() }}</td>
                                    <td>{{ $category->deleted_at }}</td>
                                    <td>
                                        <a href="{{ route('dashboard.admin.restore', ['type'=>'categories', 'id'=>$category->id]) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-undo"></i> استرجاع
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <h4 class="text-center">لا توجد تصنيفات محذوفة</h4>
                @endif
            </div>
        </div>

        {{-- Clients --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">العملاء المحذوفين <small>{{ $clients->count() }}</small></h3>
            </div>

            <div class="box-body">
                @if($clients->count() > 0)
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>تاريخ الحذف</th>
                                <th>الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $index => $client)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $client->name }}</td>
                                    <td>{{ $client->email ?? '-' }}</td>
                                    <td>{{ $client->deleted_at }}</td>
                                    <td>
                                        <a href="{{ route('dashboard.admin.restore', ['type'=>'clients', 'id'=>$client->id]) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-undo"></i> استرجاع
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <h4 class="text-center">لا يوجد عملاء محذوفين</h4>
                @endif
            </div>
        </div>

        {{-- Expenses --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">المصروفات المحذوفة <small>{{ $expenses->count() }}</small></h3>
            </div>

            <div class="box-body">
                @if($expenses->count() > 0)
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>العنوان</th>
                                <th>المبلغ</th>
                                <th>تاريخ الحذف</th>
                                <th>الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $index => $expense)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $expense->title }}</td>
                                    <td>{{ $expense->amount }}</td>
                                    <td>{{ $expense->deleted_at }}</td>
                                    <td>
                                        <a href="{{ route('dashboard.admin.restore', ['type'=>'expenses', 'id'=>$expense->id]) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-undo"></i> استرجاع
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <h4 class="text-center">لا توجد مصروفات محذوفة</h4>
                @endif
            </div>
        </div>


        {{-- Products --}}
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">المنتجات المحذوفة <small>{{ $products->count() }}</small></h3>
    </div>

    <div class="box-body">
        @if($products->count() > 0)
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>القسم</th>
                        <th>سعر الشراء</th>
                        <th>سعر البيع</th>
                        <th>المخزون</th>
                        <th>تاريخ الحذف</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>{{ $product->purchase_price }}</td>
                            <td>{{ $product->sale_price }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>{{ $product->deleted_at }}</td>
                            <td>
                                <a href="{{ route('dashboard.admin.restore', ['type'=>'products', 'id'=>$product->id]) }}" class="btn btn-success btn-sm">
                                    <i class="fa fa-undo"></i> استرجاع
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <h4 class="text-center">لا توجد منتجات محذوفة</h4>
        @endif
    </div>
</div>

    </section>
</div>

@endsection
