(function ($) {
    function boot($wrap) {
        var mode = $wrap.data('mode') || 'sale';
        var lookupUrl = $wrap.data('lookup');
        var $panel = $wrap.find('.barcode-scan-panel');
        var readerId = 'barcode-reader-' + Math.random().toString(36).slice(2, 8);
        $wrap.find('.barcode-reader').attr('id', readerId);
        var scanner = null;
        var running = false;
        var pending = null;
        var lastCode = '';
        var lastAt = 0;

        function setStatus(text) {
            $wrap.find('.barcode-scan-status').text(text);
        }

        function stopCamera() {
            if (!scanner || !running) {
                running = false;
                return $.when();
            }
            running = false;
            return scanner.stop().then(function () {
                scanner.clear();
            }).catch(function () {});
        }

        function startCamera() {
            if (!window.Html5Qrcode) {
                setStatus('تعذر تحميل ماسح الكاميرا. اكتب الباركود يدوياً.');
                return;
            }
            if (!scanner) {
                scanner = new Html5Qrcode(readerId);
            }
            scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 260, height: 140 }, aspectRatio: 1.7 },
                function (text) {
                    onCode(String(text || '').trim());
                }
            ).then(function () {
                running = true;
                setStatus('جاهز للمسح');
            }).catch(function () {
                setStatus('الكاميرا غير متاحة. اكتب الباركود ثم اضغط بحث.');
            });
        }

        function showQty(product, unknownCode) {
            pending = { product: product, code: unknownCode || '' };
            var title = product ? product.name : ('باركود جديد: ' + unknownCode);
            $wrap.find('.barcode-qty-name').text(title);
            $wrap.find('.barcode-qty-input').val(1);
            $wrap.find('.barcode-qty').prop('hidden', false);
            $wrap.find('.barcode-qty-ok').text('أضف للطلب');
            if (running && scanner) {
                try { scanner.pause(true); } catch (e) {}
            }
            setTimeout(function () {
                $wrap.find('.barcode-qty-input').trigger('focus').trigger('select');
            }, 50);
        }

        function hideQty() {
            pending = null;
            $wrap.find('.barcode-qty').prop('hidden', true);
            if (running && scanner) {
                try { scanner.resume(); } catch (e) {}
            }
        }

        function onCode(code) {
            if (!code) {
                return;
            }
            var now = Date.now();
            if (code === lastCode && now - lastAt < 1200) {
                return;
            }
            lastCode = code;
            lastAt = now;
            setStatus('جارٍ البحث…');
            $.get(lookupUrl, { barcode: code })
                .done(function (res) {
                    if (res && res.found && res.product) {
                        setStatus('تم العثور على ' + res.product.name);
                        showQty(res.product, code);
                        return;
                    }
                    if (mode === 'purchase' && typeof window.openQuickProductForBarcode === 'function') {
                        setStatus('منتج غير مسجّل. أدخل اسمه.');
                        window.openQuickProductForBarcode(code);
                        hideQty();
                        stopCamera();
                        $panel.prop('hidden', true);
                        return;
                    }
                    setStatus('هذا الباركود غير مسجّل في المنتجات.');
                    hideQty();
                })
                .fail(function () {
                    setStatus('تعذر البحث. حاول مرة أخرى.');
                });
        }

        $wrap.find('.barcode-scan-open').on('click', function () {
            $panel.prop('hidden', false);
            hideQty();
            startCamera();
        });

        $wrap.find('.barcode-scan-close').on('click', function () {
            hideQty();
            stopCamera();
            $panel.prop('hidden', true);
        });

        $wrap.find('.barcode-manual-go').on('click', function () {
            onCode($wrap.find('.barcode-manual-input').val().trim());
        });
        $wrap.find('.barcode-manual-input').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                onCode($(this).val().trim());
            }
        });

        $wrap.find('.barcode-qty-minus').on('click', function () {
            var $input = $wrap.find('.barcode-qty-input');
            var next = Math.max(0, (parseFloat($input.val()) || 0) - 1);
            $input.val(next);
        });
        $wrap.find('.barcode-qty-plus').on('click', function () {
            var $input = $wrap.find('.barcode-qty-input');
            $input.val((parseFloat($input.val()) || 0) + 1);
        });

        $wrap.find('.barcode-qty-ok').on('click', function () {
            if (!pending) {
                return;
            }
            var qty = parseFloat($wrap.find('.barcode-qty-input').val());
            if (!qty || qty <= 0) {
                setStatus('أدخل كمية أكبر من صفر.');
                return;
            }
            if (pending.product && mode === 'sale' && typeof window.addScannedSaleProduct === 'function') {
                window.addScannedSaleProduct(pending.product, qty);
                setStatus('أُضيف ' + pending.product.name);
            } else if (pending.product && mode === 'purchase' && typeof window.addScannedPurchaseProduct === 'function') {
                window.addScannedPurchaseProduct(pending.product, qty);
                setStatus('أُضيف ' + pending.product.name);
            } else if (!pending.product && mode === 'purchase' && typeof window.openQuickProductForBarcode === 'function') {
                window.openQuickProductForBarcode(pending.code);
                setStatus('أكمل بيانات المنتج الجديد.');
                hideQty();
                stopCamera();
                $panel.prop('hidden', true);
                return;
            } else if (!pending.product) {
                setStatus('هذا الباركود غير مسجّل.');
            }
            hideQty();
            $wrap.find('.barcode-manual-input').val('');
        });
    }

    $(function () {
        $('.barcode-scan-wrap').each(function () {
            boot($(this));
        });
    });
})(jQuery);
