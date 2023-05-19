<div class="dokan-panel dokan-panel-default">
    <div class="dokan-panel-heading">
        <strong><?php esc_html_e( 'Shipments', 'dokan' ); ?></strong>
    </div>
    <div class="dokan-panel-body" id="dokan-order-shipping-status-tracking-panel">
        <div id="dokan-order-shipping-status-tracking-shippments">
            <?php
            $incre        = 1;
            $s_line_items = [];
            if ( $shipment_info ) :
                foreach ( $shipment_info as $key => $shipment ) :
                    $shipment_id       = $shipment->id;
                    $order_id          = $shipment->order_id;
                    $provider          = $shipment->provider;
                    $provider_label    = $shipment->provider_label;
                    $number            = $shipment->number;
                    $date              = $shipment->date;
                    $status_label      = $shipment->status_label;
                    $shipping_status   = $shipment->shipping_status;
                    $provider_url      = $shipment->provider_url;
                    $item_qty          = json_decode( $shipment->item_qty );
                    $is_editable       = true;
                    $shipment_timeline = dokan_pro()->shipment->custom_get_order_notes( $order_id, $shipment_id );

                    if ( 'ss_delivered' === $shipping_status || 'ss_cancelled' === $shipping_status ) {
                        $is_editable = false;
                    }

                    dokan_get_template_part(
                        'orders/shipment/html-vendor-shipment-item', '', array(
                            'pro'                 => true,
                            'shipment_id'         => $shipment_id,
                            'order_id'            => $order_id,
                            'provider'            => $provider,
                            'provider_label'      => $provider_label,
                            'number'              => $number,
                            'date'                => $date,
                            'status'              => $status_label,
                            'shipping_status'     => $shipping_status,
                            'provider_url'        => $provider_url,
                            'item_qty'            => $item_qty,
                            'order'               => $order,
                            'line_items'          => $line_items,
                            'incre'               => $incre,
                            'status_list'         => $status_list,
                            's_providers'         => $s_providers,
                            'd_providers'         => $d_providers,
                            'is_editable'         => $is_editable,
                            'shipment_timeline'   => $shipment_timeline,
                            'disabled_update_btn' => $disabled_create_btn,
                        )
                    );

                    $incre++;
                endforeach;
            endif;
            ?>
            <input type="hidden" name="security_update" id="security_update" value="<?php echo esc_attr( wp_create_nonce( 'update-shipping-status-tracking-info' ) ); ?>">
        </div><!-- .dokan-shippments-body -->
        <div class="dokan-hide" id="dokan-order-shipping-status-tracking">
            <?php if ( ! $is_shipped ) : ?>
                <form id="add-shipping-tracking-status-form" method="post" class="dokan-tracking-status-form" style="padding: 15px;">
                    <div class="woocommerce_order_items_wrapper wc-order-items-editable">
                        <table cellpadding="0" cellspacing="0" class="woocommerce_order_items dokan-table dokan-table-strip">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="item sortable" data-sort="string-ins" width="15%"><?php esc_html_e( 'Item', 'dokan' ); ?></th>
                                    <th class="title sortable" data-sort="int">&nbsp;</th>
                                    <th class="quantity sortable" data-sort="int"><?php esc_html_e( 'Qty', 'dokan' ); ?></th>
                                </tr>
                            </thead>
                            <tbody id="order_line_items">
                            <?php
                            foreach ( $line_items as $item_id => $item ) {
                                if ( dokan_pro()->shipment->get_status_order_item_shipped( $order_id, $item_id, $item['qty'] ) ) {
                                    continue;
                                }

                                if ( version_compare( WC_VERSION, '4.4.0', '>=' ) ) {
                                    $_product = $item->get_product();
                                } else {
                                    $_product = $order->get_product_from_item( $item );
                                }

                                dokan_get_template_part(
                                    'orders/shipment/html-vendor-shipment-product-item', '', array(
                                        'pro'          => true,
                                        'order'        => $order,
                                        'item'         => $item,
                                        'item_id'      => $item_id,
                                        'order_id'     => $order_id,
                                        '_product'     => $_product,
                                    )
                                );
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="dokan-form-group">
                        <label class="dokan-control-label"><?php esc_html_e( 'Shipping Status', 'dokan' ); ?></label>
                        <select name="shipping_status" id="shipping_status" class="dokan-form-control" style="width:50%;">
                            <option value=""><?php esc_html_e( 'Select', 'dokan' ); ?></option>
                            <?php if ( ! empty( $status_list ) ) : ?>
                                <?php foreach ( $status_list as $s_status ) : ?>
                                    <option value="<?php echo esc_attr( $s_status['id'] ); ?>"><?php echo esc_html( $s_status['value'] ); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <hr>
                    <h4 class="shipping-status-track-info-heading"><strong><?php esc_html_e( 'Tracking Information', 'dokan' ); ?></strong></h4>

                    <div class="dokan-form-group shipping-status-provider">
                        <label class="dokan-control-label"><?php esc_html_e( 'Shipping Provider', 'dokan' ); ?></label>
                        <select name="shipping_status_provider" id="shipping_status_provider" class="dokan-form-control select2">
                            <option value=""><?php esc_html_e( 'Select', 'dokan' ); ?></option>
                            <?php foreach ( $s_providers as $k_provider => $provider ) : ?>
                                <?php if ( ! empty( $provider ) && isset( $d_providers[ $k_provider ] ) ) : ?>
                                    <option value="<?php echo esc_attr( $k_provider ); ?>"><?php echo esc_html( $d_providers[ $k_provider ] ); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="dokan-form-group shipped-status-date">
                        <label class="dokan-control-label"><?php esc_html_e( 'Date Shipped', 'dokan' ); ?></label>
                        <input type="text" name="shipped_status_date" id="shipped_status_date" class="dokan-form-control shipped_status_date" value="" autocomplete="off" placeholder="<?php esc_attr_e( 'Select date', 'dokan' ); ?>">
                    </div>
                    <div class="dokan-form-group">
                        <label class="dokan-control-label tracking-status-number-label"><?php esc_html_e( 'Tracking Number', 'dokan' ); ?></label>
                        <input type="text" name="tracking_status_number" id="tracking_status_number" class="dokan-form-control" value="">
                    </div>
                    <div class="tracking-status-other-url dokan-hide">
                        <div class="dokan-form-group tracking-status-other-provider">
                            <label class="dokan-control-label tracking-status-url-label"><?php esc_html_e( 'Provider Name', 'dokan' ); ?></label>
                            <input type="text" name="tracking_status_other_provider" id="tracking_status_other_provider" class="dokan-form-control" autocomplete="off" value="">
                        </div>
                        <div class="dokan-form-group tracking-status-other-p-url">
                            <label class="dokan-control-label tracking-status-url-label"><?php esc_html_e( 'Tracking URL', 'dokan' ); ?></label>
                            <input type="text" name="tracking_status_other_p_url" id="tracking_status_other_p_url" class="dokan-form-control" autocomplete="off" value="">
                        </div>
                    </div>
                    <div class="dokan-form-group shipping-tracking-comments">
                        <label class="dokan-control-label tracking-status-url-label"><?php esc_html_e( 'Comments', 'dokan' ); ?></label>
                        <textarea name="tracking_status_comments" id="tracking_status_comments" class="dokan-form-control" rows="2" cols="2"></textarea>
                    </div>
                    <div class="dokan-form-group shipped-status-is-notify">
                        <label class="dokan-control-label" for="shipped_status_is_notify">
                            <input type="checkbox" name="is_notify" id="shipped_status_is_notify" value="on">
                            <?php esc_html_e( 'Notify shipment details to customer', 'dokan' ); ?>
                        </label>
                    </div>
                    <input type="hidden" name="security" id="security" value="<?php echo esc_attr( wp_create_nonce( 'add-shipping-status-tracking-info' ) ); ?>">
                    <input type="hidden" name="post_id" id="post-id" value="<?php echo esc_attr( $order_id ); ?>">
                    <input type="hidden" name="action" id="action" value="dokan_add_shipping_status_tracking_info">

                    <div class="dokan-form-group">
                        <input id="add-tracking-status-details" type="button" class="btn btn-primary" value="<?php esc_attr_e( 'Create Shipment', 'dokan' ); ?>">
                        <input id="cancel-tracking-status-details" type="button" class="btn btn-primary" value="<?php esc_attr_e( 'Cancel', 'dokan' ); ?>">
                    </div>
                    <p id="shipment-update-response-area"> </p>
                </form>
            <?php endif ?>
        </div>
        <?php
            $info_class = ! empty( $shipment_info ) && ! $is_shipped ? 'info-not-shipment-yet' : '';
        ?>
        <div class="shipping-status-tracking-top-info <?php echo esc_attr( $info_class ); ?>">
            <?php if ( empty( $shipment_info ) ) : ?>
                <p class="no-shipment-found-desc"><?php esc_html_e( 'No shipment found', 'dokan' ); ?></p>
            <?php endif; ?>

            <?php if ( ! $is_shipped && ! $disabled_create_btn ) : ?>
                <button id="create-tracking-status-action" type="button" class="dokan-btn dokan-btn-theme create-shipping-tracking"><?php esc_html_e( 'Create New Shipment', 'dokan' ); ?></button>
            <?php endif ?>
        </div>
    </div> <!-- .dokan-panel-body -->
</div> <!-- .dokan-panel -->
