<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use Stripe\Account as StripeAccount;
use WeDevs\Dokan\Exceptions\DokanException;
use Stripe\Exception\InvalidRequestException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * Client API handler class
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class Account extends Api {

    /**
     * Retrieves a client.
     *
     * @since 3.6.1
     *
     * @param int|string          $account_id
     * @param array<string,mixed> $args
     *
     * @return \Stripe\Account
     * @throws DokanException
     */
    public static function get( $account_id, array $args = [] ) {
        try {
            return static::api()->accounts->retrieve( $account_id, $args );
        } catch ( \Stripe\Exception\ApiErrorException $e ) {
            /* translators: error message */
            $message = sprintf( __( 'Stripe API error on fetching account: %s', 'dokan' ), $e->getMessage() );
            Helper::log( $message, 'Account' );

            $error_code = ! empty( $e->getError()->code ) ? $e->getError()->code : $e->getCode();
            throw new DokanException( $error_code, $e->getMessage() );
        } catch ( Exception $e ) {
            /* translators: error message */
            $message = sprintf( __( 'Could not retrieve account: %s', 'dokan' ), $e->getMessage() );
            Helper::log( $message, 'Account' );

            throw new DokanException( 'account-retrieve-error', $message );
        }
    }

    /**
     * Retrieves all connected accounts.
     *
     * @since 3.6.1
     *
     * @param array<string,mixed> $args
     *
     * @return \Stripe\Account[]|false
     */
    public static function all( array $args = [] ) {
        try {
            return static::api()->accounts->all( $args );
        } catch ( Exception $e ) {
            Helper::log( 'Could not retrieve all accounts: ' . $e->getMessage(), 'Account' );
            return false;
        }
    }

    /**
     * Creates an express client.
     *
     * @since 3.6.1
     *
     * @param array<string,mixed> $args
     *
     * @return \Stripe\Account
     * @throws DokanException
     */
    public static function create( array $args = [] ) {
        try {
            $defaults = [
                'type'         => StripeAccount::TYPE_EXPRESS,
                'capabilities' => [
                    'card_payments'       => [
                        'requested' => true,
                    ],
                    'ideal_payments'      => [
                        'requested' => true,
                    ],
                    'sepa_debit_payments' => [
                        'requested' => true,
                    ],
                    'transfers'           => [
                        'requested' => true,
                    ],
                ],
            ];

            $args = wp_parse_args( $args, $defaults );
            return static::api()->accounts->create( $args );
        } catch ( InvalidRequestException $e ) {
            if ( 'requested_capabilities' === $e->getStripeParam() ) {
                if ( ! empty( $args['country'] ) ) {
                    $message = sprintf(
                        /* translators: %1$s) Opening anchor tag with link, %2$s) Closing anchor tag */
                        __( 'Required capabilities in the selected country is not currently supported. Please select a supported country. For the list of the supported countries, see %1$sStripe documentation%2$s.', 'dokan' ),
                        '<a href="https://stripe.com/global" target="_blank">',
                        '</a>'
                    );
                } else {
                    $message = __( 'The requested capabilities are not supported for this account data. Please contact Admin for more details.' );
                }
            }
            throw new DokanException( 'dokan-stripe-express-invalid-request-error', $message );
        } catch ( Exception $e ) {
            /* translators: error message */
            $message = sprintf( __( 'Could not create account. Error: %s', 'dokan' ), $e->getMessage() );
            Helper::log( $message, 'Account' );
            throw new DokanException( 'dokan-stripe-express-account-create-error', $message );
        }
    }

    /**
     * Updates an connected account.
     *
     * @since 3.6.1
     *
     * @param string $account_id
     * @param array  $data
     *
     * @return \Stripe\Account
     * @throws DokanException
     */
    public static function update( $account_id, array $data = [] ) {
        try {
            return static::api()->accounts->update( $account_id, $data );
        } catch ( Exception $e ) {
            /* translators: 1) account id, 2) error message */
            $message = sprintf( __( 'Could not update account: %1$s. Error: %2$s', 'dokan' ), $account_id, $e->getMessage() );
            Helper::log( $message, 'Account', 'error' );
            throw new DokanException( 'dokan-stripe-express-account-create-error', $message );
        }
    }

    /**
     * Creates link for client onboarding.
     *
     * @since 3.6.1
     *
     * @param int|string          $account_id
     * @param array<string,mixed> $args
     *
     * @return \Stripe\AccountLink
     * @throws DokanException
     */
    public static function create_onboarding_link( $account_id, array $args = [] ) {
        $defaults = [
            'account' => $account_id,
            'type'    => 'account_onboarding',
        ];

        $args = wp_parse_args( $args, $defaults );

        try {
            return static::api()->accountLinks->create( $args );
        } catch ( Exception $e ) {
            /* translators: error message */
            $message = sprintf( __( 'Could not create client account link: %s', 'dokan' ), $e->getMessage() );
            Helper::log( $message, 'Account', 'error' );
            throw new DokanException( 'dokan-stripe-express-account-onboarding-error', $message );
        }
    }

    /**
     * Creates login link for a connnected express account.
     *
     * @since 3.6.1
     *
     * @param string              $account_id
     * @param array<string,mixed> $args
     *
     * @return \Stripe\LoginLink
     * @throws DokanException
     */
    public static function create_login_link( $account_id, $args = [] ) {
        try {
            return static::api()->accounts->createLoginLink( $account_id, $args );
        } catch ( Exception $e ) {
            $message = sprintf(
                /* translators: 1) account id, 2) error message */
                __( 'Could not create login link for account: %1$s. Error: %2$s', 'dokan' ),
                $account_id,
                $e->getMessage()
            );
            Helper::log( $message, 'Account', 'error' );
            throw new DokanException( 'dokan-stripe-express-login-link-create-error', $message );
        }
    }

    /**
     * Retrieve balance data.
     *
     * @since 3.7.8
     *
     * @return \Stripe\Balance|false
     */
    public static function get_balance() {
        try {
            return static::api()->balance->retrieve();
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve balance. Error: %s', $e->getMessage() ), 'Balance' );
            return false;
        }
    }
}
