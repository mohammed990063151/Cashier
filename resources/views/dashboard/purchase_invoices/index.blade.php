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

                <form action="{{ route('dashboard.purchase-invoices.index') }}" method="get" class="mobile-filter-panel">
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
                <div class="table-responsive mobile-card-table">
                    <table id="purchaseInvoicesTable" class="table table-hover table-bordered text-center">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>المورد</th>
                                <th>المنتجات</th>
                                <th>الوحدة × الكمية</th>
                                <th>الإجمالي</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseInvoices as $invoice)
                            <tr>
                                <td data-label="رقم الفاتورة"><strong>{{ $invoice->invoice_number  }}</strong></td>
                                <td data-label="المورد">{{ $invoice->supplier->name ?? 'غير معروف' }}</td>
                                <td data-label="المنتجات">
                                    @foreach ($invoice->items as $item)
                                    {{ $item->product->name }}<br>
                                    @endforeach
                                </td>

                                <td data-label="الكمية">
                                    @foreach ($invoice->items as $item)
                                    {{ $item->purchase_unit_label ?? 'حبة' }} × {{ $item->entered_qty ?? $item->quantity }}
                                    <small class="text-muted">({{ $item->quantity }} حبة)</small><br>
                                    @endforeach
                                </td>
                                <td data-label="الإجمالي">{{ number_format($invoice->total, 2) }}</td>
                                <td data-label="المدفوع">{{ number_format($invoice->paid, 2) }}</td>
                                <td data-label="المتبقي">{{ number_format($invoice->remaining, 2) }}</td>
                                <td data-label="التاريخ">{{ $invoice->created_at->format('Y-m-d') }}</td>
                                <td data-label="الإجراءات">
                                    <div class="phone-action-bar purchase-action-bar">
                                        <a href="{{ route('dashboard.purchase-invoices.show', $invoice->id) }}" class="btn btn-info">
                                            <i class="fa fa-eye"></i> عرض
                                        </a>
                                        <a href="{{ route('dashboard.purchase-invoices.edit', $invoice->id) }}" class="btn btn-primary">
                                            <i class="fa fa-edit"></i> تعديل
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="products-pagination text-center">
                    {{ $purchaseInvoices->appends(request()->query())->links() }}
                </div>

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
        // على الهاتف: لا DataTables حتى لا يتعارض مع ترقيم Laravel والبطاقات
        if (window.innerWidth <= 767 || !$.fn.DataTable) {
            return;
        }
        var table = $('#purchaseInvoicesTable').DataTable({
            dom: 'Bfrtip'
            , paging: false
            , info: false
            , searching: false
            , buttons: [{
                    extend: 'copy'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                }
                , {
                    extend: 'excel'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                }
                , {
                    extend: 'csv'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                }
                , {
                    extend: 'pdf'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                    , orientation: 'landscape'
                    , pageSize: 'A4'
                }
                , {
                    extend: 'print'
                    , exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                }
            ]
            , order: [
                [0, 'desc']
            ]
            , language: {
                search: "بحث:"
                , buttons: {
                    copy: "نسخ"
                    , excel: "تصدير Excel"
                    , csv: "تصدير CSV"
                    , pdf: "تصدير PDF"
                    , print: "طباعة"
                }
            }
        });
    });

</script>
@endpush
