const SALE_UNITS = {
    piece: { label: 'حبة', multiplier: 1, step: '1' },
    pack_3: { label: '3 قطع', multiplier: 3, step: '1' },
    pack_6: { label: '6 قطع', multiplier: 6, step: '1' },
    dozen: { label: 'دستة (12)', multiplier: 12, step: '1' },
};

function parseNumber(value) {
    if (value === null || value === undefined) {
        return 0;
    }
    const cleaned = String(value).replace(/,/g, '').trim();
    const num = parseFloat(cleaned);
    return Number.isFinite(num) ? num : 0;
}

function round3(value) {
    return Math.round(parseNumber(value) * 1000) / 1000;
}

function formatMoney(value) {
    const n = round3(value);
    return n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
}

function formatPriceInput(value) {
    const n = round3(value);
    return n > 0 ? String(n) : '0';
}

function displayQty(value) {
    return String(round3(value));
}

function halfCartonPieces(bulkSize) {
    return round3(Math.max(1, parseInt(bulkSize, 10) || 12) / 2);
}

function bulkLabel(bulkSize) {
    const size = Math.max(1, parseInt(bulkSize, 10) || 12);
    if (size <= 1) {
        return 'حبة';
    }
    return 'كرتونة كاملة (' + size + ' حبة)';
}

function halfCartonLabel(bulkSize) {
    return 'نصف كرتونة (' + displayQty(halfCartonPieces(bulkSize)) + ' حبة)';
}

function unitsForProduct(bulkSize, saleMode, measureUnit) {
    const bulk = Math.max(1, parseInt(bulkSize, 10) || 12);
    const mode = saleMode || 'flexible';
    const measure = measureUnit || 'piece';

    if (measure === 'kilo') {
        return [{ key: 'kilo', label: 'كيلو', multiplier: 1, step: '0.001', isMaster: true }];
    }

    if (mode === 'piece_only') {
        return [{ key: 'piece', ...SALE_UNITS.piece, isMaster: true }];
    }

    if (mode === 'bulk_only') {
        return [{ key: 'bulk', label: bulkLabel(bulk), multiplier: bulk, step: '0.001', isMaster: true }];
    }

    // بيع مرن — للكرتونة أو أي منتج له عبوة
    if (measure === 'carton' && bulk > 1) {
        return [
            { key: 'piece', ...SALE_UNITS.piece, isMaster: true },
            { key: 'half_carton', label: halfCartonLabel(bulk), multiplier: halfCartonPieces(bulk), step: '1' },
            { key: 'bulk', label: bulkLabel(bulk), multiplier: bulk, step: '0.001' },
        ];
    }

    const units = [
        { key: 'piece', ...SALE_UNITS.piece, isMaster: true },
        { key: 'pack_3', ...SALE_UNITS.pack_3 },
        { key: 'pack_6', ...SALE_UNITS.pack_6 },
    ];

    if (bulk > 1) {
        units.push({ key: 'half_carton', label: halfCartonLabel(bulk), multiplier: halfCartonPieces(bulk), step: '1' });
        units.push({ key: 'bulk', label: bulkLabel(bulk), multiplier: bulk, step: '0.001' });
    } else {
        units.push({ key: 'dozen', ...SALE_UNITS.dozen });
    }

    return units;
}

function unitBlockHtml(productId, unit, unitPrice) {
    const priceVal = formatPriceInput(unitPrice);
    const masterClass = unit.isMaster ? ' unit-price-master' : '';
    const step = unit.step || '1';
    const hintUnit = unit.key === 'kilo' ? 'كيلو' : (unit.multiplier + ' حبة');

    return `
        <div class="order-unit-block" data-unit="${unit.key}" data-multiplier="${unit.multiplier}">
            <div class="order-unit-title">${unit.label}</div>
            <div class="row" style="margin:0 -5px;">
                <div class="col-xs-6" style="padding:0 5px;">
                    <label class="order-unit-label">الكمية</label>
                    <input type="number" min="0" step="${step}" value="0"
                        name="products[${productId}][${unit.key}][qty]"
                        class="form-control input-sm unit-qty">
                </div>
                <div class="col-xs-6" style="padding:0 5px;">
                    <label class="order-unit-label">السعر</label>
                    <input type="number" min="0" step="0.001" value="${priceVal}"
                        name="products[${productId}][${unit.key}][price]"
                        class="form-control input-sm unit-price${masterClass}">
                </div>
            </div>
            <small class="text-muted unit-hint">= ${hintUnit}</small>
        </div>
    `;
}

