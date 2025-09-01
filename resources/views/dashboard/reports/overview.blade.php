@extends('layouts.dashboard.app')

@section('title','الرئيسية')

@section('content')
@push('styles')


@endpush

<div class="content-wrapper">
    {{-- العنوان والمسار --}}
    <section class="content-header">
        <h1>الرئيسية</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li class="active">نظرة عامة</li>
        </ol>
    </section>

    {{-- المحتوى الرئيسي --}}
    <section class="content">
        {{-- الصف الأول: إحصائيات عامة --}}
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $categories_count }}</h3>
                        <p>التصنيفات</p>
                    </div>
                    <div class="icon"><i class="ion ion-bag"></i></div>
                    <a href="{{ route('dashboard.categories.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $products_count }}</h3>
                        <p>المنتجات</p>
                    </div>
                    <div class="icon"><i class="ion ion-stats-bars"></i></div>
                    <a href="{{ route('dashboard.products.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $clients_count }}</h3>
                        <p>العملاء</p>
                    </div>
                    <div class="icon"><i class="fa fa-user"></i></div>
                    <a href="{{ route('dashboard.clients.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $users_count }}</h3>
                        <p>المستخدمون</p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                    <a href="{{ route('dashboard.users.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- الصف الثاني: نظرة عامة مالية --}}
        <div class="row mt-3">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ number_format($salesOverview['total_sales'] ?? 0, 2) }} ر.س</h3>
                        <p>إجمالي المبيعات</p>
                    </div>
                    <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                    <a href="#" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ number_format($profitsOverview['total_profit'] ?? 0, 2) }} ر.س</h3>
                        <p>إجمالي الأرباح</p>
                    </div>
                    <div class="icon"><i class="fa fa-dollar-sign"></i></div>
                    <a href="#" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $clientsOverview['total_due'] ?? 0 }} ر.س</h3>
                        <p>المبالغ المتبقية للعملاء</p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                    <a href="#" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $suppliersOverview['total_due'] ?? 0 }} ر.س</h3>
                        <p>المبالغ المتبقية للموردين</p>
                    </div>
                    <div class="icon"><i class="fa fa-truck"></i></div>
                    <a href="#" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- الصف الثالث: مشتريات، مصروفات، خزينة --}}
        <div class="row mt-3">
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>{{ number_format($purchasesOverview['total_purchases'] ?? 0,2) }} ر.س</h3>
                        <p>إجمالي المشتريات</p>
                    </div>
                    <div class="icon"><i class="fa fa-shopping-basket"></i></div>
                    <a href="#" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3>{{ number_format($expensesOverview['total_expenses'] ?? 0,2) }} ر.س</h3>
                        <p>إجمالي المصروفات</p>
                    </div>
                    <div class="icon"><i class="fa fa-credit-card"></i></div>
                    <a href="#" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3>{{ number_format($cashOverview['balance'] ?? 0,2) }} ر.س</h3>
                        <p>الرصيد الحالي بالخزينة</p>
                    </div>
                    <div class="icon"><i class="fa fa-money-bill-wave"></i></div>
                    <a href="#" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">مبيعات الشهر</h3>
                    </div>
                    <div class="box-body"><canvas id="salesBarChart" height="150"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header">
                        <h3 class="box-title">الأرباح والمصروفات</h3>
                    </div>
                    <div class="box-body"><canvas id="profitDoughnutChart" height="150"></canvas></div>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <div class="box box-warning">
                    <div class="box-header">
                        <h3 class="box-title">خط زمني يومي للحركات المالية</h3>
                    </div>
                    <div class="box-body"><canvas id="dailyCashChart" height="150"></canvas></div>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title">أفضل المنتجات مبيعًا</h3>
                    </div>
                    <div class="box-body"><canvas id="topProductsChart" height="150"></canvas></div>
                </div>
            </div>
        </div>

        {{-- تقويم التذكيرات --}}
   




    </section>
</div>
@endsection

@push('scripts')
@endpush
