<style type="text/css" media="screen">
    .dokan-stripe-connect-container .dokan-alert {
        margin-bottom: 0;
    }
</style>

<div class="dokan-stripe-connect-container">
    <input type="hidden" name="settings[stripe]" value="<?php echo empty( $key ) ? 0 : 1; ?>">
    <?php if ( empty( $key ) && empty( $connected_vendor_id ) ) : ?>
        <p class="dokan-text-left">
            <a class="dokan-stripe-connect-link" href="<?php echo esc_url_raw( $auth_url ); ?>" target="_TOP">
                <img src="<?php echo esc_url( DOKAN_STRIPE_ASSETS . 'images/blue@2x.png' ); ?>" width="190" height="33" data-hires="true">
            </a>
        </p>

        <div class="dokan-alert dokan-alert-warning dokan-text-left">
            <?php esc_html_e( 'Your account is not connected with Stripe. Connect your Stripe account to receive payouts.', 'dokan' ); ?>
        </div>
    <?php else: ?>
        <p class="dokan-text-left">
            <a
                class="dokan-btn dokan-btn-danger dokan-btn-theme"
                href="<?php echo esc_url_raw( $disconnect_url ); ?>"
            ><?php _e( 'Disconnect', 'dokan' ); ?></a>
        </p>

        <div class="dokan-alert dokan-alert-success dokan-text-left">
            <?php esc_html_e( 'Your account is connected with Stripe. You can disconnect your account using the above button.', 'dokan' ); ?>
        </div>
    <?php endif; ?>
</div>
