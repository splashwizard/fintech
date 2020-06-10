var selected_bank = 0;
function copyTextToClipboard(text) {
    var textArea = document.createElement("textarea");

    //
    // *** This styling is an extra step which is likely not required. ***
    //
    // Why is it here? To ensure:
    // 1. the element is able to have focus and selection.
    // 2. if element was to flash render it has minimal visual impact.
    // 3. less flakyness with selection and copying which **might** occur if
    //    the textarea element is not visible.
    //
    // The likelihood is the element won't even render, not even a
    // flash, so some of these are just precautions. However in
    // Internet Explorer the element is visible whilst the popup
    // box asking the user for permission for the web page to
    // copy to the clipboard.
    //

    // Place in top-left corner of screen regardless of scroll position.
    textArea.style.position = 'fixed';
    textArea.style.top = 0;
    textArea.style.left = 0;

    // Ensure it has a small width and height. Setting to 1px / 1em
    // doesn't work as this gives a negative w/h on some browsers.
    textArea.style.width = '2em';
    textArea.style.height = '2em';

    // We don't need padding, reducing the size if it does flash render.
    textArea.style.padding = 0;

    // Clean up any borders.
    textArea.style.border = 'none';
    textArea.style.outline = 'none';
    textArea.style.boxShadow = 'none';

    // Avoid flash of white box if rendered for any reason.
    textArea.style.background = 'transparent';


    textArea.value = text;

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        console.log('Copying text command was ' + msg);
    } catch (err) {
        console.log('Oops, unable to copy');
    }

    document.body.removeChild(textArea);
}
var pos_form_obj;
$(document).ready(function() {
    customer_set = false;

    var variation_ids = [];
    if(edit_page){
        variation_ids = variation_ids_before;
        // $('#bank_in_time').attr('readonly', '');
        // $('#bank_box').hide();
        $('body').css('background-color', 'lightblue');
    }

    //Prevent enter key function except texarea
    $('form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13 && e.target.tagName != 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });

    //For edit pos form
    if ($('form#edit_pos_sell_form').length > 0) {
        pos_total_row();
        pos_form_obj = $('form#edit_pos_sell_form');
    } else {
        pos_form_obj = $('form#add_pos_sell_form');
    }
    if ($('form#edit_pos_sell_form').length > 0 || $('form#add_pos_sell_form').length > 0) {
        initialize_printer();
    }

    $('select#select_location_id').change(function() {
        reset_pos_form();
    });

    $('#bank_in_time').change(function () {
       $('#bank_changed').val(1);
    });

    $('#add_request_modal').on('shown.bs.modal', function(e) {
        $('#add_request_modal .select2').select2();

        $('form#add_request_form #start_date, form#add_request_form #end_date').datepicker({
            autoclose: true,
        });
    });

    $(document).on('submit', 'form#add_request_form', function(e) {
        e.preventDefault();
        $(this).find('button[type="submit"]').attr('disabled', true);
        var data = $(this).serialize();

        $.ajax({
            method: $(this).attr('method'),
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success == true) {
                    $('div#add_request_modal').modal('hide');
                    toastr.success(result.msg);
                    request_table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //get customer
    $('#customer_id').select2({
        ajax: {
            url: '/contacts/customers',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                };
            },
            processResults: function(data) {
                return {
                    results: data,
                };
            },
        },
        templateResult: function (data) { 
            // var template = data.text + "<br>" + LANG.mobile + ": " + data.mobile;
            // if (typeof(data.total_rp) != "undefined") {
            //     var rp = data.total_rp ? data.total_rp : 0;
            //     template += "<br><i class='fa fa-gift text-success'></i> " + rp;
            // }
            var template = data.contact_id;

            return template;
        },
        minimumInputLength: 1,
        language: {
            noResults: function() {
                var name = $('#customer_id')
                    .data('select2')
                    .dropdown.$search.val();
                return (
                    '<button type="button" data-name="' +
                    name +
                    '" class="btn btn-link add_new_customer"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                    __translate('add_name_as_new_customer', { name: name }) +
                    '</button>'
                );
            },
        },
        escapeMarkup: function(markup) {
            return markup;
        },
    });
    function selectCustomer(){
        $('#customer_id').prop('disabled', true);
        var text = $('#customer_id').select2('data')[0].text;
        if( text == "Unclaimed Trans") {
            $('#service_box').hide();
            // $('#bonus_box').hide();
        } else {
            // $('#bonus_box').show();
            $('#service_box').show();
        }

        var location_id = $('input#location_id').val();
        var category_id = $('select#product_category').val();
        var product_id = $('select#bank_products').val();
        var brand_id = $('select#product_brand').val();

        var cur_customer_id = $('#customer_id').val();
        reset_pos_form();
        $('select#customer_id')
            .val(cur_customer_id)
            .trigger('change');
        for(var i = 0; i < variation_ids.length; i++){
            pos_product_row(variation_ids[i]);
        }
        get_product_suggestion_list(category_id, product_id, brand_id, location_id);
    }

    $('#customer_id').on('select2:select', function(e) {
        selectCustomer();
        updateBasicBonusRate();
        updateRemarks();
        getCustomerRewardPoints();
    });

    set_default_customer();
    $('#contact_id').val($('#customer_id').val());

    const text = $('#customer_id').select2('data')[0].text;
    updateRemarks();




    //Add Product
    // $('#search_product')
    //     .autocomplete({
    //         source: function(request, response) {
    //             var price_group = '';
    //             if ($('#price_group').length > 0) {
    //                 price_group = $('#price_group').val();
    //             }
    //             $.getJSON(
    //                 '/products/list',
    //                 {
    //                     price_group: price_group,
    //                     location_id: $('input#location_id').val(),
    //                     term: request.term,
    //                     not_for_selling: 0
    //                 },
    //                 response
    //             );
    //         },
    //         minLength: 2,
    //         response: function(event, ui) {
    //             if (ui.content.length == 1) {
    //                 ui.item = ui.content[0];
    //                 if (ui.item.qty_available > 0) {
    //                     $(this)
    //                         .data('ui-autocomplete')
    //                         ._trigger('select', 'autocompleteselect', ui);
    //                     $(this).autocomplete('close');
    //                 }
    //             } else if (ui.content.length == 0) {
    //                 toastr.error(LANG.no_products_found);
    //                 $('input#search_product').select();
    //             }
    //         },
    //         focus: function(event, ui) {
    //             if (ui.item.qty_available <= 0) {
    //                 return false;
    //             }
    //         },
    //         select: function(event, ui) {
    //             var is_overselling_allowed = false;
    //             if($('input#is_overselling_allowed').length) {
    //                 is_overselling_allowed = true;
    //             }
    //
    //             if (ui.item.enable_stock != 1 || ui.item.qty_available > 0 || is_overselling_allowed) {
    //                 $(this).val(null);
    //                 pos_product_row(ui.item.variation_id);
    //             } else {
    //                 alert(LANG.out_of_stock);
    //             }
    //         },
    //     })
    //     .autocomplete('instance')._renderItem = function(ul, item) {
    //         var is_overselling_allowed = false;
    //         if($('input#is_overselling_allowed').length) {
    //             is_overselling_allowed = true;
    //         }
    //     if (item.enable_stock == 1 && item.qty_available <= 0 && !is_overselling_allowed) {
    //         var string = '<li class="ui-state-disabled">' + item.name;
    //         if (item.type == 'variable') {
    //             string += '-' + item.variation;
    //         }
    //         var selling_price = item.selling_price;
    //         if (item.variation_group_price) {
    //             selling_price = item.variation_group_price;
    //         }
    //         string +=
    //             ' (' +
    //             item.sub_sku +
    //             ')' +
    //             '<br> Price: ' +
    //             selling_price +
    //             ' (Out of stock) </li>';
    //         return $(string).appendTo(ul);
    //     } else {
    //         var string = '<div>' + item.name;
    //         if (item.type == 'variable') {
    //             string += '-' + item.variation;
    //         }
    //
    //         var selling_price = item.selling_price;
    //         if (item.variation_group_price) {
    //             selling_price = item.variation_group_price;
    //         }
    //
    //         string += ' (' + item.sub_sku + ')' + '<br> Price: ' + selling_price;
    //         if (item.enable_stock == 1) {
    //             var qty_available = __currency_trans_from_en(item.qty_available, false, false, __currency_precision, true);
    //             string += ' - ' + qty_available + item.unit;
    //         }
    //         string += '</div>';
    //
    //         return $('<li>')
    //             .append(string)
    //             .appendTo(ul);
    //     }
    // };
    //Update line total and check for quantity not greater than max quantity
    $('table#pos_table tbody').on('change', 'input.pos_quantity', function() {
        if (sell_form_validator) {
            sell_form_validator.element($(this));
        }
        if (pos_form_validator) {
            pos_form_validator.element($(this));
        }
        // var max_qty = parseFloat($(this).data('rule-max'));
        var entered_qty = __read_number($(this));

        var tr = $(this).parents('tr');

        var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
        var line_total = entered_qty * unit_price_inc_tax;

        __write_number(tr.find('input.pos_line_total'), line_total, false, 2);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));

        pos_total_row();

        adjustComboQty(tr);

        let data = new FormData(pos_form_obj[0]);
        data.delete('_method');
        $.ajax({
            method:'POST',
            url: '/sells/pos_deposit/get_payment_rows',
            data: data,
            dataType: 'html',
            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            processData: false,
            success: function(result) {
                if(result){
                    $('#payment_rows_div').html(result);
                }
            }
        });
    });

    //If change in unit price update price including tax and line total
    $('table#pos_table tbody').on('change', 'input.pos_unit_price', function() {
        var unit_price = __read_number($(this));
        var tr = $(this).parents('tr');

        //calculate discounted unit price
        var discounted_unit_price = calculate_discounted_unit_price(tr);

        var tax_rate = tr
            .find('select.tax_id')
            .find(':selected')
            .data('rate');
        __write_number(tr.find('input.pos_quantity'), 1);
        var quantity = __read_number(tr.find('input.pos_quantity'));

        var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
        var line_total = quantity *  unit_price_inc_tax;

        __write_number(tr.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);
        // __write_number(tr.find('input.pos_line_total'), line_total, false, 2);
        tr.find('input.pos_line_total').val(line_total);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
        pos_each_row(tr);
        const first_service_row = $('table#pos_table tbody tr.service_row').first();
        const first_service_total = $('#total_earned').html() - line_total;
        first_service_row.find('input.pos_line_total').val(first_service_total);
        first_service_row.find('span.pos_line_total_text').text(__currency_trans_from_en(first_service_total, true));
        pos_total_row();
        round_row_to_iraqi_dinnar(tr);
        let data = new FormData(pos_form_obj[0]);
        data.delete('_method');
        $.ajax({
            method:'POST',
            url: '/sells/pos_deposit/get_payment_rows',
            data: data,
            dataType: 'html',
            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            processData: false,
            success: function(result) {
                if(result){
                    $('#payment_rows_div').html(result);
                    $('.game_id_but').click(function (e) {
                        e.preventDefault();
                        copyTextToClipboard($(this).text());
                    });
                }
            }
        });
    });

    //If change in tax rate then update unit price according to it.
    $('table#pos_table tbody').on('change', 'select.tax_id', function() {
        var tr = $(this).parents('tr');

        var tax_rate = tr
            .find('select.tax_id')
            .find(':selected')
            .data('rate');
        var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));

        var discounted_unit_price = __get_principle(unit_price_inc_tax, tax_rate);
        var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);
        __write_number(tr.find('input.pos_unit_price'), unit_price);
        pos_each_row(tr);
    });

    //If change in unit price including tax, update unit price
    $('table#pos_table tbody').on('change', 'input.pos_unit_price_inc_tax', function() {
        var unit_price_inc_tax = __read_number($(this));

        if (iraqi_selling_price_adjustment) {
            unit_price_inc_tax = round_to_iraqi_dinnar(unit_price_inc_tax);
            __write_number($(this), unit_price_inc_tax);
        }

        var tr = $(this).parents('tr');

        var tax_rate = tr
            .find('select.tax_id')
            .find(':selected')
            .data('rate');
        var quantity = __read_number(tr.find('input.pos_quantity'));

        var line_total = quantity * unit_price_inc_tax;
        var discounted_unit_price = __get_principle(unit_price_inc_tax, tax_rate);
        var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);

        __write_number(tr.find('input.pos_unit_price'), unit_price);
        __write_number(tr.find('input.pos_line_total'), line_total, false, 2);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));

        pos_each_row(tr);
        pos_total_row();
    });

    //Change max quantity rule if lot number changes
    $('table#pos_table tbody').on('change', 'select.lot_number', function() {
        var qty_element = $(this)
            .closest('tr')
            .find('input.pos_quantity');

        var tr = $(this).closest('tr');
        var multiplier = 1;
        var unit_name = '';
        var sub_unit_length = tr.find('select.sub_unit').length;
        if (sub_unit_length > 0) {
            var select = tr.find('select.sub_unit');
            multiplier = parseFloat(select.find(':selected').data('multiplier'));
            unit_name = select.find(':selected').data('unit_name');
        }
        var allow_overselling = qty_element.data('allow-overselling');
        if ($(this).val() && !allow_overselling) {
            var lot_qty = $('option:selected', $(this)).data('qty_available');
            var max_err_msg = $('option:selected', $(this)).data('msg-max');

            if (sub_unit_length > 0) {
                lot_qty = lot_qty / multiplier;
                var lot_qty_formated = __number_f(lot_qty, false);
                max_err_msg = __translate('lot_max_qty_error', {
                    max_val: lot_qty_formated,
                    unit_name: unit_name,
                });
            }

            qty_element.attr('data-rule-max-value', lot_qty);
            qty_element.attr('data-msg-max-value', max_err_msg);

            qty_element.rules('add', {
                'max-value': lot_qty,
                messages: {
                    'max-value': max_err_msg,
                },
            });
        } else {
            var default_qty = qty_element.data('qty_available');
            var default_err_msg = qty_element.data('msg_max_default');
            if (sub_unit_length > 0) {
                default_qty = default_qty / multiplier;
                var lot_qty_formated = __number_f(default_qty, false);
                default_err_msg = __translate('pos_max_qty_error', {
                    max_val: lot_qty_formated,
                    unit_name: unit_name,
                });
            }

            qty_element.attr('data-rule-max-value', default_qty);
            qty_element.attr('data-msg-max-value', default_err_msg);

            qty_element.rules('add', {
                'max-value': default_qty,
                messages: {
                    'max-value': default_err_msg,
                },
            });
        }
        qty_element.trigger('change');
    });

    //Change in row discount type or discount amount
    $('table#pos_table tbody').on(
        'change',
        'select.row_discount_type, input.row_discount_amount',
        function() {
            var tr = $(this).parents('tr');

            //calculate discounted unit price
            var discounted_unit_price = calculate_discounted_unit_price(tr);

            var tax_rate = tr
                .find('select.tax_id')
                .find(':selected')
                .data('rate');
            var quantity = __read_number(tr.find('input.pos_quantity'));

            var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
            var line_total = quantity * unit_price_inc_tax;

            __write_number(tr.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);
            __write_number(tr.find('input.pos_line_total'), line_total, false, 2);
            tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
            pos_each_row(tr);
            pos_total_row();
            round_row_to_iraqi_dinnar(tr);
        }
    );

    //Remove row on click on remove row
    $('table#pos_table tbody').on('click', 'i.pos_remove_row', function() {
        if($('#customer_id').select2('data')[0].text == "Unclaimed Trans" && edit_page){
            var parentTr = $(this).parents('tr');
            if(parentTr.find('.category_id').val() == 66 && parentTr.find('.p_name').val() != 'Bonus')
                return;
        }
        $(this)
            .parents('tr').next()
            .remove();
        $(this)
            .parents('tr')
            .remove();
        pos_total_row();
        let data = new FormData(pos_form_obj[0]);
        data.delete('_method');
        $.ajax({
            method:'POST',
            url: '/sells/pos_deposit/get_payment_rows',
            data: data,
            dataType: 'html',
            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            processData: false,
            success: function(result) {
                if(result){
                    $('#payment_rows_div').html(result);
                }
            }
        });
    });
    $('table#pos_table tbody').on('click', '.pay-window', function() {

        // if ($('#reward_point_enabled').length) {
        //     var validate_rp = isValidatRewardPoint();
        //     if (!validate_rp['is_valid']) {
        //         toastr.error(validate_rp['msg']);
        //         return false;
        //     }
        // }

        $('#modal_payment').modal('show');
    });

    //Cancel the invoice
    $('button#pos-cancel').click(function() {
        $('#customer_id').prop('disabled', false);
        variation_ids = [];
        reset_pos_form();
    });

    //Save invoice as draft
    $('button#pos-draft').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=draft';
        var url = pos_form_obj.attr('action');

        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                if (result.success == 1) {
                    reset_pos_form();
                    toastr.success(result.msg);
                    get_recent_transactions('draft', $('div#tab_draft'));
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Save invoice as Quotation
    $('button#pos-quotation').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=quotation';
        var url = pos_form_obj.attr('action');

        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                if (result.success == 1) {
                    reset_pos_form();
                    toastr.success(result.msg);

                    //Check if enabled or not
                    if (result.receipt.is_enabled) {
                        pos_print(result.receipt);
                    }

                    get_recent_transactions('quotation', $('div#tab_quotation'));
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Save invoice as Order Confirmation
    $('button#confirm-order').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=order_conf';
        var url = pos_form_obj.attr('action');

        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                if (result.success == 1) {
                    reset_pos_form();
                    toastr.success(result.msg);

                    //Check if enabled or not
                    if (result.receipt.is_enabled) {
                        pos_print(result.receipt);
                    }

                    get_recent_transactions('quotation', $('div#tab_quotation'));
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Save invoice as Order Confirmation
    $('button#presale-note').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_form_obj.serialize();
        data = data + '&status=presale_note';
        var url = pos_form_obj.attr('action');

        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                if (result.success == 1) {
                    reset_pos_form();
                    toastr.success(result.msg);

                    //Check if enabled or not
                    if (result.receipt.is_enabled) {
                        pos_print(result.receipt);
                    }

                    get_recent_transactions('quotation', $('div#tab_quotation'));
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Finalize invoice, open payment modal
    $('button#pos-finalize').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }
        var customer = $('#customer_id').select2('data')[0].text;
        if(customer === "Unclaimed Trans" && edit_page) {
            toastr.warning(LANG.customer_not_changed);
            return false;
        }
        if(customer !== "Unclaimed Trans" && $('#total_earned').html() !== $('#total_redeemed').html()){
            toastr.warning(LANG.deposit_incoincidence_error);
            return false;
        }
        if(customer !== "Unclaimed Trans" && $('.game_id_but').length === 0){
            toastr.warning(LANG.game_id_empty);
            return false;
        }
        pos_form_obj.submit();
        // if ($('#reward_point_enabled').length) {
        //     var validate_rp = isValidatRewardPoint();
        //     if (!validate_rp['is_valid']) {
        //         toastr.error(validate_rp['msg']);
        //         return false;
        //     }
        // }
        //
        // $('#modal_payment').modal('show');
    });

    $('#modal_payment').on('shown.bs.modal', function() {
        $('#modal_payment')
            .find('input')
            .filter(':visible:first')
            .focus()
            .select();
    });

    //Finalize without showing payment options
    $('button.pos-express-finalize').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        var pay_method = $(this).data('pay_method');

        //Check for remaining balance & add it in 1st payment row
        var total_payable = __read_number($('input#final_total_input'));
        var total_paying = __read_number($('input#total_paying_input'));
        if (total_payable > total_paying) {
            var bal_due = total_payable - total_paying;

            var first_row = $('#payment_rows_div')
                .find('.payment-amount')
                .first();
            var first_row_val = __read_number(first_row);
            first_row_val = first_row_val + bal_due;
            __write_number(first_row, first_row_val);
            first_row.trigger('change');
        }

        //Change payment method.
        $('#payment_rows_div')
            .find('.payment_types_dropdown')
            .first()
            .val(pay_method);
        if (pay_method == 'card') {
            $('div#card_details_modal').modal('show');
        } else if (pay_method == 'suspend') {
            $('div#confirmSuspendModal').modal('show');
        } else {
            pos_form_obj.submit();
        }
    });

    $('div#card_details_modal').on('shown.bs.modal', function(e) {
        $('input#card_number').focus();
    });

    $('div#confirmSuspendModal').on('shown.bs.modal', function(e) {
        $(this)
            .find('textarea')
            .focus();
    });

    //on save card details
    $('button#pos-save-card').click(function() {
        $('input#card_number_0').val($('#card_number').val());
        $('input#card_holder_name_0').val($('#card_holder_name').val());
        $('input#card_transaction_number_0').val($('#card_transaction_number').val());
        $('select#card_type_0').val($('#card_type').val());
        $('input#card_month_0').val($('#card_month').val());
        $('input#card_year_0').val($('#card_year').val());
        $('input#card_security_0').val($('#card_security').val());

        $('div#card_details_modal').modal('hide');
        pos_form_obj.submit();
    });

    $('button#pos-suspend').click(function() {
        $('input#is_suspend').val(1);
        $('div#confirmSuspendModal').modal('hide');
        pos_form_obj.submit();
        $('input#is_suspend').val(0);
    });

    //fix select2 input issue on modal
    $('#modal_payment')
        .find('.select2')
        .each(function() {
            $(this).select2({
                dropdownParent: $('#modal_payment'),
            });
        });

    $('button#add-payment-row').click(function() {
        var row_index = $('#payment_row_index').val();
        $.ajax({
            method: 'POST',
            url: '/sells/pos_deposit/get_payment_row',
            data: { row_index: row_index },
            dataType: 'html',
            success: function(result) {
                if (result) {
                    var appended = $('#payment_rows_div').append(result);

                    var total_payable = __read_number($('input#final_total_input'));
                    var total_paying = __read_number($('input#total_paying_input'));
                    var b_due = total_payable - total_paying;
                    $(appended)
                        .find('input.payment-amount')
                        .focus();
                    $(appended)
                        .find('input.payment-amount')
                        .last()
                        .val(__currency_trans_from_en(b_due, false))
                        .change()
                        .select();
                    __select2($(appended).find('.select2'));
                    $('#payment_row_index').val(parseInt(row_index) + 1);
                }
            },
        });
    });



    $(document).on('blur', '.game_input', function () {
        const service_id = $(this).parents('tr').find('.account_id').val();
        $.ajax({
            method: 'POST',
            url: '/sells/pos_deposit/update_game_id',
            data: { contact_id: $('select#customer_id').val(), service_id: service_id, game_id: $(this).val() },
            dataType: 'html',
            success: function(result) {
            },
        });
    });

    $(document).on('shown.bs.modal', '.view_modal', function () {
        console.log('shown.bs.modal');
        $('select#withdraw_to')
            .val($('select#customer_id').val())
            .trigger('change');
    });

    $(document).on('click', '.remove_payment_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $(this)
                    .closest('.payment_row')
                    .remove();
                calculate_balance_due();
            }
        });
    });

    pos_form_validator = pos_form_obj.validate({
        submitHandler: function(form) {
            // var total_payble = __read_number($('input#final_total_input'));
            // var total_paying = __read_number($('input#total_paying_input'));
            var cnf = true;
            
            //Ignore if the difference is less than 0.5
            if ($('input#in_balance_due').val() >= 0.5) {
                cnf = confirm(LANG.paid_amount_is_less_than_payable);
                // if( total_payble > total_paying ){
                // 	cnf = confirm( LANG.paid_amount_is_less_than_payable );
                // } else if(total_payble < total_paying) {
                // 	alert( LANG.paid_amount_is_more_than_payable );
                // 	cnf = false;
                // }
            }

            if (cnf) {
                selected_bank = $('#pos_table tbody').children().first().find('.account_id').val();
                $('#customer_id').prop('disabled', false);
                $('div.pos-processing').show();
                $('#pos-save').attr('disabled', 'true');
                var data = $(form).serialize();
                data = data + '&status=final';
                var url = $(form).attr('action');
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == 1) {
                            $('#modal_payment').modal('hide');
                            toastr.success(result.msg);

                            if(!edit_page){
                                variation_ids = [];
                                reset_pos_form();
                                var text = $('#customer_id').select2('data')[0].text;
                                if( text === "Unclaimed Trans") {
                                    if(!edit_page)
                                        $('#service_box').hide();
                                }
                            } else {
                                setTimeout(function () {
                                    window.location.href="/pos_deposit/create";
                                },3000)
                            }


                            $('#modal_success').modal('show');

                            get_contact_ledger();
                            
                            var location_id = $('input#location_id').val();
                            var category_id = $('select#product_category').val();
                            var category_id2 = $('select#product_category2').val();
                            var product_id = $('select#bank_products').val();
                            var brand_id = $('select#product_brand').val();

                            get_product_suggestion_list(category_id, product_id, brand_id, location_id);
                            get_product2_suggestion_list(category_id2, product_id, brand_id, location_id);
                            get_product3_suggestion_list(location_id);

                            //Check if enabled or not
                            // if (result.receipt.is_enabled) {
                            //     pos_print(result.receipt);
                            // }

                            get_recent_transactions('final', $('div#tab_final'));
                        } else {
                            toastr.error(result.msg);
                        }

                        $('div.pos-processing').hide();
                        $('#pos-save').removeAttr('disabled');
                    },
                });
            }
            return false;
        },
    });

    $(document).on('change', '.payment-amount', function() {
        calculate_balance_due();
    });

    //Update discount
    $('button#posEditDiscountModalUpdate').click(function() {
        //Close modal
        $('div#posEditDiscountModal').modal('hide');

        //Update values
        $('input#discount_type').val($('select#discount_type_modal').val());
        __write_number($('input#discount_amount'), __read_number($('input#discount_amount_modal')));

        if ($('#reward_point_enabled').length) {
            var reward_validation = isValidatRewardPoint();
            if (!reward_validation['is_valid']) {
                toastr.error(reward_validation['msg']);
                $('#rp_redeemed_modal').val(0);
                $('#rp_redeemed_modal').change();
            }
            updateRedeemedAmount();
        }

        pos_total_row();
    });

    //Shipping
    $('button#posShippingModalUpdate').click(function() {
        //Close modal
        $('div#posShippingModal').modal('hide');

        //update shipping details
        $('input#shipping_details').val($('#shipping_details_modal').val());

        //Update shipping charges
        __write_number(
            $('input#shipping_charges'),
            __read_number($('input#shipping_charges_modal'))
        );

        //$('input#shipping_charges').val(__read_number($('input#shipping_charges_modal')));

        pos_total_row();
    });

    $('#posShippingModal').on('shown.bs.modal', function() {
        $('#posShippingModal')
            .find('#shipping_details_modal')
            .filter(':visible:first')
            .focus()
            .select();
    });

    $(document).on('shown.bs.modal', '.row_edit_product_price_model', function() {
        $('.row_edit_product_price_model')
            .find('input')
            .filter(':visible:first')
            .focus()
            .select();
    });

    //Update Order tax
    $('button#posEditOrderTaxModalUpdate').click(function() {
        //Close modal
        $('div#posEditOrderTaxModal').modal('hide');

        var tax_obj = $('select#order_tax_modal');
        var tax_id = tax_obj.val();
        var tax_rate = tax_obj.find(':selected').data('rate');

        $('input#tax_rate_id').val(tax_id);

        __write_number($('input#tax_calculation_amount'), tax_rate);
        pos_total_row();
    });

    //Displays list of recent transactions
    get_recent_transactions('final', $('div#tab_final'));
    get_recent_transactions('quotation', $('div#tab_quotation'));
    get_recent_transactions('draft', $('div#tab_draft'));

    $(document).on('click', '.add_new_customer', function() {
        $('#customer_id').select2('close');
        var name = $(this).data('name');
        $('.contact_modal')
            .find('input#name')
            .val(name);
        $('.contact_modal')
            .find('select#contact_type')
            .val('customer')
            .closest('div.contact_type_div')
            .addClass('hide');
        $('.contact_modal').modal('show');
    });
    $('form#quick_add_contact')
        .submit(function(e) {
            e.preventDefault();
        })
        .validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contact-id',
                        type: 'post',
                        data: {
                            contact_id: function() {
                                return $('#contact_id').val();
                            },
                            hidden_id: function() {
                                if ($('#hidden_id').length) {
                                    return $('#hidden_id').val();
                                } else {
                                    return '';
                                }
                            },
                        },
                    },
                },
            },
            messages: {
                contact_id: {
                    remote: LANG.contact_id_already_exists,
                },
            },
            submitHandler: function(form) {
                $(form)
                    .find('button[type="submit"]')
                    .attr('disabled', true);
                var data = $(form).serialize();
                $.ajax({
                    method: 'POST',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('select#customer_id').append(
                                $('<option>', { value: result.data.id, text: result.data.name })
                            );
                            $('select#customer_id')
                                .val(result.data.id)
                                .trigger('change');
                            selectCustomer();
                            $('div.contact_modal').modal('hide');
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            },
        });
    $('.contact_modal').on('hidden.bs.modal', function() {
        $('form#quick_add_contact')
            .find('button[type="submit"]')
            .removeAttr('disabled');
        $('form#quick_add_contact')[0].reset();
    });
    $('.register_details_modal, .close_register_modal').on('shown.bs.modal', function() {
        __currency_convert_recursively($(this));
    });

    //Updates for add sell
    $('select#discount_type, input#discount_amount, input#shipping_charges, input#rp_redeemed_amount').change(function() {
        pos_total_row();
    });
    $('select#tax_rate_id').change(function() {
        var tax_rate = $(this)
            .find(':selected')
            .data('rate');
        __write_number($('input#tax_calculation_amount'), tax_rate);
        pos_total_row();
    });
    //Datetime picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    //Direct sell submit
    sell_form = $('form#add_sell_form');
    if ($('form#edit_sell_form').length) {
        sell_form = $('form#edit_sell_form');
        pos_total_row();
    }
    sell_form_validator = sell_form.validate();

    $('button#submit-sell').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        if (sell_form.valid()) {
            window.onbeforeunload = null;
            sell_form.submit();
        }
    });

    //Show product list.
    get_product_suggestion_list(
        $('select#product_category').val(),
        $('select#bank_products').val(),
        $('select#product_brand').val(),
        $('input#location_id').val(),
        null
    );

    get_product2_suggestion_list(
        $('select#product_category2').val(),
        $('select#service_products').val(),
        $('select#product_brand').val(),
        $('input#location_id').val(),
        null
    );
    get_product3_suggestion_list(
        $('input#location_id').val(),
        null
    );
    $('select#product_category, select#product_brand, select#bank_products').on('change', function(e) {
        $('input#suggestion_page').val(1);
        var location_id = $('input#location_id').val();
        if (location_id != '' || location_id != undefined) {
            get_product_suggestion_list(
                $('select#product_category').val(),
                $('select#bank_products').val(),
                $('select#product_brand').val(),
                $('input#location_id').val(),
                null
            );
        }
    });
    $('select#product_category2, select#service_products').on('change', function(e) {
        $('input#suggestion_page2').val(1);
        var location_id = $('input#location_id').val();
        if (location_id != '' || location_id != undefined) {
            get_product2_suggestion_list(
                $('select#product_category2').val(),
                $('select#service_products').val(),
                $('select#product_brand').val(),
                $('input#location_id').val(),
                null
            );
        }
    });

    $(document).on('click', 'div.product_box', function(e) {
        if(e.target.tagName == 'BUTTON')
            return;
        //Check if location is not set then show error message.
        if ($('input#location_id').val() == '') {
            toastr.warning(LANG.select_location);
        } else {
            variation_ids.push($(this).data('variation_id'));
            // $('#account_0').val($(this).data('account_id')).trigger('change');
            let product_type = 0; // bank
            if($(this).parent().parent().attr('id') === 'product_list_body2')
                product_type = 1; // service
            pos_product_row($(this).data('variation_id'), product_type);

            let data = new FormData(pos_form_obj[0]);
            data.delete('_method');
            $.ajax({
                method:'POST',
                url: '/sells/pos_deposit/get_payment_rows',
                data: data,
                contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
                processData: false,
                dataType: 'html',
                success: function(result) {
                    if(result){
                        $('#payment_rows_div').html(result);
                        $('.game_id_but').click(function (e) {
                            e.preventDefault();
                            copyTextToClipboard($(this).text());
                        });
                    }
                }
            });
        }
    });

    // bonus select
    $('#bonus').change(function (e) {
        const optionSelected = $("option:selected", this);
        const variation_id = optionSelected.data('variation_id');
        variation_ids.push(variation_id);
        const product_type = 2; // bonus
        pos_product_row(variation_id, product_type, optionSelected.data('name'), optionSelected.data('amount'));

        let data = new FormData(pos_form_obj[0]);
        data.delete('_method');
        $.ajax({
            method:'POST',
            url: '/sells/pos_deposit/get_payment_rows',
            data: data,
            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            processData: false,
            dataType: 'html',
            success: function(result) {
                if(result){
                    $('#payment_rows_div').html(result);
                    $('.game_id_but').click(function (e) {
                        e.preventDefault();
                        copyTextToClipboard($(this).text());
                    });
                }
            }
        });
    });

    $(document).on('shown.bs.modal', '.row_description_modal', function() {
        $(this)
            .find('textarea')
            .first()
            .focus();
    });

    //Press enter on search product to jump into last quantty and vice-versa
    $('#search_product').keydown(function(e) {
        var key = e.which;
        if (key == 9) {
            // the tab key code
            e.preventDefault();
            if ($('#pos_table tbody tr').length > 0) {
                $('#pos_table tbody tr:last')
                    .find('input.pos_quantity')
                    .focus()
                    .select();
            }
        }
    });
    $('#pos_table').on('keypress', 'input.pos_quantity', function(e) {
        var key = e.which;
        if (key == 13) {
            // the enter key code
            $('#search_product').focus();
        }
    });

    $('#exchange_rate').change(function() {
        var curr_exchange_rate = 1;
        if ($(this).val()) {
            curr_exchange_rate = __read_number($(this));
        }
        var total_payable = __read_number($('input#final_total_input'));
        var shown_total = total_payable * curr_exchange_rate;
        $('span#total_payable').text(__currency_trans_from_en(shown_total, false));
    });

    $('select#price_group').change(function() {
        var curr_val = $(this).val();
        var prev_value = $('input#hidden_price_group').val();
        $('input#hidden_price_group').val(curr_val);
        if (curr_val != prev_value && $('table#pos_table tbody tr').length > 0) {
            swal({
                title: LANG.sure,
                text: LANG.form_will_get_reset,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    if ($('form#edit_pos_sell_form').length > 0) {
                        $('table#pos_table tbody').html('');
                        pos_total_row();
                    } else {
                        reset_pos_form();
                    }

                    $('input#hidden_price_group').val(curr_val);
                    $('select#price_group')
                        .val(curr_val)
                        .change();
                } else {
                    $('input#hidden_price_group').val(prev_value);
                    $('select#price_group')
                        .val(prev_value)
                        .change();
                }
            });
        }
    });

    //Quick add product
    $(document).on('click', 'button.pos_add_quick_product', function() {
        var url = $(this).data('href');
        var container = $(this).data('container');
        $.ajax({
            url: url + '?product_for=pos',
            dataType: 'html',
            success: function(result) {
                $(container)
                    .html(result)
                    .modal('show');
                $('.os_exp_date').datepicker({
                    autoclose: true,
                    format: 'dd-mm-yyyy',
                    clearBtn: true,
                });
            },
        });
    });

    $(document).on('change', 'form#quick_add_product_form input#single_dpp', function() {
        var unit_price = __read_number($(this));
        $('table#quick_product_opening_stock_table tbody tr').each(function() {
            var input = $(this).find('input.unit_price');
            __write_number(input, unit_price);
            input.change();
        });
    });

    $(document).on('quickProductAdded', function(e) {
        //Check if location is not set then show error message.
        if ($('input#location_id').val() == '') {
            toastr.warning(LANG.select_location);
        } else {
            pos_product_row(e.variation.id);
        }
    });

    $('div.view_modal').on('show.bs.modal', function() {
        __currency_convert_recursively($(this));
    });

    $('table#pos_table').on('change', 'select.sub_unit', function() {
        var tr = $(this).closest('tr');
        var base_unit_selling_price = tr.find('input.hidden_base_unit_sell_price').val();

        var selected_option = $(this).find(':selected');

        var multiplier = parseFloat(selected_option.data('multiplier'));

        var allow_decimal = parseInt(selected_option.data('allow_decimal'));

        tr.find('input.base_unit_multiplier').val(multiplier);

        var unit_sp = base_unit_selling_price * multiplier;

        var sp_element = tr.find('input.pos_unit_price');
        __write_number(sp_element, unit_sp);

        sp_element.change();

        var qty_element = tr.find('input.pos_quantity');
        var base_max_avlbl = qty_element.data('qty_available');
        var error_msg_line = 'pos_max_qty_error';

        if (tr.find('select.lot_number').length > 0) {
            var lot_select = tr.find('select.lot_number');
            if (lot_select.val()) {
                base_max_avlbl = lot_select.find(':selected').data('qty_available');
                error_msg_line = 'lot_max_qty_error';
            }
        }

        qty_element.attr('data-decimal', allow_decimal);
        var abs_digit = true;
        if (allow_decimal) {
            abs_digit = false;
        }
        qty_element.rules('add', {
            abs_digit: abs_digit,
        });

        if (base_max_avlbl) {
            var max_avlbl = parseFloat(base_max_avlbl) / multiplier;
            var formated_max_avlbl = __number_f(max_avlbl);
            var unit_name = selected_option.data('unit_name');
            var max_err_msg = __translate(error_msg_line, {
                max_val: formated_max_avlbl,
                unit_name: unit_name,
            });
            qty_element.attr('data-rule-max-value', max_avlbl);
            qty_element.attr('data-msg-max-value', max_err_msg);
            qty_element.rules('add', {
                'max-value': max_avlbl,
                messages: {
                    'max-value': max_err_msg,
                },
            });
            qty_element.trigger('change');
        }
    });

    //Confirmation before page load.
    window.onbeforeunload = function() {
        if($('form#edit_pos_sell_form').length == 0){
            if($('table#pos_table tbody tr').length > 0) {
                return LANG.sure;
            } else {
                return null;
            }
        }
    };
    if(!edit_page)
        get_contact_ledger();

    $(document).on('submit', 'form#withdraw_form', function(e){
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
            method: "POST",
            url: $(this).attr("action"),
            dataType: "json",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            success: function(result){
                if(result.success == true){
                    $('div.view_modal').modal('hide');
                    toastr.success(result.msg);
                    get_contact_ledger();
                    var location_id = $('input#location_id').val();
                    var category_id = $('select#product_category').val();
                    var category_id2 = $('select#product_category2').val();
                    var product_id = $('select#bank_products').val();
                    var brand_id = $('select#product_brand').val();

                    get_product_suggestion_list(category_id, product_id, brand_id, location_id);
                    get_product2_suggestion_list(category_id2, product_id, brand_id, location_id);
                    get_product3_suggestion_list(location_id);
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

});

function get_contact_ledger() {

    var start_date = '';
    var end_date = '';
    var transaction_types = $('input.transaction_types:checked').map(function(i, e) {return e.value}).toArray();
    var show_payments = $('input#show_payments').is(':checked');

    if($('#ledger_date_range').val()) {
        start_date = $('#ledger_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
        end_date = $('#ledger_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
    }
    $.ajax({
        url: '/sells/pos_deposit/ledger?contact_id=' + $('#customer_id').val()+ '&transaction_types=' + transaction_types + '&show_payments=' + show_payments + '&selected_bank=' + selected_bank,
        dataType: 'html',
        success: function(result) {
            $('#contact_ledger_div')
                .html(result);
            __currency_convert_recursively($('#ledger_table'));

            $('#ledger_table').DataTable({
                "dom": 't<"bottom"iflp>', 
                searchable: false,
                ordering:false,
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function ( i ) {
                        // if(typeof i === 'string' && i)
                        //     console.log($(i).text());
                        return typeof i === 'string' && i?
                            // i.replace(/[\$,]/g, '')*1
                            parseFloat($(i).text().replace(/[RM ]/g, ''))
                            :
                            typeof i === 'number' ?
                                i : 0;
                        return 1;
                    };

                    // Total over this page
                    let columns = [3,4,7,8,9];
                    for(let i = 0; i < columns.length; i++){
                        pageTotal = api
                            .column( columns[i], { page: 'current'} )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(__number_uf(a)) + intVal(__number_uf(b));
                            }, 0 );

                        // Update footer
                        $( api.column( columns[i] ).footer() ).html(
                            __currency_trans_from_en(pageTotal, false, false,  __currency_precision, true)
                        );
                    }
                }
            });
            $('.nav-link').click(function (e) {
                selected_bank = $(this).data('bank_id');
                get_contact_ledger();
            });
            $('#refresh').click(function (e) {
                get_contact_ledger();
            })
        },
    });
}

function get_product_suggestion_list(category_id, product_id, brand_id, location_id, url = null) {

    if($('div#product_list_body').length == 0) {
        return false;
    }

    if (url == null) {
        url = '/sells/pos_deposit/get-product-suggestion';
    }
    $('#suggestion_page_loader').fadeIn(700);
    var page = $('input#suggestion_page').val();
    if (page == 1) {
        $('div#product_list_body').html('');
    }
    if ($('div#product_list_body').find('input#no_products_found').length > 0) {
        $('#suggestion_page_loader').fadeOut(700);
        return false;
    }
    $.ajax({
        method: 'GET',
        url: url,
        data: {
            category_id: category_id,
            product_id: product_id,
            brand_id: brand_id,
            location_id: location_id,
            page: page
        },
        dataType: 'html',
        success: function(result) {
            $('div#product_list_body').append(result);
            $('#suggestion_page_loader').fadeOut(700);
        },
    });
}

function get_product2_suggestion_list(category_id, product_id, brand_id, location_id, url = null) {

    if($('div#product_list_body2').length == 0) {
        return false;
    }

    if (url == null) {
        url = '/sells/pos_deposit/get-product-suggestion';
    }
    $('#suggestion_page_loader2').fadeIn(700);
    var page = $('input#suggestion_page2').val();
    if (page == 1) {
        $('div#product_list_body2').html('');
    }
    if ($('div#product_list_body2').find('input#no_products_found').length > 0) {
        $('#suggestion_page_loader2').fadeOut(700);
        return false;
    }
    $.ajax({
        method: 'GET',
        url: url,
        data: {
            category_id: category_id,
            product_id: product_id,
            brand_id: brand_id,
            location_id: location_id,
            page: page,
        },
        dataType: 'html',
        success: function(result) {
            $('div#product_list_body2').append(result);
            $('#suggestion_page_loader2').fadeOut(700);
        },
    });
}

function get_product3_suggestion_list(location_id, url = null) {
    if (url == null) {
        url = '/sells/pos_deposit/get-bonus-suggestion';
    }
    $('#suggestion_page_loader3').fadeIn(700);
    $.ajax({
        method: 'GET',
        url: url,
        data: {
            location_id: location_id,
        },
        dataType: 'html',
        success: function(result) {
            $('div#product_list_body3').html(result);
            $('#suggestion_page_loader3').fadeOut(700);
        },
    });
}

//Get recent transactions
function get_recent_transactions(status, element_obj) {
    if (element_obj.length == 0) {
        return false;
    }

    $.ajax({
        method: 'GET',
        url: '/sells/pos/get-recent-transactions',
        data: { status: status },
        dataType: 'html',
        success: function(result) {
            element_obj.html(result);
            __currency_convert_recursively(element_obj);
        },
    });
}

function pos_product_row(variation_id, product_type = 0, name = 'Bonus',  percentage = 0) {
    //Get item addition method
    var item_addtn_method = 0;
    var add_via_ajax = true;

    if ($('#item_addition_method').length) {
        item_addtn_method = $('#item_addition_method').val();
    }

    if (item_addtn_method == 0) {
        add_via_ajax = true;
    } else {
        var is_added = false;

        //Search for variation id in each row of pos table
        $('#pos_table tbody')
            .find('tr')
            .each(function() {
                var row_v_id = $(this)
                    .find('.row_variation_id')
                    .val();
                var enable_sr_no = $(this)
                    .find('.enable_sr_no')
                    .val();
                var modifiers_exist = false;
                if ($(this).find('input.modifiers_exist').length > 0) {
                    modifiers_exist = true;
                }

                if (
                    row_v_id == variation_id &&
                    enable_sr_no !== '1' &&
                    !modifiers_exist &&
                    !is_added
                ) {
                    add_via_ajax = false;
                    is_added = true;

                    //Increment product quantity
                    qty_element = $(this).find('.pos_quantity');
                    var qty = __read_number(qty_element);
                    __write_number(qty_element, qty + 1);
                    qty_element.change();

                    round_row_to_iraqi_dinnar($(this));

                    $('input#search_product')
                        .focus()
                        .select();
                }
            });
    }

    if (add_via_ajax) {
        var product_row = $('input#product_row_count').val();
        var location_id = $('input#location_id').val();
        var customer_id = $('select#customer_id').val();
        var is_direct_sell = false;
        if (
            $('input[name="is_direct_sale"]').length > 0 &&
            $('input[name="is_direct_sale"]').val() == 1
        ) {
            is_direct_sell = true;
        }

        var price_group = '';
        if ($('#price_group').length > 0) {
            price_group = $('#price_group').val();
        }

        let amount = 0;
        if(product_type === 1){ // service
            if($('table#pos_table tbody tr.service_row').length ===  1){
                amount = $('#total_earned').html() / 2;
                const first_service_row = $('table#pos_table tbody tr.service_row').first();
                const first_service_total = amount;
                first_service_row.find('input.pos_line_total').val(first_service_total);
                first_service_row.find('span.pos_line_total_text').text(__currency_trans_from_en(first_service_total, false));
            }
            else
                amount = $('#total_earned').html();
        } else if(product_type === 2) { // bonus
            if(name === 'Bonus')
                amount = $('#credit').html() * percentage / 100;
            else
                amount = percentage;
        }

        const is_first_service = $('.service_row').length === 0 ? 1 : 0;

        if(variation_id == -1) {
            $('table#pos_table tbody tr.product_row').each(function(index){
                if($(this).find('.account_name').val() === 'Bonus Account'){
                    $(this).next().remove();
                    $(this).remove();
                }
            });
            pos_total_row();
        }
        else {
        $.ajax({
            method: 'GET',
            url: '/sells/pos_deposit/get_product_row/' + variation_id + '/' + location_id,
            async: false,
            data: {
                product_row: product_row,
                customer_id: customer_id,
                is_direct_sell: is_direct_sell,
                price_group: price_group,
                product_type: product_type,
                amount: amount,
                is_first_service: is_first_service
            },
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    if(product_type == 2){ // bonus
                        $('table#pos_table tbody tr.product_row').each(function(index){
                            if($(this).find('.account_name').val() === 'Bonus Account'){
                                $(this).next().remove();
                                $(this).remove();
                            }
                        })
                    }
                    $('table#pos_table tbody')
                        .append(result.html_content)
                        .find('input.pos_quantity');
                    //increment row count
                    $('input#product_row_count').val(parseInt(product_row) + 1);
                    var this_row = $('table#pos_table tbody')
                        .find('tr')
                        .last();
                    pos_each_row(this_row);

                    //For initial discount if present
                    var line_total = __read_number(this_row.find('input.pos_line_total'));
                    this_row.find('span.pos_line_total_text').text(line_total);

                    pos_total_row();

                    //Check if multipler is present then multiply it when a new row is added.
                    if(__getUnitMultiplier(this_row) > 1){
                        this_row.find('select.sub_unit').trigger('change');
                    }

                    if (result.enable_sr_no == '1') {
                        var new_row = $('table#pos_table tbody')
                            .find('tr')
                            .last();
                        new_row.find('.add-pos-row-description').trigger('click');
                    }

                    round_row_to_iraqi_dinnar(this_row);
                    __currency_convert_recursively(this_row);

                    $('input#search_product')
                        .focus()
                        .select();

                    //Used in restaurant module
                    if (result.html_modifier) {
                        $('table#pos_table tbody')
                            .find('tr')
                            .last()
                            .find('td:first')
                            .append(result.html_modifier);
                    }
                } else {
                    toastr.error(result.msg);
                    $('input#search_product')
                        .focus()
                        .select();
                }
            },
        });
        }
    }
}

