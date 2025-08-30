@extends('layouts.dashboard.app')

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

        <div class="row">

            <div class="col-md-8">

                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title">بيانات الفاتورة</h3>
                    </div>

                    <div class="box-body">

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
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
                            <h5>المنتجات</h5>
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
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
                                            <td>
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity"
                                                       value="{{ $item->quantity }}" min="1" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $index }}][price]" class="form-control price"
                                                       value="{{ $item->price }}" min="0" step="0.01" required>
                                            </td>
                                            <td class="row-total">{{ $item->subtotal }}</td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-row">✖</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <button type="button" id="addRow" class="btn btn-secondary mb-3">➕ إضافة صف</button>

                            <!-- الإجمالي -->
                            <div class="form-group">
                                <label>إجمالي الفاتورة:</label>
                                <strong id="invoiceTotal">{{ $invoice->total }}</strong> ريال
                            </div>

                            <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                            <a href="{{ route('dashboard.purchase-invoices.index') }}" class="btn btn-default">إلغاء</a>
                        </form>

                    </div><!-- end box-body -->

                </div><!-- end box -->

            </div><!-- end col -->

            <div class="col-md-4">
                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title">معلومات إضافية</h3>
                    </div>

                    <div class="box-body">
                        <p>يمكنك تعديل المورد والمنتجات في الفاتورة.</p>
                        <p>سيتم حساب الإجمالي تلقائياً لكل منتج والفاتورة.</p>
                    </div>

                </div>
            </div><!-- end col -->

        </div><!-- end row -->

    </section><!-- end content -->

</div><!-- end content-wrapper -->

<!-- Script لإضافة الصفوف وحساب الإجمالي -->
<script>
    let rowIndex = {{ count($invoice->items) }};

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

    // تحديث الإجمالي
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('price')) {
            let row = e.target.closest('tr');
            let qty = parseFloat(row.querySelector('.quantity').value) || 0;
            let price = parseFloat(row.querySelector('.price').value) || 0;
            let total = qty * price;
            row.querySelector('.row-total').innerText = total.toFixed(2);
            updateInvoiceTotal();
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
    }
</script>

@endsection
