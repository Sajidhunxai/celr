<?php

namespace WeDevs\DokanPro\Refund;

use ArrayAccess;
use WP_Error;

/**
 * A helper class to mimic WP_REST_Request
 *
 * Useful when a request comes from other than REST, for example Ajax request.
 *
 * @see \WeDevs\DokanPro\Refund\Ajax for usage
 */
class Request implements ArrayAccess {

    /**
     * The refund model
     *
     * @since 3.0.0
     *
     * @var null|\WeDevs\DokanPro\Refund\Refund
     */
    protected $model = null;

    /**
     * Required params
     *
     * @since 3.0.0
     *
     * @var null|array
     */
    protected $required = null;

    /**
     * Errors during request process
     *
     * @since 3.0.0
     *
     * @var null|array
     */
    protected $error = null;

    /**
     * Class constructor
     *
     * @since 3.0.0
     *
     * @param array $data
     */
    public function __construct( $data ) {
        $this->model = new Refund( $data );
    }

    /**
     * ArrayAccess method override
     *
     * @since 3.0.0
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists( $offset ) {
        $data = $this->get_params();
        return isset( $data[ $offset ] );
    }

    /**
     * ArrayAccess offset method override
     *
     * @since 3.0.0
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet( $offset, $value ) {
        $this->set_param( $offset, $value );
    }

    /**
     * ArrayAccess method override
     *
     * @since 3.0.0
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet( $offset ) {
        return $this->get_param( $offset );
    }

    /**
     * ArrayAccess method override
     *
     * @since 3.0.0
     *
     * @param string $offset
     *
     * @return void
     */
    public function offsetUnset( $offset ) {
        // not using this method here!
    }

    /**
     * Get refund model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function get_model() {
        return $this->model;
    }

    /**
     * Get model data
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_params() {
        return $this->model->get_data();
    }

    /**
     * Set model param/data
     *
     * @since 3.0.0
     *
     * @param string $param
     * @param mixed  $value
     *
     * @return void
     */
    public function set_param( $param, $value ) {
        $setter = "set_$param";
        $this->model->$setter( $value );
    }

    /**
     * Get a model param value
     *
     * @since 3.0.0
     *
     * @param string $param
     *
     * @return mixed
     */
    public function get_param( $param ) {
        $data = $this->get_params();

        if ( isset( $data[ $param ] ) ) {
            return $data[ $param ];
        }

        return null;
    }

    /**
     * Add error
     *
     * @since 3.0.0
     *
     * @param \WP_Error $error
     *
     * @return void
     */
    protected function add_error( $error ) {
        if ( ! $this->has_error() ) {
            $this->error = new WP_Error();
        }

        $this->error->add( $error->get_error_code(), $error->get_error_message(), $error->get_error_data() );
    }

    /**
     * Set required fields/params
     *
     * @since 3.0.0
     *
     * @param array $required
     *
     * @return void
     */
    public function set_required( $required ) {
        $this->required = $required;
    }

    /**
     * Checks if Request has any error
     *
     * @since 3.0.0
     *
     * @return bool
     */
    public function has_error() {
        return ! is_null( $this->get_error() );
    }

    /**
     * Get request error
     *
     * @since 3.0.0
     *
     * @return \WP_Error
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * Validate a request
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function validate() {
        $data = $this->model->get_data();

        if ( is_array( $this->required ) ) {
            $missing_required = [];

            foreach ( $this->required as $param => $message ) {
                if ( empty( $data[ $param ] ) ) {
                    $missing_required[] = $message;
                }
            }

            if ( $missing_required ) {
                $this->add_error(
                    new WP_Error(
                        'dokan_pro_missing_params',
                        // translators: %s is a list of required params
                        sprintf( __( 'Missing parameter(s): %s', 'dokan' ), implode( ', ', $missing_required ) ),
                        [
                            'status' => 400,
                            'params' => $missing_required,
                        ]
                    )
                );

                return;
            }
        }

        $is_refund_amount_valid = $this->validate_refund_amount( $data );
        if ( ! $is_refund_amount_valid ) {
            return;
        }

        foreach ( $data as $param => $value ) {
            $method = "validate_$param";

            if ( method_exists( Validator::class, $method ) ) {
                $validate = Validator::$method( $value, $this, $param );

                if ( is_wp_error( $validate ) ) {
                    $this->add_error( $validate );
                    return;
                }
            }
        }
    }

    /**
     * Sanitize a request
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function sanitize() {
        $data = $this->model->get_data();

        foreach ( $data as $param => $value ) {
            $method = "sanitize_$param";

            if ( method_exists( Sanitizer::class, $method ) ) {
                $sanitized = Sanitizer::$method( $value, $this, $param );

                if ( is_wp_error( $sanitized ) ) {
                    $this->add_error( $sanitized );
                } else {
                    $this->set_param( $param, $sanitized );
                }
            }
        }
    }

    /**
     * Validate amount for over issue.
     *
     * @param array $data Refund request data.
     *
     * @since 3.2.3
     * @since 3.3.3 Approvable and pending request validation added.
     * @since 3.3.3 LineItem name added with validation message.
     *
     * @return bool
     */
    public function validate_refund_amount( $data ): bool {
        global $wpdb;
        $order_id                = $data['order_id'];
        $order                   = wc_get_order( $order_id );
        $item_totals_request     = $data['item_totals'];
        $item_tax_totals_request = $data['item_tax_totals'];
        $already_refunded        = array();
        $refund_manager          = new Manager();

        if ( $refund_manager->has_pending_request( $order_id ) ) {
            $this->add_error(
                new WP_Error(
                    'dokan_pro_refund_error_has_pending_request',
                    __( 'There is already a pending refund request for this order.', 'dokan' )
                )
            );
            return false;
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dokan_refund WHERE order_id=%d AND status != %d",
                $order_id,
                2  // 2 is refund status cancel.
            )
        );

