@php
    $scanMode = $mode ?? 'sale';
@endphp
@once
<style>
.barcode-scan-wrap { margin-bottom: 12px; }
.barcode-scan-open { min-height: 52px; font-size: 17px; font-weight: 800; border-radius: 12px; }
.barcode-scan-panel {
    position: fixed;
    inset: 0;
    z-index: 3000;
    background: #0f172a;
    color: #fff;
    display: flex;
    flex-direction: column;
    padding: 12px;
    gap: 10px;
}
.barcode-scan-panel[hidden] { display: none !important; }
.barcode-scan-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.barcode-reader { width: 100%; min-height: 220px; border-radius: 12px; overflow: hidden; background: #000; }
.barcode-scan-status { margin: 0; font-size: 15px; font-weight: 700; }
.barcode-manual { display: flex; gap: 8px; }
.barcode-manual .form-control { min-height: 48px; font-size: 18px; }
.barcode-qty { background: #fff; color: #0f172a; border-radius: 14px; padding: 12px; }
.barcode-qty[hidden] { display: none !important; }
.barcode-qty-name { font-weight: 800; font-size: 18px; margin-bottom: 8px; }
.barcode-qty-controls { display: flex; gap: 8px; align-items: center; margin-bottom: 10px; }
.barcode-qty-controls .btn { min-width: 56px; min-height: 56px; font-size: 28px; }
.barcode-qty-input { min-height: 56px !important; font-size: 28px !important; text-align: center; font-weight: 800; }
.barcode-qty-ok { min-height: 52px; font-size: 18px; font-weight: 800; }
</style>
@endonce
<div class="barcode-scan-wrap" data-mode="{{ $scanMode }}" data-lookup="{{ route('dashboard.products.lookup-barcode') }}">
    <button type="button" class="btn btn-info btn-block barcode-scan-open">
        <i class="fa fa-camera"></i> مسح الباركود بالكاميرا
    </button>
    <div class="barcode-scan-panel" hidden>
        <div class="barcode-scan-top">
            <strong>وجّه الكاميرا إلى الباركود خلف المنتج</strong>
            <button type="button" class="btn btn-default barcode-scan-close">إغلاق</button>
        </div>
        <div class="barcode-reader"></div>
        <p class="barcode-scan-status">جاهز للمسح</p>
        <div class="barcode-manual">
            <input type="text" class="form-control barcode-manual-input" inputmode="numeric" placeholder="أو اكتب الباركود" autocomplete="off">
            <button type="button" class="btn btn-primary barcode-manual-go">بحث</button>
        </div>
        <div class="barcode-qty" hidden>
            <div class="barcode-qty-name"></div>
            <div class="barcode-qty-controls">
                <button type="button" class="btn btn-default barcode-qty-minus">−</button>
                <input type="number" class="form-control barcode-qty-input" min="0" step="any" value="1">
                <button type="button" class="btn btn-default barcode-qty-plus">+</button>
            </div>
            <button type="button" class="btn btn-success btn-block barcode-qty-ok">أضف للطلب</button>
        </div>
    </div>
</div>
@once
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="{{ asset('dashboard_files/js/custom/barcode-scan.js') }}?v={{ filemtime(public_path('dashboard_files/js/custom/barcode-scan.js')) }}"></script>
@endpush
@endonce
