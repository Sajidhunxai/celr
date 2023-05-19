<h3><?php _e( 'Stripe Connect', 'dokan' ); ?></h3>
<p><?php _e( 'Stripe works by adding credit card fields on the checkout and then sending the details to Stripe for verification.', 'dokan' ); ?></p>
<p>
    <?php
    echo wp_kses(
        sprintf(
            __( 'Set your authorize redirect uri <code>%s</code> in your Stripe <a href="%s" target="_blank">application settings</a> for Redirects.', 'dokan' ),
            dokan_get_navigation_url( 'settings/payment-manage-dokan-stripe-connect' ),
            'https://dashboard.stripe.com/account/applications/settings'
        ),
        [
            'a'    => [
                'href'   => true,
                'target' => true,
            ],
            'code' => [],
        ]
    )
    ?>
</p>
<p>
    <?php
    echo wp_kses(
        sprintf(
            __( 'Recurring subscription requires webhooks to be configured. Go to <a href="%1$s" target="_blank">webhook</a> and set your webhook url <code>%2$s</code> (if not automatically set). Otherwise recurring payment will not work automatically.', 'dokan' ),
            'https://dashboard.stripe.com/account/webhooks',
            home_url( 'wc-api/dokan_stripe' )
        ),
        [
            'a'    => [
                'href'   => true,
                'target' => true,
            ],
            'code' => [],
        ]
    )
    ?>
</p>
<table class="form-table">
    <?php $gateway->generate_settings_html(); ?>
</table>
