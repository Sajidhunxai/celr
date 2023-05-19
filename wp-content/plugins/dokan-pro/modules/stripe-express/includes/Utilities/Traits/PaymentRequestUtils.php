<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;

/**
 * Trait for payment request utility methods.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits
 */
trait PaymentRequestUtils {

    use PaymentRequestStates;

    /**
     * Checks if gateway is available.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public function is_gateway_available() {
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        return isset( $gateways[ Helper::get_gateway_id() ] );
    }

    /**
     * Checks to make sure product type is supported.
     * Currently simple and variable products are supported.
     *
     * @todo Add support for the following product types:
     *      'booking',
     *      'bundle',
     *      'composite'
     *
     * @since 3.6.1
     *
     * @return array
     */
    public function supported_product_types() {
        return apply_filters(
            'dokan_stripe_express_payment_request_supported_types',
            [
                'simple',
                'variable',
                'variation',
            ]
        );
    }

    /**
     * Returns true if the current page supports Payment Request Buttons, false otherwise.
     *
     * @since 3.6.1
     *
     * @return boolean True if the current page is supported, false otherwise.
     */
    private function is_page_supported() {
        return $this->is_product() || is_cart();
    }

    /**
     * Retrieves total label for payment request option.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_total_label() {
        $total_label = str_replace( "'", '', Settings::get_statement_descriptor() );

        if ( empty( $total_label ) ) {
            $total_label = apply_filters( 'dokan_stripe_express_payment_request_total_label_suffix', __( 'Total Payment', 'dokan' ) );
        }

        return $total_label;
    }

    /**
     * Checks whether a certain product is supported for Payment Request.
     *
     * @since 3.6.1
     *
     * @param WC_Product $product The product that's being checked for support.
     *
     * @return boolean True if the provided product is supported, false otherwise.
     */
    private function is_product_supported( $product ) {
        if ( ! is_object( $product ) || ! in_array( $product->get_type(), $this->supported_product_types(), true ) ) {
            return false;
        }

        // Trial subscriptions with shipping are not supported.
        if (
            Subscription::is_wc_subscription_product( $product->get_id() )
            && $product->needs_shipping()
            && \WC_Subscriptions_Product::get_trial_length( $product ) > 0
        ) {
            return false;
        }

        // Composite products are not supported on the product page.
        if ( class_exists( 'WC_Composite_Products' ) && function_exists( 'is_composite_product' ) && \is_composite_product() ) {
            return false;
        }

        // File upload addon not supported
        if ( class_exists( 'WC_Product_Addons_Helper' ) ) {
            $product_addons = \WC_Product_Addons_Helper::get_product_addons( $product->get_id() );
            foreach ( $product_addons as $addon ) {
                if ( 'file_upload' === $addon['type'] ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Gets the product data for the currently viewed page
     *
     * @since 3.6.1
     *
     * @return mixed Returns false if not on a product page, the product information otherwise.
     */
    public function get_product_data() {
        if ( ! $this->is_product() ) {
            return false;
        }

        $product = $this->get_product();

        if ( 'variable' === $product->get_type() ) {
            $variation_attributes = $product->get_variation_attributes();
            $attributes           = [];

            foreach ( $variation_attributes as $attribute_name => $attribute_values ) {
                $attribute_key = 'attribute_' . sanitize_title( $attribute_name );

                // Passed value via GET takes precedence. Otherwise get the default value for given attribute
                $attributes[ $attribute_key ] = isset( $_GET[ $attribute_key ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    ? wc_clean( wp_unslash( $_GET[ $attribute_key ] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    : $product->get_variation_default_attribute( $attribute_name );
            }

            $data_store   = \WC_Data_Store::load( 'product' );
            $variation_id = $data_store->find_matching_product_variation( $product, $attributes );

            if ( ! empty( $variation_id ) ) {
                $product = wc_get_product( $variation_id );
            }
        }

        $data  = [];
        $items = [];

        $product_price = $this->get_product_price( $product );
        $tax_amount    = $this->get_tax( $product );

        $items[] = [
            'label'  => $product->get_name(),
            'amount' => Helper::get_stripe_amount( $product_price ),
        ];

        if ( wc_tax_enabled() ) {
            $items[] = [
                'label'   => __( 'Tax', 'dokan' ),
                'amount'  => Helper::get_stripe_amount( $tax_amount ),
            ];
        }

        if ( wc_shipping_enabled() && $product->needs_shipping() ) {
            $items[] = [
                'label'   => __( 'Shipping', 'dokan' ),
                'amount'  => 0,
                'pending' => true,
            ];

            $data['shippingOptions'] = [
                [
                    'id'     => 'pending',
                    'label'  => __( 'Pending', 'dokan' ),
                    'detail' => '',
                    'amount' => 0,
                ],
            ];
        }

        $data['displayItems'] = $items;
        $data['total']        = [
            'label'   => $this->get_total_label(),
            'amount'  => Helper::get_stripe_amount( $product_price + $tax_amount ),
            'pending' => true,
        ];

        $data['requestShipping'] = ( wc_shipping_enabled() && $product->needs_shipping() && 0 !== wc_get_shipping_method_count( true ) );
        $data['currency']        = strtolower( get_woocommerce_currency() );
        $data['country_code']    = substr( get_option( 'woocommerce_default_country' ), 0, 2 );

        return apply_filters( 'dokan_stripe_express_payment_request_product_data', $data, $product );
    }

    /**
     * Gets the product tax amount for the provided product.
     *
     * @since 3.6.2
     *
     * @param WC_Product $product The product to get the tax for.
     *
     * @return float Tax amount for the given product.
     */
    public function get_tax( $product ) {
        if ( ! $product instanceof \WC_Product ) {
            return 0;
        }

        $price_excl_tax = wc_get_price_excluding_tax( $product );
        $price_incl_tax = wc_get_price_including_tax( $product );
        $tax_amount     = $price_incl_tax - $price_excl_tax;

        return wc_format_decimal( $tax_amount, 2 );
    }

    /**
     * Retrieves customer data for the current user.
     *
     * @since 3.6.2
     *
     * @return array
     */
    public function get_customer_data() {
        $customer      = new \WC_Customer( get_current_user_id() );
        $customer_data = [
            'first_name'  => ! empty( $customer->get_billing_first_name() )
                ? $customer->get_billing_first_name()
                : (
                    ! empty( $customer->get_shipping_first_name() )
                    ? $customer->get_shipping_first_name()
                    : $customer->get_first_name()
                ),
            'last_name'  => ! empty( $customer->get_billing_last_name() )
                ? $customer->get_billing_last_name()
                : (
                    ! empty( $customer->get_shipping_last_name() )
                    ? $customer->get_shipping_last_name()
                    : $customer->get_last_name()
                ),
        ];

        return $customer_data;
    }

    /**
     * Checks whether authentication is required for checkout.
     *
     * @since 3.6.1
     *
     * @return bool
     */
    public function is_authentication_required() {
        // If guest checkout is disabled and account creation upon checkout is not possible, authentication is required.
        if ( 'no' === get_option( 'woocommerce_enable_guest_checkout', 'yes' ) && ! $this->is_account_creation_possible() ) {
            return true;
        }
        // If cart contains subscription and account creation upon checkout is not posible, authentication is required.
        if ( $this->has_subscription_product() && ! $this->is_account_creation_possible() ) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether account creation is possible upon checkout.
     *
     * @since 3.6.1
     *
     * @return bool
     */
    public function is_account_creation_possible() {
        // If automatically generate username/password are disabled, the Payment Request API
        // can't include any of those fields, so account creation is not possible.
        return (
            'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout', 'no' ) &&
            'yes' === get_option( 'woocommerce_registration_generate_username', 'yes' ) &&
            'yes' === get_option( 'woocommerce_registration_generate_password', 'yes' )
        );
    }

    /**
     * Checks whether cart contains a subscription product or this is a subscription product page.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function has_subscription_product() {
        if ( ! class_exists( 'WC_Subscriptions_Product' ) ) {
            return false;
        }

        if ( $this->is_product() ) {
            $product = $this->get_product();
            if ( \WC_Subscriptions_Product::is_subscription( $product ) ) {
                return true;
            }
        } elseif ( Helper::has_cart_or_checkout_on_current_page() ) {
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                if ( \WC_Subscriptions_Product::is_subscription( $_product ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if this is a product page or content contains a product_page shortcode.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function is_product() {
        return is_product() || wc_post_content_has_shortcode( 'product_page' );
    }

    /**
     * Get product from product page or product_page shortcode.
     *
     * @since 3.6.1
     *
     * @return WC_Product Product object.
     */
    public function get_product() {
        global $post;

        if ( is_product() ) {
            return wc_get_product( $post->ID );
        } elseif ( wc_post_content_has_shortcode( 'product_page' ) ) {
            // Get id from product_page shortcode.
            preg_match( '/\[product_page id="(?<id>\d+)"\]/', $post->post_content, $shortcode_match );

            if ( ! isset( $shortcode_match['id'] ) ) {
                return false;
            }

            return wc_get_product( $shortcode_match['id'] );
        }

        return false;
    }

    /**
     * Gets the product total price.
     *
     * @since 3.6.1
     *
     * @param object $product WC_Product_* object.
     *
     * @return float Total price.
     */
    public function get_product_price( $product ) {
        $product_price = $product->get_price();
        // Add subscription sign-up fees to product price.
        if ( 'subscription' === $product->get_type() && class_exists( 'WC_Subscriptions_Product' ) ) {
            $product_price = $product->get_price() + \WC_Subscriptions_Product::get_sign_up_fee( $product );
        }

        return $product_price;
    }

    /**
     * Builds the shippings methods to pass to Payment Request
     *
     * @since 3.6.1
     *
     * @return array
     */
    protected function build_shipping_methods( $shipping_methods ) {
        if ( empty( $shipping_methods ) ) {
            return [];
        }

        $shipping = [];

        foreach ( $shipping_methods as $method ) {
            $shipping[] = [
                'id'     => $method['id'],
                'label'  => $method['label'],
                'detail' => '',
                'amount' => Helper::get_stripe_amount( $method['amount']['value'] ),
            ];
        }

        return $shipping;
    }

    /**
     * Builds the line items to pass to Payment Request
     *
     * @since 3.6.1
     *
     * @return array
     */
    protected function build_display_items( $itemized_display_items = false ) {
        if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
            define( 'WOOCOMMERCE_CART', true );
        }

        $items         = [];
        $lines         = [];
        $subtotal      = 0;
        $discounts     = 0;
        $display_items = ! apply_filters( 'dokan_stripe_express_payment_request_hide_itemization', true ) || $itemized_display_items;

        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $subtotal      += $cart_item['line_subtotal'];
            $amount         = $cart_item['line_subtotal'];
            $quantity_label = 1 < $cart_item['quantity'] ? ' (x' . $cart_item['quantity'] . ')' : '';
            $product_name   = $cart_item['data']->get_name();

            $lines[] = [
                'label'  => $product_name . $quantity_label,
                'amount' => Helper::get_stripe_amount( $amount ),
            ];
        }

        if ( $display_items ) {
            $items = array_merge( $items, $lines );
        } else {
            // Default show only subtotal instead of itemization.
            $items[] = [
                'label'  => esc_html__( 'Subtotal', 'dokan' ),
                'amount' => Helper::get_stripe_amount( $subtotal ),
            ];
        }

        if ( version_compare( WC_VERSION, '3.2', '<' ) ) {
            $discounts = wc_format_decimal( WC()->cart->get_cart_discount_total(), WC()->cart->dp );
        } else {
            $applied_coupons = array_values( WC()->cart->get_coupon_discount_totals() );

            foreach ( $applied_coupons as $amount ) {
                $discounts += (float) $amount;
            }
        }

        $discounts   = wc_format_decimal( $discounts, WC()->cart->dp );
        $tax         = wc_format_decimal( WC()->cart->tax_total + WC()->cart->shipping_tax_total, WC()->cart->dp );
        $shipping    = wc_format_decimal( WC()->cart->shipping_total, WC()->cart->dp );
        $items_total = wc_format_decimal( WC()->cart->cart_contents_total, WC()->cart->dp ) + $discounts;
        $order_total = version_compare( WC_VERSION, '3.2', '<' ) ? wc_format_decimal( $items_total + $tax + $shipping - $discounts, WC()->cart->dp ) : WC()->cart->get_total( false );

        if ( wc_tax_enabled() ) {
            $items[] = [
                'label'  => esc_html__( 'Tax', 'dokan' ),
                'amount' => Helper::get_stripe_amount( $tax ),
            ];
        }

        if ( WC()->cart->needs_shipping() ) {
            $items[] = [
                'label'  => esc_html__( 'Shipping', 'dokan' ),
                'amount' => Helper::get_stripe_amount( $shipping ),
            ];
        }

        if ( WC()->cart->has_discount() ) {
            $items[] = [
                'label'  => esc_html__( 'Discount', 'dokan' ),
                'amount' => Helper::get_stripe_amount( $discounts ),
            ];
        }

        if ( version_compare( WC_VERSION, '3.2', '<' ) ) {
            $cart_fees = WC()->cart->fees;
        } else {
            $cart_fees = WC()->cart->get_fees();
        }

        // Include fees and taxes as display items.
        foreach ( $cart_fees as $key => $fee ) {
            $items[] = [
                'label'  => $fee->name,
                'amount' => Helper::get_stripe_amount( $fee->amount ),
            ];
        }

        return [
            'displayItems' => $items,
            'total'        => [
                'label'   => $this->get_total_label(),
                'amount'  => max( 0, apply_filters( 'dokan_stripe_express_calculated_total', Helper::get_stripe_amount( $order_total ), $order_total, WC()->cart ) ),
                'pending' => false,
            ],
        ];
    }

    /**
     * Calculate and set shipping method.
     *
     * @param array $address Shipping address.
     *
     * @since 3.6.1
     *
     * @return void
     */
    protected function calculate_shipping( $address = [] ) {
        $country   = $address['country'];
        $state     = $address['state'];
        $postcode  = $address['postcode'];
        $city      = $address['city'];
        $address_1 = $address['address'];
        $address_2 = $address['address_2'];

        // Normalizes state to calculate shipping zones.
        $state = $this->get_normalized_state( $state, $country );

        // Normalizes postal code in case of redacted data from Apple Pay.
        $postcode = $this->get_normalized_postal_code( $postcode, $country );

        WC()->shipping->reset_shipping();

        if ( $postcode && \WC_Validation::is_postcode( $postcode, $country ) ) {
            $postcode = wc_format_postcode( $postcode, $country );
        }

        if ( $country ) {
            WC()->customer->set_location( $country, $state, $postcode, $city );
            WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
        } else {
            WC()->customer->set_billing_address_to_base();
            WC()->customer->set_shipping_address_to_base();
        }

        WC()->customer->set_calculated_shipping( true );
        WC()->customer->save();

        $packages = [];

        $packages[0]['contents']                 = WC()->cart->get_cart();
        $packages[0]['contents_cost']            = 0;
        $packages[0]['applied_coupons']          = WC()->cart->applied_coupons;
        $packages[0]['user']['ID']               = get_current_user_id();
        $packages[0]['destination']['country']   = $country;
        $packages[0]['destination']['state']     = $state;
        $packages[0]['destination']['postcode']  = $postcode;
        $packages[0]['destination']['city']      = $city;
        $packages[0]['destination']['address']   = $address_1;
        $packages[0]['destination']['address_2'] = $address_2;

        foreach ( WC()->cart->get_cart() as $item ) {
            if ( $item['data']->needs_shipping() ) {
                if ( isset( $item['line_total'] ) ) {
                    $packages[0]['contents_cost'] += $item['line_total'];
                }
            }
        }

        $packages = apply_filters( 'woocommerce_cart_shipping_packages', $packages );

        WC()->shipping->calculate_shipping( $packages );
    }

    /**
     * Gets shipping options available for specified shipping address.
     *
     * @since 3.7.8
     *
     * @param array   $shipping_address       Shipping address.
     * @param boolean $itemized_display_items Indicates whether to show subtotals or itemized views.
     *
     * @return array Shipping options data.
     * @throws \Exception
     */
    protected function process_shipping_options( $shipping_address, $itemized_display_items = false ) {
        try {
            // Set the shipping options.
            $data = [];

            // Remember current shipping method before resetting.
            $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
            $this->calculate_shipping( apply_filters( 'dokan_stripe_express_payment_request_shipping_posted_values', $shipping_address ) );

            $packages          = WC()->shipping->get_packages();
            $shipping_rate_ids = [];

            if ( ! empty( $packages ) && WC()->customer->has_calculated_shipping() ) {
                foreach ( $packages as $package_key => $package ) {
                    if ( empty( $package['rates'] ) ) {
                        throw new \Exception( __( 'Unable to find shipping method for address.', 'dokan' ) );
                    }

                    foreach ( $package['rates'] as $key => $rate ) {
                        if ( in_array( $rate->id, $shipping_rate_ids, true ) ) {
                            // The Payment Requests will try to load indefinitely if there are duplicate shipping
                            // option IDs.
                            throw new \Exception( __( 'Unable to provide shipping options for Payment Requests.', 'dokan' ) );
                        }
                        $shipping_rate_ids[]        = $rate->id;
                        $data['shipping_options'][] = [
                            'id'     => $rate->id,
                            'label'  => $rate->label,
                            'detail' => '',
                            'amount' => Helper::get_stripe_amount( $rate->cost ),
                        ];
                    }
                }
            } else {
                throw new \Exception( __( 'Unable to provide shipping options for Payment Requests.', 'dokan' ) );
            }

            /*
             * The first shipping option is automatically applied on the client.
             * Keep chosen shipping method by sorting shipping options
             * if the method still available for new address.
             * Fallback to the first available shipping method.
             */
            if ( isset( $data['shipping_options'][0] ) ) {
                if ( isset( $chosen_shipping_methods[0] ) ) {
                    $chosen_method_id = $chosen_shipping_methods[0];
                    usort(
                        $data['shipping_options'],
                        function ( $option_1, $option_2 ) use ( $chosen_method_id ) {
                            if ( $option_1['id'] === $chosen_method_id ) {
                                return -1;
                            }

                            if ( $option_2['id'] === $chosen_method_id ) {
                                return 1;
                            }

                            return 0;
                        }
                    );
                }

                $first_shipping_method_id = $data['shipping_options'][0]['id'];
                $this->modify_shipping_method( [ $first_shipping_method_id ] );
            }

            WC()->cart->calculate_totals();

            $data          += $this->build_display_items( $itemized_display_items );
            $data['result'] = 'success';
        } catch ( \Exception $e ) {
            $data          += $this->build_display_items( $itemized_display_items );
            $data['result'] = 'invalid_shipping_address';
        }

        return $data;
    }

    /**
     * Updates shipping method in WC session.
     *
     * @since 3.7.8
     *
     * @param array $shipping_methods Array of selected shipping methods ids.
     *
     * @return void
     */
    public function modify_shipping_method( $shipping_methods ) {
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

        if ( is_array( $shipping_methods ) ) {
            foreach ( $shipping_methods as $i => $value ) {
                $chosen_shipping_methods[ $i ] = wc_clean( $value );
            }
        }

        WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
    }

    /**
     * Normalizes postal code in case of redacted data from Apple Pay.
     *
     * @since 3.6.1
     *
     * @param string $postcode Postal code.
     * @param string $country Country.
     */
    public function get_normalized_postal_code( $postcode, $country ) {
        /*
         * Currently, Apple Pay truncates the UK and Canadian postal codes to the first 4 and 3 characters respectively
         * when passing it back from the shippingcontactselected object. This causes WC to invalidate
         * the postal code and not calculate shipping zones correctly.
         */
        if ( 'GB' === $country ) {
            // Replaces a redacted string with something like LN10***.
            return str_pad( preg_replace( '/\s+/', '', $postcode ), 7, '*' );
        }

        if ( 'CA' === $country ) {
            // Replaces a redacted string with something like L4Y***.
            return str_pad( preg_replace( '/\s+/', '', $postcode ), 6, '*' );
        }

        return $postcode;
    }

    /**
     * Normalizes billing and shipping state fields.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function normalize_state() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $billing_country  = ! empty( $_POST['billing_country'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) : '';
        $shipping_country = ! empty( $_POST['shipping_country'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_country'] ) ) : '';
        $billing_state    = ! empty( $_POST['billing_state'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_state'] ) ) : '';
        $shipping_state   = ! empty( $_POST['shipping_state'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_state'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        /*
         * Due to a bug in Apple Pay, the "Region" part of a Hong Kong address is delivered in
         * `shipping_postcode`, so we need some special case handling for that. According to
         * our sources at Apple Pay people will sometimes use the district or even sub-district
         * for this value. As such we check against all regions, districts, and sub-districts
         * with both English and Mandarin languages.
         *
         * The check here is quite elaborate in an attempt to make sure this doesn't break once
         * Apple Pay fixes the bug that causes address values to be in the wrong place. Because of that the
         * algorithm becomes:
         *  1. Use the supplied state if it's valid (in case Apple Pay bug is fixed)
         *  2. Use the value supplied in the postcode if it's a valid HK region (equivalent to a WC state).
         *  3. Fall back to the value supplied in the state. This will likely cause a validation error, in
         *     which case a merchant can reach out to us so we can either: 1) add whatever the customer used
         *     as a state to our list of valid states; or 2) let them know the customer must spell the state
         *     in some way that matches our list of valid states.
         *
         * This HK specific sanitazation *should be removed* once Apple Pay fix
         * the address bug.
         */
        if ( 'HK' === $billing_country ) {
            if ( ! $this->is_valid_hongkong_state( strtolower( $billing_state ) ) ) {
                $billing_postcode = ! empty( $_POST['billing_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_postcode'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
                if ( $this->is_valid_hongkong_state( strtolower( $billing_postcode ) ) ) {
                    $billing_state = $billing_postcode;
                }
            }
        }
        if ( 'HK' === $shipping_country ) {
            if ( ! $this->is_valid_hongkong_state( strtolower( $shipping_state ) ) ) {
                $shipping_postcode = ! empty( $_POST['shipping_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_postcode'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
                if ( $this->is_valid_hongkong_state( strtolower( $shipping_postcode ) ) ) {
                    $shipping_state = $shipping_postcode;
                }
            }
        }

        // Finally we normalize the state value we want to process.
        if ( $billing_state && $billing_country ) {
            $_POST['billing_state'] = $this->get_normalized_state( $billing_state, $billing_country );
        }

        if ( $shipping_state && $shipping_country ) {
            $_POST['shipping_state'] = $this->get_normalized_state( $shipping_state, $shipping_country );
        }
    }

    /**
     * Checks if given state is normalized.
     *
     * @since 3.6.1
     *
     * @param string $state State.
     * @param string $country Two-letter country code.
     *
     * @return bool Whether state is normalized or not.
     */
    public function is_normalized_state( $state, $country ) {
        $wc_states = WC()->countries->get_states( $country );
        return (
            is_array( $wc_states ) &&
            in_array( $state, array_keys( $wc_states ), true )
        );
    }

    /**
     * Sanitize string for comparison.
     *
     * @since 3.6.1
     *
     * @param string $string String to be sanitized.
     *
     * @return string The sanitized string.
     */
    public function sanitize_string( $string ) {
        return trim( wc_strtolower( remove_accents( $string ) ) );
    }

    /**
     * Get normalized state from Payment Request API dropdown list of states.
     *
     * @since 3.6.1
     *
     * @param string $state   Full state name or state code.
     * @param string $country Two-letter country code.
     *
     * @return string Normalized state or original state input value.
     */
    public function get_normalized_state_from_pr_states( $state, $country ) {
        // Include Payment Request API State list for compatibility with WC countries/states.
        $pr_states = $this->get_payment_request_states();

        if ( ! isset( $pr_states[ $country ] ) ) {
            return $state;
        }

        foreach ( $pr_states[ $country ] as $wc_state_abbr => $pr_state ) {
            $sanitized_state_string = $this->sanitize_string( $state );
            // Checks if input state matches with Payment Request state code (0), name (1) or localName (2).
            if (
                ( ! empty( $pr_state[0] ) && $sanitized_state_string === $this->sanitize_string( $pr_state[0] ) ) ||
                ( ! empty( $pr_state[1] ) && $sanitized_state_string === $this->sanitize_string( $pr_state[1] ) ) ||
                ( ! empty( $pr_state[2] ) && $sanitized_state_string === $this->sanitize_string( $pr_state[2] ) )
            ) {
                return $wc_state_abbr;
            }
        }

        return $state;
    }

    /**
     * Get normalized state from WooCommerce list of translated states.
     *
     * @since 3.6.1
     *
     * @param string $state   Full state name or state code.
     * @param string $country Two-letter country code.
     *
     * @return string Normalized state or original state input value.
     */
    public function get_normalized_state_from_wc_states( $state, $country ) {
        $wc_states = WC()->countries->get_states( $country );

        if ( is_array( $wc_states ) ) {
            foreach ( $wc_states as $wc_state_abbr => $wc_state_value ) {
                if ( preg_match( '/' . preg_quote( $wc_state_value, '/' ) . '/i', $state ) ) {
                    return $wc_state_abbr;
                }
            }
        }

        return $state;
    }

    /**
     * Gets the normalized state/county field because in some
     * cases, the state/county field is formatted differently from
     * what WC is expecting and throws an error. An example
     * for Ireland, the county dropdown in Chrome shows "Co. Clare" format.
     *
     * @since 3.6.1
     *
     * @param string $state   Full state name or an already normalized abbreviation.
     * @param string $country Two-letter country code.
     *
     * @return string Normalized state abbreviation.
     */
    public function get_normalized_state( $state, $country ) {
        // If it's empty or already normalized, skip.
        if ( ! $state || $this->is_normalized_state( $state, $country ) ) {
            return $state;
        }

        // Try to match state from the Payment Request API list of states.
        $state = $this->get_normalized_state_from_pr_states( $state, $country );

        // If it's normalized, return.
        if ( $this->is_normalized_state( $state, $country ) ) {
            return $state;
        }

        // If the above doesn't work, fallback to matching against the list of translated
        // states from WooCommerce.
        return $this->get_normalized_state_from_wc_states( $state, $country );
    }

    /**
     * The Payment Request API provides its own validation for the address form.
     * For some countries, it might not provide a state field, so we need to return a more descriptive
     * error message, indicating that the Payment Request button is not supported for that country.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function validate_state() {
        $wc_checkout     = \WC_Checkout::instance();
        $posted_data     = $wc_checkout->get_posted_data();
        $checkout_fields = $wc_checkout->get_checkout_fields();
        $countries       = WC()->countries->get_countries();

        $is_supported = true;
        // Checks if billing state is missing and is required.
        if ( ! empty( $checkout_fields['billing']['billing_state']['required'] ) && '' === $posted_data['billing_state'] ) {
            $is_supported = false;
        }

        // Checks if shipping state is missing and is required.
        if ( WC()->cart->needs_shipping_address() && ! empty( $checkout_fields['shipping']['shipping_state']['required'] ) && '' === $posted_data['shipping_state'] ) {
            $is_supported = false;
        }

        if ( ! $is_supported ) {
            wc_add_notice(
                sprintf(
                    /* translators: %s: country. */
                    __( 'The Payment Request button is not supported in %s because some required fields couldn\'t be verified. Please proceed to the checkout page and try again.', 'dokan' ),
                    isset( $countries[ $posted_data['billing_country'] ] ) ? $countries[ $posted_data['billing_country'] ] : $posted_data['billing_country']
                ),
                'error'
            );
        }
    }
}
