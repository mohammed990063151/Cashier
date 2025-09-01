@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>تقرير الأرباح المفصل</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <div class="row mb-2">
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
                <table id="detailedProfitTable" class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العميل</th>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>سعر البيع</th>
                            <th>سعر التكلفة</th>
                            <th>الربح</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            @foreach($order->products as $product)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->client->name ?? '-' }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->pivot->quantity }}</td>
                                    <td>{{ number_format($product->pivot->sale_price,2) }} ج.س</td>
                                    <td>{{ number_format($product->purchase_price,2) }} ج.س</td>
                                    <td>{{ number_format(($order->profit) * $product->pivot->quantity,2) }} ج.س</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
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
$(document).ready(function(){
    var table = $('#detailedProfitTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy','excel','csv','pdf','print'],
        order: [[0,'desc']],
        pageLength: 50,
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json" }
    });

    // فلترة حسب التاريخ واسم العميل
    $('#searchInput, #fromDate, #toDate').on('input change', function(){
        var search = $('#searchInput').val().toLowerCase();
        var from = $('#fromDate').val() ? new Date($('#fromDate').val()) : null;
        var to = $('#toDate').val() ? new Date($('#toDate').val()) : null;
        table.rows().every(function(){
            var data = this.data();
            var name = data[1].toLowerCase();
            var rowDate = new Date(data[0]); // العمود 0 = ID وليس تاريخ، يمكن تعديل لإضافة تاريخ الطلب
            var matchesSearch = name.includes(search);
            var matchesDate = true; // يمكن ربطه بتاريخ الطلب إذا أضفنا عمود التاريخ
            if(matchesSearch && matchesDate) $(this.node()).show();
            else $(this.node()).hide();
        });
    });
});
</script>
@endpush
