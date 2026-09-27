<script>
(function () {
    var endpoint = @json(route('dashboard.collection-alerts'));
    var intervalMs = 15 * 60 * 1000;
    var storageKey = 'cashier_installment_notify_at';

    function shouldNotify() {
        var last = parseInt(localStorage.getItem(storageKey) || '0', 10);
        return (Date.now() - last) >= intervalMs;
    }

    function markNotified() {
        localStorage.setItem(storageKey, String(Date.now()));
    }

    function showBrowserNotification(payload) {
        if (!('Notification' in window) || Notification.permission !== 'granted' || !payload.count) {
            return;
        }
        var first = (payload.alerts || [])[0];
        var body = first
            ? first.client + ' — ' + first.amount + ' ج.س — ' + first.when
            : 'أقساط اقترب موعدها';
        try {
            var n = new Notification('قسط خلال ' + payload.within_days + ' أيام', {
                body: body,
                tag: 'cashier-installment-due',
                renotify: true,
                dir: 'rtl',
                lang: 'ar'
            });
            n.onclick = function () {
                window.focus();
                window.location.href = payload.url;
                n.close();
            };
        } catch (e) {}
    }

    function showNoty(payload) {
        if (typeof Noty === 'undefined' || !payload || !payload.count) {
            return;
        }
        var lines = (payload.alerts || []).slice(0, 4).map(function (a) {
            return '• ' + a.client + ' — ' + a.amount + ' ج.س — ' + a.when + ' (' + a.due_at + ')';
        }).join('<br>');
        new Noty({
            type: 'warning',
            layout: 'topLeft',
            timeout: 12000,
            text: '<strong>أقساط اقترب موعدها</strong> (خلال ' + payload.within_days + ' أيام)<br>' + lines,
            killer: false
        }).show();
    }

    function tick() {
        if (!shouldNotify()) {
            return;
        }
        fetch(endpoint, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        }).then(function (r) {
            if (!r.ok) {
                throw new Error('collection alerts failed');
            }
            return r.json();
        }).then(function (payload) {
            if (!payload || !payload.count) {
                return;
            }
            showNoty(payload);
            showBrowserNotification(payload);
            markNotified();
        }).catch(function () {});
    }

    document.addEventListener('DOMContentLoaded', tick);
    setInterval(tick, intervalMs);
})();
</script>
