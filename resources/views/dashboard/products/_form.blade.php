@php
    $isEdit = isset($product);
    $item = $product ?? null;
@endphp

<div class="row">
    <div class="col-md-8">
        <div class="product-form-section">
            <h4><i class="fa fa-info-circle"></i> البيانات الأساسية</h4>
            <div class="form-group">
                <label>القسم <span class="text-danger">*</span></label>
                <select name="category_id" class="form-control" required>
                    <option value="">— اختر القسم —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ (int) old('category_id', $item->category_id ?? 0) === (int) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>اسم المنتج <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required
                       value="{{ old('name', $item->name ?? '') }}" placeholder="مثال: ابريق شاي">
            </div>
            <div class="form-group">
                <label>الوصف</label>
                <textarea name="description" class="form-control" rows="3" placeholder="وصف مختصر (اختياري)">{{ old('description', $item->description ?? '') }}</textarea>
            </div>
        </div>

        <div class="product-form-section">
            <h4><i class="fa fa-money"></i> الأسعار والمخزون</h4>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>سعر الشراء <span class="text-danger">*</span></label>
                        <input type="number" name="purchase_price" step="1" min="0" class="form-control" required
                               value="{{ old('purchase_price', $item->purchase_price ?? '') }}">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>سعر البيع (للحبة) <span class="text-danger">*</span></label>
                        <input type="number" name="sale_price" step="1" min="0" class="form-control" required
                               value="{{ old('sale_price', $item->sale_price ?? '') }}">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>المخزون (بالحبة) <span class="text-danger">*</span></label>
                        <input type="number" name="stock" step="1" min="0" class="form-control" required
                               value="{{ old('stock', $item->stock ?? 0) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="product-form-section">
            <h4><i class="fa fa-shopping-cart"></i> طريقة البيع في الطلبات</h4>
            <div class="form-group">
                <label>طريقة البيع <span class="text-danger">*</span></label>
                <select name="sale_mode" id="sale_mode" class="form-control" required>
                    <option value="piece_only" {{ old('sale_mode', $item->sale_mode ?? '') === 'piece_only' ? 'selected' : '' }}>بالحبة فقط</option>
                    <option value="bulk_only" {{ old('sale_mode', $item->sale_mode ?? '') === 'bulk_only' ? 'selected' : '' }}>بالعبوة/كرتون كامل فقط</option>
                    <option value="flexible" {{ old('sale_mode', $item->sale_mode ?? 'flexible') === 'flexible' ? 'selected' : '' }}>بيع مرن (حبة + 3 + 6 + دستة + عبوة)</option>
                </select>
                <div class="sale-mode-help" id="sale_mode_help"></div>
            </div>
            <div class="form-group" id="pieces_per_carton_group">
                <label>عدد الحبات في العبوة/الكرتون</label>
                <input type="number" name="pieces_per_carton" id="pieces_per_carton" step="1" min="1"
                       class="form-control" value="{{ old('pieces_per_carton', $item->pieces_per_carton ?? 12) }}">
                <small class="text-muted" id="pieces_per_carton_hint"></small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="product-form-section">
            <h4><i class="fa fa-image"></i> الصورة</h4>
            <input type="file" name="image" class="form-control image" accept="image/*">
            <div class="text-center" style="margin-top:12px;">
                <img src="{{ $isEdit ? $product->image_path : \App\Models\Product::defaultImageUrl() }}"
                     class="img-thumbnail image-preview" style="max-width:100%;max-height:180px;" alt="معاينة">
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa fa-save"></i> {{ $isEdit ? 'حفظ التعديلات' : 'إضافة المنتج' }}
            </button>
            <a href="{{ route('dashboard.products.index') }}" class="btn btn-default btn-block">إلغاء</a>
        </div>
    </div>
</div>
