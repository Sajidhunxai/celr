<?php
/**
 * Payment details schedule popup template
 *
 * @since 3.5.0
 */
?>

<div id="dokan-withdraw-schedule-popup"
    data-izimodal-title="<i class='fa fa-clock-o' aria-hidden='true'></i>&nbsp;<?php esc_html_e( 'Edit Withdraw Schedule', 'dokan' ); ?>"
></div>

<div class="dokan-clearfix dokan-panel-inner-container">
    <div class="dokan-w8">
        <div>
            <div class="dokan-switch-container">
                <strong><?php esc_html_e( 'Schedule', 'dokan' ); ?></strong>
                <label class='dokan-switch'>
                    <input
                        type='checkbox'
                        <?php echo esc_attr( $is_schedule_selected ? 'checked' : '' ); ?>
                        id="dokan-schedule-enabler-switch"
                        data-security="<?php echo esc_attr( wp_create_nonce( 'remove-withdraw-schedule' ) ); ?>"
                    >
                    <span class='slider round'></span>
                </label>
            </div>
            <p>
                <?php echo wp_kses_post( $schedule_information ); ?><br>
                <?php echo wp_kses_post( $threshold_information ); ?>
            </p>
        </div>

        <?php do_action( 'dokan_withdraw_content_after_schedule' ); ?>

    </div>
    <div class="dokan-w5">
        <button class="dokan-btn" id="dokan-withdraw-display-schedule-popup"><?php esc_html_e( 'Edit Schedule', 'dokan' ); ?></button>
        <?php do_action( 'dokan_withdraw_content_after_schedule_button' ); ?>
    </div>
</div>
