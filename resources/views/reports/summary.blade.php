@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>تقرير المبيعات المجمل</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">تقرير المبيعات المجمل</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body text-center">
                        <h3>💰 إجمالي المبيعات: {{ number_format($totalSales,2) }} ج.س</h3>
                        <p>عدد الطلبات: {{ $totalOrders }}</p>
                    </div>
                </div>
            </div>
        </div>

    </section>

</div>
@endsection
