<?php

use DokanPro\Modules\Subscription\Helper;

/**
 * Admin related functions
 *
 * @package Dokan
 * @subpackage Subscription
 */
class DPS_Admin {

    public function __construct() {
        $this->response = '';

        add_action( 'init', array( $this, 'register_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'dokan-vue-admin-scripts', array( $this, 'vue_admin_enqueue_scripts' ) );

        // add product area in admin panel
        add_filter( 'product_type_selector', [ __CLASS__, 'add_product_type' ], 1 );
        add_action( 'woocommerce_product_options_general_product_data', [ __CLASS__, 'general_fields' ] );
        add_action( 'woocommerce_process_product_meta', [ __CLASS__, 'general_fields_save' ], 99 );

        add_action( 'dokan_admin_menu', [ __CLASS__, 'add_submenu_in_dokan_dashboard' ], 15 );
        add_filter( 'dokan-admin-routes', [ __CLASS__, 'vue_admin_routes' ] );

        // settings section
        add_filter( 'dokan_settings_sections', [ __CLASS__, 'add_new_section_admin_panael' ] );
        add_filter( 'dokan_settings_fields', [ __CLASS__, 'add_new_setting_field_admin_panael' ], 12, 1 );

        //add dropdown field with subscription packs
        add_action( 'dokan_seller_meta_fields', [ __CLASS__, 'add_subscription_packs_dropdown' ], 10, 1 );

        //save user meta
        add_action( 'dokan_process_seller_meta_fields', [ __CLASS__, 'save_meta_fields' ], 99 );

        // related orders metabox
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 99, 10, 2 );
        add_action( 'dokan_vendor_subscription_related_orders_meta_box_rows', [ $this, 'render_subscriptions_related_order' ], 10, 1 );

        // Add column that indicates whether an order is parent or renewal for a subscription
        add_filter( 'manage_edit-shop_order_columns', [ $this, 'add_contains_subscription_column' ], 8, 1 );
        add_action( 'manage_shop_order_posts_custom_column', [ $this, 'add_contains_subscription_column_content' ], 8, 1 );

        // remove sub-order class
        add_filter( 'post_class', [ $this, 'admin_shop_order_row_classes' ], 20, 1 );
        add_filter( 'dokan_manage_shop_order_custom_columns_order_number', [ $this, 'remove_suborder_notes' ], 10, 2 );
    }

    /**
     * Remove sub-order text from Order list items
     *
     * @param string $output
     * @param WC_Order $order
     *
     * @since 3.3.7
     *
     * @return string
     */
    public function remove_suborder_notes( $output, $order ) {
        if ( Helper::is_vendor_subscription_order( $order ) ) {
            return '';
        }
        return $output;
    }

    /**
     * Remove dokan css classes on admin shop order table
     *
     * @global WP_Post $post
     *
     * @param array $classes
     *
     * @since 3.3.7
     *
     * @return array
     */
    public function admin_shop_order_row_classes( $classes ) {
        global $post;

        if ( $post->post_type === 'shop_order' && $post->post_parent !== 0 && Helper::is_vendor_subscription_order( $post->ID ) ) {
            $class = 'sub-order parent-' . $post->post_parent;
            $item_index = array_search( $class, $classes, true );
            if ( false !== $item_index ) {
                unset( $classes[ $item_index ] );
            }
        }

        return $classes;
    }

    /**
     * Add a column to the WooCommerce -> Orders admin screen to indicate whether an order is a
     * parent of a subscription, a renewal order for a subscription, or a regular order.
     *
     * @param array $columns The current list of columns
     *
     * @since 3.3.7
     *
     * @return array
     */
    public function add_contains_subscription_column( $columns ) {
        if ( class_exists( 'WC_Subscriptions_Order' ) ) {
            return $columns;
        }

        $column_header = '<span class="subscription_head tips" data-tip="' . esc_attr__( 'Vendor Subscription Relationship', 'dokan' ) . '">' . esc_attr__( 'Subscription Relationship', 'dokan' ) . '</span>';

        $new_columns = Helper::array_insert_after( 'shipping_address', $columns, 'subscription_relationship', $column_header );

        return $new_columns;
    }

    /**
     * Add column content to the WooCommerce -> Orders admin screen to indicate whether an
     * order is a parent of a subscription, a renewal order for a subscription, or a
     * regular order.
     *
     * This method will reuse column added by wcs, if wcs is enabled we are handling values provided by
     * wcs by our end, we are also deresitering hooks of wcs
     *
     * @param string $column The string of the current column
     *
     * @since 3.3.7
     *
     * @return void
     */
    public static function add_contains_subscription_column_content( $column ) {
        global $post;

        // early return if not subscription_relationship column
        if ( 'subscription_relationship' !== $column ) {
            return;
        }

        if ( class_exists( 'WC_Subscriptions_Order' ) ) {
            // remove wc subscription hooks to render this column content
            remove_action( 'manage_shop_order_posts_custom_column', 'WC_Subscriptions_Order::add_contains_subscription_column_content', 10 );

            // populate wc subscription field data
            $output = '';
            if ( wcs_order_contains_subscription( $post->ID, 'renewal' ) ) {
                $output = '<span class="subscription_renewal_order tips" data-tip="' . esc_attr__( 'Renewal Order', 'woocommerce-subscriptions' ) . '"></span>'; //phpcs:ignore
            } elseif ( wcs_order_contains_subscription( $post->ID, 'resubscribe' ) ) {
                $output = '<span class="subscription_resubscribe_order tips" data-tip="' . esc_attr__( 'Resubscribe Order', 'woocommerce-subscriptions' ) . '"></span>'; //phpcs:ignore
            } elseif ( wcs_order_contains_subscription( $post->ID, 'parent' ) ) {
                $output = '<span class="subscription_parent_order tips" data-tip="' . esc_attr__( 'Parent Order', 'woocommerce-subscriptions' ) . '"></span>';  //phpcs:ignore
            }
            // early return if its a wc subscription order
            if ( ! empty( $output ) ) {
                echo $output;
                return;
            }
        }

        // get order
        $order = wc_get_order( $post->ID );
        // check if vendor subscription order
        if ( ! Helper::is_vendor_subscription_order( $order ) ) {
            echo '<span class="normal_order">&ndash;</span>';
            return;
        }

        // check if recurring order
        if ( 0 !== $order->get_parent_id() ) {
            // this is a recurring order
            echo '<span class="dokan_vs_renew_order tips" data-tip="' . esc_attr__( 'Vendor Subscription Renewal Order', 'dokan' ) . '"></span>';
            return;
        }

        // determine if order is recurring or non-recurring
        $product = Helper::get_vendor_subscription_product_by_order( $order );

        if ( ! $product ) {
            // maybe product has been deleted
            echo '<span class="normal_order">&ndash;</span>';
            return;
        }

        // check if recurring subscription order
        if ( Helper::is_recurring_pack( $product->get_id() ) ) {
            // this is a recurring pack
            echo '<span class="dokan_vs_recurring_order tips" data-tip="' . esc_attr__( 'Vendor Subscription Recurring Order', 'dokan' ) . '"></span>';
        } else {
            // this is a non-recurring pack
            echo '<span class="dokan_vs_non_recurring_order tips" data-tip="' . esc_attr__( 'Vendor Subscription Non Recurring Order', 'dokan' ) . '"></span>';
        }
    }

    /**
     * Add WC Meta boxes
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function add_meta_boxes( $post_type, $post ) {
        // Only display the meta box if an order relates to a subscription
        if ( 'shop_order' !== $post_type || ! Helper::is_vendor_subscription_order( $post->ID ) ) {
            return;
        }

        //remove woocommerce subscription metaox
        remove_meta_box( 'woocommerce-order-data', 'shop_subscription', 'normal' );

        // remove delivery time metabox
        remove_meta_box( 'dokan_delivery_time_fields', 'shop_order', 'side' );

        // add subscription metabox
        add_meta_box( 'dokan_vendor_subscription_renewal_orders', __( 'Vendor Subscriptions Related Orders', 'dokan' ), [ $this, 'subscription_metabox_content' ], 'shop_order', 'normal', 'high' );
    }

    /**
     * Render Subscription Metabox Content
     *
     * @param $post
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function subscription_metabox_content( $post ) {
        $order = wc_get_order( $post->ID );

        if ( ! $order ) {
            return;
        }

        dokan_get_template_part( 'admin/related-orders-table', '', [ 'is_subscription' => true, 'post' => $post ] );

        /**
         * @since 3.3.7
         * @args WC_Order $order
         * @args Post $post
         */
        do_action( 'dokan_vendor_subscription_related_orders_meta_box', $order, $post );
    }

