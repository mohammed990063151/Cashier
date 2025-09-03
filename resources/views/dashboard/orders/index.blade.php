@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">

        <h1>الطلبات
            <small>{{ $orders->total() }} طلب</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">الطلبات</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-md-8">

                <div class="box box-primary">

                    <div class="box-header">

                        <h3 class="box-title" style="margin-bottom: 10px">الطلبات</h3>

                        <form action="{{ route('dashboard.orders.index') }}" method="get">

                            <div class="row">
                                <di class="row g-2 align-items-center mb-3">
                                    <!-- حقل البحث -->
                                    <div class="col-md-6 col-sm-16">
                                        <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
                                    </div>

                                    <!-- زر البحث -->
                                    <div class="col-md-3 col-sm-3">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fa fa-search"></i> بحث
                                        </button>
                                    </div>

                                    <!-- زر البيع المباشر -->
                                    <div class="col-md-3 col-sm-3">
                                        <a href="{{ route('dashboard.direct-sale') }}" class="btn btn-success w-100">
                                            <i class="fa fa-cash-register"></i> بيع مباشر
                                        </a>
                                    </div>



                            </div><!-- end of row -->

                        </form><!-- end of form -->

                    </div><!-- end of box header -->

                    @if ($orders->count() > 0)

                    <div class="box-body table-responsive">

                        <table class="table table-hover">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>اسم العميل</th>
                                <th>اجمالي طلب</th>
                                <th>المدفوع منه</th>
                                <th>المتبقي عليه</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>

                            @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->client->name }}</td>
                                <td style="color: #01941f; font-weight: bold;">{{ number_format($order->total_price, 2) }}</td>
                                <td>{{ number_format($order->discount, 2) }}</td>
                                <td style="color: #e74c3c; font-weight: bold;">{{ number_format($order->remaining, 2) }}</td>
                                <td>{{ $order->created_at->toFormattedDateString() }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm order-products" data-url="{{ route('dashboard.orders.products', $order->id) }}" data-method="get">
                                        <i class="fa fa-list"></i>
                                        عرض
                                    </button>
                                    @if (auth()->user()->hasPermission('update_orders'))
                                    <a href="{{ route('dashboard.clients.orders.edit', ['client' => $order->client->id, 'order' => $order->id]) }}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i> تعديل</a>
                                    @else
                                    <a href="#" disabled class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> تعديل</a>
                                    @endif
                                    <button type="button" class="btn btn-info btn-sm view-order-btn" data-toggle="modal" data-target="#orderModal" data-order-id="{{ $order->id }}">
                                        عرض الطلب
                                    </button>
                                    @if (auth()->user()->hasPermission('delete_orders'))
                                    <form action="{{ route('dashboard.orders.destroy', $order->id) }}" method="post" class="delete-form" style="display: inline-block;">
                                        @csrf
                                        @method('delete')
                                        <button type="button" class="btn btn-danger btn-sm delete-btn">
                                            <i class="fa fa-trash"></i> حذف
                                        </button>
                                    </form>
                                    @else
                                    <a href="#" class="btn btn-danger btn-sm" disabled><i class="fa fa-trash"></i> حذف</a>
                                    @endif





                                </td>



                            </tr>

                            @endforeach

                        </table><!-- end of table -->

                        {{ $orders->appends(request()->query())->links() }}

                    </div>

                    @else

                    <div class="box-body">
                        <h3>لا توجد سجلات</h3>
                    </div>

                    @endif

                </div><!-- end of box -->

            </div><!-- end of col -->

            <div class="col-md-4">

                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title" style="margin-bottom: 10px">عرض المنتجات</h3>
                    </div><!-- end of box header -->

                    <div class="box-body">

                        <div style="display: none; flex-direction: column; align-items: center;" id="loading">
                            <div class="loader"></div>
                            <p style="margin-top: 10px">جاري التحميل...</p>
                        </div>

                        <div id="order-product-list">

                        </div><!-- end of order product list -->

                    </div><!-- end of box body -->

                </div><!-- end of box -->

            </div><!-- end of col -->

        </div><!-- end of row -->

    </section><!-- end of content section -->

</div><!-- end of content wrapper -->


{{-- <div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">تفاصيل الطلب</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="orderModalContent">
                <!-- سيتم تحميل تفاصيل الطلب هنا -->
                <p class="text-center">جارٍ تحميل البيانات...</p>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div> --}}

<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content rounded-3 shadow-lg border-0">

            <!-- الهيدر -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="orderModalLabel">تفاصيل الطلب</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="إغلاق">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- البودي -->
            <div class="modal-body" id="orderModalContent">
                <div class="d-flex justify-content-center align-items-center" style="min-height:150px;">
                    <p class="text-muted">جارٍ تحميل البيانات...</p>
                </div>
            </div>

            <!-- الفوتر -->
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary"  onclick="window.location.href='{{ route('dashboard.orders.pdf', $order->id) }}'">طباعة الطلب</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    let orderId = "{{ session('order_id') }}"; // رقم الطلب الجديد
    Swal.fire({
        title: 'تم إضافة الطلب بنجاح!',
        text: "هل تريد طباعة الفاتورة الآن؟",
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: 'نعم، اطبع الفاتورة',
        cancelButtonText: 'لا، لاحقاً'
    }).then((result) => {
        if (result.isConfirmed) {
            // إعادة التوجيه لصفحة PDF للطباعة بالمسار الصحيح
            window.location.href = '/dashboard/orders/' + orderId + '/pdf';
        }
    });
</script>

@endif
@endpush

<script>
    $(document).ready(function() {
        $('.view-order-btn').on('click', function() {
            var orderId = $(this).data('order-id');
            var url = '/dashboard/orders/' + orderId; // رابط API أو Route يعيد HTML الطلب

            $('#orderModalContent').html('<p class="text-center">جارٍ تحميل البيانات...</p>');

            $.ajax({
                url: url
                , type: 'GET'
                , success: function(response) {
                    // ضع البيانات داخل المودال
                    $('#orderModalContent').html(response);
                }
                , error: function() {
                    $('#orderModalContent').html('<p class="text-danger text-center">حدث خطأ أثناء تحميل البيانات</p>');
                }
            });
        });
    });

</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-btn').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                let form = this.closest('.delete-form');

                Swal.fire({
                    title: 'هل أنت متأكد؟'
                    , text: "لن تتمكن من التراجع عن الحذف!"
                    , icon: 'warning'
                    , showCancelButton: true
                    , confirmButtonColor: '#d33'
                    , cancelButtonColor: '#3085d6'
                    , confirmButtonText: 'نعم، احذف!'
                    , cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });

</script>
@endpush
@endsection
