@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>تقرير العملاء
            <small>{{ $clients->count() }} عميل</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">تقرير العملاء</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">

            {{-- العمود الرئيسي: جدول العملاء --}}
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title" style="margin-bottom: 10px">العملاء</h3>

                        {{-- شريط البحث --}}
                        <form action="{{ route('dashboard.reports.clients') }}" method="get">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control" placeholder="ابحث باسم العميل" value="{{ request()->search }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if ($clients->count() > 0)
                        <div class="box-body table-responsive">
                            <table class="table table-hover text-center">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم العميل</th>
                                        <th>عدد الطلبات</th>
                                        <th>تاريخ الإنشاء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clients as $client)
                                        <tr>
                                            <td>{{ $client->id }}</td>
                                            <td>{{ $client->name }}</td>
                                            <td>{{ $client->orders_count }}</td>
                                            <td>{{ $client->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="box-body">
                            <h3 class="text-center text-muted">لا توجد بيانات لعرضها</h3>
                        </div>
                    @endif
                </div>
            </div><!-- end col-md-8 -->

            {{-- العمود الجانبي: ملخص التقرير --}}
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">ملخص التقرير</h3>
                    </div>
                    <div class="box-body text-center">
                        <p><strong>إجمالي العملاء:</strong> {{ $clients->count() }}</p>
                        <p><strong>إجمالي الطلبات:</strong> {{ $clients->sum('orders_count') }}</p>
                        {{-- يمكنك إضافة أي إحصائيات أخرى هنا --}}
                    </div>
                </div>
            </div><!-- end col-md-4 -->

        </div><!-- end row -->
    </section>

</div>
@endsection
