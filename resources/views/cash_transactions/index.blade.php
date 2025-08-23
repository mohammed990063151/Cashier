@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>حركة الصندوق
            <small>{{ $transactions->total() }} حركة</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">حركة الصندوق</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">

            {{-- القسم الرئيسي: قائمة الحركات --}}
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">حركات الصندوق</h3>

                        <form action="{{ route('dashboard.cash.transactions') }}" method="get" class="mt-2">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="box-body table-responsive">
                        @if($transactions->count() > 0)
                            <table class="table table-hover">
                                <tr>
                                    <th>#</th>
                                    <th>النوع</th>
                                    <th>المبلغ</th>
                                    <th>المصدر</th>
                                    <th>التاريخ</th>
                                </tr>
                                @foreach($transactions as $t)
                                    <tr>
                                        <td>{{ $t->id }}</td>
                                        <td>
                                            @if($t->type == 'in')
                                                <span class="badge bg-success">دخل</span>
                                            @else
                                                <span class="badge bg-danger">خرج</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="{{ $t->type == 'in' ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($t->amount,2) }} ر.س
                                            </strong>
                                        </td>
                                        <td>{{ $t->source }}</td>
                                        <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </table>

                            {{ $transactions->appends(request()->query())->links() }}

                        @else
                            <h4 class="text-center text-muted mt-3">لا توجد حركات حالياً</h4>
                        @endif
                    </div>
                </div>
            </div>

            {{-- القسم الجانبي: الرصيد الحالي + تقرير الأرباح والخسائر --}}
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">الرصيد الحالي</h3>
                    </div>
                    <div class="box-body text-center fs-5 fw-bold">
                        💰 {{ number_format($balance,2) }} ر.س
                    </div>

                    <div class="box-footer text-center mt-3">
                        <a href="{{ route('dashboard.reports.profitLoss') }}" class="btn btn-outline-primary">📊 تقرير الأرباح والخسائر</a>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

@endsection
