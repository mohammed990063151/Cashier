@extends('layouts.dashboard.app')
@section('title','تفاصيل الفاتورة')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>تفاصيل فاتورة #{{ $invoice->id }}</h1>
        <small>المورد: {{ $invoice->supplier->name ?? '—' }}</small>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">

                {{-- بيانات الفاتورة --}}
                <h4>📋 معلومات الفاتورة</h4>
                <table class="table table-bordered text-center">
                    <tr>
                        <th>إجمالي الفاتورة</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>تاريخ الفاتورة</th>
                    </tr>
                    <tr>
                        <td>{{ number_format($invoice->total, 2) }}</td>
                        <td>{{ number_format($invoice->paid, 2) }}</td>
                        <td>{{ number_format($invoice->remaining, 2) }}</td>
                        <td>{{ optional($invoice->invoice_date)->format('Y-m-d') ?? $invoice->created_at->format('Y-m-d') }}</td>
                    </tr>
                </table>

                {{-- المنتجات --}}
                <h4 class="mt-4">🛒 المنتجات المشتراه</h4>
                <table id="itemsTable" class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->product->name ?? '—' }}</td>
                            <td>{{ ($item->purchase_unit_label ?? 'حبة').' × '.($item->entered_qty ?? $item->quantity) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price, 2) }}</td>
                            <td>{{ number_format($item->subtotal,2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- المدفوعات --}}
                <h4 class="mt-4">💰 المدفوعات</h4>
                <table id="paymentsTable" class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المبلغ</th>
                            <th>تاريخ الدفع</th>
                            <th>ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ number_format($payment->amount,2) }}</td>
                            <td>{{ $payment->payment_date ?? $payment->created_at->format('Y-m-d') }}</td>
                            <td>{{ $payment->note ?? '—' }}</td>
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
    ['#itemsTable','#paymentsTable'].forEach(function(id){
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
