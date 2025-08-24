{{-- resources/views/dashboard/reports/overview.blade.php --}}
{{-- @extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>نظرة عامة على الحسابات</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
<li class="active">نظرة عامة</li>
</ol>
</section>

<section class="content">


    <div class="row mb-3">
        <div class="col-md-12">
            <form method="get" action="{{ route('dashboard.reports.overview') }}" class="form-inline">
                <label for="filter" class="mr-2">فلترة:</label>
                <select name="filter" id="filter" class="form-control mr-2">
                    <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="yesterday" {{ request('filter') == 'yesterday' ? 'selected' : '' }}>أمس</option>
                    <option value="last7days" {{ request('filter') == 'last7days' ? 'selected' : '' }}>آخر 7 أيام</option>
                    <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>هذا الشهر</option>
                    <option value="last_month" {{ request('filter') == 'last_month' ? 'selected' : '' }}>الشهر الماضي</option>
                    <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>تاريخ مخصص</option>
                </select>
                <button class="btn btn-primary">تحديث</button>
            </form>
        </div>
    </div>


    <div class="row">


        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ number_format($salesOverview['total_sales'] ?? 0, 2) }} ر.س</h3>
                    <p>إجمالي المبيعات</p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <a href="{{ route('dashboard.reports.sales_detail') }}" class="small-box-footer">
                    عرض التفاصيل <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>


        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ number_format($profitsOverview['total_profit'] ?? 0, 2) }} ر.س</h3>
                    <p>إجمالي الأرباح</p>
                </div>
                <div class="icon">
                    <i class="fa fa-dollar-sign"></i>
                </div>
                <a href="{{ route('dashboard.reports.profits') }}" class="small-box-footer">
                    عرض التفاصيل <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>


        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $clientsOverview['total_due'] ?? 0 }} ر.س</h3>
                    <p>المبالغ المتبقية للعملاء</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
                <a href="{{ route('dashboard.reports.clients') }}" class="small-box-footer">
                    عرض التفاصيل <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>


        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $suppliersOverview['total_due'] ?? 0 }} ر.س</h3>
                    <p>المبالغ المتبقية للموردين</p>
                </div>
                <div class="icon">
                    <i class="fa fa-truck"></i>
                </div>
                <a href="{{ route('dashboard.reports.suppliers_remaining') }}" class="small-box-footer">
                    عرض التفاصيل <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

    </div>


    <div class="row">

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ number_format($purchasesOverview['total_purchases'] ?? 0, 2) }} ر.س</h3>
                    <p>إجمالي المشتريات</p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-basket"></i>
                </div>
                <a href="{{ route('dashboard.reports.purchases_detail') }}" class="small-box-footer">
                    عرض التفاصيل <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3>{{ number_format($expensesOverview['total_expenses'] ?? 0, 2) }} ر.س</h3>
                    <p>إجمالي المصروفات</p>
                </div>
                <div class="icon">
                    <i class="fa fa-credit-card"></i>
                </div>
                <a href="{{ route('dashboard.reports.expenses_detail') }}" class="small-box-footer">
                    عرض التفاصيل <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ number_format($cashOverview['balance'] ?? 0, 2) }} ر.س</h3>
                    <p>الرصيد الحالي بالخزينة</p>
                </div>
                <div class="icon">
                    <i class="fa fa-money-bill-wave"></i>
                </div>
                <a href="{{ route('dashboard.reports.cash') }}" class="small-box-footer">
                    عرض التفاصيل <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

</section>

</div>
@endsection --}}
{{-- resources/views/dashboard/overview.blade.php --}}
@extends('layouts.dashboard.app')

