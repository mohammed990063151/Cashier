@php
    $fx = app(\App\Services\CurrencyService::class);
@endphp
@if($fx->enabled())
<script>
(function () {
    var rate = {{ (float) $fx->rate() }};
    var panel = document.getElementById('fx-calc-panel');
    var toggle = document.getElementById('fx-calc-toggle');
    var out = document.getElementById('fx-calc-out');
    if (!toggle || !panel) return;

    toggle.addEventListener('click', function () {
        panel.classList.toggle('is-open');
    });

    document.addEventListener('click', function (e) {
        if (!panel.classList.contains('is-open')) return;
        if (panel.contains(e.target) || toggle.contains(e.target)) return;
        panel.classList.remove('is-open');
    });

    function fmt(n, d) {
        return Number(n).toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
    }

    document.getElementById('fx-btn-sdg').addEventListener('click', function () {
        var sdg = parseFloat(document.getElementById('fx-in-sdg').value) || 0;
        var usd = rate > 0 ? (sdg / rate) : 0;
        out.textContent = fmt(sdg, 0) + ' ج.س ≈ ' + fmt(usd, 2) + ' $';
    });

    document.getElementById('fx-btn-usd').addEventListener('click', function () {
        var usd = parseFloat(document.getElementById('fx-in-usd').value) || 0;
        var sdg = rate > 0 ? (usd * rate) : 0;
        out.textContent = fmt(usd, 2) + ' $ ≈ ' + fmt(sdg, 0) + ' ج.س';
    });

    // معاينة أسعار المنتج مباشرة عند الإضافة/التعديل
    function bindProductUsdHints() {
        var purchase = document.getElementById('purchase_price');
        var sale = document.getElementById('sale_price');
        if (!purchase && !sale) return;

        function hintFor(input, id) {
            if (!input) return;
            var el = document.getElementById(id);
            if (!el) {
                el = document.createElement('div');
                el.id = id;
                el.className = 'product-usd-hint';
                input.parentNode.appendChild(el);
            }
            var v = parseFloat(input.value) || 0;
            var usd = rate > 0 ? (v / rate) : 0;
            el.textContent = rate > 0 ? ('≈ ' + fmt(usd, 2) + ' $') : 'حدّد سعر الدولار أولاً';
        }

        function refresh() {
            hintFor(purchase, 'purchase_usd_hint');
            hintFor(sale, 'sale_usd_hint');
        }

        if (purchase) purchase.addEventListener('input', refresh);
        if (sale) sale.addEventListener('input', refresh);
        refresh();
    }

    document.addEventListener('DOMContentLoaded', bindProductUsdHints);
})();
</script>
@endif
