var filtered_ids = [];
var chunk_data = [];
var update_index = 0;
var undo_index = 0;
// counter for unchecked products.
var unchecked_products = 0;
jQuery(function () {
    jQuery("#attr_names").hide();
    jQuery('.category-chosen').chosen();
    jQuery("#regex_flags_field_sku").hide();
    jQuery("#sku_show").hide();
    jQuery("#sku_show_data").hide();
    jQuery("#regex_help_link_sku").hide();
    jQuery("#regex_flags_field_title").hide();
    jQuery("#regex_help_link_title").hide();
    jQuery('.hide-price-role-select-chosen').chosen();
    jQuery('#regex_flags_field').hide();
    jQuery('#regex_help_link').hide();
    jQuery('.tooltip').darkTooltip();
    //jQuery('#add_undo_button_tooltip').trigger('mouseover');
    jQuery('.attribute-update-chosen').chosen();
    jQuery("#cat_select").hide();
    jQuery("#exclude_products").hide();
    jQuery('#regular_checkbox').hide();
    jQuery('#description_tr').hide();
    jQuery('#short_description_tr').hide();
    jQuery('#gallery_images_tr').hide();
});
jQuery(function () {
    jQuery('#save_dislay_count_order').on('click', function () {
        row_count_txt = jQuery('#display_count_order').val();
        if (!row_count_txt || row_count_txt <= 0) {
            alert('Please enter a value greater than zero');
            return false;
        }
        if (row_count_txt > 9999) {
            alert('Enter value less than 10000');
            return false;
        }

    });
    jQuery('#cancel_update_button').on('click', function () {
        jQuery('#edit_product').find('select').prop('selectedIndex', 0);
        jQuery('#edit_product').find('select').trigger('change');
    });
    jQuery("#enable_exclude_products").click(function () {
        if (jQuery(this).is(":checked")) {
            jQuery("#exclude_products").show();
        } else {
            jQuery("#exclude_products").hide();
        }
    });
});
jQuery(function () {
    //Categories Filter Search function.
    jQuery('.elex-select-categories').select2({
        ajax: {
            url: ajaxurl,
            type: "post",
            dataType: 'json',
            data: function (params) {
                var query = {
                    _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
                    action: "eh_bep_send_categories_filter_input_value",
                    input_text_value_categories:params.term,
                    page: params.page || 1
                }
                return query;
            },
            cache: true
        },
        minimumInputLength: 1,
        multiple:true
    });
    jQuery('#main_var_disp').on('click', '#pop_close', function () {
        jQuery('#main_var_disp').fadeOut(350);
    });
    jQuery('#wrap_table').on('click', '#preview_back', function () {
        unchecked_products = 0;
        localStorage.clear();
        jQuery("#wrap_table").css("display", "none");
        document.getElementById("wrap_table").hidden = true;
        document.getElementById("top_filter_tag").hidden = false;
        jQuery("#top_filter_tag").css("display", "block");
        jQuery('#step2').removeClass('active');
        jQuery('#step1').addClass('active');
    });
    jQuery('#finish_cancel, #undo_cancel').click(function () {

        swal({
            title: 'Do you want to cancel the ongoing update',
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            reverseButtons: false,
            confirmButtonColor: "#aaa",
            cancelButtonColor: "#0085ba",
            confirmButtonText: js_obj.process_edit_alert_confirm_button,
            cancelButtonText: js_obj.process_edit_alert_cancel_button
        }).then(function () {
            window.location.reload();
        });
    });
    jQuery('#wrap_table').on('click', '#process_edit', function () {
        unchecked_products = 0;
        jQuery(".loader").css("display", "block");
        var type_data = '';
        var category_data = '';
        var attribute_data = '';
        var attribute_value_data = '';
        var range_data = '';
        var desired_price_data = '';
        var minimum_price_data = '';
        var maximum_price_data = '';
        var sub_cat = '';
        type_data = jQuery("#product_type").val();
        category_data = (jQuery("#category_select").chosen().val());
        attribute_data = getValue_attrib_name();
        if (jQuery("#subcat_check").is(":checked")) {
            sub_cat = true;
        }
        if (getValue_attrib_name() != '')
            attribute_value_data = jQuery("#select_input_attributes").chosen().val();
        else {
            attribute_value_data = '';
        }
        range_data = jQuery("#regular_price_range_select").val();
        if (jQuery("#regular_price_range_select").val() != 'all')
            {
            if (jQuery("#regular_price_range_select").val() != '|')
                desired_price_data = jQuery("#regular_price_text_val").val();
            else {
                minimum_price_data = jQuery("#regular_price_min_text").val();
                maximum_price_data = jQuery("#regular_price_max_text").val();
            }
        }
        jQuery.ajax({
            url: ajaxurl,
            data: {
                _ajax_eh_bep_nonce: jQuery('#_ajax_eh_bep_nonce').val(),
                action: 'eh_bep_count_products',
                query_all: true,
                sub_category: sub_cat,
                count_products: true,
                type: type_data,
                category: category_data,
                attribute: attribute_data,
                attribute_value: attribute_value_data,
                range: range_data,
                desired_price: desired_price_data,
                minimum_price: minimum_price_data,
                maximum_price: maximum_price_data
            },
            success: function (response) {
                filtered_ids =jQuery.parseJSON(response);
                chunk_data = chunkArray(filtered_ids, 100);
                jQuery(".loader").css("display", "none");
                var desc = '';
                if (filtered_ids.length === 0)
                    {
                    desc = "No Product filtered";
                } else 
                {
                    jQuery.each(localStorage, function(key,value){
                        if((Number(key) === parseInt(key, 10)) && value === "false"){   //check if a number is int
                            unchecked_products++;
                        }
                    });
                    desc = js_obj.productsSelected + (filtered_ids.length - unchecked_products) + " " + (((filtered_ids.length - unchecked_products) === 1) ? js_obj.productSingular : js_obj.productPlural);
                }
                swal({
                    title: js_obj.process_edit_alert_title,
                    html: desc,
                    showCancelButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonColor: "#0085ba",
                    confirmButtonText: js_obj.process_edit_alert_confirm_button,
                    cancelButtonText: js_obj.process_edit_alert_cancel_button
                }).then(function () {
                    document.getElementById("wrap_table").hidden = true;
                    document.getElementById("top_filter_tag").hidden = true;
                    document.getElementById("edit_product").hidden = false;
                    jQuery('#step2').removeClass('active');
                    jQuery('#step3').addClass('active');
                    jQuery("#wrap_table").css("display", "none");
                    jQuery("#edit_product").css("display", "block");
                }, function (dismiss) {
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
    jQuery("#edit_product").on('click', '#edit_back', function () {
        unchecked_products = 0;
        jQuery("#wrap_table").css("display", "block");
        jQuery("#edit_product").css("display", "none");
        document.getElementById("wrap_table").hidden = false;
        document.getElementById("edit_product").hidden = true;
        jQuery('#step3').removeClass('active');
        jQuery('#step2').addClass('active');
        jQuery('#add_undo_now_tooltip').trigger('mouseout');
    });
    jQuery('#wrap_table').on('click', '#save_dislay_count_order', function () {
        jQuery('#save_dislay_count_order').prop('disabled', 'disabled');
        var row_count = jQuery('#display_count_order').val();
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                _ajax_eh_bep_nonce: jQuery('#_ajax_eh_bep_nonce').val(),
                action: 'eh_bulk_edit_display_count',
                row_count: row_count
            },
            success: function (response) {
                bep_ajax_filter_products();
                jQuery('#save_dislay_count_order').removeAttr('disabled');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });


    jQuery("#edit_product").on('click', '#reset_update_button', function () {
        clear_edit_data();
        jQuery('html, body').animate({
            scrollTop: jQuery(".tab_bulk_edit").offset().top
        }, 1000);

    });
    jQuery('#data_table').on('change', '#regular_price_range_select', function () {
        var dom_bet = '<input type="text"style="height:28px;width:45%;vertical-align:top;" placeholder="' + js_obj.filter_price_range_min_placeholder + '" id="regular_price_min_text"><input type="text" style="height:28px;width:45%;vertical-align:top;" placeholder="' + js_obj.filter_price_range_max_placeholder + '" id="regular_price_max_text">';
        var dom_sing = '<input type="text" style="height:28px;width:45%;vertical-align:top;" placeholder="' + js_obj.filter_price_range_desired_placeholder + '" id="regular_price_text_val">';
        switch (jQuery(this).val()) {
            case '|':
                jQuery("#regular_price_range_text").empty();
                jQuery('#regular_price_range_text').append(dom_bet);
                break;
            case 'all':
                jQuery("#regular_price_range_text").empty();
                break;
            default:
                jQuery("#regular_price_range_text").empty();
                jQuery('#regular_price_range_text').append(dom_sing);
        }
    });

    jQuery('#data_table').on('change', '#product_title_select', function () {
        var dom_title = '<input type="text" style="height:28px;width:50%;vertical-align:top;" placeholder="Enter Title Text" id="product_title_text_val">';
        jQuery("#product_title_text").empty();
        jQuery('#product_title_text').append(dom_title);
        if(jQuery("#product_title_select").val() == 'title_regex'){
            jQuery('#regex_flags_field').show();
            jQuery('#regex_help_link').show();
        }
        else {
            jQuery('#regex_flags_field').hide();
            jQuery('#regex_help_link').hide();
        }
        if (jQuery(this).val() == 'all') {
            jQuery("#product_title_text").empty();
        }
    });

    jQuery('#edit_product').on('change', '#title_action', function () {
        var dom_new = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_title_new_placeholder + '" id="title_textbox">';
        var dom_app = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_title_append_placeholder + '" id="title_textbox">';
        var dom_pre = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_title_prepand_placeholder + '" id="title_textbox">';
        var dom_rep = '<input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_title_replaceable_placeholder + '" id="replaceable_title_textbox"><input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_title_replace_placeholder + '" id="title_textbox">';
        var dom_reg_rep = '<input type="text" style="height:28px; width:36%;vertical-align:top;" placeholder="Pattern" id="regex_replaceable_title_textbox"><input type="text" style="height:28px;width:35%;vertical-align:top;" placeholder="Replacement" id="title_textbox">';
        switch (jQuery(this).val()) {
            case 'append':
                jQuery("#title_text").empty();
                jQuery("#regex_flags_field_title").hide();
                jQuery("#regex_help_link_title").hide();
                jQuery('#title_text').append(dom_app);
                break;
            case 'prepand':
                jQuery("#title_text").empty();
                jQuery("#regex_flags_field_title").hide();
                jQuery("#regex_help_link_title").hide();
                jQuery('#title_text').append(dom_pre);
                break;
            case 'set_new':
                jQuery("#title_text").empty();
                jQuery("#regex_flags_field_title").hide();
                jQuery("#regex_help_link_title").hide();
                jQuery('#title_text').append(dom_new);
                break;
            case 'replace':
                jQuery("#title_text").empty();
                jQuery("#regex_flags_field_title").hide();
                jQuery("#regex_help_link_title").hide();
                jQuery('#title_text').append(dom_rep);
                break;
            case 'regex_replace':
                jQuery("#title_text").empty();
                jQuery("#regex_flags_field_title").show();
                jQuery("#regex_help_link_title").show();
                jQuery('#title_text').append(dom_reg_rep);
                break;
            default:
                jQuery("#regex_flags_field_title").hide();
                jQuery("#regex_help_link_title").hide();
                jQuery("#title_text").empty();
        }
    });

    jQuery('#edit_product').on('change', '#description_action', function () {
        if(jQuery(this).val() == '') {
            jQuery('#description_tr').hide();
        }
        else {
            jQuery('#description_tr').show();
        }
    });
    jQuery('#edit_product').on('change', '#short_description_action', function () {
        if(jQuery(this).val() == '') {
            jQuery('#short_description_tr').hide();
        }
        else {
            jQuery('#short_description_tr').show();
        }
    });

    jQuery('#edit_product').on('change', '#gallery_image_action', function () {
        if(jQuery(this).val() == '') {
            jQuery('#gallery_images_tr').hide();
        }
        else {
            jQuery('#gallery_images_tr').show();
        }
    });
    jQuery("#edit_product").on("change", "#sku_action", function () {
        var dom_new = '<input type="text" class="sku_class" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sku_new_placeholder + '" id="sku_textbox">';
        var option1 = '<option value="" style="display: none;" disabled selected> Delimiter </option>';
        var option2 = '<option value="-"> Hyphen </option>';
        var option3 = '<option value="_"> Underscore </option>';
        var option4 = '<option value="space"> Space </option>';
        var option5 = '<option value=""> No Delim </option>';
        var selectElement = '<select class="sku_class_delimeter" placeholder="Delimeter" id="sku_delimeter" style="margin-left: 10px; margin-right: 10px;">' + option1 + option2 + option3 + option4 + option5 +'</select>';
        var sku_number = '<input type="number" class="sku_class_number" style="height:28px;width:18%;vertical-align:top;" placeholder="Padding" id="sku_padding" min ="1" max="3">';
        var dom_app = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sku_append_placeholder + '" id="sku_textbox">';
        var dom_pre = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sku_prepand_placeholder + '" id="sku_textbox">';
        var dom_rep = '<input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_sku_replaceable_placeholder + '" id="replaceable_sku_textbox"><input type="text" style="height:28px;width:20%;vertical-align:top;" placeholder="' + js_obj.edit_sku_replace_placeholder + '" id="sku_textbox">';
        var dom_reg_rep = '<input type="text" style="height:28px;width:36%;vertical-align:top;" placeholder="Pattern" id="regex_replaceable_sku_textbox"><input type="text" style="height:28px;width:35%;vertical-align:top;" placeholder="Replacement" id="sku_textbox">';
        switch (jQuery(this).val()) {
            case "append":
                jQuery("#sku_text").empty();
                jQuery("#regex_flags_field_sku").hide();
                jQuery("#sku_show").hide();
                jQuery("#sku_show_data").hide();
                jQuery("#regex_help_link_sku").hide();
                jQuery("#sku_text").append(dom_app);
                break;
            case "prepand":
                jQuery("#sku_text").empty();
                jQuery("#regex_flags_field_sku").hide();
                jQuery("#sku_show").hide();
                jQuery("#sku_show_data").hide();
                jQuery("#regex_help_link_sku").hide();
                jQuery("#sku_text").append(dom_pre);
                break;
            case "set_new":
                jQuery("#sku_text").empty();
                jQuery("#regex_flags_field_sku").hide();
                jQuery("#sku_show_data").show();
                jQuery("#sku_show").hide();
                jQuery("#regex_help_link_sku").hide();
                jQuery("#sku_text").append(dom_new);
                jQuery("#sku_text").append(selectElement);
                jQuery("#sku_text").append(sku_number);
                break;
            case "replace":
                jQuery("#sku_text").empty();
                jQuery("#regex_flags_field_sku").hide();
                jQuery("#sku_show").hide();
                jQuery("#sku_show_data").hide();
                jQuery("#regex_help_link_sku").hide();
                jQuery("#sku_text").append(dom_rep);
                break;
            case "regex_replace":
                jQuery("#sku_text").empty();
                jQuery("#regex_flags_field_sku").show();
                jQuery("#sku_show").hide();
                jQuery("#sku_show_data").hide();
                jQuery("#regex_help_link_sku").show();
                jQuery("#sku_text").append(dom_reg_rep);
                break;
            default:
                jQuery("#regex_flags_field_sku").hide();
                jQuery("#sku_show").hide();
                jQuery("#sku_show_data").hide();
                jQuery("#regex_help_link_sku").hide();
                jQuery("#sku_text").empty();
        }
        //keyup function for continueosly preparing data
        jQuery('.sku_class, .sku_class_delimeter, .sku_class_number').on('keyup', updateSKUExample);
        function updateSKUExample() {
            jQuery("#sku_show").show();
            var prefix_text = jQuery('.sku_class').val();
            var delimiter = jQuery('.sku_class_delimeter').val();
            var padding_number = jQuery('.sku_class_number').val();

            var pad = "";
            for (let index = 1; index <= padding_number; index++) {
                if( index == 1 ){
                    pad = "1";
                }else if( pad == 2){
                    pad = "01";
                }else{
                    pad = 0 + pad
                }
            }

            if( null != delimiter && "space" == delimiter ){
                var txt = prefix_text + " " + pad;
            }else if( null == delimiter && "space" != delimiter ){
                var txt = prefix_text + pad;
            }else{
                var txt = prefix_text + delimiter + pad;
            }
            if ( txt == "" ) {
                jQuery("#sku_show").hide();
            }
            // Combine the values from all input fields
            var combinedText = 'Preview : ' + txt;
            // Update the .sku element with the combined text
            jQuery('.sku_ex').html(combinedText);
        }

    });
    jQuery('#edit_product').on('change', '#stock_quantity_action', function () {
        var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="quantity_textbox">';
        var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="quantity_textbox">';
        var dom_rep = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_rep_placeholder + '" id="quantity_textbox">';
        switch (jQuery(this).val()) {
            case 'add':
                jQuery("#stock_quantity_text").empty();
                jQuery('#stock_quantity_text').append(dom_add);
                break;
            case 'sub':
                jQuery("#stock_quantity_text").empty();
                jQuery('#stock_quantity_text').append(dom_sub);
                break;
            case 'replace':
                jQuery("#stock_quantity_text").empty();
                jQuery('#stock_quantity_text').append(dom_rep);
                break;
            default:
                jQuery("#stock_quantity_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#length_action', function () {
        var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="length_textbox">';
        var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="length_textbox">';
        var dom_rep = '<input type="text" style="height:28px;vertical-align:top;"  placeholder="' + js_obj.edit_rep_placeholder + '" id="length_textbox">';
        switch (jQuery(this).val()) {
            case 'add':
                jQuery("#length_text").empty();
                jQuery('#length_text').append(dom_add);
                break;
            case 'replace':
                jQuery("#length_text").empty();
                jQuery('#length_text').append(dom_rep);
                break;
            case 'sub':
                jQuery("#length_text").empty();
                jQuery('#length_text').append(dom_sub);
                break;
            default:
                jQuery("#length_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#width_action', function () {
        var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="width_textbox">';
        var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="width_textbox">';
        var dom_rep = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_rep_placeholder + '" id="width_textbox">';
        switch (jQuery(this).val()) {
            case 'add':
                jQuery("#width_text").empty();
                jQuery('#width_text').append(dom_add);
                break;
            case 'sub':
                jQuery("#width_text").empty();
                jQuery('#width_text').append(dom_sub);
                break;
            case 'replace':
                jQuery("#width_text").empty();
                jQuery('#width_text').append(dom_rep);
                break;
            default:
                jQuery("#width_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#height_action', function () {
        var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="height_textbox">';
        var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="height_textbox">';
        var dom_rep = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_rep_placeholder + '" id="height_textbox">';
        switch (jQuery(this).val()) {
            case 'add':
                jQuery("#height_text").empty();
                jQuery('#height_text').append(dom_add);
                break;
            case 'sub':
                jQuery("#height_text").empty();
                jQuery('#height_text').append(dom_sub);
                break;
            case 'replace':
                jQuery("#height_text").empty();
                jQuery('#height_text').append(dom_rep);
                break;
            default:
                jQuery("#height_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#weight_action', function () {
        var dom_add = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_add_placeholder + '" id="weight_textbox">';
        var dom_sub = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_sub_placeholder + '" id="weight_textbox">';
        var dom_rep = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_rep_placeholder + '" id="weight_textbox">';
        switch (jQuery(this).val()) {
            case 'add':
                jQuery("#weight_text").empty();
                jQuery('#weight_text').append(dom_add);
                break;
            case 'sub':
                jQuery("#weight_text").empty();
                jQuery('#weight_text').append(dom_sub);
                break;
            case 'replace':
                jQuery("#weight_text").empty();
                jQuery('#weight_text').append(dom_rep);
                break;
            default:
                jQuery("#weight_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#shipping_unit_action', function () {
        var dom_add = '<input type="text" placeholder="' + js_obj.edit_shipping_unit_add_placeholder + '" id="shipping_unit_textbox">';
        var dom_sub = '<input type="text" placeholder="' + js_obj.edit_shipping_unit_sub_placeholder + '" id="shipping_unit_textbox">';
        var dom_rep = '<input type="text" placeholder="' + js_obj.edit_shipping_unit_rep_placeholder + '" id="shipping_unit_textbox">';
        switch (jQuery(this).val()) {
            case 'add':
                jQuery("#shipping_unit_text").empty();
                jQuery('#shipping_unit_text').append(dom_add);
                break;
            case 'sub':
                jQuery("#shipping_unit_text").empty();
                jQuery('#shipping_unit_text').append(dom_sub);
                break;
            case 'replace':
                jQuery("#shipping_unit_text").empty();
                jQuery('#shipping_unit_text').append(dom_rep);
                break;
            default:
                jQuery("#shipping_unit_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#manage_stock_action', function () {
        switch (jQuery(this).val()) {
            case '':
                jQuery("#manage_stock_check_text").empty();
                break;
            default:
                jQuery("#manage_stock_check_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#allow_backorder_action', function () {
        switch (jQuery(this).val()) {
            case '':
                jQuery("#backorder_text").empty();
                break;
            default:
                jQuery("#backorder_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#shipping_class_action', function () {
        switch (jQuery(this).val()) {
            case '':
                jQuery("#shipping_class_check_text").empty();
                break;
            default:
                jQuery("#shipping_class_check_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#sale_price_action', function () {
        var dom_up_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_per_placeholder + '" id="sale_textbox">';
        var dom_down_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_per_placeholder + '" id="sale_textbox">';
        var dom_up_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_pri_placeholder + '" id="sale_textbox">';
        var dom_down_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_pri_placeholder + '" id="sale_textbox">';
        var dom_flat_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_flat_pri_placeholder + '" id="sale_textbox">';
        var dom_round = '<select id="sale_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
        switch (jQuery(this).val()) {
            case 'up_percentage':
                jQuery("#sale_price_text").empty();
                jQuery('#sale_price_text').append(dom_up_per);
                jQuery('#sale_price_text').append(dom_round);
                jQuery('#regular_checkbox').hide();
                break;
            case 'down_percentage':
                jQuery("#sale_price_text").empty();
                jQuery('#sale_price_text').append(dom_down_per);
                jQuery('#sale_price_text').append(dom_round);
                jQuery('#regular_checkbox').show();

                break;
            case 'up_price':
                jQuery("#sale_price_text").empty();
                jQuery('#sale_price_text').append(dom_up_pri);
                jQuery('#sale_price_text').append(dom_round);
                jQuery('#regular_checkbox').hide();
                break;
            case 'down_price':
                jQuery("#sale_price_text").empty();
                jQuery('#sale_price_text').append(dom_down_pri);
                jQuery('#sale_price_text').append(dom_round);
                jQuery('#regular_checkbox').show();
                break;
            case 'flat_all':
                jQuery("#sale_price_text").empty();
                jQuery('#sale_price_text').append(dom_flat_pri);
                jQuery('#regular_checkbox').hide();
                break;
            default:
                jQuery("#sale_price_text").empty();
                jQuery('#regular_checkbox').hide();
        }
    });

    jQuery('#edit_product').on('change', '#sale_round_select', function () {
        var dom_round = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_round_off + '" id="sale_round_textbox"> ';

        switch (jQuery(this).val()) {
            case 'up':
                jQuery("#sale_round_textbox").remove();
                jQuery('#sale_price_text').append(dom_round);
                break;
            case 'down':
                jQuery("#sale_round_textbox").remove();
                jQuery('#sale_price_text').append(dom_round);
                break;
            default:
                jQuery("#sale_round_textbox").remove();

        }
    });


    jQuery('#edit_product').on('change', '#regular_price_action', function () {
        var dom_up_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_per_placeholder + '" id="regular_textbox">';
        var dom_down_per = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_per_placeholder + '" id="regular_textbox">';
        var dom_up_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_up_pri_placeholder + '" id="regular_textbox">';
        var dom_down_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_down_pri_placeholder + '" id="regular_textbox">';
        var dom_flat_pri = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_flat_pri_placeholder + '" id="regular_textbox">';
        var dom_round = '<select id="regular_round_select"><option value="">No Rounding</option><option value="up">Round Up</option><option value="down">Round Down</option></select>';
        switch (jQuery(this).val()) {
            case 'up_percentage':
                jQuery("#regular_price_text").empty();
                jQuery('#regular_price_text').append(dom_up_per);
                jQuery('#regular_price_text').append(dom_round);
                break;
            case 'down_percentage':
                jQuery("#regular_price_text").empty();
                jQuery('#regular_price_text').append(dom_down_per);
                jQuery('#regular_price_text').append(dom_round);
                break;
            case 'up_price':
                jQuery("#regular_price_text").empty();
                jQuery('#regular_price_text').append(dom_up_pri);
                jQuery('#regular_price_text').append(dom_round);
                break;
            case 'down_price':
                jQuery("#regular_price_text").empty();
                jQuery('#regular_price_text').append(dom_down_pri);
                jQuery('#regular_price_text').append(dom_round);
                break;
            case 'flat_all':
                jQuery("#regular_price_text").empty();
                jQuery('#regular_price_text').append(dom_flat_pri);
                break;
            default:
                jQuery("#regular_price_text").empty();
        }
    });
    jQuery('#edit_product').on('change', '#regular_round_select', function () {
        var dom_round = '<input type="text" style="height:28px;vertical-align:top;" placeholder="' + js_obj.edit_price_round_off + '" id="regular_round_textbox"> ';

        switch (jQuery(this).val()) {
            case 'up':
                jQuery("#regular_round_textbox").remove();
                jQuery('#regular_price_text').append(dom_round);
                break;
            case 'down':
                jQuery("#regular_round_textbox").remove();
                jQuery('#regular_price_text').append(dom_round);
                break;
            default:
                jQuery("#regular_round_textbox").remove();

        }
    });
    jQuery("#cat_update input:radio").click(function() {

        switch (jQuery(this).val()) {
            case 'cat_none':
                jQuery("input[name='cat_update']").prop("checked", false);
                jQuery("#cat_select").hide();
                break;
            case 'cat_add':
                jQuery("input[name='cat_update']").prop("checked", false);
                jQuery("#cat_select").show();
                break;
            case 'cat_remove':
                jQuery("input[name='cat_update']").prop("checked", false);
                jQuery("#cat_select").show();
                break;
            case 'cat_replace':
                jQuery("input[name='cat_update']").prop("checked", false);
                jQuery("#cat_select").show();
                break;
            default:
                jQuery("#cat_select").hide();
        }
    });

    jQuery('#edit_product').on('change', '#attribute_action', function () {
        jQuery("#attribu_name input:checked").each(function () {
            jQuery(this).removeAttr('checked');
        });
        jQuery('#add_attribute_value_select').remove();
        jQuery('#new_attr_values').remove();
        jQuery('#select_variation').remove();
        switch (jQuery(this).val()) {
            case 'add':
                jQuery("#attr_names").show();
                break;
            case 'remove':
                jQuery("#attr_names").show();
                break;
            case 'replace':
                jQuery("#attr_names").show();
                break;
            default:
                jQuery("#attr_names").hide();
        }
    });

    jQuery("#attribu_name input[type='checkbox']").click(function () {
        var display = jQuery('#attribu_name input[type=checkbox]:checked').length;
        if (display == 0) {
            jQuery('#add_attribute_value_select').remove();
            jQuery('#new_attr_values').remove();
            jQuery('#select_variation').remove();
            document.getElementById("new_attr").innerHTML = '';
        } else {
            if (!jQuery('#add_attribute_value_select').length) {
                var tool_tip = '';
                var new_tool_tip = '';
                if ((jQuery("#attribute_action").val()) == 'add') {
                    tool_tip = 'Choose an existing attribute value(s) to be added to the product attribute(s)';
                    new_tool_tip = 'Specify new values to be added to the selected attribute(s). Enter each value in a new line';
                }
                if ((jQuery("#attribute_action").val()) == 'remove') {
                    tool_tip = 'Choose existing attribute value(s) to be removed from the product attribute(s)';
                }
                if ((jQuery("#attribute_action").val()) == 'replace') {
                    tool_tip = 'Select existing attribute value(s) to be added to the product attribute(s). This will replace any already existing attribute value(s) from the product attribute';
                    new_tool_tip = 'Specify new values to be added to the selected attribute(s). Enter each value in a new line. This will replace any already existing attribute value(s) from the product attribute';
                }

                var dom = "<tr id='add_attribute_value_select'><td>" + js_obj.filter_attribute_value_title + "</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + tool_tip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_add_attributes'></select></span></td><td style='width:38%;'></td></tr>";
                var dom_new_attr = "<tr id='new_attr_values'><td>" + 'Attribute Values (New)' + "</td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + new_tool_tip + "'></span></td><td><span class='select-eh' ><textarea  id='new_attribute_values_textarea' style='width:210px; height:66px;'></textarea></span></td></tr>";
                var dom_variation_check = "<tr id='select_variation'><td class='eh-edit-tab-table-left'>Used for Variations <span style='vertical-align: super;color:green;font-size:12px'>Premium</span></td><td class='eh-edit-tab-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='Choose if selected attribute values are to be used for variations'></span></td> <td class='eh-edit-tab-table-input-td'> <select id='attr_variationa_action' style='width:210px;' disabled><option value=''>< No Change ></option><option value='add'>Enable</option><option value='remove'>Disable</option></select></td></tr>";
                jQuery('#attr_names').after(dom);
                jQuery('.attribute-chosen').chosen();
                jQuery('.tooltip').darkTooltip();
                if ((jQuery("#attribute_action").val()) == 'add' || (jQuery("#attribute_action").val()) == 'replace') {
                    jQuery('#new_attr').after(dom_new_attr);
                    jQuery('#variation_select').after(dom_variation_check);
                    jQuery('.tooltip').darkTooltip();

                } else {
                    jQuery('#new_attr_values').remove();
                    jQuery('#select_variation').remove();
                }

            }
            if (!jQuery(this).is(':checked')) {
                remove_edit_attribute_value(jQuery(this).val());
            } else {
                append_edit_attribute_value(jQuery(this).val());
            }
        }
    });

    function remove_edit_attribute_value(attrib_name) {
        var id = '#grp_' + attrib_name;
        jQuery(id).remove();
        jQuery('.attribute-chosen').trigger("chosen:updated");
    }

    function append_edit_attribute_value(attrib_name) {
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                _ajax_eh_bep_nonce: jQuery('#_ajax_eh_bep_nonce').val(),
                action: 'eh_bep_get_attributes_action',
                attrib: attrib_name
            },
            success: function (data) {
                jQuery('#select_input_add_attributes').append(data);
                jQuery('.attribute-chosen').trigger("chosen:updated");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }

    function getValue_attribu_name() {
        var chkArray = [];
        jQuery("#attribu_name input:checked").each(function () {
            chkArray.push(jQuery(this).val());
        });
        var selected;
        selected = chkArray.join(',') + ",";
        if (selected.length > 1) {
            return (selected.slice(0, -1));
        } else {
            return ('');
        }
    }

    jQuery("#attrib_name input[type='checkbox']").click(function () {
        var display = jQuery('#attrib_name input[type=checkbox]:checked').length;
        if (display == 0) {
            jQuery('#attribute_value_select').remove();
        } else {
            if (!jQuery('#attribute_value_select').length) {
                var dom = "<tr id='attribute_value_select'><td>" + js_obj.filter_attribute_value_title + "</td><td class='eh-content-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + js_obj.filter_attribute_value_tooltip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_attributes'></select></span></td></tr>";
                jQuery('#attribute_types').after(dom);
                jQuery('.attribute-chosen').chosen();
                jQuery('.tooltip').darkTooltip();
            }
            if (!jQuery(this).is(':checked')) {
                remove_attribute_value(jQuery(this).val());
            } else {
                append_attribute_value(jQuery(this).val());
            }
        }
    });

    jQuery("#attrib_name_and input[type='checkbox']").click(function () {
        var display = jQuery('#attrib_name_and input[type=checkbox]:checked').length;
        if (display == 0) {
            jQuery('#attribute_value_select_and').remove();
        } else {
            if (!jQuery('#attribute_value_select_and').length) {
                var dom = "<tr id='attribute_value_select_and'><td>" + js_obj.filter_attribute_value_title + "</td><td class='eh-content-table-middle'><span class='woocommerce-help-tip tooltip' data-tooltip='" + js_obj.filter_attribute_value_tooltip + "'></span></td><td><span class='select-eh' ><select data-placeholder='" + js_obj.filter_attribute_value_placeholder + "' multiple class='attribute-chosen' id='select_input_attributes_and'></select></span></td></tr>";
                jQuery('#attribute_types_and').after(dom);
                jQuery('.attribute-chosen').chosen();
                jQuery('.tooltip').darkTooltip();
            }
            if (!jQuery(this).is(':checked')) {
                remove_attribute_value_and(jQuery(this).val());
            } else {
                append_attribute_value_and(jQuery(this).val());
            }
        }
    });

    jQuery("html").on('click', '#why_update_undo', function () {
        jQuery('html, body').animate({
            scrollTop: jQuery(".tab_bulk_edit").offset().top
        }, 1000);
        //jQuery('#add_undo_now_tooltip').trigger('mouseover');
    });
    jQuery('#update_logs').on('click', '#update_finished', function ()
    {
        document.getElementById("update_logs").hidden = true;
        document.getElementById("wrap_table").hidden = false;
        jQuery("#update_logs").css("display", "none");
        jQuery("#wrap_table").css("display", "block");
        jQuery("#edit_product").css("display", "none");
        jQuery('html, body').animate({
            scrollTop: jQuery(".tab_bulk_edit").offset().top
        }, 1000);
        jQuery('#add_undo_now_tooltip').trigger('mouseout');
        bep_ajax_filter_products();
    });
    jQuery('#edit_product').on('click', '#update_button', function () {
        let undoEnabled = "<span style='color:green;'>" + js_obj.undoEnabled + "</span>";
        let undoDisabled = "<span style='color:red;'>" + js_obj.undoDisabled + "</span>";
        let why = "<span style='color:blue;padding-left:5px;cursor:pointer;' id='why_update_undo'>" + js_obj.why + "</span>";
        let html = jQuery("#add_undo_now").is(":checked") ? undoEnabled : undoDisabled + why;

        swal({
            title: js_obj.process_update_alert_title,
            html: html,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonColor: "#0085ba",
            confirmButtonText: js_obj.process_update_alert_confirm_button,
            cancelButtonText: js_obj.process_update_alert_cancel_button
        }).then(function ()
        {
            document.getElementById("edit_product").hidden = true;
            jQuery('#step3').removeClass('active');
            jQuery('#step4').addClass('active');
            jQuery("#logs_val").html("");
            jQuery("#edit_product").css("display", "none");
            jQuery("#update_logs").show();
            jQuery("#logs_val").show;
            jQuery("#logs_loader").html('<img src="./images/loading.gif">');
            update_index = 0;
            jQuery("#finish_cancel").show();
            jQuery("#update_finished").hide();
            xa_update_products();
        });

    });
    jQuery('#edit_cancel, #clear_filter_button, #preview_cancel').click(function () {
        unchecked_products = 0;
        localStorage.clear();
        swal({
            title: js_obj.clear_product_alert_title,
            text: js_obj.clear_product_alert_subtitle,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonColor: "#0085ba",
            confirmButtonText: js_obj.clear_product_alert_confirm_button,
            cancelButtonText: js_obj.clear_product_alert_cancel_button
        }).then(function () {
            jQuery(".loader").css("display", "block");

            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    _ajax_eh_bep_nonce: jQuery('#_ajax_eh_bep_nonce').val(),
                    action: 'eh_bep_clear_products',
                },
                success: function (response) {
                    jQuery("#wrap_table").css("display", "none");
                    jQuery("#edit_product").css("display", "none");
                    jQuery("#top_filter_tag").css("display", "block");
                    jQuery('#step2').removeClass('active');
                    jQuery('#step3').removeClass('active');
                    document.getElementById("wrap_table").hidden = true;
                    document.getElementById("top_filter_tag").hidden = false;
                    jQuery('#step1').addClass('active');
                    jQuery(".loader").css("display", "none");
                    clear_filters();
                    var response = jQuery.parseJSON(response);
                    if (response.rows.length)
                        jQuery('#the-list').html(response.rows);
                    if (response.column_headers.length)
                        jQuery('thead tr, tfoot tr').html(response.column_headers);
                    if (response.pagination.bottom.length)
                        jQuery('.tablenav.top .tablenav-pages').html(jQuery(response.pagination.top).html());
                    if (response.pagination.top.length)
                        jQuery('.tablenav.bottom .tablenav-pages').html(jQuery(response.pagination.bottom).html());
                    list.init();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });
    });
    jQuery("#filter_products_button").click(function() {
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                action: "elex_bep_update_checked_status",
                _ajax_eh_bep_nonce: jQuery("#_ajax_eh_bep_nonce").val(),
                operation: "delete"
            }
        });
        bep_ajax_filter_products();
    });

    function xa_update_products()
    {
        jQuery("#title_textbox").removeClass("input-error");
        jQuery("#replaceable_title_textbox").removeClass("input-error");
        jQuery("#sku_textbox").removeClass("input-error");
        var title_vali = false;
        if (jQuery("#title_action").val() != '') {
            if (jQuery("#title_textbox").val() == '') {
                jQuery("#title_textbox").addClass("input-error");
            } else {
                title_vali = true;
            }
            if (jQuery("#title_action").val() == 'replace') {
                if (jQuery("#replaceable_title_textbox").val() == '') {
                    jQuery("#replaceable_title_textbox").addClass("input-error");
                    title_vali = false;
                }
            }
            if (jQuery("#title_action").val() == 'regex_replace') {
                if (jQuery("#regex_replaceable_title_textbox").val() == '') {
                    jQuery("#regex_replaceable_title_textbox").addClass("input-error");
                    title_vali = false;
                }
            }
        } else {
            title_vali = true;
        }
        var sku_vali = false;
        if (jQuery("#sku_action").val() != '') {
            if (jQuery("#sku_textbox").val() == '') {
                jQuery("#sku_textbox").addClass("input-error");
            } else {
                sku_vali = true;
            }
        } else {
            sku_vali = true;
        }
        jQuery("#quantity_textbox").removeClass("input-error");
        var quanity_vali = false;
        if (jQuery("#stock_quantity_action").val() != '') {
            if (!(/^\d+$/.test(jQuery("#quantity_textbox").val()))) {
                jQuery("#quantity_textbox").addClass("input-error");
            } else {
                quanity_vali = true;
            }
        } else {
            quanity_vali = true;
        }
        jQuery("#length_textbox").removeClass("input-error");
        jQuery("#width_textbox").removeClass("input-error");
        jQuery("#height_textbox").removeClass("input-error");
        jQuery("#weight_textbox").removeClass("input-error");
        var length_vali = false;
        var width_vali = false;
        var height_vali = false;
        var weight_vali = false;
        if (jQuery("#length_action").val() != '') {
            if (!jQuery.isNumeric(jQuery("#length_textbox").val())) {
                jQuery("#length_textbox").addClass("input-error");
            } else {
                length_vali = true;
            }
        } else {
            length_vali = true;
        }
        if (jQuery("#width_action").val() != '') {
            if (!jQuery.isNumeric(jQuery("#width_textbox").val())) {
                jQuery("#width_textbox").addClass("input-error");
            } else {
                width_vali = true;
            }
        } else {
            width_vali = true;
        }
        if (jQuery("#height_action").val() != '') {
            if (!jQuery.isNumeric(jQuery("#height_textbox").val())) {
                jQuery("#height_textbox").addClass("input-error");
            } else {
                height_vali = true;
            }
        } else {
            height_vali = true;
        }
        if (jQuery("#weight_action").val() != '') {
            if (!jQuery.isNumeric(jQuery("#weight_textbox").val())) {
                jQuery("#weight_textbox").addClass("input-error");
            } else {
                weight_vali = true;
            }
        } else {
            weight_vali = true;
        }
        jQuery("#sale_textbox").removeClass("input-error");
        jQuery("#regular_textbox").removeClass("input-error");
        var sale_vali = false;
        var sale_round_type = "";
        var sale_round_val = "";
        if (jQuery("#sale_price_action").val() != '' && jQuery("#sale_price_action").val() != 'flat_all') 
            {
            if (!jQuery.isNumeric(jQuery("#sale_textbox").val()))
                {
                jQuery("#sale_textbox").addClass("input-error");
            } else
            {
                sale_round_type = jQuery("#sale_round_select").val();
                sale_round_val = jQuery("#sale_round_textbox").val();
                sale_vali = true;
            }
        } else
        {
            sale_vali = true;
        }
        var regualr_vali = false;
        var regular_round_type = "";
        var regular_round_val = "";
        if (jQuery("#regular_price_action").val() != '' && jQuery("#regular_price_action").val() != 'flat_all')
            {
            if (!jQuery.isNumeric(jQuery("#regular_textbox").val()))
                {
                jQuery("#regular_textbox").addClass("input-error");
            } else
            {
                regular_round_type = jQuery("#regular_round_select").val();
                regular_round_val = jQuery("#regular_round_textbox").val();
                regualr_vali = true;
            }
        } else
        {
            regualr_vali = true;
        }
        if (title_vali && sku_vali && quanity_vali && sale_vali && regualr_vali && length_vali && width_vali && height_vali && weight_vali)
            {

            var undo_update = (jQuery('#add_undo_now').is(':checked')) ? 'yes' : '';
            var title_select_data = jQuery("#title_action").val();
            var sku_select_data = jQuery("#sku_action").val();
            var catalog_select_data = jQuery("#catalog_action").val();
            var shipping_select_data = jQuery("#shipping_class_action").val();
            var sale_select_data = jQuery("#sale_price_action").val();
            var regular_select_data = jQuery("#regular_price_action").val();
            var stock_manage_select_data = jQuery("#manage_stock_action").val();
            var quantity_select_data = jQuery("#stock_quantity_action").val();
            var backorder_select_data = jQuery("#allow_backorder_action").val();
            var stock_status_select_data = jQuery("#stock_status_action").val();
            var length_select_data = jQuery("#length_action").val();
            var width_select_data = jQuery("#width_action").val();
            var height_select_data = jQuery("#height_action").val();
            var weight_select_data = jQuery("#weight_action").val();
            var shipping_unit_select_data = (jQuery("#shipping_unit_action").val() == undefined) ? '' : jQuery("#shipping_unit_action").val();

            var hide_price_select = (jQuery("#visibility_price").val() == undefined) ? '' : jQuery("#visibility_price").val();
            var hide_price_role_select = (jQuery("#hide_price_role_select").val() == undefined) ? '' : jQuery("#hide_price_role_select").chosen().val();
            var price_adjustment_select = (jQuery("#price_adjustment_action").val() == undefined) ? '' : jQuery("#price_adjustment_action").val();
            var title_text_data = (jQuery("#title_textbox").val() == undefined) ? '' : jQuery("#title_textbox").val();
            var replace_title_text_data = (jQuery("#replaceable_title_textbox").val() == undefined) ? '' : jQuery("#replaceable_title_textbox").val();
            var regex_replace_title_text_data = (jQuery("#regex_replaceable_title_textbox").val() == undefined) ? '' : jQuery("#regex_replaceable_title_textbox").val();
            var sku_text_data = (jQuery("#sku_textbox").val() == undefined) ? '' : jQuery("#sku_textbox").val();
            var replace_sku_text_data = (jQuery("#replaceable_sku_textbox").val() == undefined) ? '' : jQuery("#replaceable_sku_textbox").val();
            var regex_replace_sku_text_data = (jQuery("#regex_replaceable_sku_textbox").val() == undefined) ? '' : jQuery("#regex_replaceable_sku_textbox").val();
            var sale_text_data = (jQuery("#sale_textbox").val() == undefined) ? '' : jQuery("#sale_textbox").val();
            var sku_padding_data = (jQuery("#sku_padding").val() == undefined) ? '' : jQuery("#sku_padding").val();
            var sku_delimeter_data = (jQuery("#sku_delimeter").val() == undefined) ? '' : jQuery("#sku_delimeter").val();
            var regular_text_data = (jQuery("#regular_textbox").val() == undefined) ? '' : jQuery("#regular_textbox").val();
            var quantity_text_data = (jQuery("#quantity_textbox").val() == undefined) ? '' : jQuery("#quantity_textbox").val();
            var length_text_data = (jQuery("#length_textbox").val() == undefined) ? '' : jQuery("#length_textbox").val();
            var width_text_data = (jQuery("#width_textbox").val() == undefined) ? '' : jQuery("#width_textbox").val();
            var height_text_data = (jQuery("#height_textbox").val() == undefined) ? '' : jQuery("#height_textbox").val();
            var weight_text_data = (jQuery("#weight_textbox").val() == undefined) ? '' : jQuery("#weight_textbox").val();
            var shipping_unit_text_data = (jQuery("#shipping_unit_textbox").val() == undefined) ? '' : jQuery("#shipping_unit_textbox").val();
            var custom_meta_value = [];


            var type_data = '';
            var category_data = '';
            var attribute_data = '';
            var attribute_value_data = '';
            var range_data = '';
            var desired_price_data = '';
            var minimum_price_data = '';
            var maximum_price_data = '';
            var regex_flag_title = (jQuery("#regex_flags_values_title").val() == undefined) ? '' : jQuery("#regex_flags_values_title").val();;
            var regex_flag_sku = (jQuery("#regex_flags_values_sku").val() == undefined) ? '' : jQuery("#regex_flags_values_sku").val();;
            type_data = jQuery("#product_type").val();
            category_data = (jQuery("#category_select").chosen().val());
            attribute_data = getValue_attribu_name();

            if (getValue_attribu_name() != '')
                attribute_value_data = jQuery("#select_input_add_attributes").chosen().val();
            else {
                attribute_value_data = '';
            }
            var att_action = (jQuery("#attribute_action").val());
            range_data = jQuery("#regular_price_range_select").val();
            if (jQuery("#regular_price_range_select").val() != 'all')
                {
                if (jQuery("#regular_price_range_select").val() != '|')
                    desired_price_data = jQuery("#regular_price_text_val").val();
                else {
                    minimum_price_data = jQuery("#regular_price_min_text").val();
                    maximum_price_data = jQuery("#regular_price_max_text").val();
                }
            }
            var new_attrib_val = '';
            var att_variation = '';
            if ((jQuery("#attribute_action").val() == 'add' || jQuery("#attribute_action").val() == 'replace')) {
                if ( (jQuery("#new_attribute_values_textarea").length) && jQuery("#new_attribute_values_textarea").val() != '') {
                    new_attrib_val = jQuery("#new_attribute_values_textarea").val().split('\n');
                }
                att_variation = (jQuery("#attr_variationa_action").val());
            }

            // category update feature.
            var sel_categories_to_update = [];
            jQuery.each(jQuery("input[name='cat_update']:checked"), function(){
                sel_categories_to_update.push(jQuery(this).val());
            });
            var cat_update_option = jQuery("input[name='edit_category']:checked").val();

            // custom meta fields.
            jQuery('input[name="meta_keys"]').each(function() {
                custom_meta_value.push(jQuery(this).val());
            });

            // Use regular value.
            var use_regular_val = 0;
            if(jQuery('#regular_val_check').is(":checked")){
                use_regular_val = 1;
            }else {
                use_regular_val = 0;
            }


            // Product tags.
            var tags = jQuery('#elex_product_tags').val();

            // Descriptions and short descriptions.
            var product_description_action = jQuery('#description_action').val();
            var product_short_description_action = jQuery('#short_description_action').val();
            if(tinyMCE.get('elex_product_description') != null){
                var product_description = tinyMCE.get('elex_product_description').getContent();
            }else{
                var product_description = jQuery('#elex_product_description').val();
            }

            if(tinyMCE.get('elex_product_short_description') != null){
                var product_short_description = tinyMCE.get('elex_product_short_description').getContent();
            }else{
                var product_short_description = jQuery('#elex_product_short_description').val();
            }

            // Delete products.
            var delete_product = jQuery('#delete_product_action').val();

            // Product image.
            var prod_image = jQuery('#elex_product_main_image').val();
            var prod_gallery_images = '';
            var gallery_image_action = jQuery('#gallery_image_action').val();
            if ((jQuery("#elex_product_gallery_images").length) && jQuery("#elex_product_gallery_images").val() != '' && gallery_image_action != '') {
                prod_gallery_images = jQuery("#elex_product_gallery_images").val().split(',');
            }

            // Tax status and class actions.
            var tax_status_actions = (jQuery('#tax_status_action')).val();
            var tax_class_actions = (jQuery('#tax_class_action')).val();

            //Featured Product
            var featured_product = jQuery("#is_featured").val();

            //get unchecked product values and store it in array.
            var unchecked_array = [];
            jQuery.each(localStorage, function(key,value){
                if((Number(key) === parseInt(key, 10)) && value === "false"){   //check if a number is int
                    unchecked_array.push(Number(key));
                }
            });
            var cats_to_exclude_filter = {};
            var exclude_sub_cat_filter = "";
            var exclude_prods_filter = "";
            if (jQuery("#enable_exclude_products").is(":checked")) {
                exclude_prods_filter = 1;
                jQuery('#elex_select_exclude_categories :selected').each(function () { var o = jQuery(this); cats_to_exclude_filter[o.text()] = o.val() });

                if (jQuery("#exclude_subcat_check").is(":checked")) {
                    exclude_sub_cat_filter = 1;
                }
            }
            var attribute_value_data_and = "";
            if (getValue_attrib_name_and() != "")
                attribute_value_data_and = jQuery("#select_input_attributes_and").chosen().val();
            else {
                attribute_value_data_and = "";
            }

            var attribute_value_data_or = "";
            if (getValue_attrib_name() != "")
                attribute_value_data_or = jQuery("#select_input_attributes").chosen().val();
            else {
                attribute_value_data_or = "";
            }

            var sub_cat_filter = "";
            if (jQuery("#subcat_check").is(":checked")) {
                sub_cat_filter = true;
            }
            var And_cat_check = "";
            if (jQuery("#and_cat_check").is(":checked")) {
                And_cat_check = true;
            }
            var filter_product_image_not_exist = "";
            if (jQuery("#elex_filter_product_image_not_exist").is(":checked")) {
                filter_product_image_not_exist = "yes";
            }

            var regex_flag_values = "";
            var prod_title_select = jQuery("#product_title_select").val();
            if (prod_title_select == "title_regex") {
                regex_flag_values = jQuery("#regex_flags_values").val();
            }
            var prod_title_text = "";
            if (jQuery("#product_title_select").val() != "all") {
                prod_title_text = jQuery("#product_title_text_val").val();
            }
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    _ajax_eh_bep_nonce: jQuery('#_ajax_eh_bep_nonce').val(),
                    action: 'eh_bep_update_products',
                    query_all: true,
                    type: type_data,
                    category: category_data,
                    pid: chunk_data[update_index],
                    index_val: update_index,
                    chunk_length: chunk_data.length,
                    attribute: attribute_data,
                    attribute_value: attribute_value_data,
                    attribute_action: att_action,
                    new_attribute_values: new_attrib_val,
                    attribute_variation: att_variation,
                    categories_to_update:sel_categories_to_update,
                    category_update_option : cat_update_option,
                    range: range_data,
                    desired_price: desired_price_data,
                    minimum_price: minimum_price_data,
                    maximum_price: maximum_price_data,
                    undo_update_op: undo_update,
                    shipping_unit: shipping_unit_text_data,
                    shipping_unit_select: shipping_unit_select_data,
                    title_select: title_select_data,
                    sku_select: sku_select_data,
                    catalog_select: catalog_select_data,
                    shipping_select: shipping_select_data,
                    sale_select: sale_select_data,
                    sale_round_select: sale_round_type,
                    regular_check_val: use_regular_val,
                    regular_round_select: regular_round_type,
                    regular_select: regular_select_data,
                    stock_manage_select: stock_manage_select_data,
                    quantity_select: quantity_select_data,
                    backorder_select: backorder_select_data,
                    stock_status_select: stock_status_select_data,
                    length_select: length_select_data,
                    width_select: width_select_data,
                    height_select: height_select_data,
                    weight_select: weight_select_data,
                    title_text: title_text_data,
                    replace_title_text: replace_title_text_data,
                    regex_replace_title_text: regex_replace_title_text_data,
                    sku_text: sku_text_data,
                    sku_delimeter: sku_delimeter_data,
                    sku_padding: sku_padding_data,
                    sku_replace_text: replace_sku_text_data,
                    regex_sku_replace_text: regex_replace_sku_text_data,
                    sale_text: sale_text_data,
                    sale_round_text: sale_round_val,
                    regular_round_text: regular_round_val,
                    regular_text: regular_text_data,
                    quantity_text: quantity_text_data,
                    length_text: length_text_data,
                    width_text: width_text_data,
                    height_text: height_text_data,
                    weight_text: weight_text_data,
                    hide_price: hide_price_select,
                    hide_price_role: hide_price_role_select,
                    price_adjustment: price_adjustment_select,
                    regex_flag_sele_title: regex_flag_title,
                    regex_flag_sele_sku: regex_flag_sku,
                    custom_meta: custom_meta_value,
                    description_action: product_description_action,
                    short_description_action: product_short_description_action,
                    description: product_description,
                    short_description: product_short_description,
                    delete_product_action: delete_product,
                    prod_tags: tags,
                    main_image: prod_image,
                    gallery_images_action: gallery_image_action,
                    gallery_images: prod_gallery_images,
                    is_featured: featured_product,
                    tax_status_action: tax_status_actions,
                    tax_class_action: tax_class_actions,
                    unchecked_array: JSON.stringify(unchecked_array),
                },
                success: function (response) {
                    window.scrollTo(0, 0);
                    unchecked_products = 0;
                    var d = new Date();
                    d = d.toUTCString();

                    product_ids = jQuery.parseJSON(response);

                    var resp_length = product_ids.length;
                    jQuery(".loader").css("display", "none");
                    if (product_ids[resp_length - 1] != 'done') {
                        // jQuery("#logs_val").empty();
                        jQuery("#logs_val").append("<b>" + d + "</b> " + "             " + "<b style='color:blue;' >" + (update_index + 1) * 100 + "</b> products updated, <b style='color:blue;' >" + ((filtered_ids.length - unchecked_products) - ((update_index + 1) * 100)) + "</b> products remaining...<br><br>");
                        update_index++;
                        if (product_ids != '') {
                            xa_warning_display(product_ids);
                        }
                        jQuery("#logs_loader").html('<img src="./images/loading.gif">');
                        xa_update_products();
                    } else {
                        xa_warning_display(product_ids);
                        jQuery("#logs_loader").html(js_obj.allProductsUpdated + "<br><br>");
                        jQuery("#update_finished").show();
                        jQuery("#finish_cancel").hide();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        } else {
            jQuery("#update_logs").css("display", "none");
            jQuery("#edit_product").css("display", "block");

            document.getElementById("edit_product").hidden = false;
            jQuery('#step4').removeClass('active');
            jQuery('#step3').addClass('active');
            if (!title_vali || !sku_vali) {
                jQuery('html, body').animate({
                    scrollTop: jQuery("#edit_product").offset().top
                }, 1000);
            } else if (!sale_vali || !regualr_vali) {
                jQuery('html, body').animate({
                    scrollTop: jQuery("#update_general_table").offset().top
                }, 1000);
            } else if (!quanity_vali) {
                jQuery('html, body').animate({
                    scrollTop: jQuery("#update_price_table").offset().top
                }, 1000);
            } else if (!length_vali || !width_vali || !height_vali || !weight_vali) {
                jQuery('html, body').animate({
                    scrollTop: jQuery("#update_stock_table").offset().top
                }, 1000);
            }
        }
    }
});

//if the Sale price is greater then Regular.
function xa_warning_display(products_skipped) {

    var id_length = products_skipped.length;
    if (products_skipped[id_length - 1] == 'done') {
        id_length--;
    }
    for (var i = 0; i < id_length; i++) {
        var pr_id_link = products_skipped[i].link("./post.php?post=" + products_skipped[i + 1] + "&action=edit");
        pr_id_link = pr_id_link.replace('<a', '<a target=_blank');
        if (products_skipped[i + 3] == 'variable') {
            jQuery("#logs_val").append("<b>[Warning]</b> Skipping updation of " + products_skipped[i + 2] + " Price for the Product " + pr_id_link + " as it is a Variable Parent Product.<br><br>");
        }
        else {
            jQuery("#logs_val").append("<b>[Warning]</b> Skipping updation of " + products_skipped[i + 2] + " Price for the Product " + pr_id_link + " as Sales Price set is greater than Regular Price.<br><br>");
        }
        i = i + 3;
    }
    jQuery("#logs_val").append("<br><br>");
}

function bep_ajax_filter_products() {
    jQuery(".loader").css("display", "block");
    var type_data = '';
    var attribute_data = '';
    var attribute_value_data = '';
    var attribute_value_data_and = '';
    var range_data = '';
    var desired_price_data = '';
    var minimum_price_data = '';
    var maximum_price_data = '';
    var attribute_data_and = '';
    var sub_cat = '';
    var regex_flag_values = '';
    type_data = jQuery("#product_type").val();
    var category_data = {};
    jQuery('#elex_select_include_categories :selected').each(function () { var o = jQuery(this); category_data[o.text()] = o.val() });
    attribute_data = getValue_attrib_name();
    attribute_data_and = getValue_attrib_name_and();
    if (jQuery("#subcat_check").is(":checked")) {
        sub_cat = true;
    }
    if (getValue_attrib_name() != '')
        attribute_value_data = jQuery("#select_input_attributes").chosen().val();
    else {
        attribute_value_data = ''
    }
    if (getValue_attrib_name_and() != '')
        attribute_value_data_and = jQuery("#select_input_attributes_and").chosen().val();
    else {
        attribute_value_data_and = ''
    }

    if (jQuery("#regular_price_range_select").val() != 'all') {
        if (jQuery("#regular_price_range_select").val() != '|')
            desired_price_data = jQuery("#regular_price_text_val").val();
        else {
            minimum_price_data = jQuery("#regular_price_min_text").val();
            maximum_price_data = jQuery("#regular_price_max_text").val();
        }
    }
    if (desired_price_data != '' || minimum_price_data != '' || maximum_price_data != '') {
        range_data = jQuery("#regular_price_range_select").val();
    }
    var prod_title_select = jQuery("#product_title_select").val();
    if (prod_title_select == 'title_regex') {
        regex_flag_values = jQuery("#regex_flags_values").val();
    }
    var prod_title_text = '';
    if (jQuery("#product_title_select").val() != 'all') {
        prod_title_text = jQuery("#product_title_text_val").val();
    }

    var cats_to_exclude_filter = {};
    var exclude_sub_cat_filter = "";
    var exclude_prods_filter = "";
    if (jQuery("#enable_exclude_products").is(":checked")) {
        exclude_prods_filter = 1;
        jQuery('#elex_select_exclude_categories :selected').each(function () { var o = jQuery(this); cats_to_exclude_filter[o.text()] = o.val() });

        if (jQuery("#exclude_subcat_check").is(":checked")) {
            exclude_sub_cat_filter = 1;
        }
    }
    var category_data_filter = {};
    jQuery('#elex_select_include_categories :selected').each(function () { var o = jQuery(this); category_data_filter[o.text()] = o.val() });
    //exclude products
    var ids_to_exclude_filter = "";
    if (jQuery("#exclude_ids").length && jQuery("#exclude_ids").val() != "") {
        ids_to_exclude_filter = jQuery("#exclude_ids").val().split(",");
    }

    //exclude products
    var ids_to_exclude = "";
    if (jQuery("#exclude_ids").length && jQuery("#exclude_ids").val() != "") {
        ids_to_exclude = jQuery("#exclude_ids").val().split(",");
    }
    var cats_to_exclude = {};
    var exclude_sub_cat = 0;
    var exclude_prods = 0;
    if (jQuery("#enable_exclude_products").is(":checked")) {
        exclude_prods = 1;
        jQuery('#elex_select_exclude_categories :selected').each(function () { var o = jQuery(this); cats_to_exclude[o.text()] = o.val() });
        if (jQuery("#exclude_subcat_check").is(":checked")) {
            exclude_sub_cat = 1;
        }
    }
    var data = {
        paged: '1',
        _ajax_eh_bep_nonce: jQuery('#_ajax_eh_bep_nonce').val(),
        action: 'eh_bep_filter_products',
        type: type_data,
        category_filter: category_data,
        sub_category_filter: sub_cat,
        attribute: attribute_data,
        product_title_select: prod_title_select,
        product_title_text: prod_title_text,
        regex_flags: regex_flag_values,
        attribute_value_filter: attribute_value_data,
        attribute_and: attribute_data_and,
        attribute_value_and_filter: attribute_value_data_and,
        range: range_data,
        desired_price: desired_price_data,
        minimum_price: minimum_price_data,
        maximum_price: maximum_price_data,
        exclude_ids: ids_to_exclude_filter,
        exclude_categories: cats_to_exclude,
        exclude_subcat_check: exclude_sub_cat,
        enable_exclude_prods: exclude_prods
    };
    jQuery.ajax({
        type: 'post',
        url: ajaxurl,
        data: data,
        success: function (response) {
            jQuery("#top_filter_tag").css("display", "none");
            document.getElementById("top_filter_tag").hidden = true;
            document.getElementById("wrap_table").hidden = false;
            jQuery('#step1').removeClass('active');
            jQuery('#step4').removeClass('active');
            jQuery('#step3').removeClass('active');
            jQuery('#step2').addClass('active');
            jQuery("#wrap_table").css("display", "block");
            jQuery(".loader").css("display", "none");
            jQuery("#edit_product").css("display", "none");
            var response = jQuery.parseJSON(response);
            if (response.rows.length)
                jQuery('#the-list').html(response.rows);
            if (response.column_headers.length)
                jQuery('thead tr, tfoot tr').html(response.column_headers);
            if (response.pagination.bottom.length)
                jQuery('.tablenav.top .tablenav-pages').html(jQuery(response.pagination.top).html());
            if (response.pagination.top.length)
                jQuery('.tablenav.bottom .tablenav-pages').html(jQuery(response.pagination.bottom).html());
            list.init();
            if (response.total_items_count <= 0) {
                jQuery('#search_id-search-input').attr("disabled", "disabled");
                jQuery('#process_edit').attr("disabled", "disabled");
                if (response.regex_error == true) {
                    jQuery('.colspanchange').append(" Invalid regex expression.");
                }
            } else {
                jQuery('#search_id-search-input').removeAttr("disabled");
                jQuery('#process_edit').removeAttr("disabled");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });

}

function clear_edit_data() {
    jQuery('#title_action').prop('selectedIndex', 0);
    jQuery("#title_text").empty();
    jQuery('#sku_action').prop('selectedIndex', 0);
    jQuery("#sku_text").empty();
    jQuery('#is_featured').prop('selectedIndex', 0);
    jQuery('#description_action').prop('selectedIndex', 0);
    jQuery('#description_tr').hide();
    jQuery('#short_description_action').prop('selectedIndex', 0);
    jQuery('#short_description_tr').hide();
    jQuery("#elex_product_main_image").val('');
    jQuery('#gallery_image_action').prop('selectedIndex', 0);
    jQuery('#gallery_images_tr').hide();
    jQuery('#catalog_action').prop('selectedIndex', 0);
    jQuery('#shipping_class_action').prop('selectedIndex', 0);
    jQuery("#shipping_class_check_text").empty();
    jQuery('#stock_quantity_action').prop('selectedIndex', 0);
    jQuery("#stock_quantity_text").empty();
    jQuery('#allow_backorder_action').prop('selectedIndex', 0);
    jQuery('#stock_status_action').prop('selectedIndex', 0);
    jQuery('#manage_stock_action').prop('selectedIndex', 0);
    jQuery("#manage_stock_check_text").empty();
    jQuery('#sale_price_action').prop('selectedIndex', 0);
    jQuery("#sale_price_text").empty();
    jQuery('#regular_price_action').prop('selectedIndex', 0);
    jQuery("#regular_price_text").empty();
    jQuery('#length_action').prop('selectedIndex', 0);
    jQuery("#length_text").empty();
    jQuery("#backorder_text").empty();
    jQuery('#width_action').prop('selectedIndex', 0);
    jQuery("#width_text").empty();
    jQuery('#height_action').prop('selectedIndex', 0);
    jQuery("#height_text").empty();
    jQuery('#weight_action').prop('selectedIndex', 0);
    jQuery("#weight_text").empty();
    jQuery('#attribute_action').prop('selectedIndex', 0);
    jQuery("#attr_names").hide();
    jQuery('#tax_status_action').prop('selectedIndex', 0);
    jQuery('#tax_class_action').prop('selectedIndex', 0);
    jQuery("input[id='size']").prop("checked", false);
    jQuery("input[id='color']").prop("checked", false);
    jQuery('#delete_product_action').prop('selectedIndex', 0);
    jQuery("#vari_attr_changesize").hide();
    jQuery("#vari_attr_changecolor").hide();
    jQuery('.regex-flags-edit-table').val('').trigger("chosen:updated");
    jQuery('#add_attribute_value_select').remove();
    jQuery('#new_attr_values').remove();
    jQuery('#select_variation').remove();
    jQuery('#shipping_unit_action').prop('selectedIndex', 0);
    jQuery("#shipping_unit_text").empty();
    jQuery("#regex_flags_field_title").hide();
    jQuery("#regex_help_link_title").hide();
    jQuery("#regex_flags_field_sku").hide();
    jQuery("#regex_help_link_sku").hide();
    jQuery("#sku_show").hide();
    jQuery("#sku_show_data").hide();
    jQuery('#price_adjustment_action').prop('selectedIndex', 0);
    jQuery('#visibility_price').prop('selectedIndex', 0);
    jQuery('.hide-price-role-select-chosen').val('').trigger("chosen:updated");
    jQuery("input[name='cat_update']").prop("checked", false);
    jQuery("#cat_select").hide();
    jQuery("#cat_update_none").prop("checked", true);
    jQuery("#regular_val_check").removeAttr('checked');
    jQuery("#regular_checkbox").hide();
    jQuery("input[name='meta_keys']").val("");
}
function clear_filters() {
    //var regex_default = ['g','m'];
    jQuery('#product_type').prop('selectedIndex', 0);
    jQuery('.category-chosen').val('').trigger("chosen:updated");
    jQuery("#attrib_name input:checked").each(function () {
        jQuery(this).removeAttr('checked');
    });
    jQuery("#subcat_check").removeAttr('checked');
    jQuery('#regular_price_range_select').prop('selectedIndex', 0);
    jQuery('#attribute_value_select').remove();
    jQuery('#attribute_value_select_and').remove();
    jQuery("#attrib_name_and input:checked").each(function () {
        jQuery(this).removeAttr('checked');
    });
    jQuery('#regular_price_range_text').empty();
    jQuery('.attribute-chosen').val('').trigger("chosen:updated");
    jQuery('#product_title_select').prop('selectedIndex', 0);
    jQuery('#product_title_text').empty();
    jQuery('#regex_flags_field').hide();
    jQuery('#regex_help_link').hide();
    jQuery("input[name='cat_filter']").prop("checked", false);
    jQuery("input[name='cat_exclude']").prop("checked", false);
    jQuery("#exclude_subcat_check").removeAttr('checked');
    jQuery("#enable_exclude_products").removeAttr('checked');
    jQuery('#exclude_ids').val('');
    jQuery('#exclude_products').hide();
}
function getValue_attrib_name() {
    var chkArray = [];
    jQuery("#attrib_name input:checked").each(function () {
        chkArray.push(jQuery(this).val());
    });
    var selected;
    selected = chkArray.join(',') + ",";
    if (selected.length > 1) {
        return (selected.slice(0, -1));
    } else {
        return ('');
    }
}
function getValue_attrib_name_and() {
    var chkArray = [];
    jQuery("#attrib_name_and input:checked").each(function () {
        chkArray.push(jQuery(this).val());
    });
    var selected;
    selected = chkArray.join(',') + ",";
    if (selected.length > 1) {
        return (selected.slice(0, -1));
    } else {
        return ('');
    }
}
function chunkArray(myArray, chunk_size) {
    var index = 0;
    var arrayLength = myArray.length;
    var tempArray = [];

    for (index = 0; index < arrayLength; index += chunk_size) {
        myChunk = myArray.slice(index, index + chunk_size);
        // Do something if you want with the group
        tempArray.push(myChunk);
    }

    return tempArray;
}
function append_attribute_value(attrib_name) {
    jQuery.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
            _ajax_eh_bep_nonce: jQuery('#_ajax_eh_bep_nonce').val(),
            action: 'eh_bep_get_attributes_action',
            attrib: attrib_name
        },
        success: function (data) {
            jQuery('#select_input_attributes').append(data);
            jQuery('.attribute-chosen').trigger("chosen:updated");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}

function append_attribute_value_and(attrib_name) {
    jQuery.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
            _ajax_eh_bep_nonce: jQuery('#_ajax_eh_bep_nonce').val(),
            action: 'eh_bep_get_attributes_action',
            attrib: attrib_name,
            attr_and: true
        },
        success: function (data) {
            jQuery('#select_input_attributes_and').append(data);
            jQuery('.attribute-chosen').trigger("chosen:updated");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}

function remove_attribute_value(attrib_name) {
    var id = '#grp_' + attrib_name;
    jQuery(id).remove();
    jQuery('.attribute-chosen').trigger("chosen:updated");
}
function remove_attribute_value_and(attrib_name) {
    var id = '#grp_and_' + attrib_name;
    jQuery(id).remove();
    jQuery('.attribute-chosen').trigger("chosen:updated");
}
jQuery(document).ready(function () {
    jQuery('table.wp-list-table').tableSearch();
});
(function (jQuery) {
    jQuery.fn.tableSearch = function (options) {
        if (!jQuery(this).is('table')) {
            return;
        }
        var tableObj = jQuery(this),
            inputObj = jQuery('#search_id-search-input');
        inputObj.off('keyup').on('keyup', function () {
            var searchFieldVal = jQuery(this).val().toLowerCase();
            tableObj.find('tbody tr').hide().each(function () {
                var currentRow = jQuery(this);
                currentRow.find('td').each(function () {
                    if (jQuery(this).html().toLowerCase().indexOf(searchFieldVal) > -1) {
                        currentRow.show();
                        return false;
                    }
                });
            });
        });
    }
}(jQuery));  