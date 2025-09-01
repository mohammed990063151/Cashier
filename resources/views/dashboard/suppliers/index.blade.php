@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>الموردين
            <small>{{ $suppliers->total() }} مورد</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li class="active">الموردين</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title" style="margin-bottom: 10px">الموردين</h3>
                        @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif

                        <form action="{{ route('dashboard.suppliers.index') }}" method="get">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                                    <a href="{{ route('dashboard.suppliers.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> إضافة مورد جديد</a>
                                </div>
                            </div>
                        </form>

                    </div><!-- end of box header -->

                    @if ($suppliers->count() > 0)

                    <div class="box-body table-responsive">

                        <table class="table table-hover">
                            <tr>
                                <th>الاسم</th>
                                <th>الهاتف</th>
                                <th>العنوان</th>
                                <th>الرصيد</th>
                                <th>الإجراءات</th>
                            </tr>

                            @foreach ($suppliers as $supplier)
                            <tr>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ $supplier->address }}</td>
                                <td>{{ number_format($supplier->balance, 2) }}</td>
                                <td>
                                    <a href="{{ route('dashboard.suppliers.edit', $supplier->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i> تعديل</a>
                                    <!-- زر فتح مودال إضافة دفعة -->
                                    <button class="btn btn-success btn-sm add-supplier-payment-btn" data-toggle="modal" data-target="#supplierPaymentModal" data-supplier-id="{{ $supplier->id }}" data-remaining="{{ $supplier->balance }}">
                                        إضافة دفعة
                                    </button>





                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#paymentsModal{{ $supplier->id }}">
                                        💰 الدفعات
                                    </button>

                                    <form action="{{ route('dashboard.suppliers.destroy', $supplier->id) }}" method="post" style="display: inline-block;">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-danger btn-sm delete"><i class="fa fa-trash"></i> حذف</button>
                                    </form>
                                </td>
                            </tr>


                            @endforeach

                        </table>

                        {{ $suppliers->appends(request()->query())->links() }}

                    </div>

                    @else

                    <div class="box-body">
                        <h3>لا توجد سجلات</h3>
                    </div>

                    @endif

                </div><!-- end of box -->

            </div><!-- end of col -->

        </div><!-- end of row -->

    </section><!-- end of content section -->

</div><!-- end of content wrapper -->
<!-- مودال الدفع -->
<!-- زر فتح المودال -->


<!-- المودال -->
<div class="modal fade" id="supplierPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="supplierPaymentForm" method="POST" action="{{ route('dashboard.supplier-payments.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إضافة دفعة للمورد</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="supplier_id" id="supplierPaymentSupplierId">
                    <div class="form-group">
                        <label>المبلغ</label>
                        <input type="number" step="0.01" name="amount" id="supplierPaymentAmount" class="form-control" required>
                        <small id="supplierRemainingText" class="text-muted"></small>
                    </div>
                    <div class="form-group">
                        <label>تاريخ الدفع</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea name="note" class="form-control"></textarea>
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



<div class="modal fade" id="paymentsModal{{ $supplier->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">دفعات المورد: {{ $supplier->name }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                @if($supplier->payments->count() > 0)
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
                            <th>الملاحظة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplier->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->note }}</td>
                            <td><button class="btn btn-warning btn-sm edit-supplier-payment-btn"
        data-toggle="modal"
        data-target="#editSupplierPaymentModal"
        data-payment-id="{{ $payment->id }}"
        data-amount="{{ $payment->amount }}"
        data-payment-date="{{ $payment->payment_date }}"
        data-note="{{ $payment->note }}"
        data-supplier-id="{{ $payment->supplier_id }}">
    تعديل
</button>
</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p>لا توجد دفعات مسجلة</p>
                @endif
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editSupplierPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="editSupplierPaymentForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">تعديل الدفعة</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="payment_id" id="editPaymentId">
                    <input type="hidden" name="supplier_id" id="editSupplierId">

                    <div class="form-group">
                        <label>المبلغ</label>
                        <input type="number" step="0.01" name="amount" id="editPaymentAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>تاريخ الدفع</label>
                        <input type="date" name="payment_date" id="editPaymentDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea name="note" id="editPaymentNote" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                </div>
            </form>
        </div>
    </div>
</div>





<!-- مودال الدفع -->
<div class="modal fade" id="supplierPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="supplierPaymentForm" method="POST" action="{{ route('dashboard.supplier-payments.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إضافة دفعة للمورد</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="supplier_id" id="supplierPaymentSupplierId">
                    <div class="form-group">
                        <label>المبلغ</label>
                        <input type="number" step="0.01" name="amount" id="supplierPaymentAmount" class="form-control" required>
                        <small id="supplierRemainingText" class="text-muted"></small>
                    </div>
                    <div class="form-group">
                        <label>تاريخ الدفع</label>
                        <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea name="note" class="form-control"></textarea>
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

@push('scripts')
{{-- <script>
    $(document).ready(function() {

        $('.add-payment-btn').on('click', function() {
            var orderId = $(this).data('order-id');
            var url = '/dashboard/orders/' + orderId + '/payments/create'; // رابط Route لإرجاع نموذج الدفع

            // عرض رسالة تحميل
            $('#paymentModalContent').html('<p class="text-center">جارٍ تحميل نموذج الدفع...</p>');
            $('#paymentModal').modal('show'); // فتح المودال

            // جلب النموذج عبر AJAX
            $.ajax({
                url: url
                , type: 'GET'
                , success: function(response) {
                    $('#paymentModalContent').html(response);
                }
                , error: function() {
                    $('#paymentModalContent').html('<p class="text-danger text-center">حدث خطأ أثناء تحميل النموذج</p>');
                }
            });
        });

    });

</script> --}}
<script>
    $(document).ready(function() {
        $('.add-supplier-payment-btn').on('click', function() {
            var supplierId = $(this).data('supplier-id');
            var remaining = parseFloat($(this).data('remaining'));

            $('#supplierPaymentSupplierId').val(supplierId);
            $('#supplierPaymentAmount').attr('max', remaining).val('');
            $('#supplierRemainingText').text('رصيد المورد الحالي: ' + remaining.toFixed(2));
        });

        $('#supplierPaymentForm').on('submit', function(e) {
            var max = parseFloat($('#supplierPaymentAmount').attr('max'));
            var amount = parseFloat($('#supplierPaymentAmount').val());

            if (amount > max) {
                alert('⚠️ المبلغ أكبر من رصيد المورد!');
                e.preventDefault();
            }
        });
    });

</script>


<script>
$(document).ready(function() {
    $('.edit-supplier-payment-btn').on('click', function() {
        var paymentId   = $(this).data('payment-id');
        var supplierId  = $(this).data('supplier-id');
        var amount      = parseFloat($(this).data('amount'));
        var date        = $(this).data('payment-date');
        var note        = $(this).data('note');

        $('#editPaymentId').val(paymentId);
        $('#editSupplierId').val(supplierId);
        $('#editPaymentAmount').val(amount.toFixed(2));
        $('#editPaymentDate').val(date);
        $('#editPaymentNote').val(note);

        // اضبط action الفورم ديناميكيًا
        $('#editSupplierPaymentForm').attr('action', '/dashboard/supplier-payments/' + paymentId);
    });
});
</script>

@endpush
@endsection
