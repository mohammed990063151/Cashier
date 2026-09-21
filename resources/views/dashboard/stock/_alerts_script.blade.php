<script>
(function () {
    var endpoint = @json(route('dashboard.stock-alerts'));
    var productsUrl = @json(route('dashboard.products.index'));
    var collectionCount = {{ (int) ($collectionAlertsCount ?? 0) }};
    var sessionKey = 'cashier_stock_toast_' + new Date().toISOString().slice(0, 10);
    var lastOnlineNotifyAt = 0;

    function ensurePermission() {
        if (!('Notification' in window)) {
            return Promise.resolve('unsupported');
        }
        if (Notification.permission === 'granted' || Notification.permission === 'denied') {
            return Promise.resolve(Notification.permission);
        }
        return Notification.requestPermission();
    }

    function showBrowserNotification(title, body) {
        if (!('Notification' in window) || Notification.permission !== 'granted') {
            return;
        }
        try {
            var n = new Notification(title, {
                body: body,
                tag: 'cashier-low-stock',
                renotify: true,
                dir: 'rtl',
                lang: 'ar'
            });
            n.onclick = function () {
                window.focus();
                window.location.href = productsUrl;
                n.close();
            };
        } catch (e) {}
    }

    function showNoty(payload) {
        if (typeof Noty === 'undefined' || !payload || !payload.count) {
            return;
        }
        var lines = (payload.alerts || []).slice(0, 4).map(function (a) {
            return '• ' + a.name + ' — ' + a.stock_display;
        }).join('<br>');
        var text = '<strong>تنبيه مخزون</strong><br>'
            + payload.count + ' منتج بحاجة مراجعة'
            + (payload.out_count ? ' (نفد: ' + payload.out_count + ')' : '')
            + (lines ? '<br>' + lines : '');

        new Noty({
            type: 'warning',
            layout: 'topLeft',
            timeout: 8000,
            text: text,
            killer: false
        }).show();
    }

    function updateBadge(stockCount) {
        var total = (stockCount || 0) + collectionCount;
        var $toggle = $('.notifications-menu > a.dropdown-toggle');
        var $badge = $('#header-alerts-badge');
        if (total > 0) {
            if ($badge.length) {
                $badge.text(total);
            } else {
                $toggle.append('<span class="label label-warning" id="header-alerts-badge">' + total + '</span>');
            }
        } else {
            $badge.remove();
        }
    }

    function fetchAlerts() {
        return fetch(endpoint, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        }).then(function (r) {
            if (!r.ok) {
                throw new Error('stock alerts failed');
            }
            return r.json();
        });
    }

    function notifyIfNeeded(payload, force) {
        if (!payload || !payload.count) {
            updateBadge(0);
            return;
        }
        updateBadge(payload.count);

        var already = sessionStorage.getItem(sessionKey);
        if (force || !already) {
            showNoty(payload);
            sessionStorage.setItem(sessionKey, '1');
        }

        if (force) {
            var names = (payload.alerts || []).slice(0, 3).map(function (a) { return a.name; }).join('، ');
            showBrowserNotification(
                'مخزون قليل — ' + payload.count + ' منتج',
                names || 'افتح لوحة التحكم لمراجعة المخزون'
            );
        }
    }

    function onOnline() {
        var now = Date.now();
        if (now - lastOnlineNotifyAt < 15000) {
            return;
        }
        lastOnlineNotifyAt = now;
        ensurePermission().finally(function () {
            fetchAlerts().then(function (payload) {
                notifyIfNeeded(payload, true);
            }).catch(function () {});
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        ensurePermission();
        fetchAlerts().then(function (payload) {
            notifyIfNeeded(payload, false);
        }).catch(function () {});
    });

    window.addEventListener('online', onOnline);

    // إذا فُتحت الصفحة والجهاز كان offline ثم عاد الاتصال
    if (typeof navigator !== 'undefined' && navigator.onLine === false) {
        window.addEventListener('online', onOnline);
    }
})();
</script>
