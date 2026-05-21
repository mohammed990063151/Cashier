@php
    $isEdit = isset($purchaseInvoice);
    $formAction = $isEdit
        ? route('dashboard.purchase-invoices.update', $purchaseInvoice)
        : route('dashboard.purchase-invoices.store');
    $oldItems = old('items', $isEdit
        ? $purchaseInvoice->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'purchase_unit' => $i->purchase_unit ?? 'piece',
            'entered_qty' => $i->entered_qty ?? $i->quantity,
            'price' => $i->price,
        ])->values()->all()
        : [['product_id' => '', 'purchase_unit' => 'piece', 'entered_qty' => 1, 'price' => 0]]);
@endphp

@if($errors->any())
<div class="alert alert-danger purchase-validation-alert">
    <strong><i class="fa fa-exclamation-triangle"></i> راجع الحقول التالية:</strong>
    <ul class="mb-0" style="margin-top:8px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form action="{{ $formAction }}" method="POST" id="purchaseInvoiceForm" class="purchase-invoice-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <div class="col-md-4 col-sm-6">
            <div class="form-group {{ $errors->has('supplier_id') ? 'has-error' : '' }}">
                <label class="pi-label"><i class="fa fa-truck"></i> المورد <span class="text-danger">*</span></label>
                <select name="supplier_id" id="supplier_id" class="form-control input-lg" required>
                    <option value="">— اختر المورد —</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchaseInvoice->supplier_id ?? '') == $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')<span class="help-block text-danger">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="form-group">
                <label class="pi-label"><i class="fa fa-calendar"></i> تاريخ الفاتورة</label>
                <input type="date" name="invoice_date" class="form-control input-lg"
                       value="{{ old('invoice_date', optional($purchaseInvoice->invoice_date ?? null)->format('Y-m-d') ?? date('Y-m-d')) }}">
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="pi-hint-box">
                <i class="fa fa-info-circle"></i>
                اختر المنتج ثم <strong>الوحدة</strong> (حبة / عبوة / دستة…) — الكمية تُحوَّل تلقائياً إلى حبات في المخزون.
            </div>
        </div>
    </div>

    <div class="clearfix" style="margin-bottom:8px;">
        <h4 class="pi-section-title pull-right" style="margin:0;border:none;"><i class="fa fa-cubes"></i> بنود الشراء</h4>
        <button type="button" class="btn btn-info pull-left" id="btnOpenQuickProduct" style="margin-top:4px;">
            <i class="fa fa-plus-circle"></i> منتج جديد
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered pi-items-table" id="purchaseItemsTable">
            <thead>
                <tr>
                    <th style="min-width:200px;">المنتج</th>
                    <th style="min-width:130px;">الوحدة</th>
                    <th style="width:100px;">الكمية</th>
                    <th style="width:120px;">سعر الوحدة</th>
                    <th style="width:110px;">الإجمالي</th>
                    <th style="width:50px;"></th>
                </tr>
            </thead>
            <tbody id="purchaseItemsBody">
                @foreach($oldItems as $idx => $item)
                @include('dashboard.purchase_invoices._item_row', ['index' => $idx, 'item' => $item, 'products' => $products])
                @endforeach
            </tbody>
        </table>
    </div>
    <button type="button" class="btn btn-success" id="addPurchaseRow"><i class="fa fa-plus"></i> إضافة منتج</button>

    <div class="row" style="margin-top:24px;">
        <div class="col-md-4">
            <div class="pi-summary-card">
                <label>إجمالي الفاتورة</label>
                <div class="pi-summary-value" id="invoiceTotalDisplay">0.00 <small>ج.س</small></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group {{ $errors->has('paid') ? 'has-error' : '' }}">
                <label class="pi-label">المدفوع الآن</label>
                <input type="number" step="0.01" min="0" name="paid" id="paidAmount" class="form-control input-lg"
                       value="{{ old('paid', $purchaseInvoice->paid ?? 0) }}">
                <span class="help-block">يُخصم من الخزينة مباشرة</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="pi-summary-card pi-remaining-card">
                <label>المتبقي على المورد</label>
                <div class="pi-summary-value text-danger" id="remainingDisplay">0.00 <small>ج.س</small></div>
            </div>
        </div>
    </div>

    <div id="paidAlert" class="alert alert-danger" style="display:none;">
        المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة.
    </div>

    @if(!$isEdit)
    <div id="installmentsSection" class="pi-installments-panel" style="display:none;">
        <h4 class="pi-section-title"><i class="fa fa-calendar-check-o"></i> جدولة سداد المتبقي للمورد</h4>
        <p class="text-muted">مجموع الأقساط يجب أن يساوي المتبقي بالضبط.</p>
        <table class="table table-bordered" id="installmentsTable">
            <thead>
                <tr>
                    <th>المبلغ (ج.س)</th>
                    <th>تاريخ الاستحقاق</th>
                    <th>ملاحظات</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="installmentsBody"></tbody>
        </table>
        <button type="button" class="btn btn-default" id="addInstallmentRow"><i class="fa fa-plus"></i> قسط</button>
        <button type="button" class="btn btn-info" id="splitRemainingBtn"><i class="fa fa-magic"></i> تقسيم المتبقي على قسطين</button>
        <p class="text-danger" id="installmentsSumHint" style="display:none;margin-top:10px;"></p>
    </div>
    @else
    <div class="alert alert-info">
        <i class="fa fa-calendar"></i>
        لجدولة أقساط السداد بعد الحفظ استخدم
        <a href="{{ route('dashboard.supplier-schedules.index', ['supplier_id' => $purchaseInvoice->supplier_id]) }}">جدولة سداد الموردين</a>.
    </div>
    @endif

    <div class="form-group" style="margin-top:16px;">
        <label class="pi-label">ملاحظات السداد (اختياري)</label>
        <textarea name="payment_notes" class="form-control" rows="2" placeholder="مثال: اتفاق سداد نهاية الشهر">{{ old('payment_notes', $purchaseInvoice->payment_notes ?? '') }}</textarea>
    </div>

    <div class="pi-form-actions">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> {{ $isEdit ? 'حفظ التعديلات' : 'حفظ الفاتورة' }}</button>
        <a href="{{ route('dashboard.purchase-invoices.index') }}" class="btn btn-default btn-lg">إلغاء</a>
    </div>
</form>

<template id="purchaseRowTemplate">
    @include('dashboard.purchase_invoices._item_row', ['index' => '__INDEX__', 'item' => [], 'products' => $products])
</template>

@include('dashboard.purchase_invoices._quick_product_modal', ['categories' => $categories ?? []])
