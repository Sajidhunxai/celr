<div class="dokan-customer-shipment-list-area">
    <h2><?php esc_html_e( 'Shipments', 'dokan' ); ?></h2>
    <div class="customer-shipment-list-inner-area">
        <?php
        $incre = 1;
        foreach ( $shipment_info as $key => $shipment ) :
            $shipment_id       = $shipment->id;
            $order_id          = $shipment->order_id;
            $provider          = $shipment->provider_label;
            $number            = $shipment->number;
            $status_label      = $shipment->status_label;
            $shipping_status   = $shipment->shipping_status;
            $provider_url      = $shipment->provider_url;
            $item_qty          = json_decode( $shipment->item_qty );
            $shipment_timeline = dokan_pro()->shipment->custom_get_order_notes( $order_id, $shipment_id );

            dokan_get_template_part(
                'orders/shipment/html-shipment-list', '', array(
                    'pro'               => true,
                    'shipment_id'       => $shipment_id,
                    'order_id'          => $order_id,
                    'provider'          => $provider,
                    'number'            => $number,
                    'status'            => $status_label,
                    'shipping_status'   => $shipping_status,
                    'provider_url'      => $provider_url,
                    'item_qty'          => $item_qty,
                    'order'             => $order,
                    'incre'             => $incre,
                    'shipment_timeline' => $shipment_timeline,
                )
            );

            $incre++;
        endforeach;
        ?>
    </div>
</div>