//Update values for each row
function pos_each_row(row_obj) {
    var unit_price = __read_number(row_obj.find('input.pos_unit_price'));

    var discounted_unit_price = calculate_discounted_unit_price(row_obj);
    var tax_rate = row_obj
        .find('select.tax_id')
        .find(':selected')
        .data('rate');

    var unit_price_inc_tax =
        discounted_unit_price + __calculate_amount('percentage', tax_rate, discounted_unit_price);
    __write_number(row_obj.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);

    var discount = __read_number(row_obj.find('input.row_discount_amount'));

    if (discount > 0) {
        var qty = __read_number(row_obj.find('input.pos_quantity'));
        var line_total = qty * unit_price_inc_tax;
        __write_number(row_obj.find('input.pos_line_total'), line_total);
    }

    //var unit_price_inc_tax = __read_number(row_obj.find('input.pos_unit_price_inc_tax'));

    __write_number(row_obj.find('input.item_tax'), unit_price_inc_tax - discounted_unit_price);
}

function pos_total_row() {
    let credit = 0, basic_bonus = 0, special_bonus = 0, debit = 0;

    $('table#pos_table tbody tr').each(function() {
        const p_name = $(this).find('input.account_name').val();
        const category_id = parseInt($(this).find('input.category_id').val());
        if(p_name === 'Bonus Account'){
            special_bonus += __read_number($(this).find('input.pos_line_total'));
        } else if(category_id === 66){
            const line_total = __read_number($(this).find('input.pos_line_total'));
            credit += line_total;
            basic_bonus += basic_bonus_rate * line_total / 100;
        } else {
            const line_total = __read_number($(this).find('input.pos_line_total'));
            debit += line_total;
        }
    });
    basic_bonus = Math.floor(basic_bonus);
    if(special_bonus !== 0)
        basic_bonus = 0;
    $('#credit').html(credit);
    $('#basic_bonus').html(basic_bonus);
    $('#special_bonus').html(special_bonus);
    $('#total_redeemed').html(debit);
    $('#total_earned').html(credit + basic_bonus + special_bonus);
    //
    // //Go through the modifier prices.
    // $('input.modifiers_price').each(function() {
    //     price_total = price_total + __read_number($(this));
    // });
    //
    // //updating shipping charges
    // $('span#shipping_charges_amount').text(
    //     __currency_trans_from_en(__read_number($('input#shipping_charges_modal')), false)
    // );
    //
    // $('span.total_quantity').each(function() {
    //     $(this).html(__number_f(total_quantity));
    // });
    //
    // //$('span.unit_price_total').html(unit_price_total);
    // $('span.price_total').html(__currency_trans_from_en(price_total, false));
    // calculate_billing_details(price_total);
}

