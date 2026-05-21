(function ($) {
    var siIndex = 0;
    var siRemaining = 0;

    function addSiRow(amount, dueAt) {
        var html =
            '<tr>' +
            '<td><input type="number" step="0.01" min="0.01" name="installments[' + siIndex + '][amount]" class="form-control si-amount" value="' + (amount || '') + '"></td>' +
            '<td><input type="date" name="installments[' + siIndex + '][due_at]" class="form-control si-due" value="' + (dueAt || '') + '"></td>' +
            '<td><button type="button" class="btn btn-danger btn-xs si-remove"><i class="fa fa-times"></i></button></td>' +
            '</tr>';
        $('#siInstallmentRows').append(html);
        siIndex++;
        validateSiSum();
    }

    function validateSiSum() {
        var sum = 0;
        $('.si-amount').each(function () {
            sum += parseFloat($(this).val()) || 0;
        });
        if ($('.si-amount').length && Math.abs(sum - siRemaining) > 0.02) {
            $('#siSumError').text('المجموع ' + sum.toFixed(2) + ' يجب أن يساوي ' + siRemaining.toFixed(2)).show();
        } else {
            $('#siSumError').hide();
        }
    }

    $(function () {
        $(document).on('click', '.btn-schedule-installments', function () {
            var $btn = $(this);
            siRemaining = parseFloat($btn.data('remaining')) || 0;
            siIndex = 0;
            $('#siInvoiceNumber').text($btn.data('invoice-number'));
            $('#siRemaining').text(siRemaining.toFixed(2));
            $('#supplierInstallmentForm').attr('action', $btn.data('action'));
            $('#siInstallmentRows').empty();
            addSiRow('', '');
            $('#supplierInstallmentModal').modal('show');
        });

        $('#siAddRow').on('click', function () {
            addSiRow('', '');
        });

        $('#siSplitTwo').on('click', function () {
            $('#siInstallmentRows').empty();
            siIndex = 0;
            var half = (siRemaining / 2).toFixed(2);
            var rest = (siRemaining - parseFloat(half)).toFixed(2);
            var d1 = new Date().toISOString().slice(0, 10);
            var d2 = new Date();
            d2.setDate(d2.getDate() + 30);
            addSiRow(half, d1);
            addSiRow(rest, d2.toISOString().slice(0, 10));
        });

        $(document).on('input change', '.si-amount', validateSiSum);
        $(document).on('click', '.si-remove', function () {
            $(this).closest('tr').remove();
            validateSiSum();
        });

        $('#supplierInstallmentForm').on('submit', function (e) {
            var sum = 0;
            $('.si-amount').each(function () {
                sum += parseFloat($(this).val()) || 0;
            });
            if ($('.si-amount').length === 0 || Math.abs(sum - siRemaining) > 0.02) {
                e.preventDefault();
                alert('مجموع الأقساط يجب أن يساوي المتبقي.');
            }
        });
    });
})(jQuery);
