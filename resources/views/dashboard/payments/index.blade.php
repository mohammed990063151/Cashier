@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>المدفوعات</h1>
    </section>

    <section class="content">
        <div class="box box-primary">

            <div class="box-header d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h3 class="box-title mb-2 mb-md-0">قائمة الطلبات</h3>
                <div class="form-inline mb-2 mb-md-0">
                    <input type="text" id="searchInput" class="form-control" placeholder="بحث باسم العميل أو رقم الطلب">
                </div>
            </div>

            <div class="box-body table-responsive">
                <table id="paymentsTable" class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>اسم العميل</th>
                            <th>إجمالي الطلب</th>
                            <th>الدفع عند الشراء</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->client->name }}</td>
                            <td style="color: green;">{{ number_format($order->total_price,2) }}</td>
                            <td style="color: rgb(163, 153, 6);">{{ number_format($order->discount,2) }}</td>
                            <td style="color: rgb(86, 157, 23);">{{ number_format($order->payments->sum('amount'),2) }}</td>
                            <td style="color: red;">{{ number_format($order->remaining,2) }}</td>
                            <td class="d-flex flex-wrap justify-content-center">
                                <button class="btn btn-success btn-sm mr-1 mb-1 add-payment-btn" 
                                        data-toggle="modal" 
                                        data-target="#paymentModal" 
                                        data-order-id="{{ $order->id }}"
                                        data-remaining="{{ $order->remaining }}">
                                    إضافة دفعة
                                </button>

                                <a href="{{ route('dashboard.payments.edit', $order->id) }}" class="btn btn-warning btn-sm mr-1 mb-1">
                                    تعديل دفعات الطلب
                                </a>

                                <button class="btn btn-info btn-sm mb-1 view-payments-btn" 
                                        data-toggle="modal" 
                                        data-target="#viewPaymentsModal" 
                                        data-order-id="{{ $order->id }}">
                                    عرض المدفوعات
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </section>
</div>
@endsection

@push('scripts')
<!-- DataTables CSS & JS -->
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
$(document).ready(function() {

    var table = $('#paymentsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                exportOptions: { columns: [0,1,2,3,4,5] } // استبعاد عمود العمليات
            },
            {
                extend: 'excel',
                exportOptions: { columns: [0,1,2,3,4,5] }
            },
            {
                extend: 'csv',
                exportOptions: { columns: [0,1,2,3,4,5] }
            },
            {
                extend: 'pdf',
                exportOptions: { columns: [0,1,2,3,4,5] },
                orientation: 'landscape',
                pageSize: 'A4'
            },
            {
                extend: 'print',
                exportOptions: { columns: [0,1,2,3,4,5] }
            }
        ],
        language: {
            search: "بحث:",
            lengthMenu: "عرض _MENU_ سجل",
            info: "عرض _START_ إلى _END_ من _TOTAL_ سجل",
            infoEmpty: "لا توجد سجلات متاحة",
            zeroRecords: "لا توجد سجلات مطابقة",
            paginate: { first: "الأول", last: "الأخير", next: "التالي", previous: "السابق" },
            buttons: { copy: "نسخ", excel: "تصدير Excel", csv: "تصدير CSV", pdf: "تصدير PDF", print: "طباعة" }
        },
        pageLength: 25,
        responsive: true
    });

    // ربط البحث الخارجي مع DataTable
    $('#searchInput').on('keyup', function(){
        table.search(this.value).draw();
    });

});
</script>
@endpush
