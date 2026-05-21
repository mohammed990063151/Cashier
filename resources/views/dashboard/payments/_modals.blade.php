<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.payments.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="background:#00a65a;color:#fff;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                    <h4 class="modal-title" id="paymentModalTitle" style="font-size:20px;">إضافة دفعة</h4>
                </div>
                <div class="modal-body" style="font-size:16px;">
                    <input type="hidden" name="order_id" id="paymentOrderId">
                    <div class="form-group">
                        <label style="font-size:16px;font-weight:700;">المبلغ (ج.س)</label>
                        <input type="number" step="0.01" name="amount" class="form-control input-lg" required style="font-size:22px;height:auto;">
                        <p id="paymentAmountHint" class="remaining-text text-danger" style="font-size:15px;margin-top:8px;"></p>
                    </div>
                    <div class="form-group">
                        <label style="font-size:16px;">طريقة الدفع</label>
                        <select name="method" id="paymentMethod" class="form-control input-lg">
                            <option value="cash">كاش</option>
                            <option value="bank">تحويل بنكي</option>
                        </select>
                    </div>
                    <div class="form-group bank-receipt-field" style="display:none;">
                        <label>صور إشعار التحويل البنكي <span class="text-danger">*</span></label>
                        <input type="file" name="bank_receipts[]" id="paymentBankReceipt" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                        <small class="text-muted">يمكنك اختيار عدة صور دفعة واحدة (Ctrl أو مطوّل الملفات)</small>
                        <div id="paymentReceiptPreview" class="receipt-preview-row"></div>
                    </div>
                    <div class="form-group">
                        <label style="font-size:16px;">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2" style="font-size:15px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-check"></i> تأكيد الدفعة</button>
                    <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentLogModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="font-size:18px;"><i class="fa fa-list"></i> سجل المدفوعات</h4>
            </div>
            <div class="modal-body" id="paymentLogModalBody">
                <p class="text-muted text-center">جاري التحميل...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editPaymentForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="log_order" id="editPaymentLogOrder" value="">
                @if(request('client_id'))<input type="hidden" name="client_id" value="{{ request('client_id') }}">@endif
                @if(request('order_id'))<input type="hidden" name="order_id" value="{{ request('order_id') }}">@endif
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('payment_status'))<input type="hidden" name="payment_status" value="{{ request('payment_status') }}">@endif
                <div class="modal-header" style="background:#f39c12;color:#fff;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                    <h4 class="modal-title" style="font-size:18px;"><i class="fa fa-edit"></i> تعديل دفعة — <span id="editPaymentOrderNumber"></span></h4>
                </div>
                <div class="modal-body" style="font-size:15px;">
                    <div class="form-group">
                        <label>المبلغ (ج.س)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="editPaymentAmount" class="form-control input-lg" required>
                        <small id="editPaymentMaxHint" class="text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>طريقة الدفع</label>
                        <select name="method" id="editPaymentMethod" class="form-control input-lg">
                            <option value="cash">كاش</option>
                            <option value="bank">تحويل بنكي</option>
                        </select>
                    </div>
                    <div class="form-group" id="editBankReceiptGroup" style="display:none;">
                        <label>إشعارات التحويل البنكي</label>
                        <div id="editCurrentReceipt" class="receipt-preview-row" style="margin-bottom:8px;"></div>
                        <input type="file" name="bank_receipts[]" id="editPaymentReceipt" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                        <small class="text-muted">اتركه فارغاً للإبقاء على الصور الحالية، أو أضف صوراً جديدة (تُضاف للسجل)</small>
                        <div id="editReceiptPreview" class="receipt-preview-row"></div>
                    </div>
                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea name="notes" id="editPaymentNotes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning btn-lg"><i class="fa fa-save"></i> حفظ التعديل</button>
                    <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

