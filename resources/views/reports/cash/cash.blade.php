@extends('layouts.dashboard.app')
@section('title','تقرير حركة الخزينة')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>تقرير حركة الخزينة</h1>
    </section>

    <section class="content">
        <div class="row" style="margin-bottom:12px;">
            <div class="col-md-3">
                <div class="alert alert-success text-center" style="margin:0;">
                    <small>وارد (الفترة)</small><br>
                    <strong>{{ number_format($totalAdded, 2) }} ج.س</strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-danger text-center" style="margin:0;">
                    <small>صادر (الفترة)</small><br>
                    <strong>{{ number_format($totalDeducted, 2) }} ج.س</strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-info text-center" style="margin:0;">
                    <small>صافي الحركات (الفترة)</small><br>
                    <strong>{{ number_format($netFiltered, 2) }} ج.س</strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-primary text-center" style="margin:0;">
                    <small>رصيد الصندوق الفعلي</small><br>
                    <strong>{{ number_format($cashBalance, 2) }} ج.س</strong>
                </div>
            </div>
        </div>

        @if($totalReturnsOut > 0)
        <div class="alert alert-warning">
            <strong>مرتجعات الطلبات (سحب من الخزينة في الفترة):</strong>
            {{ number_format($totalReturnsOut, 2) }} ج.س
        </div>
        @endif

        <div class="box box-primary">
            <div class="box-body">
                <form method="GET" class="form-inline mb-3">
                    <label>من:</label>
                    <input type="date" name="from" value="{{ request('from', $from) }}" class="form-control">
                    <label style="margin:0 8px;">إلى:</label>
                    <input type="date" name="to" value="{{ request('to', $to) }}" class="form-control">
                    <button type="submit" class="btn btn-primary" style="margin-right:8px;">تصفية</button>
                </form>

                <table class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تاريخ الحركة</th>
                            <th>الوصف</th>
                            <th>الطلب / المرجع</th>
                            <th class="text-success">إضافة</th>
                            <th class="text-danger">سحب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $index => $t)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($t->transaction_date)->format('Y-m-d') }}</td>
                            <td>{{ $t->description ?? '—' }}</td>
                            <td>
                                @if($t->order)
                                    {{ $t->order->order_number }}
                                @elseif($t->orderReturn)
                                    {{ $t->orderReturn->return_number }}
                                    <br><small>{{ $t->orderReturn->order->order_number ?? '' }}</small>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-success">
                                {{ $t->type === 'add' ? number_format($t->amount, 2) : '—' }}
                            </td>
                            <td class="text-danger">
                                {{ $t->type === 'deduct' ? number_format($t->amount, 2) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right">إجمالي الوارد (الفترة):</th>
                            <th class="text-success">{{ number_format($totalAdded, 2) }}</th>
                            <th>—</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-right">إجمالي الصادر (الفترة):</th>
                            <th>—</th>
                            <th class="text-danger">{{ number_format($totalDeducted, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-right">صافي الحركات (الفترة):</th>
                            <th colspan="2">{{ number_format($netFiltered, 2) }} ج.س</th>
                        </tr>
                        <tr style="background:#e8f5e9;">
                            <th colspan="4" class="text-right">رصيد الصندوق الفعلي (كل الحركات):</th>
                            <th colspan="2"><strong>{{ number_format($cashBalance, 2) }} ج.س</strong></th>
                        </tr>
                    </tfoot>
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
<script>
$(function () {
    $('.datatable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' },
        pageLength: 25,
        order: [[1, 'desc']]
    });
});
</script>
@endpush
