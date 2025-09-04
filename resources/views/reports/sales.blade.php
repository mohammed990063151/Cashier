@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>تقرير المبيعات
            <small>{{ $orders->total() }} طلب مكتمل</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">تقرير المبيعات</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            {{-- جدول الطلبات --}}
            <div class="col-md-8">

                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title" style="margin-bottom: 10px">تقرير المبيعات</h3>

                        {{-- بحث --}}
                        <form action="{{ route('dashboard.reports.sales') }}" method="get">
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

                    @if ($orders->count() > 0)
                        <div class="box-body table-responsive">
                            {{-- إجمالي المبيعات --}}
                            <div class="alert alert-success text-center fs-5 fw-bold">
                                💰 إجمالي المبيعات: {{ number_format($totalSales, 2) }} ر.س
                            </div>

                            <table class="table table-hover text-center">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم العميل</th>
                                        <th>المبلغ</th>
                                        <th>تاريخ الطلب</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->client->name ?? '-' }}</td>
                                            <td>{{ number_format($order->total_price,2) }} ر.س</td>
                                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            {{-- Pagination --}}
                            <div class="d-flex justify-content-center mt-4">
                                {{ $orders->links() }}
                            </div>
                        </div>
                    @else
                        <div class="box-body">
                            <h3 class="text-center text-muted">لا توجد مبيعات حالياً</h3>
                        </div>
                    @endif

                </div>

            </div><!-- end col-md-8 -->

            {{-- ملخص عام --}}
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">ملخص التقرير</h3>
                    </div>
                    <div class="box-body text-center">
                        <p><strong>إجمالي المبيعات:</strong> {{ number_format($totalSales,2) }} ر.س</p>
                        <p><strong>عدد الطلبات:</strong> {{ $orders->total() }}</p>
                    </div>
                </div>
            </div><!-- end col-md-4 -->

            {{-- ملخص العملاء + رسم بياني --}}
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">ملخص العملاء</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <th>العميل</th>
                                    <th>إجمالي المبيعات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesByClient as $row)
                                    <tr>
                                        <td>{{ $row->client->name ?? '-' }}</td>
                                        <td>{{ number_format($row->total,2) }} ر.س</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 📊 الرسم البياني --}}
                    <div class="box-footer">
                        <canvas id="salesChart" height="200"></canvas>
                    </div>
                </div>
            </div><!-- end col-md-4 -->

        </div><!-- end row -->

    </section>

</div>


@push('scripts')
{{-- تحميل مكتبة Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar', // ممكن تخليه 'pie' لو تحب
        data: {
            labels: @json($salesByClient->pluck('client.name')),
            datasets: [{
                label: 'إجمالي المبيعات (ج.س)',
                data: @json($salesByClient->pluck('total')),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endpush
@endsection