    /**
     * Render Related Order Data
     *
     * @param $post
     *
     * @since 3.3.7
     *
     * @return void
     * @throws Exception
     */
    public function render_subscriptions_related_order( $post ) {
        $order = wc_get_order( $post->ID );

        if ( ! $order ) {
            dokan_get_template_part( 'admin/related-orders-empty-row', '', [ 'is_subscription' => true ] );
            return;
        }

        $parent_order      = null;
        $orders_to_display = [];

        // get parent order
        if ( $order->get_parent_id() === 0 ) {
            // this is the parent order
            $parent_order = $order;
        } else {
            $parent_order = wc_get_order( $order->get_parent_id() );
        }

        // check if order is main order or recurring order
        $args = [
            'parent'  => $parent_order->get_id(),
            'limit'   => -1,
            'orderby' => 'date',
            'order'   => 'DESC',
            'type'    => 'shop_order',
        ];

        $query = new WC_Order_Query( $args );
        $orders_to_display = $query->get_orders();

        // check if we got renewal orders
        if ( empty( $orders_to_display ) ) {
            dokan_get_template_part( 'admin/related-orders-empty-row', '', [ 'is_subscription' => true ] );
            return;
        }

        //include current order
        if ( $parent_order->get_id() !== $order->get_id() ) {
            $orders_to_display[] = $parent_order;
        }

        $orders_to_display = apply_filters( 'dokan_vendor_subscription_admin_related_orders_to_display', $orders_to_display, $parent_order, $post );

        foreach ( $orders_to_display as $order ) {
            // Skip the order being viewed.
            if ( $order->get_id() === (int) $post->ID ) {
                continue;
            }

            dokan_get_template_part( 'admin/related-orders-row', '', [ 'is_subscription' => true, 'order' => $order ] );
        }
    }

