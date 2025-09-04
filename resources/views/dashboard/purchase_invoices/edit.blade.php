@extends('layouts.dashboard.app')
@push('styles')
<style>
    /* تحسين تجاوب الصفحة */
    @media (max-width: 767px) {
        .content-wrapper .row {
            flex-direction: column;
        }

        .table-responsive {
            overflow-x: auto;
        }
    }

    .shadow-sm {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0069d9;
        border-color: #0062cc;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #fff;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
        color: #fff;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: #fff;
    }

</style>
@endpush

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>تعديل فاتورة شراء</h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li><a href="{{ route('dashboard.purchase-invoices.index') }}">فواتير الشراء</a></li>
            <li class="active">تعديل الفاتورة</li>
        </ol>
    </section>

    <section class="content">

        <div class="row flex-wrap">

            <!-- العمود الأساسي -->
            <div class="col-md-8 col-sm-12 mb-3">

                <div class="box box-primary shadow-sm">

                    <div class="box-header bg-primary text-white p-2">
                        <h3 class="box-title">بيانات الفاتورة</h3>
                    </div>

                    <div class="box-body p-3">

                        @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                        <div class="alert alert-error">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('dashboard.purchase-invoices.update', $invoice->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- المورد -->
                            <div class="form-group mb-3">
                                <label for="supplier_id">المورد</label>
                                <select name="supplier_id" id="supplier_id" class="form-control" required>
                                    <option value="">اختر المورد</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ $invoice->supplier_id == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- المنتجات -->
                            <h5 class="mt-3">المنتجات</h5>
                            <div class="table-responsive">
                                <!-- مكان عرض رسالة الخطأ -->
                                <div id="paidAlert" class="alert alert-danger" style="display:none;">
                                    ⚠️ المبلغ المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة!
                                </div>

                                <table class="table table-bordered table-striped" id="itemsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>المنتج</th>
                                            <th>الكمية</th>
                                            <th>السعر</th>
                                            <th>الإجمالي</th>
                                            <th>حذف</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->items as $index => $item)
                                        <tr>
                                            <td>
                                                <select name="items[{{ $index }}][product_id]" class="form-control" required>
                                                    <option value="">اختر المنتج</option>
                                                    @foreach($products as $product)
                                                    <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" value="{{ $item->quantity }}" min="1" required></td>
                                            <td><input type="number" name="items[{{ $index }}][price]" class="form-control price" value="{{ $item->price }}" min="0" step="1" required></td>
                                            <td class="row-total">{{ $item->subtotal }}</td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row">✖</button></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <button type="button" id="addRow" class="btn btn-success mb-3">➕ إضافة صف</button>

                            <!-- الإجمالي -->
                            <div class="form-group">
                                <label>إجمالي الفاتورة:</label>
                                <strong id="invoiceTotal">{{ $invoice->total }}</strong> ج.س
                            </div>

                            <!-- المدفوع -->
                            <div class="form-group">
                                <label for="paid">المبلغ المدفوع:</label>
                                <input type="number" name="paid" id="paid" class="form-control" value="{{ $invoice->paid }}" min="0" step="1">
                                <small id="paidError" class="text-danger" style="display:none;">
                                    ⚠️ المبلغ المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة
                                </small>
                            </div>

                            <!-- المتبقي -->
                            <div class="form-group">
                                <label>المتبقي:</label>
                                <strong id="remaining">{{ $invoice->remaining }}</strong> ج.س
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                                <a href="{{ route('dashboard.purchase-invoices.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>

                        </form>

                    </div><!-- end box-body -->

                </div><!-- end box -->

            </div><!-- end col -->

            <!-- العمود الجانبي -->
            <div class="col-md-4 col-sm-12 mb-3">
                <div class="box box-info shadow-sm">

                    <div class="box-header bg-info text-white p-2">
                        <h3 class="box-title">معلومات إضافية</h3>
                    </div>

                    <div class="box-body p-3">
                        <p>يمكنك إضافة المورد والمنتجات هنا.</p>
                        <p>سيتم حساب الإجمالي تلقائياً لكل منتج والفاتورة.</p>

                        <hr />

                        <h4>شرح تحديث سعر الشراء المتوسط للمنتج</h4>
                        <p>
                            عند شراء كميات جديدة من منتج موجود مسبقاً في المخزون، نستخدم <strong>سعر الشراء المتوسط</strong> لتحديث سعر المنتج، بدل أن نحتفظ بالسعر القديم فقط. هذا يساعدنا على معرفة تكلفة المنتج بدقة عند البيع مستقبلاً.
                        </p>

                        <h5>مثال توضيحي:</h5>
                        <ul>
                            <li>المخزون القديم: 10 قطع × 100 جنيه سوداني = 1000 جنيه سوداني</li>
                            <li>الشراء الجديد: 5 قطع × 120 جنيه سوداني = 600 جنيه سوداني</li>
                        </ul>

                        <h5>الحساب:</h5>
                        <ol>
                            <li>نجمع قيمة المخزون القديم مع قيمة الشراء الجديد: 1000 + 600 = 1600 جنيه سوداني</li>
                            <li>نجمع الكميات القديمة والجديدة: 10 + 5 = 15 قطعة</li>
                            <li>نحسب سعر الشراء المتوسط الجديد لكل قطعة: 1600 ÷ 15 = 106.67 جنيه سوداني</li>
                        </ol>

                        <p>🔹 <strong>النتيجة:</strong></p>
                        <p>كل قطعة الآن تُحسب بسعر 106.67 جنيه سوداني. هذا السعر يُستخدم في تقارير المخزون وحساب الأرباح عند البيع.</p>

                        <h5>فوائد هذه الطريقة:</h5>
                        <ul>
                            <li>معرفة تكلفة دقيقة لكل منتج في المخزون.</li>
                            <li>حساب أرباح البيع بشكل صحيح.</li>
                            <li>تجنب الالتباس عند اختلاف أسعار الشراء بين دفعات مختلفة.</li>
                        </ul>
                    </div>

                </div>
            </div><!-- end col -->

        </div><!-- end row -->

    </section><!-- end content -->

