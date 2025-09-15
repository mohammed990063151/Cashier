$(document).ready(function() {

    $('.add-product-btn').on('click', function(e) {
        e.preventDefault();
        var name = $(this).data('name');
        var id = $(this).data('id');
        var price = parseFloat($(this).data('price')); // سعر الوحدة
        var formattedPrice = $.number(price, 2);

        $(this).removeClass('btn-success').addClass('btn-default disabled');

        var html =
            `<tr>
        <td>${name}</td>
        <td>
            <input type="number" name="products[${id}][quantity]" data-price="${price}" class="form-control input-sm product-quantity" min="1" value="1">
        </td>
        <td>
            <input type="number" step="1" name="products[${id}][sale_price]" class="form-control input-sm product-unit-price" value="${price}">
        </td>
        <td>
            <span class="product-price">${formattedPrice}</span>
            <input type="hidden" name="products[${id}][total_price]" value="${price}">
        </td>
        <td>
            <button class="btn btn-danger btn-sm remove-product-btn" data-id="${id}"><span class="fa fa-trash"></span></button>
        </td>
    </tr>`;


        $('.order-list').append(html);
        $('#add-order-form-btn').prop('disabled', false).removeClass('disabled');
        calculateTotal();
    });

    // تحديث الإجمالي عند تغيير الكمية
    $('body').on('keyup change', '.product-quantity', function() {
        var quantity = Number($(this).val());
        var unitPrice = parseFloat($(this).data('price'));
        var total = quantity * unitPrice;

        $(this).closest('tr').find('.product-price').text($.number(total, 2));
        $(this).closest('tr').find('input[name$="[total_price]"]').val(total); // تحديث حقل المخفي للإجمالي

        calculateTotal();
    });

    // إزالة منتج
    $('body').on('click', '.remove-product-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        $(this).closest('tr').remove();
        $('#product-' + id).removeClass('btn-default disabled').addClass('btn-success');

        calculateTotal();

        // إذا لم يبق أي منتجات، أعد تعطيل الزر
        if ($('.order-list tr').length == 0) {
            $('#add-order-form-btn').prop('disabled', true).addClass('disabled');
        }
    });

    //disabled btn
    $('body').on('click', '.disabled', function(e) {

        e.preventDefault();

    }); //end of disabled

    $('body').on('keyup change', '.product-quantity', function() {

        var quantity = Number($(this).val()); //2
        var unitPrice = parseFloat($(this).data('price').replace(/,/g, '')); //150
        console.log(unitPrice);
        $(this).closest('tr').find('.product-price').html($.number(quantity * unitPrice, 2));
        calculateTotal();

    }); //end of product quantity change

    //list all order products
    $('.order-products').on('click', function(e) {

        e.preventDefault();

        $('#loading').css('display', 'flex');

        var url = $(this).data('url');
        var method = $(this).data('method');
        $.ajax({
            url: url,
            method: method,
            success: function(data) {

                $('#loading').css('display', 'none');
                $('#order-product-list').empty();
                $('#order-product-list').append(data);

            }
        })

    }); //end of order products click



}); //end of document ready




// function calculateTotal() {
//     let total = 0;

//     $('.order-list tr').each(function() {
//         let quantity = parseFloat($(this).find('.product-quantity').val()) || 0;
//         let unitPrice = parseFloat($(this).find('.product-unit-price').val()) || 0;

//         let productTotal = quantity * unitPrice;
//         $(this).find('.product-price').text(productTotal.toFixed(2));
//         $(this).find('input[name$="[total_price]"]').val(productTotal);

//         total += productTotal;
//     });

//     // إجمالي قبل الخصم
//     $('.total-price').text(total.toFixed(2));

//     // حساب الخصم
//     let invoiceDiscount = parseFloat($('#invoice_discount').val()) || 0;
//     let discountedTotal = total - invoiceDiscount;
//     if (discountedTotal < 0) discountedTotal = 0;

//     // المدفوع العام
//     let paid = parseFloat($('#discount').val()) || 0;

//     // المتبقي
//     let remaining = discountedTotal - paid;
//     if (remaining < 0) remaining = 0;

//     $('#remaining').val(remaining.toFixed(2));
// }

function calculateTotal() {
    let total = 0;

    $('.order-list tr').each(function() {
        let quantity = parseFloat($(this).find('.product-quantity').val()) || 0;
        let unitPrice = parseFloat($(this).find('.product-unit-price').val()) || 0;

        let productTotal = quantity * unitPrice;
        $(this).find('.product-price').text(productTotal.toFixed(2));
        $(this).find('input[name$="[total_price]"]').val(productTotal);

        total += productTotal;
    });

    // إجمالي قبل الخصم
    $('.total-price').text(total.toFixed(2));

    // حساب الخصم
    let invoiceDiscount = parseFloat($('#invoice_discount').val()) || 0;
    let discountedTotal = total - invoiceDiscount;
    if (discountedTotal < 0) discountedTotal = 0;

    // ✅ تحديث "الإجمالي بعد الخصم"
    $('#discounted-total').text(discountedTotal.toFixed(2));

    // المدفوع العام
    let paid = parseFloat($('#discount').val()) || 0;

    // المتبقي
    let remaining = discountedTotal - paid;
    if (remaining < 0) remaining = 0;

    $('#remaining').val(remaining.toFixed(2));
}

// تحديث عند تغيير الكمية أو السعر
// $(document).on('input', '.product-quantity, .product-unit-price', function() {
//     calculateTotal();
// });
$(document).on('input', '.product-quantity, .product-unit-price, #invoice_discount, #discount', function() {
    calculateTotal();
});


// تحديث المتبقي عند تغيير الخصم
$('#discount').on('keyup change', function() {
    calculateTotal();
});