function calculate_billing_details(price_total) {
    var discount = pos_discount(price_total);
    if ($('#reward_point_enabled').length) {
        total_customer_reward = $('#rp_redeemed_amount').val();
        discount = parseFloat(discount) + parseFloat(total_customer_reward);

        if ($('input[name="is_direct_sale"]').length <= 0) {
            $('span#total_discount').text(__currency_trans_from_en(discount, false));
        }
    }

    var order_tax = pos_order_tax(price_total, discount);

    //Add shipping charges.
    var shipping_charges = __read_number($('input#shipping_charges'));

    var total_payable = price_total + order_tax - discount + shipping_charges;

    __write_number($('input#final_total_input'), total_payable);
    var curr_exchange_rate = 1;
    if ($('#exchange_rate').length > 0 && $('#exchange_rate').val()) {
        curr_exchange_rate = __read_number($('#exchange_rate'));
    }
    var shown_total = total_payable * curr_exchange_rate;
    $('span#total_payable').text(__currency_trans_from_en(shown_total, false));

    $('span.total_payable_span').text(__currency_trans_from_en(total_payable, true));

    //Check if edit form then don't update price.
    if ($('form#edit_pos_sell_form').length == 0) {
        __write_number($('.payment-amount').first(), total_payable);
    }

    $(document).trigger('invoice_total_calculated');

    calculate_balance_due();
}

