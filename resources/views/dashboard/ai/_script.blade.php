<script>
(function () {
    var endpoint = @json(route('dashboard.ai.chat'));
    var bootstrapUrl = @json(route('dashboard.ai.bootstrap'));
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    var panel = document.getElementById('ai-assistant-panel');
    var toggle = document.getElementById('ai-assistant-toggle');
    var closeBtn = document.getElementById('ai-assistant-close');
    var form = document.getElementById('ai-assistant-form');
    var input = document.getElementById('ai-assistant-input');
    var messages = document.getElementById('ai-assistant-messages');
    var suggestions = document.getElementById('ai-assistant-suggestions');
    var busy = false;

    if (!panel || !toggle || !form) return;

    function openPanel() {
        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        input.focus();
    }

    function closePanel() {
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
    }

    toggle.addEventListener('click', function () {
        if (panel.classList.contains('is-open')) closePanel();
        else openPanel();
    });
    closeBtn.addEventListener('click', closePanel);

    var menuLink = document.getElementById('ai-assistant-menu-link');
    if (menuLink) {
        menuLink.addEventListener('click', function (e) {
            e.preventDefault();
            openPanel();
        });
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function appendMessage(role, html) {
        var wrap = document.createElement('div');
        wrap.className = 'ai-msg ai-' + role;
        wrap.innerHTML = '<div class="ai-bubble">' + html + '</div>';
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
        return wrap;
    }

    function renderSuggestions(items) {
        suggestions.innerHTML = '';
        (items || []).forEach(function (item) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ai-chip';
            btn.textContent = item.label || item.text;
            btn.addEventListener('click', function () {
                sendPayload({ message: item.text, action: 'quick_reply', text: item.text });
            });
            suggestions.appendChild(btn);
        });
    }

    function renderProducts(products, actions) {
        if (!products || !products.length) return '';
        actions = actions || [
            { key: 'stock', label: 'المخزون' },
            { key: 'value', label: 'القيمة' },
            { key: 'carton', label: 'كراتين' }
        ];

        var html = '<div class="ai-products">';
        products.forEach(function (p) {
            html += '<div class="ai-product-card">'
                + '<strong>' + escapeHtml(p.name) + '</strong>'
                + '<div class="ai-product-meta">'
                + escapeHtml(p.category) + ' • ' + escapeHtml(p.stock_display)
                + ' • بيع ' + escapeHtml(String(p.sale_price))
                + '</div><div class="ai-product-actions">';

            actions.forEach(function (a) {
                html += '<button type="button" class="btn btn-primary btn-xs ai-pick-product" data-id="'
                    + p.id + '" data-intent="' + escapeHtml(a.key) + '">'
                    + escapeHtml(a.label) + '</button>';
            });

            html += '</div></div>';
        });
        html += '</div>';
        return html;
    }

    function renderLinks(links) {
        if (!links || !links.length) return '';
        var html = '<div class="ai-links">';
        links.forEach(function (l) {
            html += '<a href="' + escapeHtml(l.url) + '" target="_blank" rel="noopener">'
                + escapeHtml(l.label) + '</a>';
        });
        html += '</div>';
        return html;
    }

    function renderCalcForm(calc) {
        if (!calc || !calc.product_id) return '';
        var units = calc.units || [];
        var options = units.map(function (u) {
            return '<option value="' + escapeHtml(u.value) + '">' + escapeHtml(u.label) + '</option>';
        }).join('');

        return '<div class="ai-calc-box">'
            + '<div class="row">'
            + '<div class="col-xs-4"><label>الكمية</label>'
            + '<input type="number" min="0" step="0.001" class="form-control input-sm ai-calc-qty" value="1"></div>'
            + '<div class="col-xs-5"><label>الوحدة</label>'
            + '<select class="form-control input-sm ai-calc-unit">' + options + '</select></div>'
            + '<div class="col-xs-3"><label>&nbsp;</label>'
            + '<button type="button" class="btn btn-success btn-block btn-sm ai-calc-btn" data-id="'
            + calc.product_id + '">احسب</button></div>'
            + '</div></div>';
    }

    function renderBotPayload(data) {
        var html = escapeHtml(data.reply || '');
        html += renderProducts(data.products, data.product_actions);
        html += renderCalcForm(data.calc_form);
        html += renderLinks(data.links);
        appendMessage('bot', html);
        renderSuggestions(data.suggestions || []);
    }

    function sendPayload(payload) {
        if (busy) return;
        busy = true;

        if (payload.message) {
            appendMessage('user', escapeHtml(payload.message));
        }

        var typing = appendMessage('bot', '<span class="ai-typing">جاري جلب البيانات من النظام…</span>');

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                typing.remove();
                if (!json.ok) {
                    appendMessage('bot', 'تعذّر الاتصال بالمساعد.');
                    return;
                }
                renderBotPayload(json.data || {});
            })
            .catch(function () {
                typing.remove();
                appendMessage('bot', 'حدث خطأ في الاتصال. حاول مرة أخرى.');
            })
            .finally(function () {
                busy = false;
            });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = (input.value || '').trim();
        if (!text) return;
        input.value = '';
        sendPayload({ message: text });
    });

    messages.addEventListener('click', function (e) {
        var pick = e.target.closest('.ai-pick-product');
        if (pick) {
            sendPayload({
                action: 'select_product',
                product_id: parseInt(pick.getAttribute('data-id'), 10),
                intent: pick.getAttribute('data-intent') || 'stock',
                message: ''
            });
            return;
        }

        var calcBtn = e.target.closest('.ai-calc-btn');
        if (calcBtn) {
            var box = calcBtn.closest('.ai-calc-box');
            var qty = box.querySelector('.ai-calc-qty').value;
            var unit = box.querySelector('.ai-calc-unit').value;
            sendPayload({
                action: 'calc_qty',
                product_id: parseInt(calcBtn.getAttribute('data-id'), 10),
                qty: qty,
                unit: unit,
                message: qty + ' ' + unit
            });
        }
    });

    // Bootstrap welcome
    fetch(bootstrapUrl, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function (res) { return res.json(); })
        .then(function (json) {
            messages.innerHTML = '';
            if (json.ok) renderBotPayload(json.data || {});
            else appendMessage('bot', 'مرحباً، كيف أساعدك؟');
        })
        .catch(function () {
            messages.innerHTML = '';
            appendMessage('bot', 'مرحباً، كيف أساعدك؟');
        });
})();
</script>
