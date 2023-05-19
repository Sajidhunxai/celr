<?php

namespace WeDevs\DokanPro\Shipping;

use WC_Order_Item_Product;
use WeDevs\Dokan\Cache;

/**
 * Shipping Status Class
 *
 * @package dokan
 */
class ShippingStatus {

    /**
     * Shipping status option
     *
     * @since 3.2.4
     */
    public $enabled;

    /**
     * Shipping status option
     *
     * @since 3.2.4
     */
    public $wc_shipping_enabled;

    /**
     * Shipping Status class construct
     *
     * @since 3.2.4
     */
    public function __construct() {
        $this->wc_shipping_enabled = get_option( 'woocommerce_calc_shipping' ) === 'yes' ? true : false;

        add_filter( 'dokan_settings_sections', [ $this, 'render_shipping_status_section' ] );
        add_filter( 'dokan_settings_fields', [ $this, 'render_shipping_status_settings' ] );

        $this->enabled = dokan_get_option( 'enabled', 'dokan_shipping_status_setting', 'off' );
        $this->add_default_shipping_status();

        $this->load_hooks();
    }

    /**
     * Load hooks for this shippping
     * tracking
     *
     * @since 3.2.4
     *
     * @return void
     */
    public function load_hooks() {
        if ( 'on' !== $this->enabled || ! $this->wc_shipping_enabled || 'sell_digital' === dokan_pro()->digital_product->get_selling_product_type() ) {
            return;
        }

        add_action( 'dokan_order_detail_after_order_items', [ $this, 'render_shipment_content' ], 15 );
        add_action( 'wp_ajax_dokan_add_shipping_status_tracking_info', [ $this, 'add_shipping_status_tracking_info' ] );
        add_action( 'wp_ajax_dokan_update_shipping_status_tracking_info', [ $this, 'update_shipping_status_tracking_info' ] );
        add_filter( 'manage_edit-shop_order_columns', [ $this, 'admin_shipping_status_tracking_columns' ], 10 );
        add_action( 'manage_shop_order_posts_custom_column', [ $this, 'shop_order_shipping_status_columns' ], 11 );
        add_action( 'woocommerce_order_details_after_order_table', [ $this, 'shipment_order_details_after_order_table' ], 11 );
        add_action( 'woocommerce_account_orders_columns', [ $this, 'shipment_my_account_my_orders_columns' ], 11 );
        add_action( 'woocommerce_my_account_my_orders_column_dokan-shipment-status', [ $this, 'shipment_my_account_orders_column_data' ], 11 );
        add_action( 'add_meta_boxes', [ $this, 'shipment_order_add_meta_boxes' ], 11 );
        add_filter( 'dokan_localized_args', [ $this, 'set_localized_data' ] );
    }