function unitBlocksHtml(productId, piecePrice, bulkSize, saleMode, measureUnit) {
    const price = round3(piecePrice);
    const units = unitsForProduct(bulkSize, saleMode, measureUnit);
    let html = '<div class="order-unit-grid">';

    units.forEach(function (unit) {
        html += unitBlockHtml(productId, unit, round3(price * unit.multiplier));
    });

    html += '</div>';

    return html;
}

function getPiecePriceFromRow($row) {
    const $master = $row.find('.unit-price-master');
    const bulkSize = Math.max(1, parseInt($row.data('bulk-size'), 10) || 12);

    if ($master.length) {
        const masterPrice = round3($master.val());
        const masterUnit = $master.closest('.order-unit-block').data('unit');
        if (masterUnit === 'bulk') {
            return round3(masterPrice / bulkSize);
        }
        return masterPrice;
    }

    return round3($row.find('.order-unit-block[data-unit="piece"] .unit-price').val());
}

function syncUnitPricesFromMaster($row) {
    const piecePrice = getPiecePriceFromRow($row);
    if (piecePrice <= 0) {
        return;
    }

    $row.find('.order-unit-block').each(function () {
        const multiplier = parseFloat($(this).data('multiplier')) || 1;
        const $priceInput = $(this).find('.unit-price');
        $priceInput.val(formatPriceInput(piecePrice * multiplier));
    });
}

function calculateRowTotal($row) {
    let lineTotal = 0;
    let totalPieces = 0;
    const measure = $row.data('measure-unit') || 'piece';
    const available = round3($row.data('stock'));

    $row.find('.order-unit-block').each(function () {
        const qty = round3($(this).find('.unit-qty').val());
        const price = round3($(this).find('.unit-price').val());
        const multiplier = parseFloat($(this).data('multiplier')) || 1;

        lineTotal = round3(lineTotal + round3(qty * price));
        totalPieces = round3(totalPieces + round3(qty * multiplier));
    });

    $row.find('.product-price').text(formatMoney(lineTotal));
    const unitLabel = measure === 'kilo' ? ' كيلو' : ' حبة';
    $row.find('.total-pieces-hint').text(totalPieces > 0 ? displayQty(totalPieces) + unitLabel : '0' + unitLabel);
    $row.find('input[name$="[total_price]"]').val(round3(lineTotal).toFixed(3));

    const $warn = $row.find('.stock-warning');
    if ($warn.length) {
        if (totalPieces > available + 0.0005) {
            $warn.text('تجاوز المخزون! المتاح ' + displayQty(available) + unitLabel).show();
        } else {
            $warn.hide().text('');
        }
    }

    return lineTotal;
}

function calculateTotal() {
    let total = 0;

    $('.order-list tr.order-item').each(function () {
        total = round3(total + calculateRowTotal($(this)));
    });

    $('.total-price').text(formatMoney(total));

    const invoiceDiscount = round3($('#invoice_discount').val());
    let discountedTotal = round3(total - invoiceDiscount);
    if (discountedTotal < 0) {
        discountedTotal = 0;
    }

    $('#discounted-total').text(formatMoney(discountedTotal));

    const paid = round3($('#paid_at_sale').val());
    let remaining = round3(discountedTotal - paid);
    if (remaining < 0) {
        remaining = 0;
    }

    const $remainingDisplay = $('#remaining-display');
    if ($remainingDisplay.length) {
        $remainingDisplay.text(formatMoney(remaining));
        $remainingDisplay.toggleClass('text-danger', remaining > 0);
        $remainingDisplay.toggleClass('text-success', remaining <= 0);
    }
}

