<table class="table table-bordered table-striped">
    <thead class="thead-light">
        <tr>
            <th>#</th>
            <th>المبلغ</th>
            <th>طريقة الدفع</th>
            <th>ملاحظات</th>
            <th>تاريخ الدفع</th>
            <th>عمليات</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->payments as $index => $payment)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                <input type="number" form="form-{{ $payment->id }}" name="amount" step="0.01" value="{{ $payment->amount }}" class="form-control" style="width:120px;">
            </td>
            <td>
                <select form="form-{{ $payment->id }}" name="method" class="form-control" style="width:150px;">
                    <option value="cash" {{ $payment->method=='cash' ? 'selected' : '' }}>كاش</option>
                    <option value="bank" {{ $payment->method=='bank' ? 'selected' : '' }}>تحويل بنكي</option>
                </select>
            </td>
            <td>
                <input type="text" form="form-{{ $payment->id }}" name="notes" value="{{ $payment->notes }}" class="form-control">
            </td>
            <td>{{ $payment->created_at->format('d-m-Y') }}</td>
            <td>
                <form id="form-{{ $payment->id }}" class="update-payment-form" data-id="{{ $payment->id }}">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success btn-sm">حفظ</button>
                </form>
            </td>

        </tr>
        @endforeach
    </tbody>
</table>
<script>
$(document).ready(function() {
    $('.update-payment-form').on('submit', function(e) {
        e.preventDefault(); // منع إعادة تحميل الصفحة
        var formId = $(this).data('id');
        var form = $(this);
        var url = '/dashboard/payments/' + formId;
        var data = $('#form-' + formId).serialize();

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(response) {
                // عرض رسالة نجاح داخل المودال أو أي div مخصص
                alert('تم تعديل الدفعة بنجاح'); // يمكن استبدال alert بـ toast جميل
            },
            error: function(xhr) {
                alert('حدث خطأ أثناء تعديل الدفعة');
            }
        });
    });
});
</script>
