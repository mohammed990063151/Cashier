<div class="modal fade" id="installmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="installmentForm">
                @csrf
                <div class="modal-header" style="background:#3c8dbc;color:#fff;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-calendar-plus-o"></i> تقسيط المتبقي</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong id="modalOrderTitle"></strong><br>
                        المتبقي: <strong id="modalRemaining">0</strong> ج.س — يجب أن يساوي مجموع الأقساط.
                    </div>

                    <div class="row" style="margin-bottom:15px;">
                        <div class="col-md-3">
                            <label>عدد الأقساط</label>
                            <input type="number" id="splitCount" class="form-control" min="1" max="24" value="3">
                        </div>
                        <div class="col-md-3">
                            <label>أول موعد</label>
                            <input type="date" id="splitStart" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>كل (يوم)</label>
                            <input type="number" id="splitInterval" class="form-control" min="1" value="30">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-warning btn-block" id="btnAutoSplit">
                                <i class="fa fa-magic"></i> تقسيم تلقائي
                            </button>
                        </div>
                    </div>

                    <table class="table table-bordered" id="installmentRowsTable">
                        <thead>
                            <tr>
                                <th width="35%">المبلغ</th>
                                <th width="35%">موعد السداد</th>
                                <th>ملاحظات</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="installmentRows"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-left"><strong>المجموع:</strong></td>
                                <td colspan="2"><strong id="installmentSum">0.00</strong> / <span id="installmentTarget">0.00</span> ج.س</td>
                            </tr>
                        </tfoot>
                    </table>
                    <button type="button" class="btn btn-default btn-sm" id="btnAddRow"><i class="fa fa-plus"></i> إضافة قسط</button>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> حفظ التقسيط</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">إغلاق</button>
                </div>
            </form>
        </div>
    </div>
</div>
