@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">

        <h1>تعديل الطلب</h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li><a href="{{ route('dashboard.clients.index') }}">العملاء</a></li>
            <li class="active">تعديل الطلب</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-md-6">

                <div class="box box-primary">

                    <div class="box-header">

                        <h3 class="box-title" style="margin-bottom: 10px">الفئات</h3>

                    </div><!-- نهاية رأس الصندوق -->

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
                                                <td>{{ $product->sale_price }}</td>
                                                <td>
                                                    <a href="" id="product-{{ $product->id }}" data-name="{{ $product->name }}" data-id="{{ $product->id }}" data-price="{{ $product->sale_price }}" class="btn {{ in_array($product->id, $order->products->pluck('id')->toArray()) ? 'btn-default disabled' : 'btn-success add-product-btn' }} btn-sm">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach

                                        </table><!-- نهاية الجدول -->

                                        @else
                                        <h5>لا توجد سجلات</h5>
                                        @endif

                                    </div><!-- نهاية جسم البانل -->

                                </div><!-- نهاية الانهيار -->

                            </div><!-- نهاية البانل -->

                        </div><!-- نهاية المجموعة -->

                        @endforeach

                    </div><!-- نهاية جسم الصندوق -->

                </div><!-- نهاية الصندوق -->

            </div><!-- نهاية العمود -->

            <div class="col-md-6">

                <div class="box box-primary">

                    <div class="box-header">

                        <h3 class="box-title">الطلبات</h3>

                    </div><!-- نهاية رأس الصندوق -->

                    <div class="box-body">
                        @if(session('error'))
                        <div id="error-alert" class="alert alert-danger text-center">
                            {{ session('error') }}
                        </div>
                        @endif
                        @include('partials._errors')

                        <form action="{{ route('dashboard.clients.orders.update', ['order' => $order->id, 'client' => $client->id]) }}" method="post">

                            {{ csrf_field() }}
                            {{ method_field('put') }}

                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>المنتج</th>
                                        <th>الكمية</th>
                                        <th>سعر الوحد</th>
                                        <th>الاجمالي</th>
                                    </tr>
                                </thead>

                                <tbody class="order-list">

                                    @foreach ($order->products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>
                                            <input type="number" name="products[{{ $product->id }}][quantity]" class="form-control input-sm product-quantity" min="1" value="{{ $product->pivot->quantity }}">
                                        </td>
                                        <td>
                                            <input type="number" name="products[{{ $product->id }}][sale_price]" class="form-control input-sm product-unit-price" min="1" step="1" value="{{ $product->pivot->sale_price }}">
                                        </td>
                                        <td>
                                            <span class="product-price">{{ number_format($product->pivot->quantity * $product->pivot->sale_price, 2) }}</span>
                                            <input type="hidden" name="products[{{ $product->id }}][total_price]" value="{{ $product->pivot->quantity * $product->pivot->sale_price }}">
                                        </td>
                                        <td>
                                            <button class="btn btn-danger btn-sm remove-product-btn" data-id="{{ $product->id }}"><span class="fa fa-trash"></span></button>

                                        </td>
                                    </tr>
                                    @endforeach


                                </tbody>

                            </table><!-- نهاية الجدول -->

                            <h4>الإجمالي: <span class="total-price" style="color: #046b0a; font-weight: bold;">{{ number_format($order->total_price, 2) }}</span></h4>
<div class="form-group">
    <label for="invoice_discount">الخصم</label>
    <input type="number" name="tax_amount" id="invoice_discount" class="form-control" min="0" step="0"
           value="{{ $order->tax_amount ?? 0 }}">
</div>
<h4>الإجمالي بعد الخصم:
    <span id="discounted-total" style="color:#007bff; font-weight:bold;">
        {{ number_format($order->total_price - ($order->tax_amount ?? 0), 2) }}
    </span>
</h4>
                            <div class="form-group">
                                <label for="discount">المدفوع </label>
                                <input type="number" name="discount" id="discount" class="form-control" min="0" step="1" value="{{ $order->discount }}">
                            </div>


                            <div class="form-group">
                                <label>المتبقي:</label>
                                <input type="text" name="remaining" id="remaining" class="form-control" readonly value="{{ number_format($order->remaining, 2) }}" style="color: #d50606; font-weight: bold;">
                            </div>


                            <button class="btn btn-primary btn-block" id="add-order-form-btn"><i class="fa fa-edit"></i> تعديل الطلب</button>
 {{-- <button class="btn btn-primary btn-block disabled" id="add-order-form-btn"><i class="fa fa-plus"></i> إضافة الطلب</button> --}}
                        </form><!-- نهاية النموذج -->

                    </div><!-- نهاية جسم الصندوق -->

                </div><!-- نهاية الصندوق -->

                @if ($client->orders->count() > 0)

                <div class="box box-primary">

                    <div class="box-header">

                        <h3 class="box-title" style="margin-bottom: 10px">
                            الطلبات السابقة
                            <small>{{ $orders->total() }}</small>
                        </h3>

                    </div><!-- نهاية رأس الصندوق -->

                    <div class="box-body">

                        @foreach ($orders as $order)

                        <div class="panel-group">

                            <div class="panel panel-success">

                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" href="#order-{{ $order->id }}">
                                            رقم الطلب# {{ $order->created_at->toFormattedDateString() }} - {{ $order->order_number  }}
                                        </a>
                                    </h4>
                                </div>

                                <div id="order-{{ $order->id  }}" class="panel-collapse collapse">

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
                                                        <td class="text-success fw-bold">{{ number_format($product->pivot->sale_price,2) }} ج.س</td>
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
                                            <div class="col-md-3">
                                                <strong>الإجمالي:</strong> {{ number_format($order->total_price,2) }} ج.س
                                            </div>
                                            <div class="col-md-3">
                                                <strong>المدفوع :</strong> {{ number_format($order->discount,2) }} ج.س
                                            </div>
                                             <div class="col-md-3">
                                                <strong>اجمالي المدفوع:</strong> {{ number_format($paid,2) }} ج.س
                                            </div>
                                            <div class="col-md-3">
                                                <strong>المتبقي:</strong> {{ number_format($order->remaining,2) }} ج.س
                                            </div>

                                        </div>

                                    </div><!-- نهاية جسم البانل -->

                                </div><!-- نهاية الانهيار -->

                            </div><!-- نهاية البانل -->

                        </div><!-- نهاية المجموعة -->

                        @endforeach

                        {{ $orders->links() }}

                    </div><!-- نهاية جسم الصندوق -->

                </div><!-- نهاية الصندوق -->

                @endif

            </div><!-- نهاية العمود -->

        </div><!-- نهاية الصف -->

    </section><!-- نهاية المحتوى -->

</div><!-- نهاية حاوية المحتوى -->
@push('scripts')
<script>
// document.addEventListener("DOMContentLoaded", function() {
//     const form = document.querySelector('form'); // النموذج
//     const totalPriceEl = document.querySelector('.total-price');
//     const discountEl = document.getElementById('discount');
//     const remainingEl = document.getElementById('remaining');

//     // تحويل النص إلى رقم
//     function parseNumber(str) {
//         return parseFloat(str.replace(/,/g, '')) || 0;
//     }

//     // تحديث المتبقي عند تغيير المدفوع
//    function updateRemaining() {
//     const total = parseNumber(totalPriceEl.textContent);
//     const tax = parseNumber(document.getElementById('invoice_discount').value);
//     const discount = parseNumber(discountEl.value);

//     const totalWithTax = total + tax;
//     remainingEl.value = Math.max(totalWithTax - discount, 0);

//     if (discount > totalWithTax) {
//         remainingEl.style.color = '#d50606';
//         remainingEl.style.fontWeight = 'bold';
//     } else {
//         remainingEl.style.color = '#000';
//         remainingEl.style.fontWeight = 'normal';
//     }
// }
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector('form');
    const totalPriceEl = document.querySelector('.total-price');
    const discountEl = document.getElementById('discount'); // المدفوع
    const remainingEl = document.getElementById('remaining');
    const invoiceDiscountEl = document.getElementById('invoice_discount'); // هذا هو tax_amount لكن كخصم

    function parseNumber(v) {
        return parseFloat((v || "0").toString().replace(/,/g, '')) || 0;
    }


 function calculateTotal() {
    let total = 0;

    document.querySelectorAll('.order-list tr').forEach(row => {
        const qty = parseNumber(row.querySelector('.product-quantity')?.value);
        const price = parseNumber(row.querySelector('.product-unit-price')?.value);
        const productTotal = qty * price;

        row.querySelector('.product-price').textContent = productTotal.toFixed(2);
        row.querySelector('input[name$="[total_price]"]').value = productTotal;

        total += productTotal;
    });

    totalPriceEl.textContent = total.toFixed(2);

    const invoiceDiscount = parseNumber(invoiceDiscountEl?.value);
    const paid = parseNumber(discountEl?.value);

    // ✅ الإجمالي بعد الخصم
    let discountedTotal = total - invoiceDiscount;
    if (discountedTotal < 0) discountedTotal = 0;
    document.getElementById('discounted-total').textContent = discountedTotal.toFixed(2);

    // ✅ المتبقي
    let remaining = discountedTotal - paid;
    if (remaining < 0) remaining = 0;
    remainingEl.value = remaining.toFixed(2);

    // hidden input عشان يتخزن في الباك
    document.getElementById('total_price').value = total.toFixed(2);
}


    // تشغيل عند أي تغيير
    $(document).on('input', '.product-quantity, .product-unit-price, #invoice_discount, #discount', function() {
        calculateTotal();
    });

    // تحقق قبل الإرسال
    form.addEventListener('submit', function(e) {
        calculateTotal();
        const total = parseNumber(totalPriceEl.textContent);
        const invoiceDiscount = parseNumber(invoiceDiscountEl.value);
        const paid = parseNumber(discountEl.value);

        if (paid > (total - invoiceDiscount)) {
            e.preventDefault();
            alert("المدفوع لا يمكن أن يكون أكبر من إجمالي الطلب بعد الخصم!");
            discountEl.focus();
        }
    });

    // أول تحديث عند التحميل
    calculateTotal();
});


document.getElementById('invoice_discount').addEventListener('input', updateRemaining);

    // حدث عند تغيير المدفوع
    discountEl.addEventListener('input', updateRemaining);

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

    // إذا أردت يمكن تحديث المتبقي عند تغيير أي كمية في المنتجات
    document.querySelectorAll('.product-quantity, .product-unit-price').forEach(input => {
        input.addEventListener('input', function() {
            let total = 0;
            document.querySelectorAll('.order-list tr').forEach(row => {
                const qty = parseNumber(row.querySelector('.product-quantity').value);
                const price = parseNumber(row.querySelector('.product-unit-price').value);
                total += qty * price;
                row.querySelector('.product-price').textContent = (qty * price).toFixed(2);
            });
            totalPriceEl.textContent = total.toFixed(2);
            updateRemaining();
        });
    });

    // تحديث المتبقي عند تحميل الصفحة
    updateRemaining();
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
