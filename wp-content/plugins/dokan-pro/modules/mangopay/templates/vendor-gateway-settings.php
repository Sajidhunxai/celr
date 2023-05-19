<?php
    do_action( 'dokan_mangopay_vendor_settings_before', $user_id );
    echo wc_print_notices();
?>

<div id="dokan-mangopay-payment">
<?php if ( $is_seller_connected && ! empty( $mp_user ) ) : ?>
    <div class="dokan-alert dokan-alert-success dokan-text-middle">
        <?php esc_html_e( 'Your account is connected with MangoPay.', 'dokan' ); ?>
    </div>

    <?php if ( ! $is_payout_enabled ) : ?>
        <div class="dokan-alert dokan-alert-danger dokan-text-middle">
            <?php esc_html_e( 'Your account is not eligible for Payout yet. Please make sure you have verified all the required KYC documents.', 'dokan' ); ?></br>
            <?php if ( 'LEGAL' === $mp_user->PersonType && 'BUSINESS' === $mp_user->LegalPersonType ) :
            esc_html_e( 'UBO declaration is also mandatory for your store. A valid company number should be provided for verification.', 'dokan' ) . '</br>';
            endif; ?>
            <?php esc_html_e( 'Also, you need to add an active Bank Account. If any of these is incomplete or invalid, MangoPay payment gateway will not be available for your store.', 'dokan' ); ?>
        </div>
    <?php endif; ?>

    <div>
        <p class="dokan-text-middle">
            <ul class="dokan-mangopay-tabs dokan-w12" id="dokan-mangopay-tabs">
                <li class="dokan-mp-account active" data-id="account"><?php esc_html_e( 'Account Info', 'dokan' ); ?></li>
                <li class="dokan-mp-bank" data-id="bank-account"><?php esc_html_e( 'Bank Account', 'dokan' ); ?></li>
                <li class="dokan-mp-verification" data-id="kyc|verification"><?php esc_html_e( 'Verification', 'dokan' ); ?></li>
                <li class="dokan-mp-wallets" data-id="wallets"><?php esc_html_e( 'E-Wallets', 'dokan' ); ?></li>
            </ul>
        </p>
    </div>
<?php else : ?>
    <div class="dokan-alert dokan-alert-warning dokan-text-left" id="dokan-mangopay-account-notice">
        <?php esc_html_e( 'Your account is not connected with MangoPay. Update the informations below to complete signing up.', 'dokan' ); ?>
    </div>
<?php endif; ?>

    <div id="dokan-mangopay-account" class="dokan-mangopay-container dokan-mangopay-account <?php echo $is_seller_connected ? 'tab-shown' : ''; ?>">
        <div class="dokan-alert dokan-text-middle action-notice hidden"></div>

        <?php foreach ( $signup_fields as $key => $fields ) :
            woocommerce_form_field(
                "settings[mangopay][vendor][$key]",
                $fields,
                ! empty( $fields['value'] )
                ? $fields['value']
                : (
                    ! empty( $payment_settings['vendor'][ $key ] )
                    ? $payment_settings['vendor'][ $key ]
                    : ''
                )
            );
        endforeach; ?>

        <?php if ( ! $is_seller_connected ) : ?>
            <button class="button dokan-mp-btn-submit"
                id="dokan-mangopay-account-connect"
                data-user="<?php echo esc_attr( $user_id ); ?>">
                <?php esc_html_e( 'Connect', 'dokan' ); ?>
            </button>
            <button class="button"
                id="dokan-mangopay-account-processing"
                style="display: none;"
                disabled="disabled"
                data-user="<?php echo esc_attr( $user_id ); ?>">
                <?php esc_html_e( 'Processing', 'dokan' ); ?>...
            </button>
        <?php else : ?>
            <button class="button"
                id="dokan-mangopay-account-disconnect"
                data-user="<?php echo esc_attr( $user_id ); ?>">
                <?php esc_html_e( 'Disconnect', 'dokan' ); ?>
            </button>
            <button class="button"
                id="dokan-mangopay-account-processing"
                style="display: none;"
                disabled="disabled"
                data-user="<?php echo esc_attr( $user_id ); ?>">
                <?php esc_html_e( 'Processing', 'dokan' ); ?>...
            </button>
            <button class="button dokan-mp-btn-submit"
                id="dokan-mangopay-account-connect"
                data-user="<?php echo esc_attr( $user_id ); ?>">
                <?php esc_html_e( 'Update', 'dokan' ); ?>
            </button>
        <?php endif; ?>
    </div>

    <?php do_action( 'dokan_mangopay_vendor_settings_bottom', $user_id, $payment_settings ); ?>
</div>

<?php do_action( 'dokan_mangopay_vendor_settings_after', $user_id ); ?>
