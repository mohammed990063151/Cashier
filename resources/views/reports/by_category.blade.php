@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>تقرير المبيعات حسب التصنيف</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">تقرير حسب التصنيف</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title">قائمة المبيعات حسب التصنيف</h3>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <input type="date" id="fromDate" class="form-control" placeholder="من تاريخ">
                            </div>
                            <div class="col-md-3">
                                <input type="date" id="toDate" class="form-control" placeholder="إلى تاريخ">
                            </div>
                        </div>
                    </div>

                    <div class="box-body table-responsive">
                        <table id="categoryReportTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>التصنيف</th>
                                    <th>إجمالي المبيعات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesByCategory as $index => $category)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $category->category_name }}</td>
                                        <td>{{ number_format($category->total_sales,2) }} ج.س</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if(count($salesByCategory) === 0)
                            <div class="text-center mt-3 text-muted">لا توجد بيانات حالياً</div>
                        @endif
                    </div>
<div class="row">
    <div class="col-md-12 d-flex justify-content-center">
        <div style="width: 60%; max-width: 600px;">
            <canvas id="salesByCategoryChart" height="200"></canvas>
        </div>
    </div>
</div>


                </div>
            </div>
        </div>

    </section>

</div>
@endsection

@push('scripts')
<!-- DataTables & Buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- JS DataTables -->
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
    var ctx = document.getElementById('salesByCategoryChart').getContext('2d');
    var salesByCategoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'إجمالي المبيعات (ج.س)',
                data: @json($totals),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    labels: { color: '#333' }
                },
                title: {
                    display: true,
                    text: '📊 المبيعات حسب التصنيف',
                    font: { size: 18 }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#333' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#333' }
                }
            }
        }
    });
</script>

<script>
$(document).ready(function () {
    var table = $('#categoryReportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy', text: 'نسخ' },
            { extend: 'excel', text: 'تصدير Excel' },
            { extend: 'csv', text: 'تصدير CSV' },
            { extend: 'pdf', text: 'تصدير PDF' },
            { extend: 'print', text: 'طباعة' }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
        }
    });

    // فلترة التاريخ
    $('#fromDate, #toDate').on('change', function () {
        var from = $('#fromDate').val();
        var to = $('#toDate').val();
        table.rows().every(function () {
            var date = this.data()[2]; // العمود الثالث = اجمالي المبيعات (غير مرتبط بتاريخ في هذا الجدول)
            this.visible(true); // خليه يظهر كله لأن البيانات مافيها تاريخ هنا
        });
    });
});
</script>
@endpush
