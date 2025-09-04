@extends('layouts.dashboard.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<style>
    .badge-payment {
        display: inline-block;
        margin: 2px;
        padding: 3px 6px;
        background: #17a2b8;
        color: #fff;
        border-radius: 4px;
        font-size: 12px;
    }

</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header mb-3 d-flex justify-content-between align-items-center">
        <h1>دفعات المورد: {{ $supplier->name }} ->المتبقي له{{ $supplier->balance }}</h1>
        <div>
            <a href="{{ route('dashboard.suppliers.index') }}" class="btn btn-default me-2">
                <i class="fa fa-arrow-left"></i> العودة للقائمة
            </a>
            <a href="{{ route('dashboard.supplier-payments.create', $supplier->id) }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> إضافة دفعة جديدة
            </a>
        </div>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                @if($payments->count() > 0)
                <div class="table-responsive">
                    <table id="paymentsTable" class="table table-bordered table-hover text-center align-middle">
                        <thead>
                            <tr class="bg-primary text-white">
                                <th>رقم الفاتورة</th>
                                <th> المتبقي</th>
                                <th>إجمالي المدفوع</th>
                                <th>الدفعات</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $invoiceId => $groupedPayments)
                            <tr>
                                <td>
                                    @if($invoiceId !== 'no_invoice')
                                    <span class="badge bg-info text-dark">
                                        {{ $groupedPayments->first()->purchase_invoice->invoice_number }}
                                    </span>

                                    @else
                                    --
                                    @endif
                                </td>
                                <td>
                                    @if($invoiceId !== 'no_invoice')
                                    {{ number_format($groupedPayments->first()->purchase_invoice->remaining, 2) }}
                                    @else
                                    --
                                    @endif
                                </td>
                                <td>
                                    {{ number_format($groupedPayments->sum('amount'), 2) }}
                                </td>
                                <td>
                                    @foreach($groupedPayments as $payment)
                                    <span class="badge-payment" title="{{ $payment->payment_date }}">{{ number_format($payment->amount, 2) }}</span><br><br>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($groupedPayments as $payment)
                                    <a href="{{ route('dashboard.suppliers.payments.edit', $payment->id) }}" class="btn btn-warning btn-sm mb-1">
                                        <i class="fa fa-pencil"></i> تعديل
                                    </a><br><br>
                                    @endforeach
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center">لا توجد دفعات مسجلة لهذا المورد.</p>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#paymentsTable').DataTable({
            dom: 'Bfrtip'
            , buttons: ['print', 'excelHtml5']
            , order: [
                [0, 'asc']
            ]
            , language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
            }
        });
    });

</script>
@endpush
