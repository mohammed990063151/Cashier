@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>قائمة المبيعات
            <small style="color: green; font-size: x-large;">
                {{ $orders->total() }} طلب
            </small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">قائمة المبيعات</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title">قائمة الطلبات</h3>
                        <div class="row mb-3 mobile-filter-panel" style="margin-top:10px;">
                            <div class="col-md-3 col-xs-12" style="margin-bottom:8px;">
                                <label class="visible-xs">من تاريخ</label>
                                <input type="date" id="fromDate" class="form-control" placeholder="من تاريخ">
                            </div>
                            <div class="col-md-3 col-xs-12" style="margin-bottom:8px;">
                                <label class="visible-xs">إلى تاريخ</label>
                                <input type="date" id="toDate" class="form-control" placeholder="إلى تاريخ">
                            </div>
                            <div class="col-md-3 col-xs-12" style="margin-bottom:8px;">
                                <label class="visible-xs">بحث</label>
                                <input type="text" id="searchInput" class="form-control" placeholder="بحث باسم العميل">
                            </div>
                        </div>
                    </div>

                    <div class="box-body table-responsive mobile-card-table">
                        <table id="ordersTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th class="hidden-xs"></th>
                                    <th>رقم الطلب</th>
                                    <th>اسم العميل</th>
                                    <th>إجمالي الطلب</th>
                                    <th>خصومات الطلب</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>تاريخ الإنشاء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    @php
                                        $finance = $order->finance ?? [];
                                        $total = $finance['totalAfterDiscount'] ?? $order->total_after_discount ?? 0;
                                        $discount = $finance['invoiceDiscount'] ?? $order->invoice_discount ?? 0;
                                        $paid = $finance['netPaid'] ?? $finance['totalPaid'] ?? $order->paid_amount ?? 0;
                                        $remaining = $finance['remaining'] ?? $order->remaining_amount ?? 0;
                                    @endphp
                                    <tr data-order-id="{{ $order->id }}">
                                        <td class="details-control"></td>
                                        <td data-label="رقم الطلب"><strong>{{ $order->order_number }}</strong></td>
                                        <td data-label="العميل">{{ $order->client->name ?? '—' }} <x-debt-rate :rate="$order->usd_rate" :remaining="$remaining" /></td>
                                        <td data-label="الإجمالي" style="color: #01941f; font-weight: bold;">
                                            {{ number_format($total, 2) }}
                                        </td>
                                        <td data-label="الخصم" style="color: rgb(192, 152, 9); font-weight: bold;">
                                            {{ number_format($discount, 2) }}
                                        </td>
                                        <td data-label="المدفوع" style="color: #2980b9; font-weight: bold;">
                                            {{ number_format($paid, 2) }}
                                        </td>
                                        <td data-label="المتبقي" style="color: #e74c3c; font-weight: bold;">
                                            {{ number_format($remaining, 2) }}
                                        </td>
                                        <td data-label="التاريخ">{{ $order->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="products-pagination text-center">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

@php
    $saleInvoiceLines = $orders->getCollection()->load('products')->mapWithKeys(function ($order) {
        return [$order->id => [
            'products' => $order->products->map(function ($product) {
                $qty = (float) $product->pivot->quantity;
                $bulk = max(1, (int) ($product->pieces_per_carton ?? 1));
                $mode = \App\Support\SaleUnits::normalizeSaleMode($product->sale_mode);
                $measure = \App\Support\SaleUnits::normalizeMeasureUnit($product->measure_unit);
                $lineTotal = \App\Support\SaleUnits::lineMoney($product);
                $salePrice = (float) $product->pivot->sale_price;
                $costPrice = (float) $product->pivot->cost_price;
                if (
                    $measure !== \App\Support\SaleUnits::UNIT_KILO
                    && ($mode === \App\Support\SaleUnits::MODE_BULK_ONLY || $measure === \App\Support\SaleUnits::UNIT_CARTON)
                    && $bulk > 1
                ) {
                    $cartons = $qty / $bulk;
                    $salePrice = $cartons > 0 ? round($lineTotal / $cartons, 2) : round($salePrice * $bulk, 2);
                    $costPrice = round($costPrice * $bulk, 2);
                }

                return [
                    'name' => $product->name,
                    'qty_label' => \App\Support\SaleUnits::formatQuantityLabel($qty, $bulk, $mode, $measure),
                    'sale_price' => number_format($salePrice, 2, '.', ''),
                    'cost_price' => number_format($costPrice, 2, '.', ''),
                    'line_total' => number_format($lineTotal, 2, '.', ''),
                ];
            })->values(),
        ]];
    });
@endphp
<script>
$(document).ready(function () {

    var ordersData = @json($saleInvoiceLines);

    // دالة لإنشاء جدول المنتجات
    function format(order) {
        var html = '<table class="table table-sm table-bordered mb-0">';
        html += '<thead><tr><th>اسم المنتج</th><th>الكمية</th><th>سعر البيع</th><th>سعر الشراء</th><th>الإجمالي</th></tr></thead>';
        html += '<tbody>';
        (order.products || []).forEach(function (p) {
            html += '<tr>';
            html += '<td>' + p.name + '</td>';
            html += '<td>' + p.qty_label + '</td>';
            html += '<td>' + p.sale_price + '</td>';
            html += '<td>' + p.cost_price + '</td>';
            html += '<td>' + p.line_total + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    // DataTable الأساسي (سطح المكتب فقط — الهاتف يعتمد على البطاقات)
    var table = null;
    if (window.innerWidth > 767 && $.fn.DataTable) {
        try {
            table = $('#ordersTable').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'excel', 'csv', 'pdf', 'print'],
                order: [[1, 'desc']],
                pageLength: 25,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
                }
            });
        } catch (e) {
            table = null;
        }
    }

    $('#ordersTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var order = ordersData[tr.data('order-id')];
        if (!order) {
            return;
        }

        if (table) {
            var row = table.row(tr);
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(order)).show();
                tr.addClass('shown');
            }
            return;
        }

        var next = tr.next('tr.order-products-row');
        if (next.length) {
            next.remove();
            tr.removeClass('shown');
        } else {
            tr.addClass('shown');
            tr.after('<tr class="order-products-row"><td colspan="8">' + format(order) + '</td></tr>');
        }
    });

    // فلترة بالتاريخ واسم العميل
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var from = $('#fromDate').val();
        var to = $('#toDate').val();
        var search = $('#searchInput').val().toLowerCase();
        var name = data[2].toLowerCase();
        var dateText = data[7];
        var rowDate = new Date(dateText);

        var matchesSearch = name.includes(search);
        var matchesDate = true;

        if (from) matchesDate = rowDate >= new Date(from);
        if (to) matchesDate = matchesDate && rowDate <= new Date(to);

        return matchesSearch && matchesDate;
    });

    $('#searchInput, #fromDate, #toDate').on('input change', function () {
        if (table) {
            table.draw();
        }
    });

});
</script>

<style>
/* أيقونة التوسيع (+) */
td.details-control {
    background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
}
</style>
@endpush
