<?php

namespace WeDevs\DokanPro\Modules\OrderMinMax;

/**
 * OrderMinMax Class.
 *
 * @since 3.5.0
 */
class FrontEnd {

    private $min_quantity = - 1;
    private $max_quantity = - 1;
    private static $enable_min_max_quantity;
    private static $enable_min_max_amount;

    /**
     * OrderMinMax Class Constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        self::$enable_min_max_quantity = dokan_get_option( 'enable_min_max_quantity', 'dokan_selling', 'off' );
        self::$enable_min_max_amount   = dokan_get_option( 'enable_min_max_amount', 'dokan_selling', 'off' );

        // To show cart table min max error.
        add_filter( 'woocommerce_cart_item_quantity', [ $this, 'cart_item_quantity_min_max_quantity_check' ], 10, 3 );
        add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'cart_item_quantity_min_max_amount_check' ], 10, 2 );
        add_filter( 'woocommerce_get_price_html', [ $this, 'add_min_max_to_shop_page' ], 10, 2 );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate_and_update_cart_item' ], 10, 4 );
        add_filter( 'woocommerce_add_cart_item', [ $this, 'update_cart_quantity' ] );
        add_filter( 'woocommerce_available_variation', [ $this, 'available_variation' ], 10, 3 );
        add_filter( 'woocommerce_quantity_input_args', [ $this, 'update_quantity_args' ], 10, 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
        add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'add_to_cart_link' ], 10, 2 );
        add_action( 'woocommerce_check_cart_items', [ $this, 'action_woocommerce_check_cart_items' ] );

        // If we have errors, make sure those are shown on the checkout page
        add_action( 'woocommerce_cart_has_errors', [ $this, 'output_errors' ] );
    }

    /**
     * Cart item quantity min max check.
     *
     * @since 3.5.0
     *
     * @param int    $product_quantity
     * @param string $cart_item_key
     * @param mixed  $cart_item
     *
     * @return int|string
     */
    public function cart_item_quantity_min_max_quantity_check( $product_quantity, $cart_item_key, $cart_item ) {
        if ( 'on' !== self::$enable_min_max_quantity ) {
            return $product_quantity;
        }

        $dokan_settings = $this->dokan_get_store_settings_by_product_id( $cart_item['product_id'] );
        if ( empty( $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) || 'yes' !== $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) {
            return $product_quantity;
        }

        $product_id = $cart_item['product_id'];
        if ( $cart_item['variation_id'] ) {
            $product_id = $cart_item['variation_id'];
        }

        // Product wise settings.
        $quantity_error = $this->check_min_max_quantity_or_amount_error( $cart_item['quantity'], $product_id, 'quantity' );
        if ( ! empty( $quantity_error ) ) {
            remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

            return "{$product_quantity} <div class='required'>$quantity_error</div>";
        }

        return $product_quantity;
    }

    /**
     * Cart item quantity min max check.
     *
     * @since 3.5.0
     *
     * @param string $product_price
     * @param mixed  $cart_item
     *
     * @return string
     */
    public function cart_item_quantity_min_max_amount_check( $product_price, $cart_item ) {
        if ( 'on' !== self::$enable_min_max_amount ) {
            return $product_price;
        }

        $dokan_settings = $this->dokan_get_store_settings_by_product_id( $cart_item['product_id'] );
        if ( empty( $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) || 'yes' !== $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] ) {
            return $product_price;
        }

        $product_id = $cart_item['product_id'];
        if ( $cart_item['variation_id'] ) {
            $product_id = $cart_item['variation_id'];
        }

