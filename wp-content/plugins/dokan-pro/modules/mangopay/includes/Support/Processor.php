<?php
namespace WeDevs\DokanPro\Modules\MangoPay\Support;

use MangoPay\Address;
use MangoPay\Billing;
use MangoPay\BrowserInfo;
use MangoPay\ShippingAddress;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Order;

defined( 'ABSPATH' ) || exit;

/**
 * Class for processing gateway apis
 *
 * @since 3.5.0
 */
class Processor {

    /**
     * Handles the configuration instance
     *
     * @since 3.5.0
     *
     * @return object
     */
    protected static function config() {
        return Config::get_instance();
    }

    /**
     * Processes browser data
     *
     * @since 3.5.0
     *
     * @param object $payin_obj
     *
     * @return object
     */
    protected static function process_browser_data( $payin_obj ) {
        // Add Browser data
        $payin_obj->IpAddress 					   = $_SERVER['REMOTE_ADDR'];
        $payin_obj->BrowserInfo					   = new BrowserInfo();
        $payin_obj->BrowserInfo->AcceptHeader	   = $_SERVER['HTTP_ACCEPT'];
        $payin_obj->BrowserInfo->Language		   = isset( $_POST['dokan-mangopay-3ds2-lang'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan-mangopay-3ds2-lang'] ) ) : '';
        $payin_obj->BrowserInfo->ColorDepth		   = isset( $_POST['dokan-mangopay-3ds2-color-depth'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan-mangopay-3ds2-color-depth'] ) ) : '';
        $payin_obj->BrowserInfo->ScreenHeight      = isset( $_POST['dokan-mangopay-3ds2-screen-height'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan-mangopay-3ds2-screen-height'] ) ) : '';
        $payin_obj->BrowserInfo->ScreenWidth	   = isset( $_POST['dokan-mangopay-3ds2-screen-width'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan-mangopay-3ds2-screen-width'] ) ) : '';
        $payin_obj->BrowserInfo->TimeZoneOffset	   = isset( $_POST['dokan-mangopay-3ds2-timezone-offset'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan-mangopay-3ds2-timezone-offset'] ) ) : '';
        $payin_obj->BrowserInfo->UserAgent		   = isset( $_POST['dokan-mangopay-3ds2-user-agent'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan-mangopay-3ds2-user-agent'] ) ) : '';
        $payin_obj->BrowserInfo->JavaEnabled	   = isset( $_POST['dokan-mangopay-3ds2-java'] ) && 'yes' === $_POST['dokan-mangopay-3ds2-java'];
        $payin_obj->BrowserInfo->JavascriptEnabled = isset( $_POST['dokan-mangopay-3ds2-js'] ) && 'yes' === $_POST['dokan-mangopay-3ds2-js'];

        return $payin_obj;
    }

    /**
     * Prepares billing and shipping data for orders
     *
     * @since 3.5.0
     *
     * @param object 	 $payin_obj
     * @param int|string $order_id
     *
     * @return object
     */
    public static function prepare_billing_shipping_data( $payin_obj, $order_id ) {
        $order = wc_get_order( $order_id );

        // Check if the order object is valid
        if ( ! $order ) {
            return $payin_obj;
        }

        $payin_obj->Billing						   = new Billing();
        $payin_obj->Billing->Address			   = new Address();
        $payin_obj->Billing->FirstName			   = $order->get_billing_first_name();
        $payin_obj->Billing->LastName			   = $order->get_billing_last_name();//
        $payin_obj->Billing->Address->AddressLine1 = $order->get_billing_address_1();
        $payin_obj->Billing->Address->AddressLine2 = $order->get_billing_address_2();
        $payin_obj->Billing->Address->City		   = $order->get_billing_city();
        $payin_obj->Billing->Address->Region	   = $order->get_billing_state();
        $payin_obj->Billing->Address->PostalCode   = $order->get_billing_postcode();
        $payin_obj->Billing->Address->Country	   = $order->get_billing_country();

        // Return if shipping data is not available
        if ( empty( $order->get_shipping_first_name() ) || empty( $order->get_shipping_country() ) ) {
            return $payin_obj;
        }

        $payin_obj->Shipping						= new ShippingAddress();
        $payin_obj->Shipping->RecipientName		    = $order->get_formatted_shipping_full_name();
        $payin_obj->Shipping->Address				= new Address();
        $payin_obj->Shipping->FirstName			    = $order->get_shipping_first_name();
        $payin_obj->Shipping->LastName			    = $order->get_shipping_last_name();//
        $payin_obj->Shipping->Address->AddressLine1 = $order->get_shipping_address_1();
        $payin_obj->Shipping->Address->AddressLine2 = $order->get_shipping_address_2();
        $payin_obj->Shipping->Address->City		    = $order->get_shipping_city();
        $payin_obj->Shipping->Address->Region		= $order->get_shipping_state();
        $payin_obj->Shipping->Address->PostalCode	= $order->get_shipping_postcode();
        $payin_obj->Shipping->Address->Country	    = $order->get_shipping_country();

        return $payin_obj;
    }

    /**
     * Saves metadata for payment
     *
     * @since 3.5.0
     *
     * @param \WC_Order    $order
     * @param array|object $payment_data
     * @param string       $payment_method
     *
     * @return boolean
     */
    protected static function update_metadata( $order, $payment_data, $payment_method ) {
        switch ( $payment_method ) {
            case 'card':
                $transaction_id = $payment_data['transaction_id'];
                break;

            case 'bank_wire':
                $transaction_id = $payment_data->Id;
                break;

            default:
                return false;
        }

        // Update payment and transaction info
        Meta::update_payment_ref( $order, $payment_data );
        Meta::update_payment_type( $order, $payment_method );
        Meta::update_transaction_id( $order, $transaction_id );
        Order::save_transaction_history( $order, $transaction_id );
        $order->save_meta_data();

        return true;
    }
}
