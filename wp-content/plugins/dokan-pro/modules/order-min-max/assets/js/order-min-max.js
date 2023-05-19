/* global woocommerce_admin_meta_boxes_variations */
;(function($){
    // Min max
    let Dokan_Vendor_Order_Min_Max = {
        dokan_min_max_product_select_all: $('.dokan-min-max-product-select-all'),
        dokan_min_max_product_clear_all: $('.dokan-min-max-product-clear-all'),
        handleCheckBoxInput: function (checkedId, removeId) {
            if ($(checkedId).prop('checked')) {
                $(removeId).removeClass('dokan-hide');
            } else {
                $(removeId).addClass('dokan-hide');
            }
        },
        isCheckedCheckBoxInput: function (isChecked, removeId) {
            if (isChecked > 0){
                $(removeId).removeClass('dokan-hide');
            }else{
                $(removeId).addClass('dokan-hide');
            }
        },
        init: function () {
            let self = this;

            self.handleCheckBoxInput('#enable_vendor_min_max_quantity', '#min_max_quantity');
            self.handleCheckBoxInput('#enable_vendor_min_max_amount', '#min_max_amount');

            var isChecked =$('input[type=checkbox].order_min_max_input_handle:checked').length;
            self.isCheckedCheckBoxInput(isChecked, '.show_if_min_max_active');

            $('.order_min_max_input_handle').on('click', function () {
                let handle_id = $(this).attr('id');
                var isChecked =$('input[type=checkbox].order_min_max_input_handle:checked').length;
                self.isCheckedCheckBoxInput(isChecked, '.show_if_min_max_active');
                switch(handle_id) {
                    case 'enable_vendor_min_max_quantity':
                        self.handleCheckBoxInput( `#${handle_id}`, '#min_max_quantity');
                        break;
                    case 'enable_vendor_min_max_amount':
                        self.handleCheckBoxInput(`#${handle_id}`, '#min_max_amount');
                        break;
                    case 'product_wise_activation':
                        self.handleCheckBoxInput(`#${handle_id}`, '.show_if_min_max');
                        break;
                }
            });

            Dokan_Vendor_Order_Min_Max.dokan_min_max_product_select_all.on('click', function (e) {
                e.preventDefault();
                let self = $(this),
                    select = self.closest('div').find('select.dokan-coupon-product-select');
                    select.find('option:first').prop('selected', 'selected');
                    select.trigger('change');
            })

            Dokan_Vendor_Order_Min_Max.dokan_min_max_product_clear_all.on('click', function (e) {
                e.preventDefault()
                let self = $(this),
                    select = self.closest('div').find('select.dokan-coupon-product-select')
                    select.val('');
                    select.trigger('change');
            })
        },
        do_variation_action: function() {
            $('.do_variation_action').on('click', function (e) {
                e.preventDefault();
                var variation_action = $('select.variation_actions').val();
                var variable_product = $('.variable_product_wise_activation' );
                switch (variation_action) {
                    case 'variable_min_quantity' :
                    case 'variable_max_quantity' :
                    case 'variable_min_amount' :
                    case 'variable_max_amount' :
                        let value = window.prompt( woocommerce_admin_meta_boxes_variations.i18n_enter_a_value );
                        if ( value == null || value === '' || value < 1 ) {
                            variable_product.prop("checked", false);
                            return false; // To avoid ajax call, return false.
                        }

                        variable_product.prop("checked", true);
                        $('.' + variation_action ).val(value).change();
                        return false; // To avoid ajax call, return false.
                    case 'min_max_deactivate_for_all' :
                        variable_product.prop("checked", false);
                        return false; // To avoid ajax call, return false.
                }
            })
        }
    }

    jQuery( document ).ready( function ( $ ) {
        Dokan_Vendor_Order_Min_Max.init();
        Dokan_Vendor_Order_Min_Max.do_variation_action();
    } );

})(jQuery);
