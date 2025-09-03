@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header mb-3 d-flex justify-content-between align-items-center">
        <h1>دفعات المورد: {{ $supplier->name }}</h1>
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
                @if($invoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead>
                                <tr class="bg-primary text-white">
                                    <th>تاريخ الدفع</th>
                                    <th>المبلغ</th>
                                    <th>الملاحظة</th>
                                    <th>رقم الفاتورة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date }}</td>
                                        <td>{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->note ?? '--' }}</td>
                                        <td>
                                            @if($payment->purchase_invoice)
                                                <span class="badge bg-info text-dark">{{ $payment->purchase_invoice->invoice_number }}</span>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('dashboard.suppliers.payments.edit', $payment->id) }}" 
                                               class="btn btn-warning btn-sm">
                                                <i class="fa fa-pencil"></i> تعديل
                                            </a>
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
