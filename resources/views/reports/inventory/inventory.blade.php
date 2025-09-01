@extends('layouts.dashboard.app')
@section('title','جرد المخزن')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>📦 تقارير جرد المخزن</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">

                {{-- Tabs --}}
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#detailed" data-toggle="tab">📑 جرد مفصل</a></li>
                    <li><a href="#summary" data-toggle="tab">📊 جرد مجمل</a></li>
                    <li><a href="#price_changes" data-toggle="tab">💲 تغير أسعار الشراء</a></li>
                </ul>

                <div class="tab-content" style="margin-top:15px;">

                    {{-- جرد مفصل --}}
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
                                    <th>الصنف</th>
                                    <th>التصنيف</th>
                                    <th>الكمية</th>
                                    <th>سعر الشراء</th>
                                    <th>سعر البيع</th>
                                    <th>إجمالي التكلفة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailed as $index=>$item)
                                    <tr>
                                        <td>{{ $index+1 }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->category->name ?? '-' }}</td>
                                        <td>{{ $item->stock }}</td>
                                        <td>{{ number_format($item->purchase_price,2) }}</td>
                                        <td>{{ number_format($item->sale_price,2) }}</td>
                                        <td>{{ number_format($item->stock * $item->purchase_price,2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- جرد مجمل --}}
                    <div class="tab-pane fade" id="summary">
                        <h4>💰 إجمالي تكلفة المخزون الحالي:
                            <span class="label label-success">
                                {{ number_format($summary,2) }}
                            </span>
                        </h4>

                        {{-- مكان الرسم البياني --}}
                        <canvas id="summaryChart"></canvas>
                        <style>
                            /* تصغير حجم الرسم البياني جدًا */
                            #summaryChart {
                                height: 200px !important; /* ارتفاع صغير */
                                max-width: 100%;
                            }
                        </style>
                    </div>

                    {{-- حركة تغير أسعار الشراء --}}
                    <div class="tab-pane fade" id="price_changes">
                        <table class="table table-bordered table-striped datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الصنف</th>
                                    <th>السعر القديم</th>
                                    <th>السعر الجديد</th>
                                    <th>تاريخ التغيير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($priceChanges as $index=>$change)
                                    <tr>
                                        <td>{{ $index+1 }}</td>
                                        <td>{{ $change->product->name }}</td>
                                        <td>{{ number_format($change->old_price,2) }}</td>
                                        <td>{{ number_format($change->new_price,2) }}</td>
                                        <td>{{ $change->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'excel', 'pdf', 'print'],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
            },
            "pageLength": 10,
            "ordering": true
        });

        // رسم بياني لمجموع كل صنف - حجم صغير جدًا
        var ctx = document.getElementById('summaryChart').getContext('2d');
        var summaryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($productsNames),
                datasets: [{
                    label: 'إجمالي المخزون لكل صنف',
                    data: @json($productsTotals),
                    backgroundColor: '#36A2EB',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'القيمة' } },
                    x: { title: { display: true, text: 'الصنف' } }
                }
            }
        });
    });
</script>
@endpush
