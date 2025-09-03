@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>المصروفات <small>{{ $expenses->total() }} مصروف</small></h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">المصروفات</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h3 class="box-title mb-2 mb-md-0">قائمة المصروفات</h3>
                <div class="d-flex flex-wrap gap-2">
                    <div class="row mb-3">
    <!-- البحث العام -->
    <div class="col-md-4 mb-2">
        <input type="text" id="searchInput" class="form-control" placeholder="ابحث هنا...">
    </div>

    <!-- تاريخ البداية -->
    <div class="col-md-3 mb-2">
        <input type="date" id="fromDate" class="form-control" placeholder="من تاريخ">
    </div>

    <!-- تاريخ النهاية -->
    <div class="col-md-3 mb-2">
        <input type="date" id="toDate" class="form-control" placeholder="إلى تاريخ">
    </div>

    <!-- زر إعادة الضبط -->
    <div class="col-md-2 mb-2">
        <button id="resetFilter" class="btn btn-secondary w-100">إعادة ضبط الفلتر</button>
    </div>
</div><br /><br />

                    <a href="{{ route('dashboard.expenses.create') }}" class="btn btn-primary">➕ إضافة مصروف</a>
                </div>
            </div>

            <div class="box-body table-responsive">
                <table id="expensesTable" class="table table-hover table-bordered text-center table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>المبلغ</th>
                            <th>النوع</th>
                            <th>ملاحظة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td>{{ $expense->id }}</td>
                            <td class="fw-bold">{{ $expense->title }}</td>
                            <td class="text-danger fw-bold">{{ number_format($expense->amount, 2) }} ج.س</td>
                            <td>
                                <span class="badge {{ $expense->type == 'operational' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                    {{ $expense->type == 'operational' ? 'تشغيلي' : 'أخرى' }}
                                </span>
                            </td>
                            <td>{{ $expense->note ?? '-' }}</td>
                            <td>{{ $expense->created_at->format('d-m-Y') }}</td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <a href="{{ route('dashboard.expenses.edit', $expense->id) }}" class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <form action="{{ route('dashboard.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصروف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="حذف">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
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

    var table = $('#expensesTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'excel', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'csv', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'pdf', exportOptions: { columns: [0,1,2,3,4,5] }, orientation: 'landscape', pageSize: 'A4' },
            { extend: 'print', exportOptions: { columns: [0,1,2,3,4,5] } }
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

    // البحث الخارجي
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // فلترة حسب التاريخ
    $('#fromDate, #toDate').on('change', function() {
        var min = $('#fromDate').val();
        var max = $('#toDate').val();

        table.draw();
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var date = data[5]; // عمود تاريخ الإنشاء
                if(!date) return false;

                var dateParts = date.split('-');
                var d = new Date(dateParts[2], dateParts[1]-1, dateParts[0]); // تحويل dd-mm-yyyy إلى تاريخ
                var from = min ? new Date(min) : null;
                var to = max ? new Date(max) : null;

                if((!from || d >= from) && (!to || d <= to)) return true;
                return false;
            }
        );
        table.draw();
    });

});
</script>
@endpush
