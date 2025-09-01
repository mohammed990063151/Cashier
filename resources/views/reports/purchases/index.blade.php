@extends('layouts.dashboard.app')
@section('title','تقارير المشتريات')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>📊 تقارير المشتريات</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">

                {{-- Tabs --}}
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#summary" data-toggle="tab">📊 تقرير مجمل</a></li>
                    <li><a href="#detailed" data-toggle="tab">📑 تقرير مفصل</a></li>
                    <li><a href="#byCategory" data-toggle="tab">🏷️ حسب التصنيف</a></li>
                    <li><a href="#unpaid" data-toggle="tab">💳 الفواتير غير المسددة</a></li>
                </ul>

                <div class="tab-content" style="margin-top:20px;">

                    {{-- تقرير مجمل --}}
                    <div class="tab-pane fade in active" id="summary">
                        <table id="summaryTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>عدد الفواتير</th>
                                    <th>إجمالي المدفوع</th>
                                    <th>إجمالي المتبقي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices->groupBy('supplier_id') as $supplierId => $group)
                                    <tr>
                                        <td>{{ $group->first()->supplier->name ?? '—' }}</td>
                                        <td>{{ $group->count() }}</td>
                                        <td>{{ number_format($group->sum(fn($i)=>$i->paid + $i->payments->sum('amount')),2) }}</td>
                                        <td>{{ number_format($group->sum('remaining'),2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- تقرير مفصل --}}
                    <div class="tab-pane fade" id="detailed">
                        <table id="detailedTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>رقم الفاتورة</th>
                                    <th>الإجمالي</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>تفاصيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    @php
                                        $totalPaid = $invoice->paid + ($invoice->payments->sum('amount') ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $invoice->supplier->name ?? '—' }}</td>
                                        <td>{{ $invoice->id }}</td>
                                        <td>{{ number_format($invoice->total,2) }}</td>
                                        <td>{{ number_format($totalPaid,2) }}</td>
                                        <td>{{ number_format($invoice->remaining,2) }}</td>
                                        <td>
                                            <a href="{{ route('dashboard.reports.purchases.invoice_details',$invoice->id) }}" class="btn btn-sm btn-info">عرض التفاصيل</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- حسب التصنيف --}}
                    <div class="tab-pane fade" id="byCategory">
                        <table id="categoryTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>المنتج</th>
                                    <th>التصنيف</th>
                                    <th>الكمية</th>
                                    <th>السعر</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    @foreach($invoice->items as $item)
                                        <tr>
                                            <td>{{ $invoice->supplier->name ?? '—' }}</td>
                                            <td>{{ $item->product->name ?? '—' }}</td>
                                            <td>{{ $item->product->category->name ?? '—' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->price,2) }}</td>
                                            <td>{{ number_format($item->subtotal,2) }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- الفواتير غير المسددة --}}
                    <div class="tab-pane fade" id="unpaid">
                        <table id="unpaidTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>رقم الفاتورة</th>
                                    <th>الإجمالي</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices->where('remaining','>',0) as $invoice)
                                    @php
                                        $totalPaid = $invoice->paid + ($invoice->payments->sum('amount') ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $invoice->supplier->name ?? '—' }}</td>
                                        <td>{{ $invoice->id }}</td>
                                        <td>{{ number_format($invoice->total,2) }}</td>
                                        <td>{{ number_format($totalPaid,2) }}</td>
                                        <td>{{ number_format($invoice->remaining,2) }}</td>
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
<!-- DataTables -->
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
    let tables = ['#summaryTable','#detailedTable','#categoryTable','#unpaidTable'];
    tables.forEach(function(id){
        $(id).DataTable({
            dom: 'Bfrtip',
            buttons: ['copy','excel','csv','pdf','print'],
            pageLength: 10,
            order: [],
            language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json" }
        });
    });
});
</script>
@endpush
