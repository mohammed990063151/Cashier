@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>سعر الدولار <small>مرجعية التضخم</small></h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">سعر الدولار</li>
        </ol>
    </section>

    <section class="content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @include('partials._errors')

        @php $stock = $holdings['stock']; $due = $holdings['receivables']; $owe = $holdings['payables']; @endphp
        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning fx-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-balance-scale"></i> أثر التضخم على ما عندك</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted" style="margin-top:0;">الجنيه ثابت. إذا زاد الدولار: «يومها» يبقى، و«الآن» ينقص. الفرق خسارة.</p>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th>البند</th>
                                        <th>جنيه</th>
                                        <th>$ يومها</th>
                                        <th>$ الآن</th>
                                        <th>الخسارة $</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>المخزون</td>
                                        <td>{{ number_format($stock['sdg'], 2) }}</td>
                                        <td>{{ number_format($stock['usd_then'], 4) }}</td>
                                        <td>{{ number_format($stock['usd_now'], 4) }}</td>
                                        <td class="{{ $stock['loss_usd'] > 0.0001 ? 'text-danger' : 'text-muted' }}">{{ number_format($stock['loss_usd'], 4) }}</td>
                                    </tr>
                                    <tr>
                                        <td>ديون العملاء</td>
                                        <td>{{ number_format($due['sdg'], 2) }}</td>
                                        <td>{{ number_format($due['usd_then'], 4) }}</td>
                                        <td>{{ number_format($due['usd_now'], 4) }}</td>
                                        <td class="{{ $due['loss_usd'] > 0.0001 ? 'text-danger' : 'text-muted' }}">{{ number_format($due['loss_usd'], 4) }}</td>
                                    </tr>
                                    <tr>
                                        <td>دين الموردين</td>
                                        <td>{{ number_format($owe['sdg'], 2) }}</td>
                                        <td>{{ number_format($owe['usd_then'], 4) }}</td>
                                        <td>{{ number_format($owe['usd_now'], 4) }}</td>
                                        <td class="{{ $owe['loss_usd'] > 0.0001 ? 'text-success' : 'text-muted' }}">{{ number_format($owe['loss_usd'], 4) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="box box-primary fx-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-dollar"></i> تحديث السعر اليوم</h3>
                    </div>
                    <div class="box-body">
                        <div class="fx-current">
                            <div class="fx-current-label">السعر الحالي</div>
                            <div class="fx-current-value">
                                @if($currentRate > 0)
                                    1 $ = <strong>{{ number_format($currentRate, 0) }}</strong> ج.س
                                @else
                                    <span class="text-muted">لم يُحدَّد بعد</span>
                                @endif
                            </div>
                            <p class="text-muted" style="margin:8px 0 0;">سعر اليوم للعمليات الجديدة. العمليات القديمة تبقى بسعر يومها.</p>
                        </div>

                        <form method="post" action="{{ route('dashboard.exchange-rates.store') }}" style="margin-top:16px;">
                            @csrf
                            <div class="form-group">
                                <label>كم جنيه سوداني = دولار واحد؟ <span class="text-danger">*</span></label>
                                <input type="number" name="sdg_per_usd" class="form-control input-lg" step="1" min="1"
                                       value="{{ old('sdg_per_usd', $currentRate > 0 ? (int) $currentRate : '') }}"
                                       placeholder="مثال: 4500" required>
                                <small class="text-muted">مثال: إذا الدولار بـ 4500 جنيه، اكتب 4500</small>
                            </div>
                            <div class="form-group">
                                <label>تاريخ السعر</label>
                                <input type="date" name="rate_date" class="form-control"
                                       value="{{ old('rate_date', now()->toDateString()) }}">
                            </div>
                            <div class="form-group">
                                <label>ملاحظة (اختياري)</label>
                                <input type="text" name="note" class="form-control" value="{{ old('note') }}"
                                       placeholder="مثال: سعر السوق اليوم">
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="show_usd" value="1" {{ $showUsd ? 'checked' : '' }}>
                                    إظهار الدولار بجانب المبلغ
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-lg">
                                <i class="fa fa-save"></i> حفظ السعر
                            </button>
                        </form>
                    </div>
                </div>

                <div class="box box-success fx-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-calculator"></i> حاسبة سريعة</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>من جنيه → دولار</label>
                            <div class="input-group">
                                <input type="number" id="calc-sdg" class="form-control" min="0" step="1" placeholder="مبلغ بالجنيه">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" id="btn-sdg-to-usd">حوّل</button>
                                </span>
                            </div>
                            <div class="fx-calc-result" id="out-sdg-to-usd">—</div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>من دولار → جنيه</label>
                            <div class="input-group">
                                <input type="number" id="calc-usd" class="form-control" min="0" step="0.01" placeholder="مبلغ بالدولار">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" id="btn-usd-to-sdg">حوّل</button>
                                </span>
                            </div>
                            <div class="fx-calc-result" id="out-usd-to-sdg">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="box box-info fx-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-history"></i> سجل الأسعار (لمراقبة التضخم)</h3>
                    </div>
                    <div class="box-body table-responsive mobile-card-table">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>1 $ = ؟ ج.س</th>
                                    <th>التغيّر</th>
                                    <th>ملاحظة</th>
                                    <th>بواسطة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rates as $i => $rate)
                                    @php
                                        $prev = $rates[$i + 1]->sdg_per_usd ?? null;
                                        $diff = $prev ? ((float) $rate->sdg_per_usd - (float) $prev) : null;
                                    @endphp
                                    <tr>
                                        <td data-label="التاريخ">{{ $rate->rate_date->format('Y-m-d') }}</td>
                                        <td data-label="السعر"><strong>{{ number_format($rate->sdg_per_usd, 0) }}</strong></td>
                                        <td data-label="التغيّر">
                                            @if($diff === null)
                                                <span class="text-muted">—</span>
                                            @elseif($diff > 0)
                                                <span class="text-danger">+{{ number_format($diff, 0) }} ▲ تضخم</span>
                                            @elseif($diff < 0)
                                                <span class="text-success">{{ number_format($diff, 0) }} ▼</span>
                                            @else
                                                <span class="text-muted">بدون تغيّر</span>
                                            @endif
                                        </td>
                                        <td data-label="ملاحظة">{{ $rate->note ?: '—' }}</td>
                                        <td data-label="بواسطة">{{ trim(($rate->user->first_name ?? '').' '.($rate->user->last_name ?? '')) ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5">لا يوجد سجل بعد — احفظ أول سعر اليوم.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="products-pagination text-center">{{ $rates->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.fx-box { border-radius: 12px; }
.fx-current {
    background: linear-gradient(135deg, #ecf8ff, #f0fdf4);
    border: 1px solid #cfe8f7;
    border-radius: 12px;
    padding: 14px;
}
.fx-current-label { color: #64748b; font-weight: 700; font-size: 12px; }
.fx-current-value { font-size: 22px; color: #0f172a; margin-top: 4px; }
.fx-calc-result {
    margin-top: 8px;
    padding: 10px 12px;
    background: #f8fafc;
    border-radius: 8px;
    font-weight: 700;
    color: #1e5f8a;
    min-height: 40px;
}
</style>
@endsection

@push('scripts')
<script>
(function () {
    var rate = {{ (float) $currentRate }};
    var convertUrl = @json(route('dashboard.exchange-rates.convert'));
    var token = @json(csrf_token());

    function convert(amount, from, outEl) {
        if (!rate || rate <= 0) {
            outEl.textContent = 'حدّد سعر الدولار أولاً';
            return;
        }
        fetch(convertUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ amount: amount, from: from })
        }).then(function (r) { return r.json(); }).then(function (data) {
            outEl.textContent = data.text || data.message || '—';
        }).catch(function () {
            outEl.textContent = 'تعذر التحويل';
        });
    }

    document.getElementById('btn-sdg-to-usd').addEventListener('click', function () {
        convert(document.getElementById('calc-sdg').value || 0, 'sdg', document.getElementById('out-sdg-to-usd'));
    });
    document.getElementById('btn-usd-to-sdg').addEventListener('click', function () {
        convert(document.getElementById('calc-usd').value || 0, 'usd', document.getElementById('out-usd-to-sdg'));
    });
})();
</script>
@endpush
