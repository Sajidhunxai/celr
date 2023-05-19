<?php do_action( 'dokan_stripe_express_vendor_settings_before', $user_id ); ?>

<div id="dokan-stripe-express-payment">
    <div class="dokan-alert dokan-alert-success dokan-text-middle signup-message" id="dokan-stripe-express-signup-message"></div>
    <div class="dokan-alert dokan-alert-danger dokan-text-middle signup-message" id="dokan-stripe-express-signup-error"></div>

    <?php if ( ! empty( $stripe_account->get_account_id() ) ) : ?>
        <?php if ( $stripe_account->is_connected() ) : ?>
            <div class="dokan-alert dokan-alert-success dokan-text-middle">
                <?php
                    echo wp_kses_post(
                        sprintf(
                            /* translators: 1) gateway title, 2) line break <br> tag, 3) merchant id, 4) line break <br> tag, 5) gateway title */
                            esc_html__( 'Your account is connected with %1$s.%2$sMerchant ID: %3$s.%4$sYou can visit your %5$s dashboard to track your payments and transactions.', 'dokan' ),
                            $gateway_title,
                            '<br>',
                            "<strong>{$stripe_account->get_account_id()}</strong>",
                            '<br>',
                            $gateway_title
                        )
                    );
                ?>
            </div>

            <div id="dokan-stripe-express-vendor-signup-message"></div>

            <button class="dokan-btn"
                id="dokan-stripe-express-dashboard-login">
                <?php esc_html_e( 'Visit Express Dashboard', 'dokan' ); ?>
            </button>

            <button class="dokan-btn dokan-btn-danger"
                id="dokan-stripe-express-account-disconnect">
                <?php esc_html_e( 'Disconnect', 'dokan' ); ?>
            </button>
        <?php else : ?>
            <div class="dokan-alert dokan-alert-warning dokan-text-middle">
                <?php
                    echo esc_html(
                        sprintf(
                            /* translators: gateway title */
                            __( 'Your have not completed the onboarding for %s. You can complete the process by clicking the button below.', 'dokan' ),
                            $gateway_title
                        )
                    );
                ?>
            </div>

            <div id="dokan-stripe-express-vendor-signup-message"></div>

            <div id='dokan-stripe-express-vendor-onboarding-buttons'>
                <button class="dokan-btn dokan-btn-success"
                    id="dokan-stripe-express-account-connect">
                    <?php esc_html_e( 'Complete Onboarding', 'dokan' ); ?>
                </button>
                <?php esc_html_e( 'Or', 'dokan' ); ?>
                <button
                    class='dokan-btn dokan-btn-danger'
                    id='dokan-stripe-express-account-cancel'>
                    <?php esc_html_e( 'Cancel Onboarding', 'dokan' ); ?>
                </button>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <div class="dokan-alert dokan-alert-warning dokan-text-left" id="dokan-stripe-express-account-notice">
            <?php
                echo esc_html(
                    sprintf(
                        /* translators: gateway title */
                        __( 'Your account is not connected with %s. Click on the button below to sign up.', 'dokan' ),
                        $gateway_title
                    )
                );
            ?>
        </div>

        <?php if ( ! empty( $supported_countries ) && ! $stripe_account->is_trashed() ) : ?>
        <div class="dokan-stripe-express-vendor-signup">
            <?php
            woocommerce_form_field(
                'dokan_stripe_express_vendor_country',
                [
                    'id'           => 'dokan_stripe_express_vendor_country',
                    'type'         => 'select',
                    'label'        => __( 'Your Country', 'dokan' ),
                    'options'      => [ '' => esc_html__( 'Select a country', 'dokan' ) ] + $supported_countries,
                    'class'        => array( 'address-field' ),
                    'autocomplete' => 'country',
                    'required'     => true,
                ]
            );
            ?>
        </div>
        <?php endif; ?>

        <div id="dokan-stripe-express-account-connect"
            data-user="<?php echo esc_attr( $user_id ); ?>">
            <img src="<?php echo esc_url_raw( DOKAN_STRIPE_EXPRESS_ASSETS . 'images/connect-button-slate.svg' ); ?>"
                alt="<?php esc_attr_e( 'Connect with Stripe', 'dokan' ); ?>">
        </div>
    <?php endif; ?>
</div>

<?php do_action( 'dokan_stripe_express_vendor_settings_after', $user_id ); ?>
