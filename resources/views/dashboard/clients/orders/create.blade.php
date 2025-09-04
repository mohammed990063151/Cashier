@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">

        <h1>إضافة طلب</h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li><a href="{{ route('dashboard.clients.index') }}">العملاء</a></li>
            <li class="active">إضافة طلب</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-md-6">

                <div class="box box-primary">

                    <div class="box-header">

                        <h3 class="box-title" style="margin-bottom: 10px">الفئات</h3>

                    </div><!-- end of box header -->

                    <div class="box-body">

                        @foreach ($categories as $category)

                        <div class="panel-group">

                            <div class="panel panel-info">

                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#{{ str_replace(' ', '-', $category->name) }}">{{ $category->name }}</a>
                                    </h4>
                                </div>

                                <div id="{{ str_replace(' ', '-', $category->name) }}" class="panel-collapse collapse">

                                    <div class="panel-body">

                                        @if ($category->products->count() > 0)

                                        <table class="table table-hover">
                                            <tr>
                                                <th>الاسم</th>
                                                <th>المخزون</th>
                                                <th>السعر</th>
                                                <th>إضافة</th>
                                            </tr>

                                            @foreach ($category->products as $product)
                                            <tr>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->stock }}</td>
                                                <td>{{ number_format($product->sale_price, 2) }}</td>

                                                <td>
                                                    <a href="" id="product-{{ $product->id }}" data-name="{{ $product->name }}" data-id="{{ $product->id }}" data-price="{{ $product->sale_price }}" class="btn btn-success btn-sm add-product-btn">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach

                                        </table><!-- end of table -->

                                        @else
                                        <h5>لا توجد سجلات</h5>
                                        @endif

                                    </div><!-- end of panel body -->

                                </div><!-- end of panel collapse -->

                            </div><!-- end of panel primary -->

                        </div><!-- end of panel group -->

                        @endforeach

                    </div><!-- end of box body -->

                </div><!-- end of box -->

            </div><!-- end of col -->

            <div class="col-md-6">

                <div class="box box-primary">

                    <div class="box-header">

                        <h3 class="box-title">الطلبات</h3>

                    </div><!-- end of box header -->

                    <div class="box-body">

                        <form action="{{ route('dashboard.clients.orders.store', $client->id) }}" method="post">

                            {{ csrf_field() }}
                            {{ method_field('post') }}
                            @if(session('error'))
                            <div id="error-alert" class="alert alert-danger text-center">
                                {{ session('error') }}
                            </div>
                            @endif

                            @include('partials._errors')

                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>المنتج</th>
                                        <th>الكمية</th>
                                        <th>سعر الوحدة</th>
                                        <th>الإجمالي</th>
                                    </tr>
                                </thead>

                                <tbody class="order-list">

                                </tbody>

                            </table><!-- end of table -->

                            <h4>الإجمالي: <span class="total-price" style="color: #01941f; font-weight: bold;">0</span></h4>

                            <div class="form-group">
                                <label>المدفوع :</label>
                                <input type="number" step="0.01" name="discount" id="discount" class="form-control" value="0">
                            </div>

                            <div class="form-group">
                                <label>المتبقي:</label>
                                <input type="text" name="remaining" id="remaining" class="form-control" readonly value="0" style="color: #d50606; font-weight: bold;">
                            </div>



                            <button class="btn btn-primary btn-block disabled" id="add-order-form-btn"><i class="fa fa-plus"></i> إضافة الطلب</button>

                        </form>

                    </div><!-- end of box body -->

                </div><!-- end of box -->

                @if ($client->orders->count() > 0)
                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title" style="margin-bottom: 10px">الطلبات السابقة
                            <small>{{ $orders->total() }}</small>
                        </h3>
                    </div><!-- end of box header -->

                    <div class="box-body">

                        @foreach ($orders as $order)
                        <div class="panel-group">

                            <div class="panel panel-success">

                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#order-{{ $order->id }}">
                                            رقم الطلب# {{ $order->created_at->toFormattedDateString() }} - {{ $order->order_number }}
                                        </a>
                                    </h4>
                                </div>

                                <div id="order-{{ $order->id }}" class="panel-collapse collapse">

                                    <div class="panel-body">

                                        <!-- جدول المنتجات -->
                                        <div class="table-responsive shadow rounded-lg border p-3 bg-white">
                                            <table class="table table-hover table-bordered align-middle text-center mb-0">
                                                <thead class="table-primary">
                                                    <tr>
                                                        <th class="fw-bold">المنتج</th>
                                                        <th class="fw-bold">الكمية</th>
                                                        <th class="fw-bold">سعر الوحدة</th>
                                                        <th class="fw-bold">الإجمالي</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($order->products as $product)
                                                    <tr>
                                                        <td class="fw-bold text-start">{{ $product->name }}</td>
                                                        <td>{{ $product->pivot->quantity }}</td>
                                                        <td class="text-success fw-bold">{{ number_format($product->sale_price,2) }} ج.س</td>
                                                        <td class="text-primary fw-bold">{{ number_format($product->pivot->sale_price * $product->pivot->quantity,2) }} ج.س</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3" class="text-end fw-bold">الإجمالي الكلي:</th>
                                                        <th class="text-danger fw-bold">
                                                            {{ number_format($order->products->sum(fn($p) => $p->pivot->sale_price * $p->pivot->quantity),2) }} ج.س
                                                        </th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        @php
                                        $paid = $order->payments->sum('amount');
                                        @endphp
                                        <!-- معلومات الطلب -->
                                        <div class="row mt-2">
                                            <div class="col-md-3" style="color: #01941f; font-weight: bold;">
                                                <strong>الإجمالي:</strong> {{ number_format($order->total_price,2) }} ج.س
                                            </div>
                                            <div class="col-md-3">
                                                <strong>المدفوع عند البيع:</strong> {{ number_format($order->discount,2) }} ج.س
                                            </div>
                                            <div class="col-md-3">
                                                <strong>اجمالي مدفوع:</strong> {{ number_format($paid,2) }} ج.س
                                            </div>
                                            <div class="col-md-3" style="color: #6a0505; font-weight: bold;">
                                                <strong>المتبقي:</strong> {{ number_format($order->remaining,2) }} ج.س
                                            </div>
                                        </div>

                                    </div><!-- نهاية جسم البانل -->

                                </div><!-- نهاية الانهيار -->

                            </div><!-- نهاية البانل -->

                        </div><!-- نهاية المجموعة -->
                        @endforeach

                        {{ $orders->links() }}

                    </div><!-- end of box body -->

                </div><!-- end of box -->
                @endif

            </div><!-- end of col -->

        </div><!-- end of row -->

    </section><!-- end of content -->

