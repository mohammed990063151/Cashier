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
        @include('reports._debt_status')
        <div class="alert alert-info">
            <strong>كيف يُحسب التقرير:</strong>
            صافي المبيعات = قيمة الطلبات بعد المرتجعات.
            ربح المبيعات = مجموع حقل الربح في الطلبات.
            المصروفات = مصروفات التشغيل + المشتريات المدفوعة من الخزينة.
            النتيجة = ربح المبيعات − المصروفات (لا تشمل إضافة نقد مباشر للخزينة كإيراد).
            الديون المعدومة تبقى ظاهرة وحدها، وتُخصم في «النتيجة بعد الهالك» دون حذف المبيعات.
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="box box-success text-center">
                    <div class="box-header bg-success text-white">صافي المبيعات</div>
                    <div class="box-body fs-5 fw-bold"><x-report-money :amount="$r['net_sales']" :entries="$r['fx']['net_sales']" /></div>
                    <small class="text-muted">أصلي: <x-report-money :amount="$r['gross_sales']" :entries="$r['fx']['gross_sales']" /></small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box box-warning text-center">
                    <div class="box-header bg-warning">ربح المبيعات (هامش)</div>
                    <div class="box-body fs-5 fw-bold"><x-report-money :amount="$r['orders_profit']" :entries="$r['fx']['profit']" /></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box box-danger text-center">
                    <div class="box-header bg-danger text-white">المصروفات + مشتريات مدفوعة</div>
                    <div class="box-body fs-5 fw-bold"><x-report-money :amount="$r['total_outflows']" /></div>
                    <small class="text-muted">مصروفات: <x-report-money :amount="$r['expenses']" /> | مشتريات: <x-report-money :amount="$r['purchases_paid']" :entries="$r['fx']['purchases_paid']" /></small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box text-center">
                    <div class="box-header {{ $r['net_result'] >= 0 ? 'bg-success' : 'bg-danger' }} text-white">صافي النتيجة</div>
                    <div class="box-body fs-5 fw-bold"><x-report-money :amount="$r['net_result']" /></div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top:12px;">
            <div class="col-md-3">
                <div class="box box-primary text-center">
                    <div class="box-header bg-primary text-white">رصيد الصندوق</div>
                    <div class="box-body fs-5 fw-bold"><x-report-money :amount="$r['cash_balance']" /></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-warning text-center" style="margin:0;height:100%;">
                    <strong>مرتجعات بضاعة:</strong><br><x-report-money :amount="$r['returns_merchandise']" :entries="$r['fx']['returns']" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-info text-center" style="margin:0;height:100%;">
                    <strong>مُسترد للعملاء (خزينة):</strong><br><x-report-money :amount="$r['cash_refunded']" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-secondary text-center" style="margin:0;height:100%;">
                    <strong>ذمم عملاء (متبقي):</strong><br><x-report-money :amount="$r['total_remaining']" :entries="$r['fx']['remaining']" />
                </div>
            </div>
        </div>

        <div class="row" style="margin-top:12px;">
            <div class="col-md-4">
                <div class="alert alert-danger text-center" style="margin:0;">
                    <strong>ديون معدومة (هوالك)</strong><br>
                    <x-report-money :amount="$r['bad_debt'] ?? 0" :entries="$r['fx']['bad_debt'] ?? []" />
                    <div><small>{{ $r['bad_debt_count'] ?? 0 }} طلب خرج من التحصيل</small></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-warning text-center" style="margin:0;">
                    <strong>ربح المبيعات بعد الهالك</strong><br>
                    <x-report-money :amount="$r['profit_after_bad_debt'] ?? 0" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert {{ ($r['result_after_bad_debt'] ?? 0) >= 0 ? 'alert-success' : 'alert-danger' }} text-center" style="margin:0;">
                    <strong>النتيجة بعد الهالك</strong><br>
                    <x-report-money :amount="$r['result_after_bad_debt'] ?? 0" />
                    <div><small>صافي النتيجة ناقص الديون المعدومة</small></div>
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
