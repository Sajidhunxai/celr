jQuery( function($) {

    jQuery(document).on( 'click', '#place_order', function() {

        if ( jQuery('input#payment_method_dokan-stripe-connect').prop('checked') !== true ) {
            return;
        }

        var $form = jQuery("form.checkout, form#order_review");

        var token = $form.find('input.stripe_token');

        token.val('');

        var token_action = function( res ) {
            $form.find('input.stripe_token').remove();
            $form.append("<input type='hidden' class='stripe_token' name='stripe_token' value='" + res.id + "'/>");
            $form.submit();
        };

        var $stripe_data = jQuery( '.stripe_new_card' );

        StripeCheckout.open({
            key:         dokan_stripe_connect_params.key,
            locale:      dokan_stripe_connect_params.locale,
            bitcoin:     dokan_stripe_connect_params.bitcoin == 'true',
            image:       dokan_stripe_connect_params.image,
            name:        dokan_stripe_connect_params.name,
            description: dokan_stripe_connect_params.description,
            panelLabel:  dokan_stripe_connect_params.label,
            billingAddress: false,
            amount:      $stripe_data.data( 'amount' ),
            currency:    $stripe_data.data( 'currency' ),
            token:       token_action
        });

        return false;
    });
});