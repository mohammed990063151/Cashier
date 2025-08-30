@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>المدفوعات</h1>
    </section>

    <section class="content">
        <div class="box box-primary">

            <!-- Header مع البحث والعنوان -->
            <div class="box-header d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h3 class="box-title mb-2 mb-md-0">قائمة الطلبات</h3>
                <form action="{{ route('dashboard.payments.index') }}" method="GET" class="form-inline mb-2 mb-md-0">
                    <div class="form-group mr-2">
                        <input type="text" name="search" id="searchInput" class="form-control" placeholder="بحث باسم العميل أو رقم الطلب" value="{{ request()->search }}">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                </form>
            </div>

            <!-- جدول الطلبات -->
            <div class="box-body table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>رقم الطلب</th>
                            <th>اسم العميل</th>
                            <th>إجمالي الطلب</th>
                            <th>الدفع عند الشراء</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->client->name }}</td>
                            <td style="color: green;">{{ number_format($order->total_price,2) }}</td>
                            <td style="color: rgb(163, 153, 6);">{{ number_format($order->discount,2) }}</td>
                            <td style="color: rgb(86, 157, 23);">{{ number_format($order->payments->sum('amount'),2) }}</td>
                            <td style="color: red;">{{ number_format($order->remaining,2) }}</td>
                            <td class="d-flex flex-wrap">
                                <button class="btn btn-success btn-sm mr-1 mb-1 add-payment-btn" data-toggle="modal" data-target="#paymentModal" data-order-id="{{ $order->id }}">
                                    إضافة دفعة
                                </button>
                                <button class="btn btn-warning btn-sm edit-payment-btn" data-order-id="{{ $order->id }}" data-toggle="modal" data-target="#editPaymentModal">
                                    تعديل الدفعات
                                </button>


                                <button class="btn btn-info btn-sm mb-1 view-payments-btn" data-toggle="modal" data-target="#viewPaymentsModal" data-order-id="{{ $order->id }}">
                                    عرض المدفوعات
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- مودال إضافة الدفعة -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="paymentForm" method="POST" action="{{ route('dashboard.payments.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إضافة دفعة</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="paymentOrderId">
                    <div class="form-group">
                        <label>المبلغ</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>طريقة الدفع</label>
                        <select name="method" class="form-control">
                            <option value="cash">كاش</option>
                            <option value="bank">تحويل بنكي</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea name="notes" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">حفظ الدفعة</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال عرض المدفوعات -->
<div class="modal fade" id="viewPaymentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">المدفوعات</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="viewPaymentsContent">
                <p class="text-center">جارٍ تحميل البيانات...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="editPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل دفعات الطلب</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="editPaymentsContent">
                <p class="text-center">جارٍ تحميل الدفعات...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        // ضبط زر إضافة الدفعة
        $('.add-payment-btn').on('click', function() {
            var orderId = $(this).data('order-id');
            $('#paymentOrderId').val(orderId);
        });

        // ضبط زر عرض المدفوعات
        $('.view-payments-btn').on('click', function() {
            var orderId = $(this).data('order-id');
            var url = '/dashboard/orders/' + orderId + '/payments';

            $('#viewPaymentsContent').html('<p class="text-center">جارٍ تحميل البيانات...</p>');

            $.ajax({
                url: url
                , type: 'GET'
                , success: function(response) {
                    $('#viewPaymentsContent').html(response);
                }
                , error: function() {
                    $('#viewPaymentsContent').html('<p class="text-danger text-center">حدث خطأ أثناء تحميل البيانات</p>');
                }
            });
        });

        // بحث مباشر في الجدول (JS)
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

    });

</script>
<script>
$('.edit-payment-btn').on('click', function() {
    var orderId = $(this).data('order-id');
    var url = '/dashboard/orders/' + orderId + '/payments/edit';

    $('#editPaymentsContent').html('<p class="text-center">جارٍ تحميل الدفعات...</p>');

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            $('#editPaymentsContent').html(response);
        },
        error: function(xhr) {
            console.log(xhr); // <-- لعرض سبب الخطأ في الكونسول
            $('#editPaymentsContent').html('<p class="text-danger text-center">حدث خطأ أثناء تحميل البيانات</p>');
        }
    });
});




</script>
<style>
    /* تحسين العرض على الشاشات الصغيرة */
    @media (max-width: 576px) {

        .table td,
        .table th {
            font-size: 12px;
            padding: 4px 6px;
        }

        .btn {
            font-size: 12px;
            padding: 4px 6px;
        }

        .box-header form {
            width: 100%;
        }

        .box-header form .form-group {
            width: 70%;
        }

        .box-header form button {
            width: 28%;
        }
    }

</style>
@endpush
