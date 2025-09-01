@extends('layouts.dashboard.app')
@section('title','تقرير المصروفات')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>💸 تقارير المصروفات</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">

                {{-- Tabs --}}
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#detailed" data-toggle="tab">📑 مصروفات مفصلة</a></li>
                    <li><a href="#summary" data-toggle="tab">📊 مصروفات مجملة</a></li>
                </ul>

                <div class="tab-content" style="margin-top:15px;">

                    {{-- مصروفات مفصلة --}}
                    <div class="tab-pane fade in active" id="detailed">
                        <form method="GET" class="form-inline">
                            <div class="form-group">
                                <label>من:</label>
                                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>إلى:</label>
                                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">تصفية</button>
                        </form>

                        <hr>
                        <table class="table table-bordered table-striped datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>العنوان</th>
                                    <th>النوع</th>
                                    <th>المبلغ</th>
                                    <th>ملاحظة</th>
                                    <th>تاريخ الإضافة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailed as $index => $expense)
                                <tr>
                                    <td>{{ $index+1 }}</td>
                                    <td>{{ $expense->title }}</td>
                                    <td>  {{ $expense->type === 'operational' ? 'تشغيلي' : 'أخرى' }}</td>
                                    <td>{{ number_format($expense->amount,2) }}</td>
                                    <td>{{ $expense->note }}</td>
                                    <td>{{ $expense->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- مصروفات مجملة --}}
                    <div class="tab-pane fade" id="summary">
                        <h4>💰 إجمالي المصروفات:
                            <span class="label label-success">{{ number_format($summary,2) }}</span>
                        </h4>

                        <canvas id="summaryChart"></canvas>
                        <style>
                            #summaryChart { height: 200px !important; max-width: 100%; }
                        </style>
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
$(document).ready(function() {
    $('.datatable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"},
        "pageLength": 10,
        "ordering": true
    });

    // Chart مجموع المصروفات حسب النوع
    var ctx = document.getElementById('summaryChart').getContext('2d');
    var summaryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($types),
            datasets: [{
                label: 'مجموع المصروفات لكل نوع',
                data: @json($typesTotals),
                backgroundColor: '#e74c3c',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'المبلغ' } },
                x: { title: { display: true, text: 'النوع' } }
            }
        }
    });
});
</script>
@endpush
