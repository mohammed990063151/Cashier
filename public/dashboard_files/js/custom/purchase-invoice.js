(function ($, window) {
    var catalog = window.purchaseProductsCatalog || [];
    var rowIndex = 0;

    function findProduct(id) {
        return catalog.find(function (p) {
            return String(p.id) === String(id);
        });
    }

    function unitsForProduct(product) {
        if (!product || !window.SaleUnitsHelper) {
            return [{ key: 'piece', label: 'حبة', multiplier: 1 }];
        }
        return window.SaleUnitsHelper.unitsForProduct(
            product.pieces_per_carton,
            product.sale_mode,
            product.measure_unit
        );
    }

    function piecePurchasePrice(product) {
        return parseFloat(product && product.purchase_price != null ? product.purchase_price : 0) || 0;
    }

    function applyUnitPrice($row, product) {
        if (!product) {
            return;
        }
        var mult = parseFloat($row.find('.unit-select option:selected').data('multiplier')) || 1;
        var piecePrice = piecePurchasePrice(product);
        $row.find('.unit-price').val((piecePrice * mult).toFixed(3));
    }

    function updateUnitSelect($row, product) {
        var $select = $row.find('.unit-select');
        var preferred = 'piece';
        if (product && product.measure_unit === 'carton') {
            preferred = 'bulk';
        } else if (product && product.measure_unit === 'kilo') {
            preferred = 'kilo';
        } else if (product && product.sale_mode === 'bulk_only') {
            preferred = 'bulk';
        }
        var current = $select.val() || preferred;
        $select.empty();
        unitsForProduct(product).forEach(function (u) {
            $select.append(
                $('<option></option>').val(u.key).text(u.label).attr('data-multiplier', u.multiplier)
            );
        });
        if ($select.find('option[value="' + current + '"]').length) {
            $select.val(current);
        } else if ($select.find('option[value="' + preferred + '"]').length) {
            $select.val(preferred);
        }
        applyUnitPrice($row, product);
        updatePiecesHint($row);
    }

    function updatePiecesHint($row) {
        var product = findProduct($row.find('.product-select').val());
        var mult = parseFloat($row.find('.unit-select option:selected').data('multiplier')) || 1;
        var qty = parseFloat($row.find('.entered-qty').val()) || 0;
        var pieces = Math.round(qty * mult * 1000) / 1000;
        var measure = product && product.measure_unit === 'kilo' ? 'كيلو' : 'حبة';
        $row.find('.unit-pieces-hint').text(pieces > 0 ? '= ' + pieces + ' ' + measure + ' في المخزون' : '');
    }

    function rowSubtotal($row) {
        var qty = parseFloat($row.find('.entered-qty').val()) || 0;
        var price = parseFloat($row.find('.unit-price').val()) || 0;
        return qty * price;
    }

    function refreshRow($row) {
        var sub = rowSubtotal($row);
        $row.find('.row-subtotal').text(sub.toFixed(2));
        updatePiecesHint($row);
    }

    function invoiceTotal() {
        var sum = 0;
        $('#purchaseItemsBody .purchase-item-row').each(function () {
            sum += rowSubtotal($(this));
        });
        return sum;
    }

    function refreshTotals() {
        var total = invoiceTotal();
        var paid = parseFloat($('#paidAmount').val()) || 0;
        var $alert = $('#paidAlert');

        if (paid > total + 0.001) {
            $alert.show();
            paid = total;
            $('#paidAmount').val(paid.toFixed(2));
        } else {
            $alert.hide();
        }

        var remaining = Math.max(total - paid, 0);
        $('#invoiceTotalDisplay').html(total.toFixed(2) + ' <small>ج.س</small>');
        $('#remainingDisplay').html(remaining.toFixed(2) + ' <small>ج.س</small>');

        if (remaining > 0.009) {
            $('#installmentsSection').slideDown(200);
        } else {
            $('#installmentsSection').slideUp(200);
        }

        return { total: total, remaining: remaining };
    }

    function bindRow($row) {
        $row.find('.product-select').on('change', function () {
            var product = findProduct($(this).val());
            updateUnitSelect($row, product);
            refreshRow($row);
            refreshTotals();
        });

        $row.find('.unit-select').on('change', function () {
            var product = findProduct($row.find('.product-select').val());
            applyUnitPrice($row, product);
            refreshRow($row);
            refreshTotals();
        });

        $row.find('.entered-qty, .unit-price').on('change input', function () {
            refreshRow($row);
            refreshTotals();
        });

        $row.find('.remove-purchase-row').on('click', function () {
            if ($('#purchaseItemsBody .purchase-item-row').length <= 1) {
                alert('يجب أن يبقى منتج واحد على الأقل.');
                return;
            }
            $row.remove();
            refreshTotals();
        });

        var product = findProduct($row.find('.product-select').val());
        updateUnitSelect($row, product);
        refreshRow($row);
    }

    function addRow() {
        var html = $('#purchaseRowTemplate').html().replace(/__INDEX__/g, rowIndex);
        var $row = $(html);
        $('#purchaseItemsBody').append($row);
        bindRow($row);
        rowIndex++;
        refreshTotals();
    }

    var installmentIndex = 0;

    function addInstallmentRow(amount, dueAt) {
        var $tr = $('<tr></tr>');
        $tr.html(
            '<td><input type="number" step="0.01" min="0.01" name="installments[' + installmentIndex + '][amount]" class="form-control installment-amount" value="' + (amount || '') + '"></td>' +
            '<td><input type="date" name="installments[' + installmentIndex + '][due_at]" class="form-control installment-due" value="' + (dueAt || '') + '"></td>' +
            '<td><input type="text" name="installments[' + installmentIndex + '][notes]" class="form-control" placeholder="اختياري"></td>' +
            '<td><button type="button" class="btn btn-danger btn-xs remove-installment"><i class="fa fa-times"></i></button></td>'
        );
        $('#installmentsBody').append($tr);
        installmentIndex++;
        $tr.find('.installment-amount, .installment-due').on('change input', validateInstallmentsSum);
        $tr.find('.remove-installment').on('click', function () {
            $tr.remove();
            validateInstallmentsSum();
        });
    }

    function validateInstallmentsSum() {
        var remaining = refreshTotals().remaining;
        var sum = 0;
        $('.installment-amount').each(function () {
            sum += parseFloat($(this).val()) || 0;
        });
        var $hint = $('#installmentsSumHint');
        if ($('#installmentsSection').is(':visible') && $('.installment-amount').length && Math.abs(sum - remaining) > 0.02) {
            $hint.text('مجموع الأقساط (' + sum.toFixed(2) + ') يجب أن يساوي المتبقي (' + remaining.toFixed(2) + ')').show();
        } else {
            $hint.hide();
        }
    }

    window.SaleUnitsHelper = {
        unitsForProduct: function (bulk, mode, measure) {
            bulk = Math.max(1, parseInt(bulk, 10) || 12);
            mode = mode || 'flexible';
            measure = measure || 'piece';

            if (measure === 'kilo') {
                return [{ key: 'kilo', label: 'كيلو', multiplier: 1 }];
            }

            if (mode === 'piece_only') {
                return [{ key: 'piece', label: 'حبة', multiplier: 1 }];
            }

            if (mode === 'bulk_only') {
                return [{ key: 'bulk', label: 'كرتونة (' + bulk + ' حبة)', multiplier: bulk }];
            }

            if (measure === 'carton' && bulk > 1) {
                return [
                    { key: 'piece', label: 'حبة', multiplier: 1 },
                    { key: 'half_carton', label: 'نصف كرتونة (' + Math.floor(bulk / 2) + ' حبة)', multiplier: Math.floor(bulk / 2) },
                    { key: 'bulk', label: 'كرتونة كاملة (' + bulk + ' حبة)', multiplier: bulk },
                ];
            }

            var units = [
                { key: 'piece', label: 'حبة', multiplier: 1 },
                { key: 'pack_3', label: '3 قطع', multiplier: 3 },
                { key: 'pack_6', label: '6 قطع', multiplier: 6 },
            ];
            if (bulk > 1) {
                units.push({ key: 'half_carton', label: 'نصف كرتونة (' + Math.floor(bulk / 2) + ' حبة)', multiplier: Math.floor(bulk / 2) });
                units.push({ key: 'bulk', label: 'عبوة (' + bulk + ' حبة)', multiplier: bulk });
            } else {
                units.push({ key: 'dozen', label: 'دستة (12)', multiplier: 12 });
            }
            return units;
        },
    };

    var $activeProductSelect = null;

    function appendProductToSelects(product) {
        catalog.push(product);
        $('.product-select').each(function () {
            if ($(this).find('option[value="' + product.id + '"]').length === 0) {
                $(this).append(
                    $('<option></option>').val(product.id).text(product.name)
                );
            }
        });
    }

    $(function () {
        rowIndex = $('#purchaseItemsBody .purchase-item-row').length;

        $('#purchaseItemsBody .purchase-item-row').each(function () {
            bindRow($(this));
        });

        $(document).on('focus', '.product-select', function () {
            $activeProductSelect = $(this);
        });

        $('#btnOpenQuickProduct').on('click', function () {
            if (!$activeProductSelect || !$activeProductSelect.length) {
                $activeProductSelect = $('#purchaseItemsBody .purchase-item-row:last .product-select');
            }
            $('#quickProductAlert').hide();
            $('#quickProductForm')[0].reset();
            $('#qpBulk').val(12);
            $('#qpSaleMode').val('flexible');
            $('#quickProductModal').modal('show');
        });

        $('#qpPurchasePrice').on('input', function () {
            var p = parseFloat($(this).val()) || 0;
            if (p > 0 && !$('#qpSalePrice').val()) {
                $('#qpSalePrice').attr('placeholder', (p * 1.15).toFixed(2) + ' (مقترح)');
            }
        });

        $('#quickProductForm').on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#qpSubmitBtn').prop('disabled', true);
            $('#quickProductAlert').hide();

            $.ajax({
                url: window.quickProductStoreUrl,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.csrfToken || $('meta[name="csrf-token"]').attr('content') },
                data: {
                    category_id: $('#qpCategory').val(),
                    name: $('#qpName').val(),
                    purchase_price: $('#qpPurchasePrice').val(),
                    sale_price: $('#qpSalePrice').val(),
                    pieces_per_carton: $('#qpBulk').val(),
                    sale_mode: $('#qpSaleMode').val(),
                    measure_unit: $('#qpMeasureUnit').val() || 'piece',
                    stock: 0,
                },
            })
                .done(function (res) {
                    appendProductToSelects(res.product);
                    if ($activeProductSelect && $activeProductSelect.length) {
                        $activeProductSelect.val(res.product.id).trigger('change');
                    }
                    $('#quickProductModal').modal('hide');
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

        $('#addPurchaseRow').on('click', addRow);
        $('#paidAmount').on('input change', refreshTotals);

        $('#addInstallmentRow').on('click', function () {
            addInstallmentRow('', '');
        });

        $('#splitRemainingBtn').on('click', function () {
            var rem = refreshTotals().remaining;
            if (rem <= 0) return;
            $('#installmentsBody').empty();
            installmentIndex = 0;
            var half = (rem / 2).toFixed(2);
            var rest = (rem - parseFloat(half)).toFixed(2);
            var d1 = new Date();
            var d2 = new Date();
            d2.setDate(d2.getDate() + 30);
            addInstallmentRow(half, d1.toISOString().slice(0, 10));
            addInstallmentRow(rest, d2.toISOString().slice(0, 10));
            validateInstallmentsSum();
        });

        $('#purchaseInvoiceForm').on('submit', function (e) {
            var total = invoiceTotal();
            if (total <= 0) {
                e.preventDefault();
                alert('أضف منتجاً بسعر صحيح.');
                return false;
            }
            var paid = parseFloat($('#paidAmount').val()) || 0;
            if (paid > total + 0.02) {
                e.preventDefault();
                alert('المدفوع أكبر من الإجمالي.');
                return false;
            }
            var remaining = total - paid;
            if (remaining > 0.009 && $('#installmentsSection').is(':visible')) {
                var sum = 0;
                $('.installment-amount').each(function () {
                    sum += parseFloat($(this).val()) || 0;
                });
                if ($('.installment-amount').length === 0) {
                    e.preventDefault();
                    alert('أضف قسطاً واحداً على الأقل لجدولة المتبقي، أو ادفع المبلغ كاملاً.');
                    return false;
                }
                if (Math.abs(sum - remaining) > 0.02) {
                    e.preventDefault();
                    alert('مجموع الأقساط يجب أن يساوي المتبقي (' + remaining.toFixed(2) + ' ج.س)');
                    return false;
                }
            }
        });

        refreshTotals();
    });
})(jQuery, window);