    /**
     * Add shipping status section in Dokan settings
     *
     * @since 3.2.4
     *
     * @param array $sections
     *
     * @return array
     */
    public function render_shipping_status_section( $sections ) {
        $sections[] = [
            'id'                   => 'dokan_shipping_status_setting',
            'title'                => __( 'Shipping Status', 'dokan' ),
            'icon_url'             => DOKAN_PRO_PLUGIN_ASSEST . '/images/admin-settings-icons/shipping.svg',
            'description'          => __( 'Manage Shipping Status', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/settings/dokan-shipping-status/',
            'settings_title'       => __( 'Shipping Status Settings', 'dokan' ),
            'settings_description' => __( 'You can configure settings to allow customers to track their products.', 'dokan' ),
        ];

        return $sections;
    }

    /**
     * Load all settings fields
     *
     * @since 3.2.4
     *
     * @return void
     */
    public function render_shipping_status_settings( $fields ) {
        $shipment_warning = array();
        $selling_type     = dokan_pro()->digital_product->get_selling_product_type();

        if ( 'sell_digital' === $selling_type ) {
            $shipment_warning['digital_warning'] = array(
                'name'  => 'digital_warning',
                'label' => __( 'Warning!', 'dokan' ),
                'type'  => 'warning',
                'desc'  => __( 'Your selling product type is Digital mode, shipping tracking system work with physical products only.', 'dokan' ),
            );
        }

        if ( ! $this->wc_shipping_enabled ) {
            $shipment_warning['wc_warning'] = array(
                'name'  => 'wc_warning',
                'label' => __( 'Warning!', 'dokan' ),
                'type'  => 'warning',
                'desc'  => __( 'Your WooCommerce shipping is currently disabled, therefore you first need to enable WC Shipping then it will work for vendors', 'dokan' ),
            );
        }

        $fields['dokan_shipping_status_setting'] = [
            'enabled' => [
                'name'  => 'enabled',
                'label' => __( 'Allow Shipment Tracking', 'dokan' ),
                'type'  => 'switcher',
                'desc'  => __( 'Allow shipment tracking service for vendors', 'dokan' ),
            ],
            'shipping_status_provider' => [
                'name'    => 'shipping_status_provider',
                'label'   => __( 'Shipping Providers', 'dokan' ),
                'desc'    => __( 'Select multiples shipping providers.', 'dokan' ),
                'type'    => 'multicheck',
                'default' => dokan_get_shipping_tracking_default_providers_list(),
                'options' => dokan_get_shipping_tracking_providers_list(),
                'tooltip' => __( 'Choose the 3rd party shipping providers.', 'dokan' ),
            ],
            'shipping_status_list' => [
                'name'  => 'shipping_status_list',
                'label' => __( 'Shipping Status', 'dokan' ),
                'type'  => 'repeatable',
                'desc'  => __( 'Add custom shipping status', 'dokan' ),
            ],
        ];

        $fields['dokan_shipping_status_setting'] = array_merge( $shipment_warning, $fields['dokan_shipping_status_setting'] );

        return $fields;
    }

    /**
     * Add default shipping status when get blank
     *
     * @since 3.2.4
     *
     * @return void
     */
    public function add_default_shipping_status() {
        $option = get_option( 'dokan_shipping_status_setting', [] );

        if ( empty( $option['shipping_status_list'] ) ) {
            $option['shipping_status_list'] = [
                [
                    'id'       => 'ss_delivered',
                    'value'    => esc_html__( 'Delivered', 'dokan' ),
                    'must_use' => true,
                    'desc'     => esc_html__( '(This is must use item)', 'dokan' ),
                ],
                [
                    'id'       => 'ss_cancelled',
                    'value'    => esc_html__( 'Cancelled', 'dokan' ),
                    'must_use' => true,
                    'desc'     => esc_html__( '(This is must use item)', 'dokan' ),
                ],
                [
                    'id'    => 'ss_proceccing',
                    'value' => esc_html__( 'Processing', 'dokan' ),
                ],
                [
                    'id'    => 'ss_ready_for_pickup',
                    'value' => esc_html__( 'Ready for pickup', 'dokan' ),
                ],
                [
                    'id'    => 'ss_pickedup',
                    'value' => esc_html__( 'Pickedup', 'dokan' ),
                ],
                [
                    'id'    => 'ss_on_the_way',
                    'value' => esc_html__( 'On the way', 'dokan' ),
                ],
            ];

            update_option( 'dokan_shipping_status_setting', $option, false );
        }
    }

    /**
     * Get shipping status main content
     *
     * @since 3.2.4
     *
     * @return void
     */
    public function render_shipment_content() {
        $line_items    = [];
        $shipment_info = [];
        $order_id      = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;

        if ( ! $order_id ) {
            return;
        }

        $_nonce              = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( $_REQUEST['_wpnonce'] ) : '';
        $default_providers   = dokan_get_shipping_tracking_providers_list();
        $selected_providers  = dokan_get_option( 'shipping_status_provider', 'dokan_shipping_status_setting' );
        $status_list         = dokan_get_option( 'shipping_status_list', 'dokan_shipping_status_setting' );
        $order               = dokan()->order->get( $order_id );
        $disabled_create_btn = false;

        if ( $order ) {
            $line_items    = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
            $shipment_info = dokan_pro()->shipment->get_shipping_tracking_info( $order_id );
            $is_shipped    = $this->is_order_shipped( $order );

            if ( $order->get_status() === 'cancelled' || $order->get_status() === 'refunded' ) {
                $disabled_create_btn = true;
            }
        }

        dokan_get_template_part(
            'orders/shipment/html-shipping-status', '', array(
                'pro'                 => true,
                'd_providers'         => $default_providers,
                's_providers'         => $selected_providers,
                'status_list'         => $status_list,
                'order_id'            => $order_id,
                'order'               => $order,
                'line_items'          => $line_items,
                'shipment_info'       => $shipment_info,
                'is_shipped'          => $is_shipped,
                'disabled_create_btn' => $disabled_create_btn,
            )
        );
    }

    /**
     * Get order shipment status
     *
     * @since 3.2.4
     *
     * @param obj $order
     *
     * @param boolean
     */
    public function is_order_shipped( $order = '' ) {
        if ( empty( $order ) ) {
            return;
        }

        $get_items       = $order->get_items( 'line_item' );
        $order_id        = $order->get_id();
        $items_available = [];

        foreach ( $get_items as $item_id => $item ) {
            if ( ! dokan_pro()->shipment->get_status_order_item_shipped( $order_id, $item_id, $item['qty'], 0 ) ) {
                $items_available[] = $item_id;
            }
        }

        if ( empty( $items_available ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add shipping tracking info via ajax
     *
     * @since 3.2.4
     *
     * @param void
     */
    public function add_shipping_status_tracking_info() {
        if ( ! is_user_logged_in() ) {
            die( -1 );
        }

        if ( ! isset( $_REQUEST['security'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['security'] ), 'add-shipping-status-tracking-info' ) ) {
            die( -1 );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( ! dokan_is_seller_has_order( dokan_get_current_user_id(), $post_id ) ) {
            die( -1 );
        }

        $shipment_comments = isset( $_POST['shipment_comments'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['shipment_comments'] ) ) ) : '';
        $tracking_info     = $this->prepare_for_db( $_POST );
        $user_id           = dokan_get_current_user_id();

        if ( ! $tracking_info ) {
            die( -1 );
        }

        $order = dokan()->order->get( $post_id );

        if ( $order->get_status() === 'cancelled' || $order->get_status() === 'refunded' ) {
            die( -1 );
        }

        if ( empty( $order ) || $tracking_info['number'] === '' || $tracking_info['shipping_status'] === '' || $tracking_info['provider'] === '' ) {
            die();
        }

        $shipment_id   = $this->create_shipping_tracking( $tracking_info );
        $tracking_info = (object) $tracking_info;

        if ( $shipment_id ) {
            dokan_shipment_cache_clear_group( $post_id );
            do_action( 'dokan_order_shipping_status_tracking_new_added', $post_id, $tracking_info, $user_id, $shipment_id );
        } else {
            die( -1 );
        }

        $ship_info = __( 'Shipping Provider: ', 'dokan' ) . '<strong>' . $tracking_info->provider_label . '</strong><br />' . __( 'Shipping number: ', 'dokan' ) . '<strong>' . $tracking_info->number . '</strong><br />' . __( 'Shipped date: ', 'dokan' ) . '<strong>' . $tracking_info->date . '</strong><br />' . __( 'Shipped status: ', 'dokan' ) . '<strong>' . $tracking_info->status_label . '</strong>';

        if ( ! empty( $shipment_comments ) ) {
            $ship_info .= '<br><br><strong>' . __( 'Comments: ', 'dokan' ) . '</strong>' . $shipment_comments;
        }

        if ( 'on' === $tracking_info->is_notify ) {
            do_action( 'dokan_order_shipping_status_tracking_notify', $post_id, $tracking_info, $ship_info, $user_id, true );
        }

        dokan_pro()->shipment->add_shipping_status_tracking_notes( $post_id, $shipment_id, $ship_info, $order );

        echo '' . esc_html__( 'Sucessfully Created New Shipment', 'dokan' ) . '';

        die();
    }

    /**
     * Update shipping tracking info via ajax
     *
     * @since 3.2.4
     *
     * @param void
     */
    public function update_shipping_status_tracking_info() {
        if ( ! is_user_logged_in() ) {
            die( -1 );
        }

        if ( ! isset( $_REQUEST['security'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['security'] ), 'update-shipping-status-tracking-info' ) ) {
            die( -1 );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( ! dokan_is_seller_has_order( dokan_get_current_user_id(), $post_id ) ) {
            die( -1 );
        }

        $shipment_id = isset( $_POST['shipment_id'] ) ? absint( $_POST['shipment_id'] ) : 0;
        $user_id     = dokan_get_current_user_id();

        $time           = current_time( 'mysql' );
        $status         = isset( $_POST['shipped_status'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['shipped_status'] ) ) ) : '';
        $provider       = isset( $_POST['shipping_provider'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['shipping_provider'] ) ) ) : '';
        $status_date    = isset( $_POST['shipped_status_date'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['shipped_status_date'] ) ) ) : '';
        $number         = isset( $_POST['tracking_status_number'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['tracking_status_number'] ) ) ) : '';
        $is_notify      = isset( $_POST['is_notify'] ) ? sanitize_text_field( wp_unslash( $_POST['is_notify'] ) ) : '';
        $ship_comments  = isset( $_POST['shipment_comments'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['shipment_comments'] ) ) ) : '';

        $provider_label = dokan_get_shipping_tracking_provider_by_key( $provider, 'label' );
        $provider_url   = dokan_get_shipping_tracking_provider_by_key( $provider, 'url', $number );
        $status_label   = dokan_get_shipping_tracking_status_by_key( $status );

        if ( 'sp-other' === $provider ) {
            $provider_label = isset( $_POST['status_other_provider'] ) ? sanitize_text_field( wp_unslash( $_POST['status_other_provider'] ) ) : '';
            $provider_url   = isset( $_POST['status_other_p_url'] ) ? sanitize_text_field( wp_unslash( $_POST['status_other_p_url'] ) ) : '';
        }

        $order = dokan()->order->get( $post_id );

        if ( $order->get_status() === 'cancelled' || $order->get_status() === 'refunded' ) {
            die( -1 );
        }

        if ( empty( $order ) || $number === '' || $status === '' || $provider === '' || $post_id < 1 || $shipment_id < 1 ) {
            die( -1 );
        }

        global $wpdb;

        $old_tracking_info = dokan_pro()->shipment->get_shipping_tracking_info( $shipment_id, 'shipment_item' );

        $ship_info = '';

        if ( $old_tracking_info->provider !== $provider ) {
            // translators: %1$s: Old provider label, %2$s: New provider label
            $ship_info .= sprintf( __( 'Shipping Provider: %1$s to %2$s', 'dokan' ), '<strong>' . $old_tracking_info->provider_label . '</strong>', '<strong>' . $provider_label . '</strong><br>' );
        }

        if ( $old_tracking_info->number !== $number ) {
            // translators: %1$s: Old provider label, %2$s: New provider label
            $ship_info .= sprintf( __( 'Shipping number: %1$s to %2$s', 'dokan' ), '<strong>' . $old_tracking_info->number . '</strong>', '<strong>' . $number . '</strong><br>' );
        }

        if ( $old_tracking_info->date !== $status_date ) {
            // translators: %1$s: Old provider label, %2$s: New provider label
            $ship_info .= sprintf( __( 'Shipping date: %1$s to %2$s', 'dokan' ), '<strong>' . $old_tracking_info->date . '</strong>', '<strong>' . $status_date . '</strong><br>' );
        }

        if ( $old_tracking_info->shipping_status !== $status ) {
            // translators: %1$s: Old provider label, %2$s: New provider label
            $ship_info .= sprintf( __( 'Shipping status: %1$s to %2$s', 'dokan' ), '<strong>' . $old_tracking_info->status_label . '</strong>', '<strong>' . $status_label . '</strong><br>' );
        }

        if ( ! empty( $ship_comments ) && ! empty( $ship_info ) ) {
            $ship_info .= '<br><strong>' . __( 'Comments: ', 'dokan' ) . '</strong>' . $ship_comments;
        }

        if ( empty( $ship_info ) ) {
            die( -1 );
        }

        $updated = $wpdb->update(
            $wpdb->prefix . 'dokan_shipping_tracking',
            [
                'provider'        => $provider,
                'provider_label'  => $provider_label,
                'provider_url'    => $provider_url,
                'number'          => $number,
                'date'            => $status_date,
                'shipping_status' => $status,
                'status_label'    => $status_label,
                'last_update'     => current_time( 'mysql' ),

            ],
            [ 'id' => $shipment_id ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ],
            [ '%d' ]
        );

        if ( $updated !== 1 ) {
            die( -1 );
        }

        dokan_shipment_cache_clear_group( $post_id );

        dokan_pro()->shipment->add_shipping_status_tracking_notes( $post_id, $shipment_id, $ship_info, $order );

        if ( 'on' === $is_notify ) {
            $tracking_item = dokan_pro()->shipment->get_shipping_tracking_info( $shipment_id, 'shipment_item' );

            do_action( 'dokan_order_shipping_status_tracking_notify', $post_id, $tracking_item, $ship_info, dokan_get_current_user_id(), false );
        }

        do_action( 'dokan_shipping_tracking_updated', $shipment_id, $_POST );

        echo $status_label;

        die();
    }