function pos_discount(total_amount) {
    var calculation_type = $('#discount_type').val();
    var calculation_amount = __read_number($('#discount_amount'));

    var discount = __calculate_amount(calculation_type, calculation_amount, total_amount);

    $('span#total_discount').text(__currency_trans_from_en(discount, false));

    return discount;
}

function pos_order_tax(price_total, discount) {
    var tax_rate_id = $('#tax_rate_id').val();
    var calculation_type = 'percentage';
    var calculation_amount = __read_number($('#tax_calculation_amount'));
    var total_amount = price_total - discount;

    if (tax_rate_id) {
        var order_tax = __calculate_amount(calculation_type, calculation_amount, total_amount);
    } else {
        var order_tax = 0;
    }

    $('span#order_tax').text(__currency_trans_from_en(order_tax, false));

    return order_tax;
}

function calculate_balance_due() {
    var total_payable = __read_number($('#final_total_input'));
    var total_paying = 0;
    $('#payment_rows_div')
        .find('.payment-amount')
        .each(function() {
            if (parseFloat($(this).val())) {
                total_paying += __read_number($(this));
            }
        });
    var bal_due = total_payable - total_paying;
    var change_return = 0;

    //change_return
    if (bal_due < 0 || Math.abs(bal_due) < 0.05) {
        __write_number($('input#change_return'), bal_due * -1);
        $('span.change_return_span').text(__currency_trans_from_en(bal_due * -1, true));
        change_return = bal_due * -1;
        bal_due = 0;
    } else {
        __write_number($('input#change_return'), 0);
        $('span.change_return_span').text(__currency_trans_from_en(0, true));
        change_return = 0;
    }

    __write_number($('input#total_paying_input'), total_paying);
    $('span.total_paying').text(__currency_trans_from_en(total_paying, true));

    __write_number($('input#in_balance_due'), bal_due);
    $('span.balance_due').text(__currency_trans_from_en(bal_due, true));

    __highlight(bal_due * -1, $('span.balance_due'));
    __highlight(change_return * -1, $('span.change_return_span'));
}

