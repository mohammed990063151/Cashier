@extends('layouts.dashboard.app')

@section('content')
@php
    $r = $report;
@endphp
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
        <div class="alert alert-info">
            <strong>كيف يُحسب التقرير:</strong>
            صافي المبيعات = قيمة الطلبات بعد المرتجعات.
            ربح المبيعات = مجموع حقل الربح في الطلبات.
            المصروفات = مصروفات التشغيل + المشتريات المدفوعة من الخزينة.
            النتيجة = ربح المبيعات − المصروفات (لا تشمل إضافة نقد مباشر للخزينة كإيراد).
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="box box-success text-center">
                    <div class="box-header bg-success text-white">صافي المبيعات</div>
                    <div class="box-body fs-5 fw-bold">{{ number_format($r['net_sales'], 2) }} ج.س</div>
                    <small class="text-muted">أصلي: {{ number_format($r['gross_sales'], 2) }}</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box box-warning text-center">
                    <div class="box-header bg-warning">ربح المبيعات (هامش)</div>
                    <div class="box-body fs-5 fw-bold">{{ number_format($r['orders_profit'], 2) }} ج.س</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box box-danger text-center">
                    <div class="box-header bg-danger text-white">المصروفات + مشتريات مدفوعة</div>
                    <div class="box-body fs-5 fw-bold">{{ number_format($r['total_outflows'], 2) }} ج.س</div>
                    <small class="text-muted">مصروفات: {{ number_format($r['expenses'], 2) }} | مشتريات: {{ number_format($r['purchases_paid'], 2) }}</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box text-center">
                    <div class="box-header {{ $r['net_result'] >= 0 ? 'bg-success' : 'bg-danger' }} text-white">صافي النتيجة</div>
                    <div class="box-body fs-5 fw-bold">{{ number_format($r['net_result'], 2) }} ج.س</div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top:12px;">
            <div class="col-md-3">
                <div class="box box-primary text-center">
                    <div class="box-header bg-primary text-white">رصيد الصندوق</div>
                    <div class="box-body fs-5 fw-bold">{{ number_format($r['cash_balance'], 2) }} ج.س</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-warning text-center" style="margin:0;height:100%;">
                    <strong>مرتجعات بضاعة:</strong><br>{{ number_format($r['returns_merchandise'], 2) }} ج.س
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-info text-center" style="margin:0;height:100%;">
                    <strong>مُسترد للعملاء (خزينة):</strong><br>{{ number_format($r['cash_refunded'], 2) }} ج.س
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-secondary text-center" style="margin:0;height:100%;">
                    <strong>ذمم عملاء (متبقي):</strong><br>{{ number_format($r['total_remaining'], 2) }} ج.س
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header bg-info text-white">توزيع النتيجة</div>
                    <div class="box-body"><canvas id="profitPieChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header bg-info text-white">ربح المبيعات مقابل المصروفات</div>
                    <div class="box-body"><canvas id="cashBarChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('dashboard.welcome') }}" class="btn btn-default">العودة للرئيسية</a>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('profitPieChart'), {
        type: 'pie',
        data: {
            labels: ['ربح المبيعات', 'المصروفات والمشتريات', 'صافي النتيجة'],
            datasets: [{
                data: [{{ $r['orders_profit'] }}, {{ $r['total_outflows'] }}, {{ max(0, $r['net_result']) }}],
                backgroundColor: ['#28a745','#dc3545','#007bff']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('cashBarChart'), {
        type: 'bar',
        data: {
            labels: ['ربح المبيعات', 'إجمالي المصروفات', 'صافي النتيجة'],
            datasets: [{
                data: [{{ $r['orders_profit'] }}, {{ $r['total_outflows'] }}, {{ $r['net_result'] }}],
                backgroundColor: ['#28a745','#dc3545','#007bff']
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
    });
</script>
@endpush
