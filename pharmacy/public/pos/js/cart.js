"use strict"
let canSubmit = false;
// Add to cart
function ADD_TO_CART(id, getterUri) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    playMusic();
    // Get product by id
    $.post({
        url: getterUri,
        data: {product_id: id},
        beforeSend: function () {
            $('#loading').show();
        },
        success: function ({view,added,no_batch}) {
            if (no_batch){
                toastr.warning('No batch available!', {
                    CloseButton: true,
                    ProgressBar: true,
                    PositionClass: 'toast-top-right'
                });
            }
            if (added){
                toastr.success('Product has been added!', {
                    CloseButton: true,
                    ProgressBar: true,
                    PositionClass: 'toast-top-right'
                });
            }

            $('#cart').empty().html(view);
            //updateCart();
            $('.search-result-box').empty().hide();
            $('#search').val('');
            watch();
        },
        complete: function () {
            $('#loading').hide();
        }
        
    });
}

// Remove item from cart
function REMOVE_FROM_CART(productId, getterURI) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    // Get product by id
    $.post({
        url: getterURI,
        data: {product_id: productId},
        success: function (data) {
            toastr.success('Item has been removed!', {
                CloseButton: true,
                ProgressBar: true
            });
            $('#cart').empty().html(data.view);
            watch();
        },

    });
}

// Get bacth info and update price and expire date
function setBatch(batch_id, cart_id, setterURI) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    if (batch_id) {
        $.post({
            url: setterURI,
            data: {batch_id: batch_id, cart: cart_id},
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    $('#cart').empty().html(res.view);
                }
                if (res.error) {
                    toastr.error(res.message, {
                        CloseButton: true,
                        ProgressBar: true,
                        "positionClass": "toast-top-right",
                    });
                }
                watch();
            },
        });
    }
}

// Update quantity
function quantityUpdate(id, setterURI) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.post({
        url: setterURI,
        data: { product_id: id },
        beforeSend: function () {
            $('#loading').show();
        },
        success: function (data) {
            if (data.res.quantity_over) {
                toastr.error('Product quantity is over the limit', {
                    CloseButton: true,
                    ProgressBar: true,
                    positionClass: "toast-top-right",
                });
            } else if (data.res.batch_not_found) {
                toastr.error('Please select a batch.', {
                    CloseButton: true,
                    ProgressBar: true,
                    positionClass: "toast-top-right",
                });
            } else if (data.res.success) {
                $('#cart').empty().html(data.view); // Reload the cart view
                toastr.success('Quantity updated successfully!', {
                    CloseButton: true,
                    ProgressBar: true,
                    positionClass: "toast-top-right",
                });
            }
        },
        error: function (xhr) {
            toastr.error('Something went wrong! Please try again.', {
                CloseButton: true,
                ProgressBar: true,
                positionClass: "toast-top-right",
            });
        },
        complete: function () {
            $('#loading').hide();
        }
    });
}


function quantityInputed(qty,id, setterURI) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.post({
        url: setterURI,
        data: {product_id: id, quantity: qty},
        beforeSend: function () {
            $('#loading').show();
        },
        success: function (data) {
            if (data.res.quantity_over) {
                toastr.error('Product quantity is over the limit', {
                    CloseButton: true,
                    ProgressBar: true,
                    "positionClass": "toast-top-right",
                });
            }
            if (data.res.batch_not_found) {
                toastr.error('Please select batch', {
                    CloseButton: true,
                    ProgressBar: true,
                    "positionClass": "toast-top-right",
                });
            }
            $('#cart').empty().html(data.view);
            watch();
            toastr.success('Item quantity updated!', {
                CloseButton: true,
                ProgressBar: true,
                "positionClass": "toast-top-right",
            });
        },
        complete: function () {
            $('#loading').hide();
        }
    });
}


/***************************************
 * Cart Calculations
 *******************************************/
