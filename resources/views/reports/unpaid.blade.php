@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>الفواتير غير المسددة</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">الفواتير غير المسددة</li>
        </ol>
    </section>

    <section class="content">
        @include('reports._debt_status')
        <div class="alert alert-warning text-center">
            <strong>إجمالي الذمم المستحقة على العملاء:</strong>
            <x-report-money :amount="$totalRemaining" :entries="$unpaidOrders->map(fn ($order) => ['amount' => $order->remaining, 'rate' => $order->usd_rate])->all()" />
            — عدد الطلبات: {{ $unpaidOrders->count() }}
            <div>الديون المعدومة لا تظهر في هذه القائمة، وتظهر في شريط الهوالك أعلى الصفحة وفي صفحة الديون المعدومة.</div>
        </div>

        <div class="box box-primary">
            <div class="box-body table-responsive">
                <table id="unpaidOrdersTable" class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>صافي الفاتورة</th>
                            <th>الخصم</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>تاريخ الطلب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unpaidOrders as $order)
                            @php $paid = (float) $order->paid_at_sale + (float) $order->payments->sum('amount'); @endphp
                            <tr>
                                <td>
                                    {{ $order->order_number }}
                                    @if($order->is_opening_balance)<div><span class="label label-warning">حساب قديم</span></div>@endif
                                </td>
                                <td>{{ $order->client->name ?? '-' }} <x-debt-rate :rate="$order->usd_rate" :remaining="$order->remaining" /></td>
                                <td class="text-success"><x-report-money :amount="$order->total_price" :rate="$order->usd_rate" /></td>
                                <td><x-report-money :amount="$order->invoice_discount" :rate="$order->usd_rate" /></td>
                                <td><x-report-money :amount="$paid" :rate="$order->usd_rate" /></td>
                                <td class="text-danger"><x-report-money :amount="$order->remaining" :rate="$order->usd_rate" /></td>
                                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:bold;background:#f5f5f5;">
                            <td colspan="5" class="text-right">الإجمالي المتبقي:</td>
                            <td class="text-danger"><x-report-money :amount="$totalRemaining" :entries="$unpaidOrders->map(fn ($order) => ['amount' => $order->remaining, 'rate' => $order->usd_rate])->all()" /></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                @if($unpaidOrders->isEmpty())
                    <p class="text-center text-muted">لا توجد فواتير غير مسددة حالياً</p>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(function () {
    $('#unpaidOrdersTable').DataTable({
        order: [[5, 'desc']],
        pageLength: 50,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' }
    });
});
</script>
@endpush
