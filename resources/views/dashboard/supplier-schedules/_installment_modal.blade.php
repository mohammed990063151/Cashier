<div class="modal fade" id="supplierInstallmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="supplierInstallmentForm">
                @csrf
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                    <h4 class="modal-title" style="color:#fff;"><i class="fa fa-calendar"></i> جدولة — <span id="siInvoiceNumber"></span></h4>
                </div>
                <div class="modal-body">
                    <p>المتبقي: <strong id="siRemaining" class="text-danger"></strong> ج.س</p>
                    <table class="table table-bordered">
                        <thead><tr><th>المبلغ</th><th>الاستحقاق</th><th></th></tr></thead>
                        <tbody id="siInstallmentRows"></tbody>
                    </table>
                    <button type="button" class="btn btn-default btn-sm" id="siAddRow"><i class="fa fa-plus"></i> قسط</button>
                    <button type="button" class="btn btn-info btn-sm" id="siSplitTwo"><i class="fa fa-magic"></i> قسطان متساويان</button>
                    <p class="text-danger" id="siSumError" style="display:none;margin-top:10px;"></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">حفظ الجدولة</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