    /**
     * Register Scripts
     *
     * @since 3.7.4
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_style( 'dps-custom-style', DPS_URL . '/assets/css/style' . $suffix . '.css', false, $version );
        wp_register_script( 'dps-custom-admin-js', DPS_URL . '/assets/js/admin-script' . $suffix . '.js', array( 'jquery' ), $version, true );
        wp_register_style( 'dps-subscription', DPS_URL . '/assets/css/subscription' . $suffix . '.css', [], $version );
        wp_register_script( 'dps-subscription', DPS_URL . '/assets/js/subscription' . $suffix . '.js', array( 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ), $version, true );
    }

    public function admin_enqueue_scripts() {
        // Get admin screen id
        $screen = get_current_screen();

        wp_enqueue_style( 'dps-custom-style' );
        wp_enqueue_script( 'dps-custom-admin-js' );

        wp_localize_script(
            'dps-custom-admin-js', 'dokanSubscription', array(
                'ajaxurl'             => admin_url( 'admin-ajax.php' ),
                'subscriptionLengths' => Helper::get_subscription_ranges(),
            )
        );


        if ( 'shop_order' === $screen->post_type ) {
            add_action( 'admin_head', [ $this, 'load_admin_order_page_css' ], 10 );
        }
    }

    public function vue_admin_enqueue_scripts() {
        wp_enqueue_style( 'dps-subscription' );
        wp_enqueue_script( 'dps-subscription' );
    }

    /**
     * WooCommerce Orders admin table css for vendor subscription relation
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function load_admin_order_page_css() {
        ?>
        <style>
            table.wp-list-table .column-subscription_relationship {
                width: 48px;
                text-align: center;
            }

            table.wp-list-table span.normal_order {
                color: #999;
            }

            table.wp-list-table .subscription_head:after,
            table.wp-list-table .dokan_vs_recurring_order:after,
            table.wp-list-table .dokan_vs_non_recurring_order:after,
            table.wp-list-table .dokan_vs_renew_order:after
            {
                font-weight: 400;
                margin: 0;
                text-indent: 0;
                position: absolute;
                width: 100%;
                height: 100%;
                text-align: center;
                line-height: 16px;
                top: 0;
                speak: none;
                font-variant: normal;
                text-transform: none;
                -webkit-font-smoothing: antialiased;
                left: 0;
            }

            table.wp-list-table .subscription_head,
            table.wp-list-table .dokan_vs_recurring_order,
            table.wp-list-table .dokan_vs_non_recurring_order,
            table.wp-list-table .dokan_vs_renew_order {
                display: block;
                text-indent: -9999px;
                position: relative;
                height: 1em;
                margin: 0 auto;
            }

            table.wp-list-table .subscription_head {
                width: 1em;
            }

            table.wp-list-table .subscription_head:after {
                font-family: WooCommerce;
                content: "\e014";
            }

            table.wp-list-table .dokan_vs_recurring_order:after {
                font-family: Dashicons;
                font-size: 20px;
                line-height: 20px;
                content: "\f113";
                color: #3ba0aa;
            }

            table.wp-list-table .dokan_vs_non_recurring_order:after {
                font-family: Dashicons;
                font-size: 20px;
                line-height: 20px;
                content: "\f469";
                color: #8a9da8;
            }

            table.wp-list-table .dokan_vs_renew_order:after {
                font-family: Dashicons;
                font-size: 20px;
                line-height: 20px;
                content: "\f321";
                color: #62baf4;
            }

            table.wp-list-table .subscription_parent_order,
            table.wp-list-table .subscription_resubscribe_order,
            table.wp-list-table .subscription_renewal_order {
                font-size: 18px;
            }

            @media only screen and (max-width: 782px) {
                table.wp-list-table .dokan_vs_renew_order,
                table.wp-list-table .dokan_vs_non_recurring_order,
                table.wp-list-table .dokan_vs_recurring_order {
                    margin: 0;
                }
                table.wp-list-table .column-subscription_relationship {
                    text-align: inherit;
                }
            }
        </style>
        <?php
    }

    /**
     * Add woocommerce extra product type
     *
     * @param array $types
     * @param array $product_type
     *
     * @return array
     */
    public static function add_product_type( $types ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return $types;
        }

        $types['product_pack'] = __( 'Dokan Subscription', 'dokan' );

