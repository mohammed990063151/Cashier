<div class="modal fade" id="quickProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0f4c81,#1a73b8);color:#fff;border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-plus-circle"></i> إضافة منتج جديد</h4>
            </div>
            <form id="quickProductForm">
                <div class="modal-body">
                    <div id="quickProductAlert" class="alert alert-danger" style="display:none;"></div>
                    <div class="form-group">
                        <label>القسم <span class="text-danger">*</span></label>
                        <select name="category_id" id="qpCategory" class="form-control" required>
                            <option value="">— اختر القسم —</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>اسم المنتج <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="qpName" class="form-control" required placeholder="مثال: شاي أحمر 250غ">
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>سعر الشراء <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" min="0" name="purchase_price" id="qpPurchasePrice" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>سعر البيع</label>
                                <input type="number" step="0.001" min="0" name="sale_price" id="qpSalePrice" class="form-control" placeholder="يُحسب تلقائياً">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>وحدة القياس</label>
                                <select name="measure_unit" id="qpMeasureUnit" class="form-control">
                                    <option value="piece">بالحبة</option>
                                    <option value="carton">بالكرتونة</option>
                                    <option value="kilo">بالكيلو</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>حبات العبوة</label>
                                <input type="number" min="1" name="pieces_per_carton" id="qpBulk" class="form-control" value="12">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>طريقة البيع</label>
                                <select name="sale_mode" id="qpSaleMode" class="form-control">
                                    <option value="flexible">مرن (حبة + عبوة)</option>
                                    <option value="piece_only">بالحبة فقط</option>
                                    <option value="bulk_only">بالعبوة فقط</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small"><i class="fa fa-info-circle"></i> يُضاف للقائمة مباشرة ويُختار في السطر الحالي.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="qpSubmitBtn"><i class="fa fa-check"></i> حفظ وإضافة للفاتورة</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
