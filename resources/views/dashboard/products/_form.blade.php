@php
    $isEdit = isset($product);
    $item = $product ?? null;
    $measureUnit = old('measure_unit', $item->measure_unit ?? 'piece');
    $entry = $item
        ? \App\Support\SaleUnits::toEntryValues($item, $measureUnit)
        : ['purchase_price' => old('purchase_price', ''), 'sale_price' => old('sale_price', ''), 'stock' => old('stock', 0)];
    if (old('purchase_price') !== null) {
        $entry['purchase_price'] = old('purchase_price');
        $entry['sale_price'] = old('sale_price');
        $entry['stock'] = old('stock', 0);
    }
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
            <h4><i class="fa fa-balance-scale"></i> وحدة التسعير والمخزون</h4>
            <input type="hidden" name="measure_unit" id="measure_unit" value="{{ $measureUnit }}">
            <div class="unit-switch" id="measure_unit_switch" role="group" aria-label="وحدة القياس">
                <button type="button" class="unit-switch-btn" data-unit="piece">
                    <i class="fa fa-cube"></i>
                    <strong>بالحبة</strong>
                    <small>قطعة واحدة</small>
                </button>
                <button type="button" class="unit-switch-btn" data-unit="carton">
                    <i class="fa fa-archive"></i>
                    <strong>بالكرتونة</strong>
                    <small>عبوة كاملة</small>
                </button>
                <button type="button" class="unit-switch-btn" data-unit="kilo">
                    <i class="fa fa-dashboard"></i>
                    <strong>بالكيلو</strong>
                    <small>وزن عشري</small>
                </button>
            </div>
            <div class="sale-mode-help" id="measure_unit_help"></div>
        </div>

        <div class="product-form-section">
            <h4><i class="fa fa-money"></i> الأسعار والمخزون</h4>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label id="label_purchase_price">سعر الشراء <span class="text-danger">*</span></label>
                        <input type="number" name="purchase_price" id="purchase_price" step="0.001" min="0" class="form-control" required
                               value="{{ $entry['purchase_price'] }}">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label id="label_sale_price">سعر البيع <span class="text-danger">*</span></label>
                        <input type="number" name="sale_price" id="sale_price" step="0.001" min="0" class="form-control" required
                               value="{{ $entry['sale_price'] }}">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label id="label_stock">المخزون <span class="text-danger">*</span></label>
                        <input type="number" name="stock" id="stock" step="0.001" min="0" class="form-control" required
                               value="{{ $entry['stock'] }}">
                    </div>
                </div>
            </div>
            <div class="unit-live-preview" id="unit_live_preview"></div>
        </div>

        <div class="product-form-section" id="sale_mode_section">
            <h4><i class="fa fa-shopping-cart"></i> طريقة البيع في الطلبات</h4>
            <div class="form-group">
                <label>طريقة البيع</label>
                <select name="sale_mode" id="sale_mode" class="form-control">
                    <option value="flexible" {{ old('sale_mode', $item->sale_mode ?? 'flexible') === 'flexible' ? 'selected' : '' }}>بيع مرن: حبة + نصف كرتونة + كرتونة كاملة</option>
                    <option value="piece_only" {{ old('sale_mode', $item->sale_mode ?? '') === 'piece_only' ? 'selected' : '' }}>بالحبة فقط</option>
                    <option value="bulk_only" {{ old('sale_mode', $item->sale_mode ?? '') === 'bulk_only' ? 'selected' : '' }}>بالكرتونة كاملة فقط</option>
                </select>
                <div class="sale-mode-help" id="sale_mode_help"></div>
            </div>
            <div class="form-group" id="pieces_per_carton_group">
                <label>كم حبة داخل الكرتونة؟ <span class="text-danger">*</span> <small class="text-muted">(تحدده يدوياً لكل منتج)</small></label>
                <input type="number" name="pieces_per_carton" id="pieces_per_carton" step="1" min="1"
                       class="form-control" value="{{ old('pieces_per_carton', $item->pieces_per_carton ?? 12) }}" required>
                <small class="text-muted" id="pieces_per_carton_hint"></small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="product-form-section">
            <h4><i class="fa fa-camera"></i> صورة المنتج</h4>

            <div class="product-image-box">
                <img src="{{ $isEdit ? $product->image_path : \App\Models\Product::defaultImageUrl() }}"
                     class="img-thumbnail image-preview" alt="معاينة"
                     onerror="this.onerror=null;this.src='{{ \App\Models\Product::defaultImageUrl() }}';">

                <div class="product-image-actions">
                    <button type="button" class="btn btn-primary btn-block" id="btn_capture_camera">
                        <i class="fa fa-camera"></i> تصوير من الجوال
                    </button>
                    <button type="button" class="btn btn-default btn-block" id="btn_pick_gallery">
                        <i class="fa fa-folder-open"></i> اختيار من المعرض
                    </button>
                </div>

                {{-- الحقل الفعلي المرسل مع النموذج --}}
                <input type="file" name="image" id="product_image" class="image product-image-input"
                       accept="image/*" style="display:none">

                {{-- يفتح كاميرا الجوال مباشرة (الخلفية) --}}
                <input type="file" id="product_image_camera" class="product-image-input"
                       accept="image/*" capture="environment" style="display:none">

                <p class="product-image-hint">
                    <i class="fa fa-mobile"></i>
                    من الجوال: اضغط «تصوير من الجوال» لفتح الكاميرا مباشرة، أو اختر صورة من المعرض.
                </p>
                <p class="product-image-filename text-muted" id="product_image_filename" style="display:none;"></p>
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
