<div class="mp_payment_fields" id="dokan-mangopay-payment-options">
    <!-- Credit cards selector -->
    <?php if ( $credit_card_activated ) : ?>
    <div class="pay-method-wrap">
        <?php if ( $credit_card_enabled ) : ?>
        <div class="card-dropdown-wrap">
            <input type="radio" name="dokan_mangopay_payment_type" class="dokan-mangopay-payment-type card" value="card" checked="checked" />
            <label for="dokan_mangopay_payment_type"><?php echo esc_html__( 'Credit Card', 'dokan' ) . '&nbsp;'; ?></label>
        <?php else : ?>
            <label for="dokan-mangopay-card-type"><?php echo esc_html__( 'Credit Card Type', 'dokan' ) . '&nbsp;'; ?></label>
        <?php endif; ?>
            <select name="dokan_mangopay_card_type" id="dokan-mangopay-card-type">
            <?php foreach ( $available_card_types as $type => $label ) : ?>
                <?php if ( 'BANK_WIRE' !== $type && in_array( $type, $selected_credit_cards, true ) ) : ?>
                <option value="<?php echo esc_attr( $type ); ?>" <?php selected( $type, $selected_card, false ); ?>>
                    <?php esc_html_e( $label, 'dokan' ); ?>
                </option>
                <?php endif; ?>
            <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php endif; ?>


    <!-- Debit cards selector -->
    <?php if ( $debit_card_enabled ) : ?>
        <div class="mp_spacer">&nbsp;</div>
        <div class="card-dropdown-wrap">
        <?php if ( $credit_card_activated || in_array( 'BANK_WIRE', $selected_credit_cards, true ) ) : ?>
            <input type="radio" name="dokan_mangopay_payment_type" class="dokan-mangopay-payment-type directdebitweb" value="directdebitweb" <?php echo ! $credit_card_enabled ? 'checked' : ''; ?>/>
            <label for="dokan-mangopay-directdw-type"><?php esc_html_e( 'Direct Debit Web Wire', 'dokan' ) . ': &nbsp;'; ?></label>
        <?php else : ?>
            <label for="dokan-mangopay-directdw-type"><?php echo esc_html( 'Payment Type', 'dokan' ) . ': &nbsp;'; ?></label>
        <?php endif; ?>
            <select name="dokan_mangopay_directdebitweb_type" id="dokan-mangopay-directdw-type">
            <?php foreach ( $available_direct_payments as $type => $label ) : ?>
                <?php if ( in_array( $type, $selected_debit_cards, true ) ) : ?>
                <option value="<?php echo esc_attr( $type ); ?>" <?php selected( $type, $selected_card ); ?>>
                    <?php esc_html_e( $label, 'dokan' ); ?>
                </option>
                <?php endif; ?>
            <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <!-- Bank wire selector -->
    <?php if ( in_array( 'BANK_WIRE', $selected_credit_cards, true ) ) : ?>
        <?php if ( $credit_card_activated || $debit_card_enabled ) : ?>
        <div class="mp_spacer">&nbsp;</div>
        <div class="card-dropdown-wrap">
            <div class="direct-dropdown-wrap">
                <input id="dokan-mangopay-payment-type-bw" type="radio" name="dokan_mangopay_payment_type" value="bank_wire" />
                <label for="dokan-mangopay-payment-type-bw"><?php esc_html_e( 'Direct Bank Wire', 'dokan' ); ?></label>
            </div>
        </div>
        <?php else : ?>
            <input type="hidden" name="dokan_mangopay_payment_type" value="bank_wire" />
            <label for="dokan_mangopay_payment_type"><?php esc_html_e( 'Direct Bank Wire', 'dokan' ); ?></label>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ( $saved_cards_enabled && ! empty( $mangopay_account_id ) ) : ?>
        <div class="mp_spacer">&nbsp;</div>
        <input type="hidden" name="dokan_mangopay_url" id="dokan-mangopay-url" value="<?php echo esc_url_raw( $mangopay_url ); ?>">

        <div>
            <input type="radio" id="dokan-mangopay-payment-type-registeredcard" name="dokan_mangopay_payment_type" class="dokan-mangopay-payment-type directdebitweb" value="registeredcard" />
            <label for="dokan_mangopay_payment_type"><?php esc_html_e( 'Registered Card', 'dokan' ); ?></label>
            <button type="button" id="dokan-mp-add-card" style="display:none;">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_attr_e( 'Add New', 'dokan' ); ?>
            </button>
        </div>

        <?php if ( is_user_logged_in() ) : ?>
        <!-- Waiting -->
        <div id="dokan-mp-saved-cards-list" class="dokan-mp-saved-cards-list"></div>

        <!-- List cards usable of this user -->
        <div id="dokan-mp-card-save-processing" class="dokan-mp-card-save-processing"><?php esc_html_e( 'Processing', 'dokan' ); ?>...</div>

        <!-- Form to add card -->
        <div id="dokan-mp-card-registration-fields">
            <div id="dokan-mp-card-save-error" class="dokan-mp-card-save-error"></div>
            <!-- CARD TYPE -->
            <div class="registered-card-type">
                <label><?php esc_html_e( 'Card Type', 'dokan' ); ?></label>
                <select name="registered_card_type" id="registered_card_type" class="mp-registered-card-type">
                    <?php foreach ( $available_card_types as $type => $label ) : ?>
                        <?php if ( 'BANK_WIRE' !== $type && 'IDEAL' !== $type && in_array( $type, $selected_credit_cards, true ) ) : ?>
                            <option value="<?php echo esc_attr( $type ); ?>" <?php selected( $type, $selected_card, false ); ?>>
                                <?php esc_html_e( $label, 'dokan' ); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- CREDIT CARD NUMBER -->
            <div class="ccnumber">
                <label><?php esc_html_e( 'Card Number','dokan' ); ?></label>
                <input type="text" id="dokan-mp-ccnumber" name="preauth_ccnumber" class="mp-ccnumber">
            </div>

            <div class="cccrypto">
                <label><?php esc_html_e( 'CVV', 'dokan' ); ?></label>
                <input type="text" id="dokan-mp-cccrypto" name="preauth_cccrypto" class="mp-cccrypto">
            </div>

            <!-- CREDIT CARD DATE -->
            <div class="ccdate">
                <label><?php esc_html_e( 'Expiration Date', 'dokan' ); ?></label>

                <div class="dokan-mp-ccdate">
                    <select name="ccdate_month" id="preauth_ccdate_month" class="mp-ccdate-month">
                        <?php foreach ( $months as $key => $month ) : ?>
                            <option value="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $month ); ?></option>
                        <?php endforeach; ?>
                    <select>

                    &nbsp;/&nbsp;

                    <select name="ccdate_year" id="preauth_ccdate_year" class="mp-ccdate-year">
                        <?php foreach ( $years as $key => $year ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $year ); ?></option>
                        <?php endforeach; ?>
                    <select>
                </div>
            </div>

            <input type="hidden" id="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
            <input type="hidden" id="order_currency" value="<?php echo esc_attr( get_woocommerce_currency() ); ?>">
            <input type="hidden" id="dokan-mp-client-id" value="<?php echo esc_attr( $client_id ); ?>">

            <div class="dokan-mp-card-register">
                <button id="save_preauth_card_button" class="button alt" type="button">
                    <?php esc_html_e( 'Register', 'dokan' ); ?>
                </button>
                <button type="button" id="cancel_addcard" style="display:none;">
                    <?php esc_attr_e( 'Cancel', 'dokan' ); ?>
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<input type="hidden" id="dokan-mangopay-3ds2-java" name="dokan-mangopay-3ds2-java" />
<input type="hidden" id="dokan-mangopay-3ds2-js" name="dokan-mangopay-3ds2-js" />
<input type="hidden" id="dokan-mangopay-3ds2-lang" name="dokan-mangopay-3ds2-lang" />
<input type="hidden" id="dokan-mangopay-3ds2-color-depth" name="dokan-mangopay-3ds2-color-depth" />
<input type="hidden" id="dokan-mangopay-3ds2-screen-height" name="dokan-mangopay-3ds2-screen-height" />
<input type="hidden" id="dokan-mangopay-3ds2-screen-width" name="dokan-mangopay-3ds2-screen-width" />
<input type="hidden" id="dokan-mangopay-3ds2-timezone-offset" name="dokan-mangopay-3ds2-timezone-offset" />
<input type="hidden" id="dokan-mangopay-3ds2-user-agent" name="dokan-mangopay-3ds2-user-agent" />
