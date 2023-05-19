<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use WP_Error;
use Exception;
use WC_Payment_Token_CC;
use Stripe\Customer as StripeCustomer;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\Dokan\Exceptions\DokanException;

defined( 'ABSPATH' ) || exit;

/**
 * StripeCustomer class.
 *
 * Represents a Stripe Customer.
 */
class Customer {

    /**
     * Stripe customer ID
     * @var string
     */
    private $id = '';

    /**
     * WP User ID
     * @var integer
     */
    private $user_id = 0;

    /**
     * Data from API
     * @var array
     */
    private $customer_data = [];

    /**
     * Constructor
     * @param int $user_id The WP user ID
     */
    public function __construct( $user_id = 0 ) {
        if ( $user_id ) {
            $this->set_user_id( $user_id );
            $this->set_id( $this->get_id_from_meta( $user_id ) );
        }
    }

    /**
     * Get Stripe customer ID.
     * @return string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Set Stripe customer ID.
     * @param [type] $id [description]
     */
    public function set_id( $id ) {
        $this->update_id_in_meta( $id );
        $this->id = wc_clean( $id );
    }

    /**
     * User ID in WordPress.
     * @return int
     */
    public function get_user_id() {
        return absint( $this->user_id );
    }

    /**
     * Set User ID used by WordPress.
     * @param int $user_id
     */
    public function set_user_id( $user_id ) {
        $this->user_id = absint( $user_id );
    }

    /**
     * Get user object.
     * @return WP_User
     */
    protected function get_user() {
        return $this->get_user_id() ? get_user_by( 'id', $this->get_user_id() ) : false;
    }

    /**
     * Store data from the Stripe API about this customer
     */
    public function set_customer_data( $data ) {
        $this->customer_data = $data;
    }