        foreach ( $results as $previous_refund ) {
            if ( ! empty( $previous_refund->item_totals ) ) {
                $item_totals_arr = json_decode( $previous_refund->item_totals, true );

                foreach ( $item_totals_arr as $line_item_id => $amount ) {
                    $already_refunded[ $line_item_id ]['item_totals'] = ( isset( $already_refunded[ $line_item_id ]['item_totals'] ) ) ? ( (float) $already_refunded[ $line_item_id ]['item_totals'] + (float) $amount ) : (float) $amount;
                }
            }

            if ( ! empty( $previous_refund->item_tax_totals ) ) {
                $item_tax_totals_arr = json_decode( $previous_refund->item_tax_totals, true );

                foreach ( $item_tax_totals_arr as $line_item_id => $amount_array ) {
                    $already_refunded[ $line_item_id ]['item_tax_totals'] = ( isset( $already_refunded[ $line_item_id ]['item_tax_totals'] ) ) ? ( (float) $already_refunded[ $line_item_id ]['item_tax_totals'] + (float) array_sum( $amount_array ) ) : (float) array_sum( $amount_array );
                }
            }
            unset( $item_totals_arr );
            unset( $item_tax_totals_arr );
        }

        foreach ( $order->get_items( [ 'line_item', 'shipping', 'fee' ] ) as $order_line_item_id => $order_line_item ) {
            $refunded_amount = ( isset( $already_refunded[ $order_line_item_id ] ) && ! empty( $already_refunded[ $order_line_item_id ]['item_totals'] ) ) ? (float) wc_format_decimal( $already_refunded[ $order_line_item_id ]['item_totals'], wc_get_rounding_precision() ) : 0.00;
            $refunded_tax    = ( isset( $already_refunded[ $order_line_item_id ] ) && ! empty( $already_refunded[ $order_line_item_id ]['item_tax_totals'] ) ) ? (float) wc_format_decimal( $already_refunded[ $order_line_item_id ]['item_tax_totals'], wc_get_rounding_precision() ) : 0.00;

            if ( isset( $item_totals_request[ $order_line_item_id ] ) ) {
                $order_line_item_total         = (float) wc_format_decimal( $order_line_item->get_total(), wc_get_rounding_precision() );
                $order_line_item_request_total = (float) wc_format_decimal( $item_totals_request[ $order_line_item_id ], wc_get_rounding_precision() );
                if ( $order_line_item_total < ( $refunded_amount + $order_line_item_request_total ) ) {
                    $this->add_error(
                        new WP_Error(
                            'dokan_pro_refund_error_excess_refund',
                            // translators: Order Line Item name.
                            sprintf( __( 'The refund amount %1$s is more than the permitted amount for line %2$s', 'dokan' ), wc_format_decimal( $item_totals_request[ $order_line_item_id ], wc_get_price_decimals() ), $order_line_item->get_name() )
                        )
                    );

                    return false;
                } elseif ( $order_line_item_request_total < 0 ) {
                    $this->add_error(
                        new WP_Error(
                            'dokan_pro_refund_error_negative_refund_amount',
                            // translators: Order Line Item name.
                            sprintf( __( 'The Refund amount is negative number for line %s.', 'dokan' ), $order_line_item->get_name() )
                        )
                    );

                    return false;
                }
            }

            if ( ! isset( $item_tax_totals_request[ $order_line_item_id ] ) ) {
                continue;
            }

            $order_line_item_total_tax = (float) wc_format_decimal( $order_line_item->get_total_tax(), 2 );
            $order_line_item_total_request_tax = (float) wc_format_decimal( array_sum( $item_tax_totals_request[ $order_line_item_id ] ), 2 );

            if ( $order_line_item_total_tax < ( $refunded_tax + $order_line_item_total_request_tax ) ) {
                $this->add_error(
                    new WP_Error(
                        'dokan_pro_refund_error_tax_excess_refund',
                        // translators: Order Line Item name.
                        sprintf( __( 'The Refund tax amount is more than permitted amount for line %s', 'dokan' ), $order_line_item->get_name() )
                    )
                );

                return false;
            } elseif ( min( $item_tax_totals_request[ $order_line_item_id ] ) < 0 ) {
                $this->add_error(
                    new WP_Error(
                        'dokan_pro_refund_error_negative_refund_tax_amount',
                        // translators: Order Line Item name.
                        sprintf( __( 'The Refund tax amount is negative number for line %s', 'dokan' ), $order_line_item->get_name() )
                    )
                );

                return false;
            }
        }

        return true;
    }
}
