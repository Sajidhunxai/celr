<?php
    $hide_others_area = ( 'sp-other' === $provider ) ? '' : 'dokan-hide';
?>
<div class="shipping-status-tracking-shippments-inner shipment_id_<?php echo esc_attr( $shipment_id ); ?>">
    <div class="shippments-tracking-header">
        <h4 class="shippments-tracking-title">
            <strong><?php esc_html_e( 'Shipment', 'dokan' ); ?> #<?php echo esc_html( $incre ); ?> </strong>
        </h4>
        <p class="shippments-tracking-status">
            <strong class="<?php echo esc_attr( $shipping_status ); ?> status_label_<?php echo esc_attr( $shipment_id ); ?>"><?php echo esc_html( $status ); ?> </strong>

            <span class="shipment-item-details-tab-toggle" data-shipment_id="<?php echo esc_attr( $shipment_id ); ?>">
                <span class="fa fa-chevron-down details-tab-toggle-sort-desc"></span>
            </span>
        </p>
        <div class="clear"></div>
        <p class="shippments-tracking-via">
            <?php esc_html_e( 'via', 'dokan' ); ?>
            <strong><?php echo esc_html( $provider_label ); ?></strong>
            <a href="<?php echo esc_url( $provider_url ); ?>" target="_blank">
                <span class="fa fa-external-link"></span>
            </a>
            <a href="<?php echo esc_url( $provider_url ); ?>" target="_blank">
                <?php echo esc_html( $number ); ?>
            </a>
        </p>
    </div>
    <div class="shippments-tracking-items dokan-hide shipment_body_<?php echo esc_attr( $shipment_id ); ?>">
        <?php
        dokan_get_template_part(
            'orders/shipment/html-shipments-tracking-items', '', array(
                'pro'      => true,
                'item_qty' => $item_qty,
            )
        );
        ?>
    </div>
    <div class="shippments-tracking-footer dokan-hide shipment_footer_<?php echo esc_attr( $shipment_id ); ?>">
        <div class="shippments-tracking-footer-status">
            <div class="dokan-form-group">
                <label class="dokan-control-label"><?php esc_html_e( 'Shipping Status', 'dokan' ); ?></label>
                <select name="update_shipping_status_<?php echo esc_attr( $shipment_id ); ?>" id="update_shipping_status_<?php echo esc_attr( $shipment_id ); ?>" class="dokan-form-control" style="width: 50%;" <?php echo $is_editable ? '' : 'disabled="disabled"'; ?>>
                    <option value=""><?php esc_html_e( 'Select', 'dokan' ); ?></option>
                    <?php if ( ! empty( $status_list ) ) : ?>
                        <?php foreach ( $status_list as $s_status ) : ?>
                            <option value="<?php echo esc_attr( $s_status['id'] ); ?>" <?php echo selected( $shipping_status, $s_status['id'], false ); ?>><?php echo esc_html( $s_status['value'] ); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <hr>
            <h4 class="shipping-status-track-info-heading"><strong><?php esc_html_e( 'Tracking Information', 'dokan' ); ?></strong></h4>
            <div class="dokan-form-group shipping-status-provider">
                <label class="dokan-control-label"><?php esc_html_e( 'Shipping Provider', 'dokan' ); ?></label>
                <select name="update_shipping_provider_<?php echo esc_attr( $shipment_id ); ?>" id="update_shipping_provider_<?php echo esc_attr( $shipment_id ); ?>" data-shipment_id="<?php echo esc_attr( $shipment_id ); ?>" class="update_shipping_provider dokan-form-control" <?php echo $is_editable ? '' : 'disabled="disabled"'; ?>>
                    <option value=""><?php esc_html_e( 'Select', 'dokan' ); ?></option>
                    <?php foreach ( $s_providers as $k_provider => $provider_v ) : ?>
                        <?php if ( ! empty( $provider_v ) && isset( $d_providers[ $k_provider ] ) ) : ?>
                            <option value="<?php echo esc_attr( $k_provider ); ?>" <?php echo selected( $k_provider, $provider, false ); ?>><?php echo esc_html( $d_providers[ $k_provider ] ); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="dokan-form-group shipped-status-date">
                <label class="dokan-control-label"><?php esc_html_e( 'Date Shipped', 'dokan' ); ?></label>
                <input type="text" name="shipped_status_date_<?php echo esc_attr( $shipment_id ); ?>" id="shipped_status_date_<?php echo esc_attr( $shipment_id ); ?>" class="dokan-form-control shipped_status_date" value="<?php echo esc_attr( $date ); ?>" autocomplete="off" placeholder="<?php esc_attr_e( 'Select date', 'dokan' ); ?>" <?php echo $is_editable ? '' : 'readonly="readonly"'; ?>>
            </div>
            <div class="dokan-form-group">
                <label class="dokan-control-label tracking-status-number-label"><?php esc_html_e( 'Tracking Number', 'dokan' ); ?></label>
                <input type="text" name="tracking_status_number_<?php echo esc_attr( $shipment_id ); ?>" id="tracking_status_number_<?php echo esc_attr( $shipment_id ); ?>" class="dokan-form-control" value="<?php echo esc_attr( $number ); ?>" <?php echo $is_editable ? '' : 'readonly="readonly"'; ?>>
            </div>

            <div class="tracking-status-other-url tracking-status-other-area-<?php echo esc_attr( $shipment_id ); ?> <?php echo esc_attr( $hide_others_area ); ?>">
                <div class="dokan-form-group tracking-status-other-provider">
                    <label class="dokan-control-label tracking-status-url-label"><?php esc_html_e( 'Provider Name', 'dokan' ); ?></label>
                    <input type="text" name="tracking_status_other_provider_<?php echo esc_attr( $shipment_id ); ?>" id="tracking_status_other_provider_<?php echo esc_attr( $shipment_id ); ?>" class="dokan-form-control" autocomplete="off" value="<?php echo esc_attr( $provider_label ); ?>" <?php echo $is_editable ? '' : 'readonly="readonly"'; ?>>
                </div>
                <div class="dokan-form-group tracking-status-other-p-url">
                    <label class="dokan-control-label tracking-status-url-label"><?php esc_html_e( 'Tracking URL', 'dokan' ); ?></label>
                    <input type="text" name="tracking_status_other_p_url_<?php echo esc_attr( $shipment_id ); ?>" id="tracking_status_other_p_url_<?php echo esc_attr( $shipment_id ); ?>" class="dokan-form-control" autocomplete="off" value="<?php echo esc_attr( $provider_url ); ?>" <?php echo $is_editable ? '' : 'readonly="readonly"'; ?>>
                </div>
            </div>

            <?php if ( $is_editable ) : ?>
                <div class="dokan-form-group shipping-tracking-comments">
                    <label class="dokan-control-label tracking-status-url-label"><?php esc_html_e( 'Comments', 'dokan' ); ?></label>
                    <textarea name="tracking_status_comments_<?php echo esc_attr( $shipment_id ); ?>" id="tracking_status_comments_<?php echo esc_attr( $shipment_id ); ?>" class="dokan-form-control" rows="2" cols="2"></textarea>
                </div>
                <div class="dokan-form-group shipped-status-update-is-notify">
                    <label class="dokan-control-label shipped-status-update-label" for="shipment_update_is_notify_<?php echo esc_attr( $shipment_id ); ?>">
                        <input type="checkbox" name="shipment_update_is_notify_<?php echo esc_attr( $shipment_id ); ?>" id="shipment_update_is_notify_<?php echo esc_attr( $shipment_id ); ?>" value="on">
                        <?php esc_html_e( 'Notify Customer', 'dokan' ); ?>
                    </label>
                </div>
            <?php endif; ?>
        </div>
        <div class="shippments-tracking-footer-button">
            <?php if ( $is_editable && ! $disabled_update_btn ) : ?>
                <input id="update-tracking-status-details" type="button" class="btn btn-primary" value="<?php esc_attr_e( 'Update Shipment', 'dokan' ); ?>" data-shipment_id="<?php echo esc_attr( $shipment_id ); ?>">
            <?php endif; ?>
            <p id="shipment-update-response-area_<?php echo esc_attr( $shipment_id ); ?>" class="shipment-update-response-box"> </p>
        </div>
        <div class="clear"></div>
    </div>
    <?php if ( $shipment_timeline ) : ?>
        <div class="dokan-customer-shipment-notes-list-area dokan-hide shipment_footer_<?php echo esc_attr( $shipment_id ); ?>">
            <h5><strong><?php esc_html_e( 'Shipment Updates Timeline', 'dokan' ); ?></strong></h5>
            <span class="shipment-notes-details-tab-toggle" data-shipment_id="<?php echo esc_attr( $shipment_id ); ?>">
                <span class="fa fa-chevron-down details-tab-toggle-sort-desc"></span>
            </span>
            <div class="customer-shipment-list-notes-inner-area dokan-hide shipment-list-notes-inner-area<?php echo esc_attr( $shipment_id ); ?>">
                <?php
                    dokan_get_template_part(
                        'orders/shipment/html-shipment-timeline-updates', '', array(
                            'pro'               => true,
                            'order'             => $order,
                            'shipment_id'       => $shipment_id,
                            'shipment_timeline' => $shipment_timeline,
                        )
                    );
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="clear"></div>
