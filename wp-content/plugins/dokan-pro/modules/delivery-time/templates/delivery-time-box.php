<?php
/**
 * Dokan Delivery time box template
 *
 * @since 3.3.0
 * @package DokanPro
 */

$vendor_infos = isset( $vendor_infos ) ? $vendor_infos : [];
$json_infos   = wp_json_encode(
    [
        'vendor_data' => $vendor_infos
    ]
);

wp_add_inline_script( 'dokan-delivery-time-main-script', "let dokan_delivery_time_infos = {$json_infos}", 'before' );
?>

<div id="dokan-delivery-time-box">
    <div class="header">
        <h3><?php esc_html_e( 'Delivery details', 'dokan' ); ?></h3>
        <div class="delivery-timezone dokan-delivery-time-tooltip" data-tip="<?php esc_attr_e( 'Date & time is based on current site time.', 'dokan' ); ?>" tabindex="1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" height="18" viewBox="0 0 24 24" stroke="#333333">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.85" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class=""><?php echo esc_html( dokan_format_datetime() ); ?><?php echo wc_timezone_string() ? ', ' . wc_timezone_string() : ''; ?></span>
        </div>
    </div>
    <div class="delivery-time-body">
        <div id="dokan-spinner-overlay">
            <div class="dokan-spinner-wrapper">
                <span class="dokan-loading-spinner"></span>
            </div>
        </div>
        <?php foreach ( $vendor_infos as $id => $info ) : // phpcs:ignore ?>
            <?php if ( $info['is_delivery_time_active'] || $info['is_store_location_active'] ) : ?>
                <?php
                    $is_delivery_time_active = isset( $info['is_delivery_time_active'] ) ? $info['is_delivery_time_active'] : false;
                    $selected_delivery_type = $is_delivery_time_active ? 'delivery' : 'store-pickup';
                ?>
                <div class="delivery-group">
                    <input type="hidden" value="<?php echo esc_attr( $id ); ?>" name="<?php echo 'vendor_delivery_time[' . $id . '][vendor_id]'; ?>">
                    <input type="hidden" value="<?php echo esc_attr( $info['store_name'] ); ?>" name="<?php echo 'vendor_delivery_time[' . $id . '][store_name]'; ?>">
                    <div class="title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" height="22" viewBox="0 0 24 24" stroke="#333333">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span><?php echo esc_html( wp_trim_words( $info['store_name'], 2, '...' ) ); ?></span>
                    </div>

                    <?php
                    /**
                     * @since 3.3.7
                     *
                     * @param int $id
                     * @param array $info
                     */
                    do_action( 'dokan_before_delivery_box_info_message', $id, $info );
                    ?>

                    <div class="vendor-info">
                        <span><?php echo esc_html( $info['vendor_delivery_options']['delivery_box_info_message'] ); ?></span>
                    </div>

                    <?php
                    /**
                     * @since 3.3.7
                     *
                     * @param int $id
                     * @param array $info
                     */
                    do_action( 'dokan_after_delivery_box_info_message', $id, $info );

                    // Delivery date input label set by admin or by default Select date
                    $delivery_date_label = isset( $info['vendor_delivery_options']['delivery_date_label'] ) ? $info['vendor_delivery_options']['delivery_date_label'] : __( 'Select date', 'dokan' );
                    ?>

                    <input id="delivery-time-date-picker-<?php echo esc_attr( $id ); ?>" data-vendor_id="<?php echo esc_attr( $id ); ?>"
                        data-nonce="<?php echo esc_attr( wp_create_nonce( 'dokan_delivery_time' ) ); ?>" class="delivery-time-date-picker"
                        name="vendor_delivery_time[<?php echo esc_attr( $id ); ?>][delivery_date]" type="text"
                        placeholder="<?php echo esc_attr( $delivery_date_label ); ?>"
                        readonly="readonly">
                    <?php
                    /**
                     * @since 3.3.7
                     *
                     * @param int $id
                     * @param array $info
                     */
                    do_action( 'dokan_after_delivery_date_picker', $id, $info );
                    ?>

                    <input class="dokan-selected-delivery-type" id="dokan-selected-delivery-type-<?php echo esc_attr( $id ); ?>" name="vendor_delivery_time[<?php echo esc_attr( $id ); ?>][selected_delivery_type]" type="hidden" data-vendor_id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $selected_delivery_type ); ?>">

                    <select class="delivery-time-slot-picker" id="delivery-time-slot-picker-<?php echo esc_attr( $id ); ?>" name="vendor_delivery_time[<?php echo esc_attr( $id ); ?>][delivery_time_slot]">
                        <option selected disabled><?php esc_html_e( 'Select time slot', 'dokan' ); ?></option>
                    </select>

                    <?php
                    /**
                     * @since 3.3.7
                     *
                     * @param int $id
                     * @param array $info
                     */
                    do_action( 'dokan_after_delivery_time_slot_picker', $id, $info );
                    ?>

                    <hr>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
