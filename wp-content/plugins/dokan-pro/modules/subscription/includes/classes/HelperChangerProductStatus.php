<?php

namespace WeDevs\DokanPro\Modules\Subscription;

use WeDevs\Dokan\Abstracts\ProductStatusChanger;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Product status changer class
 *
 * @since 3.7.21
 */
class HelperChangerProductStatus extends ProductStatusChanger {

    /**
     * Get products to process
     *
     * @since DOKAN_SINCE
     *
     * @return int[]
     */
    public function get_products() {
        $product_types = array_filter(
            wc_get_product_types(), function ( $type ) {
                return 'product_pack' !== $type;
            }
        );

        $status = dokan_get_option( 'product_status_after_end', 'dokan_product_subscription', 'draft' );

        $args = [
            'status' => 'change_status' === $this->get_task_type() ? [ 'publish', 'pending' ] : $status,
            'type'   => array_merge( array_keys( $product_types ) ),
            'author' => $this->get_vendor_id(),
            'page'   => $this->get_current_page(),
            'limit'  => $this->get_per_page(),
            'return' => 'ids',
        ];

        return wc_get_products( $args );
    }
}
