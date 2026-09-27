@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>تقرير المبيعات</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">تقرير المبيعات</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <form action="{{ route('dashboard.reports.sales') }}" method="get" class="row">
                            <div class="col-md-3">
                                <input type="date" name="from" class="form-control" value="{{ request('from', $from?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="to" class="form-control" value="{{ request('to', $to?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="بحث برقم الطلب أو العميل" value="{{ request()->search }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> بحث</button>
                            </div>
                        </form>
                    </div>

                    <div class="box-body">
                        <div class="row" style="margin-bottom:12px;">
                            <div class="col-sm-3">
                                <div class="alert alert-success text-center" style="margin:0;">
                                    <small>صافي المبيعات</small><br>
                                    <x-report-money :amount="$snapshot['net_sales']" :entries="$snapshot['fx']['net_sales']" />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="alert alert-info text-center" style="margin:0;">
                                    <small>إجمالي أصلي (قبل المرتجع)</small><br>
                                    <x-report-money :amount="$snapshot['gross_sales']" :entries="$snapshot['fx']['gross_sales']" />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="alert alert-warning text-center" style="margin:0;">
                                    <small>قيمة مرتجعات البضاعة</small><br>
                                    <x-report-money :amount="$snapshot['returns_merchandise']" :entries="$snapshot['fx']['returns']" />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="alert alert-danger text-center" style="margin:0;">
                                    <small>مُسترد من الخزينة</small><br>
                                    <x-report-money :amount="$snapshot['cash_refunded']" />
                                </div>
                            </div>
                        </div>

                        @if ($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover text-center">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>صافي المبلغ</th>
                                        <th>مرتجع</th>
                                        <th>الربح</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        @php $st = $fin->paymentStatus($order); @endphp
                                        <tr>
                                            <td>{{ $order->order_number }}</td>
                                            <td>{{ $order->client->name ?? '-' }} <x-debt-rate :rate="$order->usd_rate" :remaining="$order->remaining" /></td>
                                            <td><x-report-money :amount="$order->total_price" :rate="$order->usd_rate" /></td>
                                            <td>@if($order->total_return > 0)<x-report-money :amount="$order->total_return" :rate="$order->usd_rate" />@else — @endif</td>
                                            <td><x-report-money :amount="$order->profit ?? 0" :rate="$order->usd_rate" /></td>
                                            <td>
                                                <span class="label {{ $fin->paymentStatusClass($st) }}">
                                                    {{ $fin->paymentStatusLabel($st) }}
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $orders->links() }}
                        </div>
                        @else
                            <h4 class="text-center text-muted">لا توجد مبيعات في الفترة المحددة</h4>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header"><h3 class="box-title">ملخص سريع</h3></div>
                    <div class="box-body">
                        <p><strong>عدد الطلبات:</strong> {{ $snapshot['orders_count'] }}</p>
                        <p><strong>ذمم متبقية:</strong> <x-report-money :amount="$snapshot['total_remaining']" :entries="$snapshot['fx']['remaining']" /></p>
                        <p><strong>ربح المبيعات:</strong> <x-report-money :amount="$snapshot['orders_profit']" :entries="$snapshot['fx']['profit']" /></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header"><h3 class="box-title">أعلى العملاء (صافي)</h3></div>
                    <div class="box-body">
                        <canvas id="salesChart" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: @json($salesByClient->take(10)->map(fn($r) => $r->client->name ?? '—')),
            datasets: [{
                label: 'صافي المبيعات',
                data: @json($salesByClient->take(10)->pluck('net_total')),
                backgroundColor: 'rgba(75, 192, 192, 0.6)'
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
</script>
@endpush
