@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header mb-3 d-flex justify-content-between align-items-center flex-wrap">
        <h1>دفعات الطلب: #{{ $order->order_number }}</h1>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('dashboard.payments.index') }}" class="btn btn-default mb-1">
                <i class="fa fa-arrow-left"></i> العودة للمدفوعات
            </a>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $fin = $summary ?? app(\App\Services\OrderFinancialService::class)->calculate($order);
        $methodLabels = [
            'cash' => 'كاش',
            'bank' => 'تحويل بنكي',
            'cash_at_sale' => 'دفع عند إنشاء الطلب',
        ];
    @endphp

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">ملخص الطلب — {{ $order->client->name }}</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3"><strong>إجمالي الطلب:</strong> {{ number_format($fin['totalSale'], 2) }}</div>
                    <div class="col-sm-3"><strong>خصم الفاتورة:</strong> <span class="text-warning">{{ number_format($fin['invoiceDiscount'], 2) }}</span></div>
                    <div class="col-sm-3"><strong>بعد الخصم:</strong> {{ number_format($fin['totalAfterDiscount'], 2) }}</div>
                    <div class="col-sm-3"><strong>المتبقي:</strong>
                        <span class="{{ $fin['remaining'] > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($fin['remaining'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">تعديل دفعات الطلب</h3>
            </div>

            <div class="box-body">
                @if($order->payments->isEmpty())
                    <p class="text-muted text-center">لا توجد دفعات في السجل. المدفوع عند البيع: {{ number_format($fin['paidAtSale'], 2) }}</p>
                @else
                    @foreach($order->payments as $index => $payment)
                    <div class="panel panel-default" style="margin-bottom:15px;">
                        <div class="panel-heading"><strong>دفعة #{{ $index + 1 }}</strong> — {{ $payment->created_at->format('d-m-Y H:i') }}</div>
                        <div class="panel-body">
                            <form action="{{ route('dashboard.payment.update', $payment->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-3 form-group">
                                        <label>المبلغ</label>
                                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control" value="{{ old('amount', $payment->amount) }}" required>
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label>طريقة الدفع</label>
                                        @if($payment->method === 'cash_at_sale')
                                            <input type="text" class="form-control" readonly value="{{ $methodLabels['cash_at_sale'] }}">
                                        @else
                                            <select name="method" class="form-control payment-method-select" data-payment-id="{{ $payment->id }}" required>
                                                <option value="cash" {{ $payment->method === 'cash' ? 'selected' : '' }}>كاش</option>
                                                <option value="bank" {{ $payment->method === 'bank' ? 'selected' : '' }}>تحويل بنكي</option>
                                            </select>
                                        @endif
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>ملاحظات</label>
                                        <input type="text" name="notes" class="form-control" value="{{ old('notes', $payment->notes) }}" placeholder="ملاحظات">
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fa fa-save"></i> حفظ
                                        </button>
                                    </div>
                                </div>

                                @if($payment->method !== 'cash_at_sale')
                                <div class="row bank-receipt-row-{{ $payment->id }}" style="{{ $payment->method === 'bank' ? '' : 'display:none;' }}">
                                    <div class="col-md-6 form-group">
                                        <label>صورة إشعار التحويل البنكي @if(!$payment->bank_receipt)<span class="text-danger">*</span>@endif</label>
                                        <input type="file" name="bank_receipt" class="form-control" accept="image/jpeg,image/png,image/webp"
                                               {{ $payment->method === 'bank' && !$payment->bank_receipt ? 'required' : '' }}>
                                        <small class="text-muted">للاستبدال ارفع صورة جديدة</small>
                                    </div>
                                    @if($payment->bank_receipt_url)
                                    <div class="col-md-6">
                                        <label>الإشعار الحالي</label><br>
                                        <a href="{{ $payment->bank_receipt_url }}" target="_blank" rel="noopener">
                                            <img src="{{ $payment->bank_receipt_url }}" alt="إشعار بنكي" style="max-height:120px;border:1px solid #ddd;border-radius:4px;">
                                        </a>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </form>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('.payment-method-select').on('change', function() {
        var id = $(this).data('payment-id');
        var isBank = $(this).val() === 'bank';
        $('.bank-receipt-row-' + id).toggle(isBank);
        $('.bank-receipt-row-' + id + ' input[type=file]').prop('required', isBank && !$('.bank-receipt-row-' + id + ' img').length);
    });
});
</script>
@endpush