</div><!-- end of content wrapper -->


@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.querySelector('form'); // النموذج
        const totalPriceEl = document.querySelector('.total-price');
        const discountEl = document.getElementById('discount');
        const remainingEl = document.getElementById('remaining');

        // تحويل النص إلى رقم
        function parseNumber(str) {
            return parseFloat(str.replace(/,/g, '')) || 0;
        }

        // تحديث المتبقي عند تغيير المدفوع
        discountEl.addEventListener('input', function() {
            const total = parseNumber(totalPriceEl.textContent);
            const discount = parseNumber(this.value);

            remainingEl.value = Math.max(total - discount, 0);

            // إذا المدفوع  أكبر من الإجمالي
            if (discount > total) {
                remainingEl.style.color = '#d50606';
                remainingEl.style.fontWeight = 'bold';
            } else {
                remainingEl.style.color = '#000';
                remainingEl.style.fontWeight = 'normal';
            }
        });

        // تحقق قبل إرسال النموذج
        form.addEventListener('submit', function(e) {
            const total = parseNumber(totalPriceEl.textContent);
            const discount = parseNumber(discountEl.value);

            if (discount > total) {
                e.preventDefault(); // إيقاف الإرسال
                alert("المدفوع  لا يمكن أن يكون أكبر من إجمالي الطلب!");
                discountEl.focus();
            }
        });
    });

</script>




<script>
    document.addEventListener("DOMContentLoaded", function() {
        let alertBox = document.getElementById('error-alert');
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.transition = "opacity 0.5s";
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 500);
            }, 5000); // تختفي بعد 3 ثواني
        }
    });

</script>

@endpush
@endsection
