@extends('layouts.dashboard.app')

@section('content')

@include('dashboard.clients.orders._order_units_styles')

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
                                                <th>طريقة البيع</th>
                                                <th>المخزون</th>
                                                <th>السعر</th>
                                                <th>إضافة</th>
                                            </tr>

                                            @foreach ($category->products as $product)
                                            @php
                                                $stockLabel = app(\App\Services\ProductService::class)->stockDisplay($product);
                                                $bulkSize = max(1, (int) ($product->pieces_per_carton ?? 12));
                                                $alreadyInOrder = in_array($product->id, $order->products->pluck('id')->toArray());
                                            @endphp
                                            <tr>
                                                <td>{{ $product->name }}</td>
                                                <td><small>{{ \App\Support\SaleUnits::saleModeLabel($product->sale_mode ?? 'flexible') }}</small></td>
                                                <td><small>{{ $stockLabel }}</small></td>
                                                <td>{{ \App\Support\DecimalMath::display($product->sale_price) }}</td>
                                                <td>
                                                    <a href="" id="product-{{ $product->id }}"
                                                       data-name="{{ $product->name }}"
                                                       data-id="{{ $product->id }}"
                                                       data-price="{{ $product->sale_price }}"
                                                       data-stock="{{ $product->stock }}"
                                                       data-bulk-size="{{ $bulkSize }}"
                                                       data-sale-mode="{{ $product->sale_mode ?? 'flexible' }}"
                                                       data-measure-unit="{{ $product->measure_unit ?? 'piece' }}"
                                                       class="btn {{ $alreadyInOrder ? 'btn-default disabled' : 'btn-success add-product-btn' }} btn-sm">
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
                        <div id="error-alert" class="alert alert-danger text-center" style="white-space:pre-line;">
                            {{ session('error') }}
                        </div>
                        @endif
                        @include('partials._errors')

                        <form action="{{ route('dashboard.clients.orders.update', ['order' => $order->id, 'client' => $client->id]) }}" method="post">

                            {{ csrf_field() }}
                            {{ method_field('put') }}

                            <p class="text-muted" style="margin-bottom:10px;">
                                وحدات البيع تُحدَّد من إعداد المنتج (حبة فقط / عبوة فقط / مرن).
                            </p>

                            <table class="table table-hover order-list-table">
                                <thead>
                                    <tr>
                                        <th>المنتج</th>
                                        <th colspan="2">الوحدات والأسعار</th>
                                        <th>الإجمالي</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody class="order-list">
                                    @foreach ($order->products as $product)
                                        @include('dashboard.clients.orders._order_line_edit', ['product' => $product])
                                    @endforeach
                                </tbody>

                            </table><!-- نهاية الجدول -->

                            <h4>الإجمالي: <span class="total-price" style="color:#046b0a;font-weight:bold;">{{ number_format($order->total_price, 2) }}</span></h4>
                            <div class="form-group">
                                <label for="invoice_discount">خصم الفاتورة</label>
                                <input type="number" name="invoice_discount" id="invoice_discount" class="form-control" min="0" step="1"
                                       value="{{ $order->invoice_discount ?? 0 }}">
                            </div>
                            <h4>الإجمالي بعد الخصم:
                                <span id="discounted-total" style="color:#007bff;font-weight:bold;">
                                    {{ number_format($order->total_price - ($order->invoice_discount ?? 0), 2) }}
                                </span>
                            </h4>
                            <div class="form-group">
                                <label for="paid_at_sale">المدفوع الآن</label>
                                <input type="number" name="paid_at_sale" id="paid_at_sale" class="form-control" min="0" step="1" value="{{ $order->paid_at_sale }}">
                                <small class="text-muted">0 = لم يُدفع شيء عند الطلب؛ يمكن تحصيل المتبقي لاحقاً من صفحة المدفوعات.</small>
                            </div>
                            <div class="form-group">
                                <label>المتبقي على العميل:</label>
                                <p id="remaining-display" class="form-control-static text-danger" style="font-size:18px;font-weight:bold;margin:0;">{{ number_format($order->remaining, 2) }}</p>
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
                                                    @php $line = app(\App\Services\OrderFinancialService::class)->formatProductSaleLine($product); @endphp
                                                    <tr>
                                                        <td class="fw-bold text-start">{{ $product->name }}</td>
                                                        <td>{{ $line['quantity'] }}</td>
                                                        <td class="text-success fw-bold">{{ $line['price'] }}</td>
                                                        <td class="text-primary fw-bold">{{ number_format($line['line_total'], 2) }} ج.س</td>
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
                                                <strong>المدفوع :</strong> {{ number_format($order->paid_at_sale,2) }} ج.س
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
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector('form');
    const totalPriceEl = document.querySelector('.total-price');
    const discountEl = document.getElementById('paid_at_sale');
    const invoiceDiscountEl = document.getElementById('invoice_discount');

    function parseNumber(str) {
        return parseFloat(String(str).replace(/,/g, '')) || 0;
    }

    if (typeof calculateTotal === 'function') {
        calculateTotal();
    }

    form.addEventListener('submit', function(e) {
        if (typeof calculateTotal === 'function') {
            calculateTotal();
        }

        const total = parseNumber(totalPriceEl.textContent);
        const invoiceDiscount = parseNumber(invoiceDiscountEl.value);
        const paid = parseNumber(discountEl.value);
        const afterDiscount = Math.max(total - invoiceDiscount, 0);

        if (paid > afterDiscount) {
            e.preventDefault();
            alert("المدفوع لا يمكن أن يكون أكبر من إجمالي الطلب بعد الخصم!");
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
