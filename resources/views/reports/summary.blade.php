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

        {{-- مخطط بياني --}}
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">المبيعات حسب الفترة الزمنية</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="salesChart" height="100"></canvas>
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
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [{
                label: 'إجمالي المبيعات',
                data: @json($totals),
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#28a745',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, labels: { font: { size: 14 } } },
            },
            scales: {
                x: { title: { display: true, text: 'التاريخ' } },
                y: { title: { display: true, text: 'المبيعات (ج.س)' }, beginAtZero: true }
            }
        }
    });
</script>
@endpush
