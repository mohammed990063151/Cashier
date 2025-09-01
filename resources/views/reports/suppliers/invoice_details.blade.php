@extends('layouts.dashboard.app')
@section('title', 'تفاصيل الفاتورة')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>📄 تفاصيل الفاتورة رقم #{{ $invoice->id }}</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body table-responsive">
                <p><strong>المورد:</strong> {{ $invoice->supplier->name }}</p>
                <p><strong>إجمالي الفاتورة:</strong> {{ number_format($invoice->total,2) }}</p>
                <p><strong>المدفوع:</strong> {{ number_format($totalPaid,2) }}</p>
                <p><strong>المتبقي:</strong> {{ number_format($invoice->total - $totalPaid,2) }}</p>
                
                <h4>🛒 المنتجات</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? '—' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price,2) }}</td>
                            <td>{{ number_format($item->subtotal,2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <h4>💳 المدفوعات</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>المبلغ</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ number_format($payment->amount,2) }}</td>
                            <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <a href="{{ route('dashboard.reports.suppliers.index') }}" class="btn btn-default">رجوع للتقارير</a>
            </div>
        </div>
    </section>
</div>
@endsection
