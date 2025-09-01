@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>تقرير المبيعات المفصل</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">تقرير المبيعات المفصل</li>
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
                                    <th>#</th>
                                    <th>اسم العميل</th>
                                    <th>المنتجات</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ الطلب</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->client->name ?? '-' }}</td>
                                        <td>
                                            @foreach($order->products as $product)
                                                {{ $product->name }} ({{ $product->pivot->quantity }} × {{ number_format($product->pivot->sale_price,2) }})<br>
                                            @endforeach
                                        </td>
                                        <td>{{ number_format($order->total_price,2) }} ج.س</td>
                                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
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
<!-- CSS DataTables -->
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
$(document).ready(function() {

    // تفعيل DataTable
    var table = $('#ordersTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'excel', 'csv', 'pdf', 'print'
        ],
        order: [[0, 'desc']],
        pageLength: 50,
        language: {
            search: "بحث:",
            lengthMenu: "عرض _MENU_ سجل",
            info: "عرض _START_ إلى _END_ من _TOTAL_ سجل",
            infoEmpty: "لا توجد سجلات متاحة",
            zeroRecords: "لا توجد سجلات مطابقة",
            paginate: {
                first: "الأول",
                last: "الأخير",
                next: "التالي",
                previous: "السابق"
            },
            buttons: {
                copy: "نسخ",
                excel: "تصدير Excel",
                csv: "تصدير CSV",
                pdf: "تصدير PDF",
                print: "طباعة"
            }
        }
    });

    // فلترة حسب التاريخ واسم العميل
    $('#searchInput, #fromDate, #toDate').on('input change', function() {
        var search = $('#searchInput').val().toLowerCase();
        var from = $('#fromDate').val() ? new Date($('#fromDate').val()) : null;
        var to = $('#toDate').val() ? new Date($('#toDate').val()) : null;

        table.rows().every(function(){
            var data = this.data();
            var name = data[1].toLowerCase();
            var dateText = data[4];
            var rowDate = new Date(dateText);

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
