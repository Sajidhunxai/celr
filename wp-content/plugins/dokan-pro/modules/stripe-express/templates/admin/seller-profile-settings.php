<style>
#dokan-stripe-express-admin .alert-notice {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
    width: fit-content;
}

#dokan-stripe-express-admin .alert-notice.alert-success {
    background-color: #dff0d8;
    border-color: #d6e9c6;
    color: #3c763d;
}

#dokan-stripe-express-admin .alert-notice.alert-info {
    background-color: #d9edf7;
    border-color: #bce8f1;
    color: #31708f;
}

#dokan-stripe-express-admin .alert-notice.alert-warning {
    background-color: #fcf8e3;
    border-color: #faebcc;
    color: #8a6d3b;
}

#dokan-stripe-express-admin .alert-notice.alert-danger {
    background-color: #f2dede;
    border-color: #ebccd1;
    color: #a94442;
}
</style>

<h3>
    <?php
    /* translators: %s) gateway title */
    printf( esc_html__( '%s Settings', 'dokan' ), $gateway_title );
    ?>
</h3>

<div id="dokan-stripe-express-admin">
    <?php if ( ! empty( $stripe_account->get_account_id() ) ) : ?>
        <?php if ( $stripe_account->is_connected() ) : ?>
            <div class="alert-notice alert-info">
                <?php
                    echo wp_kses_post(
                        sprintf(
                            /* translators: 1) gateway title, 2) line break <br> tag, 3) merchant id */
                            __( 'The user is connected to %1$s.%2$sAccount ID: %3$s.', 'dokan' ),
                            $gateway_title,
                            '<br>',
                            "<strong>{$stripe_account->get_account_id()}</strong>"
                        )
                    );
                ?>
            </div>

            <input type="submit"
                class="button delete"
                id="dokan_stripe_express_admin_disconnect"
                name="dokan_stripe_express_admin_disconnect"
                value="<?php esc_html_e( 'Disconnect User', 'dokan' ); ?>"
            >
        <?php else: ?>
            <div class="alert-notice alert-warning">
                <?php
                    echo esc_html(
                        sprintf(
                            /* translators: gateway title */
                            __( 'The user has not completed the onboarding for %s. The onboarding must be completed to be connected.', 'dokan' ),
                            $gateway_title
                        )
                    );
                ?>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <?php if ( $stripe_account->is_trashed() ) : ?>
            <div class="alert-notice alert-warning">
                <?php
                    echo wp_kses_post(
                        sprintf(
                            /* translators: 1) gateway title, 2) line break <br> tag, 3) merchant id */
                            __( 'The user has an existig account but is disconnected from %1$s.%2$sAccount ID: %3$s.', 'dokan' ),
                            $gateway_title,
                            '<br>',
                            "<strong>{$stripe_account->get_trashed_account_id()}</strong>"
                        )
                    );
                ?>
            </div>

            <input type="submit"
                class="button delete"
                id="dokan_stripe_express_admin_delete"
                name="dokan_stripe_express_admin_delete"
                value="<?php esc_html_e( 'Delete Account', 'dokan' ); ?>"
            >
        <?php else : ?>
            <div class="alert-notice alert-danger" id="dokan-stripe-express-account-notice">
                <?php
                    echo esc_html(
                        sprintf(
                            /* translators: gateway title */
                            __( 'The user is not connected to %s.', 'dokan' ),
                            $gateway_title
                        )
                    );
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php wp_nonce_field( 'dokan_stripe_express_user_edit', 'dokan_stripe_express_user_edit_nonce' ); ?>
</div>
