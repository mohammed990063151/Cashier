@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>قائمة المبيعات  <small style="
    color: green;
    font-size: x-large;
">{{ $orders->total() }} طلب</small></h1>
          
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">قائمة المبيعات </li>
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
                                    <th>رقم الطلب</th>
                                <th>اسم العميل</th>
                                <th>اجمالي طلب</th>
                                <th>المدفوع منه</th>
                                <th>المتبقي عليه</th>
                                <th>تاريخ الإنشاء</th>
                                </tr>
                            </thead>
                            <tbody>
                                  @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->client->name }}</td>
                                <td style="color: #01941f; font-weight: bold;">{{ number_format($order->total_price, 2) }}</td>
                                <td>{{ number_format($order->discount, 2) }}</td>
                                <td style="color: #e74c3c; font-weight: bold;">{{ number_format($order->remaining, 2) }}</td>
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
    var table = $('#ordersTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'csv', 'pdf', 'print'],
        order: [[0, 'desc']],
        pageLength: 50,
        language: {
            search: "بحث:",
            lengthMenu: "عرض _MENU_ سجل",
            info: "عرض _START_ إلى _END_ من _TOTAL_ سجل",
            infoEmpty: "لا توجد سجلات متاحة",
            zeroRecords: "لا توجد سجلات مطابقة",
            paginate: { first: "الأول", last: "الأخير", next: "التالي", previous: "السابق" },
            buttons: { copy: "نسخ", excel: "تصدير Excel", csv: "تصدير CSV", pdf: "تصدير PDF", print: "طباعة" }
        }
    });

    // إضافة فلترة مخصصة للتاريخ واسم العميل
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var search = $('#searchInput').val().toLowerCase();
            var from = $('#fromDate').val();
            var to = $('#toDate').val();

            var name = data[1].toLowerCase();
            var dateText = data[5]; // عمود تاريخ الإنشاء
            var rowDate = new Date(dateText);

            var matchesSearch = name.includes(search);
            var matchesDate = true;

            if (from) {
                matchesDate = rowDate >= new Date(from);
            }
            if (to) {
                matchesDate = matchesDate && rowDate <= new Date(to);
            }

            return matchesSearch && matchesDate;
        }
    );

    $('#searchInput, #fromDate, #toDate').on('input change', function() {
        table.draw();
    });
});

</script>
@endpush