    /**
     * Add shipping tracking info as customer notes
     *
     * @since 3.2.4
     *
     * @param int    $post_id
     * @param string $ship_info
     * @param obj    $order
     *
     * @param void
     */
    public function add_shipping_status_tracking_notes( $post_id, $shipment_id, $ship_info, $order ) {
        if ( 'on' !== $this->enabled || ! $this->wc_shipping_enabled ) {
            return;
        }

        if ( empty( $post_id ) || empty( $ship_info ) ) {
            return;
        }

        $data = [
            'comment_post_ID'      => $post_id,
            'comment_author'       => 'WooCommerce',
            'comment_author_email' => '',
            'comment_author_url'   => '',
            'comment_content'      => $ship_info,
            'comment_type'         => 'shipment_order_note',
            'comment_parent'       => $shipment_id,
            'user_id'              => dokan_get_current_user_id(),
            'comment_author_IP'    => dokan_get_client_ip(),
            'comment_agent'        => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
            'comment_date'         => current_time( 'mysql' ),
            'comment_approved'     => 1,
        ];

        $comment_id = wp_insert_comment( $data );
    }

    /**
     * Get all approved shipment tracking notes
     *
     * @since 3.2.4
     *
     * @param int $order_id
     * @param int $shipment_id
     *
     * @return array $notes
     */
    public function custom_get_order_notes( $order_id, $shipment_id ) {
        $notes = array();
        $args  = array(
            'post_id' => (int) $order_id,
            'approve' => 'approve',
            'parent'  => $shipment_id,
            'type'    => 'shipment_order_note',
        );

        remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

        $comments = get_comments( $args );

        foreach ( $comments as $comment ) {
            $comment->comment_content = make_clickable( $comment->comment_content );
            $notes[]                  = $comment;
        }

        add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

        return $notes;
    }

