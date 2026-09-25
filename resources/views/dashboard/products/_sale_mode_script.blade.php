<script>
document.addEventListener('DOMContentLoaded', function () {
    var MODE = {
        piece: {
            help: 'التسعير والمخزون بالحبة/القطعة. مناسب للمنتجات المفردة.',
            purchase: 'سعر الشراء (للحبة)',
            sale: 'سعر البيع (للحبة)',
            stock: 'المخزون (بالحبة)',
            showSaleMode: true,
            showCarton: true
        },
        carton: {
            help: 'حدّد يدوياً كم حبة داخل الكرتونة (مثلاً 12 أو 24). في الطلبات يمكنك البيع: حبة أو نصف كرتونة أو كرتونة كاملة.',
            purchase: 'سعر الشراء (للكرتونة الكاملة)',
            sale: 'سعر البيع (للكرتونة الكاملة)',
            stock: 'المخزون (بعدد الكراتين)',
            showSaleMode: true,
            showCarton: true
        },
        kilo: {
            help: 'التسعير والمخزون بالكيلو مع دعم الكسور حتى 3 منازل عشرية (مثال: 1.455 كيلو).',
            purchase: 'سعر الشراء (للكيلو)',
            sale: 'سعر البيع (للكيلو)',
            stock: 'المخزون (بالكيلو)',
            showSaleMode: false,
            showCarton: false
        }
    };

    var measureEl = document.getElementById('measure_unit');
    var switchEl = document.getElementById('measure_unit_switch');
    var helpEl = document.getElementById('measure_unit_help');
    var purchaseLabel = document.getElementById('label_purchase_price');
    var saleLabel = document.getElementById('label_sale_price');
    var stockLabel = document.getElementById('label_stock');
    var saleModeSection = document.getElementById('sale_mode_section');
    var modeEl = document.getElementById('sale_mode');
    var cartonEl = document.getElementById('pieces_per_carton');
    var groupEl = document.getElementById('pieces_per_carton_group');
    var hintEl = document.getElementById('pieces_per_carton_hint');
    var saleHelpEl = document.getElementById('sale_mode_help');
    var previewEl = document.getElementById('unit_live_preview');
    var purchaseInput = document.getElementById('purchase_price');
    var saleInput = document.getElementById('sale_price');
    var stockInput = document.getElementById('stock');

    if (!measureEl || !switchEl) {
        return;
    }

    function round3(value) {
        var n = parseFloat(String(value).replace(/,/g, ''));
        if (!isFinite(n)) return 0;
        return Math.round(n * 1000) / 1000;
    }

    function display3(value) {
        var n = round3(value);
        return String(n);
    }

    function currentUnit() {
        return measureEl.value || 'piece';
    }

    function syncSwitchButtons() {
        var unit = currentUnit();
        switchEl.querySelectorAll('.unit-switch-btn').forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-unit') === unit);
        });
    }

    function syncSaleModeFields() {
        if (!modeEl || !cartonEl) return;

        var unit = currentUnit();
        var mode = modeEl.value;
        var hints = {
            piece_only: 'في الطلبات: بيع بالحبة فقط.',
            bulk_only: 'في الطلبات: كرتونة كاملة فقط.',
            flexible: 'في الطلبات: حبة + نصف كرتونة + كرتونة كاملة (حسب عدد الحبات الذي حددته).'
        };

        if (saleHelpEl) {
            saleHelpEl.textContent = hints[mode] || '';
        }

        if (unit === 'kilo') {
            cartonEl.value = 1;
            cartonEl.readOnly = true;
            if (groupEl) groupEl.style.display = 'none';
            return;
        }

        if (groupEl) groupEl.style.display = '';

        if (unit === 'carton') {
            cartonEl.readOnly = false;
            cartonEl.min = 2;
            if (parseInt(cartonEl.value, 10) < 2) cartonEl.value = 12;
            if (hintEl) hintEl.textContent = 'أدخله يدوياً حسب بضاعتك. مثال: شاي=12، بسكويت=24. هذا يحدد الحبة ونصف الكرتونة والكرتونة.';
            if (modeEl && (!modeEl.value || modeEl.value === 'piece_only')) {
                modeEl.value = 'flexible';
            }
            return;
        }

        if (mode === 'piece_only') {
            cartonEl.value = 1;
            cartonEl.readOnly = true;
            if (groupEl) groupEl.style.opacity = '0.55';
            if (hintEl) hintEl.textContent = 'غير مستخدم في البيع بالحبة فقط.';
        } else if (mode === 'bulk_only') {
            cartonEl.readOnly = false;
            cartonEl.min = 2;
            if (parseInt(cartonEl.value, 10) < 2) cartonEl.value = 12;
            if (groupEl) groupEl.style.opacity = '1';
            if (hintEl) hintEl.textContent = 'مثال: 12 أو 24 حبة داخل الكرتونة.';
        } else {
            cartonEl.readOnly = false;
            cartonEl.min = 1;
            if (groupEl) groupEl.style.opacity = '1';
            if (hintEl) hintEl.textContent = '1 = حبة ودستة فقط. 12+ = تظهر الكرتونة في الطلب.';
        }
    }

    function updatePreview() {
        if (!previewEl) return;
        var unit = currentUnit();
        var purchase = round3(purchaseInput && purchaseInput.value);
        var sale = round3(saleInput && saleInput.value);
        var stock = round3(stockInput && stockInput.value);
        var bulk = Math.max(1, parseInt(cartonEl && cartonEl.value, 10) || 12);
        var html = '';

        if (unit === 'carton' && bulk > 1) {
            var piecePurchase = round3(purchase / bulk);
            var pieceSale = round3(sale / bulk);
            var totalPieces = round3(stock * bulk);
            html = '<i class="fa fa-exchange"></i> يُحفظ كـ: شراء '
                + display3(piecePurchase) + ' / حبة — بيع '
                + display3(pieceSale) + ' / حبة — مخزون '
                + display3(totalPieces) + ' حبة';
        } else if (unit === 'kilo') {
            html = '<i class="fa fa-check-circle"></i> يُحفظ بالكيلو بدقة 3 منازل: '
                + display3(stock) + ' كيلو × ' + display3(sale) + ' = '
                + display3(stock * sale) + ' ج.س';
        } else {
            html = '<i class="fa fa-info-circle"></i> يُحفظ بالحبة: مخزون '
                + display3(stock) + ' × سعر '
                + display3(sale) + ' = '
                + display3(stock * sale) + ' ج.س';
        }

        previewEl.innerHTML = html;
    }

    function applyUnit(unit, options) {
        options = options || {};
        var cfg = MODE[unit] || MODE.piece;
        measureEl.value = unit;
        syncSwitchButtons();

        if (helpEl) helpEl.textContent = cfg.help;
        if (purchaseLabel) purchaseLabel.innerHTML = cfg.purchase + ' <span class="text-danger">*</span>';
        if (saleLabel) saleLabel.innerHTML = cfg.sale + ' <span class="text-danger">*</span>';
        if (stockLabel) stockLabel.innerHTML = cfg.stock + ' <span class="text-danger">*</span>';

        if (saleModeSection) {
            saleModeSection.style.display = cfg.showSaleMode ? '' : 'none';
        }

        if (unit === 'kilo' && modeEl) {
            modeEl.value = 'piece_only';
        } else if (unit === 'carton' && modeEl && (modeEl.value === 'piece_only' || !modeEl.value)) {
            modeEl.value = 'flexible';
        }

        [purchaseInput, saleInput, stockInput].forEach(function (el) {
            if (!el) return;
            el.step = unit === 'kilo' ? '0.001' : '1';
        });

        syncSaleModeFields();
        updatePreview();
    }

    switchEl.querySelectorAll('.unit-switch-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            applyUnit(btn.getAttribute('data-unit'));
        });
    });

    if (modeEl) modeEl.addEventListener('change', function () {
        syncSaleModeFields();
        updatePreview();
    });
    if (cartonEl) cartonEl.addEventListener('input', updatePreview);
    [purchaseInput, saleInput, stockInput].forEach(function (el) {
        if (el) el.addEventListener('input', updatePreview);
    });

    applyUnit(currentUnit());
});
</script>
