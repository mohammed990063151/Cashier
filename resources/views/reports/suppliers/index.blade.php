@extends('layouts.dashboard.app')
@section('title', 'تقارير الموردين')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>📊 تقارير الموردين</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">عرض تقارير الموردين</h3>
            </div>

            <div class="box-body">
                {{-- Tabs --}}
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#balances" data-toggle="tab">💰 أرصدة الموردين</a></li>
                    <li><a href="#invoices" data-toggle="tab">📑 فواتير الموردين</a></li>
                    <li><a href="#products" data-toggle="tab">🛒 المنتجات المشتراه</a></li>
                    <li><a href="#payments" data-toggle="tab">📜 كشف الحساب</a></li>
                </ul>

                <div class="tab-content" style="margin-top:20px;">
                    {{-- Tab 1: الأرصدة --}}
                    <div class="tab-pane fade in active" id="balances">
                        <table id="balancesTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>الرصيد</th>
                                    <th>الهاتف</th>
                                    <th>العنوان</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suppliers as $supplier)
                                    <tr>
                                        <td>{{ $supplier->name }}</td>
                                        <td>{{ number_format($supplier->balance, 2) }}</td>
                                        <td>{{ $supplier->phone }}</td>
                                        <td>{{ $supplier->address }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Tab 2: الفواتير --}}
                    <div class="tab-pane fade" id="invoices">
                        <table id="invoicesTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>رقم الفاتورة</th>
                                    <th>الإجمالي</th>
                                    <th>المدفوع في الفاتورة</th>
                                    <th>المتبقي</th>
                                    <th>عرض</th>
                                </tr>
                            </thead>
                            <tbody>
                                         @foreach($suppliers as $supplier)
                @foreach($supplier->purchaseInvoices as $invoice)
                   @php
    $totalPaid = $invoice->paid + ($invoice->payments?->sum('amount') ?? 0);
@endphp

                    <tr>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $invoice->invoice_number  }}</td>
                        <td>{{ number_format($invoice->total, 2) }}</td>
                        <td>{{ number_format($totalPaid, 2) }}</td>
                        <td>{{ number_format($invoice->total - $totalPaid, 2) }}</td>
                        <td>
                            <a href="{{ route('dashboard.reports.suppliers.invoice_details', $invoice->id) }}" class="btn btn-sm btn-info">عرض التفاصيل</a>
                        </td>
                    </tr>
                @endforeach
            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Tab 3: المنتجات --}}
                    <div class="tab-pane fade" id="products">
                        <table id="productsTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>رقم الفاتورة</th>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>السعر</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suppliers as $supplier)
                                    @foreach($supplier->purchaseInvoices as $invoice)
                                        @foreach($invoice->items as $item)
                                            <tr>
                                                <td>{{ $supplier->name }}</td>
                                                <td>{{ $invoice->id }}</td>
                                                <td>{{ $item->product->name ?? '—' }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ number_format($item->price, 2) }}</td>
                                                <td>{{ number_format($item->subtotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Tab 4: المدفوعات --}}
                    <div class="tab-pane fade" id="payments">
                        <table id="paymentsTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>الدفعيات</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($suppliers as $supplier)
                                    @foreach($supplier->payments as $payment)
                                        <tr>
                                            <td>{{ $supplier->name }}</td>
                                            <td>{{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> {{-- box-body --}}
        </div>
    </section>
</div>
@endsection

@push('scripts')
<!-- DataTables CSS/JS -->
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
    // IDs of tables
    let tables = ['#balancesTable','#invoicesTable','#productsTable','#paymentsTable'];
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
