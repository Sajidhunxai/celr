<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses;

use WeDevs\Dokan\Abstracts\DokanBackgroundProcesses;

class V_3_2_0_UpdateSubscriptionMeta extends DokanBackgroundProcesses {

    /**
     * Action
     *
     * Override this action in your processor class
     *
     * @since 3.2.0
     *
     * @var string
     */
    protected $action = 'dokan_pro_bg_action_3_2_0';

    /**
     * Sync Dokan Subscription old meta key data with new key
     *
     * @since 3.2.01
     *
     * @param int $page
     *
     * @return bool
     */
    public function task( $subscription_data ) {
        // check task type is update_subscription_meta
        if ( ! isset( $subscription_data['task'] ) || $subscription_data['task'] !== 'update_subscription_meta' ) {
            return false;
        }

        // check product id exist
        if ( empty( $subscription_data['product_id'] ) ) {
            return false;
        }

        $pf = new \WC_Product_Factory();
        $product = $pf->get_product( $subscription_data['product_id'] );

        if ( ! $product instanceof \WC_Product ) {
            return false;
        }

        // skip if product type is not product_pack
        if ( 'product_pack' !== $product->get_type() ) {
            return false;
        }

        // get old required meta key
        $subscription_period_interval   = get_post_meta( $product->get_id(), '_subscription_period_interval', true );
        $subscription_period            = get_post_meta( $product->get_id(), '_subscription_period', true );
        $subscription_length            = get_post_meta( $product->get_id(), '_subscription_length', true );

        // save them with new meta key
        update_post_meta( $product->get_id(), '_dokan_subscription_period_interval', $subscription_period_interval );
        update_post_meta( $product->get_id(), '_dokan_subscription_period', $subscription_period );
        update_post_meta( $product->get_id(), '_dokan_subscription_length', $subscription_length );

        return false;
    }
}
