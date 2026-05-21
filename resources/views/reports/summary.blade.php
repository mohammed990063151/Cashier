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
        <div class="box box-primary">
            <div class="box-header">
                <form method="get" class="form-inline">
                    <label>من:</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                    <label style="margin:0 8px;">إلى:</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                    <button type="submit" class="btn btn-primary" style="margin-right:8px;">تصفية</button>
                </form>
            </div>
            <div class="box-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4>صافي المبيعات</h4>
                        <strong class="text-success">{{ number_format($snapshot['net_sales'], 2) }} ج.س</strong>
                    </div>
                    <div class="col-md-3">
                        <h4>إجمالي أصلي</h4>
                        <strong>{{ number_format($snapshot['gross_sales'], 2) }} ج.س</strong>
                    </div>
                    <div class="col-md-3">
                        <h4>المرتجعات</h4>
                        <strong class="text-warning">{{ number_format($snapshot['returns_merchandise'], 2) }} ج.س</strong>
                    </div>
                    <div class="col-md-3">
                        <h4>عدد الطلبات</h4>
                        <strong>{{ $snapshot['orders_count'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header"><h3 class="box-title">المبيعات اليومية</h3></div>
            <div class="box-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [
                {
                    label: 'صافي المبيعات',
                    data: @json($netTotals),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.15)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'إجمالي أصلي',
                    data: @json($grossTotals),
                    borderColor: '#17a2b8',
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'ج.س' } }
            }
        }
    });
</script>
@endpush
