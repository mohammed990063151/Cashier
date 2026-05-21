<script>
document.addEventListener('DOMContentLoaded', function () {
    var modeEl = document.getElementById('sale_mode');
    var cartonEl = document.getElementById('pieces_per_carton');
    var groupEl = document.getElementById('pieces_per_carton_group');
    var hintEl = document.getElementById('pieces_per_carton_hint');
    var helpEl = document.getElementById('sale_mode_help');

    if (!modeEl || !cartonEl) {
        return;
    }

    var hints = {
        piece_only: 'يُباع ويُسعَّر بالحبة في الطلبات.',
        bulk_only: 'يُباع بالعبوة أو الكرتون الكامل فقط — حدد عدد الحبات داخل العبوة.',
        flexible: 'يدعم البيع بالحبة، 3، 6، الدستة (12)، والعبوة حسب الحاجة.'
    };

    function syncSaleModeFields() {
        var mode = modeEl.value;
        if (helpEl) {
            helpEl.textContent = hints[mode] || '';
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
            if (hintEl) hintEl.textContent = 'مثال: 12 أو 24 حبة داخل الكرتون.';
        } else {
            cartonEl.readOnly = false;
            cartonEl.min = 1;
            if (groupEl) groupEl.style.opacity = '1';
            if (hintEl) hintEl.textContent = '1 = حبة ودستة فقط. 12+ = تظهر العبوة في الطلب.';
        }
    }

    modeEl.addEventListener('change', syncSaleModeFields);
    syncSaleModeFields();
});
</script>
