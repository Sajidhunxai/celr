<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens;

defined( 'ABSPATH' ) || exit; // Exit if called directly

/**
 * Wrapper class for WC_Payment_Token_CC.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens
 */
class Card extends \WC_Payment_Token_CC {}
