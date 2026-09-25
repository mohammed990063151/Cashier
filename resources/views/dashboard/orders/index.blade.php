@extends('layouts.dashboard.app')

@section('content')

@include('dashboard.orders._orders_page_styles')

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

            {{-- جدول الطلبات --}}
            <div class="col-lg-8 col-md-7">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list-alt"></i> قائمة الطلبات</h3>
                    </div>

                    <div class="box-body">
                        <form action="{{ route('dashboard.orders.index') }}" method="get" class="orders-toolbar">
                            <div class="row">
                                <div class="col-sm-12 col-md-4" style="margin-bottom:8px;">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="بحث برقم الطلب أو اسم العميل"
                                           value="{{ request()->search }}">
                                </div>
                                <div class="col-sm-12 col-md-5" style="margin-bottom:8px;">
                                    @include('dashboard.orders._payment_filter')
                                </div>
                                <div class="col-xs-6 col-md-1" style="margin-bottom:8px;">
                                    <button type="submit" class="btn btn-primary btn-block" title="بحث">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                                <div class="col-xs-6 col-md-2" style="margin-bottom:8px;">
                                    <a href="{{ route('dashboard.direct-sale') }}" class="btn btn-success btn-block">
                                        <i class="fa fa-cash-register"></i> بيع مباشر
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if(session('error'))
                    <div class="box-body" style="padding-top:0;">
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('error') }}
                        </div>
                    </div>
                    @endif

                    @if ($orders->count() > 0)
                    <div class="box-body orders-table-wrap" style="padding-top:0;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover orders-table">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>الإجمالي</th>
                                        <th>الخصم</th>
                                        <th>بعد الخصم</th>
                                        <th>المدفوع</th>
                                        <th>المتبقي</th>
                                        <th>التاريخ</th>
                                        <th>مرتجع</th>
                                        <th>الحالة</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $finService = app(\App\Services\OrderFinancialService::class); @endphp
                                    @foreach ($orders as $order)
                                    @php
                                        $fin = $finService->calculate($order);
                                        $status = $finService->paymentStatus($order);
                                    @endphp
                                    <tr class="orders-row" data-order-id="{{ $order->id }}">
                                        <td class="col-order-no">{{ $order->order_number }}</td>
                                        <td class="col-client">{{ $order->client->name }}</td>
                                        <td><span class="money money-total">{{ number_format($fin['totalSale'], 2) }}</span></td>
                                        <td><span class="money money-discount">{{ number_format($fin['invoiceDiscount'], 2) }}</span></td>
                                        <td><span class="money money-after">{{ number_format($fin['totalAfterDiscount'], 2) }}</span></td>
                                        <td><span class="money money-paid">{{ number_format($fin['netPaid'] ?? $fin['totalPaid'], 2) }}</span></td>
                                        <td>
                                            <span class="money {{ $fin['remaining'] > 0 ? 'money-remain-due' : 'money-remain-zero' }}">
                                                {{ number_format($fin['remaining'], 2) }}
                                            </span>
                                        </td>
                                        <td class="col-date">{{ $order->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if(($order->total_return ?? 0) > 0)
                                            <span class="money text-danger">{{ number_format($order->total_return, 2) }}</span>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="label {{ $finService->paymentStatusClass($status) }}">
                                                {{ $finService->paymentStatusLabel($status) }}
                                            </span>
                                        </td>
                                        <td class="col-actions">
                                            <div class="orders-actions phone-action-bar">
                                                <button type="button" class="btn btn-primary btn-sm order-products"
                                                        title="معاينة سريعة"
                                                        data-url="{{ route('dashboard.orders.products', $order->id) }}"
                                                        data-method="get">
                                                    <i class="fa fa-eye"></i> معاينة
                                                </button>
                                                @if (auth()->user()->hasPermission('update_orders') && $order->products->count() > 0)
                                                <button type="button"
                                                        class="btn btn-default btn-sm order-return-btn"
                                                        title="مرتجع"
                                                        data-url="{{ route('dashboard.orders.return', $order->id) }}">
                                                    <i class="fa fa-undo"></i> مرتجع
                                                </button>
                                                @endif
                                                @if (auth()->user()->hasPermission('update_orders'))
                                                <a href="{{ route('dashboard.clients.orders.edit', ['client' => $order->client->id, 'order' => $order->id]) }}"
                                                   class="btn btn-warning btn-sm" title="تعديل">
                                                    <i class="fa fa-pencil"></i> تعديل
                                                </a>
                                                @endif
                                                @if (auth()->user()->hasPermission('delete_orders'))
                                                <form action="{{ route('dashboard.orders.destroy', $order->id) }}" method="post" class="delete-form">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="button" class="btn btn-danger btn-sm delete-btn" title="حذف">
                                                        <i class="fa fa-trash"></i> حذف
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="products-pagination text-center" style="margin-top:10px;">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    </div>
                    @else
                    <div class="box-body">
                        <div class="alert alert-info text-center" style="margin:0;">
                            <i class="fa fa-info-circle"></i> لا توجد طلبات مطابقة للبحث
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- معاينة الطلب --}}
            <div class="col-lg-4 col-md-5">
                <div class="box box-primary orders-preview-box">
                    <div class="box-header">
                        <h3 class="box-title"><i class="fa fa-receipt"></i> معاينة الطلب</h3>
                    </div>
                    <div class="box-body">
                        <div id="loading" class="orders-loading">
                            <div class="loader"></div>
                            <p style="margin-top:10px;color:#64748b;">جاري التحميل...</p>
                        </div>
                        <div class="orders-preview-body" id="order-product-list">
                            <div class="orders-preview-empty" id="orders-preview-placeholder">
                                <i class="fa fa-hand-pointer-o"></i>
                                <p>اختر طلباً من الجدول<br>واضغط <strong>معاينة</strong> لعرض الأصناف والمبالغ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<div class="modal fade" id="orderReturnModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#f39c12;color:#fff;padding:10px 15px;">
                <h5 class="modal-title" style="margin:0;"><i class="fa fa-undo"></i> مرتجع طلب</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="orderReturnModalContent">
                <p class="text-muted text-center">جارٍ التحميل...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg order-modal-fit" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" style="padding:10px 15px;">
                <h5 class="modal-title" style="margin:0;"><i class="fa fa-file-text-o"></i> تفاصيل الطلب</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body order-modal-body" id="orderModalContent">
                <p class="text-muted text-center">جارٍ التحميل...</p>
            </div>
            <div class="modal-footer" style="padding:8px 15px;">
                <a href="#" id="modalPrintPdfBtn" target="_blank" class="btn btn-primary btn-sm" style="display:none;">
                    <i class="fa fa-print"></i> طباعة
                </a>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<style>
