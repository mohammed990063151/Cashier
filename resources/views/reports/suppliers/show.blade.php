@extends('layouts.dashboard.app')
@section('title', 'تقرير المورد - ' . $supplier->name)

@section('content')
<div class="container">
    <h2 class="mb-4">📊 تقرير المورد: {{ $supplier->name }}</h2>

    {{-- ✅ تبويبات --}}
    <ul class="nav nav-tabs" id="supplierTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="invoices-tab" data-toggle="tab" href="#invoices" role="tab">🧾 الفواتير</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="products-tab" data-toggle="tab" href="#products" role="tab">📦 المنتجات المشتراه</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="payments-tab" data-toggle="tab" href="#payments" role="tab">💰 كشف الحساب</a>
        </li>
    </ul>

    <div class="tab-content mt-3" id="supplierTabsContent">

        {{-- ✅ الفواتير --}}
        <div class="tab-pane fade show active" id="invoices" role="tabpanel">
            <table class="table table-bordered" id="invoicesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>إجمالي الفاتورة</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->id }}</td>
                            <td>{{ number_format($invoice->total, 2) }}</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format($invoice->remaining, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ✅ المنتجات --}}
        <div class="tab-pane fade" id="products" role="tabpanel">
            <table class="table table-bordered" id="productsTable">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>إجمالي الشراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product['product_name'] }}</td>
                            <td>{{ $product['quantity'] }}</td>
                            <td>{{ number_format($product['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ✅ كشف الحساب --}}
        <div class="tab-pane fade" id="payments" role="tabpanel">
            <table class="table table-bordered" id="paymentsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المبلغ</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#invoicesTable').DataTable();
    $('#productsTable').DataTable();
    $('#paymentsTable').DataTable();
});
</script>
@endsection
