@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header mb-3 d-flex justify-content-between align-items-center">
        <h1>إضافة دفعة لمورد: {{ $supplier->name }}</h1>
        <a href="{{ route('dashboard.suppliers.index') }}" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> العودة للقائمة
        </a>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">إضافة دفعة جديدة</h3>
            </div>
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif


            <div class="box-body">
                <form action="{{ route('dashboard.supplier-payments.store', $supplier->id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>المبلغ</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                        <small>رصيد المورد الحالي: {{ number_format($supplier->balance, 2) }}</small>
                        @error('amount')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>اختر الفاتورة (اختياري)</label>
                        <select name="purchase_invoice_id" class="form-control">
                            <option value="">-- لا شيء --</option>
                            @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                {{ $invoice->invoice_number }} - المبلغ المتبقي: {{ $invoice->remaining }}
                            </option>
                            @endforeach
                        </select>
                        @error('purchase_invoice_id')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>تاريخ الدفع</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        @error('payment_date')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea name="note" class="form-control">{{ old('note') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> حفظ الدفعة
                        </button>
                        <a href="{{ route('dashboard.suppliers.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> رجوع
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </section>

</div>
@endsection
