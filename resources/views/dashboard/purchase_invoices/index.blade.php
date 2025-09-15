@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>فواتير الشراء</h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li class="active">فواتير الشراء</li>
        </ol>
    </section>

    <section class="content">

        <div class="box box-primary">

            <div class="box-header with-border">
                <h3 class="box-title">
                    قائمة الفواتير <small>{{ $purchaseInvoices->total() }}</small>
                </h3>

                <form action="{{ route('dashboard.purchase-invoices.index') }}" method="get">
                    <div class="row" style="margin-top: 10px">

                        <div class="col-md-4 col-sm-6 col-xs-12" style="margin-bottom:10px">
                            <input type="text" name="search" class="form-control" placeholder="بحث باسم المورد أو رقم الفاتورة" value="{{ request()->search }}">
                        </div>

                        <div class="col-md-4 col-sm-6 col-xs-12" style="margin-bottom:10px">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-search"></i> بحث
                            </button>
                        </div>

                        <div class="col-md-4 col-sm-12 col-xs-12" style="margin-bottom:10px">
                            <a href="{{ route('dashboard.purchase-invoices.create') }}" class="btn btn-success btn-block">
                                <i class="fa fa-plus"></i> إضافة
                            </a>
                        </div>

                    </div>
                </form>
            </div><!-- /.box-header -->

            <div class="box-body">

                @if ($purchaseInvoices->count() > 0)
                <div class="table-responsive">
                    <table id="purchaseInvoicesTable" class="table table-hover table-bordered text-center">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>المورد</th>
                                <th>اسماء المنتجات</th>
                                <th>السعر <br />الكمية</th>
                                <th>الإجمالي</th>
                                <th>الخصومات</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseInvoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number  }}</td>
                                <td>{{ $invoice->supplier->name ?? 'غير معروف' }}</td>
                                <td>
                                    @foreach ($invoice->items as $item)
                                    {{ $item->product->name }}<br>
                                    @endforeach
                                </td>

                                <td>
                                    @foreach ($invoice->items as $item)
                                    {{ number_format($item->price, 2) }} × {{ number_format($item->quantity, 2) }}<br>
                                    @endforeach
                                </td>


                                <td>{{ number_format($invoice->total, 2) }}</td>
                                <td>{{ number_format($invoice->tax_amount, 2) }}</td>
                                <td>{{ number_format($invoice->paid, 2) }}</td>
                                <td>{{ number_format($invoice->remaining, 2) }}</td>
                                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="btn-group" role="group" style="flex-wrap: wrap;">
                                        <a href="{{ route('dashboard.purchase-invoices.show', $invoice->id) }}" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i> عرض
                                        </a>
                                        <a href="{{ route('dashboard.purchase-invoices.edit', $invoice->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-edit"></i> تعديل
                                        </a>
                                        {{-- <form action="{{ route('dashboard.purchase-invoices.destroy', $invoice->id) }}" method="post" style="display:inline-block">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-danger btn-sm delete">
                                                <i class="fa fa-trash"></i> حذف
                                            </button>
                                        </form> --}}
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $purchaseInvoices->appends(request()->query())->links() }}

                @else
                <h4>لا توجد فواتير.</h4>
                @endif

            </div><!-- /.box-body -->

        </div><!-- /.box -->

    </section><!-- /.content -->

</div><!-- /.content-wrapper -->

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
        var table = $('#purchaseInvoicesTable').DataTable({
            dom: 'Bfrtip'
            , buttons: [{
                    extend: 'copy'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7,8]
                    } // استبعاد عمود الإجراءات
                }
                , {
                    extend: 'excel'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7,8]
                    }
                }
                , {
                    extend: 'csv'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7,8]
                    }
                }
                , {
                    extend: 'pdf'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7,8]
                    }
                    , orientation: 'landscape'
                    , pageSize: 'A4'
                }
                , {
                    extend: 'print'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7,8]
                    }
                }
            ]
            , order: [
                [0, 'desc']
            ]
            , pageLength: 50
            , language: {
                search: "بحث:"
                , lengthMenu: "عرض _MENU_ سجل"
                , info: "عرض _START_ إلى _END_ من _TOTAL_ سجل"
                , infoEmpty: "لا توجد سجلات متاحة"
                , zeroRecords: "لا توجد سجلات مطابقة"
                , paginate: {
                    first: "الأول"
                    , last: "الأخير"
                    , next: "التالي"
                    , previous: "السابق"
                }
                , buttons: {
                    copy: "نسخ"
                    , excel: "تصدير Excel"
                    , csv: "تصدير CSV"
                    , pdf: "تصدير PDF"
                    , print: "طباعة"
                }
            }
            , responsive: true
        });
    });

</script>
@endpush
