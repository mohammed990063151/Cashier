@php
    $fx = app(\App\Services\CurrencyService::class);
    $fxEnabled = $fx->enabled();
    $fxRate = $fx->rate();
@endphp

@if($fxEnabled)
<button type="button" class="fx-calc-fab" id="fx-calc-toggle" title="حاسبة الدولار">
    <i class="fa fa-calculator"></i> حاسبة $
</button>

<div class="fx-calc-panel" id="fx-calc-panel">
    <div class="fx-calc-panel-head">
        حاسبة العملة
        <small style="display:block;font-weight:600;opacity:.9;margin-top:2px;">{{ $fx->rateLabel() }}</small>
    </div>
    <div class="fx-calc-panel-body">
        <label>جنيه سوداني → دولار</label>
        <input type="number" id="fx-in-sdg" class="form-control" min="0" step="1" placeholder="مثال: 45000">
        <button type="button" class="btn btn-success" id="fx-btn-sdg">حوّل إلى دولار</button>

        <label>دولار → جنيه سوداني</label>
        <input type="number" id="fx-in-usd" class="form-control" min="0" step="0.01" placeholder="مثال: 10">
        <button type="button" class="btn btn-primary" id="fx-btn-usd">حوّل إلى جنيه</button>

        <div class="fx-calc-out" id="fx-calc-out">أدخل مبلغاً للتحويل</div>
        <a href="{{ route('dashboard.exchange-rates.index') }}" class="btn btn-default btn-block" style="margin:0;">
            <i class="fa fa-line-chart"></i> تحديث سعر الدولار
        </a>
    </div>
</div>
@endif