function isValidPosForm() {
    flag = true;
    $('span.error').remove();

    if ($('select#customer_id').val() == null) {
        flag = false;
        error = '<span class="error">' + LANG.required + '</span>';
        $(error).insertAfter($('select#customer_id').parent('div'));
    }

    if ($('tr.product_row').length == 0) {
        flag = false;
        error = '<span class="error">' + LANG.no_products + '</span>';
        $(error).insertAfter($('input#search_product').parent('div'));
    }

    return flag;
}

function reset_pos_form(){

	//If on edit page then redirect to Add POS page
	if($('form#edit_pos_sell_form').length > 0){
		// setTimeout(function() {
		// 	window.location = $("input#pos_redirect_url").val();
		// }, 4000);
		// return true;
	}
	
	if(pos_form_obj[0]){
		pos_form_obj[0].reset();
	}
	if(sell_form[0]){
		sell_form[0].reset();
	}
	set_default_customer();
	set_location();

    updateRemarks();

	// $('tr.product_row').remove();
    $('tr.product_row').remove();
    $('tr.bank_account_row').remove();
	$('span.total_quantity, span.price_total, span#total_discount, span#order_tax, span#total_payable, span#shipping_charges_amount').text(0);
	$('span.total_payable_span', 'span.total_paying', 'span.balance_due').text(0);

	$('#modal_payment').find('.remove_payment_row').each( function(){
		$(this).closest('.payment_row').remove();
	});

	//Reset discount
	__write_number($('input#discount_amount'), $('input#discount_amount').data('default'));
	$('input#discount_type').val($('input#discount_type').data('default'));

	//Reset tax rate
	$('input#tax_rate_id').val($('input#tax_rate_id').data('default'));
	__write_number($('input#tax_calculation_amount'), $('input#tax_calculation_amount').data('default'));

	$('select.payment_types_dropdown').val('cash').trigger('change');
	$('#price_group').trigger('change');

	//Reset shipping
	__write_number($('input#shipping_charges'), $('input#shipping_charges').data('default'));
	$('input#shipping_details').val($('input#shipping_details').data('default'));

	if($('input#is_recurring').length > 0){
		$('input#is_recurring').iCheck('update');
	};

    $(document).trigger('sell_form_reset');
    $('#bank_changed').val(0);
}

