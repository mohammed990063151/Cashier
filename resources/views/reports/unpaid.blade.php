@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>الفواتير غير المسددة</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">الفواتير غير المسددة</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title">قائمة الفواتير غير المسددة</h3>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <input type="date" id="fromDate" class="form-control" placeholder="من تاريخ">
                            </div>
                            <div class="col-md-3">
                                <input type="date" id="toDate" class="form-control" placeholder="إلى تاريخ">
                            </div>
                            <div class="col-md-3">
                                <input type="text" id="searchInput" class="form-control" placeholder="بحث باسم العميل">
                            </div>
                        </div>
                    </div>

                    <div class="box-body table-responsive">
                        <table id="unpaidOrdersTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم العميل</th>
                                    <th>إجمالي الفاتورة</th>
                                      <th>إجمالي الخصومات</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>تاريخ الطلب</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unpaidOrders as $order)
                                    @php
                                        $paid = $order->payments->sum('amount');
                                    @endphp
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->client->name ?? '-' }}</td>
                                        <td style="color: green">{{ number_format($order->total_price,2) }} ج.س</td>
                                         <td style="color: rgb(173, 173, 5)">{{ number_format($order->tax_amount,2) }} ج.س</td>
                                        {{-- <td>{{ number_format($order->discount + sum($order->payments->amount)  ,2) }} ج.س</td> --}}
                                        <td>{{ number_format($order->discount + $order->payments->sum('amount'), 2) }} ج.س</td>

                                        <td style="color: red">{{ number_format($order->remaining,2) }} ج.س</td>
                                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if(count($unpaidOrders) === 0)
                            <div class="text-center mt-3 text-muted">لا توجد فواتير غير مسددة حالياً</div>
                        @endif
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
<script>
$(document).ready(function () {
    var table = $('#unpaidOrdersTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy', text: 'نسخ' },
            { extend: 'excel', text: 'تصدير Excel' },
            { extend: 'csv', text: 'تصدير CSV' },
            { extend: 'pdf', text: 'تصدير PDF' },
            { extend: 'print', text: 'طباعة' }
        ],
        order: [[0, 'desc']],
        pageLength: 50,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
        }
    });

    // فلترة حسب التاريخ واسم العميل
    $('#searchInput, #fromDate, #toDate').on('input change', function () {
        var search = $('#searchInput').val().toLowerCase();
        var from = $('#fromDate').val() ? new Date($('#fromDate').val()) : null;
        var to = $('#toDate').val() ? new Date($('#toDate').val()) : null;

        table.rows().every(function(){
            var data = this.data();
            var name = data[1].toLowerCase();
            var rowDate = new Date(data[5]); // العمود 5 = تاريخ الطلب

            var matchesSearch = name.includes(search);
            var matchesDate = true;
            if(from) matchesDate = rowDate >= from;
            if(to) matchesDate = matchesDate && rowDate <= to;

            if(matchesSearch && matchesDate) $(this.node()).show();
            else $(this.node()).hide();
        });
    });

});
</script>
@endpush
