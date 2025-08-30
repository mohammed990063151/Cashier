@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>فاتورة الشراء رقم #{{ $purchaseInvoice->id }}</h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li><a href="{{ route('dashboard.purchase-invoices.index') }}">فواتير الشراء</a></li>
            <li class="active">عرض الفاتورة</li>
        </ol>
    </section>

    <section class="content">

        {{-- بيانات المورد --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">بيانات المورد</h3>
            </div>
            <div class="box-body">
                <p><strong>اسم المورد:</strong> {{ $purchaseInvoice->supplier->name ?? 'غير معروف' }}</p>
                <p><strong>تاريخ الإنشاء:</strong> {{ $purchaseInvoice->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        {{-- عناصر الفاتورة --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">عناصر الفاتورة</h3>
            </div>
            <div class="box-body">

                @if($purchaseInvoice->items && $purchaseInvoice->items->count())
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>السعر</th>
                                <th>الإجمالي (Subtotal)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseInvoice->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->product->name ?? 'غير معروف' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->price, 2) }}</td>
                                    <td>{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>لا توجد عناصر في هذه الفاتورة.</p>
                @endif

            </div>
        </div>

        {{-- الإجمالي --}}
        <div class="box box-footer text-right">
            <h4><strong>الإجمالي الكلي:</strong> {{ number_format($purchaseInvoice->total, 2) }}</h4>
        </div>

        {{-- زر الرجوع --}}
        <a href="{{ route('dashboard.purchase-invoices.index') }}" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> العودة إلى القائمة
        </a>

    </section>

</div><!-- /.content-wrapper -->

@endsection
