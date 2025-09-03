@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header mb-3 d-flex justify-content-between align-items-center">
        <h1>تعديل دفعة المورد: {{ $payment->supplier->name }}</h1>
        <div>
            <a href="{{ route('dashboard.suppliers.index') }}" class="btn btn-default me-2">
                <i class="fa fa-arrow-left"></i> العودة للقائمة
            </a>
            <a href="{{ route('dashboard.suppliers.payments', $payment->supplier->id) }}" class="btn btn-primary">
                <i class="fa fa-list"></i> عرض دفعات المورد
            </a>
        </div>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">تعديل دفعة المورد: {{ $payment->supplier->name }}</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('dashboard.dashboard.supplier-payments.update', $payment->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- معرف المورد مخفي -->
                    <input type="hidden" name="supplier_id" value="{{ $payment->supplier->id }}">

                    <div class="form-group">
                        <label>المبلغ</label>
                        <input type="number" step="0.01" name="amount" class="form-control" 
                               value="{{ old('amount', $payment->amount) }}" required>
                        @error('amount')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>اختر الفاتورة (اختياري)</label>
                        <select name="purchase_invoice_id" class="form-control">
                            <option value="">-- لا شيء --</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}"
                                    {{ $payment->purchase_invoice_id == $invoice->id ? 'selected' : '' }}>
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
                        <input type="date" name="payment_date" class="form-control" 
                               value="{{ old('payment_date', $payment->payment_date) }}" required>
                        @error('payment_date')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea name="note" class="form-control">{{ old('note', $payment->note) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> حفظ التعديلات
                        </button>
                        <a href="{{ route('dashboard.suppliers.payments', $payment->supplier->id) }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> رجوع
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </section>

</div>
@endsection