        return $types;
    }

    /**
     * Add extra custom field in woocommerce product type
     */
    public static function general_fields() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        global $woocommerce, $post;

        echo '<div class="options_group show_if_product_pack">';

        woocommerce_wp_text_input(
            array(
                'id'                => '_no_of_product',
                'label'             => __( 'Number of Products', 'dokan' ),
                'placeholder'       => __( 'Put -1 for unlimited products', 'dokan' ),
                'description'       => __( 'Enter the no of product you want to give this package.', 'dokan' ),
                'type'              => 'number',
                'custom_attributes' => array(
                    'step' => 'any',
                    'min'  => '-1',
                ),
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'                => '_pack_validity',
                'label'             => __( 'Pack Validity', 'dokan' ),
                'placeholder'       => 'Put 0 for unlimited days',
                'description'       => __( 'Enter no of validity days you want to give this pack ', 'dokan' ),
                'type'              => 'number',
                'custom_attributes' => array(
                    'step' => 'any',
                    'min'  => '0',
                ),
            )
        );

        do_action( 'dps_subscription_product_fields_after_pack_validity' );

        woocommerce_wp_select(
            array(
                'id'            => '_subscription_product_admin_commission_type',
                'label'         => __( 'Admin Commission type', 'dokan' ),
                'options'       => dokan_commission_types(),
                'description'   => __( 'Set the commission type admin will get under this subscription', 'dokan' ),
                'data_type'     => 'price',
            )
        );

        $product               = wc_get_product( get_the_ID() );
        $admin_commission_type = $product->get_meta( '_subscription_product_admin_commission_type' );
        $admin_commission      = $product->get_meta( '_subscription_product_admin_commission' );
        $additional_fee        = $product->get_meta( '_subscription_product_admin_additional_fee' );
        $admin_commission      = 'flat' === $admin_commission_type ? wc_format_localized_price( $admin_commission ) : wc_format_localized_decimal( $admin_commission );
        ?>

        <div class="commission show_if_product_pack">
            <p class="form-field dimensions_field">
                <label for="admin_commission"><?php esc_html_e( 'Admin Commission', 'dokan' ); ?></label>
                <span class="wrapper">
                    <input id="admin_commission" class="input-text wc_input_price" type="text" name="_subscription_product_admin_commission" value="<?php echo esc_attr( $admin_commission ); ?>">
                    <span class="subscription_additional_fee">
                        <?php echo esc_html( '% &nbsp;&nbsp; +' ); ?>
                        <input class="input-text wc_input_price" type="text" name="_subscription_product_admin_additional_fee" value="<?php echo wc_format_localized_price( $additional_fee ); ?>">
                    </span>
                    <span class="combine-commission-description"></span>
                </span>
            </p>
        </div>

        <style type="text/css">
            .dokan-hide {
                display: none;
            }
            .commission .wrapper input {
                width: 60px;
                float: none;
            }
            span.combine-commission-description {
                margin-left: 5px;
                font-size: 13px;
                font-style: italic;
            }
        </style>

        <script type="text/javascript">
            ;(function($) {
                $(document).ready(function(){
                    $('#_subscription_product_admin_commission_type').on('change', function() {
                        var self = $(this),
                            val = self.val();

                        if ( 'combine' === val ) {
                            $('.subscription_additional_fee').removeClass('dokan-hide');
                            $('.combine-commission-description').text( dokan_admin.combine_commission_desc );
                        } else {
                            $('.subscription_additional_fee').addClass('dokan-hide');
                            $('.combine-commission-description').text( dokan_admin.default_commission_desc );
                        }

                        if ( 'flat' == val ) {
                            $('input[name="_subscription_product_admin_commission"]').removeClass( 'wc_input_decimal' ).addClass( 'wc_input_price' );
                        } else {
                            $('input[name="_subscription_product_admin_commission"]').removeClass( 'wc_input_price' ).addClass( 'wc_input_decimal' );
                        }
                    }).trigger('change');

                    $('#_regular_price').on('keyup', function() {
                        var self = $(this),
                            val = self.val();

                        if ( $( 'input[name*="_regular_price"]' ).length > 1 ) {
                            $( 'input[name*="_regular_price"]' ).val( val );
                        }
                    }).trigger('change');
                });
            })(jQuery);
        </script>

        <?php
        // vendor allowed product types
        echo '<p class="form-field dokan_subscription_allowed_product_types">';
        echo '<label for="dokan_subscription_allowed_product_types">' . __( 'Allowed Product Types', 'dokan' ) . '</label>';
        echo '<select multiple="multiple" data-placeholder=" ' . __( 'Any product types', 'dokan' ) . '" class="wc-enhanced-select" id="_vendor_allowed_product_type" name="dokan_subscription_allowed_product_types[]" style="width: 350px;">';
        Helper::get_product_types_options();
        echo '</select>';
        echo '<span class="description">' . __( 'Select product type for this package. Leave empty to allow any product type.', 'dokan' ) . '</span>';
        echo '</p>';

        // vendor allowed categories
        echo '<p class="form-field _vendor_allowed_categories">';
        $selected_cat = get_post_meta( $post->ID, '_vendor_allowed_categories', true );
        echo '<label for="_vendor_allowed_categories">' . __( 'Allowed categories', 'dokan' ) . '</label>';
        echo '<select multiple="multiple" data-placeholder=" ' . __( 'Any categories', 'dokan' ) . '" class="wc-enhanced-select" id="_vendor_allowed_categories" name="_vendor_allowed_categories[]" style="width: 350px;">';
        $r = array();
        $r['pad_counts']    = 1;
        $r['hierarchical']  = 1;
        $r['hide_empty']    = 0;
        $r['value']         = 'id';
        $r['selected']      = ! empty( $selected_cat ) ? array_map( 'absint', $selected_cat ) : '';
        $r['orderby']       = 'name';

        $categories = get_terms( 'product_cat', $r );
        include_once WC()->plugin_path() . '/includes/walkers/class-product-cat-dropdown-walker.php';

        echo wc_walk_category_dropdown_tree( $categories, 0, $r );
        echo '</select>';
        echo '<span class="description">' . __( 'Select specific product category for this package. Leave empty to select all categories.', 'dokan' ) . '</span>';

        echo '</p>';

        woocommerce_wp_checkbox(
            array(
                'id'          => '_enable_gallery_restriction',
                'label'       => __( 'Restrict Gallery Image Upload', 'dokan' ),
                'description' => __( 'Please check this if you want to restrict gallery image uploading.', 'dokan' ),
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'                => '_gallery_image_restriction_count',
                'label'             => __( 'Maximum Image', 'dokan' ),
                'placeholder'       => 'Put -1 for unlimited image',
                'description'       => __( 'Max Image vendor can upload', 'dokan' ),
                'type'              => 'number',
                'custom_attributes' => array(
                    'step' => 'any',
                    'min'  => '-1',
                ),
            )
        );

        ?>

        <script type="text/javascript">
            ;(function () {
                var image_enable = document.querySelector('#_enable_gallery_restriction');
                var image_count = document.querySelector('._gallery_image_restriction_count_field');
                if (image_enable.checked === true) {
                    image_count.style.display = '';
                } else {
                    image_count.style.display = 'none';
                }
                image_enable.addEventListener('click', function () {
                    if (image_enable.checked === true) {
                        image_count.style.display = '';
                    } else {
                        image_count.style.display = 'none';
                    }
                })
            })();
        </script>

        <?php

        woocommerce_wp_checkbox(
            array(
                'id'          => '_enable_recurring_payment',
                'label'       => __( 'Recurring Payment', 'dokan' ),
                'description' => __( 'Please check this if you want to enable recurring payment system', 'dokan' ),
            )
        );
        echo '</div>';

        // Set month as the default billing period
        if ( ! $subscription_period = get_post_meta( $post->ID, '_dokan_subscription_period', true ) ) { // phpcs:ignore
            $subscription_period = 'day';
        }

        echo '<div class="options_group show_if_product_pack dokan_subscription_pricing">';
        // Subscription Period Interval
        echo '<div class="dokan-billing-cycle-wrap">';
        woocommerce_wp_select(
            array(
                'id'      => '_dokan_subscription_period_interval',
                'class'   => 'wc_input_subscription_period_interval',
                'label'   => __( 'Billing cycle', 'dokan' ),
                'options' => Helper::get_subscription_period_interval_strings(),
            )
        );

        // Billing Period
        woocommerce_wp_select(
            array(
                'id'          => '_dokan_subscription_period',
                'class'       => 'wc_input_subscription_period',
                'label'       => '',
                'options'     => Helper::get_subscription_period_strings(),
            )
        );

        echo '</div>';

        echo '<div class="dokan-billing-cyle-clear"></div>';

        // Subscription Length
        woocommerce_wp_select(
            array(
                'id'          => '_dokan_subscription_length',
                'class'       => 'wc_input_subscription_length',
                'label'       => __( 'Billing cycle stop', 'dokan' ),
                'options'     => Helper::get_subscription_ranges( $subscription_period ),

            )
        );

        woocommerce_wp_checkbox(
            array(
                'id'          => 'dokan_subscription_enable_trial',
                'label'       => __( 'Enable Trial', 'dokan' ),
                'description' => __( 'Please check this if you want to allow trial subscirption.', 'dokan' ),
            )
        );

        echo '<p class="form-field dokan_subscription_trial_period">';
        echo '<label for="dokan_subscription_trial_period">' . __( 'Trial Period', 'dokan' ) . '</label>';

        Helper::get_trial_period_options();

        echo '<span class="description">' . __( 'Define the trial period', 'dokan' ) . '</span>';
        echo '</p>';
        echo '</div>';

        wp_nonce_field( 'dps_product_fields_nonce', 'dps_product_pack' );

        do_action( 'dps_subscription_product_fields' );
    }


    /**
     * Manupulate custom filed meta data in post meta
     *
     * @param integer $post_id
     */
    public static function general_fields_save( $post_id ) {
        if ( ! isset( $_POST['dps_product_pack'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dps_product_pack'] ) ), 'dps_product_fields_nonce' ) ) {
            return;
        }

        if ( ! isset( $_POST['product-type'] ) || sanitize_text_field( wp_unslash( $_POST['product-type'] ) ) !== 'product_pack' ) {
            return;
        }

        update_post_meta( $post_id, '_virtual', 'yes' );
        update_post_meta( $post_id, '_sold_individually', 'yes' );

        // WC 3.0+ compatibility
        $visibility_term = array( 'exclude-from-search', 'exclude-from-catalog' );
        wp_set_post_terms( $post_id, $visibility_term, 'product_visibility', false );
        update_post_meta( $post_id, '_visibility', 'hidden' );

        $woocommerce_no_of_product_field = isset( $_POST['_no_of_product'] ) ? intval( wp_unslash( $_POST['_no_of_product'] ) ) : '';
        if ( $woocommerce_no_of_product_field !== '' ) {
            update_post_meta( $post_id, '_no_of_product', $woocommerce_no_of_product_field );
        }

        $woocommerce_pack_validity_field = isset( $_POST['_pack_validity'] ) ? intval( wp_unslash( $_POST['_pack_validity'] ) ) : '';
        if ( $woocommerce_pack_validity_field !== '' ) {
            update_post_meta( $post_id, '_pack_validity', $woocommerce_pack_validity_field );
        }

        if ( ! empty( $_POST['_subscription_product_admin_commission_type'] ) ) {
            update_post_meta( $post_id, '_subscription_product_admin_commission_type', sanitize_text_field( wp_unslash( $_POST['_subscription_product_admin_commission_type'] ) ) );
        }

        if ( isset( $_POST['_subscription_product_admin_commission'] ) ) {
            update_post_meta( $post_id, '_subscription_product_admin_commission', wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['_subscription_product_admin_commission'] ) ) ) );
        }

        if ( isset( $_POST['_subscription_product_admin_additional_fee'] ) ) {
            update_post_meta( $post_id, '_subscription_product_admin_additional_fee', wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['_subscription_product_admin_additional_fee'] ) ) ) );
        }

        if ( ! empty( $_POST['dokan_subscription_allowed_product_types'] ) ) {
            update_post_meta( $post_id, 'dokan_subscription_allowed_product_types', wc_clean( wp_unslash( $_POST['dokan_subscription_allowed_product_types'] ) ) );
        } else {
            delete_post_meta( $post_id, 'dokan_subscription_allowed_product_types' );
        }

        if ( ! empty( $_POST['_vendor_allowed_categories'] ) ) {
            update_post_meta( $post_id, '_vendor_allowed_categories', wc_clean( wp_unslash( $_POST['_vendor_allowed_categories'] ) ) );
        } else {
            delete_post_meta( $post_id, '_vendor_allowed_categories' );
        }

        $woocommerce_enable_gallery_restriction = isset( $_POST['_enable_gallery_restriction'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_enable_gallery_restriction', wc_clean( $woocommerce_enable_gallery_restriction ) );

        $gallery_image_restriction_count = isset( $_POST['_gallery_image_restriction_count'] ) && intval( $_POST['_gallery_image_restriction_count'] ) >= 0 ? intval( wp_unslash( $_POST['_gallery_image_restriction_count'] ) ) : -1;
        if ( $woocommerce_enable_gallery_restriction === 'yes' ) {
            update_post_meta( $post_id, '_gallery_image_restriction_count', $gallery_image_restriction_count );
        } elseif ( $woocommerce_enable_gallery_restriction === 'no' ) {
            delete_post_meta( $post_id, '_gallery_image_restriction_count' );
        }

        $dokan_subscription_enable_trial = isset( $_POST['dokan_subscription_enable_trial'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, 'dokan_subscription_enable_trial', $dokan_subscription_enable_trial );

        $dokan_subscription_trail_range = isset( $_POST['dokan_subscription_trail_range'] ) ? intval( wp_unslash( $_POST['dokan_subscription_trail_range'] ) ) : 1;
        if ( ! empty( $dokan_subscription_trail_range ) ) {
            update_post_meta( $post_id, 'dokan_subscription_trail_range', $dokan_subscription_trail_range );
        }

        $dokan_subscription_trial_period_types = isset( $_POST['dokan_subscription_trial_period_types'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_subscription_trial_period_types'] ) ) : 'days';
        if ( ! empty( $dokan_subscription_trial_period_types ) ) {
            update_post_meta( $post_id, 'dokan_subscription_trial_period_types', $dokan_subscription_trial_period_types );
        }

        $woocommerce_enable_recurring_field = isset( $_POST['_enable_recurring_payment'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_enable_recurring_payment', $woocommerce_enable_recurring_field );

        $woocommerce_subscription_period_interval_field = isset( $_POST['_dokan_subscription_period_interval'] ) ? intval( wp_unslash( $_POST['_dokan_subscription_period_interval'] ) ) : '';
        if ( $woocommerce_enable_recurring_field !== '' ) {
            update_post_meta( $post_id, '_dokan_subscription_period_interval', $woocommerce_subscription_period_interval_field );
        }

        $woocommerce_subscription_period_field = isset( $_POST['_dokan_subscription_period'] ) ? sanitize_text_field( wp_unslash( $_POST['_dokan_subscription_period'] ) ) : '';
        if ( $woocommerce_enable_recurring_field !== '' ) {
            update_post_meta( $post_id, '_dokan_subscription_period', $woocommerce_subscription_period_field );
        }

        $woocommerce_subscription_length_field = isset( $_POST['_dokan_subscription_length'] ) ? intval( wp_unslash( $_POST['_dokan_subscription_length'] ) ) : 0;
        update_post_meta( $post_id, '_dokan_subscription_length', $woocommerce_subscription_length_field );

        do_action( 'dps_process_subcription_product_meta', $post_id );
    }


    /**
     * Add new Section in admin dokan settings
     *
     * @param array $sections
     *
     * @return array
     */
    public static function add_new_section_admin_panael( $sections ) {
        $sections['dokan_product_subscription'] = [
            'id'                   => 'dokan_product_subscription',
            'title'                => __( 'Vendor Subscription', 'dokan' ),
            'icon_url'             => DPS_URL . '/assets/images/subscription.svg',
            'description'          => __( 'Manage Subscription Plans', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/modules/how-to-install-use-dokan-subscription/',
            'settings_title'       => __( 'Vendor Subscription Settings', 'dokan' ),
            'settings_description' => __( 'Configure marketplace settings to authorize vendors to create subscription products for their stores.', 'dokan' ),
        ];

        return $sections;
    }

    /**
     * Get all Pages
     *
     * @param string  $post_type
     * @return array
     */
    public static function get_post_type( $post_type ) {
        $pages_array = array( '-1' => __( '- select -', 'dokan' ) );
        $pages = get_posts(
            array(
                'post_type' => $post_type,
                'numberposts' => -1,
            )
        );

        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_array[ $page->ID ] = $page->post_title;
            }
        }

        return $pages_array;
    }

    /**
     * Add new Settings field in admin dashboard for selection product
     * subscription page
     *
     * @param array   $settings_fields
     * @return array
     */
    public static function add_new_setting_field_admin_panael( $settings_fields ) {
        $pages_array = self::get_post_type( 'page' );

        $settings_fields['dokan_product_subscription'] = array(
            'subscription_pack' => array(
                'name'    => 'subscription_pack',
                'label'   => __( 'Subscription', 'dokan' ),
                'type'    => 'select',
                'options' => $pages_array,
                'tooltip' => __( 'Select the page in which you want to show subscription packages.', 'dokan' ),
            ),
            'enable_pricing' => array(
                'name'  => 'enable_pricing',
                'label' => __( 'Enable Vendor Subscription', 'dokan' ),
                'desc'  => __( 'Enable subscription for vendor', 'dokan' ),
                'type'  => 'switcher',
            ),
            'enable_subscription_pack_in_reg' => [
                'name'    => 'enable_subscription_pack_in_reg',
                'label'   => __( 'Enable Subscription in Registration Form', 'dokan' ),
                'desc'    => __( 'Enable subscription pack in registration form for new vendor', 'dokan' ),
                'type'    => 'switcher',
                'default' => 'on',
                'tooltip' => __( 'If checked, vendor completes registration only after subscribing to a pack', 'dokan' ),
            ],
            'notify_by_email' => array(
                'name'  => 'notify_by_email',
                'label' => __( 'Enable Email Notification', 'dokan' ),
                'desc'  => __( 'Enable notification by email for vendor during end of the package expiration', 'dokan' ),
                'type'  => 'switcher',
            ),
            'no_of_days_before_mail' => array(
                'name'    => 'no_of_days_before_mail',
                'label'   => __( 'No. of Days', 'dokan' ),
                'desc'    => __( 'Before an email will be sent to the vendor', 'dokan' ),
                'type'    => 'text',
                'size'    => 'midium',
                'default' => '2',
            ),
            'product_status_after_end' => array(
                'name'    => 'product_status_after_end',
                'label'   => __( 'Product Status', 'dokan' ),
                'desc'    => __( 'Product status when vendor pack validity will expire', 'dokan' ),
                'type'    => 'select',
                'default' => 'draft',
                'options' => array(
                    'publish' => __( 'Published', 'dokan' ),
                    'pending' => __( 'Pending Review', 'dokan' ),
                    'draft'   => __( 'Draft', 'dokan' ),
                ),
            ),
            'cancelling_email_subject' => array(
                'name'    => 'cancelling_email_subject',
                'label'   => __( 'Cancelling Email Subject', 'dokan' ),
                'desc'    => __( 'Enter subject text for canceled subscriptions email notification', 'dokan' ),
                'type'    => 'textarea',
                'rows'    => 3,
                'default' => __( 'Subscription Package Cancel notification', 'dokan' ),
            ),
            'cancelling_email_body' => array(
                'name'  => 'cancelling_email_body',
                'label' => __( 'Cancelling Email Body', 'dokan' ),
                'desc'  => __( 'Enter body text for canceled subscriptions email notification', 'dokan' ),
                'type'  => 'textarea',
                'rows'  => 4,
                'default' => __( 'Dear subscriber, Your subscription has expired. Please renew your package to continue using it.', 'dokan' ),
            ),
            'alert_email_subject' => array(
                'name'    => 'alert_email_subject',
                'label'   => __( 'Alert Email Subject', 'dokan' ),
                'desc'    => __( 'Enter subject text for package end notification alert email', 'dokan' ),
                'type'    => 'textarea',
                'rows'    => 3,
                'default' => __( 'Subscription Ending Soon', 'dokan' ),
            ),
            'alert_email_body' => [
                'name'    => 'alert_email_body',
                'label'   => __( 'Alert Email body', 'dokan' ),
                'desc'    => __( 'Enter body text for package end notification alert email', 'dokan' ),
                'type'    => 'textarea',
                'rows'    => 4,
                'default' => __( 'Dear subscriber, Your subscription will be ending soon. Please renew your package in a timely manner for continued usage.', 'dokan' ),
            ],
        );

        if ( dokan_pro()->module->product_subscription->is_dokan_plugin() ) {
            unset( $settings_fields['dokan_product_subscription'][0] );
        }

        return $settings_fields;
    }

    /**
     * Add submenu page in dokan Dashboard
     */
    public static function add_submenu_in_dokan_dashboard( $capability ) {
        if ( ! Helper::is_subscription_module_enabled() ) {
            return;
        }

        global $submenu;

        $slug = 'dokan';

        if ( current_user_can( 'manage_options' ) ) {
            $submenu[ $slug ][] = array( __( 'Subscriptions', 'dokan' ), $capability, 'admin.php?page=' . $slug . '#/subscriptions' );
        }
    }

    /**
     * Add subscripton route
     *
     * @param  array $routes
     *
     * @return array
     */
    public static function vue_admin_routes( $routes ) {
        $routes[] = [
            'path'      => '/subscriptions',
            'name'      => 'Subscriptions',
            'component' => 'Subscriptions',
        ];

        return $routes;
    }

    /**
     * Add subscription packs in drowpdown to let admin select a pack for the seller
     */
    public static function add_subscription_packs_dropdown( $user ) {
        $users_assigned_pack       = get_user_meta( $user->ID, 'product_package_id', true );
        $vendor_allowed_categories = get_user_meta( $user->ID, 'vendor_allowed_categories', true );

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'product_pack',
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => '_enable_recurring_payment',
                    'value' => 'no',
                ),
            ),
        );
        $sub_packs = get_posts( apply_filters( 'dps_get_non_recurring_pack_arg', $args ) );
        ?>
        <tr>
            <td>
                <h3><?php esc_html_e( 'Dokan Subscription', 'dokan' ); ?> </h3>
            </td>
        </tr>

        <?php if ( $users_assigned_pack ) : ?>
            <tr>
                <td><?php esc_html_e( 'Currently Activated Pack', 'dokan' ); ?></td>
                <td> <?php echo get_the_title( $users_assigned_pack ); ?> </td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Start Date :' ); ?></td>
                <td><?php echo dokan_format_date( get_user_meta( $user->ID, 'product_pack_startdate', true ) ); ?>
                </td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'End Date :' ); ?></td>
                <td>
                    <?php
                    $product_pack_enddate = get_user_meta( $user->ID, 'product_pack_enddate', true );
                    if ( 'unlimited' === $product_pack_enddate ) {
                        printf( __( 'Lifetime package.', 'dokan' ) );
                    } else {
                        echo dokan_format_date( $product_pack_enddate );
                    }
                    ?>
                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <?php if ( $users_assigned_pack && get_user_meta( $user->ID, '_customer_recurring_subscription', true ) === 'active' ) : ?>
                <td colspan="2"><em><?php esc_html_e( 'This user already has recurring pack assigned. Are you sure to assign a new normal pack to the user? If you do so, the existing recurring plan will be replaced with the new one', 'dokan' ); ?></em></td>
            <?php endif; ?>
        </tr>

        <tr>
            <td><?php esc_html_e( 'Allowed categories', 'dokan' ); ?></td>
            <td>
                <?php
                $selected_cat = ! empty( $vendor_allowed_categories ) ? $vendor_allowed_categories : get_post_meta( $users_assigned_pack, '_vendor_allowed_categories', true );
                echo '<select multiple="multiple" data-placeholder=" ' . __( 'Select categories&hellip;', 'dokan' ) . '" class="wc-enhanced-select" id="vendor_allowed_categories" name="vendor_allowed_categories[]" style="width: 350px;">';
                $r = array();
                $r['pad_counts']    = 1;
                $r['hierarchical']  = 1;
                $r['hide_empty']    = 0;
                $r['value']         = 'id';
                $r['orderby']       = 'name';
                $r['selected']      = ! empty( $selected_cat ) ? array_map( 'absint', $selected_cat ) : '';
                $r['parent']        = 0;

                $categories = get_terms( 'product_cat', $r );

                include_once WC()->plugin_path() . '/includes/walkers/class-product-cat-dropdown-walker.php';

                echo wc_walk_category_dropdown_tree( $categories, 0, $r );
                echo '</select>';
                ?>
                <p class="description"><?php esc_html_e( 'You can override allowed categories for this user. If empty then the predefined category for this pack will be selected', 'dokan' ); ?></p>
            </td>
        </tr>

        <tr class="dps_assign_pack">
            <td><?php esc_html_e( 'Assign Subscription Pack', 'dokan' ); ?></td>
            <td>
                <select name="_dokan_user_assigned_sub_pack">
                    <option value="" <?php selected( $users_assigned_pack, '' ); ?>><?php esc_html_e( '-- Select a pack --', 'dokan' ); ?></option>
                    <?php foreach ( $sub_packs as $pack ) : ?>
                        <option value="<?php echo $pack->ID; ?>" <?php selected( $users_assigned_pack, $pack->ID ); ?>><?php echo $pack->post_title; ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e( 'You can only assign non-recurring packs', 'dokan' ); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save meta fields
     *
     * @param int $user_id
     *
     * @return void
     * @throws Exception
     */
    public static function save_meta_fields( $user_id ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( ! isset( $_POST['dokan_enable_selling'] ) ) {
            return;
        }

        if ( ! isset( $_POST['_dokan_user_assigned_sub_pack'] ) ) {
            return;
        }

        $pack_id = intval( $_POST['_dokan_user_assigned_sub_pack'] );

        if ( ! $pack_id || empty( $pack_id ) ) {
            return;
        }

        if ( ! empty( $_POST['vendor_allowed_categories'] ) ) {
            $allowed_cat = wc_clean( $_POST['vendor_allowed_categories'] );
            update_user_meta( $user_id, 'vendor_allowed_categories', $allowed_cat );
        } else {
            delete_user_meta( $user_id, 'vendor_allowed_categories' );
        }

        if ( get_user_meta( $user_id, 'product_package_id', true ) == $pack_id ) {
            return;
        }

        //cancel paypal if current pack is recurring
        if ( get_user_meta( $user_id, '_customer_recurring_subscription', true ) == 'active' ) {
            $order_id = get_user_meta( $user_id, 'product_order_id', true );

            Helper::log( 'Subscription cancel check: On assign pack by admin cancel Recurring Subscription of User #' . $user_id . ' on order #' . $order_id );

            //this hook will ensure other gateway payments cancels their subscriptions
            do_action( 'dps_cancel_recurring_subscription', $order_id, $user_id, true );

            do_action( 'dokan_subscription_cancelled_by_admin', $user_id, $order_id );
            $subscriber_id = get_user_meta( $user_id, '_paypal_subscriber_ID', true );

            if ( $order_id && ! empty( $subscriber_id ) ) {
                DPS_PayPal_Standard_Subscriptions::cancel_subscription_with_paypal( $order_id, $user_id );
            }
        }

        // create a order for the subscription
        try {
            $order = new WC_Order();
            $order->add_product( wc_get_product( $pack_id ) );
            $order->set_created_via( 'dokan' );
            $order->set_customer_id( $user_id );
            $order->calculate_totals();
            $order->set_status( 'completed' );
            $order->save();
        } catch ( Exception $e ) {
            return new WP_Error( 'dokan-order-error', $e->getMessage() );
        }

        $pack_validity           = get_post_meta( $pack_id, '_pack_validity', true );
        $admin_commission        = get_post_meta( $pack_id, '_subscription_product_admin_commission', true );
        $admin_additional_fee    = get_post_meta( $pack_id, '_subscription_product_admin_additional_fee', true );
        $admin_commission_type   = get_post_meta( $pack_id, '_subscription_product_admin_commission_type', true );

        update_user_meta( $user_id, 'product_package_id', $pack_id );
        update_user_meta( $user_id, 'product_order_id', $order->get_id() );
        update_user_meta( $user_id, 'product_no_with_pack', get_post_meta( $pack_id, '_no_of_product', true ) ); //number of products
        update_user_meta( $user_id, 'product_pack_startdate', dokan_current_datetime()->format( 'Y-m-d H:i:s' ) );

        if ( absint( $pack_validity ) > 0 ) {
            update_user_meta( $user_id, 'product_pack_enddate', dokan_current_datetime()->modify( "+$pack_validity days" )->format( 'Y-m-d H:i:s' ) );
        } else {
            update_user_meta( $user_id, 'product_pack_enddate', 'unlimited' );
        }

        update_user_meta( $user_id, 'can_post_product', 1 );
        update_user_meta( $user_id, '_customer_recurring_subscription', '' );

        if ( ! empty( $admin_commission ) && ! empty( $admin_commission_type ) ) {
            update_user_meta( $user_id, 'dokan_admin_percentage', $admin_commission );
            update_user_meta( $user_id, 'dokan_admin_percentage_type', $admin_commission_type );
        } else {
            update_user_meta( $user_id, 'dokan_admin_percentage', '' );
        }

        if ( ! empty( $admin_additional_fee ) && ! empty( $admin_commission_type ) ) {
            update_user_meta( $user_id, 'dokan_admin_additional_fee', $admin_additional_fee );
        } else {
            update_user_meta( $user_id, 'dokan_admin_additional_fee', '' );
        }

        do_action( 'dokan_vendor_purchased_subscription', $user_id );
    }
}

new DPS_Admin();