function set_default_customer() {
    var default_customer_id = $('#default_customer_id').val();
    var default_customer_name = $('#default_customer_name').val();
    var exists = $('select#customer_id option[value=' + default_customer_id + ']').length;
    if (exists == 0) {
        $('select#customer_id').append(
            $('<option>', { value: default_customer_id, text: default_customer_name })
        );
    }

    $('select#customer_id')
        .val(default_customer_id)
        .trigger('change');

    customer_set = true;
}

//Set the location and initialize printer
function set_location() {
    if ($('select#select_location_id').length == 1) {
        $('input#location_id').val($('select#select_location_id').val());
        $('input#location_id').data(
            'receipt_printer_type',
            $('select#select_location_id')
                .find(':selected')
                .data('receipt_printer_type')
        );
    }

    if ($('input#location_id').val()) {
        $('input#search_product')
            .prop('disabled', false)
            .focus();
    } else {
        $('input#search_product').prop('disabled', true);
    }

    initialize_printer();
}

function initialize_printer() {
    if ($('input#location_id').data('receipt_printer_type') == 'printer') {
        initializeSocket();
    }
}

$('body').on('click', 'label', function(e) {
    var field_id = $(this).attr('for');
    if (field_id) {
        if ($('#' + field_id).hasClass('select2')) {
            $('#' + field_id).select2('open');
            return false;
        }
    }
});