.order-modal-fit { max-width: 720px; width: 95%; margin: 30px auto; }
.order-modal-body { max-height: 70vh; overflow-y: auto; padding: 12px 15px !important; }
</style>

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    let orderId = "{{ session('order_id') }}";
    Swal.fire({
        title: 'تم إضافة الطلب بنجاح!',
        text: "هل تريد طباعة الفاتورة الآن؟",
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: 'نعم، اطبع الفاتورة',
        cancelButtonText: 'لا، لاحقاً'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/dashboard/orders/' + orderId + '/pdf';
        }
    });
</script>
@endif

<script>
function loadOrderModal(orderId) {
    $('#orderModal').modal('show');
    $('#orderModalContent').html('<p class="text-center text-muted">جارٍ تحميل البيانات...</p>');
    $.ajax({
        url: '/dashboard/orders/' + orderId,
        type: 'GET',
        success: function(response) {
            $('#orderModalContent').html(response);
            var printBtn = $('#orderModalContent .print-order-pdf');
            if (printBtn.length) {
                $('#modalPrintPdfBtn').attr('href', printBtn.attr('href')).show();
            } else {
                $('#modalPrintPdfBtn').hide();
            }
        },
        error: function() {
            $('#orderModalContent').html('<p class="text-danger text-center">حدث خطأ أثناء تحميل البيانات</p>');
        }
    });
}

$(document).ready(function() {
    $('body').on('click', '.view-order-btn, .view-order-modal-btn', function() {
        loadOrderModal($(this).data('order-id'));
    });

    $('body').on('click', '.order-return-btn', function() {
        var url = $(this).data('url');
        $('#orderReturnModal').modal('show');
        $('#orderReturnModalContent').html('<p class="text-center text-muted">جارٍ التحميل...</p>');
        $.get(url, function(html) {
            $('#orderReturnModalContent').html(html);
        }).fail(function() {
            $('#orderReturnModalContent').html('<p class="text-danger text-center">تعذر تحميل نموذج المرتجع</p>');
        });
    });

    $('body').on('submit', '#order-return-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('#return-submit-btn');
        var btnHtml = $btn.html();

        var hasQty = false;
        $form.find('.return-qty-input').each(function() {
            if (parseInt($(this).val(), 10) > 0) {
                hasQty = true;
            }
        });
        if (!hasQty) {
            Swal.fire({ icon: 'warning', title: 'حدد كمية مرتجعة واحدة على الأقل' });
            return;
        }

        Swal.fire({
            title: 'تأكيد المرتجع؟',
            text: 'سيتم تحديث الطلب والمخزون وتسجيل الحركة في الخزينة عند الحاجة.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، تنفيذ',
            cancelButtonText: 'إلغاء'
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> جاري التنفيذ...');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(res) {
                    $('#orderReturnModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'تم بنجاح',
                        text: res.message || 'تم تسجيل المرتجع.'
                    }).then(function() {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(btnHtml);
                    var msg = 'تعذر تنفيذ المرتجع. حاول مرة أخرى.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            var lines = [];
                            $.each(xhr.responseJSON.errors, function(_, arr) {
                                if ($.isArray(arr)) {
                                    lines = lines.concat(arr);
                                }
                            });
                            if (lines.length) {
                                msg = lines.join('\n');
                            }
                        }
                    }
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg });
                }
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            let form = this.closest('.delete-form');
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن الحذف!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف!',
                cancelButtonText: 'إلغاء'
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
