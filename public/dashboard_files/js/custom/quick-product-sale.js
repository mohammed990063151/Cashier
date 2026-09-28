(function ($) {
    function syncQuickSaleMethod() {
        var method = $('#qpSaleMethod').val();
        $('#qpBulkWrap').toggle(method === 'carton' || method === 'both');
    }

    $(function () {
        if (!$('#quickProductForm').length) {
            return;
        }

        $('#qpSaleMethod').on('change', syncQuickSaleMethod);
        syncQuickSaleMethod();

        $('#quickProductForm').on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#qpSubmitBtn').prop('disabled', true);
            $('#quickProductAlert').hide();

            var stock = parseFloat($('#qpStock').val());
            if (!stock || stock <= 0) {
                $('#quickProductAlert').text('أدخل مخزوناً أكبر من صفر حتى يظهر المنتج عند الاستخدام.').show();
                $btn.prop('disabled', false);
                return;
            }

            var method = $('#qpSaleMethod').val() || 'piece';
            var measure = 'piece';
            var saleMode = 'piece_only';
            var bulk = 1;
            if (method === 'kilo') {
                measure = 'kilo';
            } else if (method === 'carton') {
                measure = 'carton';
                saleMode = 'bulk_only';
                bulk = Math.max(2, parseInt($('#qpBulk').val(), 10) || 12);
            } else if (method === 'both') {
                measure = 'carton';
                saleMode = 'flexible';
                bulk = Math.max(2, parseInt($('#qpBulk').val(), 10) || 12);
            }

            $.ajax({
                url: window.quickProductStoreUrl,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: {
                    category_id: $('#qpCategory').val(),
                    name: $('#qpName').val(),
                    barcode: $('#qpBarcode').val(),
                    purchase_price: $('#qpPurchasePrice').val(),
                    sale_price: $('#qpSalePrice').val(),
                    pieces_per_carton: bulk,
                    sale_mode: saleMode,
                    measure_unit: measure,
                    stock: stock,
                },
            })
                .done(function (res) {
                    $('#quickProductModal').modal('hide');
                    if (res.product && typeof window.addScannedSaleProduct === 'function') {
                        window.addScannedSaleProduct(res.product, 1);
                    }
                })
                .fail(function (xhr) {
                    var msg = 'تعذر حفظ المنتج.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    }
                    $('#quickProductAlert').text(msg).show();
                })
                .always(function () {
                    $btn.prop('disabled', false);
                });
        });
    });

    window.openQuickProductForBarcode = function (code) {
        var form = document.getElementById('quickProductForm');
        if (!form) {
            return;
        }
        $('#quickProductAlert').hide();
        form.reset();
        $('#qpBarcode').val(code || '');
        $('#qpSaleMethod').val('piece');
        $('#qpBulk').val(12);
        $('#qpStock').val(1);
        $('#qpStockWrap').show();
        $('#qpSubmitBtn').html('<i class="fa fa-check"></i> حفظ وإظهار في الطلب');
        syncQuickSaleMethod();
        $('#quickProductModal').modal('show');
        setTimeout(function () {
            $('#qpName').trigger('focus');
        }, 350);
    };
})(jQuery);