</div><!-- end content-wrapper -->


@push('scripts')
<script>
    // rowIndex يبدأ بعدد العناصر الموجودة مسبقًا
let rowIndex = {{ isset($invoice) ? count($invoice->items) : 0 }};

// إضافة صف جديد
document.getElementById('addRow').addEventListener('click', function() {
    let tableBody = document.querySelector('#itemsTable tbody');
    let newRow = document.createElement('tr');

    newRow.innerHTML = `
        <td>
            <select name="items[${rowIndex}][product_id]" class="form-control product-select" required>
                <option value="">اختر المنتج</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-old-price="{{ $product->purchase_price ?? 0 }}">
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control quantity" value="1" min="1" required></td>
        <td>
            <input type="number" name="items[${rowIndex}][price]" class="form-control price" value="0" min="0" step="0.01" required>
            <small class="text-info old-price-text">السعر القديم: 0</small>
        </td>
        <td class="row-total">0</td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row">✖</button></td>
    `;
    tableBody.appendChild(newRow);

    // تحديث السعر القديم عند اختيار المنتج
    let select = newRow.querySelector('.product-select');
    let oldPriceText = newRow.querySelector('.old-price-text');

    select.addEventListener('change', function() {
        let oldPrice = parseFloat(select.selectedOptions[0].dataset.oldPrice) || 0;
        oldPriceText.innerText = "السعر القديم: " + oldPrice;
    });

    rowIndex++; // زيادة المؤشر للصف التالي
});


    // تحديث الإجمالي والمتبقي
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('price')) {
            let row = e.target.closest('tr');
            let qty = parseFloat(row.querySelector('.quantity').value) || 0;
            let price = parseFloat(row.querySelector('.price').value) || 0;
            let total = qty * price;
            row.querySelector('.row-total').innerText = total.toFixed(2);
            updateInvoiceTotal();
        }
        if (e.target.id === 'paid') {
            updateRemaining();
        }
    });

    // حذف صف
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            updateInvoiceTotal();
        }
    });

    function updateInvoiceTotal() {
        let totals = document.querySelectorAll('.row-total');
        let sum = 0;
        totals.forEach(td => sum += parseFloat(td.innerText) || 0);
        document.getElementById('invoiceTotal').innerText = sum.toFixed(2);
        updateRemaining();
    }

    function updateRemaining() {
        let total = parseFloat(document.getElementById('invoiceTotal').innerText) || 0;
        let paidInput = document.getElementById('paid');
        let paid = parseFloat(paidInput.value) || 0;

        let errorMsg = document.getElementById('paidError');

        if (paid > total) {
            errorMsg.style.display = 'block'; // إظهار الرسالة
            paidInput.value = total.toFixed(2); // تعديل المبلغ للحد الأقصى
            paid = total;
        } else {
            errorMsg.style.display = 'none'; // إخفاء الرسالة إذا كان صحيح
        }

        let remaining = Math.max(total - paid, 0);
        document.getElementById('remaining').innerText = remaining.toFixed(2);

    }



    // أول تحميل: حساب المتبقي من القيم القديمة
    updateRemaining();

</script>
@endpush
<!-- Script لإضافة الصفوف وحساب الإجمالي -->
<script>
    let rowIndex = {
        {
            count($invoice - > items)
        }
    };

    // إضافة صف جديد
    document.getElementById('addRow').addEventListener('click', function() {
        let tableBody = document.querySelector('#itemsTable tbody');
        let newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-control" required>
                    <option value="">اختر المنتج</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control quantity" value="1" min="1" required></td>
            <td><input type="number" name="items[${rowIndex}][price]" class="form-control price" value="0" min="0" step="0.01" required></td>
            <td class="row-total">0</td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">✖</button></td>
        `;
        tableBody.appendChild(newRow);
        rowIndex++;
    });

    // تحديث الإجمالي والمتبقي
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('price')) {
            let row = e.target.closest('tr');
            let qty = parseFloat(row.querySelector('.quantity').value) || 0;
            let price = parseFloat(row.querySelector('.price').value) || 0;
            let total = qty * price;
            row.querySelector('.row-total').innerText = total.toFixed(2);
            updateInvoiceTotal();
        }
        if (e.target.id === 'paid') {
            updateRemaining();
        }
    });

    // حذف صف
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            updateInvoiceTotal();
        }
    });

    function updateInvoiceTotal() {
        let totals = document.querySelectorAll('.row-total');
        let sum = 0;
        totals.forEach(td => sum += parseFloat(td.innerText) || 0);
        document.getElementById('invoiceTotal').innerText = sum.toFixed(2);
        updateRemaining();
    }

    function updateRemaining() {
        let total = parseFloat(document.getElementById('invoiceTotal').innerText) || 0;
        let paidInput = document.getElementById('paid');
        let paid = parseFloat(paidInput.value) || 0;

        let errorMsg = document.getElementById('paidError');

        if (paid > total) {
            errorMsg.style.display = 'block'; // إظهار الرسالة
            paidInput.value = total.toFixed(2); // تعديل المبلغ للحد الأقصى
            paid = total;
        } else {
            errorMsg.style.display = 'none'; // إخفاء الرسالة إذا كان صحيح
        }

        let remaining = Math.max(total - paid, 0);
        document.getElementById('remaining').innerText = remaining.toFixed(2);

    }



    // أول تحميل: حساب المتبقي من القيم القديمة
    updateRemaining();

</script>



@endsection
