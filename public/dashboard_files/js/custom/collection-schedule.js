(function ($) {
    var remaining = 0;
    var rowIndex = 0;

    function updateSum() {
        var sum = 0;
        $('#installmentRows .inst-amount').each(function () {
            sum += parseFloat($(this).val()) || 0;
        });
        $('#installmentSum').text(sum.toFixed(2));
        var diff = Math.abs(sum - remaining);
        $('#installmentSum').css('color', diff <= 0.02 ? '#27ae60' : '#c0392b');
    }

    function addRow(amount, dueAt, notes) {
        var html =
            '<tr data-row="' + rowIndex + '">' +
            '<td><input type="number" step="0.01" min="0.01" name="installments[' + rowIndex + '][amount]" class="form-control inst-amount" value="' + (amount || '') + '"></td>' +
            '<td><input type="date" name="installments[' + rowIndex + '][due_at]" class="form-control" value="' + (dueAt || '') + '"></td>' +
            '<td><input type="text" name="installments[' + rowIndex + '][notes]" class="form-control" value="' + (notes || '') + '"></td>' +
            '<td><button type="button" class="btn btn-xs btn-danger btn-remove-row"><i class="fa fa-times"></i></button></td>' +
            '</tr>';
        $('#installmentRows').append(html);
        rowIndex++;
        updateSum();
    }

    function formatDate(d) {
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + month + '-' + day;
    }

    function parseLocalDate(value) {
        var parts = String(value || '').split('-');
        if (parts.length !== 3) {
            return new Date();
        }
        return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    }

    function autoSplit(preserveAmounts) {
        var count = parseInt($('#splitCount').val(), 10) || 1;
        count = Math.min(24, Math.max(1, count));
        var start = $('#splitStart').val();
        var interval = parseInt($('#splitInterval').val(), 10) || 30;
        if (!start || remaining <= 0) {
            return;
        }

        var existing = [];
        if (preserveAmounts) {
            $('#installmentRows .inst-amount').each(function () {
                existing.push($(this).val());
            });
            if (existing.length !== count) {
                preserveAmounts = false;
            }
        }

        var part = Math.floor((remaining / count) * 100) / 100;
        var last = Math.round((remaining - part * (count - 1)) * 100) / 100;

        $('#installmentRows').empty();
        rowIndex = 0;

        var due = parseLocalDate(start);
        for (var i = 0; i < count; i++) {
            var generated = (i === count - 1 ? last : part).toFixed(2);
            var amount = preserveAmounts && existing[i] !== '' ? existing[i] : generated;
            addRow(amount, formatDate(due), 'قسط ' + (i + 1));
            due.setDate(due.getDate() + interval);
        }
    }

    $('#installmentModal').on('show.bs.modal', function (e) {
        var btn = $(e.relatedTarget);
        var orderId = btn.data('order-id');
        remaining = parseFloat(btn.data('remaining')) || 0;

        var urlTemplate = window.collectionInstallmentUrl || '/dashboard/collection-schedules/__ORDER__/installments';
        $('#installmentForm').attr('action', urlTemplate.replace('__ORDER__', orderId));
        $('#modalOrderTitle').text('#' + btn.data('order-number') + ' — ' + btn.data('client-name'));
        $('#modalRemaining').text(remaining.toFixed(2));
        $('#installmentTarget').text(remaining.toFixed(2));

        $('#splitStart').val(formatDate(new Date()));
        $('#splitCount').val(3);
        $('#splitInterval').val(30);

        $('#installmentRows').empty();
        rowIndex = 0;
        autoSplit(false);
    });

    $('#btnAddRow').on('click', function () {
        addRow('', '', '');
    });

    $(document).on('click', '.btn-remove-row', function () {
        if ($('#installmentRows tr').length > 1) {
            $(this).closest('tr').remove();
            updateSum();
        }
    });

    $(document).on('input', '.inst-amount', updateSum);

    $('#splitCount').on('input change', function () {
        autoSplit(false);
    });

    $('#splitStart, #splitInterval').on('input change', function () {
        autoSplit(true);
    });

    $('#btnAutoSplit').on('click', function () {
        if (!$('#splitStart').val()) {
            alert('حدد تاريخ أول قسط');
            return;
        }
        autoSplit(false);
    });

    $('#installmentForm').on('submit', function (e) {
        var sum = 0;
        $('.inst-amount').each(function () {
            sum += parseFloat($(this).val()) || 0;
        });
        if (Math.abs(sum - remaining) > 0.02) {
            e.preventDefault();
            alert('مجموع الأقساط (' + sum.toFixed(2) + ') يجب أن يساوي المتبقي (' + remaining.toFixed(2) + ')');
        }
    });
})(jQuery);
