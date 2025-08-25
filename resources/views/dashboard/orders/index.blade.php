@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">

        <h1>الطلبات
            <small>{{ $orders->total() }} طلب</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">الطلبات</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-md-8">

                <div class="box box-primary">

                    <div class="box-header">

                        <h3 class="box-title" style="margin-bottom: 10px">الطلبات</h3>

                        <form action="{{ route('dashboard.orders.index') }}" method="get">

                            <div class="row">

                                {{-- <div class="col-md-8">
                                    <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
                                </div>

                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                                </div>
                                <div class="col-md-4">
                                <a href="{{ route('dashboard.direct-sale') }}" class="btn btn-success">
                                    <i class="fa fa-cash-register"></i> بيع مباشر
                                </a>
                                </div> --}}
                                <di class="row g-2 align-items-center mb-3">
    <!-- حقل البحث -->
    <div class="col-md-6 col-sm-16">
        <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
    </div>

    <!-- زر البحث -->
    <div class="col-md-3 col-sm-3">
        <button type="submit" class="btn btn-primary w-100">
            <i class="fa fa-search"></i> بحث
        </button>
    </div>

    <!-- زر البيع المباشر -->
    <div class="col-md-3 col-sm-3">
        <a href="{{ route('dashboard.direct-sale') }}" class="btn btn-success w-100">
            <i class="fa fa-cash-register"></i> بيع مباشر
        </a>
    </div>



                            </div><!-- end of row -->

                        </form><!-- end of form -->

                    </div><!-- end of box header -->

                    @if ($orders->count() > 0)

                    <div class="box-body table-responsive">

                        <table class="table table-hover">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>اسم العميل</th>
                                <th>السعر</th>
                                {{-- <th>الحالة</th>--}}
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>

                            @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->client->name }}</td>
                                <td>{{ number_format($order->total_price, 2) }}</td>
                                {{--<td>--}}
                                {{--<button--}}
                                {{--data-status="{{ __('site.' . $order->status) }}"--}}
                                {{--data-url="{{ route('dashboard.orders.update_status', $order->id) }}"--}}
                                {{--data-method="put"--}}
                                {{--data-available-status='["{{ __('site.processing') }}", "{{ __('site.finished') }}"]'--}}
                                {{--class="order-status-btn btn {{ $order->status == 'processing' ? 'btn-warning' : 'btn-success disabled' }} btn-sm"--}}
                                {{-->--}}
                                {{--{{ __('site.' . $order->status) }}--}}
                                {{--</button>--}}
                                {{--</td>--}}
                                <td>{{ $order->created_at->toFormattedDateString() }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm order-products" data-url="{{ route('dashboard.orders.products', $order->id) }}" data-method="get">
                                        <i class="fa fa-list"></i>
                                        عرض
                                    </button>
                                    {{-- <a href="{{ route('dashboard.orders.pdf', $order->id) }}" class="btn btn-primary">
                                    <i class="fa fa-print"></i> طباعة PDF
                                    </a> --}}
                                    @if (auth()->user()->hasPermission('update_orders'))
                                    <a href="{{ route('dashboard.clients.orders.edit', ['client' => $order->client->id, 'order' => $order->id]) }}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i> تعديل</a>
                                    @else
                                    <a href="#" disabled class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> تعديل</a>
                                    @endif

                                    @if (auth()->user()->hasPermission('delete_orders'))
                                    <form action="{{ route('dashboard.orders.destroy', $order->id) }}" method="post" style="display: inline-block;">
                                        {{ csrf_field() }}
                                        {{ method_field('delete') }}
                                        <button type="submit" class="btn btn-danger btn-sm delete"><i class="fa fa-trash"></i> حذف</button>
                                    </form>

                                    @else
                                    <a href="#" class="btn btn-danger btn-sm" disabled><i class="fa fa-trash"></i> حذف</a>
                                    @endif

                                </td>

                            </tr>

                            @endforeach

                        </table><!-- end of table -->

                        {{ $orders->appends(request()->query())->links() }}

                    </div>

                    @else

                    <div class="box-body">
                        <h3>لا توجد سجلات</h3>
                    </div>

                    @endif

                </div><!-- end of box -->

            </div><!-- end of col -->

            <div class="col-md-4">

                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title" style="margin-bottom: 10px">عرض المنتجات</h3>
                    </div><!-- end of box header -->

                    <div class="box-body">

                        <div style="display: none; flex-direction: column; align-items: center;" id="loading">
                            <div class="loader"></div>
                            <p style="margin-top: 10px">جاري التحميل...</p>
                        </div>

                        <div id="order-product-list">

                        </div><!-- end of order product list -->

                    </div><!-- end of box body -->

                </div><!-- end of box -->

            </div><!-- end of col -->

        </div><!-- end of row -->

    </section><!-- end of content section -->

</div><!-- end of content wrapper -->

@endsection