    /**
     * Generates the customer request, used for both creating and updating customers.
     *
     * @param  array $args Additional arguments (optional).
     * @return array
     */
    protected function generate_customer_request() {
        $billing_email = isset( $_POST['billing_email'] ) ? filter_var( wp_unslash( $_POST['billing_email'] ), FILTER_SANITIZE_EMAIL ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
        $user          = $this->get_user();

        if ( $user ) {
            $billing_first_name = get_user_meta( $user->ID, 'billing_first_name', true );
            $billing_last_name  = get_user_meta( $user->ID, 'billing_last_name', true );

            // If billing first name does not exists try the user first name.
            if ( empty( $billing_first_name ) ) {
                $billing_first_name = get_user_meta( $user->ID, 'first_name', true );
            }

            // If billing last name does not exists try the user last name.
            if ( empty( $billing_last_name ) ) {
                $billing_last_name = get_user_meta( $user->ID, 'last_name', true );
            }

            // translators: %1$s First name, %2$s Second name, %3$s Username.
            $description = sprintf( __( 'Name: %1$s %2$s, Username: %3$s', 'dokan' ), $billing_first_name, $billing_last_name, $user->user_login );

            $defaults = [
                'email'       => $user->user_email,
                'description' => $description,
            ];
        } else {
            $billing_first_name = isset( $_POST['billing_first_name'] ) ? filter_var( wp_unslash( $_POST['billing_first_name'] ), FILTER_SANITIZE_STRING ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
            $billing_last_name  = isset( $_POST['billing_last_name'] ) ? filter_var( wp_unslash( $_POST['billing_last_name'] ), FILTER_SANITIZE_STRING ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

            // translators: %1$s First name, %2$s Second name.
            $description = sprintf( __( 'Name: %1$s %2$s, Guest', 'dokan' ), $billing_first_name, $billing_last_name );

            $defaults = [
                'email'       => $billing_email,
                'description' => $description,
            ];
        }

        $metadata             = [];
        $defaults['metadata'] = apply_filters( 'dokan_stripe_connect_customer_metadata', $metadata, $user );

        return $defaults;
    }

    /**
     * Create a customer via API.
     * @param array $args
     * @return WP_Error|int
     * @throws DokanException
     */
    public function create_customer( $args = [] ) {
        $args = wp_parse_args( $args, $this->generate_customer_request() );

        try {
            $customer = StripeCustomer::create( $args );
        } catch ( Exception $e ) {
            throw new DokanException( 'dokan_unable_to_create_customer', $e->getMessage() );
        }

        $this->set_id( $customer->id );
        $this->clear_cache();
        $this->set_customer_data( $customer );

        if ( $this->get_user_id() ) {
            $this->update_id_in_meta( $customer->id );
        }

        do_action( 'dokan_stripe_connect_add_customer', $args, $customer );

        return $customer->id;
    }

    /**
     * Updates the Stripe customer through the API.
     *
     * @param array $args Additional arguments for the request (optional).
     * @throws DokanException
     */
    public function update_customer( $args = [] ) {
        if ( empty( $this->get_id() ) ) {
            throw new DokanException( 'id_required_to_update_user', __( 'Attempting to update a Stripe customer without a customer ID.', 'dokan' ) );
        }

        // get customer args
        $args = wp_parse_args( $args, $this->generate_customer_request() );

        try {
            $response = StripeCustomer::update( $this->get_id(), $args );
        } catch ( Exception $e ) {
            if ( $this->is_no_such_customer_error( $e->getMessage() ) ) {
                $this->delete_id_from_meta();
                return $this->create_customer();
            }
            throw new DokanException( 'customer_update_failed', $e->getMessage() );
        }

        $this->clear_cache();
        $this->set_customer_data( $response );

        do_action( 'dokan_stripe_connnect_update_customer', $args, $response );
        return $this->get_id();
    }

    /**
     * Deletes caches for this users cards.
     */
    public function clear_cache() {
        delete_transient( 'dokan_stripe_sources_' . $this->get_id() );
        delete_transient( 'dokan_stripe_customer_' . $this->get_id() );
        $this->customer_data = [];
    }

    /**
     * Retrieves the Stripe Customer ID from the user meta.
     *
     * @param  int $user_id The ID of the WordPress user.
     * @return string|bool  Either the Stripe ID or false.
     */
    public function get_id_from_meta( $user_id ) {
        return get_user_meta( $user_id, 'dokan_stripe_customer_id', true );
    }


    /**
     * Updates the current user with the right Stripe ID in the meta table.
     *
     * @param string $id The Stripe customer ID.
     */
    public function update_id_in_meta( $id ) {
        update_user_meta( $this->get_user_id(), 'dokan_stripe_customer_id', $id );
    }

    /**
     * Deletes the user ID from the meta table with the right key.
     */
    public function delete_id_from_meta() {
        $this->update_id_in_meta( '' );
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such customer.
     *
     * @param array $error
     * @return false|int
     * @since 3.2.2
     */
    public function is_no_such_customer_error( $error ) {
        return preg_match( '/No such customer/i', $error );
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such customer.
     *
     * @since 3.2.2
     * @param array $error
     * @return bool
     */
    public function is_source_already_attached_error( $error ) {
        return preg_match( '/already been attached to a customer/i', $error );
    }

    /**
     * Add a source for this stripe customer.
     * @param string $source_id
     * @return WP_Error|int
     * @throws DokanException
     */
    public function add_source( $source_id ) {
        if ( ! $this->get_id() ) {
            $this->set_id( $this->create_customer() );
        }

        if ( empty( $source_id ) ) {
            return false;
        }

        try {
            $response = StripeCustomer::createSource( $this->get_id(), [ 'source' => $source_id ] );
            //make this source as default
            $this->set_default_source( $response->id );
        } catch ( Exception $e ) {
            if ( $this->is_no_such_customer_error( $e->getMessage() ) ) {
                $this->delete_id_from_meta();
                $this->create_customer();
                return $this->add_source( $source_id );
            } elseif ( $this->is_source_already_attached_error( $e->getMessage() ) ) {
                try {
                    $response = StripeCustomer::retrieveSource( $this->get_id(), $source_id );
                    if ( $response->id ) {
                        return $response->id;
                    }
                } catch ( Exception $e ) {
                    throw new DokanException( 'dokan_unable_to_get_source', $e->getMessage() );
                }
            } else {
                throw new DokanException( 'dokan_unable_to_add_source', $e->getMessage() );
            }
        }

        $wc_token = false;

        // Add token to WooCommerce.
        if ( $this->get_user_id() && class_exists( 'WC_Payment_Token_CC' ) ) {
            if ( ! empty( $response->type ) ) {
                if ( 'source' === $response->object && 'card' === $response->type ) {
                    $wc_token = new WC_Payment_Token_CC();
                    $wc_token->set_token( $response->id );
                    $wc_token->set_gateway_id( Helper::get_gateway_id() );
                    $wc_token->set_card_type( strtolower( $response->card->brand ) );
                    $wc_token->set_last4( $response->card->last4 );
                    $wc_token->set_expiry_month( $response->card->exp_month );
                    $wc_token->set_expiry_year( $response->card->exp_year );
                }
            } else {
                // Legacy.
                $wc_token = new WC_Payment_Token_CC();
                $wc_token->set_token( $response->id );
                $wc_token->set_gateway_id( Helper::get_gateway_id() );
                $wc_token->set_card_type( strtolower( $response->brand ) );
                $wc_token->set_last4( $response->last4 );
                $wc_token->set_expiry_month( $response->exp_month );
                $wc_token->set_expiry_year( $response->exp_year );
            }

            $wc_token->set_user_id( $this->get_user_id() );
            $wc_token->save();
        }

        $this->clear_cache();
        do_action( 'dokan_stripe_add_source', $this->get_id(), $wc_token, $response, $source_id );

        return $response->id;
    }

    /**
     * Add a source for this stripe customer.
     *
     * @param string $source_id
     *
     * @since 3.3.6
     *
     * @return WP_Error|int
     *
     * @throws DokanException
     */
    public function add_source_only( $source_id ) {
        if ( ! $this->get_id() ) {
            $this->set_id( $this->create_customer() );
        }

        if ( empty( $source_id ) ) {
            return false;
        }

        try {
            $response = StripeCustomer::createSource( $this->get_id(), [ 'source' => $source_id ] );
            //make this source as default
            $this->set_default_source( $response->id );
        } catch ( Exception $e ) {
            if ( $this->is_no_such_customer_error( $e->getMessage() ) ) {
                $this->delete_id_from_meta();
                $this->create_customer();
                return $this->add_source_only( $source_id );
            } elseif ( $this->is_source_already_attached_error( $e->getMessage() ) ) {
                try {
                    $response = StripeCustomer::retrieveSource( $this->get_id(), $source_id );
                    if ( $response->id ) {
                        return $response->id;
                    }
                } catch ( Exception $e ) {
                    throw new DokanException( 'dokan_unable_to_get_source', $e->getMessage() );
                }
            } else {
                throw new DokanException( 'dokan_unable_to_add_source', $e->getMessage() );
            }
        }

        $this->clear_cache();

        return $response->id;
    }

    /**
     * Delete a source from stripe.
     * @param string $source_id
     * @return bool|void
     */
    public function delete_source( $source_id ) {
        if ( ! $this->get_id() ) {
            return false;
        }

        try {
            $response = StripeCustomer::deleteSource( $this->get_id(), wc_clean( $source_id ) );
        } catch ( Exception $e ) {
            $this->clear_cache();
            return;
        }

        $this->clear_cache();
        do_action( 'dokan_stripe_connect_delete_source', $this->get_id(), $response );
    }

    /**
     * Delete a source from stripe.
     * @param string $source_id
     * @return bool|void
     */
    public function set_default_source( $source_id ) {
        if ( ! $this->get_id() ) {
            return false;
        }

        try {
            $response = StripeCustomer::update( $this->get_id(), [ 'default_source' => wc_clean( $source_id ) ] );
        } catch ( Exception $e ) {
            $this->clear_cache();
            return false;
        }

        $this->clear_cache();
        do_action( 'dokan_stripe_connect_set_default_source', $response );
    }

    /**
     * Get sources from a customer object
     *
     * @since 3.0.3
     *
     * @return array
     */
    public function get_sources() {
        try {
            $response = StripeCustomer::allSources( $this->get_id() );
        } catch ( Exception $e ) {
            return [];
        }

        return ! empty( $response->data ) ? $response->data : [];
    }
}
