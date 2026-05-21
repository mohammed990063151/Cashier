@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper purchase-invoice-page">
    <section class="content-header">
        <h1>فاتورة شراء {{ $purchaseInvoice->invoice_number }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
            <li><a href="{{ route('dashboard.purchase-invoices.index') }}">فواتير الشراء</a></li>
            <li class="active">عرض</li>
        </ol>
    </section>

    <section class="content">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-body">
                        <p><strong>المورد:</strong> {{ $purchaseInvoice->supplier->name ?? '—' }}</p>
                        <p><strong>تاريخ الفاتورة:</strong> {{ optional($purchaseInvoice->invoice_date)->format('d/m/Y') ?? '—' }}</p>
                        <p><strong>إجمالي:</strong> {{ number_format($purchaseInvoice->total, 2) }} ج.س</p>
                        <p><strong>مدفوع:</strong> <span class="text-success">{{ number_format($purchaseInvoice->paid, 2) }}</span></p>
                        <p><strong>متبقي:</strong> <span class="text-danger">{{ number_format($purchaseInvoice->remaining, 2) }}</span></p>
                        @if($purchaseInvoice->payment_notes)
                            <p><strong>ملاحظات:</strong> {{ $purchaseInvoice->payment_notes }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                @if($purchaseInvoice->remaining > 0 && $purchaseInvoice->paymentInstallments->whereNull('paid_at')->count())
                <div class="box box-warning">
                    <div class="box-header"><h3 class="box-title">أقساط السداد</h3></div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <thead><tr><th>المبلغ</th><th>الاستحقاق</th><th>الحالة</th></tr></thead>
                            <tbody>
                                @foreach($purchaseInvoice->paymentInstallments as $inst)
                                <tr>
                                    <td>{{ number_format($inst->amount, 2) }}</td>
                                    <td>{{ $inst->due_at->format('d/m/Y') }}</td>
                                    <td>{{ $inst->paid_at ? 'مسدد '.$inst->paid_at->format('d/m/Y') : 'مستحق' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <a href="{{ route('dashboard.supplier-schedules.index', ['supplier_id' => $purchaseInvoice->supplier_id]) }}" class="btn btn-primary btn-sm">إدارة الجدولة</a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header"><h3 class="box-title">بنود الشراء</h3></div>
            <div class="box-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المنتج</th>
                            <th>الشراء</th>
                            <th>في المخزون (حبات)</th>
                            <th>سعر الوحدة</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseInvoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product->name ?? '—' }}</td>
                            <td>{{ $purchaseInvoice->quantityLabelForItem($item) }}</td>
                            <td>{{ $item->quantity }} حبة</td>
                            <td>{{ number_format($item->price, 2) }}</td>
                            <td>{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('dashboard.purchase-invoices.index') }}" class="btn btn-default"><i class="fa fa-arrow-right"></i> القائمة</a>
        <a href="{{ route('dashboard.purchase-invoices.edit', $purchaseInvoice) }}" class="btn btn-warning"><i class="fa fa-edit"></i> تعديل</a>
        <a href="{{ route('dashboard.purchase-invoices.print', $purchaseInvoice) }}" class="btn btn-info" target="_blank"><i class="fa fa-print"></i> طباعة</a>
    </section>
</div>
@endsection