$('body').on('focus', 'select', function(e) {
    var field_id = $(this).attr('id');
    if (field_id) {
        if ($('#' + field_id).hasClass('select2')) {
            $('#' + field_id).select2('open');
            return false;
        }
    }
});

function round_row_to_iraqi_dinnar(row) {
    if (iraqi_selling_price_adjustment) {
        var element = row.find('input.pos_unit_price_inc_tax');
        var unit_price = round_to_iraqi_dinnar(__read_number(element));
        __write_number(element, unit_price);
        element.change();
    }
}

function pos_print(receipt) {
    //If printer type then connect with websocket
    if (receipt.print_type == 'printer') {
        var content = receipt;
        content.type = 'print-receipt';

        //Check if ready or not, then print.
        if (socket != null && socket.readyState == 1) {
            socket.send(JSON.stringify(content));
        } else {
            initializeSocket();
            setTimeout(function() {
                socket.send(JSON.stringify(content));
            }, 700);
        }

    } else if (receipt.html_content != '') {
        //If printer type browser then print content
        $('#receipt_section').html(receipt.html_content);
        __currency_convert_recursively($('#receipt_section'));
        __print_receipt('receipt_section');
    }
}

function calculate_discounted_unit_price(row) {
    var this_unit_price = __read_number(row.find('input.pos_unit_price'));
    var row_discounted_unit_price = this_unit_price;
    var row_discount_type = row.find('select.row_discount_type').val();
    var row_discount_amount = __read_number(row.find('input.row_discount_amount'));
    if (row_discount_amount) {
        if (row_discount_type == 'fixed') {
            row_discounted_unit_price = this_unit_price - row_discount_amount;
        } else {
            row_discounted_unit_price = __substract_percent(this_unit_price, row_discount_amount);
        }
    }

    return row_discounted_unit_price;
}

