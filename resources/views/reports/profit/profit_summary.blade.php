@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>تقرير الأرباح المجمل</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body table-responsive">
                <table id="summaryProfitTable" class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th>إجمالي المبيعات</th>
                            <th>إجمالي التكلفة</th>
                            <th>إجمالي الأرباح</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ number_format($totalSales,2) }} ج.س</td>
                            <td>{{ number_format($totalCost,2) }} ج.س</td>
                            <td>{{ number_format($totalProfit,2) }} ج.س</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Charts --}}
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header bg-info text-white">مخطط دائري (Pie)</div>
                    <div class="box-body">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header bg-info text-white">مخطط عمودي (Bar)</div>
                    <div class="box-body">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header bg-info text-white">مخطط خطي (Line)</div>
                    <div class="box-body">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function(){
    // DataTable
    $('#summaryProfitTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy','excel','csv','pdf','print'],
        paging: false,
        searching: false,
        info: false,
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json" }
    });

    // البيانات العامة لكل المخططات
    const labels = ['إجمالي المبيعات','إجمالي التكلفة','إجمالي الأرباح'];
    const data = [{{ $totalSales }}, {{ $totalCost }}, {{ $totalProfit }}];
    const colors = ['#28a745','#dc3545','#007bff'];

    // Pie Chart
    new Chart(document.getElementById('pieChart').getContext('2d'), {
        type: 'pie',
        data: { labels: labels, datasets: [{ data: data, backgroundColor: colors }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Bar Chart
    new Chart(document.getElementById('barChart').getContext('2d'), {
        type: 'bar',
        data: { labels: labels, datasets: [{ label: 'القيمة بجنية السوداني', data: data, backgroundColor: colors }] },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Line Chart
    new Chart(document.getElementById('lineChart').getContext('2d'), {
        type: 'line',
        data: { labels: labels, datasets: [{ label: 'القيمة بجنية السوداني', data: data, borderColor: '#007bff', backgroundColor: 'rgba(0,123,255,0.2)', fill: true, tension: 0.4 }] },
        options: { responsive: true }
    });
});
</script>
@endpush