@section('content')

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

            <div class="row mb-3">
                <div class="col-md-12">
                    <form method="get"
                    {{-- action="{{ route('dashboard.reports.overview') }}" --}}
                    class="form-inline">
                        <label for="filter" class="mr-2">فلترة:</label>
                        <select name="filter" id="filter" class="form-control mr-2">
                            <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>اليوم</option>
                            <option value="yesterday" {{ request('filter') == 'yesterday' ? 'selected' : '' }}>أمس</option>
                            <option value="last7days" {{ request('filter') == 'last7days' ? 'selected' : '' }}>آخر 7 أيام</option>
                            <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>هذا الشهر</option>
                            <option value="last_month" {{ request('filter') == 'last_month' ? 'selected' : '' }}>الشهر الماضي</option>
                            <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>تاريخ مخصص</option>
                        </select>
                        <button class="btn btn-primary">تحديث</button>
                    </form>
                </div>
            </div>
            {{-- التصنيفات --}}
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $categories_count }}</h3>
                        <p>التصنيفات</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="{{ route('dashboard.categories.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- المنتجات --}}
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $products_count }}</h3>
                        <p>المنتجات</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="{{ route('dashboard.products.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- العملاء --}}
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $clients_count }}</h3>
                        <p>العملاء</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <a href="{{ route('dashboard.clients.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- المستخدمون --}}
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $users_count }}</h3>
                        <p>المستخدمون</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <a href="{{ route('dashboard.users.index') }}" class="small-box-footer">عرض <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- فلترة النظرة العامة --}}


        {{-- الصف الثاني: نظرة عامة مالية --}}
        <div class="row">
            {{-- المبيعات --}}
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ number_format($salesOverview['total_sales'] ?? 0, 2) }} ر.س</h3>
                        <p>إجمالي المبيعات</p>
                    </div>
                    <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                    <a href="
                    {{-- {{ route('dashboard.reports.sales_detail') }} --}}
                     " class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- الأرباح --}}
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ number_format($profitsOverview['total_profit'] ?? 0, 2) }} ر.س</h3>
                        <p>إجمالي الأرباح</p>
                    </div>
                    <div class="icon"><i class="fa fa-dollar-sign"></i></div>
                    <a href="{{ route('dashboard.reports.profits') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- العملاء --}}
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $clientsOverview['total_due'] ?? 0 }} ر.س</h3>
                        <p>المبالغ المتبقية للعملاء</p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                    <a href="{{ route('dashboard.reports.clients') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- الموردين --}}
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $suppliersOverview['total_due'] ?? 0 }} ر.س</h3>
                        <p>المبالغ المتبقية للموردين</p>
                    </div>
                    <div class="icon"><i class="fa fa-truck"></i></div>
                    <a href="{{ route('dashboard.reports.suppliers_remaining') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- الصف الثالث: المشتريات والمصروفات والخزينة --}}
        <div class="row">
            {{-- المشتريات --}}
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>{{ number_format($purchasesOverview['total_purchases'] ?? 0, 2) }} ر.س</h3>
                        <p>إجمالي المشتريات</p>
                    </div>
                    <div class="icon"><i class="fa fa-shopping-basket"></i></div>
                    <a href="{{ route('dashboard.reports.purchases_detail') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- المصروفات --}}
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3>{{ number_format($expensesOverview['total_expenses'] ?? 0, 2) }} ر.س</h3>
                        <p>إجمالي المصروفات</p>
                    </div>
                    <div class="icon"><i class="fa fa-credit-card"></i></div>
                    <a href="{{ route('dashboard.reports.expenses_detail') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- الخزينة --}}
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3>{{ number_format($cashOverview['balance'] ?? 0, 2) }} ر.س</h3>
                        <p>الرصيد الحالي بالخزينة</p>
                    </div>
                    <div class="icon"><i class="fa fa-money-bill-wave"></i></div>
                    <a href="{{ route('dashboard.reports.cash') }}" class="small-box-footer">عرض التفاصيل <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- الرسم البياني للمبيعات --}}
        <div class="box box-solid mt-4">
            <div class="box-header">
                <h3 class="box-title">Sales Graph</h3>
            </div>
            <div class="box-body border-radius-none">
                <div class="chart" id="line-chart" style="height: 250px;"></div>
            </div>
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    var line = new Morris.Line({
        element: 'line-chart'
        , resize: true
        , data: [
            @foreach($sales_data as $data) {
                ym: "{{ $data->year }}-{{ $data->month }}"
                , sum: "{{ $data->sum }}"
            }
            , @endforeach
        ]
        , xkey: 'ym'
        , ykeys: ['sum']
    , labels: ['الإجمالي']
        , lineWidth: 2
        , hideHover: 'auto'
        , gridStrokeWidth: 0.4
        , pointSize: 4
        , gridTextFamily: 'Open Sans'
        , gridTextSize: 10
    });

</script>
@endpush