// Calculate Product dicount
function setProductDiscount(value, productId, setterURI) {
    if (value > 100){
        toastr.error('Discount amount can\'t be over 100% !', {
            CloseButton: true,
            ProgressBar: true
        });
        return false
    }
    $.post({
        url: setterURI,
        data: {product_id: productId, discount_amount: value},
        success: function (data) {
            if (data.success) {
                toastr.success('Discount has been added!', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
            $('#cart').empty().html(data.view);
            watch();
        },
    });
}

// Calculate set invoice discount
function setInvoiceDiscount() {
    let subTotalField = $('#subtotal');
    let totalDiscountField = $('#total_discount_ammount');
    let invoice_discount_type = $('#invoice__discount__type').find("option:selected").val();
    let amount = $('#invoice_discount').val();
    const subtotal = subTotalField.data('subtotal');
    const discountAmount = totalDiscountField.data('totalamount');
    const inputAmount = amount == '' ? 0 : amount;
    // Check invoice discount type
    let invoiceDiscountAmount = inputAmount;
    if (invoice_discount_type === 'percent'){
        invoiceDiscountAmount = (parseInt(subtotal) * inputAmount / 100);
    }
    

    const total_amount = (parseFloat(discountAmount) + parseFloat(invoiceDiscountAmount));
    totalDiscountField.val(amountFormatted(parseFloat(total_amount)))
    calculateGrandTotal();
}


// Calculate grand total
function calculateGrandTotal() {
    const invoiceDiscountAmount = $('#total_discount_ammount').val();
    let subTotalField = $('#grandTotal');
    let subtotal = $('#subtotal').data('subtotal');
    const inputAmount = subTotalField.data('grandtotal');
    const invoiceDiscountedAmount = (parseFloat(inputAmount) - parseFloat(invoiceDiscountAmount));

    let taxInputAmount =  $('#tax_amount').val();
    let taxValue = !isNaN(taxInputAmount) ? taxInputAmount : 0;
    const tax = (subtotal - invoiceDiscountAmount) * parseInt(taxValue) / 100;
    const totatVatTax = !isNaN(tax) ? tax : 0;
    const total = invoiceDiscountedAmount + totatVatTax;

    subTotalField.val(amountFormatted(total))
    $('#net_total_text').text(amountFormatted(total))
    $('#n_total').val(amountFormatted(total))
    setDueAmount();
}



function invoice_paidamount(amount) {
    let netTotalAmount = $('#n_total').val();
    const inputAmount = amount ? amount : 0;
    let dueAmount = (netTotalAmount - inputAmount);
    let totalDueAmount = 0.00
    let totalChangeAmount = 0.00
    if (dueAmount > 0) {
        totalDueAmount = dueAmount;
    } else {
        totalChangeAmount = dueAmount;
    }
    setDueAmount();
    $("#recieved_amount").val(inputAmount);
    $("#change").val(Math.abs(amountFormatted(totalChangeAmount)));
}

function fullPaid() {
    let netTotalAmount = $('#n_total').val();
    $('#paidAmount').val(netTotalAmount);
    $("#recieved_amount").val(netTotalAmount);
    setDueAmount();
}

function setNetPayAmount() {
    let subTotalField = $('#grandTotal');
    let payableAmount = subTotalField.data('grandtotal');
    $('#net_total_text').text(amountFormatted(payableAmount))
    $('#n_total').val(amountFormatted(payableAmount))
}


function setDueAmount() {
    let netTotalAmount = $('#n_total').val();
    const totalDue = (netTotalAmount - $('#paidAmount').val());
    let totalDueAmount = 0.00
    if (totalDue > 0){
        totalDueAmount = totalDue;
    }
    $('#due_amount').val(amountFormatted(totalDueAmount));
    $('#due_text').text(amountFormatted(totalDueAmount));
}

function amountFormatted(amount) {
    return amount.toFixed(2)
}


// Place Order
function placeOrder(pay_with)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    let action  = $("#placeOrder").attr('action');
    const paid_amount = $('#paidAmount').val();
    const due_amount = $("#due_amount").val();
    let payment_method = pay_with;
    let customer_id = $('#customer').val();

    if (due_amount > 0 && customer_id == 0){
        showError('Please add as a customer! If the sale is due, customer information is mandatory.');
        return false;
    }

    if (pay_with == 'mfs'){
        payment_method = $('#payment_method').val()
    }
    // if (paid_amount == 0){
    //     showError('Please enter paid amount!');
    //     return false;
    // }
    if (!payment_method){
        showError('Please select payment method!');
        return false;
    }

    let data = {
        customer_id: $('#customer').val(),
        payment_method: payment_method,
        sub_total: $('#subtotal').data('subtotal'),
        invoice__discount__type: $('#invoice__discount__type').val(),
        invoice_discount: $('#invoice_discount').val(),
        total_discount: $('#total_discount_ammount').val(),
        vat: $('#vat').val(),
        tax: $('#tax_amount').val(),
        igta: $('#igta_amount').val(),
        grand_total: $('#grandTotal').data('grandtotal'),
        paid_amount: paid_amount,
        due_amount: due_amount,
        recieve_amount: $("#recieved_amount").val(),
        change_amount: $("#change").val(),
    }


    let batches = document.querySelectorAll('.batch');
    let expires = document.querySelectorAll('#expire');
    let emptyBatch = 0;
    let emptyExpire = 0;
    batches.forEach(batch => {
        if (batch.value ==  null || batch.value == "") {
            emptyBatch ++;
        }
    });
    expires.forEach(expire => {

        if (expire.value ==  null || expire.value == "") {
            emptyExpire ++;
        }
    });
    if (emptyBatch < 1 && emptyExpire < 1) {
        $.post({
            url: action,
            data: data,
            success: function (res) {
                if (res.error){
                    showError(res.message);
                }
                if (res.success) {
                    toastr.success(res.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    Swal.fire({
                        title: 'Order has been placed successfully!',
                        type: 'success',
                        showCancelButton: true,
                        cancelButtonColor: '#e01313',
                        confirmButtonColor: '#161853',
                        cancelButtonText: 'Cancel',
                        confirmButtonText: 'Print Invoice',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.value) {
                            window.location.href = res.redirect_url;
                        } else {
                            window.location.reload()
                        }
                    })
                }

            },
        });
    }else {
        showError('Please select batch and expire')
    }

}

function watch(){
    setNetPayAmount();
    setDueAmount();
}

function init(){
    fullPaid();
    invoice_paidamount();
    calculateGrandTotal();
    watch();
}
$(document).ready(function (){
    init();
})
function showError(message){
    toastr.error(message, {
        CloseButton: true,
        ProgressBar: true,
        "positionClass": "toast-top-right",
    });
}
function playMusic() {
    var audio = document.getElementById("audio");
    audio.play();
}

