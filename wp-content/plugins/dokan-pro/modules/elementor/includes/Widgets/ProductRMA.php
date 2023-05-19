<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use Elementor\Widget_Base;

class ProductRMA extends Widget_Base {

    /**
     * Widget name
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-product-rma';
    }

    /**
     * Widget title
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Dokan Return and Warranty Request', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-product-breadcrumbs';
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
        return [ 'dokan', 'product', 'vendor', 'rma' ];
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
                'label' => __( 'Return and Warranty Request', 'dokan' ),
            ]
        );

        $this->add_control(
            'text',
            [
                'label'       => __( 'Label', 'dokan' ),
                'default'     => __( 'Warranty', 'dokan' ),
                'placeholder' => __( 'Warranty', 'dokan' ),
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
        if ( ! dokan_pro()->module->is_active( 'rma' ) ) {
            return;
        }

        if ( ! is_singular( 'product' ) ) {
            return;
        }

        global $post, $product;

        if ( $product->is_type( 'external' ) ) {
            return;
        }

        $product_id     = $product->get_id();
        $warranty       = $this->get_rma_settings( $product_id );
        $warranty_label = sanitize_text_field( $this->get_settings( 'text' ) );

        if ( $warranty['type'] === 'included_warranty' ) {
            if ( $warranty['length'] === 'limited' ) {
                $value      = $warranty['length_value'];
                $duration   = dokan_rma_get_duration_value( $warranty['length_duration'], $value );

                echo '<p class="warranty_info"><b>' . $warranty_label . ':</b> ' . $value . ' ' . $duration . '</p>';
            } else {
                echo '<p class="warranty_info"><b>' . $warranty_label . ':</b> ' . __( 'Lifetime', 'dokan' ) . '</p>';
            }
        } elseif ( $warranty['type'] === 'addon_warranty' ) {
            $addons = $warranty['addon_settings'];

            if ( is_array( $addons ) && ! empty( $addons ) ) {
                echo '<p class="warranty_info"><b>' . $warranty_label . '</b> <select name="dokan_warranty">';
                echo '<option value="-1">' . __( 'No warranty', 'dokan' ) . '</option>';

                foreach ( $addons as $x => $addon ) {
                    $amount     = $addon['price'];
                    $value      = $addon['length'];
                    $duration   = dokan_rma_get_duration_value( $addon['duration'], $value );

                    if ( (int) $value === 0 && (int) $amount === 0 ) {
                        // no warranty option
                        echo '<option value="-1">' . __( 'No warranty', 'dokan' ) . '</option>';
                    } else {
                        if ( $amount === 0 ) {
                            $amount = __( 'Free', 'dokan' );
                        } else {
                            $amount = wc_price( $amount );
                        }
                        echo '<option value="' . $x . '">' . $value . ' ' . $duration . ' &mdash; ' . $amount . '</option>';
                    }
                }

                echo '</select></p>';
            }
        } elseif ( dokan_elementor()->is_edit_or_preview_mode() ) {
            echo '<p class="warranty_info"><b>' . $warranty_label . ':</b> ' . __( 'Lifetime', 'dokan' ) . '</p>';
        }
    }

    /**
     * Get setting value
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_rma_settings( $product_id = 0 ) {
        $rma_settings   = [];
        $admin_settings = dokan_get_option( 'rma_policy', 'dokan_rma', '' );
        $default        = [
            'from'            => 'store',
            'label'           => __( 'Warranty', 'dokan' ),
            'type'            => 'no_warranty',
            'policy'          => $admin_settings,
            'reasons'         => [],
            'length'          => '',
            'length_value'    => '',
            'length_duration' => '',
            'addon_settings'  => [],
        ];

        if ( $product_id ) {

            /**
             * Has product ID and get product rma settings if have. If not set in product then
             * return those product store owner default settings
             */
            $override_default = get_post_meta( $product_id, '_dokan_rma_override_product', true );

            if ( 'yes' === $override_default ) {
                $rma_settings         = get_post_meta( $product_id, '_dokan_rma_settings', true );
                $rma_settings['from'] = 'product';
            } else {
                $seller_id    = get_post_field( 'post_author', $product_id );
                $rma_settings = get_user_meta( $seller_id, '_dokan_rma_settings', true );
                $rma_settings = dokan_parse_args( $rma_settings, $default );
            }
        } else {
            // return default store settings if user logged id as a seller
            $user_id = dokan_get_current_user_id();

            // Not found any user so return $default settings
            if ( ! $user_id ) {
                return $default;
            }

            $rma_settings         = get_user_meta( $user_id, '_dokan_rma_settings', true );
            $rma_settings         = dokan_parse_args( $rma_settings, $default );
            $rma_settings['from'] = 'store';
        }

        return $rma_settings;
    }
}
