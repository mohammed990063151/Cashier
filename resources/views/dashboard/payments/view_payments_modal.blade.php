@php
    $fin = $summary ?? app(\App\Services\OrderFinancialService::class)->calculate($order);
    $methodLabels = [
        'cash' => 'كاش',
        'bank' => 'تحويل بنكي',
        'cash_at_sale' => 'دفع عند إنشاء الطلب',
    ];
@endphp

<div class="payment-log-modal">
    <h4 style="font-size:20px;font-weight:700;">
        سجل مدفوعات الطلب
        <span class="label label-primary" style="font-size:16px;">{{ $order->order_number }}</span>
    </h4>
    <p style="font-size:15px;"><i class="fa fa-user"></i> {{ $order->client->name }}</p>

    <div class="row" style="margin-bottom:15px;">
        <div class="col-sm-3 col-xs-6">
            <div class="log-stat-box">
                <small>إجمالي الطلب</small>
                <strong>{{ number_format($fin['totalSale'], 2) }}</strong>
            </div>
        </div>
        <div class="col-sm-3 col-xs-6">
            <div class="log-stat-box">
                <small>المدفوع</small>
                <strong class="text-success">{{ number_format($fin['totalPaid'], 2) }}</strong>
            </div>
        </div>
        <div class="col-sm-3 col-xs-6">
            <div class="log-stat-box">
                <small>المتبقي</small>
                <strong class="{{ $fin['remaining'] > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($fin['remaining'], 2) }}</strong>
            </div>
        </div>
        <div class="col-sm-3 col-xs-6">
            <div class="log-stat-box">
                <small>بعد الخصم</small>
                <strong>{{ number_format($fin['totalAfterDiscount'], 2) }}</strong>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped payment-log-table">
            <thead>
                <tr>
                    <th>المبلغ</th>
                    <th>الطريقة</th>
                    <th>إشعار بنكي</th>
                    <th>ملاحظات</th>
                    <th>التاريخ</th>
                    <th width="140">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->payments as $payment)
                <tr id="payment-row-{{ $payment->id }}">
                    <td><strong style="font-size:16px;">{{ number_format($payment->amount, 2) }}</strong></td>
                    <td>{{ $methodLabels[$payment->method] ?? $payment->method }}</td>
                    <td>
                        @if($payment->bank_receipt_url)
                            <a href="{{ $payment->bank_receipt_url }}" target="_blank">
                                <img src="{{ $payment->bank_receipt_url }}" class="receipt-thumb" alt="إشعار">
                            </a>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $payment->notes ?? '—' }}</td>
                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-nowrap">
                        @if($payment->method !== 'cash_at_sale')
                        <button type="button" class="btn btn-warning btn-sm edit-payment-btn"
                                data-payment-id="{{ $payment->id }}"
                                data-order-number="{{ $order->order_number }}"
                                data-amount="{{ $payment->amount }}"
                                data-method="{{ $payment->method }}"
                                data-notes="{{ $payment->notes }}"
                                data-receipt-url="{{ $payment->bank_receipt_url }}"
                                data-update-url="{{ route('dashboard.payment.update', $payment) }}"
                                data-max-amount="{{ $fin['remaining'] + $payment->amount }}">
                            <i class="fa fa-edit"></i> تعديل
                        </button>
                        <form method="POST" action="{{ route('dashboard.payment.destroy', $payment) }}" style="display:inline;"
                              onsubmit="return confirm('حذف هذه الدفعة وعكسها من الخزينة؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                        </form>
                        @else
                        <small class="text-muted">من الطلب</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">لا توجد دفعات مسجّلة</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-muted small">
        <i class="fa fa-info-circle"></i>
        أي تعديل أو حذف يُحدّث المتبقي على الطلب ورصيد الخزينة تلقائياً.
    </p>
</div>

<style>
.payment-log-modal .log-stat-box {
    background: #f4f6f9;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    margin-bottom: 8px;
}
.payment-log-modal .log-stat-box small { display: block; color: #7f8c8d; }
.payment-log-modal .log-stat-box strong { font-size: 18px; }
.payment-log-modal .receipt-thumb { max-height: 50px; border-radius: 4px; border: 1px solid #ddd; }
.payment-log-table { font-size: 14px; }
</style>
