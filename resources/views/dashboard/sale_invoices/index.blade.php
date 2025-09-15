@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>قائمة المبيعات
            <small style="color: green; font-size: x-large;">
                {{ $orders->total() }} طلب
            </small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">قائمة المبيعات</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title">قائمة الطلبات</h3>
                        <div class="row mb-3">
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
                        <table id="ordersTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>رقم الطلب</th>
                                    <th>اسم العميل</th>
                                    <th>إجمالي الطلب</th>
                                    <th>خصومات الطلب</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>تاريخ الإنشاء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="details-control"></td>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->client->name }}</td>
                                        <td style="color: #01941f; font-weight: bold;">
                                            {{ number_format($order->total_amount, 2) }}
                                        </td>
                                        <td style="color: rgb(192, 152, 9); font-weight: bold;">
                                            {{ number_format($order->tax_amount, 2) }}
                                        </td>
                                        <td>{{ number_format($order->paid_amount, 2) }}</td>
                                        <td style="color: #e74c3c; font-weight: bold;">
                                            {{ number_format($order->remaining_amount, 2) }}
                                        </td>
                                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
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
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- jQuery + DataTables JS -->
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

    // تجهيز بيانات الطلبات مع المنتجات
    var ordersData = @json($orders->load('products'));

    // دالة لإنشاء جدول المنتجات
    function format(order) {
        var html = '<table class="table table-sm table-bordered mb-0">';
        html += '<thead><tr><th>اسم المنتج</th><th>الكمية</th><th>سعر البيع</th><th>سعر الشراء</th><th>الإجمالي</th></tr></thead>';
        html += '<tbody>';
        order.products.forEach(function (p) {
            html += '<tr>';
            html += '<td>' + p.name + '</td>';
            html += '<td>' + p.pivot.quantity + '</td>';
            html += '<td>' + parseFloat(p.pivot.sale_price).toFixed(2) + '</td>';
            html += '<td>' + parseFloat(p.pivot.cost_price).toFixed(2) + '</td>';
            html += '<td>' + (p.pivot.quantity * p.pivot.sale_price).toFixed(2) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    // DataTable الأساسي
    var table = $('#ordersTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'csv', 'pdf', 'print'],
        order: [[1, 'desc']],
        pageLength: 50,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
        }
    });

    // حدث الضغط على أيقونة التفاصيل
    $('#ordersTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var orderIndex = row.index();
        var order = ordersData[orderIndex];

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(format(order)).show();
            tr.addClass('shown');
        }
    });

    // فلترة بالتاريخ واسم العميل
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var from = $('#fromDate').val();
        var to = $('#toDate').val();
        var search = $('#searchInput').val().toLowerCase();
        var name = data[2].toLowerCase();
        var dateText = data[6];
        var rowDate = new Date(dateText);

        var matchesSearch = name.includes(search);
        var matchesDate = true;

        if (from) matchesDate = rowDate >= new Date(from);
        if (to) matchesDate = matchesDate && rowDate <= new Date(to);

        return matchesSearch && matchesDate;
    });

    $('#searchInput, #fromDate, #toDate').on('input change', function () {
        table.draw();
    });

});
</script>

<style>
/* أيقونة التوسيع (+) */
td.details-control {
    background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
}
</style>
@endpush
