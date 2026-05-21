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
        <div class="alert alert-info">
            <strong>صافي المبلغ</strong> = قيمة الطلب بعد الخصم والمرتجعات.
            <strong>المدفوع</strong> = دفعات العميل + المدفوع عند البيع.
        </div>

        <div class="box box-primary">
            <div class="box-header">
                <form method="get" class="row">
                    <div class="col-md-3">
                        <input type="date" name="from" class="form-control" value="{{ request('from', $from) }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="to" class="form-control" value="{{ request('to', $to) }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">تصفية</button>
                    </div>
                    <div class="col-md-4 text-left">
                        <span class="label label-success">صافي: {{ number_format($snapshot['net_sales'], 2) }}</span>
                        <span class="label label-warning">مرتجع: {{ number_format($snapshot['returns_merchandise'], 2) }}</span>
                    </div>
                </form>
            </div>

            <div class="box-body table-responsive">
                <table id="ordersTable" class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>الحالة</th>
                            <th>صافي المبلغ</th>
                            <th>مرتجع</th>
                            <th>الخصم</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الربح</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            @php
                                $st = $fin->paymentStatus($order);
                                $paid = (float) $order->paid_at_sale + (float) $order->payments->sum('amount');
                            @endphp
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->client->name ?? '-' }}</td>
                                <td>
                                    <span class="label {{ $fin->paymentStatusClass($st) }}">
                                        {{ $fin->paymentStatusLabel($st) }}
                                    </span>
                                </td>
                                <td>{{ number_format($order->total_price, 2) }}</td>
                                <td>{{ $order->total_return > 0 ? number_format($order->total_return, 2) : '—' }}</td>
                                <td>{{ number_format($order->invoice_discount, 2) }}</td>
                                <td>{{ number_format($paid, 2) }}</td>
                                <td>{{ number_format($order->remaining, 2) }}</td>
                                <td>{{ number_format($order->profit ?? 0, 2) }}</td>
                                <td data-order="{{ $order->created_at->timestamp }}">{{ $order->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $orders->links() }}
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
<script>
$(function () {
    $('#ordersTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        order: [[9, 'desc']],
        pageLength: 50,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' }
    });
});
</script>
@endpush
