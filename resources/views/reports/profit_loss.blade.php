@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>تقرير الأرباح والخسائر
            <small>ملخص الأداء المالي</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">تقرير الأرباح والخسائر</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            {{-- البطاقات المالية --}}
            <div class="col-md-3">
                <div class="box box-success text-center">
                    <div class="box-header bg-success text-white fs-6 fw-bold rounded-top-4">
                        الإيرادات
                    </div>
                    <div class="box-body fs-5 fw-bold mt-2">
                        {{ number_format($revenues,2) }} ر.س
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="box box-danger text-center">
                    <div class="box-header bg-danger text-white fs-6 fw-bold rounded-top-4">
                        المصروفات
                    </div>
                    <div class="box-body fs-5 fw-bold mt-2">
                        {{ number_format($expenses,2) }} ر.س
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="box text-center">
                    <div class="box-header {{ $profit >= 0 ? 'bg-success' : 'bg-danger' }} text-white fs-6 fw-bold rounded-top-4">
                        الأرباح
                    </div>
                    <div class="box-body fs-5 fw-bold mt-2">
                        {{ number_format($profit,2) }} ر.س
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="box box-primary text-center">
                    <div class="box-header bg-primary text-white fs-6 fw-bold rounded-top-4">
                        رصيد الصندوق
                    </div>
                    <div class="box-body fs-5 fw-bold mt-2">
                        {{ number_format($cashBalance,2) }} ر.س
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header bg-info text-white">نسبة الإيرادات والمصروفات والأرباح</div>
                    <div class="box-body">
                        <canvas id="profitPieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header bg-info text-white">رصيد الصندوق مقابل الأرباح</div>
                    <div class="box-body">
                        <canvas id="cashBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- زر العودة --}}
        <div class="text-center mt-4">
            <a href="{{ route('dashboard.welcome') }}" class="btn btn-outline-secondary">⬅ العودة للرئيسية</a>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pie Chart - الإيرادات والمصروفات والأرباح
    const ctxPie = document.getElementById('profitPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['الإيرادات', 'المصروفات', 'الأرباح'],
            datasets: [{
                data: [{{ $revenues }}, {{ $expenses }}, {{ $profit }}],
                backgroundColor: ['#28a745','#dc3545','#007bff']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { enabled: true }
            }
        }
    });

    // Bar Chart - رصيد الصندوق مقابل الأرباح
    const ctxBar = document.getElementById('cashBarChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['رصيد الصندوق', 'الأرباح'],
            datasets: [{
                label: 'القيمة بالريال',
                data: [{{ $cashBalance }}, {{ $profit }}],
                backgroundColor: ['#007bff','#28a745']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: { legend: { display: false } }
        }
    });
</script>
@endpush