function buildOrderRow(name, id, piecePrice, bulkSize, saleMode, measureUnit, stock) {
    const price = round3(piecePrice);
    const mode = saleMode || 'flexible';
    const measure = measureUnit || 'piece';
    const available = round3(stock);
    const zeroHint = measure === 'kilo' ? '0 كيلو' : '0 حبة';
    const stockHint = measure === 'kilo'
        ? ('المتاح: ' + displayQty(available) + ' كيلو')
        : ('المتاح: ' + displayQty(available) + ' حبة'
            + (bulkSize > 1 ? ' ≈ ' + displayQty(available / bulkSize) + ' كرتونة' : ''));

    return `
        <tr class="order-item" data-id="${id}" data-bulk-size="${bulkSize}" data-sale-mode="${mode}" data-measure-unit="${measure}" data-stock="${available}">
            <td>
                <strong>${name}</strong>
                <div class="text-muted total-pieces-hint" style="font-size:12px;">${zeroHint}</div>
                <div class="text-info" style="font-size:11px;">${stockHint}</div>
                <div class="text-danger stock-warning" style="font-size:11px;display:none;"></div>
            </td>
            <td colspan="2">${unitBlocksHtml(id, price, bulkSize, mode, measure)}</td>
            <td>
                <span class="product-price" style="color:#01941f;font-weight:bold;">${formatMoney(0)}</span>
                <input type="hidden" name="products[${id}][total_price]" value="0">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-product-btn" data-id="${id}">
                    <span class="fa fa-trash"></span>
                </button>
            </td>
        </tr>
    `;
}

$(document).ready(function () {
    $('.add-product-btn').on('click', function (e) {
        e.preventDefault();

        const name = $(this).data('name');
        const id = $(this).data('id');
        const price = $(this).data('price');
        const bulkSize = $(this).data('bulk-size') || $(this).data('carton-size') || 12;
        const saleMode = $(this).data('sale-mode') || 'flexible';
        const measureUnit = $(this).data('measure-unit') || 'piece';
        const stock = $(this).data('stock');

        if ($('.order-list tr.order-item[data-id="' + id + '"]').length) {
            return;
        }

        $(this).removeClass('btn-success').addClass('btn-default disabled');

        $('.order-list').append(buildOrderRow(name, id, price, bulkSize, saleMode, measureUnit, stock));
        $('#add-order-form-btn').prop('disabled', false).removeClass('disabled');
        calculateTotal();
    });

    $('body').on('input', '.unit-qty, .unit-price', function () {
        const $row = $(this).closest('tr.order-item');
        calculateRowTotal($row);
        calculateTotal();
    });

    $('body').on('focusout', '.unit-price-master', function () {
        const $row = $(this).closest('tr.order-item');
        syncUnitPricesFromMaster($row);
        calculateRowTotal($row);
        calculateTotal();
    });

    $('body').on('click', '.remove-product-btn', function (e) {
        e.preventDefault();
        const id = $(this).data('id');

        $(this).closest('tr').remove();
        $('#product-' + id).removeClass('btn-default disabled').addClass('btn-success');

        calculateTotal();

        if ($('.order-list tr.order-item').length === 0) {
            $('#add-order-form-btn').prop('disabled', true).addClass('disabled');
        }
    });

    $('body').on('click', '.disabled', function (e) {
        e.preventDefault();
    });

    $(document).on('input', '#invoice_discount, #paid_at_sale', function () {
        calculateTotal();
    });

    $('body').on('click', '.order-products', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const $row = $btn.closest('tr.orders-row');

        $('.orders-row').removeClass('active');
        if ($row.length) {
            $row.addClass('active');
        }

        const $loading = $('#loading');
        const $list = $('#order-product-list');

        if ($loading.length) {
            $loading.css('display', 'flex');
        }
        if ($list.length) {
            $list.find('#orders-preview-placeholder').remove();
        }

        $.ajax({
            url: $btn.data('url'),
            method: $btn.data('method') || 'get',
            success: function (data) {
                if ($loading.length) {
                    $loading.css('display', 'none');
                }
                if ($list.length) {
                    $list.html(data);
                }
            },
            error: function () {
                if ($loading.length) {
                    $loading.css('display', 'none');
                }
                if ($list.length) {
                    $list.html(
                        '<div class="orders-preview-empty"><i class="fa fa-exclamation-triangle"></i><p>تعذّر تحميل المعاينة</p></div>'
                    );
                }
            },
        });
    });
});
