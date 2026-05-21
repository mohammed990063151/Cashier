<div class="row" style="margin-top:12px;">
    <div class="col-md-3 col-sm-6">
        <label class="filter-label">حالة الجدولة</label>
        <select name="schedule_status" class="form-control input-lg">
            <option value="all" {{ $st === 'all' ? 'selected' : '' }}>الكل</option>
            <option value="alert" {{ $st === 'alert' ? 'selected' : '' }}>يحتاج متابعة</option>
            <option value="due_today" {{ $st === 'due_today' ? 'selected' : '' }}>مستحق اليوم</option>
            <option value="overdue" {{ $st === 'overdue' ? 'selected' : '' }}>متأخر</option>
            <option value="due_soon" {{ $st === 'due_soon' ? 'selected' : '' }}>قريب</option>
            <option value="no_date" {{ $st === 'no_date' ? 'selected' : '' }}>بدون تقسيط</option>
        </select>
    </div>
    <div class="col-md-2 col-sm-6">
        <label class="filter-label">من تاريخ</label>
        <input type="date" name="due_from" class="form-control input-lg" value="{{ $filters['due_from'] ?? '' }}">
    </div>
    <div class="col-md-2 col-sm-6">
        <label class="filter-label">إلى تاريخ</label>
        <input type="date" name="due_to" class="form-control input-lg" value="{{ $filters['due_to'] ?? '' }}">
    </div>
    <div class="col-md-2 col-sm-6">
        <label class="filter-label">ترتيب</label>
        <select name="sort" class="form-control input-lg">
            <option value="due_asc" {{ $sort === 'due_asc' ? 'selected' : '' }}>الموعد</option>
            <option value="remaining_desc" {{ $sort === 'remaining_desc' ? 'selected' : '' }}>المتبقي</option>
        </select>
    </div>
</div>