function get_unit_price_from_discounted_unit_price(row, discounted_unit_price) {
    var this_unit_price = discounted_unit_price;
    var row_discount_type = row.find('select.row_discount_type').val();
    var row_discount_amount = __read_number(row.find('input.row_discount_amount'));
    if (row_discount_amount) {
        if (row_discount_type == 'fixed') {
            this_unit_price = discounted_unit_price + row_discount_amount;
        } else {
            this_unit_price = __get_principle(discounted_unit_price, row_discount_amount, true);
        }
    }

    return this_unit_price;
}

//Update quantity if line subtotal changes
$('table#pos_table tbody').on('change', 'input.pos_line_total', function() {
    var subtotal = __read_number($(this));
    var tr = $(this).parents('tr');
    var quantity_element = tr.find('input.pos_quantity');
    var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
    var quantity = subtotal / unit_price_inc_tax;
    __write_number(quantity_element, quantity);

    if (sell_form_validator) {
        sell_form_validator.element(quantity_element);
    }
    if (pos_form_validator) {
        pos_form_validator.element(quantity_element);
    }
    tr.find('span.pos_line_total_text').text(__currency_trans_from_en(subtotal, true));

    pos_total_row();
});

$('div#product_list_body').on('scroll', function() {
    if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
        var page = parseInt($('#suggestion_page').val());
        page += 1;
        $('#suggestion_page').val(page);
        var location_id = $('input#location_id').val();
        var category_id = $('select#product_category').val();
        var product_id = $('select#bank_products').val();
        var brand_id = $('select#product_brand').val();

        get_product_suggestion_list(category_id, product_id, brand_id, location_id);
    }
});

$('div#product_list_body2').on('scroll', function() {
    if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
        var page = parseInt($('#suggestion_page2').val());
        page += 1;
        $('#suggestion_page2').val(page);
        var location_id = $('input#location_id').val();
        var category_id = $('select#product_category2').val();
        var product_id = $('select#service_products').val();
        var brand_id = $('select#product_brand').val();

        get_product2_suggestion_list(category_id, product_id, brand_id, location_id);
    }
});

$(document).on('ifChecked', '#is_recurring', function() {
    $('#recurringInvoiceModal').modal('show');
});

$(document).on('shown.bs.modal', '#recurringInvoiceModal', function() {
    $('input#recur_interval').focus();
});

$(document).on('click', '#select_all_service_staff', function() {
    var val = $('#res_waiter_id').val();
    $('#pos_table tbody')
        .find('select.order_line_service_staff')
        .each(function() {
            $(this)
                .val(val)
                .change();
        });
});

$(document).on('click', '.print-invoice-link', function(e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('href') + "?check_location=true",
        dataType: 'json',
        success: function(result) {
            if (result.success == 1) {
                //Check if enabled or not
                if (result.receipt.is_enabled) {
                    pos_print(result.receipt);
                }
            } else {
                toastr.error(result.msg);
            }

        },
    });
});

function getCustomerRewardPoints() {
    if ($('#reward_point_enabled').length <= 0) {
        return false;
    }
    var is_edit = $('form#edit_sell_form').length || 
    $('form#edit_pos_sell_form').length ? true : false;
    if (is_edit && !customer_set) {
        return false;
    }

    var customer_id = $('#customer_id').val();

    $.ajax({
        method: 'POST',
        url: '/sells/pos/get-reward-details',
        data: { 
            customer_id: customer_id
        },
        dataType: 'json',
        success: function(result) {
            $('#available_rp').text(result.points);
            $('#rp_redeemed_modal').data('max_points', result.points);
            // updateRedeemedAmount();
            $('#rp_redeemed_amount').change()
        },
    });
}

function updateRedeemedAmount(argument) {
    var points = $('#rp_redeemed_modal').val().trim();
    points = points == '' ? 0 : parseInt(points);
    var amount_per_unit_point = parseFloat($('#rp_redeemed_modal').data('amount_per_unit_point'));
    var redeemed_amount = points * amount_per_unit_point;
    $('#rp_redeemed_amount_text').text(__currency_trans_from_en(redeemed_amount, true));
    $('#rp_redeemed').val(points);
    $('#rp_redeemed_amount').val(redeemed_amount);
}

function updateBasicBonusRate(){
    $('#contact_id').val($('#customer_id').val());
    $.ajax({
        method: 'POST',
        url: '/sells/pos_deposit/get_basic_bonus_rate',
        data: {customer_id: $('#customer_id').val()},
        dataType: 'json',
        success: function(result) {
            if(result.success){
                basic_bonus_rate = result.basic_bonus_rate;
            }
        }
    });
}

// $(document).on('change', 'select#customer_id', function(){
//     console.log('changing');
//     updateRemarks();
//     getCustomerRewardPoints();
// });

function updateRemarks(){
    $.ajax({
        method: 'POST',
        url: '/sells/pos_deposit/get_remarks',
        data: {customer_id: $('#customer_id').val()},
        dataType: 'json',
        success: function(result) {
            if(result.success){
                $('#remarks').html(result.remarks);
            }
        }
    });
}

$(document).on('change', '#rp_redeemed_modal', function(){
    var points = $(this).val().trim();
    points = points == '' ? 0 : parseInt(points);
    var amount_per_unit_point = parseFloat($(this).data('amount_per_unit_point'));
    var redeemed_amount = points * amount_per_unit_point;
    $('#rp_redeemed_amount_text').text(__currency_trans_from_en(redeemed_amount, true));
    var reward_validation = isValidatRewardPoint();
    if (!reward_validation['is_valid']) {
        toastr.error(reward_validation['msg']);
        $('#rp_redeemed_modal').select();
    }
});

$(document).on('change', '.direct_sell_rp_input', function(){
    updateRedeemedAmount();
    pos_total_row();
});

function isValidatRewardPoint() {
    var element = $('#rp_redeemed_modal');
    var points = element.val().trim();
    points = points == '' ? 0 : parseInt(points);

    var max_points = parseInt(element.data('max_points'));
    var is_valid = true;
    var msg = '';

    if (points == 0) {
        return {
            is_valid: is_valid,
            msg: msg
        }
    }

    var rp_name = $('input#rp_name').val();
    if (points > max_points) {
        is_valid = false;
        msg = __translate('max_rp_reached_error', {max_points: max_points, rp_name: rp_name});
    }

    var min_order_total_required = parseFloat(element.data('min_order_total'));

    var order_total = __read_number($('#final_total_input'));

    if (order_total < min_order_total_required) {
        is_valid = false;
        msg = __translate('min_order_total_error', {min_order: __currency_trans_from_en(min_order_total_required, true), rp_name: rp_name});
    }

    var output = {
        is_valid: is_valid,
        msg: msg,
    }

    return output;
}

function adjustComboQty(tr){
    if(tr.find('input.product_type').val() == 'combo'){
        var qty = __read_number(tr.find('input.pos_quantity'));
        var multiplier = __getUnitMultiplier(tr);

        tr.find('input.combo_product_qty').each(function(){
            $(this).val($(this).data('unit_quantity') * qty * multiplier);
        });
    }
}