        // Product wise settings.
        $amount_error = $this->check_min_max_quantity_or_amount_error( $cart_item['line_subtotal'], $product_id, 'amount' );
        if ( ! empty( $amount_error ) ) {
            remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

            return "{$product_price} <div class='required'>$amount_error</div>";
        } else {
            return $product_price;
        }
    }

    /**
     * Add_min_max_to_shop.
     *
     * @since 3.5.0
     *
     * @param string      $price
     * @param \WC_Product $product
     *
     * @return string
     */
    public function add_min_max_to_shop_page( $price, $product ) {
        if ( 'external' === $product->get_type() ) {
            return $price;
        }

        if ( 'on' !== self::$enable_min_max_quantity && 'on' !== self::$enable_min_max_amount ) {
            return $price;
        }

        $page_id = dokan_get_option( 'dashboard', 'dokan_pages' );
        // We don't want to show min max in admin and vendor dashboard page.
        if ( is_admin() || ( apply_filters( 'dokan_get_dashboard_page_id', (int) $page_id ) === get_queried_object_id() ) ) {
            return $price;
        }

        // Show price and quantity range.
        if ( 'variable' === $product->get_type() ) {
            // We don't want to show price and quantity range in single product page. So return.
            if ( is_single() ) {
                return $price;
            }

            $return       = false;
            $min_quantity = 0;
            $max_quantity = 0;
            $min_amount   = 0;
            $max_amount   = 0;
            foreach ( $product->get_children() as $child_id ) {
                $child_product_settings = get_post_meta( $child_id, '_dokan_min_max_meta', true );
                if ( empty( $child_product_settings ) ) {
                    continue;
                }

                $qty    = [];
                $amount = [];
                if ( empty( $min_quantity ) || ( ! empty( $child_product_settings['min_quantity'] ) && ( $min_quantity > $child_product_settings['min_quantity'] ) ) ) {
                    $return              = true;
                    $min_quantity        = $child_product_settings['min_quantity'];
                    $qty['min_quantity'] = $child_product_settings['min_quantity'];
                }

                if ( empty( $max_quantity ) || ( ! empty( $child_product_settings['max_quantity'] ) && ( $max_quantity < $child_product_settings['max_quantity'] ) ) ) {
                    $return              = true;
                    $max_quantity        = $child_product_settings['max_quantity'];
                    $qty['max_quantity'] = $child_product_settings['max_quantity'];
                }

                if ( empty( $min_amount ) || ( ! empty( $child_product_settings['min_amount'] ) && ( $min_amount > $child_product_settings['min_amount'] ) ) ) {
                    $return               = true;
                    $min_amount           = $child_product_settings['min_amount'];
                    $amount['min_amount'] = wc_price( $child_product_settings['min_amount'] );
                }

                if ( empty( $max_amount ) || ( ! empty( $child_product_settings['max_amount'] ) && ( $max_amount < $child_product_settings['max_amount'] ) ) ) {
                    $return               = true;
                    $max_amount           = $child_product_settings['max_amount'];
                    $amount['max_amount'] = wc_price( $child_product_settings['max_amount'] );
                }

                $qty_div = '';
                if ( $max_quantity > 0 && $min_quantity > 0 ) {
                    $qty_div = "<div class='required'>" . apply_filters( 'dokan_min_max_variation_quantity_html', __( 'Quantity ', 'dokan' ) . implode( ' - ', $qty ), $qty ) . '</div>';
                }

                $amount_div = '';
                if ( $max_amount > 0 && $min_amount > 0 ) {
                    $amount_div = "<div class='required'>" . apply_filters( 'dokan_min_max_variation_amount_html', __( 'Amount ', 'dokan' ) . implode( ' - ', $amount ), $amount ) . '</div>';
                }
                if ( $return ) {
                    remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

                    return $price . $qty_div . $amount_div;
                }
            }

            return $price;
        }

        // Product wise settings.
        $product_settings = get_post_meta( $product->get_id(), '_dokan_min_max_meta', true );

        if ( ! empty( WC()->cart->cart_contents ) && WC()->cart->cart_contents_count >= 1 && ( is_cart() || is_checkout() ) && ( isset( $product_settings['_donot_count'] ) && 'yes' === $product_settings['_donot_count'] ) ) {
            return $price;
        }

        $quantity_error = $this->check_min_max_quantity_or_amount_error( '', $product->get_id(), 'quantity', true );

        if ( ! empty( $quantity_error ) ) {
            $html['quantity_error'] = "<span class='min_qty'>" . number_format_i18n( (int) $quantity_error ) . '</span>' . __( ' piece', 'dokan' );
        }

        $amount_error = $this->check_min_max_quantity_or_amount_error( '', $product->get_id(), 'amount', true );

        if ( ! empty( $amount_error ) && $amount_error !== 0 ) {
            $html['amount_error'] = "<span class='min_amount'>" . trim( wc_price( $amount_error ) ) . '</span>';
        }

        if ( ( ! empty( $quantity_error ) || ! empty( $amount_error ) ) && ! empty( $html ) ) {
            remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

            return "{$price} <div class='required'>" . apply_filters( 'dokan_min_max_quantity_amount_html', __( 'Min', 'dokan' ) . ' (' . implode( '/', $html ) . ')', $html ) . '</div>';
        }

        return $price;
    }

    /**
     * Get_html_error.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get_html_price_error( $dokan_settings, $product_id ) {
        $html = [];
        if ( 'yes' === $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) {
            $quantity_error = $this->check_min_max_quantity_or_amount_error( '', $product_id, 'quantity', true );

            if ( ! empty( $quantity_error ) ) {
                // Get integer from string.
                $html['quantity_error'] = number_format_i18n( (int) $quantity_error ) . __( ' piece', 'dokan' );
            }
        }

        if ( 'yes' === $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] ) {
            $amount_error = $this->check_min_max_quantity_or_amount_error( '', $product_id, 'amount', true );

            if ( ! empty( $amount_error ) && $amount_error !== 0 ) {
                $html['amount_error'] = wc_price( $amount_error );
            }
        }

        return $html;
    }

    /**
     * Update cart quantity.
     *
     * @since 3.5.0
     *
     * @param mixed $cart_item_data
     *
     * @return mixed
     */
    public function update_cart_quantity( $cart_item_data ) {
        if ( 'on' !== self::$enable_min_max_quantity ) {
            return $cart_item_data;
        }

        $product_id = $cart_item_data['product_id'];
        if ( ! empty( $cart_item_data['variation_id'] ) ) {
            $product_id = $cart_item_data['variation_id'];
        }

        $dokan_settings = $this->dokan_get_store_settings_by_product_id( $product_id );

        if ( empty( $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) || 'yes' !== $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) {
            return $cart_item_data;
        }

        $other_product_found = false;
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            if ( $product_id === $cart_item['product_id'] ) {
                $cart_item_data['quantity'] += $cart_item['quantity'];
            } else {
                $other_product_found = true;
            }
        }

        $product_settings = get_post_meta( $product_id, '_dokan_min_max_meta', true );
        if ( ( isset( $product_settings['_donot_count'] ) && 'yes' === $product_settings['_donot_count'] ) && $other_product_found ) {
            return $cart_item_data;
        }

        $quantity_error = $this->check_min_max_quantity_or_amount_error( $cart_item_data['quantity'], $product_id, 'quantity', true );

        if ( ! empty( $quantity_error ) ) {
            $cart_item_data['quantity'] = $quantity_error;
        }

        return $cart_item_data;
    }

    /**
     * Validate and Update cart item.
     *
     * @since 3.5.0
     *
     * @param bool $passed
     * @param int  $product_id
     * @param int  $quantity
     * @param int  $variation_id
     *
     * @throws \Exception
     * @return bool
     */
    public function validate_and_update_cart_item( $passed, $product_id, $quantity, $variation_id = 0 ) {
        if ( 'on' !== self::$enable_min_max_quantity ) {
            return $passed;
        }

        $parent_product_id = $product_id;
        if ( 0 !== $variation_id ) {
            $product_id = $variation_id;
        }

        $dokan_settings = $this->dokan_get_store_settings_by_product_id( $product_id );
        if ( empty( $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) || 'yes' !== $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) {
            return $passed;
        }

        // Check cart previous quantity to add with new quantity.
        $cart_key               = '';
        $cart                   = WC()->cart;
        $cart_has_other_product = false;
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $cart_product_id = $cart_item['product_id'];
            if ( ! empty( $cart_item['variation_id'] ) ) {
                $cart_product_id = $cart_item['variation_id'];
            }

            if ( $product_id === $cart_product_id ) {
                // Add previous quantity with new quantity.
                $quantity += $cart_item['quantity'];
                $cart_key = $cart_item_key;
            } else {
                $cart_has_other_product = true;
            }
        }

        $product_settings = get_post_meta( $product_id, '_dokan_min_max_meta', true );
        if ( empty( $product_settings ) ) {
            $product_settings = get_post_meta( $parent_product_id, '_dokan_min_max_meta', true );
        }

        $quantity_error = $this->check_min_max_quantity_or_amount_error( $quantity, $product_id, 'quantity', true );
        if ( ! empty( $quantity_error ) ) {
            $settings_quantity = (int) $quantity_error;
            if ( ( isset( $product_settings['_donot_count'] ) && 'yes' === $product_settings['_donot_count'] ) && $cart_has_other_product ) {
                $settings_quantity = $quantity_error;
            }

            $product = wc_get_product( $product_id );

            if ( $quantity < $settings_quantity ) {
                /* translators: here %1$s is product name, %2$s is quantity. */
                wc_add_notice( sprintf( __( 'Minimum quantity for %1$s to order is %2$s.', 'dokan' ), $product->get_title(), $settings_quantity ), 'error' );

                return $passed;
            }

            if ( $quantity > $settings_quantity ) {
                if ( ! empty( $cart_key ) ) {
                    $cart->set_quantity( $cart_key, $settings_quantity );
                } else {
                    try {
                        $cart->add_to_cart( $product_id, $settings_quantity );
                    } catch ( \Exception $e ) {
                        if ( $e->getMessage() ) {
                            wc_add_notice( $e->getMessage(), 'error' );
                        }

                        return false;
                    }
                }
                /* translators: here %1$s is product name, %2$s is quantity. */
                wc_add_notice( sprintf( __( 'Maximum quantity for %1$s to order is %2$s.', 'dokan' ), $product->get_title(), $settings_quantity ), 'error' );

                return false;
            }
        }

        return $passed;
    }

    /**
     * Quantity error.
     *
     * @since 3.5.0
     *
     * @param mixed  $dokan_settings
     * @param string $context
     * @param int    $product_quantity
     * @param bool   $return_type_number
     *
     * @return string
     */
    public function maybe_quantity_or_amount_error( $dokan_settings, $context, $product_quantity, $return_type_number = false ) {
        $error = '';

        if ( ! empty( $dokan_settings['order_min_max'][ "max_{$context}_to_order" ] ) && ( $product_quantity > $dokan_settings['order_min_max'][ "max_{$context}_to_order" ] ) ) {
            $error = "Max {$context} {$dokan_settings['order_min_max']["max_{$context}_to_order"]}";
            if ( $return_type_number ) {
                $error = $dokan_settings['order_min_max'][ "max_{$context}_to_order" ];
            }
        }

        if ( ! empty( $dokan_settings['order_min_max'][ "min_{$context}_to_order" ] ) && ( $product_quantity < $dokan_settings['order_min_max'][ "min_{$context}_to_order" ] ) ) {
            $error = "Min {$context} {$dokan_settings['order_min_max']["min_{$context}_to_order"]}";
            if ( $return_type_number ) {
                $error = $dokan_settings['order_min_max'][ "min_{$context}_to_order" ];
            }
        }

        if ( empty( $product_quantity ) ) {
            $error = "Min {$context} {$dokan_settings['order_min_max']["min_{$context}_to_order"]}";
            if ( $return_type_number ) {
                $error = $dokan_settings['order_min_max'][ "min_{$context}_to_order" ];
            }
        }

        return $error;
    }

    /**
     * Min_max_quantity check.
     *
     * @since 3.5.0
     *
     * @param int        $product_quantity
     * @param int|string $product_id
     * @param string     $context
     * @param bool      $return_type_number
     *
     * @return string
     */
    public function check_min_max_quantity_or_amount_error( $product_quantity, $product_id, $context = 'quantity', $return_type_number = false ) {
        $error = '';
        if ( 'on' !== dokan_get_option( 'enable_min_max_' . $context, 'dokan_selling', 'off' ) ) {
            return $error;
        }

        $product_settings = get_post_meta( $product_id, '_dokan_min_max_meta', true );
        if ( ! empty( $product_settings ) && ( isset( $product_settings['product_wise_activation'] ) && 'yes' === $product_settings['product_wise_activation'] ) ) {
            return $this->dokan_product_wise_min_max_settings( $product_id, $context, $product_quantity, $return_type_number );
        }

        $dokan_settings = $this->dokan_get_store_settings_by_product_id( $product_id );
        if ( ! empty( $dokan_settings['order_min_max']['vendor_min_max_products'] ) || ! empty( $dokan_settings['order_min_max']['vendor_min_max_product_cat'] ) ) {
            return $this->dokan_global_min_max_settings( $product_id, $context, $product_quantity, $return_type_number );
        }

        return $error;
    }

    /**
     * Update quantity args.
     *
     * @since 3.5.0
     *
     * @param             $data
     * @param \WC_Product $product
     *
     * @return int|mixed
     */
    public function update_quantity_args( $data, $product ) {
        $quantity_error = $this->check_min_max_quantity_or_amount_error( 1, $product->get_id(), 'quantity' );

        if ( empty( $quantity_error ) ) {
            return $data;
        }

        if ( - 1 !== $this->max_quantity ) {
            $data['max_value'] = $this->max_quantity;
        }

        if ( - 1 !== $this->min_quantity ) {
            $data['min_value'] = $this->min_quantity;
        }

        return $data;
    }

    /**
     * Adds variation min max settings to the localized variation parameters to be used by JS.
     *
     * @since 3.5.0
     *
     * @param array       $data      Available variation data.
     * @param \WC_Product $product   Product object.
     * @param object      $variation Variation object.
     *
     * @return array $data
     */
    public function available_variation( $data, $product, $variation ) {
        $variation_id                 = $variation->get_id();
        $dokan_min_max_variation_meta = get_post_meta( $variation_id, '_dokan_min_max_meta', true );

        $min_max_rules = false;
        if ( ! empty( $dokan_min_max_variation_meta ) && 'no' !== $dokan_min_max_variation_meta['product_wise_activation'] ) {
            $min_max_rules              = true;
            $variation_minimum_quantity = $dokan_min_max_variation_meta['min_quantity'];
            $variation_maximum_quantity = $dokan_min_max_variation_meta['max_quantity'];
        }

        $dokan_min_max_meta = get_post_meta( $product->get_id(), '_dokan_min_max_meta', true );
        if ( ! empty( $dokan_min_max_meta ) && 'no' !== $dokan_min_max_meta['product_wise_activation'] ) {
            $min_max_rules    = true;
            $minimum_quantity = $dokan_min_max_meta['min_quantity'];
            $maximum_quantity = $dokan_min_max_meta['max_quantity'];
        }

        // Override product level.
        if ( $variation->managing_stock() ) {
            $product = $variation;
        }

        // Override product level.
        if ( $min_max_rules && ! empty( $variation_minimum_quantity ) ) {
            $minimum_quantity = $variation_minimum_quantity;
        }

        // Override product level.
        if ( $min_max_rules && ! empty( $variation_maximum_quantity ) ) {
            $maximum_quantity = $variation_maximum_quantity;
        }

        $this->check_min_max_quantity_or_amount_error( 1, $product->get_id(), 'quantity' );

        if ( empty( $minimum_quantity ) ) {
            $minimum_quantity = ( - 1 !== $this->min_quantity ) ? $this->min_quantity : 1;
        }

        if ( empty( $maximum_quantity ) ) {
            $maximum_quantity = ( - 1 !== $this->max_quantity ) ? $this->max_quantity : '';
        }

        if ( ! empty( $minimum_quantity ) ) {
            if ( $product->managing_stock() && $product->backorders_allowed() && absint( $minimum_quantity ) > $product->get_stock_quantity() ) {
                $data['min_qty'] = $product->get_stock_quantity();
            } else {
                $data['min_qty'] = $minimum_quantity;
            }
        }

        if ( ! empty( $maximum_quantity ) ) {
            if ( $product->managing_stock() && $product->backorders_allowed() ) {
                $data['max_qty'] = $maximum_quantity;
            } elseif ( $product->managing_stock() && absint( $maximum_quantity ) > $product->get_stock_quantity() ) {
                $data['max_qty'] = $product->get_stock_quantity();
            } else {
                $data['max_qty'] = $maximum_quantity;
            }
        }

        // Don't apply for cart as cart has qty already pre-filled.
        if ( ! is_cart() ) {
            $data['input_value'] = ! empty( $minimum_quantity ) ? $minimum_quantity : 1;
        }

        return $data;
    }

    /**
     * Load_scripts.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function load_scripts() {
        // Only load on single product page and cart page.
        if ( is_product() || is_cart() ) {
            wc_enqueue_js(
                "
					jQuery( 'body' ).on( 'show_variation', function( event, variation ) {
						const step = 'undefined' !== typeof variation.step ? variation.step : 1;
						$('.min_qty').text(variation.input_value);
						jQuery( 'form.variations_form' ).find( 'input[name=quantity]' ).prop( 'step', step ).val( variation.input_value );
					});
					"
            );
        }
    }

    /**
     * Add quantity property to add to cart button on shop loop for simple products.
     *
     * @since 3.5.0
     *
     * @param string      $html    Add to cart link.
     * @param \WC_Product $product Product object.
     *
     * @return string
     */
    public function add_to_cart_link( $html, $product ) {
        if ( 'variable' !== $product->get_type() ) {
            $quantity_error = $this->check_min_max_quantity_or_amount_error( '', $product->get_id(), 'quantity', true );

            if ( ! empty( $quantity_error ) ) {
                $quantity_attribute = number_format_i18n( (int) $quantity_error );
                $html               = str_replace( '<a ', '<a data-quantity="' . $quantity_attribute . '" ', $html );
            }
        }

        return $html;
    }


    /**
     * Action_woocommerce_check_cart_items.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function action_woocommerce_check_cart_items() {
        $i            = 0;
        $bad_products = [];

        foreach ( WC()->cart->get_cart() as $cart_item ) {
            // Checking is here instead of outside the loop is to check cart & checkout page errors.
            if ( ! isset( $cart_item['line_total'] ) ) {
                continue;
            }

            $product_id   = $cart_item['product_id'];
            $variation_id = $cart_item['variation_id'];
            $product      = $variation_id > 0 ? wc_get_product( $product_id ) : $cart_item['data'];

            if ( $product->get_type() === 'variable' ) {
                $product_id = $cart_item['variation_id'];
            }

            $quantity_error = $this->check_min_max_quantity_or_amount_error( $cart_item['quantity'], $product_id, 'quantity', true );

            $quantity_attribute = 1;
            if ( ! empty( $quantity_error ) ) {
                $quantity_attribute = number_format_i18n( (int) $quantity_error );
            }

            $amount_error     = $this->check_min_max_quantity_or_amount_error( $cart_item['line_total'], $product_id, 'amount', true );
            $amount_attribute = 1;
            if ( ! empty( $amount_error ) ) {
                $amount_attribute = $amount_error;
            }

            // Get meta
            $min_amount = $amount_attribute;
            $min_qty    = $quantity_attribute;
            // NOT empty & minimum quantity is greater than or equal to 2 (1 never needs to be checked)
            if ( ! empty( $min_qty ) && $min_qty >= 2 ) {
                $cart_qty = $cart_item['quantity'];
                if ( $cart_qty < $min_qty ) {
                    $bad_products[ $i ]['product_id'] = $product_id;
                    $bad_products[ $i ]['in_cart']    = $cart_qty;
                    $bad_products[ $i ]['min_req']    = $min_qty;
                }

                if ( $cart_qty > $min_qty ) {
                    $bad_products[ $i ]['product_id'] = $product_id;
                    $bad_products[ $i ]['in_cart']    = $cart_qty;
                    $bad_products[ $i ]['max_req']    = $min_qty;
                }
            }

            if ( ! empty( $min_amount ) && $min_amount >= 2 ) {
                $cart_qty = $cart_item['line_total'];
                if ( $cart_qty < $min_amount ) {
                    $bad_products[ $i ]['product_id']     = $product_id;
                    $bad_products[ $i ]['amount_in_cart'] = $cart_qty;
                    $bad_products[ $i ]['min_req_amount'] = $min_amount;
                }

                if ( $cart_qty > $min_amount ) {
                    $bad_products[ $i ]['product_id']     = $product_id;
                    $bad_products[ $i ]['amount_in_cart'] = $cart_qty;
                    $bad_products[ $i ]['max_req_amount'] = $min_amount;
                }
            }

            $i ++;
        }

        // Time to build our error message to inform the customer, about the minimum quantity per order.
        if ( count( $bad_products ) > 0 ) {
            // Clear all others notices
            wc_clear_notices();
            foreach ( $bad_products as $bad_product ) {
                // Displaying an error notice
                if ( ! empty( $bad_product['min_req'] ) ) {
                    wc_add_notice(
                        sprintf(
                        // translators: here %1$s is product name, %2$d is cart quantity and %3$d is quantity.
                            __( '%1$s requires a minimum quantity of %2$d. You currently have %3$d in cart', 'dokan' ),
                            get_the_title( $bad_product['product_id'] ),
                            $bad_product['min_req'],
                            $bad_product['in_cart']
                        ), 'error'
                    );
                } elseif ( ! empty( $bad_product['max_req'] ) ) {
                    wc_add_notice(
                        sprintf(
                        // translators: here %1$s is product name, %2$d is cart quantity and %3$d is quantity.
                            __( '%1$s requires a maximum quantity of %2$d. You currently have %3$d in cart', 'dokan' ),
                            get_the_title( $bad_product['product_id'] ),
                            $bad_product['max_req'],
                            $bad_product['in_cart']
                        ), 'error'
                    );
                }
                if ( ! empty( $bad_product['min_req_amount'] ) ) {
                    wc_add_notice(
                        sprintf(
                        // translators: here %1$s is product name, %2$d is amount and %3$d is amount.
                            __( '%1$s requires a minimum amount of %2$s. You currently have %3$d in cart', 'dokan' ),
                            get_the_title( $bad_product['product_id'] ),
                            wc_price( wc_format_decimal( $bad_product['min_req_amount'] ) ),
                            $bad_product['amount_in_cart']
                        ), 'error'
                    );
                } elseif ( ! empty( $bad_product['max_req_amount'] ) ) {
                    wc_add_notice(
                        sprintf(
                        // translators: here %1$s is product name, %2$d is amount and %3$d is amount.
                            __( '%1$s requires a maximum amount of %2$s. You currently have %3$d in cart', 'dokan' ),
                            get_the_title( $bad_product['product_id'] ),
                            wc_price( wc_format_decimal( $bad_product['max_req_amount'] ) ),
                            $bad_product['amount_in_cart']
                        ), 'error'
                    );
                }
            }

            // Remove proceed to checkout button
            remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
        }
    }

    /**
     * Returns all queued notices, optionally filtered by a notice type.
     *
     * @since  3.5.0
     *
     * @param string $notice_type Optional. The singular name of the notice type - either error, success or notice.
     *
     * @return array|void
     */
    public function wc_get_notices( $notice_type = '' ) {
        if ( ! did_action( 'woocommerce_init' ) ) {
            wc_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before woocommerce_init.', 'dokan' ), '2.3' );

            return;
        }

        $all_notices = WC()->session->get( 'wc_notices', [] );

        if ( empty( $notice_type ) ) {
            $notices = $all_notices;
        } elseif ( isset( $all_notices[ $notice_type ] ) ) {
            $notices = $all_notices[ $notice_type ];
        } else {
            $notices = [];
        }

        return $notices;
    }

    /**
     * Output any plugin specific error messages
     *
     * We use this instead of wc_print_notices, so we
     * can remove any error notices that aren't from us.
     *
     * @since  3.5.0
     *
     * @return void
     */
    public function output_errors() {
        $notices = $this->wc_get_notices( 'error' );
        ob_start();

        wc_get_template(
            'notices/error.php',
            [
                'notices' => array_filter( $notices ),
            ]
        );

        echo wc_kses_notice( ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Dokan get store info by product id. A wrapper for dokan_get_store_info.
     *
     * @since 3.5.0
     *
     * @param $product_id
     *
     * @return array
     */
    public function dokan_get_store_settings_by_product_id( $product_id ) {
        $store_id = dokan_get_vendor_by_product( $product_id, true );

        return dokan_get_store_info( $store_id );
    }

    /**
     * Dokan product wise min max settings.
     *
     * @since 3.5.0
     *
     * @param int    $product_id
     * @param string $context
     * @param int    $product_quantity
     * @param bool   $return_type_number
     *
     * @return string
     */
    public function dokan_product_wise_min_max_settings( $product_id, $context, $product_quantity, $return_type_number ) {
        $error                    = '';
        $product_settings         = get_post_meta( $product_id, '_dokan_min_max_meta', true );
        $this->{"max_{$context}"} = $product_settings[ "max_{$context}" ];
        $this->{"min_{$context}"} = $product_settings[ "min_{$context}" ];

        $found_other_products = false;
        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $product ) {
                if ( $product['product_id'] !== $product_id ) {
                    $found_other_products = true;
                }
            }

            if ( ! empty( $product_settings['_donot_count'] ) && 'yes' === $product_settings['_donot_count'] && WC()->cart->get_cart_contents_count() >= 1 && $found_other_products ) {
                return $error;
            }
        }

        if ( $product_settings[ "min_{$context}" ] < 1 ) {
            return $this->dokan_global_min_max_settings( $product_id, $context, $product_quantity, $return_type_number );
        }

        if ( empty( $product_quantity ) || $product_quantity < $product_settings[ "min_{$context}" ] ) {
            $this->{"min_{$context}"} = $product_settings[ "min_{$context}" ];
            $error                    = __( 'Min', 'dokan' ) . " {$context} {$product_settings["min_{$context}"]}";
            if ( $return_type_number ) {
                $error = $product_settings[ "min_{$context}" ];
            }
        }

        if ( $product_quantity > $product_settings[ "max_{$context}" ] ) {
            $this->{"max_{$context}"} = $product_settings[ "min_{$context}" ];
            $error                    = __( 'Max', 'dokan' ) . " {$context} {$product_settings["max_{$context}"]}";
            if ( $return_type_number ) {
                $error = $product_settings[ "max_{$context}" ];
            }
        }

        return $error;
    }

    /**
     * Dokan global min max settings.
     *
     * @since 3.5.0
     *
     * @param int    $product_id
     * @param string $context
     * @param int    $product_quantity
     *
     * @return string
     */
    public function dokan_global_min_max_settings( $product_id, $context, $product_quantity, $return_type_number ) {
        $error                    = '';
        $dokan_settings           = $this->dokan_get_store_settings_by_product_id( $product_id );
        if ( ! isset( $dokan_settings['order_min_max'] ) ) {
            return $error;
        }
        $this->{"max_{$context}"} = $dokan_settings['order_min_max'][ "max_{$context}_to_order" ];
        $this->{"min_{$context}"} = $dokan_settings['order_min_max'][ "min_{$context}_to_order" ];

        // Global Product settings.
        if ( ! in_array( '-1', $dokan_settings['order_min_max']['vendor_min_max_products'], true ) ) {
            // In this array string type product id stored, that's why in_array strict type can't be set.
            // phpcs:ignore
            if ( in_array( $product_id, $dokan_settings['order_min_max']['vendor_min_max_products'] ) ) {
                $error = $this->maybe_quantity_or_amount_error( $dokan_settings, $context, $product_quantity, $return_type_number );
            }
        } else {
            $error = $this->maybe_quantity_or_amount_error( $dokan_settings, $context, $product_quantity, $return_type_number );
        }

        if ( empty( $error ) ) {
            $product_settings = get_post_meta( $product_id, '_dokan_min_max_meta', true );
            // Global Category settings.
            if ( ! empty( $product_settings ) && ( isset( $product_settings['ignore_from_cat'] ) && 'yes' === $product_settings['ignore_from_cat'] ) ) {
                $this->{"max_{$context}"} = - 1;
                $this->{"min_{$context}"} = - 1;

                return $error;
            }

            $terms = get_the_terms( $product_id, 'product_cat' );

            if ( empty( $terms ) ) {
                return $error;
            }

            $found_cat = false;

            foreach ( $terms as $term ) {
                // phpcs:ignore
                if ( is_array( $dokan_settings['order_min_max']['vendor_min_max_product_cat'] ) && in_array( $term->term_id, $dokan_settings['order_min_max']['vendor_min_max_product_cat'] ) ) {
                    $found_cat = true;
                    break;
                } else {
                    if ( $term->term_id === (int) $dokan_settings['order_min_max']['vendor_min_max_product_cat'] ) {
                        $found_cat = true;
                        break;
                    }
                }
            }

            if ( $found_cat ) {
                $this->{"max_{$context}"} = $dokan_settings['order_min_max'][ "max_{$context}_to_order" ];
                $this->{"min_{$context}"} = $dokan_settings['order_min_max'][ "min_{$context}_to_order" ];
                $error                    = $this->maybe_quantity_or_amount_error( $dokan_settings, $context, $product_quantity, $return_type_number );
            }
        }

        return $error;
    }

}
