@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>تقرير الأرباح المجمل</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <form method="get" class="form-inline">
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                    <input type="date" name="to" class="form-control" value="{{ $to }}" style="margin:0 8px;">
                    <button type="submit" class="btn btn-primary">تصفية</button>
                </form>
            </div>
            <div class="alert alert-info">
                الأرباح محسوبة من <strong>حقل ربح الطلب</strong> (بعد الخصم والمرتجعات).
                صافي المبيعات: {{ number_format($snapshot['net_sales'] ?? 0, 2) }} —
                مرتجعات: {{ number_format($snapshot['returns_merchandise'] ?? 0, 2) }} ج.س
            </div>
            <div class="box-body table-responsive">
                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th>صافي المبيعات</th>
                            <th>تقدير التكلفة</th>
                            <th>ربح المبيعات</th>
                            <th>هامش الربح %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ number_format($totalSales, 2) }} ج.س</td>
                            <td>{{ number_format($totalCost, 2) }} ج.س</td>
                            <td class="text-success"><strong>{{ number_format($totalProfit, 2) }} ج.س</strong></td>
                            <td>{{ $totalSales > 0 ? number_format(($totalProfit / $totalSales) * 100, 2) : 0 }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4"><div class="box"><div class="box-body"><canvas id="pieChart"></canvas></div></div></div>
            <div class="col-md-4"><div class="box"><div class="box-body"><canvas id="barChart"></canvas></div></div></div>
            <div class="col-md-4"><div class="box"><div class="box-body"><canvas id="lineChart"></canvas></div></div></div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = ['صافي المبيعات','التكلفة','الربح'];
    const data = [{{ $totalSales }}, {{ $totalCost }}, {{ $totalProfit }}];
    const colors = ['#28a745','#dc3545','#007bff'];
    new Chart(document.getElementById('pieChart'), { type: 'pie', data: { labels, datasets: [{ data, backgroundColor: colors }] } });
    new Chart(document.getElementById('barChart'), { type: 'bar', data: { labels, datasets: [{ data, backgroundColor: colors }] }, options: { scales: { y: { beginAtZero: true } } } });
    new Chart(document.getElementById('lineChart'), { type: 'line', data: { labels, datasets: [{ data, borderColor: '#007bff', fill: true }] } });
</script>
@endpush
