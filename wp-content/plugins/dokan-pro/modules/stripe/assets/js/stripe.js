jQuery( function($) {
    Stripe.setPublishableKey( dokan_stripe_connect_params.key );

    /* Checkout Form */
    $('form.woocommerce-checkout').on('checkout_place_order_dokan-stripe-connect', function( event ) {
        return stripeFormHandler();
    });

    /* Pay Page Form */
    $('form#order_review').on( 'submit', function() {
        return stripeFormHandler();
    });

    /* Both Forms */
    $("form.woocommerce-checkout, form#order_review").on('change', '.card-number, .card-cvc, .card-expiry-month, .card-expiry-year, input[name=dokan_stripe_customer_id], #dokan-stripe-connect-card-number, #dokan-stripe-connect-card-cvc, #dokan-stripe-connect-card-expiry', function( event ) {
        $( '.woocommerce_error, .woocommerce-error, .woocommerce-message, .woocommerce_message, .stripe_token' ).remove();
        $('.stripe_token' ).remove();

        dokan_stripe_connect_params.token_done = false;
    });

    $("form.woocommerce-checkout, form#order_review").on('change', 'input[name=dokan_stripe_customer_id]', function() {
        if ( $('input[name=dokan_stripe_customer_id]:checked').val() == 'new' ) {
            $('div.stripe_new_card').slideDown( 200 );
        } else {
            $('div.stripe_new_card').slideUp( 200 );
        }
    } );

    function stripeFormHandler() {
        if ( $('#payment_method_dokan-stripe-connect').is(':checked') ) {
            if ( isStripeSaveCardChosen() || $( 'input.stripe_token' ).length ) {
                return true;
            }

            var card = $('#dokan-stripe-connect-card-number').val();
            var cvc = $('#dokan-stripe-connect-card-cvc').val();
            var $form = $("form.woocommerce-checkout, form#order_review");
            var expires = $('#dokan-stripe-connect-card-expiry').payment( 'cardExpiryVal' );
            var month = parseInt( expires['month'] ) || 0;
            var year = parseInt( expires['year'] ) || 0;

            month = stripe_pad( month, 2 );
            year = stripe_pad( year, 2 );
            $form.block({message: null, overlayCSS: {background: '#fff url(' + woocommerce_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6}});

            if ( ! $('#billing_first_name').length ) {
                name            = dokan_stripe_connect_params.billing_first_name + ' ' + dokan_stripe_connect_params.billing_last_name;
                address_line1   = dokan_stripe_connect_params.billing_address_1;
                address_line2   = dokan_stripe_connect_params.billing_address_2;
                address_state   = dokan_stripe_connect_params.billing_state;
                address_city    = dokan_stripe_connect_params.billing_city;
                address_zip     = dokan_stripe_connect_params.billing_postcode;
                address_country = dokan_stripe_connect_params.billing_country;
            } else {
                name            = $('#billing_first_name').val() + ' ' + $('#billing_last_name').val();
                address_line1   = $('#billing_address_1').val();
                address_line2   = $('#billing_address_2').val();
                address_state   = $('#billing_state').val();
                address_city    = $('#billing_city').val();
                address_zip     = $('#billing_postcode').val();
                address_country = $('#billing_country').val();
            }

            Stripe.createToken( {
                number: card,
                cvc: cvc,
                exp_month: month,
                exp_year: year,
                name: name,
                address_line1: address_line1,
                address_line2: address_line2,
                address_state: address_state,
                address_city: address_city,
                address_zip: address_zip,
                address_country: address_country
            }, stripeResponseHandler );

            return false;
        }

        return true;
    }

    function isStripeSaveCardChosen() {
        return (
            $( '#payment_method_dokan-stripe-connect' ).is( ':checked' )
            && $( 'input[name="wc-dokan-stripe-connect-payment-token"]' ).is( ':checked' )
            && 'new' !== $( 'input[name="wc-dokan-stripe-connect-payment-token"]:checked' ).val()
        );
    }

    function stripeResponseHandler( status, response ) {
        var $form = $("form.woocommerce-checkout, form#order_review");

        if ( response.error ) {
            $('.woocommerce_error, .woocommerce-error, .woocommerce-message, .woocommerce_message, .stripe_token').remove();
            $('#dokan-stripe-connect-card-number').closest('p').before( '<ul class="woocommerce_error woocommerce-error"><li>' + response.error.message + '</li></ul>' );

            $form.unblock();
        } else {
            var token = response['id'];

            dokan_stripe_connect_params.token_done = true;
            $( '.stripe_token' ).remove();

            $form.off( 'checkout_place_order_dokan-stripe-connect' );
            $form.append("<input type='hidden' class='stripe_token' name='stripe_token' value='" + token + "'/>");
            $form.submit();

            //in case of error from stripe end, we need to create stripe token again.
            $form.on('checkout_place_order_dokan-stripe-connect', function( event ) {
                return stripeFormHandler();
            });
        }
    }

    function stripe_pad( num, size ) {
        var string = num + '';

        while ( string.length < size ) {
            string = '0' + string;
        }

        return string;
    }
} );
