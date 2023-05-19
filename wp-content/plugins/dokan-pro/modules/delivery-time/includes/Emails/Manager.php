<?php

namespace WeDevs\DokanPro\Modules\DeliveryTime\Emails;

use WC_Order;
use WeDevs\DokanPro\Modules\DeliveryTime\Helper;

class Manager {

    /**
     * Manager constructor.
     *
     * @since 3.7.8
     */
    public function __construct() {
        add_filter( 'woocommerce_email_classes', [ $this, 'load_delivery_time_emails' ] );
        add_filter( 'dokan_email_list', [ $this, 'set_delivery_time_email_template_directory' ] );
        add_filter( 'dokan_email_actions', [ $this, 'register_delivery_time_email_actions' ] );
        add_filter( 'woocommerce_email_order_meta_fields', [ $this, 'add_delivery_time_order_metas' ], 10, 3 );
    }

    /**
     * Load delivery time related emails.
     *
     * @since 3.7.8
     *
     * @param array $wc_emails
     *
     * @return array
     */
    public function load_delivery_time_emails( $wc_emails ) {
        $wc_emails['Dokan_Email_Admin_Update_Order_Delivery_Time']  = new UpdateAdminOrderDeliveryTime();
        $wc_emails['Dokan_Email_Vendor_Update_Order_Delivery_Time'] = new UpdateVendorOrderDeliveryTime();

        return $wc_emails;
    }

    /**
     * Set email template directory from here.
     *
     * @since 3.7.8
     *
     * @param array $dokan_emails
     *
     * @return array
     */
    public function set_delivery_time_email_template_directory( $dokan_emails ) {
        $delivery_time_emails[] = 'update-admin-order-time-email.php';
        $delivery_time_emails[] = 'update-vendor-order-time-email.php';

        return array_merge( $delivery_time_emails, $dokan_emails );
    }

    /**
     * Register Dokan Delivery Time Email actions.
     *
     * @since 3.7.8
     *
     * @param array $actions
     *
     * @return array
     */
    public function register_delivery_time_email_actions( $actions ) {
        $actions[] = 'dokan_after_vendor_update_order_delivery_info';
        $actions[] = 'dokan_after_admin_update_order_delivery_info';

        return $actions;
    }

    /**
     * Add order metas to email.
     *
     * @param array $metas Metas to display.
     * @param bool $sent_to_admin Is the email sent to the admin.
     * @param WC_Order $order The order object.
     *
     * @return array
     */
    public function add_delivery_time_order_metas( array $metas, bool $sent_to_admin, WC_Order $order): array {
        if ( ! empty( $order->get_meta('has_sub_order' ) ) || empty( $order->get_meta( 'dokan_delivery_time_date' ) ) ) {
            return $metas;
        }

        $delivery_info = Helper::get_order_delivery_info( dokan_get_seller_id_by_order( $order->get_id() ), $order->get_id() );
        $date_label    = 'delivery' === $delivery_info->delivery_type ? __( 'Delivery Date', 'dokan' ) : __( 'Pickup Date', 'dokan' );
        $time_label    = 'delivery' === $delivery_info->delivery_type ? __( 'Delivery Time Slot', 'dokan' ) : __( 'Pickup Time Slot', 'dokan' );
        $separator     = ' - ';
        $time_string   = implode(
            $separator,
            array_map(
                function ( $time ) {
                    return dokan_format_time( trim( $time ) );
                },
                explode(
                    $separator,
                    $order->get_meta( 'dokan_delivery_time_slot' )
                )
            )
        );

        $metas['dokan_delivery_time_date'] = [
            'label' => $date_label,
            'value' => dokan_format_date( $order->get_meta( 'dokan_delivery_time_date' ) ),
        ];

        $metas['dokan_delivery_time_slot'] = [
            'label' => $time_label,
            'value' => $time_string,
        ];

        if ( 'delivery' !== $delivery_info->delivery_type ) {
            $metas['dokan_store_pickup_location'] = [
                'label' => __( 'Pickup Location', 'dokan' ),
                'value' => $order->get_meta( 'dokan_store_pickup_location' ),
            ];
        }

        return $metas;
    }
}
