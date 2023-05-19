<?php
/**
 * Payment details schedule `WP.script` template
 *
 * @since 3.5.0
 */
?>
<script type="text/html" id="tmpl-withdraw-schedule-popup">
    <div id="withdraw-schedule-popup" class="white-popup dokan-withdraw-popup">
        <div id="dokan-send-withdraw-schedule-popup-form">
            <?php do_action( 'dokan_send_withdraw_schedule_form_content' ); ?>

            <div class="withdraw-schedule-select-container">
                <div class="dokan-form-group">
                    <label for="preferred-payment-method"><strong><?php esc_html_e( 'Preferred Payment Method', 'dokan' ); ?></strong></label>
                    <select class="dokan-form-control" id="preferred-payment-method">
                        <?php foreach ( $active_methods as $payment_method ) : ?>
                            <option value="<?php echo esc_attr( $payment_method ); ?>" <?php selected( $default_method, $payment_method ); ?>>
                                <?php
                                    echo wp_kses_post(
                                        // translators: 1: payment method name. 2: payment method information.
                                        sprintf(
                                            '%1$s %2$s',
                                            dokan_withdraw_get_method_title( $payment_method ),
                                            dokan_withdraw_get_method_additional_info( $payment_method )
                                        )
                                    );
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p>
                    <strong><?php esc_html_e( 'Preferred Payment Schedule', 'dokan' ); ?></strong><br>
                    <?php esc_html_e( 'Earning will be released upon your request.', 'dokan' ); ?>
                </p>
                <div class="dokan-form-group">
                    <?php foreach ( $schedules as $schedule_key => $schedule_info ) : ?>
                    <div class="radio">
                        <label>
                            <input
                                type="radio" name="withdraw-schedule"
                                id="withdraw-schedule-<?php echo esc_attr( $schedule_key ); ?>>"
                                data-next-schedule="<?php echo esc_attr( $schedule_info['next'] ); ?>"
                                value="<?php echo esc_attr( $schedule_key ); ?>"
                                <?php checked( $schedule_key, $selected_schedule ); ?>
                            >
                            <?php
                            // translators: 1: Schedule title 2: Schedule description.
                            echo wp_kses_post( sprintf( __( '<strong>%1$s</strong> ( %2$s )', 'dokan' ), $schedule_info['title'], $schedule_info['description'] ) );
                            ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="dokan-form-group">
                    <label for="minimum-withdraw-amount"><strong><?php esc_html_e( 'Only When Balance Is', 'dokan' ); ?></strong></label>
                    <select class="dokan-form-control" id="minimum-withdraw-amount">
                        <?php foreach ( $minimum_amount_list as $amount ) : ?>
                            <option value="<?php echo esc_attr( $amount ); ?>" <?php selected( $minimum_amount_selected, $amount ); ?>>
                                <?php
                                    /* translators: %s amount with currency */
                                    echo wp_kses_post( sprintf( __( '%s or more', 'dokan' ), wc_price( $amount ) ) );
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="dokan-form-group">
                    <label for="withdraw-remaining-amount"><strong><?php esc_html_e( 'Maintain A Reserve Balance', 'dokan' ); ?></strong></label>
                    <select class="dokan-form-control" id="withdraw-remaining-amount">
                        <?php foreach ( $reserve_balance_list as $remaining_balance ) : ?>
                            <option value="<?php echo esc_attr( $remaining_balance ); ?>" <?php selected( $reserve_balance_selected, $remaining_balance ); ?>>
                                <?php echo wp_kses_post( wc_price( $remaining_balance ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p style="padding-top: 20px;"><strong><?php esc_html_e( 'Next Payment', 'dokan' ); ?></strong> <?php esc_html_e( '( based on your schedule )', 'dokan' ); ?><br>
                    <span id="dokan-withdraw-next-scheduled-date"><?php echo esc_html( $schedules[ $selected_schedule ]['next'] ); ?></span></p>
            </div>
            <div class="footer">
                <button
                    class="dokan-btn dokan-btn-theme" id="dokan-withdraw-schedule-request-submit"
                    data-security="<?php echo esc_attr( wp_create_nonce( 'dokan_withdraw_schedule_nonce' ) ); ?>"
                >
                    <?php esc_html_e( 'Change Schedule', 'dokan' ); ?>
                </button>
            </div>
        </div>
    </div>
</script>
