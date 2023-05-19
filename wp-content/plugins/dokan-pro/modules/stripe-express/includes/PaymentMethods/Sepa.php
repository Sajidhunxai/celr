<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\PaymentMethods;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\PaymentMethod;

/**
 * Gateway handler class for SEPA Direct Debit.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\PaymentMethods
 */
class Sepa extends PaymentMethod {

    /**
     * Stores Stripe ID.
     *
     * @since 3.7.8
     *
     * @var string
     */
    const STRIPE_ID = 'sepa_debit';

    /**
     * Strores label for the method.
     *
     * @since 3.7.8
     *
     * @var string
     */
    const LABEL = 'SEPA Direct Debit';

    /**
     * Constructor for iDEAL payment method.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->stripe_id            = self::STRIPE_ID;
        $this->title                = apply_filters( 'dokan_stripe_express_payment_method_title', __( 'Pay with SEPA Direct Debit', 'dokan' ), self::STRIPE_ID );
        $this->is_reusable          = true;
        $this->supported_currencies = [ 'EUR' ];
        $this->label                = Helper::get_method_label( self::STRIPE_ID );
        $this->description          = __(
            'Reach 500 million customers and over 20 million businesses across the European Union.',
            'dokan'
        );
    }

    /**
     * Returns string representing payment method type
     * to query to retrieve saved payment methods from Stripe.
     *
     * @since 3.7.8
     *
     * @return string|null
     */
    public function get_retrievable_type() {
        return $this->get_id();
    }
}
