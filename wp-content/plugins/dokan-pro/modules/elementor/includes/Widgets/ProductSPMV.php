<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use Elementor\Widget_Base;

class ProductSPMV extends Widget_Base {

    /**
     * Widget name
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-product-spmv';
    }

    /**
     * Widget title
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Dokan Single Product MultiVendor', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-flow';
    }

    /**
     * Widget categories
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_categories() {
        return [ 'woocommerce-elements-single' ];
    }

    /**
     * Widget keywords
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'product', 'vendor', 'spmv' ];
    }

    /**
     * Register HTML widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 3.3.0
     * @access protected
     */
    protected function register_controls() {
        parent::register_controls();

        $this->start_controls_section(
            'section_title',
            [
                'label' => __( 'Single Product MultiVendor', 'dokan' ),
            ]
        );

        $this->add_control(
            'text',
            [
                'label'       => __( 'Label', 'dokan' ),
                'default'     => __( 'Sell This Item', 'dokan' ),
                'placeholder' => __( 'Sell This Item', 'dokan' ),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Frontend render method
     *
     * @since 3.3.0
     *
     * @return void
     */
    protected function render() {
        if ( ! dokan_pro()->module->is_active( 'spmv' ) ) {
            return;
        }

        if ( ! is_singular( 'product' ) ) {
            return;
        }

        $enable_option = dokan_get_option( 'enable_pricing', 'dokan_spmv', 'off' );

        if ( 'off' === $enable_option && ! dokan_elementor()->is_edit_or_preview_mode() ) {
            return;
        }

        global $product;

        if ( $product->get_type() === 'product_pack' ) {
            return;
        }

        if ( ! $this->is_valid_user( $product->get_id() ) && ! dokan_elementor()->is_edit_or_preview_mode() ) {
            return;
        }

        $sell_item_btn_txt = $this->get_settings( 'text' );
        ?>
        <form method="post">
            <?php wp_nonce_field( 'dokan-sell-item-action', 'dokan-sell-item-nonce' ); ?>
            <button name="dokan_sell_this_item" class="dokan-btn dokan-btn-theme"><?php echo esc_html( $sell_item_btn_txt ); ?></button>
            <input type="hidden" name="product_id" value="<?php echo $product->get_id(); ?>">
            <input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>">
        </form>
        <?php
    }

    /**
     * Check is seller is elligible for sell this item
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_valid_user( $product_id ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user_id = get_current_user_id();

        if ( ! dokan_is_user_seller( $user_id ) ) {
            return false;
        }

        /** We are checking if vendor subscription is active,
         * if true, we are getting the subscription of the vendor
         * and checking if the vendor has remaining product based on active subscription
         **/
        if ( dokan_pro()->module->is_active( 'product_subscription' ) ) {
            if ( ! \DokanPro\Modules\Subscription\Helper::get_vendor_remaining_products( $user_id ) ) {
                return false;
            }
        }

        $product_author = get_post_field( 'post_author', $product_id );

        if ( $user_id === (int) $product_author ) {
            return false;
        }

        if ( $this->check_already_cloned( $product_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check already cloned this product
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function check_already_cloned( $product_id ) {
        global $wpdb;

        $map_id  = get_post_meta( $product_id, '_has_multi_vendor', true );
        $user_id = get_current_user_id();

        if ( empty( $map_id ) ) {
            return false;
        }

        $sql = $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}dokan_product_map` WHERE `map_id`= %d AND `seller_id` = %d AND `is_trash` IN (0,2,3)",
            $map_id,
            $user_id
        );
        $results = $wpdb->get_row( $sql ); //phpcs:ignore

        if ( $results ) {
            return true;
        }

        return false;
    }
}