    /**
     * Create a shipping tracking info
     *
     * @since 3.2.4
     *
     * @param array $data
     *
     * @return int insert_id
     */
    public function create_shipping_tracking( $data ) {
        global $wpdb;

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'dokan_shipping_tracking',
            $data,
            [ '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        if ( $inserted !== 1 ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Prepare shipping tracking data
     *
     * @since 3.2.4
     *
     * @param array $post_data
     *
     * @return void
     */
    public function prepare_for_db( $post_data ) {
        if ( empty( $post_data ) ) {
            return false;
        }

        $order_id          = isset( $post_data['post_id'] ) ? absint( sanitize_text_field( wp_unslash( $post_data['post_id'] ) ) ) : 0;
        $shipping_provider = isset( $post_data['shipping_provider'] ) ? sanitize_text_field( wp_unslash( $post_data['shipping_provider'] ) ) : '';
        $shipping_number   = isset( $post_data['shipping_number'] ) ? sanitize_text_field( wp_unslash( $post_data['shipping_number'] ) ) : '';
        $shipping_number   = trim( stripslashes( $shipping_number ) );
        $shipped_date      = isset( $post_data['shipped_date'] ) ? trim( sanitize_text_field( wp_unslash( $post_data['shipped_date'] ) ) ) : '';
        $shipped_status    = isset( $post_data['shipped_status'] ) ? trim( sanitize_text_field( wp_unslash( $post_data['shipped_status'] ) ) ) : '';
        $is_notify         = isset( $post_data['is_notify'] ) ? sanitize_text_field( wp_unslash( $post_data['is_notify'] ) ) : '';
        $item_id           = isset( $post_data['item_id'] ) ? sanitize_text_field( wp_unslash( $post_data['item_id'] ) ) : '';
        $item_qty          = isset( $post_data['item_qty'] ) ? wp_unslash( $post_data['item_qty'] ) : '';
        $provider_label    = dokan_get_shipping_tracking_provider_by_key( $shipping_provider, 'label' );
        $provider_url      = dokan_get_shipping_tracking_provider_by_key( $shipping_provider, 'url', $shipping_number );

        if ( 'sp-other' === $shipping_provider ) {
            $provider_label = isset( $post_data['other_provider'] ) ? sanitize_text_field( wp_unslash( $post_data['other_provider'] ) ) : '';
            $provider_url   = isset( $post_data['other_p_url'] ) ? sanitize_text_field( wp_unslash( $post_data['other_p_url'] ) ) : '';
        }

        $request_items = json_decode( $item_qty );
        $item_id_data  = array();
        $item_qty_data = array();

        if ( is_object( $request_items ) ) {
            foreach ( $request_items as $item_id => $quantity ) {
                $item_id  = intval( $item_id );
                $quantity = intval( $quantity );

                $order_item_details = new WC_Order_Item_Product( $item_id );
                $order_quantity     = $order_item_details->get_quantity();

                $is_shiptted = dokan_pro()->shipment->get_status_order_item_shipped( $order_id, $item_id, $order_quantity, 1 );
                $item_qty    = $is_shiptted ? $is_shiptted : 0;

                if ( $quantity <= (int) $item_qty && $quantity > 0 ) {
                    $item_id_data[]            = $item_id;
                    $item_qty_data[ $item_id ] = $quantity;
                }
            }
        }

        if ( empty( $item_id_data ) || empty( $item_qty_data ) ) {
            return false;
        }

        $item_id_data  = wp_json_encode( $item_id_data );
        $item_qty_data = wp_json_encode( $item_qty_data );

        $data = [
            'order_id'        => $order_id,
            'seller_id'       => dokan_get_current_user_id(),
            'provider'        => $shipping_provider,
            'provider_label'  => $provider_label,
            'provider_url'    => $provider_url,
            'number'          => $shipping_number,
            'date'            => $shipped_date,
            'shipping_status' => $shipped_status,
            'status_label'    => dokan_get_shipping_tracking_status_by_key( $shipped_status ),
            'is_notify'       => $is_notify,
            'item_id'         => $item_id_data,
            'item_qty'        => $item_qty_data,
            'last_update'     => current_time( 'mysql' ),
            'status'          => 0,
        ];

        return $data;
    }

    /**
     * Get shipping tracking data by order id
     *
     * @since 3.2.4
     *
     * @param int $order_id
     *
     * @param array $shipment
     */
    public function get_shipping_tracking_data( $order_id ) {
        // getting result from cache
        $cache_group = 'seller_shipment_tracking_data_' . $order_id;
        $cache_key   = 'shipping_tracking_data_' . $order_id;
        $results     = Cache::get( $cache_key, $cache_group );

        if ( false !== $results ) {
            return $results;
        }

        // get all data from database
        $tracking_info = $this->get_shipping_tracking_info( $order_id );

        if ( empty( $tracking_info ) ) {
            // no shipment is added, so set cache and return empty array
            Cache::set( $cache_key, [], $cache_group );
            return [];
        }

        $line_item_count                    = [];
        $shipping_status_count              = [];
        $total_item_count                   = 0;
        $total_item_count_without_cancelled = 0;

        foreach ( $tracking_info as $shipment_data ) {
            // count shipping status
            $shipping_status = $shipment_data->shipping_status;

            $shipping_status_count[ $shipping_status ] = isset( $shipping_status_count[ $shipping_status ] ) ? $shipping_status_count[ $shipping_status ] + 1 : 1;

            // count total item
            $total_item_count++;

            // count total item without cancelled shipping
            if ( 'ss_cancelled' !== $shipping_status ) {
                $total_item_count_without_cancelled++;
            }

            // count line item
            $shipment_items = json_decode( $shipment_data->item_qty );

            if ( is_object( $shipment_items ) && 'ss_cancelled' !== $shipping_status ) {
                foreach ( $shipment_items as $item_id => $count ) {
                    $line_item_count[ $item_id ] = isset( $line_item_count[ $item_id ] ) ? $line_item_count[ $item_id ] + (int) $count : (int) $count;
                }
            }
        }

        $results = [
            'line_item_count'        => $line_item_count,
            'shipping_status_count'  => $shipping_status_count,
            'total_count'            => $total_item_count,
            'total_except_cancelled' => $total_item_count_without_cancelled,
        ];

        // set cache
        Cache::set( $cache_key, $results, $cache_group );

        return $results;
    }

    /**
     * Change the columns shown in admin area
     *
     * @since 3.2.4
     *
     * @param array $existing_columns
     *
     * @return array
     */
    public function admin_shipping_status_tracking_columns( $existing_columns ) {
        $existing_columns['shipping_status_tracking'] = __( 'Shipment', 'dokan' );

        if ( ! current_user_can( 'manage_woocommerce' ) || ( isset( $_GET['author'] ) && ! empty( $_GET['author'] ) ) ) {
            unset( $existing_columns['shipping_status_tracking'] );
        }

        return apply_filters( 'dokan_edit_shop_order_columns', $existing_columns );
    }

    /**
     * Adds custom column on dokan admin shop order table
     *
     * @since 3.2.4
     *
     * @global type $post
     * @global \WC_Order $the_order
     *
     * @param type $col
     *
     * @return void
     */
    public function shop_order_shipping_status_columns( $col ) {
        global $post, $the_order;

        if ( empty( $the_order ) || $the_order->get_id() !== $post->ID ) {
            $the_order = new \WC_Order( $post->ID );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return $col;
        }

        $has_sub = get_post_meta( $post->ID, 'has_sub_order', true );
        $status  = '';

        if ( $has_sub === '1' ) {
            $status = dokan_get_main_order_shipment_current_status( $post->ID );
        } else {
            $status = dokan_get_order_shipment_current_status( $post->ID );
        }

        switch ( $col ) {
            case 'shipping_status_tracking':
                echo $status;
                break;
        }
    }

    /**
     * Shipment order meta box for admin order page
     *
     * @since 3.2.4
     *
     * @return void
     */
    public function shipment_order_add_meta_boxes() {
        global $post;

        $order = dokan()->order->get( $post->ID );

        if ( empty( $order ) ) {
            return;
        }

        $has_sub = get_post_meta( $post->ID, 'has_sub_order', true );

        if ( $has_sub === '1' ) {
            return;
        }

        $has_sub = get_post_meta( $post->ID, 'has_sub_order', true );
        add_meta_box( 'dokan_shipment_status_details', __( 'Shipments', 'dokan' ), [ self::class, 'shipment_order_details_add_meta_boxes' ], 'shop_order', 'normal', 'core' );
    }

    /**
     * Get shipping tracking info by order/shipment id
     *
     * @since 3.2.4
     *
     * @param int    $id
     * @param string $context
     * @param bool   $ignore_cancelled
     *
     * @param array $shipment
     */
    public function get_shipping_tracking_info( $id, $context = 'shipment_info', $ignore_cancelled = false ) {
        if ( empty( $id ) || ! in_array( $context, [ 'shipment_info', 'shipment_item' ], true ) ) {
            return;
        }

        global $wpdb;

        $ignore_cancel = '';

        if ( $ignore_cancelled ) {
            $ignore_cancel = " AND shipping_status != 'ss_cancelled' ";
        }

        if ( 'shipment_info' === $context ) {
            $sql = "SELECT * from {$wpdb->prefix}dokan_shipping_tracking WHERE order_id = %d {$ignore_cancel} ORDER BY id ASC";
        } elseif ( 'shipment_item' === $context ) {
            $sql = "SELECT * from {$wpdb->prefix}dokan_shipping_tracking WHERE id = %d {$ignore_cancel}";
        }

        $shipment = $wpdb->get_results( $wpdb->prepare( $sql, $id ) );

        return 'shipment_item' === $context && $shipment ? $shipment[0] : $shipment;
    }

    /**
     * Is order item fully shiptted
     *
     * @since 3.2.4
     *
     * @param int $order_id
     * @param int $item_id
     * @param int $item_qty
     * @param int $need_available
     *
     * @param mix
     */
    public function get_status_order_item_shipped( $order_id, $item_id, $item_qty = 0, $need_available = 0 ) {
        // based on $need_available decide what to return in case of validation error
        $return = $need_available ? $item_qty : false;

        if ( empty( $order_id ) ) {
            return $return;
        }

        // get all shipment related data for this order
        $shipping_data = $this->get_shipping_tracking_data( $order_id );

        // check if data exits
        if ( empty( $shipping_data ) || ! isset( $shipping_data['line_item_count'] ) ) {
            return $return;
        }

        // get line item count data
        $line_item_count = $shipping_data['line_item_count'];

        // check if $item_id exists
        if ( ! array_key_exists( $item_id, $line_item_count ) ) {
            return $return;
        }

        // if $need_available is true return remaining item count
        if ( $need_available ) {
            return intval( $item_qty ) - intval( $line_item_count[ $item_id ] );
        }

        if ( intval( $item_qty ) === intval( $line_item_count[ $item_id ] ) ) {
            return true;
        }

        return false;
    }

    /**
     * Shipment order details meta box for admin area order page
     *
     * @since 3.2.4
     *
     * @return void
     */
    public static function shipment_order_details_add_meta_boxes() {
        global $post;

        $order = dokan()->order->get( $post->ID );

        if ( empty( $order ) ) {
            return;
        }

        $order_id      = $order->get_id();
        $shipment_info = dokan_pro()->shipment->get_shipping_tracking_info( $order_id );
        $incre         = 1;
        $line_items    = [];

        if ( empty( $shipment_info ) ) {
            echo __( 'No shipment added for this order', 'dokan' );
            return;
        }

        $line_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );

        foreach ( $shipment_info as $key => $shipment ) :
            $shipment_id       = $shipment->id;
            $order_id          = $shipment->order_id;
            $provider          = $shipment->provider_label;
            $number            = $shipment->number;
            $status            = $shipment->status_label;
            $shipping_status   = $shipment->shipping_status;
            $provider_url      = $shipment->provider_url;
            $item_qty          = json_decode( $shipment->item_qty );
            $shipment_timeline = dokan_pro()->shipment->custom_get_order_notes( $order_id, $shipment_id );

            dokan_get_template_part(
                'orders/shipment/html-shipments-list-admin', '', array(
                    'pro'               => true,
                    'shipment_id'       => $shipment_id,
                    'order_id'          => $order_id,
                    'provider'          => $provider,
                    'number'            => $number,
                    'status'            => $status,
                    'shipping_status'   => $shipping_status,
                    'provider_url'      => $provider_url,
                    'item_qty'          => $item_qty,
                    'order'             => $order,
                    'line_items'        => $line_items,
                    'incre'             => $incre,
                    'shipment_timeline' => $shipment_timeline,
                )
            );

            $incre++;
        endforeach;
    }

    /**
     * Shipment order details show after order table WC my account
     *
     * @since 3.2.4
     *
     * @param Obj $order
     *
     * @return void
     */
    public function shipment_order_details_after_order_table( $order ) {
        if ( empty( $order ) ) {
            return;
        }

        $order_id      = $order->get_id();
        $shipment_info = dokan_pro()->shipment->get_shipping_tracking_info( $order_id );
        $line_items    = $order->get_items( 'line_item' );

        if ( empty( $shipment_info ) ) {
            return;
        }

        dokan_get_template_part(
            'orders/shipment/html-customer-shipments-list', '', array(
                'pro'           => true,
                'shipment_info' => $shipment_info,
                'order'         => $order,
                'line_items'    => $line_items,
            )
        );
    }

    /**
     * Shipment column added on my account page order listing page
     *
     * @since 3.2.4
     *
     * @param array $columns
     *
     * @return array
     */
    public function shipment_my_account_my_orders_columns( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $name ) {
            $new_columns[ $key ] = $name;

            // add ship-to after order status column
            if ( 'order-status' === $key ) {
                $new_columns['dokan-shipment-status'] = __( 'Shipment', 'dokan' );
            }
        }

        return $new_columns;
    }

    /**
     * Shipment data show on my account page order listing page
     *
     * @since 3.2.4
     *
     * @param obj $order
     *
     * @return mix
     */
    public function shipment_my_account_orders_column_data( $order ) {
        $has_sub = get_post_meta( $order->get_id(), 'has_sub_order', true );

        if ( $has_sub === '1' ) {
            echo dokan_get_main_order_shipment_current_status( $order->get_id() );
            return;
        }

        echo dokan_get_order_shipment_current_status( $order->get_id() );
    }

    /**
     * Add Dokan Pro localized vars
     *
     * @since 3.2.4
     *
     * @param array $args
     *
     * @return array
     */
    public function set_localized_data( $args ) {
        $args['shipment_status_error_msg'] = __( 'Error! Please enter the correct data for all shipments', 'dokan' );
        $args['shipment_status_update_msg'] = __( 'Shipment Successfully Updated', 'dokan' );

        return $args;
    }
}
