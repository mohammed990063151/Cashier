@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>تفاصيل العميل: {{ $client->name }}</h1>
        <small>الرصيد المتبقي: {{ number_format($remainingBalance,2) }} ج.س</small>
    </section>

    <section class="content">
        <div class="box box-primary">

            <div class="box-header">
                <a href="{{ route('dashboard.reports.reports.index') }}" class="btn btn-default">⬅ رجوع</a>
            </div>

            <div class="box-body">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab-invoices" data-toggle="tab">فواتير العميل</a></li>
                    <li><a href="#tab-products" data-toggle="tab">المنتجات المباعة</a></li>
                    <li><a href="#tab-statement" data-toggle="tab">كشف الحساب</a></li>
                </ul>

                <div class="tab-content" style="margin-top:15px;">
                    {{-- Invoices --}}
                    <div class="tab-pane active" id="tab-invoices">
                        <table id="invoicesTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>رقم الفاتورة</th>
                                    <th>الإجمالي</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>تاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $inv)
                                <tr>
                                    <td>{{ $inv->id }}</td>
                                    <td>{{ $inv->order_number }}</td>
                                    <td>{{ number_format($inv->total,2) }} ج.س</td>
                                    <td>{{ number_format($inv->paid,2) }} ج.س</td>
                                    <td>{{ number_format($inv->remaining,2) }} ج.س</td>
                                    <td>{{ $inv->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Products --}}
                    <div class="tab-pane" id="tab-products">
                        <table id="productsTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المنتج</th>
                                    <th>الكمية المباعة</th>
                                    <th>إجمالي المبيعات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productsSold as $index => $p)
                                <tr>
                                    <td>{{ $index+1 }}</td>
                                    <td>{{ $p->product_name }}</td>
                                    <td>{{ $p->quantity }}</td>
                                    <td>{{ number_format($p->total_sales,2) }} ج.س</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Statement --}}
                    <div class="tab-pane" id="tab-statement">
                        <table id="statementTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>التاريخ</th>
                                    <th>الإجمالي</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statement as $s)
                                <tr>
                                    <td>{{ $s->order_number }}</td>
                                    <td>{{ $s->date->format('Y-m-d') }}</td>
                                    <td>{{ number_format($s->total,2) }} ج.س</td>
                                    <td>{{ number_format($s->paid,2) }} ج.س</td>
                                    <td>{{ number_format($s->remaining,2) }} ج.س</td>
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
<!-- DataTables assets (إذا مضافة مرة واحدة في layout فلا حاجة هنا) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function(){
    $('#invoicesTable, #productsTable, #statementTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy','excel','csv','pdf','print'],
        pageLength: 25,
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json" }
    });

    // لتفعيل تبويبات bootstrap (لو تستخدم bootstrap)
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
    });
});
</script>
@endpush
