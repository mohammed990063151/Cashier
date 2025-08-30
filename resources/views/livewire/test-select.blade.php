<div>
    <h4>🧪 اختبار Select مع Livewire</h4>

    <select wire:model="filterCategory" class="form-control">
        <option value="all">جميع الحركات</option>
        <option value="sale">فواتير المبيعات</option>
        <option value="purchase">فواتير المشتريات</option>
        <option value="expense">المصروفات</option>
    </select>

    <p class="mt-3">📌 الفئة المختارة: <strong>{{ $filterCategory }}</strong></p>
</div>
