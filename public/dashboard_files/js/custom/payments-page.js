(function ($) {
    function previewSelectedFiles(fileList, $container) {
        $container.empty();
        if (!fileList || !fileList.length) {
            return;
        }
        Array.prototype.forEach.call(fileList, function (file) {
            if (!file.type || file.type.indexOf('image') !== 0) {
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                $container.append(
                    '<img src="' + e.target.result + '" alt="معاينة" title="' + file.name + '">'
                );
            };
            reader.readAsDataURL(file);
        });
    }

    function renderReceiptUrls(urls, $container, emptyMessage) {
        $container.empty();
        if (!urls || !urls.length) {
            $container.html('<span class="text-warning">' + (emptyMessage || 'لا توجد صور') + '</span>');
            return;
        }
        urls.forEach(function (url) {
            $container.append(
                '<a href="' + url + '" target="_blank" rel="noopener"><img src="' + url + '" alt="إشعار"></a>'
            );
        });
    }

    function parseReceiptUrls($btn) {
        var raw = $btn.attr('data-receipt-urls');
        if (!raw) {
            return [];
        }
        try {
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function toggleBankReceiptField() {
        var isBank = $('#paymentMethod').val() === 'bank';
        $('.bank-receipt-field').toggle(isBank);
        $('#paymentBankReceipt').prop('required', isBank);
    }

    function openPaymentModal($btn) {
        var orderId = $btn.data('order-id');
        var orderNumber = $btn.data('order-number');
        var clientName = $btn.data('client-name');
        var remaining = parseFloat($btn.data('remaining')) || 0;
        var amount = $btn.data('amount');

        $('#paymentOrderId').val(orderId);
        $('#paymentModalTitle').html(
            '<i class="fa fa-money"></i> دفعة — طلب <strong>' + orderNumber + '</strong><br>' +
            '<small>' + clientName + '</small>'
        );

        if (amount !== undefined && amount !== '') {
            $('input[name="amount"]').val(parseFloat(amount).toFixed(2));
            $('#paymentAmountHint').text('مبلغ القسط المحدد — المتبقي على الطلب: ' + remaining.toFixed(2) + ' ج.س');
        } else {
            $('input[name="amount"]').val('');
            $('#paymentAmountHint').text('المتبقي على الطلب: ' + remaining.toFixed(2) + ' ج.س');
        }

        $('.remaining-text').text($('#paymentAmountHint').text());
        $('#paymentMethod').val('cash');
        $('#paymentBankReceipt').val('');
        $('#paymentReceiptPreview').empty();
        toggleBankReceiptField();
    }

    function loadClientOrders(clientId, selectedOrderId) {
        var $orderSelect = $('#filterOrderId');
        $orderSelect.find('option:not(:first)').remove();

        if (!clientId) {
            return;
        }

        var url = (window.paymentsClientOrdersUrl || '').replace('__CLIENT__', clientId);
        $.get(url, function (res) {
            (res.orders || []).forEach(function (o) {
                var selected = selectedOrderId && String(selectedOrderId) === String(o.id) ? ' selected' : '';
                $orderSelect.append(
                    '<option value="' + o.id + '" data-remaining="' + o.remaining + '"' + selected + '>' +
                    o.order_number + ' — متبقي ' + parseFloat(o.remaining).toFixed(2) +
                    '</option>'
                );
            });
        });
    }

    window.openPaymentLog = function (orderId) {
        var url = (window.paymentLogUrl || '').replace('__ORDER__', orderId);
        var $body = $('#paymentLogModalBody');
        var $modal = $('#paymentLogModal');

        $body.html('<p class="text-muted text-center" style="padding:30px;"><i class="fa fa-spinner fa-spin"></i> جاري التحميل...</p>');
        $modal.modal('show');

        $.ajax({
            url: url,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .done(function (html) {
                $body.html(html);
            })
            .fail(function () {
                $body.html('<p class="text-danger text-center" style="padding:20px;">خطأ في التحميل — حدّث الصفحة وحاول مرة أخرى.</p>');
            });
    };

    $(function () {
        $('#paymentMethod').on('change', toggleBankReceiptField);
        $('#paymentBankReceipt').on('change', function () {
            previewSelectedFiles(this.files, $('#paymentReceiptPreview'));
        });
        $('#editPaymentReceipt').on('change', function () {
            previewSelectedFiles(this.files, $('#editReceiptPreview'));
        });

        $('.add-payment-btn, .collect-today-btn').on('click', function () {
            openPaymentModal($(this));
        });

        $(document).on('click', '.btn-view-payment-log', function (e) {
            e.preventDefault();
            var orderId = $(this).data('order-id');
            if (orderId) {
                window.openPaymentLog(orderId);
            }
        });

        $('#filterClientId').on('change', function () {
            var clientId = $(this).val();
            loadClientOrders(clientId, null);
            if (clientId) {
                $('#filterOrderId').val('');
            }
        });

        var initialClient = $('#filterClientId').val();
        var initialOrder = $('#filterOrderId').val();
        if (initialClient && $('#filterOrderId option').length <= 1) {
            loadClientOrders(initialClient, initialOrder);
        }

        $(document).on('click', '.edit-payment-btn', function () {
            var $btn = $(this);
            $('#editPaymentForm').attr('action', $btn.data('update-url'));
            $('#editPaymentLogOrder').val($btn.data('log-order') || '');
            $('#editPaymentOrderNumber').text($btn.data('order-number'));
            $('#editPaymentAmount').val(parseFloat($btn.data('amount')).toFixed(2));
            $('#editPaymentAmount').attr('max', parseFloat($btn.data('max-amount')).toFixed(2));
            $('#editPaymentMaxHint').text('الحد الأقصى المسموح: ' + parseFloat($btn.data('max-amount')).toFixed(2) + ' ج.س');
            $('#editPaymentMethod').val($btn.data('method'));
            $('#editPaymentNotes').val($btn.data('notes') || '');
            $('#editPaymentReceipt').val('');
            $('#editReceiptPreview').empty();

            var receiptUrls = parseReceiptUrls($btn);
            if ($btn.data('method') === 'bank') {
                $('#editBankReceiptGroup').show();
                renderReceiptUrls(
                    receiptUrls,
                    $('#editCurrentReceipt'),
                    'لا توجد صور — أرفق إشعاراً واحداً على الأقل'
                );
            } else {
                $('#editBankReceiptGroup').hide();
            }

            $('#paymentLogModal').modal('hide');
            $('#editPaymentModal').modal('show');
        });

        $('#editPaymentMethod').on('change', function () {
            var isBank = $(this).val() === 'bank';
            $('#editBankReceiptGroup').toggle(isBank);
        });

        var params = new URLSearchParams(window.location.search);
        var logOrder = params.get('log_order');
        if (logOrder) {
            setTimeout(function () {
                window.openPaymentLog(logOrder);
            }, 300);
        }

        if (params.get('collect') === '1') {
            var $collectBtn = $('.collect-today-btn').first();
            if ($collectBtn.length) {
                setTimeout(function () { openPaymentModal($collectBtn); $('#paymentModal').modal('show'); }, 400);
            } else {
                var $payBtn = $('.add-payment-btn').first();
                if ($payBtn.length) {
                    setTimeout(function () { openPaymentModal($payBtn); $('#paymentModal').modal('show'); }, 400);
                }
            }
        }
    });
})(jQuery);
